<?php

namespace Tests\Feature;

use App\Enums\ConnectionStatus;
use App\Enums\PostStatus;
use App\Enums\SocialPlatform;
use App\Enums\WorkspaceRole;
use App\Jobs\Analytics\SyncProfileAnalyticsJob;
use App\Livewire\Analytics\AnalyticsDashboard;
use App\Models\AnalyticsSnapshot;
use App\Models\Client;
use App\Models\Post;
use App\Models\PostVariant;
use App\Models\SocialConnection;
use App\Models\SocialProfile;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\Social\Contracts\AnalyticsProviderInterface;
use App\Services\Social\Registry\SocialProviderManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class AnalyticsDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Workspace $workspace;
    protected Client $client;
    protected SocialProfile $facebookProfile;
    protected SocialProfile $instagramProfile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->workspace = Workspace::create([
            'name' => 'Apex Digital',
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
            'name' => 'Bella Italian Trattoria',
            'client_code' => 'BIT01',
            'status' => 'active',
        ]);

        $connection = SocialConnection::create([
            'workspace_id' => $this->workspace->id,
            'platform' => SocialPlatform::FACEBOOK,
            'account_id' => 'fb_user_888',
            'account_name' => 'Bella Manager',
            'access_token' => 'mock_token',
            'scopes' => ['pages_show_list', 'pages_read_engagement', 'read_insights'],
            'status' => ConnectionStatus::CONNECTED,
        ]);

        $this->facebookProfile = SocialProfile::create([
            'workspace_id' => $this->workspace->id,
            'connection_id' => $connection->id,
            'client_id' => $this->client->id,
            'platform' => SocialPlatform::FACEBOOK,
            'profile_id' => 'fb_page_888',
            'name' => 'Bella Trattoria FB Page',
            'capabilities' => ['supports_analytics' => true],
            'publishing_enabled' => true,
            'is_active' => true,
        ]);

        $this->instagramProfile = SocialProfile::create([
            'workspace_id' => $this->workspace->id,
            'connection_id' => $connection->id,
            'client_id' => $this->client->id,
            'platform' => SocialPlatform::INSTAGRAM,
            'profile_id' => 'ig_user_999',
            'name' => 'bellatrattoria_ig',
            'capabilities' => ['supports_analytics' => true],
            'publishing_enabled' => true,
            'is_active' => true,
        ]);
    }

    public function test_sync_profile_analytics_job_ingests_metrics_and_logs_execution(): void
    {
        $mockProvider = Mockery::mock(AnalyticsProviderInterface::class);
        $mockProvider->shouldReceive('fetchProfileMetrics')
            ->once()
            ->andReturn([
                [
                    'metric_name' => 'impressions',
                    'metric_value' => 1500,
                    'metric_date' => now()->toDateString(),
                    'period' => 'day',
                ],
                [
                    'metric_name' => 'reach',
                    'metric_value' => 1100,
                    'metric_date' => now()->toDateString(),
                    'period' => 'day',
                ],
            ]);

        $mockProvider->shouldReceive('fetchPostMetrics')
            ->andReturn([]);

        $providerManager = Mockery::mock(SocialProviderManager::class);
        $providerManager->shouldReceive('getAnalyticsProvider')
            ->with(SocialPlatform::FACEBOOK)
            ->andReturn($mockProvider);

        $job = new SyncProfileAnalyticsJob($this->facebookProfile->id);
        $job->handle($providerManager);

        $this->assertDatabaseHas('analytics_snapshots', [
            'social_profile_id' => $this->facebookProfile->id,
            'metric_name' => 'impressions',
            'metric_value' => 1500.00,
        ]);

        $this->assertDatabaseHas('analytics_sync_logs', [
            'social_profile_id' => $this->facebookProfile->id,
            'status' => 'success',
            'records_ingested' => 2,
        ]);
    }

    public function test_analytics_dashboard_renders_and_computes_kpis(): void
    {
        // Seed snapshots
        AnalyticsSnapshot::create([
            'workspace_id' => $this->workspace->id,
            'social_profile_id' => $this->facebookProfile->id,
            'metric_date' => now()->subDays(2)->toDateString(),
            'metric_name' => 'impressions',
            'metric_value' => 4500,
        ]);

        AnalyticsSnapshot::create([
            'workspace_id' => $this->workspace->id,
            'social_profile_id' => $this->facebookProfile->id,
            'metric_date' => now()->subDays(2)->toDateString(),
            'metric_name' => 'reach',
            'metric_value' => 3000,
        ]);

        AnalyticsSnapshot::create([
            'workspace_id' => $this->workspace->id,
            'social_profile_id' => $this->facebookProfile->id,
            'metric_date' => now()->subDays(2)->toDateString(),
            'metric_name' => 'engagement',
            'metric_value' => 300,
        ]);

        Livewire::test(AnalyticsDashboard::class)
            ->assertOk()
            ->assertSee('Analytics')
            ->assertSee('Total Impressions')
            ->assertSee('4,500') // Impressions formatted
            ->assertSee('3,000') // Reach formatted
            ->assertSee('10%');  // Engagement rate (300 / 3000 = 10%)
    }

    public function test_analytics_dashboard_dispatches_sync_jobs_via_sync_now(): void
    {
        Queue::fake();

        Livewire::test(AnalyticsDashboard::class)
            ->call('syncNow')
            ->assertSee('Analytics synchronization queued');

        Queue::assertPushed(SyncProfileAnalyticsJob::class, 2);
    }
}
