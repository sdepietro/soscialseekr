<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappNotificationLog extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_notification_logs';

    protected $fillable = [
        'phone',
        'tweet_id',
        'status',
        'message_snippet',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    /**
     * Relación con el tweet notificado
     */
    public function tweet(): BelongsTo
    {
        return $this->belongsTo(Tweet::class, 'tweet_id');
    }

    /**
     * Scope: Filtrar por número de teléfono
     */
    public function scopeForPhone($query, string $phone)
    {
        return $query->where('phone', $phone);
    }

    /**
     * Scope: Envíos exitosos únicamente
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope: Envíos recientes (últimos X minutos)
     */
    public function scopeRecent($query, int $minutes = 2)
    {
        return $query->where('sent_at', '>=', now()->subMinutes($minutes));
    }

    /**
     * Scope: Envíos fallidos
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'error');
    }

    /**
     * Obtener el último envío exitoso para un número específico
     */
    public static function getLastSuccessfulSent(string $phone): ?self
    {
        return self::forPhone($phone)
            ->successful()
            ->whereNotNull('sent_at')
            ->orderBy('sent_at', 'desc')
            ->first();
    }

    /**
     * Verificar si se puede enviar a un número (respetando rate limit de 2 minutos)
     *
     * @param string $phone Número de teléfono a verificar
     * @return array ['can_send' => bool, 'wait_seconds' => int|null]
     */
    public static function canSendTo(string $phone): array
    {
        $lastSent = self::getLastSuccessfulSent($phone);

        if (!$lastSent || !$lastSent->sent_at) {
            return ['can_send' => true, 'wait_seconds' => null];
        }

        // Usar false como segundo parámetro para obtener valores con signo
        // (negativos si sent_at está en el futuro)
        $secondsSinceLastSent = now()->diffInSeconds($lastSent->sent_at, false);
        $minWaitSeconds = 120; // 2 minutos

        // Debug logging para diagnosticar problemas
        \Log::debug('Rate limit check', [
            'phone' => $phone,
            'last_sent_at' => $lastSent->sent_at->toIso8601String(),
            'now' => now()->toIso8601String(),
            'seconds_since_last_sent' => $secondsSinceLastSent,
        ]);

        // Si sent_at está en el futuro (segundos negativos), permitir envío inmediato
        if ($secondsSinceLastSent < 0) {
            \Log::warning('sent_at en el futuro detectado - permitiendo envío inmediato', [
                'phone' => $phone,
                'sent_at' => $lastSent->sent_at->toIso8601String(),
                'now' => now()->toIso8601String(),
                'seconds_in_future' => abs($secondsSinceLastSent),
                'last_log_id' => $lastSent->id,
            ]);
            return ['can_send' => true, 'wait_seconds' => null];
        }

        if ($secondsSinceLastSent >= $minWaitSeconds) {
            return ['can_send' => true, 'wait_seconds' => null];
        }

        $waitSeconds = $minWaitSeconds - $secondsSinceLastSent;

        return ['can_send' => false, 'wait_seconds' => (int) $waitSeconds];
    }
}
