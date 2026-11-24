<?php

namespace App\Http\Controllers;

use App\Services\Tenancy\WorkspaceManagerService;
use Illuminate\Http\Request;

class WorkspaceCreateController extends Controller
{
    public function __invoke(Request $request, WorkspaceManagerService $workspaceManager)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:60', 'min:2'],
            'timezone' => ['nullable', 'string', 'max:50'],
        ]);

        $user = $request->user();
        $timezone = $validated['timezone'] ?? 'Asia/Kolkata';

        $workspace = $workspaceManager->createWorkspace($user, $validated['name'], $timezone);

        session([
            'active_workspace_id' => $workspace->id,
            'active_workspace_name' => $workspace->name,
        ]);

        return redirect()->route('dashboard')->with('success', "Workspace '{$workspace->name}' created successfully! You are now managing this workspace.");
    }
}

