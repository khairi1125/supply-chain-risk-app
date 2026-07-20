<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-refresh weather cache every 4 hours for all countries
\Illuminate\Support\Facades\Schedule::command('weather:warm-cache --limit=250')->everyFourHours();

// Take daily risk score snapshot at midnight
\Illuminate\Support\Facades\Schedule::command('risk:snapshot')->daily();

// Generate/update alert logs every hour
\Illuminate\Support\Facades\Schedule::command('alerts:generate')->hourly();

