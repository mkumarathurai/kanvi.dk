<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Retention: polls are deleted twelve months after their last activity.
// The server must run the scheduler for this to happen.
Schedule::command('kanvi:purge-polls')->dailyAt('03:30')->onOneServer();
