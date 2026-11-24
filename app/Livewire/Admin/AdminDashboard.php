<?php

namespace App\Livewire\Admin;

use App\Enums\PostStatus;
use App\Enums\WorkspaceRole;
use App\Models\MediaAsset;
use App\Models\Post;
use App\Models\SocialProfile;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\Tenancy\WorkspaceManagerService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class AdminDashboard extends Component
{
    use WithPagination;

    public string $activeTab = 'users'; // 'users', 'workspaces', 'social_profiles'

    // Search filters
    public string $userSearch = '';
    public string $workspaceSearch = '';
    public string $profileSearch = '';

    // Team detail modal
    public bool $showTeamModal = false;
    public ?int $selectedWorkspaceId = null;
    public ?Workspace $selectedWorkspace = null;

    protected $queryString = [
        'activeTab' => ['except' => 'users'],
    ];

    public function updatedUserSearch(): void
    {
        $this->resetPage('usersPage');
    }

    public function updatedWorkspaceSearch(): void
    {
        $this->resetPage('workspacesPage');
    }

    public function updatedProfileSearch(): void
    {
        $this->resetPage('profilesPage');
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function switchIntoWorkspace(int $workspaceId, WorkspaceManagerService $workspaceManager)
    {
        $workspace = $workspaceManager->switchWorkspace(auth()->user(), $workspaceId);
        session()->flash('success', "Switched into Workspace: {$workspace->name} ({$workspace->slug})");
        return $this->redirect(route('dashboard'), navigate: true);
    }

    public function toggleSuperAdmin(int $userId): void
    {
        if ($userId === auth()->id()) {
            session()->flash('error', "You cannot remove your own super administrator privileges.");
            return;
        }

        $user = User::findOrFail($userId);
        $user->is_super_admin = !$user->is_super_admin;
        $user->save();

        $status = $user->is_super_admin ? 'promoted to Super Admin' : 'demoted to Regular User';
        session()->flash('success', "User '{$user->name}' ({$user->email}) has been {$status}.");
    }

    public function toggleWorkspaceStatus(int $workspaceId): void
    {
        $ws = Workspace::findOrFail($workspaceId);
        $ws->is_active = !$ws->is_active;
        $ws->save();

        $status = $ws->is_active ? 'activated' : 'deactivated';
        session()->flash('success', "Workspace '{$ws->name}' has been {$status}.");
    }

    public function openTeamModal(int $workspaceId): void
    {
        $this->selectedWorkspaceId = $workspaceId;
        $this->selectedWorkspace = Workspace::with(['owner', 'members.user'])->findOrFail($workspaceId);
        $this->showTeamModal = true;
    }

    public function closeTeamModal(): void
    {
        $this->showTeamModal = false;
        $this->selectedWorkspaceId = null;
        $this->selectedWorkspace = null;
    }

    public function updateMemberRole(int $memberId, string $newRole): void
    {
        $roleEnum = WorkspaceRole::tryFrom($newRole);
        if (!$roleEnum) {
            session()->flash('error', "Invalid role selected.");
            return;
        }

        $member = WorkspaceMember::findOrFail($memberId);
        $member->role = $roleEnum;
        $member->save();

        if ($this->selectedWorkspaceId) {
            $this->selectedWorkspace = Workspace::with(['owner', 'members.user'])->findOrFail($this->selectedWorkspaceId);
        }

        session()->flash('success', "Member role updated to " . ucfirst($newRole) . ".");
    }

    public function render()
    {
        // 1. High-Level Metrics
        $totalUsers = User::count();
        $totalWorkspaces = Workspace::count();
        $totalActiveWorkspaces = Workspace::where('is_active', true)->count();
        $totalSocialProfiles = SocialProfile::where('is_active', true)->count();
        $totalPosts = Post::count();
        $publishedPosts = Post::where('status', PostStatus::PUBLISHED)->count();
        $totalMedia = MediaAsset::count();

        // 2. Users query with workspaces & team members count
        $usersQuery = User::with([
            'ownedWorkspaces' => function ($q) {
                $q->withCount('members');
            },
            'workspaces'
        ]);

        if (!empty(trim($this->userSearch))) {
            $term = '%' . trim($this->userSearch) . '%';
            $usersQuery->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('email', 'like', $term);
            });
        }

        $users = $usersQuery->latest()->paginate(10, ['*'], 'usersPage');

        // 3. Workspaces query with members breakdown & social profiles
        $workspacesQuery = Workspace::with(['owner', 'members.user', 'socialProfiles']);

        if (!empty(trim($this->workspaceSearch))) {
            $term = '%' . trim($this->workspaceSearch) . '%';
            $workspacesQuery->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('slug', 'like', $term);
            });
        }

        $workspaces = $workspacesQuery->latest()->paginate(10, ['*'], 'workspacesPage');

        // 4. Social Profiles query across entire SaaS
        $profilesQuery = SocialProfile::with(['connection', 'workspace']);

        if (!empty(trim($this->profileSearch))) {
            $term = '%' . trim($this->profileSearch) . '%';
            $profilesQuery->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('platform', 'like', $term);
            });
        }

        $profiles = $profilesQuery->latest()->paginate(12, ['*'], 'profilesPage');

        return view('livewire.admin.admin-dashboard', [
            'metrics' => [
                'totalUsers' => $totalUsers,
                'totalWorkspaces' => $totalWorkspaces,
                'totalActiveWorkspaces' => $totalActiveWorkspaces,
                'totalSocialProfiles' => $totalSocialProfiles,
                'totalPosts' => $totalPosts,
                'publishedPosts' => $publishedPosts,
                'totalMedia' => $totalMedia,
            ],
            'users' => $users,
            'workspaces' => $workspaces,
            'profiles' => $profiles,
        ])->title('Master Control Panel — Super Admin');
    }
}

