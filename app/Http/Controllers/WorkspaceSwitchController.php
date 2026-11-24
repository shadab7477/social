<?php

namespace App\Http\Controllers;

use App\Services\Tenancy\WorkspaceManagerService;
use Illuminate\Http\Request;

class WorkspaceSwitchController extends Controller
{
    public function __invoke(Request $request, int $workspaceId, WorkspaceManagerService $workspaceManager)
    {
        $workspace = $workspaceManager->switchWorkspace($request->user(), $workspaceId);

        return redirect()->back()->with('success', "Switched to workspace: {$workspace->name}");
    }
}

