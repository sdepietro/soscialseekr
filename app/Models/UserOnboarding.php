<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserOnboarding extends Model
{
    use HasFactory;

    protected $table = 'user_onboarding';

    protected $fillable = [
        'user_id',
        'current_step',
        'completed_at',
        'step1_account',
        'step2_company',
        'step3_search',
        'skipped_steps',
        'source',
    ];

    protected $casts = [
        'step1_account' => 'boolean',
        'step2_company' => 'boolean',
        'step3_search' => 'boolean',
        'skipped_steps' => 'array',
        'completed_at' => 'datetime',
    ];

    /**
     * Relación con usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verificar si el onboarding está completado
     */
    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    /**
     * Marcar un paso como completado
     */
    public function completeStep(int $step): void
    {
        $stepField = "step{$step}_" . $this->getStepName($step);
        $this->$stepField = true;
        $this->current_step = $step + 1;

        // Si es el último paso, marcar como completado
        if ($step === 3) {
            $this->completed_at = now();
        }

        $this->save();
    }

    /**
     * Saltar un paso
     */
    public function skipStep(int $step): void
    {
        $skipped = $this->skipped_steps ?? [];
        $skipped[] = $step;
        $this->skipped_steps = $skipped;
        $this->current_step = $step + 1;
        $this->save();
    }

    /**
     * Obtener nombre del paso
     */
    private function getStepName(int $step): string
    {
        $steps = [
            1 => 'account',
            2 => 'company',
            3 => 'search',
        ];

        return $steps[$step] ?? 'unknown';
    }

    /**
     * Obtener progreso del onboarding (porcentaje)
     */
    public function getProgress(): int
    {
        $completed = 0;

        if ($this->step1_account) $completed++;
        if ($this->step2_company) $completed++;
        if ($this->step3_search) $completed++;

        return (int) (($completed / 3) * 100);
    }
}
