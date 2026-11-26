<?php

namespace Tests\Feature;

use App\Enums\SocialPlatform;
use App\Models\OAuthTransaction;
use App\Models\SocialConnection;
use App\Models\SocialProfile;
use App\Models\User;
use App\Models\Workspace;
use App\Services\Social\DTOs\PublishingPayloadDTO;
use App\Services\Social\Pinterest\PinterestOAuthService;
use App\Services\Social\Pinterest\PinterestPublishingService;
use App\Services\Social\Registry\SocialProviderManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PinterestIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_manager_resolves_pinterest_oauth_and_publisher(): void
    {
        $manager = app(SocialProviderManager::class);

        $this->assertInstanceOf(PinterestOAuthService::class, $manager->getOAuthProvider(SocialPlatform::PINTEREST));
        $this->assertInstanceOf(PinterestPublishingService::class, $manager->getPublishingProvider(SocialPlatform::PINTEREST));
    }

    public function test_pinterest_oauth_flow_and_board_discovery(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::create(['name' => 'Studio', 'owner_id' => $user->id]);

        $oauth = app(PinterestOAuthService::class);
        $tx = OAuthTransaction::create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
            'platform' => SocialPlatform::PINTEREST,
            'state_token' => 'pin_state_123',
            'redirect_url' => 'https://example.com/oauth/callback/pinterest',
            'expires_at' => now()->addMinutes(10),
        ]);

        $authUrl = $oauth->getAuthorizationUrl($tx);
        $this->assertStringContainsString('https://www.pinterest.com/oauth/', $authUrl);
        $this->assertStringContainsString('pins:write', urlencode('pins:write'));

        $tokenDto = $oauth->handleCallback('mock_pin_code', $tx);
        $this->assertNotEmpty($tokenDto->accessToken);

        $profiles = $oauth->discoverProfiles($tokenDto);
        $this->assertCount(1, $profiles);
        $this->assertEquals(SocialPlatform::PINTEREST->value, $profiles[0]->platform);
        $this->assertNotEmpty($profiles[0]->metadata['boards']);
    }

    public function test_pinterest_publishing_service_creates_pin(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::create(['name' => 'Studio', 'owner_id' => $user->id]);

        $connection = SocialConnection::create([
            'workspace_id' => $workspace->id,
            'platform' => SocialPlatform::PINTEREST,
            'account_id' => 'pin_acc_1',
            'account_name' => 'Design Studio',
            'access_token' => 'mock_pin_token',
            'scopes' => ['pins:write', 'boards:read'],
        ]);

        $profile = SocialProfile::create([
            'workspace_id' => $workspace->id,
            'connection_id' => $connection->id,
            'platform' => SocialPlatform::PINTEREST,
            'profile_id' => 'pin_acc_1',
            'name' => 'Design Studio (Pinterest)',
            'metadata' => ['boards' => [['id' => 'board_1', 'name' => 'Ideas']]],
        ]);

        $publisher = app(PinterestPublishingService::class);
        $result = $publisher->publish($profile, new PublishingPayloadDTO(
            caption: 'Minimalist workspace setup ideas',
            mediaUrls: ['https://cdn.example.com/setup.jpg'],
            linkUrl: 'https://postsync.cloud',
            platformSettings: ['title' => 'Desk Setup', 'board_id' => 'board_1']
        ));

        $this->assertTrue($result->success);
        $this->assertStringContainsString('pinterest.com/pin/', $result->postUrl);
    }
}
