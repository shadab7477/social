<?php

namespace Tests\Feature;

use App\Enums\ConnectionStatus;
use App\Enums\SocialPlatform;
use App\Enums\WorkspaceRole;
use App\Livewire\Composer\PostComposer;
use App\Models\Client;
use App\Models\SocialConnection;
use App\Models\SocialProfile;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\AI\AIAssistantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AIAssistantTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Workspace $workspace;
    protected AIAssistantService $aiService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->aiService = app(AIAssistantService::class);

        $this->user = User::factory()->create(['name' => 'Agency Creative Director']);
        $this->workspace = Workspace::create([
            'name' => 'HyperSocial AI Lab',
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
    }

    public function test_can_generate_social_caption_and_tracks_usage(): void
    {
        $caption = $this->aiService->generateCaption(
            $this->workspace,
            'Cyber Monday Tech Mega Sale',
            'urgent',
            'instagram',
            'Click the bio link to shop now!'
        );

        $this->assertNotEmpty($caption);
        $this->assertStringContainsString('Cyber Monday Tech Mega Sale', $caption);
        $this->assertStringContainsString('Click the bio link to shop now!', $caption);

        // Assert usage tracking
        $cycle = now()->format('Y-m');
        $this->assertDatabaseHas('usage_records', [
            'workspace_id' => $this->workspace->id,
            'metric_key' => 'ai_requests_count',
            'billing_cycle_month' => $cycle,
            'usage_count' => 1,
        ]);

        $this->assertDatabaseHas('usage_records', [
            'workspace_id' => $this->workspace->id,
            'metric_key' => 'ai_tokens_used',
            'billing_cycle_month' => $cycle,
        ]);
    }

    public function test_can_rewrite_content(): void
    {
        $original = "Our company is really proud to present our newest product line of athletic shoes that will improve your marathon performance.";
        $shorter = $this->aiService->rewriteContent($this->workspace, $original, 'shorter');

        $this->assertNotEmpty($shorter);
        $this->assertTrue(strlen($shorter) < strlen($original) + 10);
    }

    public function test_can_generate_hashtags(): void
    {
        $content = "Delicious vegan artisan pizza with organic tomato sauce and fresh basil.";
        $hashtags = $this->aiService->generateHashtags($this->workspace, $content, 5, 'instagram');

        $this->assertIsArray($hashtags);
        $this->assertNotEmpty($hashtags);
        foreach ($hashtags as $tag) {
            $this->assertStringStartsWith('#', $tag);
        }
    }

    public function test_composer_ai_modal_workflow(): void
    {
        $client = Client::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Taste of Italy',
            'client_code' => 'TOI01',
            'status' => 'active',
        ]);

        $connection = SocialConnection::create([
            'workspace_id' => $this->workspace->id,
            'platform' => SocialPlatform::FACEBOOK,
            'account_id' => 'fb_conn_99',
            'account_name' => 'Taste of Italy FB',
            'access_token' => 'token',
            'scopes' => ['pages_manage_posts'],
            'status' => ConnectionStatus::CONNECTED,
        ]);

        $profile = SocialProfile::create([
            'workspace_id' => $this->workspace->id,
            'connection_id' => $connection->id,
            'client_id' => $client->id,
            'platform' => SocialPlatform::FACEBOOK,
            'profile_id' => 'fb_page_99',
            'name' => 'Taste of Italy Page',
            'capabilities' => ['supports_images' => true],
            'publishing_enabled' => true,
            'is_active' => true,
        ]);

        Livewire::test(PostComposer::class)
            ->assertOk()
            ->assertSee('AI Assistant')
            ->call('openAiModal', 'base')
            ->assertSet('showAiModal', true)
            ->set('aiAction', 'caption')
            ->set('aiPromptTopic', 'Grand Opening of our new Bistro Location')
            ->set('aiTone', 'enthusiastic')
            ->call('generateAiContent')
            ->assertNotSet('aiGeneratedResult', '')
            ->call('applyAiResult')
            ->assertSet('showAiModal', false);
    }
}

