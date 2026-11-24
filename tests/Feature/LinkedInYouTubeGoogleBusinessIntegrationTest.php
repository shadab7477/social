<?php

namespace Tests\Feature;

use App\Enums\SocialPlatform;
use App\Models\OAuthTransaction;
use App\Models\SocialConnection;
use App\Models\SocialProfile;
use App\Models\User;
use App\Services\Social\DTOs\PublishingPayloadDTO;
use App\Services\Social\GoogleBusiness\GoogleBusinessOAuthService;
use App\Services\Social\GoogleBusiness\GoogleBusinessPublishingService;
use App\Services\Social\LinkedIn\LinkedInOAuthService;
use App\Services\Social\LinkedIn\LinkedInPublishingService;
use App\Services\Social\Registry\SocialProviderManager;
use App\Services\Social\YouTube\YouTubeOAuthService;
use App\Services\Social\YouTube\YouTubePublishingService;
use App\Services\Social\YouTube\YouTubeQuotaTrackerService;
use App\Services\Tenancy\WorkspaceManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;

class LinkedInYouTubeGoogleBusinessIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected $workspace;
    protected SocialProviderManager $providerManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $workspaceManager = app(WorkspaceManagerService::class);
        $this->workspace = $workspaceManager->createWorkspace($this->user, 'Acme Global Media');
        $this->providerManager = app(SocialProviderManager::class);
    }

    public function test_social_provider_manager_resolves_all_three_platforms(): void
    {
        // LinkedIn
        $this->assertInstanceOf(LinkedInOAuthService::class, $this->providerManager->getOAuthProvider(SocialPlatform::LINKEDIN));
        $this->assertInstanceOf(LinkedInPublishingService::class, $this->providerManager->getPublishingProvider(SocialPlatform::LINKEDIN));
        $this->assertInstanceOf(LinkedInPublishingService::class, $this->providerManager->getProvider(SocialPlatform::LINKEDIN));

        // YouTube
        $this->assertInstanceOf(YouTubeOAuthService::class, $this->providerManager->getOAuthProvider(SocialPlatform::YOUTUBE));
        $this->assertInstanceOf(YouTubePublishingService::class, $this->providerManager->getPublishingProvider(SocialPlatform::YOUTUBE));
        $this->assertInstanceOf(YouTubePublishingService::class, $this->providerManager->getProvider(SocialPlatform::YOUTUBE));

        // Google Business Profile
        $this->assertInstanceOf(GoogleBusinessOAuthService::class, $this->providerManager->getOAuthProvider(SocialPlatform::GOOGLE_BUSINESS));
        $this->assertInstanceOf(GoogleBusinessPublishingService::class, $this->providerManager->getPublishingProvider(SocialPlatform::GOOGLE_BUSINESS));
        $this->assertInstanceOf(GoogleBusinessPublishingService::class, $this->providerManager->getProvider(SocialPlatform::GOOGLE_BUSINESS));
    }

    public function test_linkedin_oauth_flow_and_profile_discovery(): void
    {
        $oauthService = app(LinkedInOAuthService::class);
        $transaction = OAuthTransaction::create([
            'workspace_id' => $this->workspace->id,
            'user_id' => $this->user->id,
            'platform' => SocialPlatform::LINKEDIN->value,
            'state_token' => Str::random(40),
            'code_verifier' => Str::random(64),
            'redirect_url' => 'https://example.com/oauth/callback/linkedin',
            'expires_at' => now()->addMinutes(10),
        ]);

        $authUrl = $oauthService->getAuthorizationUrl($transaction);
        $this->assertStringContainsString('https://www.linkedin.com/oauth/v2/authorization', $authUrl);
        $this->assertStringContainsString('w_member_social', $authUrl);

        $tokenDto = $oauthService->handleCallback('mock_auth_code_123', $transaction);
        $this->assertNotEmpty($tokenDto->accessToken);

        $discoveredProfiles = $oauthService->discoverProfiles($tokenDto);
        $this->assertNotEmpty($discoveredProfiles);
        $this->assertCount(2, $discoveredProfiles); // 1 personal profile + 1 organization page
        $this->assertEquals(SocialPlatform::LINKEDIN->value, $discoveredProfiles[0]->platform);
        $this->assertEquals('urn:li:person:mock_linkedin_user_123', $discoveredProfiles[0]->profileId);
        $this->assertEquals('urn:li:organization:mock_company_987654', $discoveredProfiles[1]->profileId);
    }

    public function test_linkedin_publishing_service(): void
    {
        $connection = SocialConnection::create([
            'workspace_id' => $this->workspace->id,
            'platform' => SocialPlatform::LINKEDIN,
            'account_id' => 'urn:li:person:mock_linkedin_user_123',
            'account_name' => 'John Doe',
            'access_token' => 'mock_token_123',
            'scopes' => ['w_member_social', 'w_organization_social'],
        ]);

        $profile = SocialProfile::create([
            'workspace_id' => $this->workspace->id,
            'connection_id' => $connection->id,
            'platform' => SocialPlatform::LINKEDIN,
            'profile_id' => 'urn:li:person:mock_linkedin_user_123',
            'name' => 'John Doe',
            'access_token' => 'mock_token_123',
            'capabilities' => ['supports_text' => true, 'supports_images' => true, 'supports_links' => true],
            'is_active' => true,
            'publishing_enabled' => true,
        ]);

        $publishingService = app(LinkedInPublishingService::class);
        $payload = new PublishingPayloadDTO(
            caption: 'Exciting news! We just launched our AI social suite.',
            linkUrl: 'https://example.com/announcement'
        );

        $result = $publishingService->publish($profile, $payload);
        $this->assertTrue($result->success);
        $this->assertStringStartsWith('urn:li:share:', $result->providerPostId);
        $this->assertStringContainsString('linkedin.com/feed/update', $result->postUrl);
    }

    public function test_youtube_quota_tracking_and_threshold_enforcement(): void
    {
        Cache::flush();
        $quotaTracker = app(YouTubeQuotaTrackerService::class);
        $wsId = $this->workspace->id;

        // Standard remaining quota is 10000
        $this->assertEquals(10000, $quotaTracker->getRemainingQuota($wsId));
        $this->assertTrue($quotaTracker->canUploadVideo($wsId));

        // Consume 1600 for a video upload
        $quotaTracker->recordUsage($wsId, 1600);
        $this->assertEquals(8400, $quotaTracker->getRemainingQuota($wsId));

        // Fill up to limit
        $quotaTracker->recordUsage($wsId, 8400);
        $this->assertEquals(0, $quotaTracker->getRemainingQuota($wsId));
        $this->assertFalse($quotaTracker->canUploadVideo($wsId));
    }

    public function test_youtube_oauth_and_video_publishing(): void
    {
        Cache::flush();
        $oauthService = app(YouTubeOAuthService::class);
        $transaction = OAuthTransaction::create([
            'workspace_id' => $this->workspace->id,
            'user_id' => $this->user->id,
            'platform' => SocialPlatform::YOUTUBE->value,
            'state_token' => Str::random(40),
            'code_verifier' => Str::random(64),
            'redirect_url' => 'https://example.com/oauth/callback/youtube',
            'expires_at' => now()->addMinutes(10),
        ]);

        $authUrl = $oauthService->getAuthorizationUrl($transaction);
        $this->assertStringContainsString('accounts.google.com', $authUrl);
        $this->assertStringContainsString('youtube.upload', $authUrl);

        $tokenDto = $oauthService->handleCallback('mock_yt_code', $transaction);
        $this->assertNotEmpty($tokenDto->accessToken);

        $channels = $oauthService->discoverProfiles($tokenDto);
        $this->assertNotEmpty($channels);
        $this->assertEquals(SocialPlatform::YOUTUBE->value, $channels[0]->platform);

        $connection = SocialConnection::create([
            'workspace_id' => $this->workspace->id,
            'platform' => SocialPlatform::YOUTUBE,
            'account_id' => $channels[0]->profileId,
            'account_name' => $channels[0]->name,
            'access_token' => $tokenDto->accessToken,
            'scopes' => ['https://www.googleapis.com/auth/youtube.upload'],
        ]);

        $profile = SocialProfile::create([
            'workspace_id' => $this->workspace->id,
            'connection_id' => $connection->id,
            'platform' => SocialPlatform::YOUTUBE,
            'profile_id' => $channels[0]->profileId,
            'name' => $channels[0]->name,
            'access_token' => $tokenDto->accessToken,
            'capabilities' => ['supports_video' => true],
            'is_active' => true,
            'publishing_enabled' => true,
        ]);

        $publishingService = app(YouTubePublishingService::class);
        $payload = new PublishingPayloadDTO(
            caption: "Product Demo 2026\n\nCheck out the full walkthrough of our software.",
            mediaUrls: ['https://storage.example.com/videos/demo.mp4']
        );

        $result = $publishingService->publish($profile, $payload);
        $this->assertTrue($result->success);
        $this->assertNotEmpty($result->providerPostId);
        $this->assertStringContainsString('youtube.com/watch?v=', $result->postUrl);
    }

    public function test_google_business_profile_oauth_and_local_post(): void
    {
        $oauthService = app(GoogleBusinessOAuthService::class);
        $transaction = OAuthTransaction::create([
            'workspace_id' => $this->workspace->id,
            'user_id' => $this->user->id,
            'platform' => SocialPlatform::GOOGLE_BUSINESS->value,
            'state_token' => Str::random(40),
            'code_verifier' => Str::random(64),
            'redirect_url' => 'https://example.com/oauth/callback/google_business',
            'expires_at' => now()->addMinutes(10),
        ]);

        $authUrl = $oauthService->getAuthorizationUrl($transaction);
        $this->assertStringContainsString('business.manage', $authUrl);

        $tokenDto = $oauthService->handleCallback('mock_gbp_code', $transaction);
        $this->assertNotEmpty($tokenDto->accessToken);

        $locations = $oauthService->discoverProfiles($tokenDto);
        $this->assertNotEmpty($locations);
        $this->assertEquals(SocialPlatform::GOOGLE_BUSINESS->value, $locations[0]->platform);

        $connection = SocialConnection::create([
            'workspace_id' => $this->workspace->id,
            'platform' => SocialPlatform::GOOGLE_BUSINESS,
            'account_id' => $locations[0]->metadata['account_name'],
            'account_name' => 'Acme Corporation',
            'access_token' => $tokenDto->accessToken,
            'scopes' => ['https://www.googleapis.com/auth/business.manage'],
        ]);

        $profile = SocialProfile::create([
            'workspace_id' => $this->workspace->id,
            'connection_id' => $connection->id,
            'platform' => SocialPlatform::GOOGLE_BUSINESS,
            'profile_id' => $locations[0]->profileId,
            'name' => $locations[0]->name,
            'access_token' => $tokenDto->accessToken,
            'capabilities' => ['local_posts' => true, 'supports_text' => true],
            'is_active' => true,
            'publishing_enabled' => true,
        ]);

        $publishingService = app(GoogleBusinessPublishingService::class);
        $payload = new PublishingPayloadDTO(
            caption: 'Join us for our Weekend Grand Reopening! 20% off all items.',
            linkUrl: 'https://example.com/reopening'
        );

        $result = $publishingService->publish($profile, $payload);
        $this->assertTrue($result->success);
        $this->assertStringContainsString('localPosts/', $result->providerPostId);
    }
}
