<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * Laravel 12: el scheduler se define en routes/console.php usando la Facade Schedule.
 * Mantenemos este Kernel vacío para compatibilidad y evitar ClassNotFound si algún
 * paquete intenta resolver App\Console\Kernel.
 */
class Kernel extends ConsoleKernel
{
    /**
     * No definimos tareas aquí; se manejan en routes/console.php
     */
    protected function schedule(Schedule $schedule): void
    {
        // Intencionalmente vacío en Laravel 12
    }

    /**
     * No registramos comandos aquí; Laravel 12 los auto-descubre y routes/console.php
     * es el lugar recomendado para definir comandos ad-hoc.
     */
    protected function commands(): void
    {
        // Intencionalmente vacío
    }
}
