<?php

namespace App\Services\Social\Contracts;

use App\Models\PostVariant;
use App\Models\SocialProfile;

interface AnalyticsProviderInterface
{
    /**
     * Fetch account/page level metrics (impressions, reach, engagement, followers) for date range.
     * Returns array of items: [['metric_name' => '...', 'metric_value' => ..., 'metric_date' => 'YYYY-MM-DD', 'period' => 'day']]
     */
    public function fetchProfileMetrics(SocialProfile $profile, string $startDate, string $endDate): array;

    /**
     * Fetch post-level metrics for a published post variant.
     * Returns associative array: ['impressions' => 120, 'reach' => 90, 'likes' => 15, 'comments' => 4, 'shares' => 2]
     */
    public function fetchPostMetrics(SocialProfile $profile, PostVariant $variant): array;
}

