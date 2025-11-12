<?php

namespace App\Console\Commands;

use App\Jobs\NotifyHighScoreTweet;
use App\Models\Search;
use App\Models\Tweet;
use App\Services\ChatGptService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AnalyzeTweetsWithAI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tweets:analyze-ai';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyze pending tweets with AI and dispatch notifications';

    protected ChatGptService $chatGptService;

    /**
     * Create a new command instance.
     */
    public function __construct(ChatGptService $chatGptService)
    {
        parent::__construct();
        $this->chatGptService = $chatGptService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Obtener tweets pendientes de análisis, agrupados por search_id
        $tweetsToAnalyze = Tweet::where('ia_analyzed', false)
            ->whereNotNull('search_id')
            ->with('search')
            ->get();

        $this->info('🤖 Iniciando análisis IA de tweets...');

        Log::info('CronIaAnalyzer: Iniciando análisis de tweets', [
            'tweets_pending' => $tweetsToAnalyze->count()
        ]);

        if ($tweetsToAnalyze->isEmpty()) {
            $this->info('✓ No hay tweets pendientes de analizar');
            return self::SUCCESS;
        }

        $this->info("📊 Tweets pendientes de análisis: {$tweetsToAnalyze->count()}");

        // Agrupar tweets por search_id
        $tweetsBySearch = $tweetsToAnalyze->groupBy('search_id');

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
                $this->warn("  ⚠️  Búsqueda ID {$searchId} no encontrada ({$tweets->count()} tweets)");
                continue;
            }

            // Verificar que la búsqueda tenga un prompt configurado
            if (empty($search->ia_prompt)) {
                Log::warning('CronIaAnalyzer: Búsqueda sin prompt configurado', [
                    'search_id' => $searchId,
                    'search_name' => $search->name
                ]);
                $this->warn("  ⚠️  Búsqueda '{$search->name}' sin prompt IA configurado");
                continue;
            }

            // Limitar a 20 tweets por búsqueda por ejecución
            $tweetsToProcess = $tweets->take(20);

            $this->line("  → Procesando '{$search->name}': {$tweetsToProcess->count()} tweets");

            Log::info('CronIaAnalyzer: Procesando tweets para búsqueda', [
                'search_id' => $searchId,
                'search_name' => $search->name,
                'tweets_count' => $tweetsToProcess->count()
            ]);

            // Evaluar tweets usando el prompt personalizado de la búsqueda
            $results = $this->chatGptService->evaluateTweets($tweetsToProcess, $search->ia_prompt, $search->user->company);

            Log::info('CronIaAnalyzer: Resultados recibidos de ChatGPT', [
                'search_id' => $searchId,
                'results_count' => count($results)
            ]);

            // Actualizar tweets con los resultados
            $tweetsUpdatedInSearch = 0;
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
                    $tweetsUpdatedInSearch++;

                    // Disparar job para notificación de WhatsApp si cumple criterios
                    NotifyHighScoreTweet::dispatch($tweet);
                }
            }

            $this->info("    ✓ Analizados: {$tweetsUpdatedInSearch} tweets");
            $searchesProcessed++;
        }

        $this->newLine();
        $this->info("✅ Análisis completado: {$searchesProcessed} búsquedas procesadas, {$totalUpdated} tweets analizados");

        Log::info('CronIaAnalyzer: Análisis completado', [
            'searches_processed' => $searchesProcessed,
            'tweets_updated' => $totalUpdated
        ]);

        return self::SUCCESS;
    }
}
