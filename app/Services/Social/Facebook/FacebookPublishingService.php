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
        $mediaPaths = array_values(array_filter($payload->mediaPaths));
        $mediaCount = count($mediaUrls);
        $postType = $payload->platformSettings['post_type'] ?? 'feed';

        try {
            // Facebook Stories (Photo or Video)
            if ($postType === 'story') {
                if ($mediaCount === 0) {
                    return PublishingResultDTO::failed("Facebook Story requires a photo or video asset.", 'ERR_MEDIA_REQUIRED', false);
                }
                if ($this->isVideoUrl($mediaUrls[0])) {
                    return $this->publishVideoStory($pageId, $token, $mediaUrls[0], $mediaPaths[0] ?? null);
                }
                return $this->publishPhotoStory($pageId, $token, $mediaUrls[0], $mediaPaths[0] ?? null);
            }

            // Facebook Reel
            if ($postType === 'reel') {
                if ($mediaCount === 0 || !$this->isVideoUrl($mediaUrls[0])) {
                    return PublishingResultDTO::failed("Facebook Reel requires an uploaded video.", 'ERR_VIDEO_REQUIRED', false);
                }
                return $this->publishReel($pageId, $token, $mediaUrls[0], $mediaPaths[0] ?? null, $payload);
            }

            // Standard Video Post
            if ($mediaCount === 1 && $this->isVideoUrl($mediaUrls[0])) {
                return $this->publishVideoPost($pageId, $token, $mediaUrls[0], $payload, $mediaPaths[0] ?? null);
            }

            // Multi-photo album post
            if ($mediaCount > 1) {
                return $this->publishMultiPhotoPost($pageId, $token, $mediaUrls, $payload, $mediaPaths);
            }

            // Single photo post
            if ($mediaCount === 1) {
                return $this->publishSinglePhotoPost($pageId, $token, $mediaUrls[0], $payload, $mediaPaths[0] ?? null);
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
    protected function publishSinglePhotoPost(string $pageId, string $token, string $photoUrl, PublishingPayloadDTO $payload, ?string $photoPath = null): PublishingResultDTO
    {
        if ($photoPath && is_file($photoPath)) {
            $response = Http::withToken($token)
                ->timeout(60)
                ->attach('source', fopen($photoPath, 'r'), basename($photoPath))
                ->post("https://graph.facebook.com/{$this->graphVersion}/{$pageId}/photos", [
                    'caption' => $payload->caption,
                ]);
        } else {
            $response = Http::withToken($token)
                ->post("https://graph.facebook.com/{$this->graphVersion}/{$pageId}/photos", [
                    'url' => $photoUrl,
                    'caption' => $payload->caption,
                ]);
        }

        return $this->handlePostResponse($response, $pageId);
    }

    /**
     * Publish multi-image post via unpublished photo uploads + feed attachment.
     */
    protected function publishMultiPhotoPost(string $pageId, string $token, array $photoUrls, PublishingPayloadDTO $payload, array $photoPaths = []): PublishingResultDTO
    {
        $attachedMedia = [];

        // Step 1: Upload each photo as unpublished
        foreach ($photoUrls as $idx => $url) {
            $path = $photoPaths[$idx] ?? null;
            if ($path && is_file($path)) {
                $uploadResponse = Http::withToken($token)
                    ->timeout(60)
                    ->attach('source', fopen($path, 'r'), basename($path))
                    ->post("https://graph.facebook.com/{$this->graphVersion}/{$pageId}/photos", [
                        'published' => false,
                    ]);
            } else {
                $uploadResponse = Http::withToken($token)
                    ->post("https://graph.facebook.com/{$this->graphVersion}/{$pageId}/photos", [
                        'url' => $url,
                        'published' => false,
                    ]);
            }

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
    protected function publishVideoPost(string $pageId, string $token, string $videoUrl, PublishingPayloadDTO $payload, ?string $videoPath = null): PublishingResultDTO
    {
        if ($videoPath && is_file($videoPath)) {
            $response = Http::withToken($token)
                ->timeout(180)
                ->attach('source', fopen($videoPath, 'r'), basename($videoPath))
                ->post("https://graph.facebook.com/{$this->graphVersion}/{$pageId}/videos", [
                    'description' => $payload->caption,
                ]);
        } else {
            $response = Http::withToken($token)
                ->timeout(120)
                ->post("https://graph.facebook.com/{$this->graphVersion}/{$pageId}/videos", [
                    'file_url' => $videoUrl,
                    'description' => $payload->caption,
                ]);
        }

        return $this->handlePostResponse($response, $pageId);
    }

    /**
     * Publish Facebook Page Reel via Meta video_reels 3-step upload flow.
     */
    protected function publishReel(string $pageId, string $token, string $videoUrl, ?string $videoPath, PublishingPayloadDTO $payload): PublishingResultDTO
    {
        $initRes = Http::withToken($token)->post("https://graph.facebook.com/{$this->graphVersion}/{$pageId}/video_reels", [
            'upload_phase' => 'start',
        ]);

        if (!$initRes->successful()) {
            return $this->handlePostResponse($initRes, $pageId);
        }

        $videoId = $initRes->json('video_id');
        $uploadUrl = $initRes->json('upload_url') ?: "https://rupload.facebook.com/video-upload/{$this->graphVersion}/{$videoId}";

        if ($videoPath && is_file($videoPath)) {
            $bytes = @file_get_contents($videoPath);
            $uploadRes = Http::withHeaders([
                'Authorization' => "OAuth {$token}",
                'offset' => '0',
                'file_size' => (string) strlen($bytes),
            ])->timeout(180)->withBody($bytes, 'application/octet-stream')->post($uploadUrl);
        } else {
            $uploadRes = Http::withHeaders([
                'Authorization' => "OAuth {$token}",
                'file_url' => $videoUrl,
            ])->timeout(120)->post($uploadUrl);
        }

        if (!$uploadRes->successful()) {
            return $this->handlePostResponse($uploadRes, $pageId);
        }

        $finishRes = Http::withToken($token)->post("https://graph.facebook.com/{$this->graphVersion}/{$pageId}/video_reels", [
            'upload_phase' => 'finish',
            'video_id' => $videoId,
            'video_state' => 'PUBLISHED',
            'description' => $payload->caption,
        ]);

        return $this->handlePostResponse($finishRes, $pageId);
    }

    /**
     * Publish Facebook Page Photo Story.
     */
    protected function publishPhotoStory(string $pageId, string $token, string $photoUrl, ?string $photoPath): PublishingResultDTO
    {
        if ($photoPath && is_file($photoPath)) {
            $photoRes = Http::withToken($token)
                ->timeout(60)
                ->attach('source', fopen($photoPath, 'r'), basename($photoPath))
                ->post("https://graph.facebook.com/{$this->graphVersion}/{$pageId}/photos", [
                    'published' => false,
                ]);
        } else {
            $photoRes = Http::withToken($token)->post("https://graph.facebook.com/{$this->graphVersion}/{$pageId}/photos", [
                'url' => $photoUrl,
                'published' => false,
            ]);
        }

        if (!$photoRes->successful()) {
            return $this->handlePostResponse($photoRes, $pageId);
        }

        $photoId = $photoRes->json('id');
        $storyRes = Http::withToken($token)->post("https://graph.facebook.com/{$this->graphVersion}/{$pageId}/photo_stories", [
            'photo_id' => $photoId,
        ]);

        return $this->handlePostResponse($storyRes, $pageId);
    }

    /**
     * Publish Facebook Page Video Story.
     */
    protected function publishVideoStory(string $pageId, string $token, string $videoUrl, ?string $videoPath): PublishingResultDTO
    {
        $initRes = Http::withToken($token)->post("https://graph.facebook.com/{$this->graphVersion}/{$pageId}/video_stories", [
            'upload_phase' => 'start',
        ]);

        if (!$initRes->successful()) {
            return $this->handlePostResponse($initRes, $pageId);
        }

        $videoId = $initRes->json('video_id');
        $uploadUrl = $initRes->json('upload_url') ?: "https://rupload.facebook.com/video-upload/{$this->graphVersion}/{$videoId}";

        if ($videoPath && is_file($videoPath)) {
            $bytes = @file_get_contents($videoPath);
            $uploadRes = Http::withHeaders([
                'Authorization' => "OAuth {$token}",
                'offset' => '0',
                'file_size' => (string) strlen($bytes),
            ])->timeout(180)->withBody($bytes, 'application/octet-stream')->post($uploadUrl);
        } else {
            $uploadRes = Http::withHeaders([
                'Authorization' => "OAuth {$token}",
                'file_url' => $videoUrl,
            ])->timeout(120)->post($uploadUrl);
        }

        if (!$uploadRes->successful()) {
            return $this->handlePostResponse($uploadRes, $pageId);
        }

        $finishRes = Http::withToken($token)->post("https://graph.facebook.com/{$this->graphVersion}/{$pageId}/video_stories", [
            'upload_phase' => 'finish',
            'video_id' => $videoId,
        ]);

        return $this->handlePostResponse($finishRes, $pageId);
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
        $rawMessage = $errorData['message'] ?? 'Unknown Meta Graph API error.';
        $userMsg = $errorData['error_user_msg'] ?? null;

        // Human-friendly contextual diagnostics
        if ((int)$errorCode === 200 || str_contains(strtolower($rawMessage), 'permission')) {
            $errorMessage = "(#200) Permissions error: Missing 'pages_manage_posts' permission. Your Meta App requires 'pages_manage_posts' in Use Cases, your Facebook profile must be an App Admin/Developer, and the Page must be reconnected.";
        } elseif ((int)$errorCode === 190) {
            $errorMessage = "(#190) Invalid Access Token: Session has expired or was revoked. Please reconnect this Facebook account.";
        } elseif (in_array((int)$errorCode, [32, 613], true)) {
            $errorMessage = "(#{$errorCode}) Meta API rate limit exceeded. Please wait a few minutes before retrying.";
        } elseif (!empty($userMsg)) {
            $errorMessage = "(#{$errorCode}) {$rawMessage} - {$userMsg}";
        } else {
            $errorMessage = "(#{$errorCode}) {$rawMessage}";
        }

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

