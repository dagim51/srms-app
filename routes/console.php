<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use App\Jobs\CheckOverdueRentJob;
use Illuminate\Support\Facades\Schedule;

Schedule::command('srms:expire-offers')->everyFifteenMinutes();
Schedule::command('srms:generate-rent-invoices')->daily();
Schedule::job(new CheckOverdueRentJob)->dailyAt('01:00');
Schedule::command('srms:check-milestones')->dailyAt('06:00');
