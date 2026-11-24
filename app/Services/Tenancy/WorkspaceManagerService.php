<?php

namespace App\Services\Tenancy;

use App\Enums\WorkspaceRole;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceMember;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkspaceManagerService
{
    /**
     * Create a new workspace and assign the creator as the Owner.
     */
    public function createWorkspace(User $owner, string $name, ?string $timezone = 'UTC', ?string $currency = 'INR'): Workspace
    {
        return DB::transaction(function () use ($owner, $name, $timezone, $currency) {
            $workspace = Workspace::create([
                'name' => trim($name),
                'owner_id' => $owner->id,
                'timezone' => $timezone ?? $owner->timezone ?? 'UTC',
                'currency' => $currency ?? 'INR',
                'is_active' => true,
                'settings' => [
                    'default_publish_delay_minutes' => 0,
                    'enable_approval_workflows' => true,
                    'client_notifications' => true,
                ],
            ]);

            WorkspaceMember::create([
                'workspace_id' => $workspace->id,
                'user_id' => $owner->id,
                'role' => WorkspaceRole::OWNER,
                'is_active' => true,
                'joined_at' => now(),
            ]);

            // Set as active workspace in session
            session([
                'active_workspace_id' => $workspace->id,
                'active_workspace_name' => $workspace->name,
            ]);

            return $workspace;
        });
    }

    /**
     * Switch active workspace for an authenticated user.
     */
    public function switchWorkspace(User $user, int $workspaceId): Workspace
    {
        $member = WorkspaceMember::where('workspace_id', $workspaceId)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->with('workspace')
            ->first();

        if (!$member || !$member->workspace || !$member->workspace->is_active) {
            throw ValidationException::withMessages([
                'workspace' => 'You do not have active access to the requested workspace.',
            ]);
        }

        session([
            'active_workspace_id' => $member->workspace->id,
            'active_workspace_name' => $member->workspace->name,
        ]);

        return $member->workspace;
    }

    /**
     * Invite a new team member to a workspace.
     */
    public function inviteMember(Workspace $workspace, User $inviter, string $email, WorkspaceRole $role): WorkspaceInvitation
    {
        $existingMember = $workspace->users()->where('email', strtolower(trim($email)))->exists();
        if ($existingMember) {
            throw ValidationException::withMessages([
                'email' => 'This user is already a member of this workspace.',
            ]);
        }

        return WorkspaceInvitation::updateOrCreate(
            [
                'workspace_id' => $workspace->id,
                'email' => strtolower(trim($email)),
            ],
            [
                'inviter_id' => $inviter->id,
                'role' => $role,
                'expires_at' => now()->addDays(7),
                'accepted_at' => null,
            ]
        );
    }
}

