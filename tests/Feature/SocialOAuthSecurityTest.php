<?php

namespace Tests\Feature;

use App\Enums\SocialPlatform;
use App\Livewire\SocialAccounts\AccountManager;
use App\Models\Client;
use App\Models\OAuthTransaction;
use App\Models\SocialConnection;
use App\Models\SocialProfile;
use App\Models\User;
use App\Services\Clients\ClientService;
use App\Services\Social\DTOs\DiscoveredProfileDTO;
use App\Services\Social\DTOs\OAuthTokenDTO;
use App\Services\Social\Registry\SocialProviderManager;
use App\Services\Tenancy\WorkspaceManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class SocialOAuthSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test OAuth connection initiation creates transaction and redirects to provider.
     */
    public function test_connect_generates_transaction_and_redirects(): void
    {
        $owner = User::factory()->create();
        $workspaceManager = app(WorkspaceManagerService::class);
        $workspace = $workspaceManager->createWorkspace($owner, 'Apex Media Group');

        $this->actingAs($owner);
        session(['active_workspace_id' => $workspace->id]);
        app()->instance('current_workspace', $workspace);

        $response = $this->get(route('oauth.connect', ['platform' => 'facebook']));
        $response->assertStatus(302);

        $transaction = OAuthTransaction::where('workspace_id', $workspace->id)
            ->where('user_id', $owner->id)
            ->where('platform', 'facebook')
            ->first();

        $this->assertNotNull($transaction);
        $this->assertFalse($transaction->is_consumed);
        $this->assertNotEmpty($transaction->state_token);

        // Verify redirect URL points to Facebook with state parameter
        $redirectUrl = $response->headers->get('Location');
        $this->assertStringContainsString('https://www.facebook.com/v20.0/dialog/oauth', $redirectUrl);
        $this->assertStringContainsString('state=' . $transaction->state_token, $redirectUrl);
    }

    /**
     * Test invalid or expired state token is rejected with 403 Forbidden.
     */
    public function test_callback_with_invalid_state_throws_forbidden(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);

        $response = $this->get(route('oauth.callback', [
            'platform' => 'facebook',
            'code' => 'mock_auth_code',
            'state' => 'non_existent_state_token',
        ]));

        $response->assertStatus(403);
    }

    /**
     * Test replay attack prevention: consumed transaction cannot be used twice.
     */
    public function test_state_cannot_be_replayed_twice(): void
    {
        $owner = User::factory()->create();
        $workspaceManager = app(WorkspaceManagerService::class);
        $workspace = $workspaceManager->createWorkspace($owner, 'Apex Agency');

        $transaction = OAuthTransaction::create([
            'state_token' => 'test_unique_state_12345',
            'workspace_id' => $workspace->id,
            'user_id' => $owner->id,
            'platform' => 'facebook',
            'redirect_url' => route('oauth.callback', ['platform' => 'facebook']),
            'is_consumed' => true, // Already consumed!
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->actingAs($owner);
        session(['active_workspace_id' => $workspace->id]);

        $response = $this->get(route('oauth.callback', [
            'platform' => 'facebook',
            'code' => 'mock_code',
            'state' => 'test_unique_state_12345',
        ]));

        $response->assertStatus(403);
    }

    /**
     * Test token encryption at rest in MySQL / SQLite.
     */
    public function test_social_connection_tokens_are_stored_encrypted(): void
    {
        $owner = User::factory()->create();
        $workspaceManager = app(WorkspaceManagerService::class);
        $workspace = $workspaceManager->createWorkspace($owner, 'Secure Digital');

        $plainAccessToken = 'EAABwzLixnjYBAK_secret_raw_meta_token_1234567890';
        $plainRefreshToken = 'secret_refresh_token_abcdef';

        $connection = SocialConnection::create([
            'workspace_id' => $workspace->id,
            'platform' => SocialPlatform::FACEBOOK,
            'account_id' => '1092837465',
            'account_name' => 'John Marketing Admin',
            'access_token' => $plainAccessToken,
            'refresh_token' => $plainRefreshToken,
            'scopes' => ['pages_show_list'],
        ]);

        // Raw database query must NOT contain plain text token
        $rawRecord = DB::table('social_connections')->where('id', $connection->id)->first();
        $this->assertNotEquals($plainAccessToken, $rawRecord->access_token);
        $this->assertNotEquals($plainRefreshToken, $rawRecord->refresh_token);

        // Model accessor transparently decrypts
        $freshConnection = SocialConnection::find($connection->id);
        $this->assertEquals($plainAccessToken, $freshConnection->access_token);
        $this->assertEquals($plainRefreshToken, $freshConnection->refresh_token);
    }

    /**
     * Test post-OAuth profile selection and persistence.
     */
    public function test_import_selected_profiles_creates_records(): void
    {
        $owner = User::factory()->create();
        $workspaceManager = app(WorkspaceManagerService::class);
        $workspace = $workspaceManager->createWorkspace($owner, 'Global Media');

        $clientService = app(ClientService::class);
        $client = $clientService->createClient($workspace, ['name' => 'Sharma Restaurant', 'client_code' => 'SHR-01']);

        $connection = SocialConnection::create([
            'workspace_id' => $workspace->id,
            'platform' => SocialPlatform::FACEBOOK,
            'account_id' => '999888',
            'account_name' => 'Agency FB Account',
            'access_token' => 'sample_token',
            'scopes' => ['pages_show_list'],
        ]);

        $discoveredProfiles = [
            new DiscoveredProfileDTO(
                platform: 'facebook',
                profileId: 'page_123',
                name: 'Sharma Restaurant Facebook Page',
                capabilities: ['supports_text' => true, 'supports_images' => true]
            ),
            new DiscoveredProfileDTO(
                platform: 'instagram',
                profileId: 'ig_456',
                name: 'sharmarestaurant_ig',
                username: 'sharmarestaurant',
                capabilities: ['supports_images' => true, 'supports_reels' => true]
            ),
        ];

        session([
            'active_workspace_id' => $workspace->id,
            'pending_connection_id' => $connection->id,
            'pending_discovered_profiles' => serialize($discoveredProfiles),
        ]);

        $this->actingAs($owner);
        app()->instance('current_workspace', $workspace);

        Livewire::test(AccountManager::class)
            ->set('assignClientId', $client->id)
            ->set('selectedProfileIds', ['page_123', 'ig_456'])
            ->call('importSelectedProfiles');

        $fbProfile = SocialProfile::where('profile_id', 'page_123')->first();
        $this->assertNotNull($fbProfile);
        $this->assertEquals($client->id, $fbProfile->client_id);
        $this->assertEquals($workspace->id, $fbProfile->workspace_id);

        $igProfile = SocialProfile::where('profile_id', 'ig_456')->first();
        $this->assertNotNull($igProfile);
        $this->assertEquals('sharmarestaurant', $igProfile->username);
        $this->assertEquals($client->id, $igProfile->client_id);
    }
}

