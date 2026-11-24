<?php

namespace App\Services\Social\Facebook;

use App\Enums\SocialPlatform;
use App\Models\SocialProfile;
use App\Services\Social\Contracts\PublishingProviderInterface;
use App\Services\Social\Contracts\SocialProviderInterface;
use App\Services\Social\DTOs\PublishingPayloadDTO;
use App\Services\Social\DTOs\PublishingResultDTO;
use App\Services\Social\Registry\PlatformCapabilityRegistry;
use Exception;
use Illuminate\Support\Facades\Http;

class FacebookPublishingService implements SocialProviderInterface, PublishingProviderInterface
{
    protected string $graphVersion = 'v20.0';

    public function getPlatform(): SocialPlatform
    {
        return SocialPlatform::FACEBOOK;
    }

    public function getCapabilities(): array
    {
        return PlatformCapabilityRegistry::getCapabilities(SocialPlatform::FACEBOOK);
    }

    /**
     * Dispatch publishing request to appropriate Meta Graph API endpoint based on media payload.
     */
    public function publish(SocialProfile $profile, PublishingPayloadDTO $payload): PublishingResultDTO
    {
        // 1. Resolve Access Token (Page-specific token preferred, fallback to connection user token)
        $token = $profile->access_token ?? $profile->connection?->access_token;
        if (empty($token)) {
            return PublishingResultDTO::failed("Missing access token for Facebook Page '{$profile->name}'.", 'ERR_NO_TOKEN', false);
        }

        $pageId = $profile->profile_id;
        $mediaUrls = array_values(array_filter($payload->mediaUrls));
        $mediaCount = count($mediaUrls);

        try {
            // Check if media is video
            if ($mediaCount === 1 && $this->isVideoUrl($mediaUrls[0])) {
                return $this->publishVideoPost($pageId, $token, $mediaUrls[0], $payload);
            }

            // Multi-photo album post
            if ($mediaCount > 1) {
                return $this->publishMultiPhotoPost($pageId, $token, $mediaUrls, $payload);
            }

            // Single photo post
            if ($mediaCount === 1) {
                return $this->publishSinglePhotoPost($pageId, $token, $mediaUrls[0], $payload);
            }

            // Link post
            if (!empty($payload->linkUrl)) {
                return $this->publishLinkPost($pageId, $token, $payload);
            }

            // Plain text post
            return $this->publishTextPost($pageId, $token, $payload);

        } catch (Exception $e) {
            return PublishingResultDTO::failed("Facebook Publishing Exception: " . $e->getMessage(), 'ERR_EXCEPTION', true);
        }
    }

    /**
     * Publish standard text status update to Page feed.
     */
    protected function publishTextPost(string $pageId, string $token, PublishingPayloadDTO $payload): PublishingResultDTO
    {
        $response = Http::withToken($token)
            ->post("https://graph.facebook.com/{$this->graphVersion}/{$pageId}/feed", [
                'message' => $payload->caption,
            ]);

        return $this->handlePostResponse($response, $pageId);
    }

    /**
     * Publish link post to Page feed.
     */
    protected function publishLinkPost(string $pageId, string $token, PublishingPayloadDTO $payload): PublishingResultDTO
    {
        $response = Http::withToken($token)
            ->post("https://graph.facebook.com/{$this->graphVersion}/{$pageId}/feed", [
                'message' => $payload->caption,
                'link' => $payload->linkUrl,
            ]);

        return $this->handlePostResponse($response, $pageId);
    }

    /**
     * Publish single photo with caption to Page photos.
     */
    protected function publishSinglePhotoPost(string $pageId, string $token, string $photoUrl, PublishingPayloadDTO $payload): PublishingResultDTO
    {
        $response = Http::withToken($token)
            ->post("https://graph.facebook.com/{$this->graphVersion}/{$pageId}/photos", [
                'url' => $photoUrl,
                'caption' => $payload->caption,
            ]);

        return $this->handlePostResponse($response, $pageId);
    }

    /**
     * Publish multi-image post via unpublished photo uploads + feed attachment.
     */
    protected function publishMultiPhotoPost(string $pageId, string $token, array $photoUrls, PublishingPayloadDTO $payload): PublishingResultDTO
    {
        $attachedMedia = [];

        // Step 1: Upload each photo as unpublished
        foreach ($photoUrls as $url) {
            $uploadResponse = Http::withToken($token)
                ->post("https://graph.facebook.com/{$this->graphVersion}/{$pageId}/photos", [
                    'url' => $url,
                    'published' => false,
                ]);

            if (!$uploadResponse->successful()) {
                $errorMsg = $uploadResponse->json('error.message') ?? 'Failed to upload photo to Facebook';
                return PublishingResultDTO::failed("Multi-photo upload error: {$errorMsg}", 'ERR_PHOTO_UPLOAD', true, $uploadResponse->json() ?? []);
            }

            $photoId = $uploadResponse->json('id');
            $attachedMedia[] = ['media_fbid' => $photoId];
        }

        // Step 2: Publish feed post attaching all uploaded photos
        $response = Http::withToken($token)
            ->post("https://graph.facebook.com/{$this->graphVersion}/{$pageId}/feed", [
                'message' => $payload->caption,
                'attached_media' => $attachedMedia,
            ]);

        return $this->handlePostResponse($response, $pageId);
    }

    /**
     * Publish video post to Page videos.
     */
    protected function publishVideoPost(string $pageId, string $token, string $videoUrl, PublishingPayloadDTO $payload): PublishingResultDTO
    {
        $response = Http::withToken($token)
            ->timeout(120)
            ->post("https://graph.facebook.com/{$this->graphVersion}/{$pageId}/videos", [
                'file_url' => $videoUrl,
                'description' => $payload->caption,
            ]);

        return $this->handlePostResponse($response, $pageId);
    }

    /**
     * Normalize Graph API response into PublishingResultDTO.
     */
    protected function handlePostResponse($response, string $pageId): PublishingResultDTO
    {
        if ($response->successful()) {
            $data = $response->json();
            $postId = (string)($data['id'] ?? $data['post_id'] ?? '');

            // Build public permalink
            $postUrl = "https://facebook.com/{$postId}";

            return PublishingResultDTO::successful($postId, $postUrl, $data);
        }

        $errorData = $response->json('error') ?? [];
        $errorCode = (string)($errorData['code'] ?? $response->status());
        $errorMessage = $errorData['message'] ?? 'Unknown Meta Graph API error.';

        // Determine if retriable (Rate limits: code 32, 613; Temporary server error: code 1, 2)
        $retriable = in_array((int)$errorCode, [1, 2, 32, 613], true);

        return PublishingResultDTO::failed($errorMessage, $errorCode, $retriable, $response->json() ?? []);
    }

    public function checkPublishStatus(SocialProfile $profile, string $providerPostId): PublishingResultDTO
    {
        $token = $profile->access_token ?? $profile->connection?->access_token;
        $response = Http::withToken($token)
            ->get("https://graph.facebook.com/{$this->graphVersion}/{$providerPostId}", [
                'fields' => 'id,is_published,permalink_url',
            ]);

        if ($response->successful()) {
            return PublishingResultDTO::successful($providerPostId, $response->json('permalink_url'));
        }

        return PublishingResultDTO::failed($response->json('error.message') ?? 'Status check failed');
    }

    protected function isVideoUrl(string $url): bool
    {
        $cleanUrl = strtolower(strtok($url, '?'));
        return str_ends_with($cleanUrl, '.mp4') || str_ends_with($cleanUrl, '.mov') || str_ends_with($cleanUrl, '.webm');
    }
}

