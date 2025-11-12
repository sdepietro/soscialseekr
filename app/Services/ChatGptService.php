<?php

namespace App\Services;

use App\Http\Services\EpisodesService;
use App\Models\Answer;
use App\Models\Episodes;
use App\Models\Question;
use OpenAI;
use Exception;
use function App\Helpers\wGetConfigs;

class ChatGptService
{
    protected $client;

    public function __construct()
    {
        $this->client = OpenAI::client(config('constants.openai.api_key'));

    }


    public function evaluateTweets($tweets, $customPrompt, $company): array
    {
        // 1) System prompt (criterios de evaluación)
        // Si se proporciona un prompt personalizado, usarlo; sino usar el predeterminado
        $system_prompt = "Eres un analista de redes sociales para una empresa del area de ".$company->industry.". El country code de la empresa es: ".$company->country.".
Debes puntuar cada tweet de 0 a 100 según su relevancia para el negocio, Usando la siguiente consigna:".$customPrompt."
Devuelve un array JSON estricto con el siguiente formato:
[{\"id\":\"<tweet_id>\",\"score\":87,\"reason\":\"...\"}]
Tweets a evaluar (máximo 20):";
        // 2) Normalizamos y limitamos a 20 tweets
        $rows = [];
        $counter = 0;
        foreach ($tweets as $t) {
            if ($counter >= 20) break;

            // Aseguramos campos mínimos
            // Soportar tanto arrays como modelos Eloquent
            $id = is_array($t)
                ? (string)($t['id'] ?? $t['twitter_id'] ?? '')
                : (string)($t->id ?? $t->twitter_id ?? '');

            $date = is_array($t)
                ? (string)($t['createdAt'] ?? $t['created_at_twitter'] ?? $t['created_at'] ?? '')
                : (string)($t->created_at_twitter ?? $t->created_at ?? '');

            $likes = is_array($t)
                ? (int)($t['likeCount'] ?? $t['like_count'] ?? 0)
                : (int)($t->like_count ?? 0);

            $text = is_array($t)
                ? (string)($t['text'] ?? '')
                : (string)($t->text ?? '');

            // Skip si no hay id o texto
            if ($id === '' || $text === '') continue;

            // Línea en formato requerido
            $rows[] = $id . '|' . $date . '|' . $likes . '|' . str_replace(["\n", "\r"], ' ', $text);
            $counter++;
        }

        // Si no hay nada para evaluar, devolvemos vacío
        if (empty($rows)) {
            \Log::warning('ChatGptService: No se pudieron extraer tweets válidos para evaluar', [
                'total_tweets' => count($tweets),
                'tweets_type' => get_class($tweets->first() ?? 'unknown')
            ]);
            return [];
        }

        \Log::info('ChatGptService: Evaluando tweets', [
            'count' => count($rows),
            'sample' => $rows[0] ?? null,
            'custom_prompt' => $customPrompt !== null
        ]);

        // 3) Mensaje de usuario con el bloque de tweets
        $user_prompt = $system_prompt . "\n" . implode("\n", $rows);

        // 4) Construimos mensajes para Chat Completions
        $messages = [
            ['role' => 'system', 'content' => $system_prompt],
            ['role' => 'user', 'content' => implode("\n", $rows)], // los tweets van como contenido del user
        ];

        // 5) Llamada al modelo (con fallbacks por si no tenés helpers)
        $model = function_exists('wGetConfigs') ? wGetConfigs('chatgpt-version', 'gpt-4o-mini') : 'gpt-4o-mini';
        // Aumentamos max_tokens para permitir respuestas completas de 20 tweets
        // Cada tweet puede generar ~100 tokens, así que 2000 tokens es seguro para 20 tweets
        $maxTokens = function_exists('wGetConfigs') ? (int)wGetConfigs('max-tokens', 2000) : 2000;

        try {
            $apiResponse = $this->client->chat()->create([
                'model' => $model,
                'messages' => $messages,
                'temperature' => 0.2,
                'max_tokens' => $maxTokens,
            ]);

            $content = $apiResponse->choices[0]->message->content ?? '';

            // 6) Limpieza: si vino en ```json ... ``` (puede estar truncado)
            $content = trim($content);

            // Intentar extraer JSON de bloques markdown (incluso si están incompletos)
            if (preg_match('/```(?:json)?\s*(.*?)(?:```|$)/is', $content, $m)) {
                $content = trim($m[1]);
            }

            // Si el JSON está truncado, intentar "cerrarlo" agregando ] al final
            $endsWithBracket = substr($content, -1) === ']';
            $startsWithBracket = substr($content, 0, 1) === '[';

            if (!$endsWithBracket && $startsWithBracket) {
                // Remover la última entrada incompleta si existe
                $lastBrace = strrpos($content, '{');
                $lastCompleteBrace = strrpos($content, '}');

                // Si hay un { sin cerrar al final, lo removemos
                if ($lastBrace !== false && ($lastCompleteBrace === false || $lastBrace > $lastCompleteBrace)) {
                    $content = substr($content, 0, $lastBrace);
                    // Limpiar la coma final si existe
                    $content = rtrim($content, ", \n\r\t");
                }

                $content .= ']';

                \Log::warning('ChatGptService: JSON truncado detectado, intentando reparar', [
                    'original_length' => strlen($apiResponse->choices[0]->message->content ?? ''),
                    'repaired' => true
                ]);
            }

            // 7) Decodificar JSON (intentar extraer si viene con texto alrededor)
            $decoded = json_decode($content, true);
            if (!is_array($decoded)) {
                // Fallback: intentar extraer el primer array JSON
                if (preg_match('/\[[\s\S]*\{[\s\S]*\}[\s\S]*\]/s', $content, $m2)) {
                    $decoded = json_decode($m2[0], true);
                }
            }

            // 8) Validación final
            if (!is_array($decoded)) {
                // Devolvemos vacío si no pudimos parsear
                \Log::error('ChatGptService: No se pudo parsear la respuesta de OpenAI', [
                    'raw_content' => $content,
                    'content_length' => strlen($content)
                ]);
                return [];
            }

            // Opcional: normalizar estructura (id/score/reason)
            $out = [];
            foreach ($decoded as $row) {
                if (!is_array($row)) continue;
                $out[] = [
                    'id' => (string)($row['id'] ?? ''),
                    'score' => (int)($row['score'] ?? 0),
                    'reason' => (string)($row['reason'] ?? ''),
                ];
            }

            \Log::info('ChatGptService: Tweets evaluados exitosamente', [
                'results_count' => count($out)
            ]);

            return $out;
        } catch (\Throwable $e) {
            \Log::error('ChatGptService: Error al evaluar tweets con OpenAI', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }
}

