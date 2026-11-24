<?php

namespace Tests\Feature;

use App\Enums\WorkspaceRole;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceMember;
use App\Services\Tenancy\WorkspaceManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceInvitationAndMultiTenantClientsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test a user can create a new workspace for a different client.
     */
    public function test_user_can_create_new_client_workspace(): void
    {
        $subscriber = User::factory()->create(['email' => 'sk@gmail.com']);
        $workspaceManager = app(WorkspaceManagerService::class);
        $primaryWorkspace = $workspaceManager->createWorkspace($subscriber, 'Primary Agency');

        $this->actingAs($subscriber);
        session(['active_workspace_id' => $primaryWorkspace->id]);
        app()->instance('current_workspace', $primaryWorkspace);

        // Create a new client workspace
        $response = $this->post(route('workspaces.create'), [
            'name' => 'Nike India Campaign',
            'timezone' => 'Asia/Kolkata',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('workspaces', [
            'name' => 'Nike India Campaign',
            'owner_id' => $subscriber->id,
        ]);

        $newWs = Workspace::where('name', 'Nike India Campaign')->first();
        $this->assertEquals($newWs->id, session('active_workspace_id'));
    }

    /**
     * Test workspace invitation via shareable URL acceptance.
     */
    public function test_team_member_can_accept_invitation_via_url(): void
    {
        $owner = User::factory()->create(['email' => 'sk@gmail.com']);
        $workspaceManager = app(WorkspaceManagerService::class);
        $workspace = $workspaceManager->createWorkspace($owner, 'Domino Agency Workspace');

        // Create invite for a team member
        $invitation = $workspaceManager->inviteMember(
            $workspace,
            $owner,
            'editor@partner.com',
            WorkspaceRole::EDITOR
        );

        $this->assertNotEmpty($invitation->token);

        // An existing or new team member accepts the URL
        $teamMember = User::factory()->create(['email' => 'editor@partner.com']);
        $this->actingAs($teamMember);

        $acceptUrl = route('invitations.accept', ['token' => $invitation->token]);
        $response = $this->get($acceptUrl);

        $response->assertRedirect(route('dashboard'));

        // Verify membership created with role EDITOR
        $this->assertDatabaseHas('workspace_members', [
            'workspace_id' => $workspace->id,
            'user_id' => $teamMember->id,
            'role' => WorkspaceRole::EDITOR->value,
            'is_active' => true,
        ]);

        $invitation->refresh();
        $this->assertTrue($invitation->isAccepted());
    }

    /**
     * Test guest clicking invite link is redirected to login then auto-joins.
     */
    public function test_guest_clicking_invite_link_is_directed_to_login(): void
    {
        $owner = User::factory()->create();
        $workspaceManager = app(WorkspaceManagerService::class);
        $workspace = $workspaceManager->createWorkspace($owner, 'Client Beta');

        $invitation = $workspaceManager->inviteMember(
            $workspace,
            $owner,
            'newmember@domain.com',
            WorkspaceRole::MANAGER
        );

        // Guest visits invite URL
        $response = $this->get(route('invitations.accept', ['token' => $invitation->token]));
        $response->assertRedirect(route('login'));

        $this->assertEquals($invitation->token, session('pending_invite_token'));
    }
}
