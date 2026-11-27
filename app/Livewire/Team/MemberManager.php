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
    public ?string $lastInviteLink = null;
    public ?string $lastInviteEmail = null;

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

        $invitation = $workspaceManager->inviteMember($workspace, auth()->user(), $this->inviteEmail, $role);

        $this->lastInviteLink = route('invitations.accept', ['token' => $invitation->token]);
        $this->lastInviteEmail = $this->inviteEmail;

        if (config('mail.default') !== 'log') {
            try {
                \Illuminate\Support\Facades\Mail::html(
                    "<div style='font-family:sans-serif;max-width:520px;margin:auto;padding:28px;border:1px solid #e2e8f0;border-radius:14px;'>
                        <h2 style='margin:0 0 12px;color:#0f172a;'>You're invited to join {$workspace->name}</h2>
                        <p style='color:#475569;'>You have been invited as <strong>{$role->label()}</strong> on PostSync.</p>
                        <div style='margin:24px 0;text-align:center;'>
                            <a href='{$this->lastInviteLink}' style='display:inline-block;background:#0f172a;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:999px;font-weight:600;'>Accept Invitation</a>
                        </div>
                    </div>",
                    fn ($m) => $m->to($this->inviteEmail)->subject("Invitation to join {$workspace->name} on PostSync")
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Team invite mail failed: " . $e->getMessage());
            }
        }

        session()->flash('success', "Invitation sent for {$this->inviteEmail}. You can also copy and share the direct invite link below!");
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

