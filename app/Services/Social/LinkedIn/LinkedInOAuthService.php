<?php

namespace App\Services\Social\LinkedIn;

use App\Enums\SocialPlatform;
use App\Models\OAuthTransaction;
use App\Services\Social\Contracts\OAuthProviderInterface;
use App\Services\Social\Contracts\ProfileDiscoveryInterface;
use App\Services\Social\DTOs\DiscoveredProfileDTO;
use App\Services\Social\DTOs\OAuthTokenDTO;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LinkedInOAuthService implements OAuthProviderInterface, ProfileDiscoveryInterface
{
    protected string $authUrl = 'https://www.linkedin.com/oauth/v2/authorization';
    protected string $tokenUrl = 'https://www.linkedin.com/oauth/v2/accessToken';
    protected string $apiBase = 'https://api.linkedin.com/v2';

    public function getPlatform(): SocialPlatform
    {
        return SocialPlatform::LINKEDIN;
    }

    public function getAuthorizationUrl(OAuthTransaction $transaction): string
    {
        $clientId = config('services.linkedin.client_id', env('LINKEDIN_CLIENT_ID', 'mock_linkedin_client_id'));
        $redirectUri = route('oauth.callback', ['platform' => 'linkedin']);

        $scopes = [
            'openid',
            'profile',
            'email',
            'w_member_social',
        ];

        // Community Management API scopes require LinkedIn App Review; opt-in via .env
        if (filter_var(env('LINKEDIN_ORG_SCOPES', false), FILTER_VALIDATE_BOOLEAN)) {
            $scopes[] = 'w_organization_social';
            $scopes[] = 'r_organization_social';
        }

        $params = [
            'response_type' => 'code',
            'client_id' => $clientId,
            'redirect_uri' => $transaction->redirect_url ?? $redirectUri,
            'state' => $transaction->state_token ?? $transaction->state,
            'scope' => implode(' ', $scopes),
        ];

        return $this->authUrl . '?' . http_build_query($params);
    }

    public function handleCallback(string $code, OAuthTransaction $transaction): OAuthTokenDTO
    {
        $clientId = config('services.linkedin.client_id', env('LINKEDIN_CLIENT_ID', 'mock_linkedin_client_id'));
        $clientSecret = config('services.linkedin.client_secret', env('LINKEDIN_CLIENT_SECRET', 'mock_linkedin_secret'));
        $redirectUri = route('oauth.callback', ['platform' => 'linkedin']);

        // Offline / testing mock response
        if (app()->environment('testing') && !config('services.linkedin.live_oauth', false)) {
            return new OAuthTokenDTO(
                accessToken: 'mock_linkedin_access_token_' . time(),
                refreshToken: 'mock_linkedin_refresh_token',
                expiresAt: now()->addDays(60),
                scopes: ['openid', 'profile', 'w_member_social', 'w_organization_social'],
                accountId: 'urn:li:person:mock_linkedin_user',
                accountName: 'John Doe, MBA',
                accountEmail: 'john@example.com',
                raw: ['id' => 'mock_linkedin_user']
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
            throw new Exception("LinkedIn token exchange failed: " . $response->body());
        }

        $data = $response->json();
        $accessToken = $data['access_token'];

        // Fetch User Info
        $userResponse = Http::withToken($accessToken)->get('https://api.linkedin.com/v2/userinfo');
        $userData = $userResponse->json() ?? [];

        return new OAuthTokenDTO(
            accessToken: $accessToken,
            refreshToken: $data['refresh_token'] ?? null,
            expiresAt: isset($data['expires_in']) ? now()->addSeconds((int)$data['expires_in']) : now()->addDays(60),
            scopes: explode(' ', $data['scope'] ?? 'w_member_social'),
            accountId: $userData['sub'] ?? 'unknown_linkedin_user',
            accountName: $userData['name'] ?? 'LinkedIn Member',
            accountEmail: $userData['email'] ?? null,
            raw: $data
        );
    }

    public function refreshToken(string $refreshToken): OAuthTokenDTO
    {
        $clientId = config('services.linkedin.client_id', env('LINKEDIN_CLIENT_ID', 'mock_linkedin_client_id'));
        $clientSecret = config('services.linkedin.client_secret', env('LINKEDIN_CLIENT_SECRET', 'mock_linkedin_secret'));

        if (app()->environment('testing') && !config('services.linkedin.live_oauth', false)) {
            return new OAuthTokenDTO(
                accessToken: 'mock_refreshed_linkedin_token_' . time(),
                refreshToken: $refreshToken,
                expiresAt: now()->addDays(60),
                scopes: ['w_member_social'],
                raw: ['refreshed' => true]
            );
        }

        $response = Http::asForm()->post($this->tokenUrl, [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);

        if ($response->failed()) {
            throw new Exception("LinkedIn token refresh failed: " . $response->body());
        }

        $data = $response->json();

        return new OAuthTokenDTO(
            accessToken: $data['access_token'],
            refreshToken: $data['refresh_token'] ?? $refreshToken,
            expiresAt: isset($data['expires_in']) ? now()->addSeconds((int)$data['expires_in']) : now()->addDays(60),
            scopes: explode(' ', $data['scope'] ?? ''),
            raw: $data
        );
    }

    public function discoverProfiles(OAuthTokenDTO $token): array
    {
        $accessToken = $token->accessToken;

        if (app()->environment('testing') && !config('services.linkedin.live_oauth', false)) {
            return [
                new DiscoveredProfileDTO(
                    platform: SocialPlatform::LINKEDIN->value,
                    profileId: 'urn:li:person:mock_linkedin_user_123',
                    name: 'John Doe (Personal Profile)',
                    username: 'johndoe',
                    avatarUrl: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100',
                    profileUrl: 'https://linkedin.com/in/johndoe',
                    accessToken: $accessToken,
                    capabilities: [
                        'supports_text' => true,
                        'supports_images' => true,
                        'supports_video' => true,
                        'supports_scheduling' => true,
                    ]
                ),
                new DiscoveredProfileDTO(
                    platform: SocialPlatform::LINKEDIN->value,
                    profileId: 'urn:li:organization:mock_company_987654',
                    name: 'Acme Technologies Inc. (Company Page)',
                    username: 'acme-technologies',
                    avatarUrl: 'https://images.unsplash.com/photo-1570295999919-56ceb5ecca61?w=100',
                    profileUrl: 'https://linkedin.com/company/acme-technologies',
                    accessToken: $accessToken,
                    capabilities: [
                        'supports_text' => true,
                        'supports_images' => true,
                        'supports_video' => true,
                        'supports_scheduling' => true,
                        'supports_comments' => true,
                        'supports_analytics' => true,
                    ]
                ),
            ];
        }

        $profiles = [];

        try {
            // 1. Personal Profile via OpenID UserInfo
            $userResponse = Http::withToken($accessToken)->get('https://api.linkedin.com/v2/userinfo');
            if ($userResponse->successful()) {
                $user = $userResponse->json();
                $personUrn = "urn:li:person:{$user['sub']}";
                $profiles[] = new DiscoveredProfileDTO(
                    platform: SocialPlatform::LINKEDIN->value,
                    profileId: $personUrn,
                    name: ($user['name'] ?? 'LinkedIn User') . ' (Personal Profile)',
                    username: strtolower(str_replace(' ', '', $user['name'] ?? 'linkedinuser')),
                    avatarUrl: $user['picture'] ?? null,
                    accessToken: $accessToken,
                    capabilities: [
                        'supports_text' => true,
                        'supports_images' => true,
                        'supports_video' => true,
                        'supports_scheduling' => true,
                    ],
                    metadata: ['account_type' => 'personal']
                );
            }

            // 2. Organization Company Pages
            $orgResponse = Http::withToken($accessToken)
                ->get("{$this->apiBase}/organizationalEntityAcls?q=roleAssignee&state=APPROVED");

            if ($orgResponse->successful()) {
                $elements = $orgResponse->json('elements') ?? [];
                foreach ($elements as $el) {
                    $orgUrn = $el['organizationalTarget'] ?? null;
                    if (!$orgUrn) continue;

                    // Fetch Org Name
                    $orgId = str_replace('urn:li:organization:', '', $orgUrn);
                    $detailsResponse = Http::withToken($accessToken)
                        ->get("{$this->apiBase}/organizations/{$orgId}");

                    $orgName = $detailsResponse->json('localizedName') ?? "LinkedIn Company Page ({$orgId})";

                    $profiles[] = new DiscoveredProfileDTO(
                        platform: SocialPlatform::LINKEDIN->value,
                        profileId: $orgUrn,
                        name: $orgName . ' (Company Page)',
                        accessToken: $accessToken,
                        capabilities: [
                            'supports_text' => true,
                            'supports_images' => true,
                            'supports_video' => true,
                            'supports_scheduling' => true,
                            'supports_comments' => true,
                            'supports_analytics' => true,
                        ],
                        metadata: ['account_type' => 'organization']
                    );
                }
            }
        } catch (Exception $e) {
            Log::error("LinkedIn discovery error: " . $e->getMessage());
        }

        return $profiles;
    }
}
