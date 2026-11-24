<?php

namespace Tests\Feature;

use App\Enums\ConnectionStatus;
use App\Enums\SocialPlatform;
use App\Models\OAuthTransaction;
use App\Models\SocialConnection;
use App\Models\SocialProfile;
use App\Models\User;
use App\Models\Workspace;
use App\Services\Social\DTOs\DiscoveredProfileDTO;
use App\Services\Social\DTOs\OAuthTokenDTO;
use App\Services\Social\Facebook\FacebookOAuthService;
use App\Services\Social\Registry\SocialProviderManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ClientConnectFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_view_public_connect_page(): void
    {
        $owner = User::factory()->create(['name' => 'Agency Boss']);
        $workspace = Workspace::create([
            'owner_id' => $owner->id,
            'name' => 'Acme Restaurant',
            'is_active' => true,
        ]);

        $response = $this->get(route('client-connect.show', ['uuid' => $workspace->uuid]));

        $response->assertOk();
        $response->assertSee('Acme Restaurant');
        $response->assertSee('Agency Boss');
        $response->assertSee('Connect Facebook & IG', false);
    }

    public function test_client_connect_initiates_oauth_handshake(): void
    {
        $owner = User::factory()->create();
        $workspace = Workspace::create([
            'owner_id' => $owner->id,
            'name' => 'Acme Client Two',
            'is_active' => true,
        ]);

        // Facebook direct redirect
        $response = $this->get(route('client-connect.oauth', [
            'uuid' => $workspace->uuid,
            'platform' => 'facebook',
        ]));

        $response->assertRedirect();
        $this->assertStringContainsString('facebook.com', $response->headers->get('Location'));

        $this->assertDatabaseHas('oauth_transactions', [
            'workspace_id' => $workspace->id,
            'user_id' => $owner->id,
            'platform' => 'facebook',
        ]);

        // Direct Instagram connect URL redirects straight to Meta dialog
        $igResponse = $this->get(route('client-connect.oauth', [
            'uuid' => $workspace->uuid,
            'platform' => 'instagram',
        ]));

        $igResponse->assertRedirect();
        $this->assertStringContainsString('facebook.com', $igResponse->headers->get('Location'));
    }

    public function test_client_oauth_callback_auto_imports_profiles_and_redirects_to_success(): void
    {
        $owner = User::factory()->create();
        $workspace = Workspace::create([
            'owner_id' => $owner->id,
            'name' => 'Acme Client Three',
            'is_active' => true,
        ]);

        $transaction = OAuthTransaction::create([
            'state_token' => 'client_test_token_xyz_123',
            'workspace_id' => $workspace->id,
            'user_id' => $owner->id,
            'platform' => 'facebook',
            'redirect_url' => route('oauth.callback', ['platform' => 'facebook']),
            'is_consumed' => false,
            'expires_at' => now()->addMinutes(15),
        ]);

        // Mock OAuth provider
        $mockProvider = Mockery::mock(FacebookOAuthService::class);
        $mockTokenDto = new OAuthTokenDTO(
            accessToken: 'client_fb_token',
            refreshToken: null,
            expiresAt: now()->addDays(60),
            refreshTokenExpiresAt: null,
            scopes: ['pages_manage_posts'],
            accountId: 'client_fb_user_99',
            accountName: 'Client Facebook Owner',
            accountEmail: 'client@brand.com',
            avatarUrl: 'https://cdn.example.com/client.jpg',
            raw: [],
        );

        $mockDiscoveredProfile = new DiscoveredProfileDTO(
            platform: 'facebook',
            profileId: 'fb_page_888',
            name: 'Client Main Page',
            username: 'clientbrand',
            avatarUrl: 'https://cdn.example.com/page.jpg',
            profileUrl: 'https://facebook.com/clientbrand',
            accessToken: 'page_token_888',
            capabilities: ['posts', 'photos'],
            metadata: [],
        );

        $mockProvider->shouldReceive('handleCallback')
            ->once()
            ->andReturn($mockTokenDto);

        $mockProvider->shouldReceive('discoverProfiles')
            ->once()
            ->andReturn([$mockDiscoveredProfile]);

        $this->instance(FacebookOAuthService::class, $mockProvider);

        // Guest user hits callback with client connect session
        $response = $this->withSession([
            'client_connect_uuid' => $workspace->uuid,
            'client_connect_state' => 'client_test_token_xyz_123',
            'client_connect_workspace_id' => $workspace->id,
        ])->get(route('oauth.callback', [
            'platform' => 'facebook',
            'state' => 'client_test_token_xyz_123',
            'code' => 'fb_auth_code_sample',
        ]));

        $response->assertRedirect(route('client-connect.success', ['uuid' => $workspace->uuid]));

        $this->assertDatabaseHas('social_connections', [
            'workspace_id' => $workspace->id,
            'account_id' => 'client_fb_user_99',
            'account_name' => 'Client Facebook Owner',
        ]);

        $this->assertDatabaseHas('social_profiles', [
            'workspace_id' => $workspace->id,
            'profile_id' => 'fb_page_888',
            'name' => 'Client Main Page',
        ]);
    }
}
