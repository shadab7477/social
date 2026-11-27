<?php

namespace App\Livewire\Admin;

use App\Enums\ConnectionStatus;
use App\Enums\PostStatus;
use App\Enums\SocialPlatform;
use App\Enums\WorkspaceRole;
use App\Jobs\Analytics\SyncProfileAnalyticsJob;
use App\Jobs\Publishing\DispatchDuePostsJob;
use App\Jobs\Publishing\PublishPostVariantJob;
use App\Models\AnalyticsSnapshot;
use App\Models\AnalyticsSyncLog;
use App\Models\MediaAsset;
use App\Models\Post;
use App\Models\PostVariant;
use App\Models\SocialConnection;
use App\Models\SocialProfile;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\Social\Registry\SocialProviderManager;
use App\Services\Tenancy\WorkspaceManagerService;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class AdminDashboard extends Component
{
    use WithPagination;

    public string $activeTab = 'overview'; // 'overview', 'users', 'workspaces', 'social_profiles', 'posts', 'diagnostics'

    // Search filters
    public string $userSearch = '';
    public string $workspaceSearch = '';
    public string $profileSearch = '';
    public string $postSearch = '';
    public string $postStatusFilter = '';
    public string $platformFilter = '';

    // Team detail modal
    public bool $showTeamModal = false;
    public ?int $selectedWorkspaceId = null;
    public ?Workspace $selectedWorkspace = null;

    // Reset password modal
    public bool $showPasswordModal = false;
    public ?int $passwordUserId = null;
    public string $passwordUserName = '';
    public string $newPassword = '';

    // Create user modal
    public bool $showCreateUserModal = false;
    public string $newUserName = '';
    public string $newUserEmail = '';
    public string $newUserPassword = '';
    public bool $newUserIsAdmin = false;

    // Edit workspace modal
    public bool $showWorkspaceModal = false;
    public ?int $editWorkspaceId = null;
    public string $editWorkspaceName = '';
    public string $editPlanType = 'starter';
    public int $editMaxProfiles = 10;

    // Flash feedback
    public ?string $feedbackMessage = null;
    public string $feedbackType = 'success';

    protected $queryString = [
        'activeTab' => ['except' => 'overview'],
    ];

    #[On('set-admin-tab')]
    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    #[On('trigger-cron')]
    public function triggerCron(): void
    {
        $this->runCron();
    }

    #[On('clear-cache')]
    public function triggerClearCache(): void
    {
        $this->flushCache();
    }

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

    public function updatedPostSearch(): void
    {
        $this->resetPage('postsPage');
    }

    public function updatedPostStatusFilter(): void
    {
        $this->resetPage('postsPage');
    }

    // ==========================================
    // USER MANAGEMENT
    // ==========================================

    public function openCreateUserModal(): void
    {
        $this->newUserName = '';
        $this->newUserEmail = '';
        $this->newUserIsAdmin = false;
        $this->showCreateUserModal = true;
    }

    public function createUser(WorkspaceManagerService $workspaceManager): void
    {
        $this->validate([
            'newUserName' => 'required|min:2|max:100',
            'newUserEmail' => 'required|email|unique:users,email',
        ]);

        $user = User::create([
            'name' => $this->newUserName,
            'email' => strtolower(trim($this->newUserEmail)),
            'password' => Hash::make(\Illuminate\Support\Str::random(32)),
            'is_super_admin' => $this->newUserIsAdmin,
            'timezone' => 'Asia/Kolkata',
        ]);

        // Provision workspace for new user
        $workspaceManager->createWorkspace(
            $user,
            $user->name . "'s Agency",
            $user->timezone
        );

        $this->feedbackMessage = "User '{$user->name}' ({$user->email}) created successfully!";
        $this->feedbackType = 'success';
        $this->showCreateUserModal = false;
    }

    public function toggleSuperAdmin(int $userId): void
    {
        if ($userId === auth()->id()) {
            $this->feedbackMessage = "You cannot remove your own super administrator privileges.";
            $this->feedbackType = 'danger';
            return;
        }

        $user = User::findOrFail($userId);
        $user->is_super_admin = !$user->is_super_admin;
        $user->save();

        $status = $user->is_super_admin ? 'promoted to Super Admin' : 'demoted to Regular User';
        $this->feedbackMessage = "User '{$user->name}' ({$user->email}) has been {$status}.";
        $this->feedbackType = 'success';
    }

    public function openPasswordModal(int $userId): void
    {
        $user = User::findOrFail($userId);
        $this->passwordUserId = $user->id;
        $this->passwordUserName = $user->name;
        $this->newPassword = 'PostSync@' . rand(1000, 9999);
        $this->showPasswordModal = true;
    }

    public function resetPassword(): void
    {
        $this->validate([
            'newPassword' => 'required|min:6',
        ]);

        $user = User::findOrFail($this->passwordUserId);
        $user->password = Hash::make($this->newPassword);
        $user->save();

        $this->feedbackMessage = "Password for '{$user->name}' has been updated to '{$this->newPassword}'.";
        $this->feedbackType = 'success';
        $this->showPasswordModal = false;
    }

    public function impersonateUser(int $userId): mixed
    {
        if ($userId === auth()->id()) {
            $this->feedbackMessage = "You are already logged in as this user.";
            $this->feedbackType = 'info';
            return null;
        }

        $user = User::findOrFail($userId);
        Auth::login($user);

        session()->flash('success', "Impersonating {$user->name}. Log out and back in as admin to return.");
        return $this->redirect(route('dashboard'), navigate: true);
    }

    public function deleteUser(int $userId): void
    {
        if ($userId === auth()->id()) {
            $this->feedbackMessage = "You cannot delete your own account.";
            $this->feedbackType = 'danger';
            return;
        }

        $user = User::findOrFail($userId);
        $userName = $user->name;
        $user->delete();

        $this->feedbackMessage = "User '{$userName}' has been deleted.";
        $this->feedbackType = 'warning';
    }

    // ==========================================
    // WORKSPACE MANAGEMENT
    // ==========================================

    public function switchIntoWorkspace(int $workspaceId, WorkspaceManagerService $workspaceManager)
    {
        $workspace = $workspaceManager->switchWorkspace(auth()->user(), $workspaceId);
        session()->flash('success', "Switched into Workspace: {$workspace->name} ({$workspace->slug})");
        return $this->redirect(route('dashboard'), navigate: true);
    }

    public function toggleWorkspaceStatus(int $workspaceId): void
    {
        $ws = Workspace::findOrFail($workspaceId);
        $ws->is_active = !$ws->is_active;
        $ws->save();

        $status = $ws->is_active ? 'activated' : 'suspended';
        $this->feedbackMessage = "Workspace '{$ws->name}' has been {$status}.";
        $this->feedbackType = $ws->is_active ? 'success' : 'warning';
    }

    public function openEditWorkspaceModal(int $workspaceId): void
    {
        $ws = Workspace::findOrFail($workspaceId);
        $this->editWorkspaceId = $ws->id;
        $this->editWorkspaceName = $ws->name;
        $this->editPlanType = $ws->plan_type ?? 'starter';
        $this->editMaxProfiles = $ws->max_profiles ?? 10;
        $this->showWorkspaceModal = true;
    }

    public function saveWorkspacePlan(): void
    {
        $ws = Workspace::findOrFail($this->editWorkspaceId);
        $ws->update([
            'name' => $this->editWorkspaceName,
            'plan_type' => $this->editPlanType,
            'max_profiles' => $this->editMaxProfiles,
        ]);

        $this->feedbackMessage = "Workspace '{$ws->name}' updated: Plan [{$ws->plan_type}], Max Profiles [{$ws->max_profiles}].";
        $this->feedbackType = 'success';
        $this->showWorkspaceModal = false;
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
            $this->feedbackMessage = "Invalid role selected.";
            $this->feedbackType = 'danger';
            return;
        }

        $member = WorkspaceMember::findOrFail($memberId);
        $member->role = $roleEnum;
        $member->save();

        if ($this->selectedWorkspaceId) {
            $this->selectedWorkspace = Workspace::with(['owner', 'members.user'])->findOrFail($this->selectedWorkspaceId);
        }

        $this->feedbackMessage = "Member role updated to " . ucfirst($newRole) . ".";
        $this->feedbackType = 'success';
    }

    // ==========================================
    // SOCIAL CONNECTIONS & PROFILES
    // ==========================================

    public function forceRefreshToken(int $connectionId): void
    {
        $conn = SocialConnection::findOrFail($connectionId);
        $freshToken = $conn->getValidAccessToken();

        if ($freshToken) {
            $this->feedbackMessage = "Token refreshed successfully for {$conn->account_name} ({$conn->platform->label()})!";
            $this->feedbackType = 'success';
        } else {
            $this->feedbackMessage = "Could not refresh token for {$conn->account_name}. Re-authorization may be required.";
            $this->feedbackType = 'warning';
        }
    }

    public function syncProfileAnalytics(int $profileId): void
    {
        $profile = SocialProfile::findOrFail($profileId);
        SyncProfileAnalyticsJob::dispatchSync($profile->id);

        $this->feedbackMessage = "Live metrics synchronized for profile '{$profile->name}'!";
        $this->feedbackType = 'success';
    }

    public function toggleProfilePublishing(int $profileId): void
    {
        $p = SocialProfile::findOrFail($profileId);
        $p->publishing_enabled = !$p->publishing_enabled;
        $p->save();

        $status = $p->publishing_enabled ? 'enabled' : 'paused';
        $this->feedbackMessage = "Publishing {$status} for {$p->name}.";
        $this->feedbackType = 'info';
    }

    // ==========================================
    // PUBLISHING MODERATION
    // ==========================================

    public function retryPublishVariant(int $variantId): void
    {
        $variant = PostVariant::findOrFail($variantId);
        $variant->update(['status' => PostStatus::QUEUED]);

        PublishPostVariantJob::dispatchSync($variant->id, (string) Str::uuid());

        $variant->refresh();
        if ($variant->status === PostStatus::PUBLISHED) {
            $this->feedbackMessage = "Variant #{$variant->id} published successfully! External ID: {$variant->external_post_id}";
            $this->feedbackType = 'success';
        } else {
            $this->feedbackMessage = "Variant #{$variant->id} failed: " . ($variant->last_error_message ?? 'Unknown provider error');
            $this->feedbackType = 'danger';
        }
    }

    // ==========================================
    // SYSTEM DIAGNOSTICS & TOOLS
    // ==========================================

    public function runCron(): void
    {
        try {
            (new DispatchDuePostsJob())->handle();
            Artisan::call('schedule:run');
            $output = trim(Artisan::output());
            $this->feedbackMessage = "Scheduler & Due Posts dispatched successfully! Output: " . ($output ?: 'OK');
            $this->feedbackType = 'success';
        } catch (Exception $e) {
            $this->feedbackMessage = "Scheduler error: " . $e->getMessage();
            $this->feedbackType = 'danger';
        }
    }

    public function flushCache(): void
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('config:clear');
            $this->feedbackMessage = "System caches (App Cache, Blade Views, Config) flushed cleanly!";
            $this->feedbackType = 'success';
        } catch (Exception $e) {
            $this->feedbackMessage = "Cache flush error: " . $e->getMessage();
            $this->feedbackType = 'danger';
        }
    }

    public function syncAllAnalyticsGlobal(): void
    {
        $profiles = SocialProfile::where('is_active', true)->get();
        $count = 0;
        foreach ($profiles as $p) {
            SyncProfileAnalyticsJob::dispatchSync($p->id);
            $count++;
        }

        $this->feedbackMessage = "Synchronized live analytics metrics across {$count} active social profiles!";
        $this->feedbackType = 'success';
    }

    // ==========================================
    // RENDER PIPELINE
    // ==========================================

    public function render()
    {
        // 1. High-Level Metrics
        $totalUsers = User::count();
        $totalSuperAdmins = User::where('is_super_admin', true)->count();
        $totalWorkspaces = Workspace::count();
        $totalActiveWorkspaces = Workspace::where('is_active', true)->count();
        $totalSocialProfiles = SocialProfile::where('is_active', true)->count();
        $totalSocialConnections = SocialConnection::count();
        $totalPosts = Post::count();
        $publishedPosts = PostVariant::where('status', PostStatus::PUBLISHED)->count();
        $scheduledPosts = PostVariant::where('status', PostStatus::SCHEDULED)->count();
        $failedPosts = PostVariant::where('status', PostStatus::FAILED)->count();
        $totalMedia = MediaAsset::count();
        $totalSnapshots = AnalyticsSnapshot::count();

        // 2. Users query
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

        // 3. Workspaces query
        $workspacesQuery = Workspace::with(['owner', 'members.user', 'socialProfiles']);
        if (!empty(trim($this->workspaceSearch))) {
            $term = '%' . trim($this->workspaceSearch) . '%';
            $workspacesQuery->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('slug', 'like', $term);
            });
        }
        $workspaces = $workspacesQuery->latest()->paginate(10, ['*'], 'workspacesPage');

        // 4. Social Profiles query
        $profilesQuery = SocialProfile::with(['connection', 'workspace']);
        if (!empty(trim($this->profileSearch))) {
            $term = '%' . trim($this->profileSearch) . '%';
            $profilesQuery->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('platform', 'like', $term);
            });
        }
        if (!empty($this->platformFilter)) {
            $profilesQuery->where('platform', $this->platformFilter);
        }
        $profiles = $profilesQuery->latest()->paginate(12, ['*'], 'profilesPage');

        // 5. Posts moderation query
        $postsQuery = PostVariant::with(['post.workspace', 'socialProfile', 'mediaAssets']);
        if (!empty(trim($this->postSearch))) {
            $term = '%' . trim($this->postSearch) . '%';
            $postsQuery->where(function ($q) use ($term) {
                $q->where('caption', 'like', $term)
                  ->orWhere('external_post_id', 'like', $term)
                  ->orWhereHas('post', fn($pq) => $pq->where('base_caption', 'like', $term));
            });
        }
        if (!empty($this->postStatusFilter)) {
            $postsQuery->where('status', $this->postStatusFilter);
        }
        $allPosts = $postsQuery->latest('updated_at')->paginate(10, ['*'], 'postsPage');

        // 6. Diagnostics: Recent Sync Logs
        $recentSyncLogs = AnalyticsSyncLog::with('socialProfile')->latest('synced_at')->take(10)->get();

        return view('livewire.admin.admin-dashboard', [
            'metrics' => [
                'totalUsers' => $totalUsers,
                'totalSuperAdmins' => $totalSuperAdmins,
                'totalWorkspaces' => $totalWorkspaces,
                'totalActiveWorkspaces' => $totalActiveWorkspaces,
                'totalSocialProfiles' => $totalSocialProfiles,
                'totalSocialConnections' => $totalSocialConnections,
                'totalPosts' => $totalPosts,
                'publishedPosts' => $publishedPosts,
                'scheduledPosts' => $scheduledPosts,
                'failedPosts' => $failedPosts,
                'totalMedia' => $totalMedia,
                'totalSnapshots' => $totalSnapshots,
            ],
            'users' => $users,
            'workspaces' => $workspaces,
            'profiles' => $profiles,
            'allPosts' => $allPosts,
            'recentSyncLogs' => $recentSyncLogs,
        ])->title('Master Control Panel — Super Admin');
    }
}
