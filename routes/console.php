<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('saas:check-subscriptions')->daily();
Schedule::command('saas:suspend-expired')->daily();
Schedule::command('saas:generate-trials')->hourly();
