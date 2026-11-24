<?php

namespace Tests\Feature;

use App\Enums\ConnectionStatus;
use App\Enums\PostStatus;
use App\Enums\SocialPlatform;
use App\Enums\WorkspaceRole;
use App\Jobs\Publishing\DispatchDuePostsJob;
use App\Jobs\Publishing\PublishPostVariantJob;
use App\Livewire\Calendar\ContentCalendar;
use App\Livewire\Posts\PostList;
use App\Models\Client;
use App\Models\Post;
use App\Models\PostVariant;
use App\Models\PublishingRecord;
use App\Models\SocialConnection;
use App\Models\SocialProfile;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\Social\Contracts\PublishingProviderInterface;
use App\Services\Social\DTOs\PublishingResultDTO;
use App\Services\Social\Registry\SocialProviderManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class PublishingEngineAndCalendarTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Workspace $workspace;
    protected Client $client;
    protected SocialProfile $profile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->workspace = Workspace::create([
            'name' => 'Agency HQ',
            'owner_id' => $this->user->id,
            'plan_type' => 'pro',
            'max_profiles' => 20,
        ]);

        WorkspaceMember::create([
            'workspace_id' => $this->workspace->id,
            'user_id' => $this->user->id,
            'role' => WorkspaceRole::OWNER,
            'is_active' => true,
        ]);

        $this->user->update(['current_workspace_id' => $this->workspace->id]);
        $this->actingAs($this->user);
        app()->instance('current_workspace', $this->workspace);

        $this->client = Client::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Pizza House',
            'client_code' => 'PZ01',
            'status' => 'active',
        ]);

        $connection = SocialConnection::create([
            'workspace_id' => $this->workspace->id,
            'platform' => SocialPlatform::FACEBOOK,
            'account_id' => 'fb_user_123',
            'account_name' => 'John Marketer',
            'access_token' => 'mock_token',
            'scopes' => ['pages_show_list', 'pages_read_engagement', 'pages_manage_posts'],
            'status' => ConnectionStatus::CONNECTED,
        ]);

        $this->profile = SocialProfile::create([
            'workspace_id' => $this->workspace->id,
            'connection_id' => $connection->id,
            'client_id' => $this->client->id,
            'platform' => SocialPlatform::FACEBOOK,
            'profile_id' => 'fb_page_999',
            'name' => 'Pizza House Official Page',
            'capabilities' => ['supports_images' => true, 'supports_video' => true],
            'publishing_enabled' => true,
            'is_active' => true,
        ]);
    }

    public function test_dispatch_due_posts_job_claims_and_queues_due_variants(): void
    {
        Queue::fake();

        $post = Post::create([
            'workspace_id' => $this->workspace->id,
            'client_id' => $this->client->id,
            'author_id' => $this->user->id,
            'title' => 'Flash Sale Post',
            'base_caption' => '50% off on all pizzas today!',
            'status' => PostStatus::SCHEDULED,
            'scheduled_at' => now()->subMinute(),
        ]);

        $variant = PostVariant::create([
            'post_id' => $post->id,
            'social_profile_id' => $this->profile->id,
            'status' => PostStatus::SCHEDULED,
            'scheduled_at' => now()->subMinute(),
        ]);

        // Run dispatch due posts job
        (new DispatchDuePostsJob())->handle();

        // Assert record created and queued
        $this->assertDatabaseHas('publishing_records', [
            'post_variant_id' => $variant->id,
            'status' => 'queued',
        ]);

        $this->assertEquals(PostStatus::QUEUED, $variant->fresh()->status);

        Queue::assertPushedOn('publishing', PublishPostVariantJob::class);
    }

    public function test_publish_post_variant_job_successfully_publishes_and_updates_post_status(): void
    {
        $post = Post::create([
            'workspace_id' => $this->workspace->id,
            'client_id' => $this->client->id,
            'author_id' => $this->user->id,
            'title' => 'Weekly Special',
            'base_caption' => 'Try our new Pepperoni Special!',
            'status' => PostStatus::SCHEDULED,
            'scheduled_at' => now(),
        ]);

        $variant = PostVariant::create([
            'post_id' => $post->id,
            'social_profile_id' => $this->profile->id,
            'status' => PostStatus::QUEUED,
            'scheduled_at' => now(),
        ]);

        $publishingRecord = PublishingRecord::create([
            'post_variant_id' => $variant->id,
            'idempotency_key' => 'idem-test-123',
            'status' => 'queued',
        ]);

        // Mock SocialProviderManager & PublishingProvider
        $mockProvider = Mockery::mock(PublishingProviderInterface::class);
        $mockProvider->shouldReceive('publish')
            ->once()
            ->andReturn(new PublishingResultDTO(
                success: true,
                providerPostId: 'fb_post_777888',
                postUrl: 'https://facebook.com/posts/777888',
                rawResponse: ['id' => 'fb_post_777888']
            ));

        $providerManager = Mockery::mock(SocialProviderManager::class);
        $providerManager->shouldReceive('getPublishingProvider')
            ->with(SocialPlatform::FACEBOOK)
            ->andReturn($mockProvider);

        // Execute worker job
        $job = new PublishPostVariantJob($variant->id, 'idem-test-123');
        $job->handle($providerManager);

        $variant->refresh();
        $this->assertEquals(PostStatus::PUBLISHED, $variant->status);
        $this->assertEquals('fb_post_777888', $variant->external_post_id);
        $this->assertEquals('https://facebook.com/posts/777888', $variant->external_post_url);

        $publishingRecord->refresh();
        $this->assertEquals('completed', $publishingRecord->status);
        $this->assertNotNull($publishingRecord->completed_at);

        $this->assertDatabaseHas('publish_attempts', [
            'publishing_record_id' => $publishingRecord->id,
            'response_status_code' => 200,
        ]);

        // Parent post should now be marked as PUBLISHED
        $post->refresh();
        $this->assertEquals(PostStatus::PUBLISHED, $post->status);
    }

    public function test_publish_post_variant_job_records_failure_and_updates_status(): void
    {
        $post = Post::create([
            'workspace_id' => $this->workspace->id,
            'client_id' => $this->client->id,
            'author_id' => $this->user->id,
            'title' => 'Failing Post',
            'base_caption' => 'Should fail cleanly.',
            'status' => PostStatus::SCHEDULED,
            'scheduled_at' => now(),
        ]);

        $variant = PostVariant::create([
            'post_id' => $post->id,
            'social_profile_id' => $this->profile->id,
            'status' => PostStatus::QUEUED,
            'scheduled_at' => now(),
        ]);

        $publishingRecord = PublishingRecord::create([
            'post_variant_id' => $variant->id,
            'idempotency_key' => 'idem-fail-456',
            'status' => 'queued',
        ]);

        $mockProvider = Mockery::mock(PublishingProviderInterface::class);
        $mockProvider->shouldReceive('publish')
            ->once()
            ->andReturn(new PublishingResultDTO(
                success: false,
                errorMessage: 'Permissions error: manage_pages required',
                errorCode: '200',
                retriable: false
            ));

        $providerManager = Mockery::mock(SocialProviderManager::class);
        $providerManager->shouldReceive('getPublishingProvider')
            ->with(SocialPlatform::FACEBOOK)
            ->andReturn($mockProvider);

        $job = new PublishPostVariantJob($variant->id, 'idem-fail-456');
        $job->handle($providerManager);

        $variant->refresh();
        $this->assertEquals(PostStatus::FAILED, $variant->status);
        $this->assertStringContainsString('manage_pages required', $variant->error_message);

        $publishingRecord->refresh();
        $this->assertEquals('failed', $publishingRecord->status);

        $post->refresh();
        $this->assertEquals(PostStatus::FAILED, $post->status);
    }

    public function test_calendar_component_renders_and_loads_events(): void
    {
        $post = Post::create([
            'workspace_id' => $this->workspace->id,
            'client_id' => $this->client->id,
            'author_id' => $this->user->id,
            'base_caption' => 'Calendar event test post',
            'status' => PostStatus::SCHEDULED,
            'scheduled_at' => now()->addDays(2),
        ]);

        $variant = PostVariant::create([
            'post_id' => $post->id,
            'social_profile_id' => $this->profile->id,
            'status' => PostStatus::SCHEDULED,
            'scheduled_at' => now()->addDays(2),
        ]);

        $component = Livewire::test(ContentCalendar::class)
            ->assertOk()
            ->assertSee('Content Calendar');

        // Test getEvents call
        $events = $component->instance()->getEvents(
            now()->startOfMonth()->toDateString(),
            now()->endOfMonth()->addMonth()->toDateString()
        );

        $this->assertNotEmpty($events);
        $this->assertEquals((string) $variant->id, $events[0]['id']);
        $this->assertEquals('facebook', $events[0]['extendedProps']['platform']);
        $this->assertStringContainsString('Pizza House Official Page', $events[0]['title']);
    }

    public function test_calendar_rescheduling_via_drag_and_drop_action(): void
    {
        $post = Post::create([
            'workspace_id' => $this->workspace->id,
            'client_id' => $this->client->id,
            'author_id' => $this->user->id,
            'base_caption' => 'Drag drop test',
            'status' => PostStatus::SCHEDULED,
            'scheduled_at' => now()->addDay(),
        ]);

        $variant = PostVariant::create([
            'post_id' => $post->id,
            'social_profile_id' => $this->profile->id,
            'status' => PostStatus::SCHEDULED,
            'scheduled_at' => now()->addDay(),
        ]);

        $newDate = now()->addDays(5)->setTime(15, 30);

        Livewire::test(ContentCalendar::class)
            ->call('rescheduleVariantFromCalendar', $variant->id, $newDate->toIso8601String())
            ->assertDispatched('calendar-refresh');

        $variant->refresh();
        $this->assertEquals($newDate->format('Y-m-d H:i'), $variant->scheduled_at->format('Y-m-d H:i'));
        $this->assertEquals(PostStatus::SCHEDULED, $variant->status);
    }

    public function test_posts_directory_renders_and_supports_cloning_and_retrying(): void
    {
        Queue::fake();

        $post = Post::create([
            'workspace_id' => $this->workspace->id,
            'client_id' => $this->client->id,
            'author_id' => $this->user->id,
            'title' => 'Original Master Post',
            'base_caption' => 'Cloneable text caption',
            'status' => PostStatus::FAILED,
        ]);

        $variant = PostVariant::create([
            'post_id' => $post->id,
            'social_profile_id' => $this->profile->id,
            'status' => PostStatus::FAILED,
            'error_message' => 'Rate limit exceeded',
        ]);

        Livewire::test(PostList::class)
            ->assertOk()
            ->assertSee('Posts Directory')
            ->assertSee('Original Master Post')
            ->call('clonePost', $post->id)
            ->assertSee('Post successfully duplicated as a new draft!')
            ->call('retryVariant', $variant->id);

        $this->assertDatabaseHas('posts', [
            'title' => 'Original Master Post (Copy)',
            'status' => PostStatus::DRAFT->value,
        ]);

        Queue::assertPushedOn('publishing', PublishPostVariantJob::class);
    }
}
