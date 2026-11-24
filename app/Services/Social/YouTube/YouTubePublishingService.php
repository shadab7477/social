<?php

namespace App\Services\Social\YouTube;

use App\Enums\SocialPlatform;
use App\Models\SocialProfile;
use App\Services\Social\Contracts\PublishingProviderInterface;
use App\Services\Social\Contracts\SocialProviderInterface;
use App\Services\Social\DTOs\PublishingPayloadDTO;
use App\Services\Social\DTOs\PublishingResultDTO;
use App\Services\Social\Registry\PlatformCapabilityRegistry;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class YouTubePublishingService implements SocialProviderInterface, PublishingProviderInterface
{
    public function __construct(
        protected YouTubeQuotaTrackerService $quotaTracker
    ) {}

    public function getPlatform(): SocialPlatform
    {
        return SocialPlatform::YOUTUBE;
    }

    public function getCapabilities(): array
    {
        return PlatformCapabilityRegistry::getCapabilities(SocialPlatform::YOUTUBE);
    }

    public function publish(SocialProfile $profile, PublishingPayloadDTO $payload): PublishingResultDTO
    {
        $accessToken = $profile->access_token ?? $profile->connection?->access_token;
        if (!$accessToken) {
            return new PublishingResultDTO(
                success: false,
                errorMessage: "Missing YouTube / Google access token.",
                errorCode: 'TOKEN_MISSING',
                retriable: false
            );
        }

        // 1. Quota Check
        if (!$this->quotaTracker->canUploadVideo($profile->workspace_id)) {
            return new PublishingResultDTO(
                success: false,
                errorMessage: "YouTube daily project quota exceeded (Limit: 10,000 units/day). Try again tomorrow.",
                errorCode: 'QUOTA_EXCEEDED',
                retriable: false
            );
        }

        // 2. Validate video presence
        if (empty($payload->mediaUrls)) {
            return new PublishingResultDTO(
                success: false,
                errorMessage: "YouTube publishing requires an attached video asset.",
                errorCode: 'VIDEO_REQUIRED',
                retriable: false
            );
        }

        $videoUrl = $payload->mediaUrls[0];

        // Testing / Mock simulation
        if (app()->environment('testing') && !config('services.youtube.live_publishing', false)) {
            $mockVideoId = 'yt_' . Str::random(11);
            $this->quotaTracker->recordUsage($profile->workspace_id);

            return new PublishingResultDTO(
                success: true,
                providerPostId: $mockVideoId,
                postUrl: "https://www.youtube.com/watch?v={$mockVideoId}",
                rawResponse: ['id' => $mockVideoId]
            );
        }

        try {
            $title = Str::limit($payload->caption, 100);
            $description = $payload->caption;
            $privacy = $payload->platformSettings['privacy_status'] ?? 'public';

            // Step 1: Initialize Resumable Session
            $initResponse = Http::withToken($accessToken)
                ->withHeaders([
                    'X-Upload-Content-Type' => 'video/*',
                    'Content-Type' => 'application/json; charset=UTF-8',
                ])
                ->post('https://www.googleapis.com/upload/youtube/v3/videos?uploadType=resumable&part=snippet,status', [
                    'snippet' => [
                        'title' => $title,
                        'description' => $description,
                        'categoryId' => '22',
                    ],
                    'status' => [
                        'privacyStatus' => $privacy,
                        'selfDeclaredMadeForKids' => false,
                    ],
                ]);

            if ($initResponse->status() !== 200) {
                return new PublishingResultDTO(
                    success: false,
                    errorMessage: "YouTube Resumable Upload Init failed: " . $initResponse->body(),
                    errorCode: (string) $initResponse->status(),
                    retriable: true
                );
            }

            $uploadUrl = $initResponse->header('Location');
            if (!$uploadUrl) {
                return new PublishingResultDTO(
                    success: false,
                    errorMessage: "YouTube Location header missing from upload initialization.",
                    errorCode: 'NO_LOCATION',
                    retriable: true
                );
            }

            // Step 2: Fetch and Upload Video Binary Stream
            $videoBytes = Http::timeout(120)->get($videoUrl)->body();
            $uploadResponse = Http::withBody($videoBytes, 'video/*')->put($uploadUrl);

            if ($uploadResponse->successful()) {
                $data = $uploadResponse->json();
                $videoId = $data['id'] ?? null;

                $this->quotaTracker->recordUsage($profile->workspace_id);

                return new PublishingResultDTO(
                    success: true,
                    providerPostId: $videoId,
                    postUrl: "https://www.youtube.com/watch?v={$videoId}",
                    rawResponse: $data
                );
            }

            return new PublishingResultDTO(
                success: false,
                errorMessage: "YouTube Video Binary Upload failed: " . $uploadResponse->body(),
                errorCode: (string) $uploadResponse->status(),
                retriable: true
            );

        } catch (Exception $e) {
            Log::error("YouTube publishing exception: " . $e->getMessage());

            return new PublishingResultDTO(
                success: false,
                errorMessage: "YouTube Publishing Exception: " . $e->getMessage(),
                errorCode: 'EXCEPTION',
                retriable: true
            );
        }
    }

    public function checkPublishStatus(SocialProfile $profile, string $providerPostId): PublishingResultDTO
    {
        return new PublishingResultDTO(
            success: true,
            providerPostId: $providerPostId,
            postUrl: "https://www.youtube.com/watch?v={$providerPostId}"
        );
    }
}
