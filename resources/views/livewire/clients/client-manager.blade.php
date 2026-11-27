<div>
    <!-- Page Header & Action Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="bi bi-building text-primary me-2"></i>Clients Directory
            </h4>
            <p class="text-muted small mb-0">
                Manage your agency clients (e.g. restaurants, brands, retail stores) and assign dedicated team members.
            </p>
        </div>
        <div>
            <button wire:click="openCreateModal" class="btn btn-primary d-inline-flex align-items-center gap-2">
                <i class="bi bi-plus-lg"></i>
                <span>Add New Client</span>
            </button>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-6 col-lg-8">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control bg-light border-start-0" wire:model.live.debounce.300ms="search" placeholder="Search by client name, client code, or contact...">
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 d-flex gap-2">
                    <select class="form-select bg-light" wire:model.live="statusFilter">
                        <option value="all">All Statuses</option>
                        <option value="active">Active Clients</option>
                        <option value="paused">Paused</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Clients Table / List -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if ($clients->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th class="ps-3">Client</th>
                                <th>Code</th>
                                <th>Primary Contact</th>
                                <th>Timezone</th>
                                <th>Assigned Team</th>
                                <th>Status</th>
                                <th class="pe-3 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($clients as $client)
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px;">
                                                {{ strtoupper(substr($client->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="fw-semibold text-dark">{{ $client->name }}</div>
                                                @if ($client->notes)
                                                    <div class="text-muted small text-truncate" style="max-width: 220px;">{{ $client->notes }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border font-monospace">{{ $client->client_code }}</span>
                                    </td>
                                    <td>
                                        @if ($client->contact_person || $client->contact_email)
                                            <div class="small fw-medium">{{ $client->contact_person ?? 'Not specified' }}</div>
                                            <div class="text-muted small">{{ $client->contact_email }}</div>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="small text-secondary">{{ $client->timezone }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1 flex-wrap">
                                            @forelse ($client->users as $member)
                                                <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1" title="{{ $member->email }}">
                                                    <span>{{ $member->name }}</span>
                                                    @if ($member->pivot->can_approve)
                                                        <i class="bi bi-shield-check text-success" title="Can Approve Posts"></i>
                                                    @endif
                                                    <button type="button" wire:click="removeMember({{ $client->id }}, {{ $member->id }})" class="btn-close" style="font-size: 0.55rem;"></button>
                                                </span>
                                            @empty
                                                <span class="text-muted small fst-italic">No members assigned</span>
                                            @endforelse
                                            <button type="button" wire:click="openAssignModal({{ $client->id }})" class="btn btn-sm btn-outline-secondary py-0 px-1 border-dashed" style="font-size: 0.75rem;">
                                                <i class="bi bi-person-plus"></i> Assign
                                            </button>
                                        </div>
                                    </td>
                                    <td>
                                        <x-badge :status="$client->status" />
                                    </td>
                                    <td class="pe-3 text-end">
                                        <div class="btn-group btn-group-sm">
                                            @if ($client->contact_email)
                                                <button wire:click="sendClientInvite({{ $client->id }})" wire:loading.attr="disabled" class="btn btn-light border text-primary" title="Send Portal Invitation Email">
                                                    <i class="bi bi-envelope-paper"></i>
                                                </button>
                                            @endif
                                            <button wire:click="editClient({{ $client->id }})" class="btn btn-light border" title="Edit Client">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button wire:click="deleteClient({{ $client->id }})" wire:confirm="Are you sure you want to remove {{ $client->name }}?" class="btn btn-light border text-danger" title="Delete Client">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3 border-top">
                    {{ $clients->links() }}
                </div>
            @else
                <x-empty-state 
                    icon="bi-building-slash"
                    title="No clients found"
                    description="You haven't added any clients yet or no clients match your filter criteria."
                >
                    <x-slot:action>
                        <button wire:click="openCreateModal" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-lg me-1"></i> Add First Client
                        </button>
                    </x-slot:action>
                </x-empty-state>
            @endif
        </div>
    </div>

    <!-- Create / Edit Client Modal -->
    @if ($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow border-0">
                    <div class="modal-header">
                        <h5 class="modal-title fs-6 fw-bold">
                            {{ $isEditing ? 'Edit Client' : 'Add New Client' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <form wire:submit="saveClient">
                        <div class="modal-body">
                            <!-- Name -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Client Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name" placeholder="e.g. Sharma Restaurant, Fashion Store" required>
                                @error('name') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                            </div>

                            <!-- Code -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Client Code</label>
                                <input type="text" class="form-control @error('client_code') is-invalid @enderror" wire:model="client_code" placeholder="e.g. SHAR-01 (Auto-generated if blank)">
                                @error('client_code') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                                <div class="form-text small">Unique code used for internal scoping.</div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Contact Person</label>
                                    <input type="text" class="form-control" wire:model="contact_person" placeholder="Manager Name">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Client Login Gmail / Email</label>
                                    <input type="email" class="form-control" wire:model="contact_email" placeholder="client@gmail.com">
                                    <div class="form-text" style="font-size: 0.72rem;">Client can sign in with this Gmail via OTP to view their own Analytics, Posts &amp; Reports.</div>
                                </div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Timezone</label>
                                    <select class="form-select" wire:model="timezone">
                                        <option value="UTC">UTC</option>
                                        <option value="Asia/Kolkata">Asia/Kolkata (IST)</option>
                                        <option value="America/New_York">America/New_York (EST)</option>
                                        <option value="America/Los_Angeles">America/Los_Angeles (PST)</option>
                                        <option value="Europe/London">Europe/London (GMT)</option>
                                        <option value="Asia/Dubai">Asia/Dubai (GST)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Status</label>
                                    <select class="form-select" wire:model="status">
                                        <option value="active">Active</option>
                                        <option value="paused">Paused</option>
                                        <option value="archived">Archived</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Internal Notes</label>
                                <textarea class="form-control" wire:model="notes" rows="2" placeholder="Brand guidelines, primary social targets, or instructions..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer bg-light py-2">
                            <button type="button" class="btn btn-sm btn-secondary" wire:click="$set('showModal', false)">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-primary">
                                {{ $isEditing ? 'Save Changes' : 'Create Client' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Assign Member Modal -->
    @if ($showAssignModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content shadow border-0">
                    <div class="modal-header">
                        <h5 class="modal-title fs-6 fw-bold">Assign Team Member</h5>
                        <button type="button" class="btn-close" wire:click="$set('showAssignModal', false)"></button>
                    </div>
                    <form wire:submit="assignMember">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Select Team Member</label>
                                <select class="form-select @error('assignTargetUserId') is-invalid @enderror" wire:model="assignTargetUserId" required>
                                    <option value="">Choose member...</option>
                                    @foreach ($workspaceUsers as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                                    @endforeach
                                </select>
                                @error('assignTargetUserId') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="canApproveCheck" wire:model="assignCanApprove">
                                <label class="form-check-label small" for="canApproveCheck">
                                    Can approve client posts
                                </label>
                            </div>
                        </div>
                        <div class="modal-footer bg-light py-2">
                            <button type="button" class="btn btn-sm btn-secondary" wire:click="$set('showAssignModal', false)">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-primary">Assign Member</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

