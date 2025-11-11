<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'legal_name',
        'tax_id',
        'industry',
        'company_size',
        'phone',
        'website',
        'country',
        'city',
        'address',
        'plan',
        'max_searches',
        'max_frequency_minutes',
        'can_use_ai',
        'subscription_status',
        'trial_ends_at',
        'subscription_started_at',
        'billing_email',
    ];

    protected $casts = [
        'can_use_ai' => 'boolean',
        'trial_ends_at' => 'datetime',
        'subscription_started_at' => 'datetime',
    ];

    /**
     * Relación con usuarios
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Verificar si la empresa puede crear más búsquedas
     */
    public function canCreateSearch(): bool
    {
        if ($this->max_searches === -1) {
            return true; // Plan ilimitado (enterprise)
        }

        $totalSearches = $this->users()
            ->withCount('searches')
            ->get()
            ->sum('searches_count');

        return $totalSearches < $this->max_searches;
    }

    /**
     * Obtener el número actual de búsquedas
     */
    public function getCurrentSearchCount(): int
    {
        return $this->users()
            ->withCount('searches')
            ->get()
            ->sum('searches_count');
    }

    /**
     * Verificar si el trial está activo
     */
    public function isTrialActive(): bool
    {
        return $this->subscription_status === 'trial'
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    /**
     * Verificar si la suscripción está activa
     */
    public function isSubscriptionActive(): bool
    {
        return in_array($this->subscription_status, ['trial', 'active'])
            && ($this->subscription_status !== 'trial' || $this->isTrialActive());
    }

    /**
     * Obtener los límites del plan actual
     */
    public function getPlanLimits(): array
    {
        return [
            'max_searches' => $this->max_searches,
            'max_frequency_minutes' => $this->max_frequency_minutes,
            'can_use_ai' => $this->can_use_ai,
            'current_searches' => $this->getCurrentSearchCount(),
            'remaining_searches' => $this->max_searches === -1
                ? -1
                : max(0, $this->max_searches - $this->getCurrentSearchCount()),
        ];
    }
}
