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

// Automatic Profile Analytics Sync (every 2 hours)
Schedule::call(function () {
    $profiles = \App\Models\SocialProfile::where('publishing_enabled', true)->where('is_active', true)->get();
    foreach ($profiles as $p) {
        \App\Jobs\Analytics\SyncProfileAnalyticsJob::dispatch($p->id);
    }
})->everyTwoHours();

// Automated Client Report Dispatches (weekly & monthly)
Schedule::call(function () {
    $now = now();
    $configs = \App\Models\ReportConfig::where('is_active', true)->get();
    $service = app(\App\Services\Reports\ReportBuilderService::class);

    foreach ($configs as $config) {
        $shouldRun = false;
        if ($config->frequency === \App\Enums\ReportFrequency::WEEKLY && $now->isMonday() && $now->hour === 9) {
            $shouldRun = true;
        } elseif ($config->frequency === \App\Enums\ReportFrequency::MONTHLY && $now->day === 1 && $now->hour === 9) {
            $shouldRun = true;
        }

        if ($shouldRun) {
            $dateFrom = $config->frequency === \App\Enums\ReportFrequency::WEEKLY ? $now->copy()->subDays(7) : $now->copy()->subMonth();
            $service->createReport(
                $config->workspace->owner ?? \App\Models\User::first(),
                $config->name . ' — ' . $now->format('M Y'),
                \App\Enums\ReportFormat::PDF,
                $dateFrom,
                $now,
                $config->client_id,
                $config->branding_options ?? []
            );
            $config->update(['last_sent_at' => $now]);
        }
    }
})->dailyAt('09:00');

