<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Process the existing EMAIL_QUEUE table every minute when the scheduler runs.
Schedule::command('coolclean:process-emails')->everyMinute()->withoutOverlapping();
