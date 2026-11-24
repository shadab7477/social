<?php

namespace App\Services\Social\Instagram;

use App\Models\PostVariant;
use App\Models\SocialProfile;
use App\Services\Social\Contracts\AnalyticsProviderInterface;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstagramAnalyticsService implements AnalyticsProviderInterface
{
    protected string $graphApiBase = 'https://graph.facebook.com/v20.0';

    /**
     * Ingest profile metrics from Instagram Graph API.
     */
    public function fetchProfileMetrics(SocialProfile $profile, string $startDate, string $endDate): array
    {
        $accessToken = $profile->access_token ?? $profile->connection?->access_token;
        if (!$accessToken) {
            return [];
        }

        $igUserId = $profile->profile_id;
        $url = "{$this->graphApiBase}/{$igUserId}/insights";

        try {
            $response = Http::withToken($accessToken)
                ->timeout(15)
                ->get($url, [
                    'metric' => 'impressions,reach,profile_views',
                    'period' => 'day',
                    'since' => strtotime($startDate),
                    'until' => strtotime($endDate),
                ]);

            if ($response->failed()) {
                Log::warning("Instagram analytics fetch failed for account {$igUserId}: " . $response->body());
                return [];
            }

            $data = $response->json('data') ?? [];
            $records = [];

            foreach ($data as $item) {
                $metricName = match ($item['name'] ?? '') {
                    'impressions' => 'impressions',
                    'reach' => 'reach',
                    'profile_views' => 'profile_views',
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
            Log::error("Instagram analytics exception: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch media metrics for published Instagram container.
     */
    public function fetchPostMetrics(SocialProfile $profile, PostVariant $variant): array
    {
        if (!$variant->external_post_id) {
            return [];
        }

        $accessToken = $profile->access_token ?? $profile->connection?->access_token;
        $mediaId = $variant->external_post_id;
        $url = "{$this->graphApiBase}/{$mediaId}/insights";

        try {
            $response = Http::withToken($accessToken)
                ->timeout(15)
                ->get($url, [
                    'metric' => 'impressions,reach,saved,engagement',
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

                if ($name === 'impressions') {
                    $metrics['impressions'] = (int) $val;
                } elseif ($name === 'reach') {
                    $metrics['reach'] = (int) $val;
                } elseif ($name === 'engagement') {
                    $metrics['likes'] = (int) $val;
                }
            }

            return $metrics;
        } catch (Exception $e) {
            return [];
        }
    }
}

