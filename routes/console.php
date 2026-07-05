<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily database backup (requires the scheduler/cron to be running on the server).
Schedule::command('boxeros:backup')->dailyAt('03:00');

// Nightly billing sync — keeps subscription statuses true (cancellations, renewals)
// even when Paddle webhooks are blocked at the CDN edge.
Schedule::command('boxeros:sync-subscriptions')->dailyAt('04:00');
