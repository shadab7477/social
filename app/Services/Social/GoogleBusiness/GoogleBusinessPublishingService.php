<?php

namespace App\Services\Social\GoogleBusiness;

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

class GoogleBusinessPublishingService implements SocialProviderInterface, PublishingProviderInterface
{
    protected string $apiBase = 'https://mybusiness.googleapis.com/v4';

    public function getPlatform(): SocialPlatform
    {
        return SocialPlatform::GOOGLE_BUSINESS;
    }

    public function getCapabilities(): array
    {
        return PlatformCapabilityRegistry::getCapabilities(SocialPlatform::GOOGLE_BUSINESS);
    }

    public function publish(SocialProfile $profile, PublishingPayloadDTO $payload): PublishingResultDTO
    {
        $accessToken = $profile->access_token ?? $profile->connection?->access_token;
        if (!$accessToken) {
            return new PublishingResultDTO(
                success: false,
                errorMessage: "Missing Google Business access token.",
                errorCode: 'TOKEN_MISSING',
                retriable: false
            );
        }

        $locationName = $profile->profile_id; // e.g. accounts/123/locations/456 or locations/456

        // Offline / testing simulation
        if (app()->environment('testing') && !config('services.google_business.live_publishing', false)) {
            $mockPostId = $locationName . '/localPosts/' . rand(10000000, 99999999);
            return new PublishingResultDTO(
                success: true,
                providerPostId: $mockPostId,
                postUrl: "https://business.google.com",
                rawResponse: [
                    'name' => $mockPostId,
                    'summary' => $payload->caption,
                    'state' => 'LIVE',
                ]
            );
        }

        // Proactively refresh expired Google OAuth token if refresh_token is available
        $connection = $profile->connection;
        if ($connection && $connection->refresh_token && ($connection->token_expires_at === null || $connection->token_expires_at->isPast() || $connection->token_expires_at->diffInMinutes(now(), false) > -5)) {
            try {
                $refreshed = app(GoogleBusinessOAuthService::class)->refreshToken($connection->refresh_token);
                $accessToken = $refreshed->accessToken;
                $connection->update([
                    'access_token' => $refreshed->accessToken,
                    'refresh_token' => $refreshed->refreshToken ?? $connection->refresh_token,
                    'token_expires_at' => $refreshed->expiresAt,
                ]);
                $profile->update(['access_token' => $refreshed->accessToken]);
            } catch (\Throwable $refreshEx) {
                Log::warning('Google Business token proactive refresh failed: ' . $refreshEx->getMessage());
            }
        }

        try {
            $topicType = $payload->platformSettings['topic_type'] ?? 'STANDARD';
            $actionType = $payload->platformSettings['action_type'] ?? 'LEARN_MORE';

            $localPost = [
                'languageCode' => 'en-US',
                'summary' => $payload->caption,
                'topicType' => $topicType,
            ];

            // Add Call-to-Action if link is provided
            if (!empty($payload->linkUrl)) {
                $localPost['callToAction'] = [
                    'actionType' => $actionType,
                    'url' => $payload->linkUrl,
                ];
            }

            // Add Media (Photos)
            if (!empty($payload->mediaUrls)) {
                $mediaItems = [];
                foreach (array_slice($payload->mediaUrls, 0, 10) as $url) {
                    $mediaItems[] = [
                        'mediaFormat' => 'PHOTO',
                        'sourceUrl' => $url,
                    ];
                }
                $localPost['media'] = $mediaItems;
            }

            $endpoint = "{$this->apiBase}/{$locationName}/localPosts";

            $response = Http::withToken($accessToken)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($endpoint, $localPost);

            if ($response->successful()) {
                $data = $response->json();
                $postId = $data['name'] ?? ('localPost_' . time());
                $postUrl = $data['searchUrl'] ?? "https://business.google.com";

                return new PublishingResultDTO(
                    success: true,
                    providerPostId: $postId,
                    postUrl: $postUrl,
                    rawResponse: $data
                );
            }

            $status = $response->status();
            $body = $response->body();
            $json = $response->json();
            $errorMessage = $json['error']['message'] ?? $body;
            $retriable = in_array($status, [429, 500, 502, 503, 504]);

            Log::error('Google Business Profile publishing failed', [
                'profile_id' => $profile->id,
                'status' => $status,
                'error' => $errorMessage,
            ]);

            return new PublishingResultDTO(
                success: false,
                errorMessage: "Google Business publish error: {$errorMessage}",
                errorCode: (string) $status,
                retriable: $retriable,
                rawResponse: $json ?? ['body' => $body]
            );
        } catch (Exception $e) {
            Log::error('Google Business Profile publish exception: ' . $e->getMessage());

            return new PublishingResultDTO(
                success: false,
                errorMessage: $e->getMessage(),
                errorCode: 'EXCEPTION',
                retriable: false
            );
        }
    }

    public function checkPublishStatus(SocialProfile $profile, string $providerPostId): PublishingResultDTO
    {
        return new PublishingResultDTO(
            success: true,
            providerPostId: $providerPostId,
            postUrl: "https://business.google.com"
        );
    }
}
