<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule monthly leave accrual - runs on the last day of every month at 11:59 PM
Schedule::command('leave:process-monthly-accrual')->monthlyOn(31, '23:59');

// Schedule year-end carryover - runs on January 1st at 00:01 AM
Schedule::command('leave:process-year-end-carryover')->yearlyOn(1, 1, '00:01');
