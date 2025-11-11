<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Search;
use App\Models\Tweet;
use App\Models\Account;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{

    public function index(Request $request)
    {
        $user = auth()->user();

        // Obtener período seleccionado (por defecto 24 horas)
        $period = $request->get('period', '24h');
        $startDate = $this->getStartDate($period);
        $previousStartDate = $this->getPreviousStartDate($period);

        // Obtener IDs de búsquedas del usuario/empresa (multi-tenant)
        $searchIds = $this->getUserSearchIds($user);

        // Si no hay búsquedas, retornar vista vacía
        if ($searchIds->isEmpty()) {
            return view('admin_panel.pages.dashboard.index', [
                'period' => $period,
                'kpis' => null,
                'charts' => null,
                'topTweets' => collect([]),
                'topSearches' => collect([])
            ]);
        }

        // Calcular KPIs
        $kpis = $this->calculateKPIs($searchIds, $startDate, $previousStartDate);

        // Obtener datos para gráficos
        $charts = $this->getChartData($searchIds, $startDate, $period);

        // Top tweets con alto score IA
        $topTweets = $this->getTopTweets($searchIds, $startDate);

        // Performance por búsqueda
        $topSearches = $this->getTopSearches($searchIds, $startDate);

        return view('admin_panel.pages.dashboard.index', compact('period', 'kpis', 'charts', 'topTweets', 'topSearches'));
    }

    /**
     * Obtener fecha de inicio según el período seleccionado
     */
    private function getStartDate($period)
    {
        return match($period) {
            '24h' => now()->subHours(24),
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            default => now()->subHours(24)
        };
    }

    /**
     * Obtener fecha de inicio del período anterior (para comparación)
     */
    private function getPreviousStartDate($period)
    {
        return match($period) {
            '24h' => now()->subHours(48),
            '7d' => now()->subDays(14),
            '30d' => now()->subDays(60),
            default => now()->subHours(48)
        };
    }

    /**
     * Obtener IDs de búsquedas según usuario/empresa (multi-tenant)
     */
    private function getUserSearchIds($user)
    {
        if ($user->company) {
            // Si el usuario tiene empresa, obtener búsquedas de todos los usuarios de la empresa
            $companyUserIds = $user->company->users->pluck('id');
            return Search::whereIn('user_id', $companyUserIds)->pluck('id');
        }

        // Si no tiene empresa, solo sus búsquedas
        return Search::where('user_id', $user->id)->pluck('id');
    }

    /**
     * Calcular KPIs principales
     */
    private function calculateKPIs($searchIds, $startDate, $previousStartDate)
    {
        // Tweets período actual
        $currentTweets = Tweet::whereJsonContains('matched_search_ids', $searchIds->toArray())
            ->where('created_at_twitter', '>=', $startDate)
            ->get();

        // Tweets período anterior
        $previousTweets = Tweet::whereJsonContains('matched_search_ids', $searchIds->toArray())
            ->whereBetween('created_at_twitter', [$previousStartDate, $startDate])
            ->get();

        // KPI 1: Total de tweets
        $totalTweets = $currentTweets->count();
        $previousTotal = $previousTweets->count();
        $tweetsChange = $previousTotal > 0 ? (($totalTweets - $previousTotal) / $previousTotal) * 100 : 0;

        // KPI 2: Tweets de alto valor (score >= 70)
        $highValueTweets = $currentTweets->where('ia_analyzed', true)->where('ia_score', '>=', 70)->count();
        $highValuePercentage = $totalTweets > 0 ? ($highValueTweets / $totalTweets) * 100 : 0;

        // KPI 3: Score promedio IA
        $avgIaScore = $currentTweets->where('ia_analyzed', true)->avg('ia_score') ?? 0;

        // KPI 4: Engagement total
        $totalEngagement = $currentTweets->sum(function($tweet) {
            return ($tweet->like_count ?? 0) + ($tweet->retweet_count ?? 0) + ($tweet->reply_count ?? 0);
        });
        $previousEngagement = $previousTweets->sum(function($tweet) {
            return ($tweet->like_count ?? 0) + ($tweet->retweet_count ?? 0) + ($tweet->reply_count ?? 0);
        });
        $engagementChange = $previousEngagement > 0 ? (($totalEngagement - $previousEngagement) / $previousEngagement) * 100 : 0;

        return [
            'total_tweets' => [
                'value' => $totalTweets,
                'change' => round($tweetsChange, 1),
                'trend' => $tweetsChange >= 0 ? 'up' : 'down'
            ],
            'high_value_tweets' => [
                'value' => $highValueTweets,
                'percentage' => round($highValuePercentage, 1)
            ],
            'avg_ia_score' => [
                'value' => round($avgIaScore, 1),
                'color' => $avgIaScore >= 70 ? 'success' : ($avgIaScore >= 40 ? 'warning' : 'danger')
            ],
            'total_engagement' => [
                'value' => $totalEngagement,
                'change' => round($engagementChange, 1),
                'trend' => $engagementChange >= 0 ? 'up' : 'down'
            ]
        ];
    }

    /**
     * Obtener datos para gráficos
     */
    private function getChartData($searchIds, $startDate, $period)
    {
        // Timeline de tweets
        $timelineData = $this->getTimelineData($searchIds, $startDate, $period);

        return [
            'timeline' => $timelineData
        ];
    }

    /**
     * Timeline de tweets (por hora o por día según el período)
     */
    private function getTimelineData($searchIds, $startDate, $period)
    {
        $groupBy = $period === '24h' ? 'hour' : 'date';

        if ($groupBy === 'hour') {
            // Agrupar por hora
            $tweets = Tweet::whereJsonContains('matched_search_ids', $searchIds->toArray())
                ->where('created_at_twitter', '>=', $startDate)
                ->get()
                ->groupBy(function($tweet) {
                    return $tweet->created_at_twitter->format('Y-m-d H:00:00');
                })
                ->map(function($group) {
                    return $group->count();
                })
                ->sortKeys();

            // Generar todas las horas del período
            $labels = [];
            $data = [];
            for ($i = 23; $i >= 0; $i--) {
                $hour = now()->subHours($i);
                $key = $hour->format('Y-m-d H:00:00');
                $labels[] = $hour->format('H:00');
                $data[] = $tweets->get($key, 0);
            }
        } else {
            // Agrupar por día
            $days = $period === '7d' ? 7 : 30;
            $tweets = Tweet::whereJsonContains('matched_search_ids', $searchIds->toArray())
                ->where('created_at_twitter', '>=', $startDate)
                ->get()
                ->groupBy(function($tweet) {
                    return $tweet->created_at_twitter->format('Y-m-d');
                })
                ->map(function($group) {
                    return $group->count();
                })
                ->sortKeys();

            // Generar todos los días del período
            $labels = [];
            $data = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $day = now()->subDays($i);
                $key = $day->format('Y-m-d');
                $labels[] = $day->format('d/m');
                $data[] = $tweets->get($key, 0);
            }
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * Top 10 tweets con mejor score IA
     */
    private function getTopTweets($searchIds, $startDate)
    {
        return Tweet::with('account')
            ->whereJsonContains('matched_search_ids', $searchIds->toArray())
            ->where('ia_analyzed', true)
            ->where('ia_score', '>=', 70)
            ->where('created_at_twitter', '>=', $startDate)
            ->orderByDesc('ia_score')
            ->orderByDesc('created_at_twitter')
            ->limit(10)
            ->get();
    }

    /**
     * Performance por búsqueda (top 10)
     */
    private function getTopSearches($searchIds, $startDate)
    {
        return Search::whereIn('id', $searchIds)
            ->withCount(['tweets' => function($query) use ($startDate) {
                $query->where('created_at_twitter', '>=', $startDate);
            }])
            ->with(['tweets' => function($query) use ($startDate) {
                $query->where('created_at_twitter', '>=', $startDate)
                      ->where('ia_analyzed', true);
            }])
            ->get()
            ->map(function($search) {
                return [
                    'name' => $search->name ?? 'Sin nombre',
                    'tweets_count' => $search->tweets_count,
                    'avg_score' => $search->tweets->avg('ia_score') ?? 0
                ];
            })
            ->sortByDesc('tweets_count')
            ->take(10)
            ->values();
    }
}
