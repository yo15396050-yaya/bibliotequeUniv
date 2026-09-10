<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Tâches planifiées
|--------------------------------------------------------------------------
| À activer côté serveur par une seule ligne de cron :
| * * * * * cd /chemin/du/projet && php artisan schedule:run >> /dev/null 2>&1
*/

// Chaque matin : retards, pénalités et rappels d'échéance.
Schedule::command('bibliotheque:traiter-retards')
    ->dailyAt('07:00')
    ->withoutOverlapping()
    ->onOneServer();

// Deux fois par jour : libération des réservations non retirées.
Schedule::command('bibliotheque:expirer-reservations')
    ->twiceDaily(8, 18)
    ->withoutOverlapping();

// Chaque nuit : cohérence des compteurs d'exemplaires.
Schedule::command('bibliotheque:synchroniser-compteurs')
    ->dailyAt('02:30')
    ->withoutOverlapping();
