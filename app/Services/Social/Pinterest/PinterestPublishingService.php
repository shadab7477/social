<?php

namespace App\Services\Social\Pinterest;

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

class PinterestPublishingService implements SocialProviderInterface, PublishingProviderInterface
{
    protected function apiBase(): string
    {
        return rtrim(config('services.pinterest.api_base', env('PINTEREST_API_BASE', 'https://api.pinterest.com/v5')), '/');
    }

    public function getPlatform(): SocialPlatform
    {
        return SocialPlatform::PINTEREST;
    }

    public function getCapabilities(): array
    {
        return PlatformCapabilityRegistry::getCapabilities(SocialPlatform::PINTEREST);
    }

    public function publish(SocialProfile $profile, PublishingPayloadDTO $payload): PublishingResultDTO
    {
        $accessToken = $profile->access_token ?? $profile->connection?->access_token;
        if (!$accessToken) {
            return PublishingResultDTO::failed("Missing Pinterest access token.", 'TOKEN_MISSING', false);
        }

        if (empty($payload->mediaUrls)) {
            return PublishingResultDTO::failed("Pinterest Pins require an attached image or video.", 'MEDIA_REQUIRED', false);
        }

        if (app()->environment('testing') && !config('services.pinterest.live_publishing', false)) {
            $pinId = 'pin_' . Str::random(10);
            return PublishingResultDTO::successful($pinId, "https://www.pinterest.com/pin/{$pinId}/", ['id' => $pinId]);
        }

        // Proactive token refresh if expired
        $connection = $profile->connection;
        if ($connection && $connection->refresh_token && ($connection->token_expires_at === null || $connection->token_expires_at->isPast())) {
            try {
                $refreshed = app(PinterestOAuthService::class)->refreshToken($connection->refresh_token);
                $accessToken = $refreshed->accessToken;
                $connection->update([
                    'access_token' => $refreshed->accessToken,
                    'refresh_token' => $refreshed->refreshToken ?? $connection->refresh_token,
                    'token_expires_at' => $refreshed->expiresAt,
                ]);
                $profile->update(['access_token' => $refreshed->accessToken]);
            } catch (\Throwable $e) {
                Log::warning("Pinterest token refresh warning: " . $e->getMessage());
            }
        }

        try {
            $boardId = $payload->platformSettings['board_id']
                ?? ($profile->metadata['boards'][0]['id'] ?? null);

            if (empty($boardId)) {
                $boardsRes = Http::withToken($accessToken)->get($this->apiBase() . '/boards', ['page_size' => 10]);
                if ($boardsRes->successful()) {
                    $boardId = $boardsRes->json('items.0.id');
                }
            }

            // Create a default board if user has zero boards
            if (empty($boardId)) {
                $newBoardName = trim($payload->platformSettings['board_name'] ?? '') ?: 'PostSync Pins';
                $createBoardRes = Http::withToken($accessToken)->post($this->apiBase() . '/boards', [
                    'name' => $newBoardName,
                    'privacy' => 'PUBLIC',
                ]);
                if ($createBoardRes->successful()) {
                    $boardId = $createBoardRes->json('id');
                }
            }

            if (empty($boardId)) {
                return PublishingResultDTO::failed("No Pinterest Board selected or found on account.", 'BOARD_REQUIRED', false);
            }

            $mediaUrl = $payload->mediaUrls[0];
            $mediaPath = $payload->mediaPaths[0] ?? null;
            $title = Str::limit(trim($payload->platformSettings['title'] ?? '') ?: strtok($payload->caption, "\r\n") ?: 'New Pin', 100, '');

            $pinBody = [
                'board_id' => (string) $boardId,
                'title' => $title,
                'description' => Str::limit($payload->caption, 500, ''),
            ];

            if (!empty($payload->linkUrl)) {
                $pinBody['link'] = $payload->linkUrl;
            }

            $isVideo = (bool) preg_match('/\.(mp4|mov|webm|avi|m4v|mkv)$/i', $mediaPath ?: (parse_url($mediaUrl, PHP_URL_PATH) ?: $mediaUrl));

            if ($isVideo) {
                $mediaId = $this->uploadVideoToPinterest($accessToken, $mediaUrl, $mediaPath);
                if (!$mediaId) {
                    return PublishingResultDTO::failed("Pinterest video upload failed during /v5/media registration.", 'VIDEO_UPLOAD_FAILED', false);
                }
                $pinBody['media_source'] = [
                    'source_type' => 'video_id',
                    'media_id' => $mediaId,
                    'cover_image_key_frame_time_ms' => 1000,
                ];
            } elseif ($mediaPath && is_file($mediaPath)) {
                $mime = mime_content_type($mediaPath) ?: 'image/jpeg';
                $pinBody['media_source'] = [
                    'source_type' => 'image_base64',
                    'content_type' => $mime,
                    'data' => base64_encode(file_get_contents($mediaPath)),
                ];
            } else {
                $pinBody['media_source'] = [
                    'source_type' => 'image_url',
                    'url' => $mediaUrl,
                ];
            }

            $res = Http::withToken($accessToken)->timeout(30)->post($this->apiBase() . '/pins', $pinBody);

            if ($res->successful()) {
                $pinId = (string) ($res->json('id') ?? '');
                return PublishingResultDTO::successful($pinId, "https://www.pinterest.com/pin/{$pinId}/", $res->json() ?? []);
            }

            $err = $res->json('message') ?? $res->body();
            return PublishingResultDTO::failed("Pinterest API Error ({$res->status()}): {$err}", (string) $res->status(), $res->status() >= 500);

        } catch (Exception $e) {
            return PublishingResultDTO::failed("Pinterest Publishing Exception: " . $e->getMessage(), 'EXCEPTION', true);
        }
    }

    public function checkPublishStatus(SocialProfile $profile, string $providerPostId): PublishingResultDTO
    {
        return PublishingResultDTO::successful($providerPostId, "https://www.pinterest.com/pin/{$providerPostId}/");
    }

    protected function uploadVideoToPinterest(string $accessToken, string $videoUrl, ?string $localPath = null): ?string
    {
        $regRes = Http::withToken($accessToken)->post($this->apiBase() . '/media', [
            'media_type' => 'video',
        ]);

        if (!$regRes->successful()) {
            Log::warning("Pinterest /v5/media init failed: " . $regRes->body());
            return null;
        }

        $data = $regRes->json();
        $mediaId = $data['media_id'] ?? null;
        $uploadUrl = $data['upload_url'] ?? null;
        $params = $data['upload_parameters'] ?? [];

        if (!$mediaId || !$uploadUrl) {
            return null;
        }

        $videoBytes = ($localPath && is_file($localPath))
            ? @file_get_contents($localPath)
            : Http::timeout(120)->get($videoUrl)->body();

        if (empty($videoBytes)) {
            return null;
        }

        $multipart = [];
        foreach ($params as $k => $v) {
            $multipart[] = ['name' => $k, 'contents' => (string) $v];
        }
        $multipart[] = ['name' => 'file', 'contents' => $videoBytes, 'filename' => basename($localPath ?: 'video.mp4')];

        $uploadRes = Http::timeout(180)->asMultipart()->post($uploadUrl, $multipart);
        if (!$uploadRes->successful()) {
            Log::warning("Pinterest video S3 upload failed: " . $uploadRes->status());
            return null;
        }

        return $mediaId;
    }
}

