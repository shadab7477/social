<?php

namespace App\Http\Controllers;

use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceMember;
use Illuminate\Http\Request;

class WorkspaceInviteController extends Controller
{
    public function __invoke(Request $request, string $token)
    {
        $invitation = WorkspaceInvitation::where('token', $token)
            ->with(['workspace', 'inviter'])
            ->first();

        if (!$invitation) {
            return redirect('/login')->with('error', 'Invalid invitation link.');
        }

        if ($invitation->isExpired()) {
            return redirect('/login')->with('error', 'This invitation link has expired. Please ask the workspace administrator to re-invite you.');
        }

        if ($invitation->isAccepted()) {
            return redirect('/login')->with('info', 'This invitation has already been accepted.');
        }

        $user = $request->user();

        // If guest, save intended URL and token in session and redirect to login / register
        if (!$user) {
            session([
                'pending_invite_token' => $token,
                'url.intended' => route('invitations.accept', ['token' => $token]),
            ]);

            return redirect()->route('login')->with(
                'info',
                "You have been invited to join workspace '{$invitation->workspace->name}' as {$invitation->role->label()}. Please log in or create an account to accept."
            );
        }

        // Add user as workspace member
        WorkspaceMember::firstOrCreate(
            [
                'workspace_id' => $invitation->workspace_id,
                'user_id' => $user->id,
            ],
            [
                'role' => $invitation->role,
                'is_active' => true,
                'joined_at' => now(),
            ]
        );

        $invitation->update(['accepted_at' => now()]);

        // Switch to the newly joined workspace immediately
        session([
            'active_workspace_id' => $invitation->workspace->id,
            'active_workspace_name' => $invitation->workspace->name,
        ]);
        session()->forget('pending_invite_token');

        return redirect()->route('dashboard')->with(
            'success',
            "You have joined workspace '{$invitation->workspace->name}' as {$invitation->role->label()}!"
        );
    }
}

