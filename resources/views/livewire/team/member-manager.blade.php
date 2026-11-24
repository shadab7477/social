<div>
    <!-- Page Header & Action Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="bi bi-people text-primary me-2"></i>Team & Role Management
            </h4>
            <p class="text-muted small mb-0">
                Manage agency team members, permissions, and client reviewers for this workspace.
            </p>
        </div>
        <div>
            <button wire:click="openInviteModal" class="btn btn-primary d-inline-flex align-items-center gap-2">
                <i class="bi bi-envelope-plus"></i>
                <span>Invite Member</span>
            </button>
        </div>
    </div>

    @if ($lastInviteLink)
        <div class="alert alert-info border-0 shadow-sm p-3 mb-4 rounded-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <div class="fw-bold text-dark"><i class="bi bi-link-45deg fs-5 text-primary me-1"></i>Shareable Invite Link for {{ $lastInviteEmail }}:</div>
                <code class="small user-select-all text-break">{{ $lastInviteLink }}</code>
                <div class="text-muted small mt-1">You can send this URL directly on WhatsApp, Slack, or Email.</div>
            </div>
            <button class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1" onclick="navigator.clipboard.writeText('{{ $lastInviteLink }}'); this.innerHTML='<i class=\'bi bi-check2\'></i> Copied!'; setTimeout(() => this.innerHTML='<i class=\'bi bi-clipboard\'></i> Copy Link', 2000);">
                <i class="bi bi-clipboard"></i> Copy Link
            </button>
        </div>
    @endif

    <!-- Active Team Members Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold mb-0 text-dark">Active Members ({{ $members->count() }})</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th class="ps-3">Team Member</th>
                            <th>Role & Permissions</th>
                            <th>Status</th>
                            <th>Joined Date</th>
                            <th class="pe-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($members as $member)
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;">
                                            {{ strtoupper(substr($member->user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $member->user->name }}</div>
                                            <div class="text-muted small">{{ $member->user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $roleBadgeClass = match($member->role->value) {
                                            'owner' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                            'admin' => 'bg-indigo-subtle text-indigo border border-indigo-subtle',
                                            'manager' => 'bg-info-subtle text-info border border-info-subtle',
                                            'editor' => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                            'client_viewer' => 'bg-warning-subtle text-warning border border-warning-subtle',
                                            default => 'bg-light text-dark',
                                        };
                                    @endphp
                                    <span class="badge {{ $roleBadgeClass }} px-2 py-1">
                                        {{ $member->role->label() }}
                                    </span>
                                </td>
                                <td>
                                    @if ($member->is_active)
                                        <span class="badge bg-success-subtle text-success">Active</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">Suspended</span>
                                    @endif
                                </td>
                                <td class="small text-muted">
                                    {{ $member->joined_at ? $member->joined_at->format('M d, Y') : '—' }}
                                </td>
                                <td class="pe-3 text-end">
                                    @if (!$member->isOwner())
                                        <div class="btn-group btn-group-sm">
                                            <button wire:click="openRoleModal({{ $member->id }})" class="btn btn-light border" title="Edit Role">
                                                <i class="bi bi-shield-shaded"></i>
                                            </button>
                                            <button wire:click="removeMember({{ $member->id }})" wire:confirm="Are you sure you want to remove {{ $member->user->name }} from this workspace?" class="btn btn-light border text-danger" title="Remove Member">
                                                <i class="bi bi-person-x"></i>
                                            </button>
                                        </div>
                                    @else
                                        <span class="badge bg-light text-muted border">Primary Owner</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pending Invitations Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold mb-0 text-dark">Pending Invitations ({{ $invitations->count() }})</h6>
        </div>
        <div class="card-body p-0">
            @if ($invitations->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th class="ps-3">Invited Email</th>
                                <th>Designated Role</th>
                                <th>Sent Date</th>
                                <th>Expires In</th>
                                <th class="pe-3 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invitations as $inv)
                                <tr>
                                    <td class="ps-3 fw-medium">{{ $inv->email }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $inv->role->label() }}</span>
                                    </td>
                                    <td class="small text-muted">{{ $inv->created_at->format('M d, Y') }}</td>
                                    <td class="small text-muted">
                                        {{ $inv->expires_at->diffForHumans() }}
                                    </td>
                                    <td class="pe-3 text-end">
                                        <div class="d-flex align-items-center justify-content-end gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 py-1 px-2" style="font-size: 0.75rem;" onclick="navigator.clipboard.writeText('{{ route('invitations.accept', ['token' => $inv->token]) }}'); this.innerHTML='<i class=\'bi bi-check2\'></i> Copied!'; setTimeout(() => this.innerHTML='<i class=\'bi bi-link-45deg\'></i> Copy Link', 2000);" title="Copy Invite Link">
                                                <i class="bi bi-link-45deg"></i> Copy Link
                                            </button>
                                            <button wire:click="cancelInvitation({{ $inv->id }})" class="btn btn-sm btn-light border text-danger py-1 px-2" style="font-size: 0.75rem;" title="Cancel Invitation">
                                                Revoke
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-4 text-center text-muted small">
                    <i class="bi bi-envelope-check fs-4 d-block mb-1 text-secondary"></i>
                    No pending invitations. All invited members have joined!
                </div>
            @endif
        </div>
    </div>

    <!-- Invite Member Modal -->
    @if ($showInviteModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow border-0">
                    <div class="modal-header">
                        <h5 class="modal-title fs-6 fw-bold">Invite New Team Member</h5>
                        <button type="button" class="btn-close" wire:click="$set('showInviteModal', false)"></button>
                    </div>
                    <form wire:submit="sendInvitation">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('inviteEmail') is-invalid @enderror" wire:model="inviteEmail" placeholder="colleague@agency.com" required autofocus>
                                @error('inviteEmail') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Workspace Role <span class="text-danger">*</span></label>
                                <select class="form-select @error('inviteRole') is-invalid @enderror" wire:model="inviteRole">
                                    <option value="admin">Administrator (Manage members & connections)</option>
                                    <option value="manager">Content Manager (Approve & publish posts)</option>
                                    <option value="editor" selected>Content Editor (Draft posts & upload media)</option>
                                    <option value="client_viewer">Client Reviewer (Restricted to assigned clients)</option>
                                </select>
                                @error('inviteRole') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                            </div>

                            <div class="bg-light p-3 rounded-3 small text-secondary">
                                <i class="bi bi-info-circle me-1 text-primary"></i>
                                An email invitation with an onboarding link will be sent to this address.
                            </div>
                        </div>
                        <div class="modal-footer bg-light py-2">
                            <button type="button" class="btn btn-sm btn-secondary" wire:click="$set('showInviteModal', false)">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-primary">Send Invitation</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Change Role Modal -->
    @if ($showRoleModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content shadow border-0">
                    <div class="modal-header">
                        <h5 class="modal-title fs-6 fw-bold">Change Role</h5>
                        <button type="button" class="btn-close" wire:click="$set('showRoleModal', false)"></button>
                    </div>
                    <form wire:submit="updateRole">
                        <div class="modal-body">
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Select New Role</label>
                                <select class="form-select" wire:model="selectedMemberRole">
                                    <option value="admin">Administrator</option>
                                    <option value="manager">Content Manager</option>
                                    <option value="editor">Content Editor</option>
                                    <option value="client_viewer">Client Reviewer</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer bg-light py-2">
                            <button type="button" class="btn btn-sm btn-secondary" wire:click="$set('showRoleModal', false)">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-primary">Save Role</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

