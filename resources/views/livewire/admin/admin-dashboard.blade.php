<div>
    <!-- Top Super Admin Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold text-dark mb-0">
                    <i class="bi bi-shield-lock-fill text-danger me-2"></i>Platform Master Control Panel
                </h4>
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-semibold px-2 py-1">
                    Super Admin
                </span>
            </div>
            <p class="text-muted small mb-0 mt-1">
                Global governance over all registered tenants, agency workspaces, team member hierarchies, and social connectivity.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark border px-3 py-2 small">
                <i class="bi bi-person-circle text-primary me-1"></i> Logged in as: <strong>{{ auth()->user()->name }}</strong>
            </span>
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i>
                <span>Return to Workspace</span>
            </a>
        </div>
    </div>

    <!-- Global Platform KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-bold">Total Registered Users</span>
                        <h3 class="fw-bold text-dark mb-0 mt-1">{{ number_format($metrics['totalUsers']) }}</h3>
                        <span class="text-success small fw-medium" style="font-size: 0.75rem;">
                            <i class="bi bi-people-fill me-1"></i>Platform Accounts
                        </span>
                    </div>
                    <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3 fs-3">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-bold">Active Workspaces</span>
                        <h3 class="fw-bold text-dark mb-0 mt-1">{{ number_format($metrics['totalActiveWorkspaces']) }}</h3>
                        <span class="text-muted small" style="font-size: 0.75rem;">
                            Total created: {{ $metrics['totalWorkspaces'] }}
                        </span>
                    </div>
                    <div class="rounded-3 bg-indigo bg-opacity-10 text-primary p-3 fs-3">
                        <i class="bi bi-briefcase"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-bold">Connected Socials</span>
                        <h3 class="fw-bold text-dark mb-0 mt-1">{{ number_format($metrics['totalSocialProfiles']) }}</h3>
                        <span class="text-success small fw-medium" style="font-size: 0.75rem;">
                            <i class="bi bi-link-45deg me-1"></i>Live Pages & Profiles
                        </span>
                    </div>
                    <div class="rounded-3 bg-info bg-opacity-10 text-info p-3 fs-3">
                        <i class="bi bi-share"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-bold">Total Posts Published</span>
                        <h3 class="fw-bold text-dark mb-0 mt-1">{{ number_format($metrics['publishedPosts']) }}</h3>
                        <span class="text-muted small" style="font-size: 0.75rem;">
                            All campaigns: {{ $metrics['totalPosts'] }}
                        </span>
                    </div>
                    <div class="rounded-3 bg-success bg-opacity-10 text-success p-3 fs-3">
                        <i class="bi bi-send-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills bg-white p-2 rounded-3 shadow-sm border mb-4 gap-2">
        <li class="nav-item">
            <button wire:click="setTab('users')" class="nav-link px-3 py-2 fw-semibold {{ $activeTab === 'users' ? 'active' : 'text-dark' }}">
                <i class="bi bi-people me-1"></i> Users & Team Members ({{ $users->total() }})
            </button>
        </li>
        <li class="nav-item">
            <button wire:click="setTab('workspaces')" class="nav-link px-3 py-2 fw-semibold {{ $activeTab === 'workspaces' ? 'active' : 'text-dark' }}">
                <i class="bi bi-briefcase me-1"></i> Workspaces & Team Roles ({{ $workspaces->total() }})
            </button>
        </li>
        <li class="nav-item">
            <button wire:click="setTab('social_profiles')" class="nav-link px-3 py-2 fw-semibold {{ $activeTab === 'social_profiles' ? 'active' : 'text-dark' }}">
                <i class="bi bi-share me-1"></i> Connected Social Channels ({{ $profiles->total() }})
            </button>
        </li>
    </ul>

    <!-- ========================================== -->
    <!-- TAB 1: USERS & TEAM MEMBERS OVERVIEW       -->
    <!-- ========================================== -->
    @if ($activeTab === 'users')
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom p-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h6 class="fw-bold mb-0 text-dark">Platform Users Directory</h6>
                    <small class="text-muted">Inspect registered accounts, their owned workspaces, and team memberships.</small>
                </div>
                <div class="col-md-4 col-12">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control bg-light border-start-0" wire:model.live.debounce.300ms="userSearch" placeholder="Search user by name or email...">
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase text-muted">
                        <tr>
                            <th class="ps-3">User</th>
                            <th>Role & Access</th>
                            <th>Owned Workspaces</th>
                            <th>Total Team Under User</th>
                            <th>Joined Date</th>
                            <th class="text-end pe-3">Workspace Switch / Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            @php
                                $totalTeamInUserWorkspaces = $user->ownedWorkspaces->sum('members_count');
                            @endphp
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-xs fw-bold" style="width: 36px; height: 36px; font-size: 0.9rem;">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark small">{{ $user->name }}</div>
                                            <div class="text-muted" style="font-size: 0.75rem;">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if ($user->is_super_admin)
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                            <i class="bi bi-shield-check me-1"></i>Super Admin
                                        </span>
                                    @else
                                        <span class="badge bg-light text-secondary border px-2 py-1">
                                            Regular Tenant
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if ($user->ownedWorkspaces->count() > 0)
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach ($user->ownedWorkspaces as $ws)
                                                <span class="badge bg-light text-dark border px-2 py-1 small" title="Workspace: {{ $ws->name }}">
                                                    <i class="bi bi-briefcase-fill text-primary me-1"></i>{{ $ws->name }}
                                                    <span class="badge bg-secondary ms-1">{{ $ws->members_count }} team</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted small fst-italic">None (Member only)</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold text-dark fs-6">{{ $totalTeamInUserWorkspaces }}</span>
                                    <span class="text-muted small">members</span>
                                </td>
                                <td class="text-secondary small">
                                    {{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-flex align-items-center justify-content-end gap-2">
                                        @if ($user->ownedWorkspaces->count() === 1)
                                            <button wire:click="switchIntoWorkspace({{ $user->ownedWorkspaces->first()->id }})" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 py-1 px-2" style="font-size: 0.75rem;" title="Switch into this user's workspace">
                                                <i class="bi bi-box-arrow-in-right"></i>
                                                <span>Switch to Workspace</span>
                                            </button>
                                        @elseif ($user->ownedWorkspaces->count() > 1)
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-primary dropdown-toggle py-1 px-2" style="font-size: 0.75rem;" type="button" data-bs-toggle="dropdown">
                                                    <i class="bi bi-box-arrow-in-right me-1"></i>Switch Workspace ({{ $user->ownedWorkspaces->count() }})
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                    @foreach ($user->ownedWorkspaces as $ws)
                                                        <li>
                                                            <button wire:click="switchIntoWorkspace({{ $ws->id }})" class="dropdown-item small">
                                                                <i class="bi bi-briefcase me-1 text-primary"></i> {{ $ws->name }}
                                                            </button>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif

                                        @if ($user->id !== auth()->id())
                                            <button wire:click="toggleSuperAdmin({{ $user->id }})" wire:confirm="Are you sure you want to toggle Super Admin status for {{ $user->name }}?" class="btn btn-sm {{ $user->is_super_admin ? 'btn-light text-danger border' : 'btn-light text-secondary border' }} py-1 px-2" style="font-size: 0.75rem;" title="{{ $user->is_super_admin ? 'Demote from Super Admin' : 'Make Super Admin' }}">
                                                <i class="bi bi-shield"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No users match your search criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="card-footer bg-white border-top p-3">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- ========================================== -->
    <!-- TAB 2: WORKSPACES & TEAM ROLES DIRECTORY   -->
    <!-- ========================================== -->
    @if ($activeTab === 'workspaces')
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom p-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h6 class="fw-bold mb-0 text-dark">Workspaces & Team Roles Hierarchy</h6>
                    <small class="text-muted">Directly inspect all tenant workspaces, role assignments, and jump directly into any workspace.</small>
                </div>
                <div class="col-md-4 col-12">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control bg-light border-start-0" wire:model.live.debounce.300ms="workspaceSearch" placeholder="Search workspace name or slug...">
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase text-muted">
                        <tr>
                            <th class="ps-3">Workspace</th>
                            <th>Primary Owner</th>
                            <th>Status</th>
                            <th>Team Members</th>
                            <th>Role Distribution</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($workspaces as $ws)
                            @php
                                $roleCounts = $ws->members->groupBy('role.value')->map->count();
                            @endphp
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-3 bg-indigo bg-opacity-10 text-primary p-2 fs-5">
                                            <i class="bi bi-building"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark small">{{ $ws->name }}</div>
                                            <div class="text-muted" style="font-size: 0.75rem;">
                                                Slug: <code>{{ $ws->slug }}</code> | TZ: {{ $ws->timezone ?? 'Asia/Kolkata' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="small fw-semibold text-dark">{{ $ws->owner?->name ?? 'N/A' }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $ws->owner?->email ?? 'No owner assigned' }}</div>
                                </td>
                                <td>
                                    @if ($ws->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                            <i class="bi bi-check-circle me-1"></i>Active
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                            Suspended
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <button wire:click="openTeamModal({{ $ws->id }})" class="btn btn-sm btn-light border py-1 px-2 text-primary fw-semibold" style="font-size: 0.8rem;">
                                        <i class="bi bi-people-fill me-1"></i> {{ $ws->members->count() }} members
                                    </button>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach ($roleCounts as $roleName => $count)
                                            <span class="badge bg-light text-dark border px-2 py-1 small">
                                                {{ ucfirst($roleName) }}: <strong>{{ $count }}</strong>
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-flex align-items-center justify-content-end gap-2">
                                        <button wire:click="switchIntoWorkspace({{ $ws->id }})" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1 py-1 px-2" style="font-size: 0.75rem;">
                                            <i class="bi bi-box-arrow-in-right"></i>
                                            <span>Enter Workspace</span>
                                        </button>
                                        <button wire:click="openTeamModal({{ $ws->id }})" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 0.75rem;" title="Manage Workspace Team">
                                            <i class="bi bi-gear"></i> Team
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No workspaces match your query.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($workspaces->hasPages())
                <div class="card-footer bg-white border-top p-3">
                    {{ $workspaces->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- ========================================== -->
    <!-- TAB 3: PLATFORM SOCIAL CHANNELS OVERVIEW   -->
    <!-- ========================================== -->
    @if ($activeTab === 'social_profiles')
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom p-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h6 class="fw-bold mb-0 text-dark">Platform Connected Channels</h6>
                    <small class="text-muted">All active social media profiles, pages, and token connectivity across all tenant workspaces.</small>
                </div>
                <div class="col-md-4 col-12">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control bg-light border-start-0" wire:model.live.debounce.300ms="profileSearch" placeholder="Search page name or platform...">
                    </div>
                </div>
            </div>

            <div class="card-body p-3">
                <div class="row g-3">
                    @forelse ($profiles as $profile)
                        <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                            <div class="card h-100 border p-3 shadow-xs">
                                <div class="d-flex align-items-start justify-content-between mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge {{ $profile->platform?->cssClass() ?? 'bg-primary' }} p-1 px-2">
                                            <i class="bi {{ $profile->platform?->icon() }} me-1"></i>
                                            {{ $profile->platform?->label() }}
                                        </span>
                                    </div>
                                    @if ($profile->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 0.7rem;">
                                            <i class="bi bi-check2"></i> Live
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1" style="font-size: 0.7rem;">
                                            Inactive
                                        </span>
                                    @endif
                                </div>

                                <div class="fw-bold text-dark text-truncate mb-1" title="{{ $profile->name }}">
                                    {{ $profile->name }}
                                </div>
                                <div class="text-muted small text-truncate mb-2" style="font-size: 0.75rem;">
                                    ID: <code>{{ $profile->profile_id }}</code>
                                </div>

                                <div class="mt-auto pt-2 border-top d-flex align-items-center justify-content-between" style="font-size: 0.75rem;">
                                    <span class="text-secondary text-truncate" style="max-width: 140px;">
                                        <i class="bi bi-briefcase me-1 text-primary"></i> {{ $profile->workspace?->name ?? 'Unassigned' }}
                                    </span>
                                    @if ($profile->workspace_id)
                                        <button wire:click="switchIntoWorkspace({{ $profile->workspace_id }})" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 0.7rem;">
                                            Open
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-4 text-muted">
                            No social profiles match your search filter.
                        </div>
                    @endforelse
                </div>
            </div>

            @if ($profiles->hasPages())
                <div class="card-footer bg-white border-top p-3">
                    {{ $profiles->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- ========================================== -->
    <!-- MODAL: TEAM MEMBERS & ROLES DRILLDOWN      -->
    <!-- ========================================== -->
    @if ($showTeamModal && $selectedWorkspace)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content shadow border-0">
                    <div class="modal-header border-bottom py-3">
                        <div>
                            <h5 class="modal-title fs-6 fw-bold mb-0 text-dark">
                                <i class="bi bi-people-fill text-primary me-2"></i>{{ $selectedWorkspace->name }} — Team Members
                            </h5>
                            <small class="text-muted">Total {{ $selectedWorkspace->members->count() }} member(s) registered in this workspace.</small>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeTeamModal"></button>
                    </div>

                    <div class="modal-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light small text-uppercase text-muted">
                                    <tr>
                                        <th class="ps-3">Member</th>
                                        <th>Current Role</th>
                                        <th>Status</th>
                                        <th>Joined</th>
                                        <th class="text-end pe-3">Change Role</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($selectedWorkspace->members as $member)
                                        <tr>
                                            <td class="ps-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-xs fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                        {{ strtoupper(substr($member->user?->name ?? 'U', 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold text-dark small">{{ $member->user?->name ?? 'Invited User' }}</div>
                                                        <div class="text-muted" style="font-size: 0.75rem;">{{ $member->user?->email ?? $member->invited_email ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge {{ $member->role->badgeClass() }}">
                                                    {{ $member->role->label() }}
                                                </span>
                                            </td>
                                            <td>
                                                @if ($member->is_active)
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.7rem;">Active</span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size: 0.7rem;">Pending</span>
                                                @endif
                                            </td>
                                            <td class="text-secondary small">
                                                {{ $member->joined_at ? $member->joined_at->format('M d, Y') : ($member->created_at ? $member->created_at->format('M d, Y') : 'N/A') }}
                                            </td>
                                            <td class="text-end pe-3">
                                                <select class="form-select form-select-sm d-inline-block w-auto" style="font-size: 0.75rem;" wire:change="updateMemberRole({{ $member->id }}, $event.target.value)">
                                                    <option value="owner" {{ $member->role->value === 'owner' ? 'selected' : '' }}>Owner</option>
                                                    <option value="admin" {{ $member->role->value === 'admin' ? 'selected' : '' }}>Admin</option>
                                                    <option value="manager" {{ $member->role->value === 'manager' ? 'selected' : '' }}>Manager</option>
                                                    <option value="editor" {{ $member->role->value === 'editor' ? 'selected' : '' }}>Editor</option>
                                                    <option value="client_viewer" {{ $member->role->value === 'client_viewer' ? 'selected' : '' }}>Client Viewer</option>
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="modal-footer border-top py-2 bg-light d-flex justify-content-between align-items-center">
                        <button wire:click="switchIntoWorkspace({{ $selectedWorkspace->id }})" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1">
                            <i class="bi bi-box-arrow-in-right"></i>
                            <span>Switch Into This Workspace Now</span>
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary" wire:click="closeTeamModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
