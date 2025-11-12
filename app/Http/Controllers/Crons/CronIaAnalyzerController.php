<?php

namespace App\Http\Controllers\Crons;

use App\Http\Controllers\Controller;
use App\Jobs\NotifyHighScoreTweet;
use App\Models\Account;
use App\Models\Search;
use App\Models\Tweet;
use App\Models\TweetHistory;
use App\Services\TwitterService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CronIaAnalyzerController extends Controller
{
    /**
     * Ejecuta el análisis IA de tweets pendientes.
     * Agrupa tweets por búsqueda y usa el prompt personalizado de cada una.
     * Este cron debe ejecutarse cada 1 minuto.
     */
    public function run(Request $request)
    {
        // Obtener tweets pendientes de análisis, agrupados por search_id
        $tweetsToAnalyze = Tweet::where('ia_analyzed', false)
            ->whereNotNull('search_id')
            ->with('search')
            ->get();

        Log::info('CronIaAnalyzer: Iniciando análisis de tweets', [
            'tweets_pending' => $tweetsToAnalyze->count()
        ]);

        if ($tweetsToAnalyze->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No hay tweets pendientes de analizar',
                'tweets_analyzed' => 0
            ]);
        }

        // Agrupar tweets por search_id
        $tweetsBySearch = $tweetsToAnalyze->groupBy('search_id');

        $chatGptService = app()->make(\App\Services\ChatGptService::class);
        $totalUpdated = 0;
        $searchesProcessed = 0;

        // Procesar cada grupo de tweets por búsqueda
        foreach ($tweetsBySearch as $searchId => $tweets) {
            // Obtener la búsqueda asociada
            $search = Search::find($searchId);

            if (!$search) {
                Log::warning('CronIaAnalyzer: Búsqueda no encontrada', [
                    'search_id' => $searchId,
                    'tweets_count' => $tweets->count()
                ]);
                continue;
            }

            // Verificar que la búsqueda tenga un prompt configurado
            if (empty($search->ia_prompt)) {
                Log::warning('CronIaAnalyzer: Búsqueda sin prompt configurado', [
                    'search_id' => $searchId,
                    'search_name' => $search->name
                ]);
                continue;
            }

            // Limitar a 20 tweets por búsqueda por ejecución
            $tweetsToProcess = $tweets->take(20);

            Log::info('CronIaAnalyzer: Procesando tweets para búsqueda', [
                'search_id' => $searchId,
                'search_name' => $search->name,
                'tweets_count' => $tweetsToProcess->count()
            ]);

            // Evaluar tweets usando el prompt personalizado de la búsqueda
            $results = $chatGptService->evaluateTweets($tweetsToProcess, $search->ia_prompt,$search->user->company);

            Log::info('CronIaAnalyzer: Resultados recibidos de ChatGPT', [
                'search_id' => $searchId,
                'results_count' => count($results)
            ]);

            // Actualizar tweets con los resultados
            foreach ($results as $res) {
                // Buscar el tweet correspondiente
                $tweet = Tweet::where('id', $res['id'])->first();
                if ($tweet) {
                    // Actualizar el tweet con la puntuación y razón
                    $tweet->ia_analyzed = 1;
                    $tweet->ia_score = $res['score'];
                    $tweet->ia_reason = $res['reason'];
                    $tweet->save();
                    $totalUpdated++;

                    // Disparar job para notificación de WhatsApp si cumple criterios
                    NotifyHighScoreTweet::dispatch($tweet);
                }
            }

            $searchesProcessed++;
        }

        Log::info('CronIaAnalyzer: Análisis completado', [
            'searches_processed' => $searchesProcessed,
            'tweets_updated' => $totalUpdated
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Análisis completado',
            'searches_processed' => $searchesProcessed,
            'tweets_analyzed' => $totalUpdated
        ]);
    }
}
