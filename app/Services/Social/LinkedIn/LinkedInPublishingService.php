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

    protected function getApiVersion(): string
    {
        return env('LINKEDIN_API_VERSION') ?: now()->subMonths(2)->format('Ym');
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

            // Handle Videos, Images, or Link
            if (!empty($payload->mediaUrls)) {
                $firstUrl = $payload->mediaUrls[0];
                $firstPath = $payload->mediaPaths[0] ?? null;

                if ($this->isVideoMedia($firstUrl, $firstPath)) {
                    $videoUrn = $this->registerAndUploadVideo($authorUrn, $firstUrl, $accessToken, $firstPath);
                    if (!$videoUrn) {
                        return new PublishingResultDTO(
                            success: false,
                            errorMessage: "LinkedIn video upload failed during initialize/upload/finalize step.",
                            errorCode: 'VIDEO_UPLOAD_FAILED',
                            retriable: false
                        );
                    }
                    $postData['content'] = [
                        'media' => [
                            'title' => \Illuminate\Support\Str::limit($payload->caption ?: 'Video', 80),
                            'id' => $videoUrn,
                        ],
                    ];
                } else {
                    $imageUrns = [];
                    foreach (array_slice($payload->mediaUrls, 0, 9) as $idx => $imageUrl) {
                        $localPath = $payload->mediaPaths[$idx] ?? null;
                        $imageUrn = $this->registerAndUploadImage($authorUrn, $imageUrl, $accessToken, $localPath);
                        if ($imageUrn) {
                            $imageUrns[] = ['id' => $imageUrn];
                        }
                    }

                    if (empty($imageUrns)) {
                        return new PublishingResultDTO(
                            success: false,
                            errorMessage: "LinkedIn image upload failed before publishing post.",
                            errorCode: 'IMAGE_UPLOAD_FAILED',
                            retriable: false
                        );
                    }

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
                    'LinkedIn-Version' => $this->getApiVersion(),
                    'X-Restli-Protocol-Version' => '2.0.0',
                    'Content-Type' => 'application/json',
                ])
                ->post("{$this->restApiBase}/posts", $postData);

            // Fallback to unversioned v2/ugcPosts if LinkedIn rejects version with 426
            if ($response->status() === 426) {
                $ugcPayload = [
                    'author' => $authorUrn,
                    'lifecycleState' => 'PUBLISHED',
                    'specificContent' => [
                        'com.linkedin.ugc.ShareContent' => [
                            'shareCommentary' => ['text' => $payload->caption],
                            'shareMediaCategory' => !empty($payload->linkUrl) ? 'ARTICLE' : 'NONE',
                        ],
                    ],
                    'visibility' => [
                        'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
                    ],
                ];

                if (!empty($payload->linkUrl)) {
                    $ugcPayload['specificContent']['com.linkedin.ugc.ShareContent']['media'] = [[
                        'status' => 'READY',
                        'originalUrl' => $payload->linkUrl,
                    ]];
                }

                $ugcRes = Http::withToken($accessToken)
                    ->withHeaders([
                        'X-Restli-Protocol-Version' => '2.0.0',
                        'Content-Type' => 'application/json',
                    ])
                    ->post('https://api.linkedin.com/v2/ugcPosts', $ugcPayload);

                if ($ugcRes->successful()) {
                    $postUrn = $ugcRes->json('id') ?? $ugcRes->header('x-restli-id') ?? 'urn:li:share:' . time();
                    return new PublishingResultDTO(
                        success: true,
                        providerPostId: $postUrn,
                        postUrl: "https://www.linkedin.com/feed/update/{$postUrn}",
                        rawResponse: $ugcRes->json() ?? []
                    );
                }
            }

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
    protected function registerAndUploadImage(string $ownerUrn, string $imageUrl, string $accessToken, ?string $localPath = null): ?string
    {
        try {
            $initResponse = Http::withToken($accessToken)
                ->withHeaders([
                    'LinkedIn-Version' => $this->getApiVersion(),
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

            // Read local file directly when available, fallback to URL
            $fileContents = ($localPath && is_file($localPath))
                ? @file_get_contents($localPath)
                : @file_get_contents($imageUrl);
            if ($fileContents === false || empty($fileContents)) {
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

    /**
     * Upload a video to LinkedIn via /rest/videos initialize -> PUT chunk(s) -> finalize flow.
     */
    protected function registerAndUploadVideo(string $ownerUrn, string $videoUrl, string $accessToken, ?string $localPath = null): ?string
    {
        try {
            $fileContents = ($localPath && is_file($localPath))
                ? @file_get_contents($localPath)
                : Http::timeout(120)->get($videoUrl)->body();

            if ($fileContents === false || empty($fileContents)) {
                Log::warning("LinkedIn video read failed: {$videoUrl}");
                return null;
            }

            $fileSize = strlen($fileContents);
            $version = $this->getApiVersion();

            $initResponse = Http::withToken($accessToken)
                ->withHeaders([
                    'LinkedIn-Version' => $version,
                    'X-Restli-Protocol-Version' => '2.0.0',
                    'Content-Type' => 'application/json',
                ])
                ->post("{$this->restApiBase}/videos?action=initializeUpload", [
                    'initializeUploadRequest' => [
                        'owner' => $ownerUrn,
                        'fileSizeBytes' => $fileSize,
                        'uploadCaptions' => false,
                        'uploadThumbnail' => false,
                    ],
                ]);

            if (!$initResponse->successful()) {
                Log::warning("LinkedIn video init failed: " . $initResponse->body());
                return null;
            }

            $value = $initResponse->json('value') ?? [];
            $videoUrn = $value['video'] ?? null;
            $uploadToken = $value['uploadToken'] ?? '';
            $instructions = $value['uploadInstructions'] ?? [];

            if (!$videoUrn || empty($instructions)) {
                return null;
            }

            $uploadedPartIds = [];
            foreach ($instructions as $inst) {
                $firstByte = (int) ($inst['firstByte'] ?? 0);
                $lastByte = (int) ($inst['lastByte'] ?? ($fileSize - 1));
                $uploadUrl = $inst['uploadUrl'] ?? null;
                if (!$uploadUrl) continue;

                $chunk = substr($fileContents, $firstByte, ($lastByte - $firstByte) + 1);
                $putRes = Http::timeout(180)
                    ->withBody($chunk, 'application/octet-stream')
                    ->put($uploadUrl);

                if (!$putRes->successful()) {
                    Log::warning("LinkedIn video chunk upload failed: " . $putRes->status());
                    return null;
                }

                $etag = trim((string) $putRes->header('ETag'), '"');
                if (!empty($etag)) {
                    $uploadedPartIds[] = $etag;
                }
            }

            $finalizeRes = Http::withToken($accessToken)
                ->withHeaders([
                    'LinkedIn-Version' => $version,
                    'X-Restli-Protocol-Version' => '2.0.0',
                    'Content-Type' => 'application/json',
                ])
                ->post("{$this->restApiBase}/videos?action=finalizeUpload", [
                    'finalizeUploadRequest' => [
                        'video' => $videoUrn,
                        'uploadToken' => $uploadToken,
                        'uploadedPartIds' => $uploadedPartIds,
                    ],
                ]);

            if (!$finalizeRes->successful()) {
                Log::warning("LinkedIn video finalize failed: " . $finalizeRes->body());
                return null;
            }

            return $videoUrn;
        } catch (Exception $e) {
            Log::warning("LinkedIn video upload error: " . $e->getMessage());
            return null;
        }
    }

    protected function isVideoMedia(string $url, ?string $localPath = null): bool
    {
        $path = $localPath ?: (parse_url($url, PHP_URL_PATH) ?: $url);
        return (bool) preg_match('/\.(mp4|mov|webm|avi|m4v|mkv)$/i', $path);
    }
}
