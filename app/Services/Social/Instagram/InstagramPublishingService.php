<?php

namespace App\Services\Social\Instagram;

use App\Enums\SocialPlatform;
use App\Models\SocialProfile;
use App\Services\Social\Contracts\PublishingProviderInterface;
use App\Services\Social\Contracts\SocialProviderInterface;
use App\Services\Social\DTOs\PublishingPayloadDTO;
use App\Services\Social\DTOs\PublishingResultDTO;
use App\Services\Social\Registry\PlatformCapabilityRegistry;
use Exception;
use Illuminate\Support\Facades\Http;

class InstagramPublishingService implements SocialProviderInterface, PublishingProviderInterface
{
    protected string $graphVersion = 'v20.0';

    public function getPlatform(): SocialPlatform
    {
        return SocialPlatform::INSTAGRAM;
    }

    public function getCapabilities(): array
    {
        return PlatformCapabilityRegistry::getCapabilities(SocialPlatform::INSTAGRAM);
    }

    /**
     * Publish media to Instagram Professional Account via Meta Graph Container API.
     */
    public function publish(SocialProfile $profile, PublishingPayloadDTO $payload): PublishingResultDTO
    {
        $token = $profile->access_token ?? $profile->connection?->access_token;
        if (empty($token)) {
            return PublishingResultDTO::failed("Missing access token for Instagram account '{$profile->name}'.", 'ERR_NO_TOKEN', false);
        }

        $igUserId = $profile->profile_id;
        $mediaUrls = array_values(array_filter($payload->mediaUrls));
        $mediaCount = count($mediaUrls);

        // Instagram Graph API strictly requires at least 1 image or video
        if ($mediaCount === 0) {
            return PublishingResultDTO::failed("Instagram publishing requires at least one image or video.", 'ERR_NO_MEDIA', false);
        }

        try {
            // Workflow A: Carousel Post (> 1 media items)
            if ($mediaCount > 1) {
                return $this->publishCarouselPost($igUserId, $token, $mediaUrls, $payload);
            }

            $mediaUrl = $mediaUrls[0];

            // Workflow B: Reel / Video Post
            if ($this->isVideoUrl($mediaUrl)) {
                return $this->publishReelPost($igUserId, $token, $mediaUrl, $payload);
            }

            // Workflow C: Single Image Post
            return $this->publishSingleImagePost($igUserId, $token, $mediaUrl, $payload);

        } catch (Exception $e) {
            return PublishingResultDTO::failed("Instagram Publishing Exception: " . $e->getMessage(), 'ERR_EXCEPTION', true);
        }
    }

    /**
     * Step 1 -> Step 2 -> Step 3 Workflow for Single Image.
     */
    protected function publishSingleImagePost(string $igUserId, string $token, string $imageUrl, PublishingPayloadDTO $payload): PublishingResultDTO
    {
        // 1. Create Media Container
        $containerResponse = Http::withToken($token)
            ->post("https://graph.facebook.com/{$this->graphVersion}/{$igUserId}/media", [
                'image_url' => $imageUrl,
                'caption' => $payload->caption,
            ]);

        if (!$containerResponse->successful()) {
            return $this->formatError($containerResponse, "Failed to create Instagram photo container");
        }

        $creationId = (string)$containerResponse->json('id');

        // 2. Poll Container Status (Wait until FINISHED)
        $ready = $this->pollContainerStatus($creationId, $token);
        if (!$ready) {
            return PublishingResultDTO::failed("Instagram container transcoding timed out.", 'ERR_TIMEOUT', true);
        }

        // 3. Publish Media Container
        return $this->publishContainer($igUserId, $token, $creationId);
    }

    /**
     * Step 1 -> Step 2 -> Step 3 Workflow for Instagram Reels.
     */
    protected function publishReelPost(string $igUserId, string $token, string $videoUrl, PublishingPayloadDTO $payload): PublishingResultDTO
    {
        // 1. Create Reels Container
        $containerResponse = Http::withToken($token)
            ->post("https://graph.facebook.com/{$this->graphVersion}/{$igUserId}/media", [
                'media_type' => 'REELS',
                'video_url' => $videoUrl,
                'caption' => $payload->caption,
                'share_to_feed' => true,
            ]);

        if (!$containerResponse->successful()) {
            return $this->formatError($containerResponse, "Failed to create Instagram Reel container");
        }

        $creationId = (string)$containerResponse->json('id');

        // 2. Poll Status (Videos take longer to transcode)
        $ready = $this->pollContainerStatus($creationId, $token, 10, 3);
        if (!$ready) {
            return PublishingResultDTO::failed("Instagram Reel video processing timed out.", 'ERR_TIMEOUT', true);
        }

        // 3. Publish Container
        return $this->publishContainer($igUserId, $token, $creationId);
    }

