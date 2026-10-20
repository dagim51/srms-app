<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('srms:expire-offers')->hourly();
Schedule::command('srms:generate-rent-invoices')->daily();
Schedule::command('srms:check-milestones')->dailyAt('06:00');

