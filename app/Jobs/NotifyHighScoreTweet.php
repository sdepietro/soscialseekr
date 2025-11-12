<?php

namespace App\Jobs;

use App\Models\Tweet;
use App\Models\WhatsappNotificationLog;
use App\Services\WabotyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NotifyHighScoreTweet implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * El número de veces que se puede intentar el job.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * El número de segundos que el job puede ejecutarse antes de timeout.
     *
     * @var int
     */
    public $timeout = 120;

    /**
     * El tweet a evaluar para notificación
     *
     * @var Tweet
     */
    public $tweet;

    /**
     * Número específico al que enviar (opcional)
     * Si está presente, solo envía a este número
     * Si es null, procesa todos los números configurados
     *
     * @var string|null
     */
    public $specificPhone;

    /**
     * Create a new job instance.
     *
     * @param Tweet $tweet Tweet a notificar
     * @param string|null $specificPhone Número específico (opcional)
     */
    public function __construct(Tweet $tweet, ?string $specificPhone = null)
    {
        $this->tweet = $tweet;
        $this->specificPhone = $specificPhone;
    }

    /**
     * Execute the job.
     */
    public function handle(WabotyService $wabotyService): void
    {
        // Cargar relaciones necesarias
        $this->tweet->load('account');

        // Verificar que el tweet tenga cuenta asociada
        if (!$this->tweet->account) {
            Log::warning('Tweet sin cuenta asociada', [
                'tweet_id' => $this->tweet->id,
                'twitter_id' => $this->tweet->twitter_id
            ]);
            return;
        }

        // Verificar antigüedad del tweet (máximo 6 horas)
        if ($this->tweet->created_at_twitter) {
            $tweetAtUtc = $this->tweet->created_at_twitter->utc();
            $nowUtc = now('UTC');

            $hoursOld = intdiv($nowUtc->getTimestamp() - $tweetAtUtc->getTimestamp(), 3600);
            // alternativa con decimales:
            // $hoursOld = ($nowUtc->getTimestamp() - $tweetAtUtc->getTimestamp()) / 3600;

            // Debug:
            Log::warning('Tiempos (UTC):', [
                'ahora_utc' => $nowUtc->toIso8601String(),
                'tweet_utc' => $tweetAtUtc->toIso8601String(),
                'diff_horas' => $hoursOld,
            ]);

            if ($hoursOld > 6) {
                return; // descartar
            }
        }

        $score = $this->tweet->ia_score ?? 0;
        $followers = $this->tweet->account->followers ?? 0;

        // Aplicar lógica de filtrado según score y followers
        $shouldNotify = false;
        $reason = '';

        if ($score >= 60 && $followers < 10000) {
            // Umbral alto: score >= 60 Y followers < 10,000
            $shouldNotify = true;
            $reason = "Score alto ({$score}) con audiencia pequeña";
        } elseif ($score >= 40 && $followers >= 10000) {
            // Umbral bajo: score >= 40 Y followers >= 10,000
            $shouldNotify = true;
            $reason = "Score moderado ({$score}) con audiencia grande";
        }

        if (!$shouldNotify) {
            Log::debug('Tweet no cumple criterios de notificación', [
                'tweet_id' => $this->tweet->id,
                'score' => $score,
                'followers' => $followers
            ]);
            return;
        }

        // Construir mensaje de WhatsApp
        $message = $this->buildWhatsAppMessage($reason);

        // Obtener números de teléfono
        $phones = $this->specificPhone
            ? [$this->specificPhone]
            : [
                config('constants.whatsapp_notifications.phone_1'),
                config('constants.whatsapp_notifications.phone_2'),
            ];

        // Procesar cada número
        foreach ($phones as $phone) {
            if (empty($phone)) {
                Log::warning('Número de WhatsApp no configurado o vacío');
                continue;
            }

            // Verificar rate limiting
            $rateLimitCheck = WhatsappNotificationLog::canSendTo($phone);

            if (!$rateLimitCheck['can_send']) {
                $waitSeconds = $rateLimitCheck['wait_seconds'];

                Log::info('Rate limit activo - Job en espera', [
                    'tweet_id' => $this->tweet->id,
                    'phone' => $phone,
                    'wait_seconds' => $waitSeconds
                ]);

                // Crear un nuevo job con delay solo para este número
                self::dispatch($this->tweet, $phone)->delay(now()->addSeconds($waitSeconds));

                continue;
            }

            // Enviar WhatsApp
            $result = $wabotyService->sendWhatsapp($phone, $message);

            // Registrar en base de datos
            $log = WhatsappNotificationLog::create([
                'phone' => $phone,
                'tweet_id' => $this->tweet->id,
                'status' => $result->status === 'success' ? 'success' : 'error',
                'message_snippet' => Str::limit($message, 100),
                'error_message' => $result->status !== 'success' ? ($result->message ?? 'Error desconocido') : null,
                'sent_at' => $result->status === 'success' ? now() : null,
            ]);

            if ($result->status === 'success') {
                Log::info('WhatsApp enviado exitosamente', [
                    'tweet_id' => $this->tweet->id,
                    'twitter_id' => $this->tweet->twitter_id,
                    'score' => $score,
                    'followers' => $followers,
                    'phone' => $phone,
                    'reason' => $reason,
                    'log_id' => $log->id
                ]);
            } else {
                Log::error('Error al enviar WhatsApp', [
                    'tweet_id' => $this->tweet->id,
                    'phone' => $phone,
                    'error' => $result->message,
                    'log_id' => $log->id
                ]);
            }
        }
    }

    /**
     * Construir el mensaje de WhatsApp
     */
    protected function buildWhatsAppMessage(string $reason): string
    {
        $score = $this->tweet->ia_score ?? 0;
        $followers = number_format($this->tweet->account->followers ?? 0, 0, ',', '.');
        $likes = number_format($this->tweet->like_count ?? 0, 0, ',', '.');
        $replies = number_format($this->tweet->reply_count ?? 0, 0, ',', '.');
        $username = $this->tweet->account->username ?? 'N/A';
        $url = $this->tweet->url;

        $message = "*Tweet Relevante Detectado*\n\n";
        $message .= "*Motivo:* {$reason}\n";
        $message .= "*Score IA:* {$score}/100\n\n";
        $message .= "*Autor:* @{$username}\n";
        $message .= "*Followers:* {$followers}\n";
        $message .= "*Likes:* {$likes}\n";
        $message .= "*Replies:* {$replies}\n\n";
        $message .= "*Link:* {$url}";

        return $message;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('NotifyHighScoreTweet job failed', [
            'tweet_id' => $this->tweet->id,
            'specific_phone' => $this->specificPhone,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        // Registrar el fallo en la base de datos si tenemos un número específico
        if ($this->specificPhone) {
            WhatsappNotificationLog::create([
                'phone' => $this->specificPhone,
                'tweet_id' => $this->tweet->id,
                'status' => 'error',
                'error_message' => 'Job failed: ' . $exception->getMessage(),
                'sent_at' => null,
            ]);
        }
    }
}
