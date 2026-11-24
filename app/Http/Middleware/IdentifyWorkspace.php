<?php

namespace App\Http\Middleware;

use App\Models\Workspace;
use App\Services\Tenancy\WorkspaceManagerService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyWorkspace
{
    public function __construct(
        protected WorkspaceManagerService $workspaceManager
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $workspace = null;
            $activeWorkspaceId = session('active_workspace_id');

            if ($activeWorkspaceId) {
                // Verify user is still an active member of this workspace
                $workspace = $user->workspaces()
                    ->where('workspaces.id', $activeWorkspaceId)
                    ->where('workspaces.is_active', true)
                    ->where('workspace_members.is_active', true)
                    ->first();
            }

            // Fallback to first available workspace
            if (!$workspace) {
                $workspace = $user->workspaces()
                    ->where('workspaces.is_active', true)
                    ->where('workspace_members.is_active', true)
                    ->first();
            }

            // If user has zero workspaces, auto-create their personal default workspace
            if (!$workspace) {
                $workspace = $this->workspaceManager->createWorkspace(
                    $user,
                    $user->name . "'s Agency",
                    $user->timezone
                );
            }

            // Bind workspace to container, session, and views
            app()->instance('current_workspace', $workspace);
            session([
                'active_workspace_id' => $workspace->id,
                'active_workspace_name' => $workspace->name,
                'current_user_role' => $workspace->pivot->role ?? 'owner',
            ]);

            view()->share('currentWorkspace', $workspace);
        }

        return $next($request);
    }
}

