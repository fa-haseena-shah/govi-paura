<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('listings:close-expired-bidding')->everyMinute()->withoutOverlapping();
Schedule::command('subscriptions:update-statuses')->dailyAt('00:10');
Schedule::command('deliveries:enforce-sla')->everyFiveMinutes()->withoutOverlapping();