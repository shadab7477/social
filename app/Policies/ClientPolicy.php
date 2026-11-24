<?php

namespace App\Policies;

use App\Enums\WorkspaceRole;
use App\Models\Client;
use App\Models\User;
use App\Models\Workspace;

class ClientPolicy
{
    /**
     * Determine whether the user can view client listings.
     */
    public function viewAny(User $user, ?Workspace $workspace = null): bool
    {
        $currentWorkspace = $workspace ?? app('current_workspace');
        if (!$currentWorkspace) {
            return false;
        }

        return $currentWorkspace->users()
            ->where('users.id', $user->id)
            ->where('workspace_members.is_active', true)
            ->exists();
    }

    /**
     * Determine whether the user can view a specific client.
     */
    public function view(User $user, Client $client): bool
    {
        $workspace = $client->workspace;
        if (!$workspace) {
            return false;
        }

        $role = $workspace->getMemberRole($user);
        if (!$role) {
            return false;
        }

        // Client viewers can ONLY view clients they are explicitly assigned to
        if ($role === WorkspaceRole::CLIENT_VIEWER) {
            return $client->isAssigned($user);
        }

        // Owners, Admins, Managers, and Editors can view all workspace clients
        return true;
    }

    /**
     * Determine whether the user can create clients.
     */
    public function create(User $user): bool
    {
        $workspace = app('current_workspace');
        if (!$workspace) {
            return false;
        }

        $role = $workspace->getMemberRole($user);
        return $role && in_array($role, [WorkspaceRole::OWNER, WorkspaceRole::ADMIN, WorkspaceRole::MANAGER], true);
    }

    /**
     * Determine whether the user can update the client.
     */
    public function update(User $user, Client $client): bool
    {
        $role = $client->workspace->getMemberRole($user);
        return $role && in_array($role, [WorkspaceRole::OWNER, WorkspaceRole::ADMIN, WorkspaceRole::MANAGER], true);
    }

    /**
     * Determine whether the user can delete the client.
     */
    public function delete(User $user, Client $client): bool
    {
        $role = $client->workspace->getMemberRole($user);
        return $role && in_array($role, [WorkspaceRole::OWNER, WorkspaceRole::ADMIN], true);
    }
}

