<?php

namespace App\Services\Social\GoogleBusiness;

use App\Enums\SocialPlatform;
use App\Models\OAuthTransaction;
use App\Services\Social\Contracts\OAuthProviderInterface;
use App\Services\Social\Contracts\ProfileDiscoveryInterface;
use App\Services\Social\DTOs\DiscoveredProfileDTO;
use App\Services\Social\DTOs\OAuthTokenDTO;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleBusinessOAuthService implements OAuthProviderInterface, ProfileDiscoveryInterface
{
    protected string $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth';
    protected string $tokenUrl = 'https://oauth2.googleapis.com/token';

    public function getPlatform(): SocialPlatform
    {
        return SocialPlatform::GOOGLE_BUSINESS;
    }

    public function getAuthorizationUrl(OAuthTransaction $transaction): string
    {
        $clientId = config('services.google.client_id', env('GOOGLE_CLIENT_ID', 'mock_google_client_id'));
        $redirectUri = $transaction->redirect_url ?? route('oauth.callback', ['platform' => 'google_business']);

        $scopes = [
            'openid',
            'profile',
            'email',
            'https://www.googleapis.com/auth/business.manage',
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
        $redirectUri = $transaction->redirect_url ?? route('oauth.callback', ['platform' => 'google_business']);

        // Mock token for testing
        if (app()->environment('testing') && !config('services.google_business.live_oauth', false)) {
            return new OAuthTokenDTO(
                accessToken: 'mock_gbp_access_token_' . time(),
                refreshToken: 'mock_gbp_refresh_token_' . time(),
                expiresAt: now()->addHours(1),
                scopes: ['https://www.googleapis.com/auth/business.manage', 'openid', 'email', 'profile'],
                accountId: 'accounts/10987654321',
                accountName: 'Acme Corporation',
                raw: [
                    'access_token' => 'mock_gbp_access_token',
                    'refresh_token' => 'mock_gbp_refresh_token',
                ]
            );
        }

        try {
            $response = Http::asForm()->post($this->tokenUrl, [
                'code' => $code,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code',
            ]);

            if ($response->failed()) {
                Log::error('Google Business Profile OAuth Token Error', ['body' => $response->body()]);
                throw new Exception('Failed to obtain Google Business Profile access token: ' . $response->body());
            }

            $data = $response->json();
            $scopes = isset($data['scope']) ? explode(' ', $data['scope']) : [];

            return new OAuthTokenDTO(
                accessToken: $data['access_token'],
                refreshToken: $data['refresh_token'] ?? null,
                expiresAt: isset($data['expires_in']) ? now()->addSeconds((int)$data['expires_in']) : now()->addHours(1),
                scopes: $scopes,
                raw: $data
            );
        } catch (Exception $e) {
            Log::error('Google Business Profile OAuth Exception: ' . $e->getMessage());
            throw $e;
        }
    }

    public function refreshToken(string $refreshToken): OAuthTokenDTO
    {
        $clientId = config('services.google.client_id', env('GOOGLE_CLIENT_ID', 'mock_google_client_id'));
        $clientSecret = config('services.google.client_secret', env('GOOGLE_CLIENT_SECRET', 'mock_google_secret'));

        if (app()->environment('testing') && !config('services.google_business.live_oauth', false)) {
            return new OAuthTokenDTO(
                accessToken: 'mock_refreshed_gbp_access_token_' . time(),
                refreshToken: $refreshToken,
                expiresAt: now()->addHours(1),
                scopes: ['https://www.googleapis.com/auth/business.manage'],
                raw: ['access_token' => 'mock_refreshed_gbp_token']
            );
        }

        $response = Http::asForm()->post($this->tokenUrl, [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
        ]);

        if ($response->failed()) {
            throw new Exception('Google Business token refresh failed: ' . $response->body());
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

        if (app()->environment('testing') && !config('services.google_business.live_oauth', false)) {
            return [
                new DiscoveredProfileDTO(
                    platform: SocialPlatform::GOOGLE_BUSINESS->value,
                    profileId: 'accounts/10987654321/locations/987654321',
                    name: 'Acme Flagship Store (Google Business)',
                    username: 'acme-flagship-downtown',
                    avatarUrl: 'https://lh3.googleusercontent.com/p/AF1QipN_mock_business_photo.jpg',
                    accessToken: $accessToken,
                    capabilities: ['publish', 'insights', 'reviews', 'local_posts'],
                    metadata: [
                        'account_name' => 'accounts/10987654321',
                        'location_name' => 'accounts/10987654321/locations/987654321',
                        'address' => '100 Main Street, Suite 400, New York, NY 10001',
                    ]
                )
            ];
        }

        try {
            $profiles = [];

            // 1. Fetch My Business Accounts
            $accountsRes = Http::withToken($accessToken)
                ->get('https://mybusinessaccountmanagement.googleapis.com/v1/accounts');

            if ($accountsRes->failed()) {
                Log::warning('Google Business fetch accounts failed', ['res' => $accountsRes->body()]);
                return [];
            }

            $accounts = $accountsRes->json('accounts') ?? [];

            foreach ($accounts as $account) {
                $accountName = $account['name']; // e.g. accounts/123456

                // 2. Fetch locations under this account
                $locationsRes = Http::withToken($accessToken)
                    ->get("https://mybusinessbusinessinformation.googleapis.com/v1/{$accountName}/locations", [
                        'readMask' => 'name,title,storefrontAddress,websiteUri,profile',
                    ]);

                if ($locationsRes->successful()) {
                    $locations = $locationsRes->json('locations') ?? [];
                    foreach ($locations as $loc) {
                        $locName = $loc['name']; // e.g. locations/7890
                        $fullId = "{$accountName}/{$locName}";
                        $title = $loc['title'] ?? 'Google Business Location';
                        $address = $loc['storefrontAddress']['addressLines'][0] ?? null;

                        $profiles[] = new DiscoveredProfileDTO(
                            platform: SocialPlatform::GOOGLE_BUSINESS->value,
                            profileId: $fullId,
                            name: $title . ' (Google Business)',
                            username: $loc['websiteUri'] ?? $locName,
                            avatarUrl: null,
                            accessToken: $accessToken,
                            capabilities: ['publish', 'insights', 'reviews', 'local_posts'],
                            metadata: [
                                'account_type' => 'location',
                                'account_name' => $accountName,
                                'location_name' => $locName,
                                'address' => $address,
                            ]
                        );
                    }
                }
            }

            return $profiles;
        } catch (Exception $e) {
            Log::error('Google Business Profile discovery error: ' . $e->getMessage());
            return [];
        }
    }
}
