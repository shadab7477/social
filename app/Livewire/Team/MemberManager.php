<?php

namespace App\Livewire\Team;

use App\Enums\WorkspaceRole;
use App\Models\User;
use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceMember;
use App\Services\Tenancy\WorkspaceManagerService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class MemberManager extends Component
{
    public bool $showInviteModal = false;
    public bool $showRoleModal = false;

    // Invite fields
    public string $inviteEmail = '';
    public string $inviteRole = 'editor';

    // Role Edit fields
    public ?int $selectedMemberId = null;
    public string $selectedMemberRole = 'editor';

    protected function rules(): array
    {
        return [
            'inviteEmail' => ['required', 'email'],
            'inviteRole' => ['required', 'in:admin,manager,editor,client_viewer'],
        ];
    }

    public function openInviteModal(): void
    {
        $this->reset(['inviteEmail', 'inviteRole']);
        $this->inviteRole = 'editor';
        $this->showInviteModal = true;
    }

    public function sendInvitation(WorkspaceManagerService $workspaceManager): void
    {
        $this->validate();

        $workspace = app('current_workspace');
        $role = WorkspaceRole::from($this->inviteRole);

        $workspaceManager->inviteMember($workspace, auth()->user(), $this->inviteEmail, $role);

        session()->flash('success', "Invitation sent to {$this->inviteEmail}.");
        $this->showInviteModal = false;
        $this->reset(['inviteEmail', 'inviteRole']);
    }

    public function openRoleModal(int $memberId): void
    {
        $member = WorkspaceMember::findOrFail($memberId);
        $this->selectedMemberId = $member->id;
        $this->selectedMemberRole = $member->role->value;
        $this->showRoleModal = true;
    }

    public function updateRole(): void
    {
        $member = WorkspaceMember::findOrFail($this->selectedMemberId);
        $workspace = app('current_workspace');

        // Prevent demoting the owner
        if ($member->isOwner() && $this->selectedMemberRole !== WorkspaceRole::OWNER->value) {
            session()->flash('error', "The primary owner's role cannot be demoted.");
            $this->showRoleModal = false;
            return;
        }

        $member->update([
            'role' => WorkspaceRole::from($this->selectedMemberRole),
        ]);

        session()->flash('success', "Updated role for {$member->user->name}.");
        $this->showRoleModal = false;
    }

    public function removeMember(int $memberId): void
    {
        $member = WorkspaceMember::findOrFail($memberId);

        if ($member->isOwner()) {
            session()->flash('error', "The primary workspace owner cannot be removed.");
            return;
        }

        $name = $member->user->name;
        $member->delete();

        session()->flash('success', "Removed {$name} from the workspace.");
    }

    public function cancelInvitation(int $invitationId): void
    {
        $invitation = WorkspaceInvitation::findOrFail($invitationId);
        $email = $invitation->email;
        $invitation->delete();

        session()->flash('success', "Cancelled invitation for {$email}.");
    }

    public function render()
    {
        $workspace = app('current_workspace');
        $members = $workspace->members()->with('user')->get();
        $invitations = $workspace->invitations()->whereNull('accepted_at')->get();

        return view('livewire.team.member-manager', [
            'members' => $members,
            'invitations' => $invitations,
            'roles' => WorkspaceRole::cases(),
        ]);
    }
}

