<?php

namespace App\Jobs\Analytics;

use App\Enums\PostStatus;
use App\Models\AnalyticsSnapshot;
use App\Models\AnalyticsSyncLog;
use App\Models\SocialProfile;
use App\Services\Social\Registry\SocialProviderManager;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncProfileAnalyticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;

    public function __construct(
        public int $profileId,
        public ?string $startDate = null,
        public ?string $endDate = null
    ) {}

    public function handle(SocialProviderManager $providerManager): void
    {
        $profile = SocialProfile::with('connection')->find($this->profileId);
        if (!$profile || !$profile->is_active || !$profile->connection?->isPublishable()) {
            return;
        }

        // Ensure fresh access token
        $profile->connection?->getValidAccessToken();

        $startDate = $this->startDate ?? now()->subDays(30)->toDateString();
        $endDate = $this->endDate ?? now()->toDateString();
        $recordsCount = 0;

        try {
            $adapter = $providerManager->getAnalyticsProvider($profile->platform);

            // 1. Ingest account-level metrics
            $profileMetrics = $adapter->fetchProfileMetrics($profile, $startDate, $endDate);
            foreach ($profileMetrics as $metric) {
                AnalyticsSnapshot::updateOrCreate(
                    [
                        'workspace_id' => $profile->workspace_id,
                        'social_profile_id' => $profile->id,
                        'post_variant_id' => null,
                        'metric_date' => $metric['metric_date'],
                        'metric_name' => $metric['metric_name'],
                        'period' => $metric['period'] ?? 'day',
                    ],
                    [
                        'metric_value' => $metric['metric_value'],
                        'raw_payload' => $metric['raw_payload'] ?? null,
                        'fetched_at' => now(),
                    ]
                );
                $recordsCount++;
            }

            // 2. Ingest post-level metrics for published variants
            $variants = $profile->postVariants()
                ->where('status', PostStatus::PUBLISHED)
                ->whereNotNull('external_post_id')
                ->latest()
                ->take(20)
                ->get();

            foreach ($variants as $variant) {
                $postMetrics = $adapter->fetchPostMetrics($profile, $variant);
                foreach ($postMetrics as $metricName => $val) {
                    AnalyticsSnapshot::updateOrCreate(
                        [
                            'workspace_id' => $profile->workspace_id,
                            'social_profile_id' => $profile->id,
                            'post_variant_id' => $variant->id,
                            'metric_date' => $variant->published_at?->toDateString() ?? now()->toDateString(),
                            'metric_name' => $metricName,
                            'period' => 'lifetime',
                        ],
                        [
                            'metric_value' => (float) $val,
                            'fetched_at' => now(),
                        ]
                    );
                    $recordsCount++;
                }
            }

            AnalyticsSyncLog::create([
                'social_profile_id' => $profile->id,
                'status' => 'success',
                'records_ingested' => $recordsCount,
                'synced_at' => now(),
            ]);

            $profile->update(['last_health_check_at' => now()]);

        } catch (Exception $e) {
            Log::error("Analytics sync failed for profile {$profile->id}: " . $e->getMessage());

            AnalyticsSyncLog::create([
                'social_profile_id' => $profile->id,
                'status' => 'failed',
                'records_ingested' => $recordsCount,
                'error_message' => $e->getMessage(),
                'synced_at' => now(),
            ]);
        }
    }
}

