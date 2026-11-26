<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Enums\SocialPlatform;
use App\Livewire\Composer\PostComposer;
use App\Models\Client;
use App\Models\MediaAsset;
use App\Models\Post;
use App\Models\PostVariant;
use App\Models\SocialConnection;
use App\Models\SocialProfile;
use App\Models\User;
use App\Services\Publishing\PostCreationService;
use App\Services\Tenancy\WorkspaceManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContentComposerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected $workspace;
    protected SocialProfile $facebookProfile;
    protected SocialProfile $instagramProfile;
    protected MediaAsset $mediaAsset;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $workspaceManager = app(WorkspaceManagerService::class);
        $this->workspace = $workspaceManager->createWorkspace($this->user, 'Creative Digital Agency');

        $this->actingAs($this->user);
        session(['active_workspace_id' => $this->workspace->id]);
        app()->instance('current_workspace', $this->workspace);

        $connection = SocialConnection::create([
            'workspace_id' => $this->workspace->id,
            'platform' => SocialPlatform::FACEBOOK,
            'account_id' => 'fb_acc_9001',
            'account_name' => 'Agency Master Account',
            'access_token' => 'mock_token',
            'scopes' => ['pages_show_list'],
        ]);

        $this->facebookProfile = SocialProfile::create([
            'workspace_id' => $this->workspace->id,
            'connection_id' => $connection->id,
            'platform' => 'facebook',
            'profile_id' => 'fb_page_100',
            'name' => 'Sharma Restaurant Page',
            'capabilities' => ['supports_text' => true, 'supports_images' => true],
            'publishing_enabled' => true,
        ]);

        $this->instagramProfile = SocialProfile::create([
            'workspace_id' => $this->workspace->id,
            'connection_id' => $connection->id,
            'platform' => 'instagram',
            'profile_id' => 'ig_acc_200',
            'name' => 'sharmarestaurant_ig',
            'username' => 'sharmarestaurant',
            'capabilities' => ['supports_images' => true, 'supports_reels' => true],
            'publishing_enabled' => true,
        ]);

        $this->mediaAsset = MediaAsset::create([
            'workspace_id' => $this->workspace->id,
            'uploader_id' => $this->user->id,
            'file_name' => 'thali_special.jpg',
            'storage_path' => 'workspaces/1/media/thali.jpg',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'file_size' => 102400,
            'media_type' => 'image',
            'width' => 1080,
            'height' => 1080,
            'aspect_ratio' => '1:1',
            'public_url' => 'https://cdn.socialpilotx.com/thali.jpg',
        ]);
    }

    /**
     * Test creating a multi-profile post via Livewire Composer.
     */
    public function test_can_compose_and_schedule_multi_platform_post(): void
    {
        Livewire::test(PostComposer::class)
            ->set('selectedProfileIds', [$this->facebookProfile->id, $this->instagramProfile->id])
            ->set('baseCaption', 'Weekend Buffet is Live! Unlimited starters and desserts.')
            ->set('selectedMediaAssetIds', [$this->mediaAsset->id])
            ->set('publishMode', 'schedule')
            ->set('scheduledDate', now()->addDays(2)->format('Y-m-d'))
            ->set('scheduledTime', '18:30')
            ->call('savePost', 'schedule')
            ->assertHasNoErrors()
            ->assertRedirect(route('posts.index'));

        $post = Post::where('base_caption', 'like', 'Weekend Buffet%')->first();
        $this->assertNotNull($post);
        $this->assertEquals(PostStatus::SCHEDULED, $post->status);
        $this->assertEquals($this->workspace->id, $post->workspace_id);

        // Verify variants created for both profiles
        $this->assertCount(2, $post->variants);

        $fbVariant = $post->variants()->where('social_profile_id', $this->facebookProfile->id)->first();
        $this->assertNotNull($fbVariant);
        $this->assertCount(1, $fbVariant->mediaAssets);

        $igVariant = $post->variants()->where('social_profile_id', $this->instagramProfile->id)->first();
        $this->assertNotNull($igVariant);
        $this->assertCount(1, $igVariant->mediaAssets);
    }

    /**
     * Test independent custom caption for specific platform variant.
     */
    public function test_can_customize_caption_for_individual_variant(): void
    {
        $creationService = app(PostCreationService::class);

        $post = $creationService->createPost($this->workspace, $this->user, [
            'base_caption' => 'Default master caption for all networks.',
            'profile_ids' => [$this->facebookProfile->id, $this->instagramProfile->id],
            'variant_captions' => [
                $this->instagramProfile->id => 'Instagram-tailored caption with #hashtags #foodie',
            ],
            'media_asset_ids' => [$this->mediaAsset->id],
            'status' => 'draft',
        ]);

        $this->assertCount(2, $post->variants);

        $fbVariant = $post->variants()->where('social_profile_id', $this->facebookProfile->id)->first();
        $this->assertEquals('Default master caption for all networks.', $fbVariant->getEffectiveCaption());

        $igVariant = $post->variants()->where('social_profile_id', $this->instagramProfile->id)->first();
        $this->assertEquals('Instagram-tailored caption with #hashtags #foodie', $igVariant->getEffectiveCaption());
    }

    /**
     * Test post duplication into fresh draft.
     */
    public function test_can_duplicate_post_with_variants(): void
    {
        $creationService = app(PostCreationService::class);

        $originalPost = $creationService->createPost($this->workspace, $this->user, [
            'title' => 'Sunday Campaign',
            'base_caption' => 'Master announcement.',
            'profile_ids' => [$this->facebookProfile->id],
            'media_asset_ids' => [$this->mediaAsset->id],
            'status' => 'scheduled',
            'scheduled_at' => now()->addDay(),
        ]);

        $duplicatedPost = $creationService->duplicatePost($originalPost, $this->user);

        $this->assertNotEquals($originalPost->id, $duplicatedPost->id);
        $this->assertEquals(PostStatus::DRAFT, $duplicatedPost->status);
        $this->assertStringContainsString('Copy of Sunday Campaign', $duplicatedPost->title);
        $this->assertCount(1, $duplicatedPost->variants);
    }

    /**
     * Test configuring YouTube video settings and publishing now.
     */
    public function test_can_configure_youtube_settings_and_publish_now(): void
    {
        $ytConnection = SocialConnection::create([
            'workspace_id' => $this->workspace->id,
            'platform' => SocialPlatform::YOUTUBE,
            'account_id' => 'yt_acc_1',
            'account_name' => 'Shadab Khan YT',
            'access_token' => 'mock_yt_token',
            'scopes' => ['https://www.googleapis.com/auth/youtube.upload'],
        ]);

        $ytProfile = SocialProfile::create([
            'workspace_id' => $this->workspace->id,
            'connection_id' => $ytConnection->id,
            'platform' => 'youtube',
            'profile_id' => 'yt_ch_1',
            'name' => 'Shadab Khan (YouTube Channel)',
            'capabilities' => ['supports_video' => true],
            'publishing_enabled' => true,
        ]);

        $videoAsset = MediaAsset::create([
            'workspace_id' => $this->workspace->id,
            'uploader_id' => $this->user->id,
            'file_name' => 'promo_short.mp4',
            'storage_path' => 'workspaces/1/media/promo_short.mp4',
            'disk' => 'public',
            'mime_type' => 'video/mp4',
            'file_size' => 2048000,
            'media_type' => 'video',
            'width' => 1080,
            'height' => 1920,
            'aspect_ratio' => '9:16',
            'public_url' => 'https://cdn.socialpilotx.com/promo_short.mp4',
        ]);

        Livewire::test(PostComposer::class)
            ->set('selectedProfileIds', [$ytProfile->id])
            ->set('baseCaption', 'Testing YouTube Shorts upload!')
            ->set('selectedMediaAssetIds', [$videoAsset->id])
            ->call('setTab', (string) $ytProfile->id)
            ->set("platformSettings.{$ytProfile->id}.post_type", 'short')
            ->set("platformSettings.{$ytProfile->id}.title", 'My Test Short')
            ->set("platformSettings.{$ytProfile->id}.privacy_status", 'public')
            ->set('publishMode', 'now')
            ->call('savePost', 'now')
            ->assertHasNoErrors();

        $variant = PostVariant::where('social_profile_id', $ytProfile->id)->first();
        $this->assertNotNull($variant);
        $this->assertEquals('short', $variant->platform_settings['post_type'] ?? null);
        $this->assertEquals('My Test Short', $variant->platform_settings['title'] ?? null);
        $this->assertEquals('public', $variant->platform_settings['privacy_status'] ?? null);
    }
}