    /**
     * Multi-item Carousel Container Workflow.
     */
    protected function publishCarouselPost(string $igUserId, string $token, array $mediaUrls, PublishingPayloadDTO $payload): PublishingResultDTO
    {
        $childIds = [];

        // 1. Create child containers for each item
        foreach ($mediaUrls as $url) {
            $isVideo = $this->isVideoUrl($url);
            $params = [
                'is_carousel_item' => true,
            ];

            if ($isVideo) {
                $params['media_type'] = 'VIDEO';
                $params['video_url'] = $url;
            } else {
                $params['image_url'] = $url;
            }

            $childResponse = Http::withToken($token)
                ->post("https://graph.facebook.com/{$this->graphVersion}/{$igUserId}/media", $params);

            if (!$childResponse->successful()) {
                return $this->formatError($childResponse, "Failed to create Instagram carousel item");
            }

            $childId = (string)$childResponse->json('id');
            $childIds[] = $childId;

            // Wait for child item to finish processing
            if (!$this->pollContainerStatus($childId, $token, 5, 2)) {
                return PublishingResultDTO::failed("Carousel item {$childId} processing timed out.", 'ERR_TIMEOUT', true);
            }
        }

        // 2. Create Parent Carousel Container
        $parentResponse = Http::withToken($token)
            ->post("https://graph.facebook.com/{$this->graphVersion}/{$igUserId}/media", [
                'media_type' => 'CAROUSEL',
                'caption' => $payload->caption,
                'children' => implode(',', $childIds),
            ]);

        if (!$parentResponse->successful()) {
            return $this->formatError($parentResponse, "Failed to create parent carousel container");
        }

        $parentCreationId = (string)$parentResponse->json('id');

        // 3. Poll parent status
        if (!$this->pollContainerStatus($parentCreationId, $token, 5, 2)) {
            return PublishingResultDTO::failed("Carousel parent container processing timed out.", 'ERR_TIMEOUT', true);
        }

        // 4. Publish Container
        return $this->publishContainer($igUserId, $token, $parentCreationId);
    }

    /**
     * Step 3: Trigger media publish for a finished container.
     */
    protected function publishContainer(string $igUserId, string $token, string $creationId): PublishingResultDTO
    {
        $response = Http::withToken($token)
            ->post("https://graph.facebook.com/{$this->graphVersion}/{$igUserId}/media_publish", [
                'creation_id' => $creationId,
            ]);

        if ($response->successful()) {
            $publishedMediaId = (string)$response->json('id');

            // Query permalink
            $detailsResponse = Http::withToken($token)
                ->get("https://graph.facebook.com/{$this->graphVersion}/{$publishedMediaId}", [
                    'fields' => 'id,permalink',
                ]);

            $permalink = $detailsResponse->successful() ? $detailsResponse->json('permalink') : null;

            return PublishingResultDTO::successful($publishedMediaId, $permalink, $response->json());
        }

        return $this->formatError($response, "Instagram media publish failed");
    }

    /**
     * Poll status of an Instagram media container until status is FINISHED or ERROR.
     */
    public function pollContainerStatus(string $creationId, string $token, int $maxRetries = 6, int $delaySeconds = 2): bool
    {
        for ($i = 0; $i < $maxRetries; $i++) {
            $statusResponse = Http::withToken($token)
                ->get("https://graph.facebook.com/{$this->graphVersion}/{$creationId}", [
                    'fields' => 'status_code,status',
                ]);

            if ($statusResponse->successful()) {
                $status = strtoupper($statusResponse->json('status_code') ?? '');

                if ($status === 'FINISHED') {
                    return true;
                }

                if ($status === 'ERROR' || $status === 'EXPIRED') {
                    return false;
                }
            }

            if ($i < $maxRetries - 1 && $delaySeconds > 0) {
                // In production, sleep for delay; during tests Http::fake() responds immediately
                if (!app()->environment('testing')) {
                    sleep($delaySeconds);
                }
            }
        }

        return false;
    }

    public function checkPublishStatus(SocialProfile $profile, string $providerPostId): PublishingResultDTO
    {
        $token = $profile->access_token ?? $profile->connection?->access_token;
        $response = Http::withToken($token)
            ->get("https://graph.facebook.com/{$this->graphVersion}/{$providerPostId}", [
                'fields' => 'id,permalink,media_type',
            ]);

        if ($response->successful()) {
            return PublishingResultDTO::successful($providerPostId, $response->json('permalink'));
        }

        return PublishingResultDTO::failed($response->json('error.message') ?? 'Status check failed');
    }

    protected function formatError($response, string $prefix): PublishingResultDTO
    {
        $errorData = $response->json('error') ?? [];
        $code = (string)($errorData['code'] ?? $response->status());
        $message = $prefix . ": " . ($errorData['message'] ?? 'Unknown Graph API error');
        $retriable = in_array((int)$code, [1, 2, 4, 17, 32, 613], true);

        return PublishingResultDTO::failed($message, $code, $retriable, $response->json() ?? []);
    }

    protected function isVideoUrl(string $url): bool
    {
        $cleanUrl = strtolower(strtok($url, '?'));
        return str_ends_with($cleanUrl, '.mp4') || str_ends_with($cleanUrl, '.mov');
    }
}

