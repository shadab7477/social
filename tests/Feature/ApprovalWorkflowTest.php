<?php

namespace Tests\Feature;

use App\Enums\ApprovalActionType;
use App\Enums\ApprovalStage;
use App\Enums\ApprovalStatus;
use App\Enums\ConnectionStatus;
use App\Enums\PostStatus;
use App\Enums\SocialPlatform;
use App\Enums\WorkspaceRole;
use App\Livewire\Approvals\ApprovalCenter;
use App\Models\Approval;
use App\Models\Client;
use App\Models\ClientMember;
use App\Models\Post;
use App\Models\PostVariant;
use App\Models\SocialConnection;
use App\Models\SocialProfile;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\Approvals\ApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $editor;
    protected User $clientViewer;
    protected Workspace $workspace;
    protected Client $client;
    protected SocialProfile $profile;
    protected ApprovalService $approvalService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->approvalService = app(ApprovalService::class);

        $this->owner = User::factory()->create(['name' => 'Agency Boss']);
        $this->editor = User::factory()->create(['name' => 'Creative Editor']);
        $this->clientViewer = User::factory()->create(['name' => 'Client Representative']);

        $this->workspace = Workspace::create([
            'name' => 'Starlight Media Agency',
            'owner_id' => $this->owner->id,
            'plan_type' => 'pro',
            'max_profiles' => 20,
        ]);

        WorkspaceMember::create([
            'workspace_id' => $this->workspace->id,
            'user_id' => $this->owner->id,
            'role' => WorkspaceRole::OWNER,
            'is_active' => true,
        ]);

        WorkspaceMember::create([
            'workspace_id' => $this->workspace->id,
            'user_id' => $this->editor->id,
            'role' => WorkspaceRole::EDITOR,
            'is_active' => true,
        ]);

        WorkspaceMember::create([
            'workspace_id' => $this->workspace->id,
            'user_id' => $this->clientViewer->id,
            'role' => WorkspaceRole::CLIENT_VIEWER,
            'is_active' => true,
        ]);

        $this->owner->update(['current_workspace_id' => $this->workspace->id]);
        $this->actingAs($this->owner);
        app()->instance('current_workspace', $this->workspace);

        $this->client = Client::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Gourmet Burger Co.',
            'client_code' => 'GB01',
            'status' => 'active',
        ]);

        // Assign client viewer to this client
        ClientMember::create([
            'client_id' => $this->client->id,
            'user_id' => $this->clientViewer->id,
            'can_approve' => true,
        ]);

        $connection = SocialConnection::create([
            'workspace_id' => $this->workspace->id,
            'platform' => SocialPlatform::FACEBOOK,
            'account_id' => 'fb_conn_555',
            'account_name' => 'Burger Official',
            'access_token' => 'mock_token',
            'scopes' => ['pages_show_list', 'pages_read_engagement', 'pages_manage_posts'],
            'status' => ConnectionStatus::CONNECTED,
        ]);

        $this->profile = SocialProfile::create([
            'workspace_id' => $this->workspace->id,
            'connection_id' => $connection->id,
            'client_id' => $this->client->id,
            'platform' => SocialPlatform::FACEBOOK,
            'profile_id' => 'fb_page_555',
            'name' => 'Gourmet Burger Official Page',
            'capabilities' => ['supports_images' => true],
            'publishing_enabled' => true,
            'is_active' => true,
        ]);
    }

    public function test_submit_for_approval_transitions_post_and_creates_approval_record(): void
    {
        $post = Post::create([
            'workspace_id' => $this->workspace->id,
            'client_id' => $this->client->id,
            'author_id' => $this->editor->id,
            'title' => 'Black Friday Burger Promo',
            'base_caption' => 'Buy one gourmet burger, get one free this Friday!',
            'status' => PostStatus::DRAFT,
            'scheduled_at' => now()->addDays(3),
        ]);

        $variant = PostVariant::create([
            'post_id' => $post->id,
            'social_profile_id' => $this->profile->id,
            'status' => PostStatus::DRAFT,
            'scheduled_at' => now()->addDays(3),
        ]);

        $approval = $this->approvalService->submitForApproval($post, $this->editor);

        $this->assertNotNull($approval);
        $this->assertEquals(ApprovalStatus::PENDING, $approval->status);
        $this->assertEquals(ApprovalStage::MANAGER_REVIEW, $approval->current_stage);
        $this->assertEquals($this->editor->id, $approval->requested_by);

        // Check parent post and variant statuses
        $this->assertEquals(PostStatus::PENDING_APPROVAL, $post->fresh()->status);
        $this->assertEquals(PostStatus::PENDING_APPROVAL, $variant->fresh()->status);

        // Check audit action
        $this->assertDatabaseHas('approval_actions', [
            'approval_id' => $approval->id,
            'user_id' => $this->editor->id,
            'action' => ApprovalActionType::SUBMITTED->value,
        ]);
    }

    public function test_two_tier_approval_flow_manager_advances_to_client_review_then_final_approves(): void
    {
        $post = Post::create([
            'workspace_id' => $this->workspace->id,
            'client_id' => $this->client->id,
            'author_id' => $this->editor->id,
            'title' => 'Holiday Menu Launch',
            'base_caption' => 'Introducing our winter truffle burger!',
            'status' => PostStatus::PENDING_APPROVAL,
            'scheduled_at' => now()->addDays(5),
            'requires_approval' => true,
        ]);

        $variant = PostVariant::create([
            'post_id' => $post->id,
            'social_profile_id' => $this->profile->id,
            'status' => PostStatus::PENDING_APPROVAL,
            'scheduled_at' => now()->addDays(5),
        ]);

        $approval = Approval::create([
            'post_id' => $post->id,
            'workspace_id' => $this->workspace->id,
            'status' => ApprovalStatus::PENDING,
            'current_stage' => ApprovalStage::MANAGER_REVIEW,
            'requested_by' => $this->editor->id,
        ]);

        // Tier 1: Manager Review approves -> because client has CLIENT_VIEWER, advances to client_review stage
        $this->approvalService->approve($approval, $this->owner);

        $approval->refresh();
        $this->assertEquals(ApprovalStatus::PENDING, $approval->status);
        $this->assertEquals(ApprovalStage::CLIENT_REVIEW, $approval->current_stage);

        // Tier 2: Client Reviewer approves -> final approval reached!
        $this->approvalService->approve($approval, $this->clientViewer);

        $approval->refresh();
        $this->assertEquals(ApprovalStatus::APPROVED, $approval->status);
        $this->assertEquals($this->clientViewer->id, $approval->reviewed_by);
        $this->assertNotNull($approval->reviewed_at);

        // Post was scheduled, so should now be SCHEDULED
        $post->refresh();
        $this->assertEquals(PostStatus::SCHEDULED, $post->status);
        $this->assertEquals(PostStatus::SCHEDULED, $variant->fresh()->status);
    }

    public function test_request_changes_returns_post_to_draft_and_increments_revision(): void
    {
        $post = Post::create([
            'workspace_id' => $this->workspace->id,
            'client_id' => $this->client->id,
            'author_id' => $this->editor->id,
            'title' => 'Draft Ad',
            'base_caption' => 'Initial copy here.',
            'status' => PostStatus::PENDING_APPROVAL,
            'revision' => 1,
        ]);

        $variant = PostVariant::create([
            'post_id' => $post->id,
            'social_profile_id' => $this->profile->id,
            'status' => PostStatus::PENDING_APPROVAL,
        ]);

        $approval = Approval::create([
            'post_id' => $post->id,
            'workspace_id' => $this->workspace->id,
            'status' => ApprovalStatus::PENDING,
            'current_stage' => ApprovalStage::MANAGER_REVIEW,
            'requested_by' => $this->editor->id,
        ]);

        $this->approvalService->requestChanges($approval, $this->owner, 'Please change discount from 10% to 20%');

        $approval->refresh();
        $this->assertEquals(ApprovalStatus::CHANGES_REQUESTED, $approval->status);
        $this->assertEquals('Please change discount from 10% to 20%', $approval->rejection_reason);

        $post->refresh();
        $this->assertEquals(PostStatus::DRAFT, $post->status);
        $this->assertEquals(2, $post->revision); // Incremented revision
        $this->assertEquals(PostStatus::DRAFT, $variant->fresh()->status);
    }

    public function test_reject_cancels_post(): void
    {
        $post = Post::create([
            'workspace_id' => $this->workspace->id,
            'client_id' => $this->client->id,
            'author_id' => $this->editor->id,
            'title' => 'Unwanted Campaign',
            'base_caption' => 'Will be rejected.',
            'status' => PostStatus::PENDING_APPROVAL,
        ]);

        $approval = Approval::create([
            'post_id' => $post->id,
            'workspace_id' => $this->workspace->id,
            'status' => ApprovalStatus::PENDING,
            'current_stage' => ApprovalStage::MANAGER_REVIEW,
            'requested_by' => $this->editor->id,
        ]);

        $this->approvalService->reject($approval, $this->owner, 'Campaign cancelled by leadership');

        $approval->refresh();
        $this->assertEquals(ApprovalStatus::REJECTED, $approval->status);

        $post->refresh();
        $this->assertEquals(PostStatus::CANCELLED, $post->status);
    }

    public function test_adding_comments_and_internal_notes(): void
    {
        $post = Post::create([
            'workspace_id' => $this->workspace->id,
            'client_id' => $this->client->id,
            'author_id' => $this->editor->id,
            'base_caption' => 'Discussion post.',
            'status' => PostStatus::PENDING_APPROVAL,
        ]);

        $approval = Approval::create([
            'post_id' => $post->id,
            'workspace_id' => $this->workspace->id,
            'status' => ApprovalStatus::PENDING,
            'current_stage' => ApprovalStage::MANAGER_REVIEW,
            'requested_by' => $this->editor->id,
        ]);

        // Regular comment
        $comment1 = $this->approvalService->addComment($approval, $this->editor, 'Are we happy with the hashtag?', false);
        $this->assertFalse($comment1->is_internal);

        // Internal note
        $comment2 = $this->approvalService->addComment($approval, $this->owner, 'Check margins with account team first.', true);
        $this->assertTrue($comment2->is_internal);

        $this->assertCount(2, $approval->comments);
    }

    public function test_livewire_approval_center_renders_and_executes_actions(): void
    {
        $post = Post::create([
            'workspace_id' => $this->workspace->id,
            'client_id' => $this->client->id,
            'author_id' => $this->editor->id,
            'title' => 'Livewire Reviewed Post',
            'base_caption' => 'Approving via UI',
            'status' => PostStatus::PENDING_APPROVAL,
            'scheduled_at' => now()->addDays(2),
        ]);

        $variant = PostVariant::create([
            'post_id' => $post->id,
            'social_profile_id' => $this->profile->id,
            'status' => PostStatus::PENDING_APPROVAL,
            'scheduled_at' => now()->addDays(2),
        ]);

        $approval = Approval::create([
            'post_id' => $post->id,
            'workspace_id' => $this->workspace->id,
            'status' => ApprovalStatus::PENDING,
            'current_stage' => ApprovalStage::MANAGER_REVIEW,
            'requested_by' => $this->editor->id,
        ]);

        Livewire::test(ApprovalCenter::class)
            ->assertOk()
            ->assertSee('Approval Center')
            ->assertSee('Livewire Reviewed Post')
            ->call('approve', $approval->id)
            ->assertSee('processed successfully!');

        // Post had client reviewer, so first approval moves to client_review stage
        $approval->refresh();
        $this->assertEquals(ApprovalStage::CLIENT_REVIEW, $approval->current_stage);

        // Now client approval via UI
        Livewire::test(ApprovalCenter::class)
            ->call('approve', $approval->id);

        $approval->refresh();
        $this->assertEquals(ApprovalStatus::APPROVED, $approval->status);
        $this->assertEquals(PostStatus::SCHEDULED, $post->fresh()->status);
    }
}
