<?php

namespace App\Http\Controllers\Test;

use App\Http\Controllers\Controller;
use App\Models\BlackSpotType;
use App\Models\Tweet;
use App\Models\Account;
use App\Models\Search;
use App\Services\TwitterService;
use App\Jobs\NotifyHighScoreTweet;
use Illuminate\Http\Request;
use App\Traits\UploadImageTrait;

class TwitterController extends Controller
{
    use UploadImageTrait;


    public function index(Request $request)
    {


            $twitterService = new TwitterService();

            // Construimos la query avanzada (basada en operadores del endpoint)
            // Referencia: https://docs.twitterapi.io/api-reference/endpoint/tweet_advanced_search
            $query = 'min_replies:1 (receta OR medico OR clinica) geocode:-34.619340,-58.494032,50km since:2025-10-28 -filter:replies'; // ejemplo de búsqueda avanzada

            // Ejecutamos la búsqueda
            $tweets = $twitterService->search($query, 'Latest');

            // Si la API respondió correctamente
            if ($tweets && isset($tweets['tweets'])) {
                // Ordenar por fecha de creación descendente si la API no lo hace automáticamente
                usort($tweets['tweets'], function ($a, $b) {
                    return strtotime($b['createdAt']) <=> strtotime($a['createdAt']);
                });

                return response()->json([
                    'success' => true,
                    'query' => $query,
                    'count' => count($tweets['tweets']),
                    'tweets' => $tweets['tweets'],
                ]);
            }

            // En caso de error o sin resultados
            return response()->json([
                'success' => false,
                'message' => 'No se pudieron obtener tweets o no hay resultados.',
                'data' => $tweets,
            ], 400);


    }


    public function evaluateTweets(Request $request)
    {
        $tweets = Tweet::where('ia_analyzed', false)
            ->limit(20)
            ->get();

        $chatGptService = app()->make(\App\Services\ChatGptService::class);
        $results = $chatGptService->evaluateTweets($tweets);


        foreach ($results as $res) {
            // Buscar el tweet correspondiente
            $tweet = Tweet::where('id', $res['id'])->first();
            if ($tweet) {
                // Actualizar el tweet con la puntuación y razón
                $tweet->ia_analyzed = 1;
                $tweet->ia_score = $res['score'];
                $tweet->ia_reason = $res['reason'];
                $tweet->save();
            }
        }

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);

    }


    /**
     * Crea un tweet fake con análisis IA fake y dispara la notificación de WhatsApp
     * Endpoint de testing para probar el flujo completo de notificaciones
     */
    public function createFakeTweetForNotification(Request $request)
    {
        try {
            // 1. Obtener una búsqueda activa existente (o la primera disponible)
            $search = Search::where('active', true)->first();

            if (!$search) {
                // Si no hay búsquedas activas, intentar con cualquier búsqueda
                $search = Search::first();
            }

            if (!$search) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay búsquedas disponibles en la base de datos. Crea al menos una búsqueda primero.',
                ], 400);
            }

            // 2. Crear un Account fake con audiencia pequeña (< 10,000 followers)
            $timestamp = time();
            $account = Account::create([
                'twitter_id' => 'fake_' . $timestamp,
                'username' => 'test_user_' . $timestamp,
                'name' => 'Usuario de Prueba WhatsApp',
                'followers' => 5000, // Audiencia pequeña para cumplir criterio
                'following' => 200,
                'description' => 'Cuenta de prueba generada automáticamente para testing de notificaciones',
                'url' => 'https://x.com/test_user_' . $timestamp,
                'profile_picture' => 'https://abs.twimg.com/sticky/default_profile_images/default_profile_normal.png',
                'verified_type' => 'none',
                'is_blue_verified' => false,
                'can_dm' => true,
                'statuses_count' => 100,
                'favourites_count' => 50,
                'created_at_twitter' => now()->subYears(2), // Cuenta "antigua"
            ]);

            // 3. Crear un Tweet fake con análisis IA fake completado
            $tweetTimestamp = time();
            $tweet = Tweet::create([
                'twitter_id' => 'fake_tweet_' . $tweetTimestamp,
                'account_id' => $account->id,
                'search_id' => $search->id,
                'text' => '🏥 Busco recomendación de médico cardiólogo en CABA que atienda por obra social. Agradezco referencias y experiencias. #SaludArgentina #MedicoCARDIOLOGO',
                'created_at_twitter' => now(), // Fecha actual para no ser rechazado por antigüedad
                'like_count' => 50,
                'reply_count' => 10,
                'retweet_count' => 5,
                'quote_count' => 2,
                'view_count' => 1200,
                'bookmark_count' => 8,
                'is_reply' => false,
                'lang' => 'es',

                // Análisis IA fake - ya completado con score alto
                'ia_analyzed' => true,
                'ia_score' => 80, // Score alto que dispara notificación con audiencia pequeña
                'ia_reason' => 'Testing: Tweet generado automáticamente para pruebas de notificación WhatsApp. Score alto (80) + audiencia pequeña (5,000 followers) cumple criterios de notificación.',

                // Metadata adicional
                'matched_search_ids' => [$search->id],
            ]);

            // 4. Disparar el Job de notificación inmediatamente
            NotifyHighScoreTweet::dispatch($tweet);

            // 5. Retornar respuesta con información del tweet creado
            return response()->json([
                'success' => true,
                'message' => 'Tweet fake creado exitosamente. El job de notificación fue despachado a la cola.',
                'data' => [
                    'tweet' => [
                        'id' => $tweet->id,
                        'twitter_id' => $tweet->twitter_id,
                        'text' => $tweet->text,
                        'url' => $tweet->url,
                        'created_at_twitter' => $tweet->created_at_twitter->format('Y-m-d H:i:s'),
                        'like_count' => $tweet->like_count,
                        'reply_count' => $tweet->reply_count,
                        'ia_score' => $tweet->ia_score,
                        'ia_reason' => $tweet->ia_reason,
                    ],
                    'account' => [
                        'id' => $account->id,
                        'username' => $account->username,
                        'name' => $account->name,
                        'followers' => $account->followers,
                    ],
                    'search' => [
                        'id' => $search->id,
                        'name' => $search->name ?? 'Sin nombre',
                        'query' => $search->query,
                    ],
                    'notification' => [
                        'status' => 'Job despachado a la cola',
                        'expected_time' => 'Menos de 1 minuto (si el worker está corriendo)',
                        'criteria_met' => 'Score 80 + followers 5,000 (score alto + audiencia pequeña)',
                    ],
                ],
                'next_steps' => [
                    '1. Verifica que el worker de cola esté corriendo: composer run dev',
                    '2. Revisa los logs con: php artisan pail --timeout=0',
                    '3. Consulta la tabla whatsapp_notification_logs para ver el registro de envío',
                ],
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al crear tweet fake para notificación', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el tweet fake: ' . $e->getMessage(),
            ], 500);
        }
    }


}
