<?php

namespace Tests\Feature;

use App\Enums\ConnectionStatus;
use App\Enums\PostStatus;
use App\Enums\ReportFormat;
use App\Enums\ReportStatus;
use App\Enums\SocialPlatform;
use App\Enums\WorkspaceRole;
use App\Livewire\Reports\ReportBuilder;
use App\Models\AnalyticsSnapshot;
use App\Models\Client;
use App\Models\Post;
use App\Models\PostVariant;
use App\Models\Report;
use App\Models\SocialConnection;
use App\Models\SocialProfile;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\Reports\ReportBuilderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ReportGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Workspace $workspace;
    protected Client $client;
    protected SocialProfile $profile;
    protected ReportBuilderService $reportService;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->reportService = app(ReportBuilderService::class);

        $this->user = User::factory()->create(['name' => 'Agency Director']);
        $this->workspace = Workspace::create([
            'name' => 'Nexus Media Agency',
            'owner_id' => $this->user->id,
            'plan_type' => 'enterprise',
            'max_profiles' => 50,
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
            'name' => 'Grand Plaza Hotel',
            'client_code' => 'GPH01',
            'status' => 'active',
        ]);

        $connection = SocialConnection::create([
            'workspace_id' => $this->workspace->id,
            'platform' => SocialPlatform::FACEBOOK,
            'account_id' => 'fb_conn_777',
            'account_name' => 'Grand Plaza Official',
            'access_token' => 'mock_token',
            'scopes' => ['read_insights'],
            'status' => ConnectionStatus::CONNECTED,
        ]);

        $this->profile = SocialProfile::create([
            'workspace_id' => $this->workspace->id,
            'connection_id' => $connection->id,
            'client_id' => $this->client->id,
            'platform' => SocialPlatform::FACEBOOK,
            'profile_id' => 'fb_page_777',
            'name' => 'Grand Plaza Facebook',
            'capabilities' => ['supports_analytics' => true],
            'publishing_enabled' => true,
            'is_active' => true,
        ]);

        // Seed some analytics snapshots
        AnalyticsSnapshot::create([
            'workspace_id' => $this->workspace->id,
            'social_profile_id' => $this->profile->id,
            'metric_date' => now()->subDays(5)->toDateString(),
            'metric_name' => 'impressions',
            'metric_value' => 8400,
        ]);

        AnalyticsSnapshot::create([
            'workspace_id' => $this->workspace->id,
            'social_profile_id' => $this->profile->id,
            'metric_date' => now()->subDays(5)->toDateString(),
            'metric_name' => 'reach',
            'metric_value' => 6200,
        ]);

        AnalyticsSnapshot::create([
            'workspace_id' => $this->workspace->id,
            'social_profile_id' => $this->profile->id,
            'metric_date' => now()->subDays(5)->toDateString(),
            'metric_name' => 'engagement',
            'metric_value' => 520,
        ]);
    }

    public function test_can_generate_csv_report_with_metrics(): void
    {
        $report = $this->reportService->createReport(
            $this->user,
            'Monthly Grand Plaza Performance',
            ReportFormat::CSV,
            now()->subDays(30),
            now(),
            $this->client->id,
            ['agency_name' => 'Nexus Media Agency']
        );

        $this->assertEquals(ReportStatus::READY, $report->status);
        $this->assertNotNull($report->file_path);

        Storage::disk('public')->assertExists($report->file_path);

        $csvContent = Storage::disk('public')->get($report->file_path);
        $this->assertStringContainsString('SOCIAL MEDIA PERFORMANCE REPORT', $csvContent);
        $this->assertStringContainsString('Grand Plaza Hotel', $csvContent);
        $this->assertStringContainsString('Nexus Media Agency', $csvContent);
        $this->assertStringContainsString('8400', $csvContent); // Impressions
        $this->assertStringContainsString('6200', $csvContent); // Reach
    }

    public function test_can_generate_white_labeled_pdf_executive_summary(): void
    {
        $report = $this->reportService->createReport(
            $this->user,
            'Q4 Executive Board Report',
            ReportFormat::PDF,
            now()->subDays(30),
            now(),
            $this->client->id,
            [
                'agency_name' => 'Nexus Custom WhiteLabel',
                'primary_color' => '#059669',
            ]
        );

        $this->assertEquals(ReportStatus::READY, $report->status);
        $this->assertNotNull($report->file_path);

        Storage::disk('public')->assertExists($report->file_path);

        $htmlContent = Storage::disk('public')->get($report->file_path);
        $this->assertStringContainsString('Nexus Custom WhiteLabel', $htmlContent);
        $this->assertStringContainsString('Grand Plaza Hotel', $htmlContent);
        $this->assertStringContainsString('#059669', $htmlContent); // Custom brand color injected
        $this->assertStringContainsString('Total Impressions', $htmlContent);
        $this->assertStringContainsString('8,400', $htmlContent);
    }

    public function test_report_download_controller_enforces_tenant_security(): void
    {
        $report = $this->reportService->createReport(
            $this->user,
            'Secure Agency Report',
            ReportFormat::CSV,
            now()->subDays(7),
            now(),
            $this->client->id
        );

        // 1. Owner of workspace can download report
        $response = $this->get(route('reports.download', $report->id));
        $response->assertOk();
        $response->assertDownload();
        $this->assertStringStartsWith('%PDF-', $response->streamedContent());

        // 2. User from another workspace cannot download report (Tenant isolation check)
        $alienUser = User::factory()->create();
        $alienWorkspace = Workspace::create([
            'name' => 'Intruder Media',
            'owner_id' => $alienUser->id,
        ]);
        WorkspaceMember::create([
            'workspace_id' => $alienWorkspace->id,
            'user_id' => $alienUser->id,
            'role' => WorkspaceRole::OWNER,
            'is_active' => true,
        ]);

        $this->actingAs($alienUser);
        app()->instance('current_workspace', $alienWorkspace);

        $alienResponse = $this->get(route('reports.download', $report->id));
        $alienResponse->assertNotFound();
    }

    public function test_livewire_report_builder_component_actions(): void
    {
        Livewire::test(ReportBuilder::class)
            ->assertOk()
            ->assertSee('Client Reports')
            ->set('reportTitle', 'Spring Campaign Recap')
            ->set('modalFormat', 'pdf')
            ->set('datePreset', '30d')
            ->set('agencyName', 'Nexus Agency')
            ->call('generateReport')
            ->assertSee('Spring Campaign Recap');

        $this->assertDatabaseHas('reports', [
            'title' => 'Spring Campaign Recap',
            'format' => ReportFormat::PDF->value,
            'status' => ReportStatus::READY->value,
        ]);

        // Test configuring recurring schedule
        Livewire::test(ReportBuilder::class)
            ->set('scheduleName', 'Weekly Grand Plaza Summary')
            ->set('scheduleFrequency', 'weekly')
            ->set('scheduleRecipients', 'gm@grandplaza.com, marketing@grandplaza.com')
            ->call('saveSchedule')
            ->assertSee('Weekly Grand Plaza Summary');

        $this->assertDatabaseHas('report_configs', [
            'name' => 'Weekly Grand Plaza Summary',
            'frequency' => 'weekly',
        ]);
    }
}

