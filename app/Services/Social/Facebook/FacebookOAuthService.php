<?php

namespace App\Services\Social\Facebook;

use App\Enums\SocialPlatform;
use App\Models\OAuthTransaction;
use App\Services\Social\Contracts\OAuthProviderInterface;
use App\Services\Social\Contracts\ProfileDiscoveryInterface;
use App\Services\Social\Contracts\SocialProviderInterface;
use App\Services\Social\DTOs\DiscoveredProfileDTO;
use App\Services\Social\DTOs\OAuthTokenDTO;
use App\Services\Social\Registry\PlatformCapabilityRegistry;
use Exception;
use Illuminate\Support\Facades\Http;

class FacebookOAuthService implements SocialProviderInterface, OAuthProviderInterface, ProfileDiscoveryInterface
{
    protected string $graphVersion = 'v20.0';
    protected string $appId;
    protected string $appSecret;

    public function __construct()
    {
        $this->appId = (string) config('services.facebook.client_id', env('META_APP_ID', ''));
        $this->appSecret = (string) config('services.facebook.client_secret', env('META_APP_SECRET', ''));
    }

    public function getPlatform(): SocialPlatform
    {
        return SocialPlatform::FACEBOOK;
    }

    public function getCapabilities(): array
    {
        return PlatformCapabilityRegistry::getCapabilities(SocialPlatform::FACEBOOK);
    }

    public function getAuthorizationUrl(OAuthTransaction $transaction): string
    {
        $scopes = [
            'pages_show_list',
            'pages_read_engagement',
            'pages_manage_posts',
            'read_insights',
            'instagram_basic',
            'instagram_content_publish',
        ];

        $params = http_build_query([
            'client_id' => $this->appId,
            'redirect_uri' => $transaction->redirect_url,
            'state' => $transaction->state_token,
            'scope' => implode(',', $scopes),
            'response_type' => 'code',
        ]);

        return "https://www.facebook.com/{$this->graphVersion}/dialog/oauth?{$params}";
    }

    public function handleCallback(string $code, OAuthTransaction $transaction): OAuthTokenDTO
    {
        // 1. Exchange authorization code for short-lived user access token
        $response = Http::get("https://graph.facebook.com/{$this->graphVersion}/oauth/access_token", [
            'client_id' => $this->appId,
            'client_secret' => $this->appSecret,
            'redirect_uri' => $transaction->redirect_url,
            'code' => $code,
        ]);

        if (!$response->successful()) {
            throw new Exception("Facebook OAuth Error: " . ($response->json('error.message') ?? $response->body()));
        }

        $data = $response->json();
        $shortLivedToken = $data['access_token'];

        // 2. Exchange short-lived token for long-lived (60 days) user access token
        $longLivedResponse = Http::get("https://graph.facebook.com/{$this->graphVersion}/oauth/access_token", [
            'grant_type' => 'fb_exchange_token',
            'client_id' => $this->appId,
            'client_secret' => $this->appSecret,
            'fb_exchange_token' => $shortLivedToken,
        ]);

        $longLivedData = $longLivedResponse->successful() ? $longLivedResponse->json() : $data;
        $accessToken = $longLivedData['access_token'];
        $expiresIn = $longLivedData['expires_in'] ?? (60 * 86400);

        // 3. Query user profile identity
        $meResponse = Http::withToken($accessToken)
            ->get("https://graph.facebook.com/{$this->graphVersion}/me", [
                'fields' => 'id,name,email,picture.type(large)',
            ]);

        $userData = $meResponse->successful() ? $meResponse->json() : [];

        return new OAuthTokenDTO(
            accessToken: $accessToken,
            expiresAt: now()->addSeconds((int)$expiresIn),
            scopes: ['pages_show_list', 'pages_manage_posts', 'instagram_basic', 'instagram_content_publish'],
            accountId: $userData['id'] ?? 'unknown',
            accountName: $userData['name'] ?? 'Facebook User',
            accountEmail: $userData['email'] ?? null,
            avatarUrl: $userData['picture']['data']['url'] ?? null,
            raw: $longLivedData
        );
    }

    public function refreshToken(string $refreshToken): OAuthTokenDTO
    {
        // Facebook user tokens are re-exchanged; Page tokens generated from long-lived user tokens do not expire
        throw new Exception("Facebook access tokens must be refreshed via user re-authorization flow.");
    }

    public function discoverProfiles(OAuthTokenDTO $token): array
    {
        $profiles = [];

        // Fetch manageable Facebook Pages
        $accountsResponse = Http::withToken($token->accessToken)
            ->get("https://graph.facebook.com/{$this->graphVersion}/me/accounts", [
                'fields' => 'id,name,category,access_token,picture.type(large),instagram_business_account{id,username,profile_picture_url}',
                'limit' => 100,
            ]);

        if (!$accountsResponse->successful()) {
            return $profiles;
        }

        $pages = $accountsResponse->json('data') ?? [];

        foreach ($pages as $page) {
            // Discovered Facebook Page
            $profiles[] = new DiscoveredProfileDTO(
                platform: SocialPlatform::FACEBOOK->value,
                profileId: (string)$page['id'],
                name: (string)$page['name'],
                username: null,
                avatarUrl: $page['picture']['data']['url'] ?? null,
                profileUrl: "https://facebook.com/{$page['id']}",
                accessToken: $page['access_token'] ?? null, // Permanent Page Token
                capabilities: PlatformCapabilityRegistry::getCapabilities(SocialPlatform::FACEBOOK),
                metadata: [
                    'category' => $page['category'] ?? 'General',
                ]
            );

            // Discovered linked Instagram Professional Account (if present)
            if (!empty($page['instagram_business_account'])) {
                $ig = $page['instagram_business_account'];
                $profiles[] = new DiscoveredProfileDTO(
                    platform: SocialPlatform::INSTAGRAM->value,
                    profileId: (string)$ig['id'],
                    name: (string)($ig['username'] ?? $page['name']),
                    username: (string)($ig['username'] ?? ''),
                    avatarUrl: $ig['profile_picture_url'] ?? $page['picture']['data']['url'] ?? null,
                    profileUrl: "https://instagram.com/" . ($ig['username'] ?? ''),
                    accessToken: $page['access_token'] ?? null, // Uses linked Page access token
                    capabilities: PlatformCapabilityRegistry::getCapabilities(SocialPlatform::INSTAGRAM),
                    metadata: [
                        'linked_facebook_page_id' => $page['id'],
                        'linked_facebook_page_name' => $page['name'],
                    ]
                );
            }
        }

        return $profiles;
    }
}

