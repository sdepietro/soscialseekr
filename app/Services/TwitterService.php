<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwitterService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('constants.twitterapiio.base_url', 'https://api.twitterapi.io/twitter/tweet/advanced_search');
        $this->apiKey = config('constants.twitterapiio.api_key');
    }

    /**
     * Ejecuta una búsqueda de tweets según la query dada.
     *
     * @param string $query Query avanzada de búsqueda (operadores permitidos).
     * @param string|null $queryType Tipo de búsqueda (“Latest” o “Top”). Opcional.
     * @param string|null $cursor Cursor para paginación. Opcional.
     * @return array|null Retorna el JSON decodificado de la respuesta o null en caso de error.
     */
    public function search(string $query, string $queryType = null, string $cursor = null): ?array
    {

        $params = [
            'query' => $query,
        ];

        if ($queryType !== null) {
            $params['queryType'] = $queryType;
        }

        if ($cursor !== null) {
            $params['cursor'] = $cursor;
        }

        $response = Http::withHeaders([
            'X-API-Key' => $this->apiKey,
        ])->get($this->baseUrl . 'tweet/advanced_search', $params);

        if ($response->successful()) {
            //imprimimos en pantalla un json con la respuesta

            $data = $response->json();
            $data['tweets'] = array_slice($data['tweets'] ?? [], 0, 2);

            // (Opcional) ajustar paginación porque ya no seguimos
            $data['has_next_page'] = false;
            unset($data['next_cursor']);

            return $response->json();
        } else {
            Log::error('TwitterService.search: respuesta no exitosa', [
                'status' => $response->status(),
                'body' => $response->body(),
                'query' => $query,
                'params' => $params,
            ]);
            return null;
        }

    }
}
