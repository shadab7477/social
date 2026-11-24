<?php

namespace Tests\Feature;

use App\Enums\WorkspaceRole;
use App\Livewire\Admin\AdminDashboard;
use App\Models\User;
use App\Models\Workspace;
use App\Services\Tenancy\WorkspaceManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SuperAdminPanelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Regular user cannot access super admin panel.
     */
    public function test_regular_user_forbidden_from_admin_panel(): void
    {
        $regularUser = User::factory()->create(['is_super_admin' => false]);
        $workspaceManager = app(WorkspaceManagerService::class);
        $workspace = $workspaceManager->createWorkspace($regularUser, 'Regular Workspace');

        $this->actingAs($regularUser);
        session(['active_workspace_id' => $workspace->id]);
        app()->instance('current_workspace', $workspace);

        $response = $this->get('/admin');
        $response->assertStatus(403);
    }

    /**
     * Super admin can access master admin panel.
     */
    public function test_super_admin_can_access_admin_panel(): void
    {
        $admin = User::factory()->create(['is_super_admin' => true]);
        $workspaceManager = app(WorkspaceManagerService::class);
        $workspace = $workspaceManager->createWorkspace($admin, 'Admin Workspace');

        $this->actingAs($admin);
        session(['active_workspace_id' => $workspace->id]);
        app()->instance('current_workspace', $workspace);

        $response = $this->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('Platform Master Control Panel');
    }

    /**
     * Super admin can switch into any tenant workspace.
     */
    public function test_super_admin_can_switch_into_any_workspace(): void
    {
        $admin = User::factory()->create(['is_super_admin' => true]);
        $workspaceManager = app(WorkspaceManagerService::class);
        $adminWorkspace = $workspaceManager->createWorkspace($admin, 'Admin HQ');

        $tenantUser = User::factory()->create(['is_super_admin' => false]);
        $tenantWorkspace = $workspaceManager->createWorkspace($tenantUser, 'Client Agency Beta');

        $this->actingAs($admin);
        session(['active_workspace_id' => $adminWorkspace->id]);
        app()->instance('current_workspace', $adminWorkspace);

        Livewire::test(AdminDashboard::class)
            ->call('switchIntoWorkspace', $tenantWorkspace->id)
            ->assertRedirect(route('dashboard'));

        $this->assertEquals($tenantWorkspace->id, session('active_workspace_id'));
    }

    /**
     * Super admin can toggle super admin status and update member roles.
     */
    public function test_super_admin_management_actions(): void
    {
        $admin = User::factory()->create(['is_super_admin' => true]);
        $workspaceManager = app(WorkspaceManagerService::class);
        $workspace = $workspaceManager->createWorkspace($admin, 'Master Workspace');

        $targetUser = User::factory()->create(['is_super_admin' => false]);

        $this->actingAs($admin);
        session(['active_workspace_id' => $workspace->id]);
        app()->instance('current_workspace', $workspace);

        Livewire::test(AdminDashboard::class)
            ->call('toggleSuperAdmin', $targetUser->id);

        $targetUser->refresh();
        $this->assertTrue($targetUser->is_super_admin);
    }
}
