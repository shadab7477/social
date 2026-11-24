<?php

namespace App\Policies;

use App\Enums\WorkspaceRole;
use App\Models\User;
use App\Models\Workspace;

class WorkspacePolicy
{
    /**
     * Determine whether the user can view the workspace.
     */
    public function view(User $user, Workspace $workspace): bool
    {
        return $workspace->users()
            ->where('users.id', $user->id)
            ->where('workspace_members.is_active', true)
            ->exists();
    }

    /**
     * Determine whether the user can update the workspace.
     */
    public function update(User $user, Workspace $workspace): bool
    {
        $role = $workspace->getMemberRole($user);
        return $role && in_array($role, [WorkspaceRole::OWNER, WorkspaceRole::ADMIN], true);
    }

    /**
     * Determine whether the user can manage team members.
     */
    public function manageMembers(User $user, Workspace $workspace): bool
    {
        $role = $workspace->getMemberRole($user);
        return $role && in_array($role, [WorkspaceRole::OWNER, WorkspaceRole::ADMIN], true);
    }

    /**
     * Determine whether the user can manage billing and plans.
     */
    public function manageBilling(User $user, Workspace $workspace): bool
    {
        $role = $workspace->getMemberRole($user);
        return $role === WorkspaceRole::OWNER;
    }

    /**
     * Determine whether the user can delete the workspace.
     */
    public function delete(User $user, Workspace $workspace): bool
    {
        return $workspace->isOwner($user);
    }
}

