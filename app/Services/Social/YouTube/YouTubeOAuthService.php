<?php

namespace App\Services\Social\YouTube;

use App\Enums\SocialPlatform;
use App\Models\OAuthTransaction;
use App\Services\Social\Contracts\OAuthProviderInterface;
use App\Services\Social\Contracts\ProfileDiscoveryInterface;
use App\Services\Social\DTOs\DiscoveredProfileDTO;
use App\Services\Social\DTOs\OAuthTokenDTO;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YouTubeOAuthService implements OAuthProviderInterface, ProfileDiscoveryInterface
{
    protected string $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth';
    protected string $tokenUrl = 'https://oauth2.googleapis.com/token';

    public function getPlatform(): SocialPlatform
    {
        return SocialPlatform::YOUTUBE;
    }

    public function getAuthorizationUrl(OAuthTransaction $transaction): string
    {
        $clientId = config('services.google.client_id', env('GOOGLE_CLIENT_ID', 'mock_google_client_id'));
        $redirectUri = route('oauth.callback', ['platform' => 'youtube']);

        $scopes = [
            'openid',
            'profile',
            'email',
            'https://www.googleapis.com/auth/youtube.upload',
            'https://www.googleapis.com/auth/youtube.readonly',
        ];

        $params = [
            'response_type' => 'code',
            'client_id' => $clientId,
            'redirect_uri' => $transaction->redirect_url ?? $redirectUri,
            'state' => $transaction->state_token ?? $transaction->state,
            'scope' => implode(' ', $scopes),
            'access_type' => 'offline',
            'prompt' => 'consent',
        ];

        return $this->authUrl . '?' . http_build_query($params);
    }

    public function handleCallback(string $code, OAuthTransaction $transaction): OAuthTokenDTO
    {
        $clientId = config('services.google.client_id', env('GOOGLE_CLIENT_ID', 'mock_google_client_id'));
        $clientSecret = config('services.google.client_secret', env('GOOGLE_CLIENT_SECRET', 'mock_google_secret'));
        $redirectUri = route('oauth.callback', ['platform' => 'youtube']);

        // Mock token for testing
        if (app()->environment('testing') && !config('services.youtube.live_oauth', false)) {
            return new OAuthTokenDTO(
                accessToken: 'mock_youtube_access_token_' . time(),
                refreshToken: 'mock_youtube_refresh_token',
                expiresAt: now()->addHours(1),
                scopes: ['https://www.googleapis.com/auth/youtube.upload', 'https://www.googleapis.com/auth/youtube.readonly'],
                accountId: 'mock_youtube_channel_123',
                accountName: 'Epic Videos Official Channel',
                accountEmail: 'creator@example.com',
                raw: ['id' => 'mock_youtube_channel_123']
            );
        }

        $response = Http::asForm()->post($this->tokenUrl, [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);

        if ($response->failed()) {
            throw new Exception("Google/YouTube token exchange failed: " . $response->body());
        }

        $data = $response->json();
        $accessToken = $data['access_token'];

        // Get user profile
        $userResponse = Http::withToken($accessToken)->get('https://www.googleapis.com/oauth2/v2/userinfo');
        $userData = $userResponse->json() ?? [];

        return new OAuthTokenDTO(
            accessToken: $accessToken,
            refreshToken: $data['refresh_token'] ?? null,
            expiresAt: isset($data['expires_in']) ? now()->addSeconds((int)$data['expires_in']) : now()->addHours(1),
            scopes: explode(' ', $data['scope'] ?? ''),
            accountId: $userData['id'] ?? 'unknown_google_user',
            accountName: $userData['name'] ?? 'Google/YouTube User',
            accountEmail: $userData['email'] ?? null,
            raw: $data
        );
    }

    public function refreshToken(string $refreshToken): OAuthTokenDTO
    {
        $clientId = config('services.google.client_id', env('GOOGLE_CLIENT_ID', 'mock_google_client_id'));
        $clientSecret = config('services.google.client_secret', env('GOOGLE_CLIENT_SECRET', 'mock_google_secret'));

        if (app()->environment('testing') && !config('services.youtube.live_oauth', false)) {
            return new OAuthTokenDTO(
                accessToken: 'mock_refreshed_youtube_token_' . time(),
                refreshToken: $refreshToken,
                expiresAt: now()->addHours(1),
                scopes: ['https://www.googleapis.com/auth/youtube.upload'],
                raw: ['refreshed' => true]
            );
        }

        $response = Http::asForm()->post($this->tokenUrl, [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
        ]);

        if ($response->failed()) {
            throw new Exception("YouTube token refresh failed: " . $response->body());
        }

        $data = $response->json();

        return new OAuthTokenDTO(
            accessToken: $data['access_token'],
            refreshToken: $data['refresh_token'] ?? $refreshToken,
            expiresAt: isset($data['expires_in']) ? now()->addSeconds((int)$data['expires_in']) : now()->addHours(1),
            scopes: isset($data['scope']) ? explode(' ', $data['scope']) : [],
            raw: $data
        );
    }

    public function discoverProfiles(OAuthTokenDTO $token): array
    {
        $accessToken = $token->accessToken;

        if (app()->environment('testing') && !config('services.youtube.live_oauth', false)) {
            return [
                new DiscoveredProfileDTO(
                    platform: SocialPlatform::YOUTUBE->value,
                    profileId: 'UC_mock_channel_alpha',
                    name: 'Epic Food & Travel Channel',
                    username: 'EpicFoodTravel',
                    avatarUrl: 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=100',
                    profileUrl: 'https://youtube.com/@EpicFoodTravel',
                    accessToken: $accessToken,
                    capabilities: [
                        'supports_video' => true,
                        'supports_shorts' => true,
                        'supports_scheduling' => true,
                        'supports_comments' => true,
                        'supports_analytics' => true,
                    ]
                )
            ];
        }

        $profiles = [];

        try {
            $response = Http::withToken($accessToken)->get('https://www.googleapis.com/youtube/v3/channels', [
                'part' => 'snippet,contentDetails',
                'mine' => 'true',
            ]);

            if ($response->successful()) {
                $items = $response->json('items') ?? [];
                foreach ($items as $item) {
                    $snippet = $item['snippet'] ?? [];
                    $channelId = $item['id'];
                    $customUrl = $snippet['customUrl'] ?? $snippet['title'];

                    $profiles[] = new DiscoveredProfileDTO(
                        platform: SocialPlatform::YOUTUBE->value,
                        profileId: $channelId,
                        name: $snippet['title'] . ' (YouTube Channel)',
                        username: ltrim($customUrl, '@'),
                        avatarUrl: $snippet['thumbnails']['default']['url'] ?? null,
                        profileUrl: "https://www.youtube.com/channel/{$channelId}",
                        accessToken: $accessToken,
                        capabilities: [
                            'supports_video' => true,
                            'supports_shorts' => true,
                            'supports_scheduling' => true,
                            'supports_comments' => true,
                            'supports_analytics' => true,
                        ],
                        metadata: [
                            'custom_url' => $customUrl,
                        ]
                    );
                }
            }
        } catch (Exception $e) {
            Log::error("YouTube channel discovery failed: " . $e->getMessage());
        }

        return $profiles;
    }
}
