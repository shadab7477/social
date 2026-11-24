<?php

namespace Tests\Feature;

use App\Enums\WorkspaceRole;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\Tenancy\WorkspaceManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WorkspaceMultiTenancyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user registration automatically sets up workspace and owner membership.
     */
    public function test_user_can_register_and_automatically_creates_workspace(): void
    {
        Livewire::test(Register::class)
            ->set('name', 'Vikram Sharma')
            ->set('email', 'vikram@apexagency.com')
            ->set('password', 'SecurePass123!')
            ->set('password_confirmation', 'SecurePass123!')
            ->set('workspace_name', 'Apex Digital Agency')
            ->set('timezone', 'Asia/Kolkata')
            ->call('register')
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();

        $user = User::where('email', 'vikram@apexagency.com')->first();
        $this->assertNotNull($user);

        $workspace = Workspace::where('name', 'Apex Digital Agency')->first();
        $this->assertNotNull($workspace);
        $this->assertEquals($user->id, $workspace->owner_id);
        $this->assertEquals('Asia/Kolkata', $workspace->timezone);

        $membership = WorkspaceMember::where('workspace_id', $workspace->id)
            ->where('user_id', $user->id)
            ->first();

        $this->assertNotNull($membership);
        $this->assertEquals(WorkspaceRole::OWNER, $membership->role);
        $this->assertTrue($membership->isOwner());
    }

    /**
     * Test user login resolves active workspace.
     */
    public function test_user_can_login_and_resolves_active_workspace(): void
    {
        $user = User::factory()->create([
            'email' => 'sarah@mediaworks.com',
            'password' => bcrypt('Password123!'),
        ]);

        $workspaceManager = app(WorkspaceManagerService::class);
        $workspace = $workspaceManager->createWorkspace($user, 'MediaWorks International');

        Livewire::test(Login::class)
            ->set('email', 'sarah@mediaworks.com')
            ->set('password', 'Password123!')
            ->call('login')
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertEquals($workspace->id, session('active_workspace_id'));
    }

    /**
     * Test user can switch between multiple workspaces they belong to.
     */
    public function test_user_can_switch_active_workspaces(): void
    {
        $user = User::factory()->create();
        $workspaceManager = app(WorkspaceManagerService::class);

        $workspace1 = $workspaceManager->createWorkspace($user, 'First Workspace');
        $workspace2 = $workspaceManager->createWorkspace($user, 'Second Workspace');

        $this->actingAs($user);

        // Switch to Workspace 1
        $response = $this->post(route('workspace.switch', $workspace1->id));
        $response->assertStatus(302);
        $this->assertEquals($workspace1->id, session('active_workspace_id'));

        // Switch to Workspace 2
        $response = $this->post(route('workspace.switch', $workspace2->id));
        $response->assertStatus(302);
        $this->assertEquals($workspace2->id, session('active_workspace_id'));
    }

    /**
     * Test user cannot switch to a workspace they do not belong to.
     */
    public function test_user_cannot_switch_to_unauthorized_workspace(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $workspaceManager = app(WorkspaceManagerService::class);
        $workspace1 = $workspaceManager->createWorkspace($user1, 'Tenant A Workspace');
        $workspace2 = $workspaceManager->createWorkspace($user2, 'Tenant B Workspace');

        $this->actingAs($user1);

        // User 1 tries to access Workspace 2 (Tenant B)
        $response = $this->post(route('workspace.switch', $workspace2->id));
        $response->assertSessionHasErrors('workspace');
        $this->assertNotEquals($workspace2->id, session('active_workspace_id'));
    }

    /**
     * Test dashboard page requires authentication and workspace resolution.
     */
    public function test_dashboard_requires_auth_and_resolves_workspace(): void
    {
        // Unauthenticated access redirects to login
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));

        // Authenticated access loads dashboard with current workspace
        $user = User::factory()->create();
        $workspaceManager = app(WorkspaceManagerService::class);
        $workspace = $workspaceManager->createWorkspace($user, 'Apex Creative');

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Apex Creative');
        $response->assertSee('Connected Accounts');
    }
}

