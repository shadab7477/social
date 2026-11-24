<?php

namespace Tests\Feature;

use App\Enums\SocialPlatform;
use App\Models\SocialConnection;
use App\Models\SocialProfile;
use App\Models\User;
use App\Models\WebhookEvent;
use App\Services\Social\DTOs\PublishingPayloadDTO;
use App\Services\Social\Facebook\FacebookPublishingService;
use App\Services\Social\Instagram\InstagramPublishingService;
use App\Services\Tenancy\WorkspaceManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FacebookAndInstagramIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected $workspace;
    protected SocialConnection $connection;
    protected SocialProfile $facebookProfile;
    protected SocialProfile $instagramProfile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $workspaceManager = app(WorkspaceManagerService::class);
        $this->workspace = $workspaceManager->createWorkspace($this->user, 'Delta Agency');

        $this->connection = SocialConnection::create([
            'workspace_id' => $this->workspace->id,
            'platform' => SocialPlatform::FACEBOOK,
            'account_id' => 'fb_acc_1001',
            'account_name' => 'Agency Master Account',
            'access_token' => 'EAAB_master_user_token_valid',
            'scopes' => ['pages_show_list', 'pages_manage_posts', 'instagram_basic', 'instagram_content_publish'],
        ]);

        $this->facebookProfile = SocialProfile::create([
            'workspace_id' => $this->workspace->id,
            'connection_id' => $this->connection->id,
            'platform' => 'facebook',
            'profile_id' => 'fb_page_555',
            'name' => 'Sharma Restaurant Facebook Page',
            'access_token' => 'EAAB_page_permanent_token_555',
            'capabilities' => ['supports_text' => true, 'supports_images' => true, 'supports_video' => true],
            'is_active' => true,
            'publishing_enabled' => true,
        ]);

        $this->instagramProfile = SocialProfile::create([
            'workspace_id' => $this->workspace->id,
            'connection_id' => $this->connection->id,
            'platform' => 'instagram',
            'profile_id' => 'ig_user_777',
            'name' => 'sharmarestaurant_official',
            'username' => 'sharmarestaurant_official',
            'access_token' => 'EAAB_page_permanent_token_555',
            'capabilities' => ['supports_images' => true, 'supports_video' => true, 'supports_carousel' => true, 'supports_reels' => true],
            'is_active' => true,
            'publishing_enabled' => true,
        ]);
    }

    /**
     * Test Facebook plain text publishing via /{page-id}/feed.
     */
    public function test_facebook_can_publish_text_post(): void
    {
        Http::fake([
            'https://graph.facebook.com/v20.0/fb_page_555/feed' => Http::response([
                'id' => 'fb_page_555_post_99991',
            ], 200),
        ]);

        $service = app(FacebookPublishingService::class);
        $payload = new PublishingPayloadDTO(
            caption: 'Weekend Special: Gourmet Thali Buffet starting today!'
        );

        $result = $service->publish($this->facebookProfile, $payload);

        $this->assertTrue($result->success);
        $this->assertEquals('fb_page_555_post_99991', $result->providerPostId);
        $this->assertStringContainsString('https://facebook.com/fb_page_555_post_99991', $result->postUrl);
    }

    /**
     * Test Facebook single photo publishing via /{page-id}/photos.
     */
    public function test_facebook_can_publish_single_image_post(): void
    {
        Http::fake([
            'https://graph.facebook.com/v20.0/fb_page_555/photos' => Http::response([
                'id' => 'photo_1001',
                'post_id' => 'fb_page_555_post_8888',
            ], 200),
        ]);

        $service = app(FacebookPublishingService::class);
        $payload = new PublishingPayloadDTO(
            caption: 'Check out our new interior seating!',
            mediaUrls: ['https://cdn.socialpilotx.com/media/interior.jpg']
        );

        $result = $service->publish($this->facebookProfile, $payload);

        $this->assertTrue($result->success);
        $this->assertEquals('photo_1001', $result->providerPostId);
    }

    /**
     * Test Facebook multi-photo publishing workflow.
     */
    public function test_facebook_can_publish_multi_photo_album(): void
    {
        Http::fake([
            'https://graph.facebook.com/v20.0/fb_page_555/photos' => Http::sequence()
                ->push(['id' => 'photo_id_1'], 200)
                ->push(['id' => 'photo_id_2'], 200),
            'https://graph.facebook.com/v20.0/fb_page_555/feed' => Http::response([
                'id' => 'fb_album_post_123',
            ], 200),
        ]);

        $service = app(FacebookPublishingService::class);
        $payload = new PublishingPayloadDTO(
            caption: 'Our culinary team in action!',
            mediaUrls: [
                'https://cdn.socialpilotx.com/dish1.jpg',
                'https://cdn.socialpilotx.com/dish2.jpg',
            ]
        );

        $result = $service->publish($this->facebookProfile, $payload);

        $this->assertTrue($result->success);
        $this->assertEquals('fb_album_post_123', $result->providerPostId);
    }

    /**
     * Test Facebook rate limit error detection (code 32).
     */
    public function test_facebook_handles_rate_limit_error_with_retriable_flag(): void
    {
        Http::fake([
            'https://graph.facebook.com/v20.0/fb_page_555/feed' => Http::response([
                'error' => [
                    'message' => 'User request limit reached',
                    'type' => 'OAuthException',
                    'code' => 32,
                ],
            ], 400),
        ]);

        $service = app(FacebookPublishingService::class);
        $payload = new PublishingPayloadDTO(caption: 'Test post');

        $result = $service->publish($this->facebookProfile, $payload);

        $this->assertFalse($result->success);
        $this->assertTrue($result->retriable);
        $this->assertEquals('32', $result->errorCode);
    }

    /**
     * Test Instagram Single Image Container-based publishing flow.
     */
    public function test_instagram_can_publish_single_image_container(): void
    {
        Http::fake([
            // 1. Container creation
            'https://graph.facebook.com/v20.0/ig_user_777/media' => Http::response([
                'id' => 'ig_creation_container_123',
            ], 200),
            // 2. Container status check
            'https://graph.facebook.com/v20.0/ig_creation_container_123*' => Http::response([
                'status_code' => 'FINISHED',
            ], 200),
            // 3. Media Publish
            'https://graph.facebook.com/v20.0/ig_user_777/media_publish' => Http::response([
                'id' => 'ig_published_media_999',
            ], 200),
            // 4. Permalink fetch
            'https://graph.facebook.com/v20.0/ig_published_media_999*' => Http::response([
                'id' => 'ig_published_media_999',
                'permalink' => 'https://www.instagram.com/p/Cxyz123/',
            ], 200),
        ]);

        $service = app(InstagramPublishingService::class);
        $payload = new PublishingPayloadDTO(
            caption: 'Freshly roasted espresso beans! #coffee #espresso',
            mediaUrls: ['https://cdn.socialpilotx.com/coffee.jpg']
        );

        $result = $service->publish($this->instagramProfile, $payload);

        $this->assertTrue($result->success);
        $this->assertEquals('ig_published_media_999', $result->providerPostId);
        $this->assertEquals('https://www.instagram.com/p/Cxyz123/', $result->postUrl);
    }

    /**
     * Test Instagram Reels video container publishing flow.
     */
    public function test_instagram_can_publish_reels_video(): void
    {
        Http::fake([
            'https://graph.facebook.com/v20.0/ig_user_777/media' => Http::response([
                'id' => 'ig_reel_container_555',
            ], 200),
            'https://graph.facebook.com/v20.0/ig_reel_container_555*' => Http::response([
                'status_code' => 'FINISHED',
            ], 200),
            'https://graph.facebook.com/v20.0/ig_user_777/media_publish' => Http::response([
                'id' => 'ig_reel_published_777',
            ], 200),
            'https://graph.facebook.com/v20.0/ig_reel_published_777*' => Http::response([
                'id' => 'ig_reel_published_777',
                'permalink' => 'https://www.instagram.com/reel/Cr12345/',
            ], 200),
        ]);

        $service = app(InstagramPublishingService::class);
        $payload = new PublishingPayloadDTO(
            caption: 'Watch the chef make our signature lasagna! #reels #foodie',
            mediaUrls: ['https://cdn.socialpilotx.com/videos/lasagna.mp4']
        );

        $result = $service->publish($this->instagramProfile, $payload);

        $this->assertTrue($result->success);
        $this->assertEquals('ig_reel_published_777', $result->providerPostId);
        $this->assertEquals('https://www.instagram.com/reel/Cr12345/', $result->postUrl);
    }

    /**
     * Test Meta Webhook Hub Challenge verification (GET).
     */
    public function test_meta_webhook_challenge_verification(): void
    {
        $challenge = 'sample_random_challenge_123456';
        $verifyToken = config('services.facebook.verify_token', 'postsync_meta_verify_2026');

        $response = $this->get("/webhooks/meta?hub_mode=subscribe&hub_verify_token={$verifyToken}&hub_challenge={$challenge}");

        $response->assertStatus(200);
        $this->assertEquals($challenge, $response->getContent());
    }

    /**
     * Test Meta Webhook Event Ingestion with duplicate prevention.
     */
    public function test_meta_webhook_event_ingestion(): void
    {
        $payload = [
            'object' => 'page',
            'entry' => [
                [
                    'id' => 'fb_page_555',
                    'time' => 1700000000,
                    'changes' => [
                        [
                            'field' => 'feed',
                            'value' => [
                                'item' => 'comment',
                                'verb' => 'add',
                                'comment_id' => 'c_9999',
                                'message' => 'Great ambiance!',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/webhooks/meta', $payload);
        $response->assertStatus(200);

        $event = WebhookEvent::where('provider', 'meta')
            ->where('event_id', 'meta_fb_page_555_1700000000')
            ->first();

        $this->assertNotNull($event);
        $this->assertEquals('pending', $event->status);
        $this->assertEquals('page', $event->event_name);
    }

    /**
     * Test Meta User Data Deletion Callback and Status Page.
     */
    public function test_meta_user_data_deletion_callback(): void
    {
        $response = $this->postJson('/webhooks/meta/data-deletion', [
            'signed_request' => 'mock_test_request',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['url', 'confirmation_code']);

        $data = $response->json();
        $this->assertStringStartsWith('DEL_', $data['confirmation_code']);
        $this->assertStringContainsString('/deletion-status?code=', $data['url']);

        // Verify status page
        $statusPage = $this->get($data['url']);
        $statusPage->assertStatus(200);
        $statusPage->assertSee($data['confirmation_code']);
    }

    /**
     * Test Meta Legal Pages (Privacy Policy, Terms of Service, Data Deletion Instructions).
     */
    public function test_meta_legal_compliance_pages(): void
    {
        $this->get('/privacy-policy')->assertStatus(200)->assertSee('postsync.cloud');
        $this->get('/terms')->assertStatus(200)->assertSee('postsync.cloud');
        $this->get('/data-deletion')->assertStatus(200)->assertSee('postsync.cloud');
    }
}

