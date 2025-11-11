<?php

namespace App\Http\Controllers\Crons;

use App\Http\Controllers\Controller;
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
     * Ejecuta las búsquedas configuradas, persiste cuentas y tweets, genera historial y detecta spikes.
     * Este cron debe ejecutarse cada 1 minuto.
     */
    public function run(Request $request)
    {
        $tweets = Tweet::where('ia_analyzed', false)
            ->limit(20)
            ->get();

        Log::info('CronIaAnalyzer: Iniciando análisis de tweets', [
            'tweets_pending' => $tweets->count()
        ]);

        if ($tweets->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No hay tweets pendientes de analizar',
                'tweets_analyzed' => 0
            ]);
        }

        $chatGptService = app()->make(\App\Services\ChatGptService::class);
        $results = $chatGptService->evaluateTweets($tweets);

        Log::info('CronIaAnalyzer: Resultados recibidos de ChatGPT', [
            'results_count' => count($results)
        ]);

        $updated = 0;
        foreach ($results as $res) {
            // Buscar el tweet correspondiente
            $tweet = Tweet::where('id', $res['id'])->first();
            if ($tweet) {
                // Actualizar el tweet con la puntuación y razón
                $tweet->ia_analyzed = 1;
                $tweet->ia_score = $res['score'];
                $tweet->ia_reason = $res['reason'];
                $tweet->save();
                $updated++;
            }
        }

        Log::info('CronIaAnalyzer: Análisis completado', [
            'tweets_updated' => $updated
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Análisis completado',
            'tweets_processed' => $tweets->count(),
            'tweets_updated' => $updated
        ]);
    }
}
