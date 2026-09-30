<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Retention: polls are deleted twelve months after their last activity.
// The server must run the scheduler for this to happen.
Schedule::command('kanvi:purge-polls')
    ->dailyAt('03:30')
    ->onOneServer()
    // Nothing else would notice this failing. The polls would simply outlive the
    // retention the privacy page promises, quietly and for as long as it kept failing.
    ->onFailure(fn () => Log::error('kanvi:purge-polls failed. Polls past the retention window were not deleted.'));
