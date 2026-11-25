<?php

namespace App\Services\Social\YouTube;

use App\Models\PostVariant;
use App\Models\SocialProfile;
use App\Services\Social\Contracts\AnalyticsProviderInterface;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YouTubeAnalyticsService implements AnalyticsProviderInterface
{
    protected string $channelsApi = 'https://www.googleapis.com/youtube/v3/channels';
    protected string $videosApi = 'https://www.googleapis.com/youtube/v3/videos';

    /**
     * Ingest channel-level metrics from YouTube Data API v3.
     */
    public function fetchProfileMetrics(SocialProfile $profile, string $startDate, string $endDate): array
    {
        $accessToken = $profile->access_token ?? $profile->connection?->access_token;
        if (!$accessToken) {
            return [];
        }

        $records = [];

        try {
            // 1. Fetch channel statistics
            $response = Http::withToken($accessToken)
                ->timeout(15)
                ->get($this->channelsApi, [
                    'part' => 'statistics,snippet',
                    'mine' => 'true',
                ]);

            if ($response->successful()) {
                $channel = $response->json('items.0') ?? [];
                $stats = $channel['statistics'] ?? [];
                $today = now()->toDateString();

                $viewCount = (float) ($stats['viewCount'] ?? 0);
                $subscriberCount = (float) ($stats['subscriberCount'] ?? 0);
                $commentCount = (float) ($stats['commentCount'] ?? 0);

                $records[] = [
                    'metric_name' => 'impressions',
                    'metric_value' => $viewCount,
                    'metric_date' => $today,
                    'period' => 'day',
                    'raw_payload' => $stats,
                ];

                $records[] = [
                    'metric_name' => 'reach',
                    'metric_value' => (float) round($viewCount * 0.9),
                    'metric_date' => $today,
                    'period' => 'day',
                    'raw_payload' => $stats,
                ];

                $records[] = [
                    'metric_name' => 'followers_count',
                    'metric_value' => $subscriberCount,
                    'metric_date' => $today,
                    'period' => 'day',
                    'raw_payload' => $stats,
                ];

                $records[] = [
                    'metric_name' => 'engagement',
                    'metric_value' => $commentCount,
                    'metric_date' => $today,
                    'period' => 'day',
                    'raw_payload' => $stats,
                ];
            } else {
                Log::warning("YouTube analytics fetch failed for profile {$profile->id}: " . $response->body());
            }

            return $records;
        } catch (Exception $e) {
            Log::error("YouTube analytics exception: " . $e->getMessage());
            return $records;
        }
    }

    /**
     * Fetch video metrics for published YouTube variant.
     */
    public function fetchPostMetrics(SocialProfile $profile, PostVariant $variant): array
    {
        if (!$variant->external_post_id) {
            return [];
        }

        $accessToken = $profile->access_token ?? $profile->connection?->access_token;
        if (!$accessToken) {
            return [];
        }

        $metrics = [
            'impressions' => 0,
            'reach' => 0,
            'likes' => 0,
            'comments' => 0,
            'shares' => 0,
        ];

        try {
            $response = Http::withToken($accessToken)
                ->timeout(15)
                ->get($this->videosApi, [
                    'part' => 'statistics',
                    'id' => $variant->external_post_id,
                ]);

            if ($response->successful()) {
                $video = $response->json('items.0') ?? [];
                $stats = $video['statistics'] ?? [];

                $views = (int) ($stats['viewCount'] ?? 0);
                $likes = (int) ($stats['likeCount'] ?? 0);
                $comments = (int) ($stats['commentCount'] ?? 0);

                $metrics['impressions'] = $views;
                $metrics['reach'] = (int) round($views * 0.9);
                $metrics['likes'] = $likes;
                $metrics['comments'] = $comments;
            }

            return $metrics;
        } catch (Exception $e) {
            return $metrics;
        }
    }
}
