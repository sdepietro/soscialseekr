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

class CronSearchesController extends Controller
{
    /**
     * Ejecuta las búsquedas configuradas, persiste cuentas y tweets, genera historial y detecta spikes.
     * Este cron debe ejecutarse cada 1 minuto.
     */
    public function run(Request $request)
    {
        $now = Carbon::now();
        $twitterService = new TwitterService();

        // Cargar búsquedas activas
        $searches = Search::active()->get();

        if ($searches->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No hay búsquedas activas configuradas.',
                'searches_processed' => 0,
            ]);
        }

        $resultsPerSearch = [];
        $totalSearchesExecuted = 0;

        foreach ($searches as $search) {
            // Verificar si debe ejecutarse esta búsqueda
            if (!$this->shouldRunSearch($search, $now)) {
                continue;
            }

            try {
                $result = $this->executeSearch($search, $twitterService, $now);
                $resultsPerSearch[] = $result;
                $totalSearchesExecuted++;
            } catch (\Throwable $e) {
                Log::error('Error ejecutando búsqueda', [
                    'search_id' => $search->id,
                    'query' => $search->query,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                $resultsPerSearch[] = [
                    'search_id' => $search->id,
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Procesamiento de búsquedas completado',
            'searches_executed' => $totalSearchesExecuted,
            'total_active_searches' => $searches->count(),
            'results' => $resultsPerSearch,
        ]);
    }

    /**
     * Verifica si una búsqueda debe ejecutarse basándose en run_every_minutes y last_run_at
     */
    protected function shouldRunSearch(Search $search, Carbon $now): bool
    {
        // Si nunca se ha ejecutado, debe ejecutarse
        if (!$search->last_run_at) {
            return true;
        }

        // Calcular minutos transcurridos desde la última ejecución
        $minutesSinceLastRun = $search->last_run_at->diffInMinutes($now);

        // Ejecutar si han pasado suficientes minutos
        return $minutesSinceLastRun >= ($search->run_every_minutes ?? 1);
    }

    /**
     * Ejecuta una búsqueda individual y procesa sus resultados
     */
    protected function executeSearch(Search $search, TwitterService $twitterService, Carbon $now): array
    {
        // Ejecutar la búsqueda con los parámetros configurados
        $query = $search->query;
        $queryType = $search->query_type ?? 'Latest';

        $resp = $twitterService->search($query, $queryType);

        // Si la API respondió correctamente
        if ($resp && isset($resp['tweets']) && is_array($resp['tweets']) && count($resp['tweets']) > 0) {
            // Ordenar por fecha de creación descendente si la API no lo hace automáticamente
            usort($resp['tweets'], function ($a, $b) {
                return strtotime($b['createdAt'] ?? 'now') <=> strtotime($a['createdAt'] ?? 'now');
            });

            $savedAccounts = 0;
            $savedTweets = 0;
            $updatedTweets = 0;
            $notified = [];
            $processedTweetModels = [];

            DB::beginTransaction();
            try {
                foreach ($resp['tweets'] as $t) {
                    // 1) Upsert Account
                    $author = $t['author'] ?? null;
                    $accountModel = null;
                    if ($author && isset($author['id'])) {
                        $accountPayload = [
                            'twitter_id' => (string)($author['id'] ?? null),
                            'username' => $author['userName'] ?? null,
                            'name' => $author['name'] ?? null,
                            'url' => $author['url'] ?? null,
                            'is_blue_verified' => (bool)($author['isBlueVerified'] ?? false),
                            'verified_type' => $author['verifiedType'] ?? null,
                            'profile_picture' => $author['profilePicture'] ?? null,
                            'cover_picture' => $author['coverPicture'] ?? null,
                            'description' => $author['description'] ?? ($author['profile_bio']['description'] ?? null),
                            'location' => $author['location'] ?? null,
                            'followers' => $author['followers'] ?? null,
                            'following' => $author['following'] ?? null,
                            'can_dm' => (bool)($author['canDm'] ?? false),
                            'created_at_twitter' => isset($author['createdAt']) ? Carbon::parse($author['createdAt']) : null,
                            'favourites_count' => $author['favouritesCount'] ?? null,
                            'has_custom_timelines' => (bool)($author['hasCustomTimelines'] ?? false),
                            'is_translator' => (bool)($author['isTranslator'] ?? false),
                            'media_count' => $author['mediaCount'] ?? null,
                            'statuses_count' => $author['statusesCount'] ?? null,
                            'withheld_in_countries' => $author['withheldInCountries'] ?? null,
                            'affiliates_highlighted_label' => $author['affiliatesHighlightedLabel'] ?? null,
                            'possibly_sensitive' => (bool)($author['possiblySensitive'] ?? false),
                            'pinned_tweet_ids' => $author['pinnedTweetIds'] ?? null,
                            'is_automated' => (bool)($author['isAutomated'] ?? false),
                            'automated_by' => $author['automatedBy'] ?? null,
                            'profile_bio_description' => $author['profile_bio']['description'] ?? null,
                            'profile_bio_entities' => $author['profile_bio']['entities'] ?? null,
                            'raw_payload' => $author,
                        ];

                        $accountModel = Account::updateOrCreate(
                            ['twitter_id' => $accountPayload['twitter_id']],
                            $accountPayload
                        );
                        $savedAccounts++;
                    }

                    // 2) Upsert Tweet + history
                    if (!isset($t['id'])) {
                        continue;
                    }

                    $existing = Tweet::where('twitter_id', (string)$t['id'])->first();

                    $tweetPayload = [
                        'account_id' => $accountModel?->id,
                        'twitter_id' => (string)$t['id'],
                        'url' => $t['url'] ?? ($t['twitterUrl'] ?? null),
                        'text' => $t['text'] ?? null,
                        'source' => $t['source'] ?? null,
                        'lang' => $t['lang'] ?? null,
                        'retweet_count' => $t['retweetCount'] ?? 0,
                        'reply_count' => $t['replyCount'] ?? 0,
                        'like_count' => $t['likeCount'] ?? 0,
                        'quote_count' => $t['quoteCount'] ?? 0,
                        'view_count' => $t['viewCount'] ?? 0,
                        'bookmark_count' => $t['bookmarkCount'] ?? 0,
                        'is_reply' => (bool)($t['isReply'] ?? false),
                        'in_reply_to_id' => $t['inReplyToId'] ?? null,
                        'conversation_id' => $t['conversationId'] ?? null,
                        'display_text_range' => $t['displayTextRange'] ?? null,
                        'in_reply_to_user_id' => $t['inReplyToUserId'] ?? null,
                        'in_reply_to_username' => $t['inReplyToUsername'] ?? null,
                        'entities' => $t['entities'] ?? null,
                        'quoted_tweet' => $t['quoted_tweet'] ?? null,
                        'retweeted_tweet' => $t['retweeted_tweet'] ?? null,
                        'is_limited_reply' => (bool)($t['isLimitedReply'] ?? false),
                        'created_at_twitter' => isset($t['createdAt']) ? Carbon::parse($t['createdAt']) : null,
                        'raw_payload' => $t,
                    ];

                    if ($existing) {
                        $previousSnapshot = [
                            'retweet_count' => (int)$existing->retweet_count,
                            'reply_count' => (int)$existing->reply_count,
                            'like_count' => (int)$existing->like_count,
                            'quote_count' => (int)$existing->quote_count,
                            'view_count' => (int)$existing->view_count,
                            'bookmark_count' => (int)$existing->bookmark_count,
                        ];

                        $existing->fill($tweetPayload);

                        // Agregar el search_id actual al array matched_search_ids si no existe
                        $matchedSearchIds = $existing->matched_search_ids ?? [];
                        if (!in_array($search->id, $matchedSearchIds)) {
                            $matchedSearchIds[] = $search->id;
                            $existing->matched_search_ids = $matchedSearchIds;
                        }

                        $existing->save();
                        $tweetModel = $existing;
                        $updatedTweets++;
                    } else {
                        // Para tweets nuevos, establecer search_id y matched_search_ids
                        $tweetPayload['search_id'] = $search->id;
                        $tweetPayload['matched_search_ids'] = [$search->id];

                        $tweetModel = Tweet::create($tweetPayload);
                        $previousSnapshot = null;
                        $savedTweets++;
                    }

                    $newSnapshot = [
                        'retweet_count' => (int)$tweetModel->retweet_count,
                        'reply_count' => (int)$tweetModel->reply_count,
                        'like_count' => (int)$tweetModel->like_count,
                        'quote_count' => (int)$tweetModel->quote_count,
                        'view_count' => (int)$tweetModel->view_count,
                        'bookmark_count' => (int)$tweetModel->bookmark_count,
                    ];

                    $diff = null;
                    if ($previousSnapshot !== null) {
                        $diff = [
                            'retweet_count' => $newSnapshot['retweet_count'] - $previousSnapshot['retweet_count'],
                            'reply_count' => $newSnapshot['reply_count'] - $previousSnapshot['reply_count'],
                            'like_count' => $newSnapshot['like_count'] - $previousSnapshot['like_count'],
                            'quote_count' => $newSnapshot['quote_count'] - $previousSnapshot['quote_count'],
                            'view_count' => $newSnapshot['view_count'] - $previousSnapshot['view_count'],
                            'bookmark_count' => $newSnapshot['bookmark_count'] - $previousSnapshot['bookmark_count'],
                        ];
                    }

                    TweetHistory::create([
                        'tweet_id' => $tweetModel->id,
                        'reason' => 'metrics_update',
                        'retweet_count' => $newSnapshot['retweet_count'],
                        'reply_count' => $newSnapshot['reply_count'],
                        'like_count' => $newSnapshot['like_count'],
                        'quote_count' => $newSnapshot['quote_count'],
                        'view_count' => $newSnapshot['view_count'],
                        'bookmark_count' => $newSnapshot['bookmark_count'],
                        'previous_snapshot' => $previousSnapshot,
                        'new_snapshot' => $newSnapshot,
                        'diff' => $diff,
                        'changed_at' => $now,
                    ]);

                    $processedTweetModels[] = $tweetModel;
                }

                // 3) Analizar spikes en la última hora y 4) llamar a notificate()
                $thresholdLikes = 20;   // regla simple: +20 likes en 1h
                $thresholdReplies = 10; // o +10 respuestas en 1h

                foreach ($processedTweetModels as $tm) {
                    $since = Carbon::now()->subHour();
                    $histories = TweetHistory::where('tweet_id', $tm->id)
                        ->where('changed_at', '>=', $since)
                        ->orderBy('changed_at', 'asc')
                        ->get(['like_count', 'reply_count', 'changed_at']);

                    if ($histories->count() >= 2) {
                        $first = $histories->first();
                        $last = $histories->last();
                        $deltaLikes = ($last->like_count ?? 0) - ($first->like_count ?? 0);
                        $deltaReplies = ($last->reply_count ?? 0) - ($first->reply_count ?? 0);
                        if ($deltaLikes >= $thresholdLikes || $deltaReplies >= $thresholdReplies) {
                            $this->notificate($tm);
                            $notified[] = $tm->twitter_id;
                        }
                    }
                }

                // Actualizar last_run_at de la búsqueda
                $search->last_run_at = $now;
                $search->save();

                DB::commit();


                //Ahora analizamos los tweets procesados con IA


                return [
                    'search_id' => $search->id,
                    'search_query' => $search->query,
                    'success' => true,
                    'message' => 'Procesamiento completado',
                    'stats' => [
                        'accounts_saved_or_updated' => $savedAccounts,
                        'tweets_created' => $savedTweets,
                        'tweets_updated' => $updatedTweets,
                        'notified_count' => count($notified),
                        'notified_tweets' => $notified,
                    ],
                ];
            } catch (\Throwable $e) {
                DB::rollBack();

                // No actualizar last_run_at si hubo error
                Log::error('Error procesando tweets de búsqueda', [
                    'search_id' => $search->id,
                    'error' => $e->getMessage(),
                ]);

                throw $e; // Re-lanzar para que sea capturado por el catch del método run
            }
        }

        // En caso de error o sin resultados
        return [
            'search_id' => $search->id,
            'search_query' => $search->query,
            'success' => false,
            'message' => 'No se pudieron obtener tweets o no hay resultados.',
            'tweets_count' => 0,
        ];
    }

    /**
     * Método interno para notificar spikes. Por ahora no hace nada.
     */
    protected function notificate(Tweet $tweet): void
    {
        // Implementación vacía. El usuario luego completará.
    }
}
