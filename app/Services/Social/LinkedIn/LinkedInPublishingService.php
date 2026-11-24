<?php

namespace App\Services\Social\LinkedIn;

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

class LinkedInPublishingService implements SocialProviderInterface, PublishingProviderInterface
{
    protected string $restApiBase = 'https://api.linkedin.com/rest';

    public function getPlatform(): SocialPlatform
    {
        return SocialPlatform::LINKEDIN;
    }

    public function getCapabilities(): array
    {
        return PlatformCapabilityRegistry::getCapabilities(SocialPlatform::LINKEDIN);
    }

    public function publish(SocialProfile $profile, PublishingPayloadDTO $payload): PublishingResultDTO
    {
        $accessToken = $profile->access_token ?? $profile->connection?->access_token;
        if (!$accessToken) {
            return new PublishingResultDTO(
                success: false,
                errorMessage: "Missing LinkedIn access token.",
                errorCode: 'TOKEN_MISSING',
                retriable: false
            );
        }

        $authorUrn = $profile->profile_id; // e.g. urn:li:person:... or urn:li:organization:...

        // Offline / testing simulation
        if (app()->environment('testing') && !config('services.linkedin.live_publishing', false)) {
            $mockPostId = 'urn:li:share:' . rand(100000000, 999999999);
            return new PublishingResultDTO(
                success: true,
                providerPostId: $mockPostId,
                postUrl: "https://www.linkedin.com/feed/update/{$mockPostId}",
                rawResponse: ['id' => $mockPostId]
            );
        }

        try {
            $postData = [
                'author' => $authorUrn,
                'commentary' => $payload->caption,
                'visibility' => 'PUBLIC',
                'distribution' => [
                    'feedDistribution' => 'MAIN_FEED',
                    'targetEntities' => [],
                    'thirdPartyDistributionChannels' => [],
                ],
                'lifecycleState' => 'PUBLISHED',
                'isReshareDisabledByAuthor' => false,
            ];

            // Handle Link or Images
            if (!empty($payload->mediaUrls)) {
                $imageUrns = [];
                foreach (array_slice($payload->mediaUrls, 0, 9) as $imageUrl) {
                    $imageUrn = $this->registerAndUploadImage($authorUrn, $imageUrl, $accessToken);
                    if ($imageUrn) {
                        $imageUrns[] = ['id' => $imageUrn];
                    }
                }

                if (!empty($imageUrns)) {
                    if (count($imageUrns) === 1) {
                        $postData['content'] = [
                            'media' => [
                                'title' => 'Image',
                                'id' => $imageUrns[0]['id'],
                            ],
                        ];
                    } else {
                        $postData['content'] = [
                            'multiImage' => [
                                'images' => $imageUrns,
                            ],
                        ];
                    }
                }
            } elseif (!empty($payload->linkUrl)) {
                $postData['content'] = [
                    'article' => [
                        'source' => $payload->linkUrl,
                        'title' => 'Link',
                    ],
                ];
            }

            $response = Http::withToken($accessToken)
                ->withHeaders([
                    'LinkedIn-Version' => '202401',
                    'X-Restli-Protocol-Version' => '2.0.0',
                    'Content-Type' => 'application/json',
                ])
                ->post("{$this->restApiBase}/posts", $postData);

            if ($response->status() === 201) {
                $postUrn = $response->header('x-restli-id') ?? $response->header('x-linkedin-id') ?? 'urn:li:share:' . time();
                return new PublishingResultDTO(
                    success: true,
                    providerPostId: $postUrn,
                    postUrl: "https://www.linkedin.com/feed/update/{$postUrn}",
                    rawResponse: ['headers' => $response->headers()]
                );
            }

            $statusCode = $response->status();
            $body = $response->json() ?? [];
            $errorMsg = $body['message'] ?? $response->body();

            return new PublishingResultDTO(
                success: false,
                errorMessage: "LinkedIn Posts API Error ({$statusCode}): {$errorMsg}",
                errorCode: (string) $statusCode,
                retriable: in_array($statusCode, [429, 500, 502, 503, 504], true),
                rawResponse: $body
            );

        } catch (Exception $e) {
            Log::error("LinkedIn publishing exception: " . $e->getMessage());

            return new PublishingResultDTO(
                success: false,
                errorMessage: "LinkedIn Publishing Exception: " . $e->getMessage(),
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
            postUrl: "https://www.linkedin.com/feed/update/{$providerPostId}"
        );
    }

    /**
     * Upload an image to LinkedIn and retrieve its permanent asset URN.
     */
    protected function registerAndUploadImage(string $ownerUrn, string $imageUrl, string $accessToken): ?string
    {
        try {
            $initResponse = Http::withToken($accessToken)
                ->withHeaders([
                    'LinkedIn-Version' => '202401',
                    'X-Restli-Protocol-Version' => '2.0.0',
                    'Content-Type' => 'application/json',
                ])
                ->post("{$this->restApiBase}/images?action=initializeUpload", [
                    'initializeUploadRequest' => [
                        'owner' => $ownerUrn,
                    ],
                ]);

            if (!$initResponse->successful()) {
                return null;
            }

            $initData = $initResponse->json('value') ?? [];
            $uploadUrl = $initData['uploadUrl'] ?? null;
            $imageUrn = $initData['image'] ?? null;

            if (!$uploadUrl || !$imageUrn) {
                return null;
            }

            // Download file and PUT to uploadUrl
            $fileContents = file_get_contents($imageUrl);
            if ($fileContents === false) {
                return null;
            }

            $uploadResponse = Http::withBody($fileContents, 'application/octet-stream')
                ->put($uploadUrl);

            return $uploadResponse->successful() ? $imageUrn : null;
        } catch (Exception $e) {
            Log::warning("LinkedIn image upload error: " . $e->getMessage());
            return null;
        }
    }
}
