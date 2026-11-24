<?php

use App\Jobs\Publishing\DispatchDuePostsJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Publishing Scheduler: Claims and dispatches due social posts every minute
Schedule::job(new DispatchDuePostsJob())->everyMinute();

