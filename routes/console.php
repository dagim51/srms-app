<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use App\Jobs\CheckOverdueRentJob;
use App\Jobs\ExpireHostBookingRequestsJob;
use App\Jobs\ExpireTemporaryBookingHoldsJob;
use Illuminate\Support\Facades\Schedule;

Schedule::command('srms:expire-offers')->everyFifteenMinutes();
Schedule::command('srms:generate-rent-invoices')->daily();
Schedule::command('srms:apply-rent-penalties')->dailyAt('02:00');
Schedule::job(new CheckOverdueRentJob)->dailyAt('01:00');
Schedule::job(new ExpireTemporaryBookingHoldsJob)->everyMinute();
Schedule::job(new ExpireHostBookingRequestsJob)->hourly();
Schedule::command('srms:check-milestones')->dailyAt('06:00');
