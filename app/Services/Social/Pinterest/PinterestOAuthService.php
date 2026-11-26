<?php

namespace App\Services\Social\Pinterest;

use App\Enums\SocialPlatform;
use App\Models\OAuthTransaction;
use App\Services\Social\Contracts\OAuthProviderInterface;
use App\Services\Social\Contracts\ProfileDiscoveryInterface;
use App\Services\Social\DTOs\DiscoveredProfileDTO;
use App\Services\Social\DTOs\OAuthTokenDTO;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PinterestOAuthService implements OAuthProviderInterface, ProfileDiscoveryInterface
{
    protected string $authUrl = 'https://www.pinterest.com/oauth/';

    protected function apiBase(): string
    {
        return rtrim(config('services.pinterest.api_base', env('PINTEREST_API_BASE', 'https://api.pinterest.com/v5')), '/');
    }

    public function getPlatform(): SocialPlatform
    {
        return SocialPlatform::PINTEREST;
    }

    public function getAuthorizationUrl(OAuthTransaction $transaction): string
    {
        $clientId = config('services.pinterest.client_id', env('PINTEREST_CLIENT_ID', 'mock_pinterest_client_id'));
        $redirectUri = $transaction->redirect_url ?? route('oauth.callback', ['platform' => 'pinterest']);

        $scopes = [
            'boards:read',
            'boards:write',
            'pins:read',
            'pins:write',
            'user_accounts:read',
        ];

        $params = [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => implode(',', $scopes),
            'state' => $transaction->state_token ?? $transaction->state,
        ];

        return $this->authUrl . '?' . http_build_query($params);
    }

    public function handleCallback(string $code, OAuthTransaction $transaction): OAuthTokenDTO
    {
        $clientId = config('services.pinterest.client_id', env('PINTEREST_CLIENT_ID', 'mock_pinterest_client_id'));
        $clientSecret = config('services.pinterest.client_secret', env('PINTEREST_CLIENT_SECRET', 'mock_pinterest_secret'));
        $redirectUri = $transaction->redirect_url ?? route('oauth.callback', ['platform' => 'pinterest']);

        if (app()->environment('testing') && !config('services.pinterest.live_oauth', false)) {
            return new OAuthTokenDTO(
                accessToken: 'mock_pinterest_access_token_' . time(),
                refreshToken: 'mock_pinterest_refresh_token',
                expiresAt: now()->addDays(30),
                scopes: ['boards:read', 'pins:read', 'pins:write', 'user_accounts:read'],
                accountId: 'pin_user_mock_123',
                accountName: 'Design Studio',
                raw: ['username' => 'designstudio']
            );
        }

        $response = Http::withBasicAuth($clientId, $clientSecret)
            ->asForm()
            ->post($this->apiBase() . '/oauth/token', [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $redirectUri,
            ]);

        if ($response->failed()) {
            throw new Exception("Pinterest token exchange failed: " . $response->body());
        }

        $data = $response->json();
        $accessToken = $data['access_token'];

        $userRes = Http::withToken($accessToken)->get($this->apiBase() . '/user_account');
        $userData = $userRes->json() ?? [];
        $username = $userData['username'] ?? 'pinterest_user';

        return new OAuthTokenDTO(
            accessToken: $accessToken,
            refreshToken: $data['refresh_token'] ?? null,
            expiresAt: isset($data['expires_in']) ? now()->addSeconds((int) $data['expires_in']) : now()->addDays(30),
            scopes: isset($data['scope']) ? explode(' ', (string) $data['scope']) : ['pins:write', 'boards:read'],
            accountId: (string) ($userData['id'] ?? $username),
            accountName: $userData['business_name'] ?? $username,
            raw: $userData
        );
    }

    public function refreshToken(string $refreshToken): OAuthTokenDTO
    {
        $clientId = config('services.pinterest.client_id', env('PINTEREST_CLIENT_ID', 'mock_pinterest_client_id'));
        $clientSecret = config('services.pinterest.client_secret', env('PINTEREST_CLIENT_SECRET', 'mock_pinterest_secret'));

        if (app()->environment('testing') && !config('services.pinterest.live_oauth', false)) {
            return new OAuthTokenDTO(
                accessToken: 'mock_refreshed_pinterest_token_' . time(),
                refreshToken: $refreshToken,
                expiresAt: now()->addDays(30),
                scopes: ['pins:write', 'boards:read'],
                raw: ['refreshed' => true]
            );
        }

        $response = Http::withBasicAuth($clientId, $clientSecret)
            ->asForm()
            ->post($this->apiBase() . '/oauth/token', [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
            ]);

        if ($response->failed()) {
            throw new Exception("Pinterest token refresh failed: " . $response->body());
        }

        $data = $response->json();

        return new OAuthTokenDTO(
            accessToken: $data['access_token'],
            refreshToken: $data['refresh_token'] ?? $refreshToken,
            expiresAt: isset($data['expires_in']) ? now()->addSeconds((int) $data['expires_in']) : now()->addDays(30),
            scopes: isset($data['scope']) ? explode(' ', (string) $data['scope']) : ['pins:write', 'boards:read'],
            raw: $data
        );
    }

    public function discoverProfiles(OAuthTokenDTO $token): array
    {
        $accessToken = $token->accessToken;

        if (app()->environment('testing') && !config('services.pinterest.live_oauth', false)) {
            return [
                new DiscoveredProfileDTO(
                    platform: SocialPlatform::PINTEREST->value,
                    profileId: 'pin_user_mock_123',
                    name: 'Design Studio (Pinterest)',
                    username: 'designstudio',
                    accessToken: $accessToken,
                    capabilities: [
                        'supports_images' => true,
                        'supports_video' => true,
                        'requires_board' => true,
                    ],
                    metadata: [
                        'boards' => [
                            ['id' => 'board_mock_1', 'name' => 'Inspiration & Tips'],
                            ['id' => 'board_mock_2', 'name' => 'Product Showcase'],
                        ],
                    ]
                ),
            ];
        }

        $profiles = [];
        try {
            $userRes = Http::withToken($accessToken)->get($this->apiBase() . '/user_account');
            $user = $userRes->successful() ? ($userRes->json() ?? []) : [];

            $boardsRes = Http::withToken($accessToken)->get($this->apiBase() . '/boards', ['page_size' => 50]);
            $boards = [];
            if ($boardsRes->successful()) {
                foreach ($boardsRes->json('items') ?? [] as $b) {
                    $boards[] = ['id' => (string) $b['id'], 'name' => $b['name'] ?? 'Untitled Board'];
                }
            }

            $username = $user['username'] ?? $token->accountId ?? 'pinterest';
            $profiles[] = new DiscoveredProfileDTO(
                platform: SocialPlatform::PINTEREST->value,
                profileId: (string) ($user['id'] ?? $username),
                name: ($user['business_name'] ?? $username) . ' (Pinterest)',
                username: $username,
                avatarUrl: $user['profile_image'] ?? null,
                profileUrl: "https://www.pinterest.com/{$username}/",
                accessToken: $accessToken,
                capabilities: [
                    'supports_images' => true,
                    'supports_video' => true,
                    'requires_board' => true,
                ],
                metadata: ['boards' => $boards]
            );
        } catch (Exception $e) {
            Log::error("Pinterest discovery error: " . $e->getMessage());
        }

        return $profiles;
    }
}
