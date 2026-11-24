<?php

namespace Tests\Feature;

use App\Enums\WorkspaceRole;
use App\Livewire\Clients\ClientManager;
use App\Livewire\Team\MemberManager;
use App\Models\Client;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\Clients\ClientService;
use App\Services\Tenancy\WorkspaceManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ClientManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test creating a client inside active workspace.
     */
    public function test_can_create_client_inside_active_workspace(): void
    {
        $owner = User::factory()->create();
        $workspaceManager = app(WorkspaceManagerService::class);
        $workspace = $workspaceManager->createWorkspace($owner, 'Sharma Media Agency');

        $this->actingAs($owner);
        session(['active_workspace_id' => $workspace->id]);

        Livewire::test(ClientManager::class)
            ->call('openCreateModal')
            ->set('name', 'Sharma Restaurant')
            ->set('client_code', 'SHAR-01')
            ->set('contact_person', 'Rajesh Sharma')
            ->set('contact_email', 'rajesh@sharmarestaurant.com')
            ->set('timezone', 'Asia/Kolkata')
            ->set('status', 'active')
            ->call('saveClient')
            ->assertHasNoErrors();

        $client = Client::where('name', 'Sharma Restaurant')->first();
        $this->assertNotNull($client);
        $this->assertEquals($workspace->id, $client->workspace_id);
        $this->assertEquals('SHAR-01', $client->client_code);
    }

    /**
     * Test tenant isolation on clients.
     */
    public function test_tenant_scope_isolates_clients_between_workspaces(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $workspaceManager = app(WorkspaceManagerService::class);
        $workspaceA = $workspaceManager->createWorkspace($userA, 'Agency Alpha');
        $workspaceB = $workspaceManager->createWorkspace($userB, 'Agency Beta');

        $clientService = app(ClientService::class);
        $clientA = $clientService->createClient($workspaceA, ['name' => 'Alpha Client 1', 'client_code' => 'ALP-01']);
        $clientB = $clientService->createClient($workspaceB, ['name' => 'Beta Client 1', 'client_code' => 'BET-01']);

        // User A context
        $this->actingAs($userA);
        session(['active_workspace_id' => $workspaceA->id]);
        app()->instance('current_workspace', $workspaceA);

        $visibleClientsA = Client::all();
        $this->assertTrue($visibleClientsA->contains($clientA));
        $this->assertFalse($visibleClientsA->contains($clientB));

        // User B context
        $this->actingAs($userB);
        session(['active_workspace_id' => $workspaceB->id]);
        app()->instance('current_workspace', $workspaceB);

        $visibleClientsB = Client::all();
        $this->assertFalse($visibleClientsB->contains($clientA));
        $this->assertTrue($visibleClientsB->contains($clientB));
    }

    /**
     * Test assigning member to client with approval rights.
     */
    public function test_can_assign_member_to_client(): void
    {
        $owner = User::factory()->create();
        $editor = User::factory()->create();

        $workspaceManager = app(WorkspaceManagerService::class);
        $workspace = $workspaceManager->createWorkspace($owner, 'Creative Hub');

        // Add editor to workspace
        WorkspaceMember::create([
            'workspace_id' => $workspace->id,
            'user_id' => $editor->id,
            'role' => WorkspaceRole::EDITOR,
            'is_active' => true,
        ]);

        $clientService = app(ClientService::class);
        $client = $clientService->createClient($workspace, ['name' => 'Fashion Store', 'client_code' => 'FASH-01']);

        $this->actingAs($owner);
        session(['active_workspace_id' => $workspace->id]);
        app()->instance('current_workspace', $workspace);

        Livewire::test(ClientManager::class)
            ->call('openAssignModal', $client->id)
            ->set('assignTargetUserId', $editor->id)
            ->set('assignCanApprove', true)
            ->call('assignMember')
            ->assertHasNoErrors();

        $this->assertTrue($client->fresh()->isAssigned($editor));
        $memberPivot = $client->fresh()->users()->where('users.id', $editor->id)->first()->pivot;
        $this->assertTrue((bool)$memberPivot->can_approve);
    }

    /**
     * Test team member manager can invite members and update roles.
     */
    public function test_can_invite_team_member_and_update_roles(): void
    {
        $owner = User::factory()->create();
        $workspaceManager = app(WorkspaceManagerService::class);
        $workspace = $workspaceManager->createWorkspace($owner, 'Vanguard Marketing');

        $this->actingAs($owner);
        session(['active_workspace_id' => $workspace->id]);
        app()->instance('current_workspace', $workspace);

        Livewire::test(MemberManager::class)
            ->call('openInviteModal')
            ->set('inviteEmail', 'sarah@vanguard.com')
            ->set('inviteRole', 'manager')
            ->call('sendInvitation')
            ->assertHasNoErrors();

        $invitation = $workspace->invitations()->where('email', 'sarah@vanguard.com')->first();
        $this->assertNotNull($invitation);
        $this->assertEquals(WorkspaceRole::MANAGER, $invitation->role);
    }
}

