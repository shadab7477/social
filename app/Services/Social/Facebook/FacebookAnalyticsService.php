<?php

namespace App\Services\Social\Facebook;

use App\Models\PostVariant;
use App\Models\SocialProfile;
use App\Services\Social\Contracts\AnalyticsProviderInterface;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookAnalyticsService implements AnalyticsProviderInterface
{
    protected string $graphApiBase = 'https://graph.facebook.com/v20.0';

    /**
     * Ingest page-level metrics from Meta Graph API.
     */
    public function fetchProfileMetrics(SocialProfile $profile, string $startDate, string $endDate): array
    {
        $accessToken = $profile->access_token ?? $profile->connection?->access_token;
        if (!$accessToken) {
            return [];
        }

        $pageId = $profile->profile_id;
        $url = "{$this->graphApiBase}/{$pageId}/insights";

        try {
            $response = Http::withToken($accessToken)
                ->timeout(15)
                ->get($url, [
                    'metric' => 'page_impressions,page_engaged_users,page_post_engagements,page_fans',
                    'period' => 'day',
                    'since' => $startDate,
                    'until' => $endDate,
                ]);

            if ($response->failed()) {
                Log::warning("Facebook analytics fetch failed for page {$pageId}: " . $response->body());
                return [];
            }

            $data = $response->json('data') ?? [];
            $records = [];

            foreach ($data as $item) {
                $metricName = match ($item['name'] ?? '') {
                    'page_impressions' => 'impressions',
                    'page_engaged_users' => 'reach',
                    'page_post_engagements' => 'engagement',
                    'page_fans' => 'followers_count',
                    default => $item['name'] ?? 'custom',
                };

                $values = $item['values'] ?? [];
                foreach ($values as $val) {
                    $date = substr($val['end_time'] ?? now()->toDateString(), 0, 10);
                    $records[] = [
                        'metric_name' => $metricName,
                        'metric_value' => (float) ($val['value'] ?? 0),
                        'metric_date' => $date,
                        'period' => 'day',
                        'raw_payload' => $val,
                    ];
                }
            }

            return $records;
        } catch (Exception $e) {
            Log::error("Facebook analytics exception: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch post metrics for published Facebook variant.
     */
    public function fetchPostMetrics(SocialProfile $profile, PostVariant $variant): array
    {
        if (!$variant->external_post_id) {
            return [];
        }

        $accessToken = $profile->access_token ?? $profile->connection?->access_token;
        $postId = $variant->external_post_id;
        $url = "{$this->graphApiBase}/{$postId}/insights";

        try {
            $response = Http::withToken($accessToken)
                ->timeout(15)
                ->get($url, [
                    'metric' => 'post_impressions,post_engaged_users,post_reactions_by_type_total',
                ]);

            if ($response->failed()) {
                return [];
            }

            $data = $response->json('data') ?? [];
            $metrics = [
                'impressions' => 0,
                'reach' => 0,
                'likes' => 0,
                'comments' => 0,
                'shares' => 0,
            ];

            foreach ($data as $item) {
                $name = $item['name'] ?? '';
                $val = $item['values'][0]['value'] ?? 0;

                if ($name === 'post_impressions') {
                    $metrics['impressions'] = (int) $val;
                } elseif ($name === 'post_engaged_users') {
                    $metrics['reach'] = (int) $val;
                } elseif ($name === 'post_reactions_by_type_total') {
                    $metrics['likes'] = (int) ($val['like'] ?? 0);
                }
            }

            return $metrics;
        } catch (Exception $e) {
            return [];
        }
    }
}

