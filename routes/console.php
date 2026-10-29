<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduled tasks for daily execution (Path A: Operational Mode)

// 1. Daily morning match sync across all 5 active leagues (staggered to respect API rate limits)
Schedule::command('sync:matches premier-league')->dailyAt('06:00')->withoutOverlapping()->runInBackground();
Schedule::command('sync:matches la-liga')->dailyAt('06:05')->withoutOverlapping()->runInBackground();
Schedule::command('sync:matches bundesliga')->dailyAt('06:10')->withoutOverlapping()->runInBackground();
Schedule::command('sync:matches serie-a')->dailyAt('06:15')->withoutOverlapping()->runInBackground();
Schedule::command('sync:matches ligue-1')->dailyAt('06:20')->withoutOverlapping()->runInBackground();

// 2. Matchday refresh on peak fixture days (Saturdays & Sundays) for confirmed lineups & injuries
Schedule::command('refresh:matchday premier-league')
    ->saturdays()
    ->sundays()
    ->hourly()
    ->between('11:00', '20:00')
    ->withoutOverlapping()
    ->runInBackground();

// 3. Post-match evaluation after full time (scores Log Loss and Brier score against real outcomes)
Schedule::command('evaluate:predictions')
    ->dailyAt('23:00')
    ->withoutOverlapping()
    ->runInBackground();

// 4. Daily SQLite snapshot backup at 02:00 UTC (keeps rolling 10 snapshots)
Schedule::command('app:backup')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->runInBackground();
