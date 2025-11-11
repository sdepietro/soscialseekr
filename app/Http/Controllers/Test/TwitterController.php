<?php

namespace App\Http\Controllers\Test;

use App\Http\Controllers\Controller;
use App\Models\BlackSpotType;
use App\Models\Tweet;
use App\Services\TwitterService;
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



}
