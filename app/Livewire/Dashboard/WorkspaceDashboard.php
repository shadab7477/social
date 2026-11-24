<?php

namespace App\Livewire\Dashboard;

use App\Enums\ConnectionStatus;
use App\Enums\PostStatus;
use App\Models\Client;
use App\Models\Post;
use App\Models\PostVariant;
use App\Models\SocialProfile;
use App\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class WorkspaceDashboard extends Component
{
    public function render()
    {
        $workspace = app('current_workspace');

        // 1. Dynamic Key Metrics
        $totalConnectedProfiles = SocialProfile::where('workspace_id', $workspace->id)
            ->where('is_active', true)
            ->count();

        $scheduledPostsCount = PostVariant::whereHas('post', fn($q) => $q->where('workspace_id', $workspace->id))
            ->where('status', PostStatus::SCHEDULED)
            ->count();

        $pendingApprovalsCount = Post::where('workspace_id', $workspace->id)
            ->where('status', PostStatus::PENDING_APPROVAL)
            ->count();

        $publishedPostsCount = PostVariant::whereHas('post', fn($q) => $q->where('workspace_id', $workspace->id))
            ->where('status', PostStatus::PUBLISHED)
            ->count();

        // 2. Next Upcoming Post
        $nextScheduledVariant = PostVariant::whereHas('post', fn($q) => $q->where('workspace_id', $workspace->id))
            ->where('status', PostStatus::SCHEDULED)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at', 'asc')
            ->first();

        // 3. Upcoming Queue (Latest 5 items scheduled or queued)
        $upcomingVariants = PostVariant::whereHas('post', fn($q) => $q->where('workspace_id', $workspace->id))
            ->whereIn('status', [PostStatus::SCHEDULED, PostStatus::QUEUED, PostStatus::PUBLISHING])
            ->with(['post.client', 'socialProfile', 'mediaAssets'])
            ->orderBy('scheduled_at', 'asc')
            ->take(6)
            ->get();

        // 4. Recently Published Posts (Latest 5 items)
        $recentPublishedVariants = PostVariant::whereHas('post', fn($q) => $q->where('workspace_id', $workspace->id))
            ->where('status', PostStatus::PUBLISHED)
            ->with(['post.client', 'socialProfile', 'mediaAssets'])
            ->latest('published_at')
            ->take(5)
            ->get();

        // 5. Connected Profiles List & Health
        $connectedProfiles = SocialProfile::where('workspace_id', $workspace->id)
            ->where('is_active', true)
            ->with('connection')
            ->latest()
            ->take(8)
            ->get();

        // 6. Clients Count & Team Members Count
        $clientsCount = Client::where('workspace_id', $workspace->id)->count();
        $teamMembersCount = $workspace->members()->count();

        return view('livewire.dashboard.workspace-dashboard', [
            'workspace' => $workspace,
            'totalConnectedProfiles' => $totalConnectedProfiles,
            'scheduledPostsCount' => $scheduledPostsCount,
            'pendingApprovalsCount' => $pendingApprovalsCount,
            'publishedPostsCount' => $publishedPostsCount,
            'nextScheduledVariant' => $nextScheduledVariant,
            'upcomingVariants' => $upcomingVariants,
            'recentPublishedVariants' => $recentPublishedVariants,
            'connectedProfiles' => $connectedProfiles,
            'clientsCount' => $clientsCount,
            'teamMembersCount' => $teamMembersCount,
        ]);
    }
}

