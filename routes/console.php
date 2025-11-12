<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Programar ejecución de búsquedas de Twitter cada minuto
Schedule::command('twitter:search')->everyMinute();

// Programar análisis IA de tweets cada minuto
Schedule::command('tweets:analyze-ai')->everyMinute();
