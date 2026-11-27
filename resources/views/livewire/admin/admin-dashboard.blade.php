<div>
    <!-- Flash Feedback Alert -->
    @if ($feedbackMessage)
        <div class="alert alert-{{ $feedbackType }} alert-dismissible fade show mb-4 d-flex align-items-center justify-content-between p-3 rounded-4 shadow-sm" role="alert" style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #10b981;">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div class="fw-medium text-white">{{ $feedbackMessage }}</div>
            </div>
            <button type="button" class="btn-close btn-close-white" wire:click="$set('feedbackMessage', null)"></button>
        </div>
    @endif

    <!-- Top Master Control Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h3 class="fw-bold text-white mb-0">Platform Master Control Panel</h3>
                <span class="badge bg-danger bg-opacity-25 text-danger border border-danger-subtle px-2 py-1 small fw-semibold">
                    <i class="bi bi-shield-shaded me-1"></i> Root Governance
                </span>
            </div>
            <p class="text-muted small mb-0">
                Complete platform supervision over all tenants, agency workspaces, registered users, and connected social media APIs.
            </p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="runCron" wire:loading.attr="disabled" class="btn btn-sm btn-outline-warning d-inline-flex align-items-center gap-1 rounded-pill px-3">
                <i class="bi bi-clock-history"></i>
                <span>Execute Cron & Due Posts</span>
            </button>
            <button wire:click="flushCache" wire:loading.attr="disabled" class="btn btn-sm btn-outline-info d-inline-flex align-items-center gap-1 rounded-pill px-3">
                <i class="bi bi-lightning-charge"></i>
                <span>Flush System Cache</span>
            </button>
        </div>
    </div>

    <!-- Global Platform KPI Cards Row -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6 col-sm-6">
            <div class="admin-metric-card h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Users</span>
                        <h2 class="fw-bold text-white mb-0 mt-1">{{ number_format($metrics['totalUsers']) }}</h2>
                        <span class="text-danger small fw-medium" style="font-size: 0.75rem;">
                            {{ $metrics['totalSuperAdmins'] }} Super Admin(s)
                        </span>
                    </div>
                    <div class="rounded-3 p-3 fs-3" style="background: rgba(99, 102, 241, 0.15); color: #818cf8;">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 col-sm-6">
            <div class="admin-metric-card h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Active Workspaces</span>
                        <h2 class="fw-bold text-white mb-0 mt-1">{{ number_format($metrics['totalActiveWorkspaces']) }}</h2>
                        <span class="text-muted small" style="font-size: 0.75rem;">
                            Total created: {{ $metrics['totalWorkspaces'] }}
                        </span>
                    </div>
                    <div class="rounded-3 p-3 fs-3" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                        <i class="bi bi-buildings-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 col-sm-6">
            <div class="admin-metric-card h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Connected Channels</span>
                        <h2 class="fw-bold text-white mb-0 mt-1">{{ number_format($metrics['totalSocialProfiles']) }}</h2>
                        <span class="text-info small fw-medium" style="font-size: 0.75rem;">
                            {{ $metrics['totalSocialConnections'] }} OAuth Connections
                        </span>
                    </div>
                    <div class="rounded-3 p-3 fs-3" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8;">
                        <i class="bi bi-share-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 col-sm-6">
            <div class="admin-metric-card h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Published Posts</span>
                        <h2 class="fw-bold text-white mb-0 mt-1">{{ number_format($metrics['publishedPosts']) }}</h2>
                        <span class="text-warning small fw-medium" style="font-size: 0.75rem;">
                            {{ $metrics['scheduledPosts'] }} Scheduled | {{ $metrics['failedPosts'] }} Failed
                        </span>
                    </div>
                    <div class="rounded-3 p-3 fs-3" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
                        <i class="bi bi-send-check-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Pills Bar -->
    <ul class="nav nav-pills p-2 rounded-4 shadow-sm mb-4 gap-2" style="background: rgba(18, 24, 38, 0.7); border: 1px solid var(--admin-border);">
        <li class="nav-item">
            <button wire:click="setTab('overview')" class="nav-link px-3 py-2 fw-semibold rounded-3 {{ $activeTab === 'overview' ? 'active' : 'text-light' }}">
                <i class="bi bi-speedometer2 me-1"></i> Overview
            </button>
        </li>
        <li class="nav-item">
            <button wire:click="setTab('users')" class="nav-link px-3 py-2 fw-semibold rounded-3 {{ $activeTab === 'users' ? 'active' : 'text-light' }}">
                <i class="bi bi-people-fill me-1"></i> Users ({{ $users->total() }})
            </button>
        </li>
        <li class="nav-item">
            <button wire:click="setTab('workspaces')" class="nav-link px-3 py-2 fw-semibold rounded-3 {{ $activeTab === 'workspaces' ? 'active' : 'text-light' }}">
                <i class="bi bi-buildings-fill me-1"></i> Workspaces ({{ $workspaces->total() }})
            </button>
        </li>
        <li class="nav-item">
            <button wire:click="setTab('social_profiles')" class="nav-link px-3 py-2 fw-semibold rounded-3 {{ $activeTab === 'social_profiles' ? 'active' : 'text-light' }}">
                <i class="bi bi-share-fill me-1"></i> Social Channels ({{ $profiles->total() }})
            </button>
        </li>
        <li class="nav-item">
            <button wire:click="setTab('posts')" class="nav-link px-3 py-2 fw-semibold rounded-3 {{ $activeTab === 'posts' ? 'active' : 'text-light' }}">
                <i class="bi bi-send-check-fill me-1"></i> Posts & Queue ({{ $allPosts->total() }})
            </button>
        </li>
        <li class="nav-item">
            <button wire:click="setTab('diagnostics')" class="nav-link px-3 py-2 fw-semibold rounded-3 {{ $activeTab === 'diagnostics' ? 'active' : 'text-light' }}">
                <i class="bi bi-cpu-fill me-1"></i> Diagnostics & Logs
            </button>
        </li>
    </ul>

    <!-- ========================================== -->
    <!-- TAB 1: MASTER OVERVIEW                     -->
    <!-- ========================================== -->
    @if ($activeTab === 'overview')
        <div class="row g-4 mb-4">
            <!-- Left: Platform Architecture & Health -->
            <div class="col-lg-7">
                <div class="admin-card p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom" style="border-color: var(--admin-border) !important;">
                        <div>
                            <h5 class="fw-bold text-white mb-1"><i class="bi bi-hdd-network text-primary me-2"></i>Infrastructure & Architecture</h5>
                            <small class="text-muted">Real-time daemon and cloud runtime parameters.</small>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">
                            <span class="pulse-live me-1"></span> Healthy
                        </span>
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="p-3 rounded-3" style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--admin-border);">
                                <span class="small text-muted d-block mb-1">PHP Engine</span>
                                <div class="fw-bold text-white mono fs-6">{{ PHP_VERSION }}</div>
                                <small class="text-muted" style="font-size: 0.72rem;">Memory Limit: {{ ini_get('memory_limit') }}</small>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 rounded-3" style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--admin-border);">
                                <span class="small text-muted d-block mb-1">Database Engine</span>
                                <div class="fw-bold text-white mono fs-6">MySQL 8.x / MariaDB</div>
                                <small class="text-success" style="font-size: 0.72rem;"><i class="bi bi-check2 me-1"></i>Connection Active</small>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 rounded-3" style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--admin-border);">
                                <span class="small text-muted d-block mb-1">Queue Driver</span>
                                <div class="fw-bold text-white mono fs-6">{{ config('queue.default') }}</div>
                                <small class="text-muted" style="font-size: 0.72rem;">Worker Publishing Pipeline</small>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 rounded-3" style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--admin-border);">
                                <span class="small text-muted d-block mb-1">Cache Store</span>
                                <div class="fw-bold text-white mono fs-6">{{ config('cache.default') }}</div>
                                <small class="text-muted" style="font-size: 0.72rem;">Atomic Lock & Session</small>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top" style="border-color: var(--admin-border) !important;">
                        <h6 class="fw-bold text-white mb-3">Quick Platform Operations</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <button wire:click="runCron" class="btn btn-sm btn-outline-light rounded-pill px-3">
                                <i class="bi bi-arrow-clockwise me-1 text-warning"></i> Trigger Scheduled Posts
                            </button>
                            <button wire:click="syncAllAnalyticsGlobal" class="btn btn-sm btn-outline-light rounded-pill px-3">
                                <i class="bi bi-graph-up-arrow me-1 text-info"></i> Ingest All Social Analytics
                            </button>
                            <button wire:click="flushCache" class="btn btn-sm btn-outline-light rounded-pill px-3">
                                <i class="bi bi-trash3 me-1 text-danger"></i> Flush System Caches
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Platform Distribution -->
            <div class="col-lg-5">
                <div class="admin-card p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom" style="border-color: var(--admin-border) !important;">
                        <div>
                            <h5 class="fw-bold text-white mb-1"><i class="bi bi-pie-chart text-info me-2"></i>SaaS Aggregates</h5>
                            <small class="text-muted">High-level distribution summary.</small>
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex align-items-center justify-content-between p-3 rounded-3" style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--admin-border);">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-people fs-4 text-primary"></i>
                                <div>
                                    <div class="text-white fw-bold small">Registered Users</div>
                                    <small class="text-muted">{{ $metrics['totalUsers'] }} total accounts</small>
                                </div>
                            </div>
                            <button wire:click="setTab('users')" class="btn btn-xs btn-outline-primary rounded-pill px-3">Manage</button>
                        </div>

                        <div class="d-flex align-items-center justify-content-between p-3 rounded-3" style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--admin-border);">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-buildings fs-4 text-success"></i>
                                <div>
                                    <div class="text-white fw-bold small">Agency Workspaces</div>
                                    <small class="text-muted">{{ $metrics['totalWorkspaces'] }} total workspaces</small>
                                </div>
                            </div>
                            <button wire:click="setTab('workspaces')" class="btn btn-xs btn-outline-success rounded-pill px-3">Manage</button>
                        </div>

                        <div class="d-flex align-items-center justify-content-between p-3 rounded-3" style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--admin-border);">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-share fs-4 text-info"></i>
                                <div>
                                    <div class="text-white fw-bold small">Social Profiles</div>
                                    <small class="text-muted">{{ $metrics['totalSocialProfiles'] }} pages & channels</small>
                                </div>
                            </div>
                            <button wire:click="setTab('social_profiles')" class="btn btn-xs btn-outline-info rounded-pill px-3">Manage</button>
                        </div>

                        <div class="d-flex align-items-center justify-content-between p-3 rounded-3" style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--admin-border);">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-file-earmark-bar-graph fs-4 text-warning"></i>
                                <div>
                                    <div class="text-white fw-bold small">Analytics Ingested</div>
                                    <small class="text-muted">{{ $metrics['totalSnapshots'] }} snapshot records</small>
                                </div>
                            </div>
                            <button wire:click="setTab('diagnostics')" class="btn btn-xs btn-outline-warning rounded-pill px-3">Logs</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ========================================== -->
    <!-- TAB 2: USERS & SUPER ADMINS                -->
    <!-- ========================================== -->
    @if ($activeTab === 'users')
        <div class="admin-card">
            <div class="p-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3" style="border-color: var(--admin-border) !important;">
                <div>
                    <h5 class="fw-bold text-white mb-0">Platform Users Directory</h5>
                    <small class="text-muted">Inspect registered accounts, toggle roles, or impersonate users.</small>
                </div>
                <div class="d-flex align-items-center gap-2 col-md-5 col-12 justify-content-end">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-dark border-secondary text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control bg-dark border-secondary text-white" wire:model.live.debounce.300ms="userSearch" placeholder="Search user by name or email...">
                    </div>
                    <button wire:click="openCreateUserModal" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1 rounded-pill px-3 text-nowrap">
                        <i class="bi bi-person-plus-fill"></i>
                        <span>Create User</span>
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-light" style="--bs-table-bg: transparent; --bs-table-hover-bg: rgba(255,255,255,0.03);">
                    <thead class="small text-uppercase text-muted" style="border-bottom: 1px solid var(--admin-border);">
                        <tr>
                            <th class="ps-3 py-3">User</th>
                            <th>Role</th>
                            <th>Owned Workspaces</th>
                            <th>Joined Date</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $u)
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-secondary bg-opacity-25 d-flex align-items-center justify-content-center text-white fw-bold" style="width: 36px; height: 36px;">
                                            {{ strtoupper(substr($u->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-white">{{ $u->name }}</div>
                                            <small class="text-muted">{{ $u->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if ($u->is_super_admin)
                                        <span class="badge bg-danger text-white px-2 py-1">
                                            <i class="bi bi-shield-fill me-1"></i> Super Admin
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary px-2 py-1">Regular User</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-dark border text-light">{{ $u->ownedWorkspaces->count() }} workspace(s)</span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $u->created_at?->format('M d, Y') ?? 'N/A' }}</small>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <button wire:click="impersonateUser({{ $u->id }})" class="btn btn-outline-info" title="Impersonate User">
                                            <i class="bi bi-box-arrow-in-right"></i> Log In As
                                        </button>
                                        <button wire:click="toggleSuperAdmin({{ $u->id }})" class="btn {{ $u->is_super_admin ? 'btn-outline-danger' : 'btn-outline-success' }}" title="Toggle Super Admin">
                                            <i class="bi bi-shield-shaded"></i>
                                            {{ $u->is_super_admin ? 'Demote' : 'Make Admin' }}
                                        </button>
                                        @if ($u->id !== auth()->id())
                                            <button wire:click="deleteUser({{ $u->id }})" wire:confirm="Are you sure you want to delete this user?" class="btn btn-outline-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">No users found matching your search.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="p-3 border-top" style="border-color: var(--admin-border) !important;">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- ========================================== -->
    <!-- TAB 3: WORKSPACES & TENANTS                -->
    <!-- ========================================== -->
    @if ($activeTab === 'workspaces')
        <div class="admin-card">
            <div class="p-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3" style="border-color: var(--admin-border) !important;">
                <div>
                    <h5 class="fw-bold text-white mb-0">Agency Workspaces Directory</h5>
                    <small class="text-muted">Manage tenant subscription tiers, maximum profile quotas, and inspect team members.</small>
                </div>
                <div class="col-md-4 col-12">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-dark border-secondary text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control bg-dark border-secondary text-white" wire:model.live.debounce.300ms="workspaceSearch" placeholder="Search workspace by name or slug...">
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-light" style="--bs-table-bg: transparent; --bs-table-hover-bg: rgba(255,255,255,0.03);">
                    <thead class="small text-uppercase text-muted" style="border-bottom: 1px solid var(--admin-border);">
                        <tr>
                            <th class="ps-3 py-3">Workspace</th>
                            <th>Owner</th>
                            <th>Plan Type</th>
                            <th>Profiles Limit</th>
                            <th>Status</th>
                            <th>Team</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($workspaces as $ws)
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                                <td class="ps-3">
                                    <div class="fw-bold text-white">{{ $ws->name }}</div>
                                    <small class="text-muted mono">{{ $ws->slug }}</small>
                                </td>
                                <td>
                                    <div class="small text-white">{{ $ws->owner?->name ?? 'None' }}</div>
                                    <small class="text-muted">{{ $ws->owner?->email ?? '' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary text-uppercase px-2 py-1">
                                        {{ $ws->plan_type ?? 'starter' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="small text-light">{{ $ws->socialProfiles->count() }} / {{ $ws->max_profiles ?? 10 }}</span>
                                </td>
                                <td>
                                    @if ($ws->is_active)
                                        <span class="badge bg-success-subtle text-success px-2 py-1">Active</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger px-2 py-1">Suspended</span>
                                    @endif
                                </td>
                                <td>
                                    <button wire:click="openTeamModal({{ $ws->id }})" class="btn btn-xs btn-outline-light rounded-pill px-2">
                                        <i class="bi bi-people me-1"></i> {{ $ws->members->count() }} Members
                                    </button>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <button wire:click="switchIntoWorkspace({{ $ws->id }})" class="btn btn-outline-primary" title="Enter Workspace">
                                            <i class="bi bi-box-arrow-in-up-right"></i> Enter
                                        </button>
                                        <button wire:click="openEditWorkspaceModal({{ $ws->id }})" class="btn btn-outline-warning" title="Edit Plan & Limits">
                                            <i class="bi bi-pencil-square"></i> Plan
                                        </button>
                                        <button wire:click="toggleWorkspaceStatus({{ $ws->id }})" class="btn {{ $ws->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}" title="Toggle Status">
                                            <i class="bi {{ $ws->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">No workspaces found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($workspaces->hasPages())
                <div class="p-3 border-top" style="border-color: var(--admin-border) !important;">
                    {{ $workspaces->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- ========================================== -->
    <!-- TAB 4: SOCIAL CHANNELS & PROFILES          -->
    <!-- ========================================== -->
    @if ($activeTab === 'social_profiles')
        <div class="admin-card">
            <div class="p-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3" style="border-color: var(--admin-border) !important;">
                <div>
                    <h5 class="fw-bold text-white mb-0">Connected Social Channels & APIs</h5>
                    <small class="text-muted">Monitor OAuth token health, trigger background metric sync, or force token refresh.</small>
                </div>
                <div class="d-flex align-items-center gap-2 col-md-5 col-12 justify-content-end">
                    <select wire:model.live="platformFilter" class="form-select form-select-sm bg-dark border-secondary text-white w-auto">
                        <option value="">All Platforms</option>
                        <option value="facebook">Facebook</option>
                        <option value="instagram">Instagram</option>
                        <option value="youtube">YouTube</option>
                        <option value="linkedin">LinkedIn</option>
                        <option value="google_business">Google Business</option>
                    </select>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-dark border-secondary text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control bg-dark border-secondary text-white" wire:model.live.debounce.300ms="profileSearch" placeholder="Search profile...">
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-light" style="--bs-table-bg: transparent; --bs-table-hover-bg: rgba(255,255,255,0.03);">
                    <thead class="small text-uppercase text-muted" style="border-bottom: 1px solid var(--admin-border);">
                        <tr>
                            <th class="ps-3 py-3">Profile / Channel</th>
                            <th>Platform</th>
                            <th>Workspace</th>
                            <th>Token Expiry</th>
                            <th>Publishing</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($profiles as $prof)
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                                <td class="ps-3">
                                    <div class="fw-bold text-white">{{ $prof->name }}</div>
                                    <small class="text-muted mono">ID: {{ $prof->profile_id }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-dark border text-light">
                                        <i class="bi {{ $prof->platform->icon() }} me-1 text-primary"></i> {{ $prof->platform->label() }}
                                    </span>
                                </td>
                                <td>
                                    <span class="small text-muted">{{ $prof->workspace?->name ?? 'None' }}</span>
                                </td>
                                <td>
                                    @php
                                        $conn = $prof->connection;
                                        $isExp = $conn ? $conn->isExpired() : true;
                                    @endphp
                                    @if ($conn)
                                        @if (!$isExp)
                                            <span class="badge bg-success-subtle text-success">Valid</span>
                                            <small class="d-block text-muted" style="font-size: 0.7rem;">{{ $conn->token_expires_at?->diffForHumans() ?? 'Perpetual' }}</small>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning">Auto-Refreshable</span>
                                            <small class="d-block text-muted" style="font-size: 0.7rem;">Token Expired</small>
                                        @endif
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">No Connection</span>
                                    @endif
                                </td>
                                <td>
                                    <button wire:click="toggleProfilePublishing({{ $prof->id }})" class="btn btn-xs {{ $prof->publishing_enabled ? 'btn-success' : 'btn-outline-secondary' }} rounded-pill">
                                        {{ $prof->publishing_enabled ? 'Enabled' : 'Paused' }}
                                    </button>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        @if ($prof->connection)
                                            <button wire:click="forceRefreshToken({{ $prof->connection->id }})" class="btn btn-outline-warning" title="Force Refresh OAuth Token">
                                                <i class="bi bi-arrow-clockwise"></i> Token
                                            </button>
                                        @endif
                                        <button wire:click="syncProfileAnalytics({{ $prof->id }})" class="btn btn-outline-info" title="Sync Metrics Now">
                                            <i class="bi bi-graph-up"></i> Sync
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No social profiles connected yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($profiles->hasPages())
                <div class="p-3 border-top" style="border-color: var(--admin-border) !important;">
                    {{ $profiles->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- ========================================== -->
    <!-- TAB 5: POSTS & PUBLISHING QUEUE MODERATION -->
    <!-- ========================================== -->
    @if ($activeTab === 'posts')
        <div class="admin-card">
            <div class="p-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3" style="border-color: var(--admin-border) !important;">
                <div>
                    <h5 class="fw-bold text-white mb-0">Platform Content & Publishing Queue</h5>
                    <small class="text-muted">Inspect scheduled, queued, published, and failed posts across all agency workspaces.</small>
                </div>
                <div class="d-flex align-items-center gap-2 col-md-5 col-12 justify-content-end">
                    <select wire:model.live="postStatusFilter" class="form-select form-select-sm bg-dark border-secondary text-white w-auto">
                        <option value="">All Statuses</option>
                        <option value="published">Published</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="queued">Queued</option>
                        <option value="failed">Failed</option>
                    </select>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-dark border-secondary text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control bg-dark border-secondary text-white" wire:model.live.debounce.300ms="postSearch" placeholder="Search caption or post ID...">
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-light" style="--bs-table-bg: transparent; --bs-table-hover-bg: rgba(255,255,255,0.03);">
                    <thead class="small text-uppercase text-muted" style="border-bottom: 1px solid var(--admin-border);">
                        <tr>
                            <th class="ps-3 py-3" style="width: 320px;">Content / Caption</th>
                            <th>Platform & Channel</th>
                            <th>Workspace</th>
                            <th>Status</th>
                            <th>Timestamp</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($allPosts as $variant)
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        @if ($variant->mediaAssets->isNotEmpty())
                                            <img src="{{ $variant->mediaAssets->first()->public_url }}" class="rounded object-fit-cover" style="width: 42px; height: 42px;" alt="">
                                        @endif
                                        <div class="text-truncate" style="max-width: 250px;">
                                            <div class="text-white small fw-medium text-truncate">{{ $variant->getEffectiveCaption() ?: 'Media Post' }}</div>
                                            @if ($variant->external_post_id)
                                                <small class="text-muted mono" style="font-size: 0.7rem;">Ext ID: {{ $variant->external_post_id }}</small>
                                            @endif
                                            @if ($variant->last_error_message)
                                                <small class="text-danger d-block text-truncate" style="font-size: 0.7rem;" title="{{ $variant->last_error_message }}">{{ $variant->last_error_message }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-dark border text-light">
                                        <i class="bi {{ $variant->socialProfile?->platform?->icon() ?? 'bi-share' }} text-primary me-1"></i>
                                        {{ $variant->socialProfile?->name ?? 'Unknown' }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $variant->post?->workspace?->name ?? 'Workspace' }}</small>
                                </td>
                                <td>
                                    <span class="badge {{ $variant->status->badgeClass() }}">
                                        {{ $variant->status->label() }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $variant->published_at?->format('M d, Y h:i A') ?? ($variant->scheduled_at?->format('M d, Y h:i A') ?? 'N/A') }}
                                    </small>
                                </td>
                                <td class="text-end pe-3">
                                    @if ($variant->status === \App\Enums\PostStatus::FAILED || $variant->status === \App\Enums\PostStatus::SCHEDULED)
                                        <button wire:click="retryPublishVariant({{ $variant->id }})" class="btn btn-xs btn-outline-warning rounded-pill px-2">
                                            <i class="bi bi-arrow-repeat me-1"></i> Publish Now
                                        </button>
                                    @endif
                                    @if ($variant->external_post_url)
                                        <a href="{{ $variant->external_post_url }}" target="_blank" class="btn btn-xs btn-outline-info rounded-pill px-2">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No posts found in platform queue.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($allPosts->hasPages())
                <div class="p-3 border-top" style="border-color: var(--admin-border) !important;">
                    {{ $allPosts->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- ========================================== -->
    <!-- TAB 6: DIAGNOSTICS & LOGS                  -->
    <!-- ========================================== -->
    @if ($activeTab === 'diagnostics')
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="admin-card p-4">
                    <h5 class="fw-bold text-white mb-3"><i class="bi bi-tools text-warning me-2"></i>System Maintenance Tools</h5>
                    <p class="small text-muted mb-4">Run background worker routines, reset memory stores, and test cron execution.</p>

                    <div class="d-grid gap-3">
                        <button wire:click="runCron" class="btn btn-outline-warning text-start p-3 rounded-3 d-flex align-items-center justify-content-between">
                            <div>
                                <div class="fw-bold text-white">Trigger Due Posts Job & Scheduler</div>
                                <small class="text-muted">Executes `DispatchDuePostsJob` and `schedule:run` synchronously.</small>
                            </div>
                            <i class="bi bi-play-circle fs-4"></i>
                        </button>

                        <button wire:click="flushCache" class="btn btn-outline-info text-start p-3 rounded-3 d-flex align-items-center justify-content-between">
                            <div>
                                <div class="fw-bold text-white">Flush All Caches</div>
                                <small class="text-muted">Clears application cache, compiled Blade templates, and config cache.</small>
                            </div>
                            <i class="bi bi-arrow-repeat fs-4"></i>
                        </button>

                        <button wire:click="syncAllAnalyticsGlobal" class="btn btn-outline-success text-start p-3 rounded-3 d-flex align-items-center justify-content-between">
                            <div>
                                <div class="fw-bold text-white">Ingest All Active Social Analytics</div>
                                <small class="text-muted">Polls Facebook & YouTube APIs for latest live impressions and metrics.</small>
                            </div>
                            <i class="bi bi-cloud-arrow-down fs-4"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="admin-card p-4">
                    <h5 class="fw-bold text-white mb-3"><i class="bi bi-journal-text text-info me-2"></i>Recent Analytics Synchronization Logs</h5>
                    <p class="small text-muted mb-3">Live feed of provider API ingestion attempts and record counts.</p>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0 text-light" style="--bs-table-bg: transparent;">
                            <thead class="small text-uppercase text-muted" style="border-bottom: 1px solid var(--admin-border);">
                                <tr>
                                    <th>Channel</th>
                                    <th>Status</th>
                                    <th>Records Ingested</th>
                                    <th>Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentSyncLogs as $log)
                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                                        <td>
                                            <span class="small fw-semibold text-white">{{ $log->socialProfile?->name ?? 'Profile #' . $log->social_profile_id }}</span>
                                        </td>
                                        <td>
                                            @if ($log->status === 'success')
                                                <span class="badge bg-success-subtle text-success">Success</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger" title="{{ $log->error_message }}">Failed</span>
                                            @endif
                                        </td>
                                        <td class="mono small">{{ $log->records_ingested }} records</td>
                                        <td class="small text-muted">{{ $log->synced_at?->diffForHumans() ?? 'Just now' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">No recent sync logs recorded yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ========================================== -->
    <!-- MODAL 1: CREATE USER                       -->
    <!-- ========================================== -->
    @if ($showCreateUserModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.7); backdrop-filter: blur(6px);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content admin-card text-light border-0 shadow-lg">
                    <div class="modal-header border-bottom py-3" style="border-color: var(--admin-border) !important;">
                        <h6 class="modal-title fw-bold text-white"><i class="bi bi-person-plus text-primary me-2"></i>Create New Platform User</h6>
                        <button type="button" class="btn-close btn-close-white" wire:click="$set('showCreateUserModal', false)"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small text-muted">Full Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model="newUserName" class="form-control bg-dark border-secondary text-white" placeholder="e.g. Rahul Sharma">
                            @error('newUserName') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted">Email Address <span class="text-danger">*</span></label>
                            <input type="email" wire:model="newUserEmail" class="form-control bg-dark border-secondary text-white" placeholder="rahul@example.com">
                            @error('newUserEmail') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="alert alert-dark border-secondary small text-muted mb-3">
                            <i class="bi bi-envelope-check text-info me-1"></i> Users sign in securely via 6-digit Gmail/Email OTP.
                        </div>
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" wire:model="newUserIsAdmin" id="adminSwitch">
                            <label class="form-check-label small text-light" for="adminSwitch">Grant Platform Super Administrator Privileges</label>
                        </div>
                    </div>
                    <div class="modal-footer border-top py-2" style="border-color: var(--admin-border) !important;">
                        <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="$set('showCreateUserModal', false)">Cancel</button>
                        <button type="button" class="btn btn-sm btn-primary" wire:click="createUser">Create & Provision Workspace</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ========================================== -->
    <!-- MODAL 2: RESET PASSWORD                   -->
    <!-- ========================================== -->
    @if ($showPasswordModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.7); backdrop-filter: blur(6px);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content admin-card text-light border-0 shadow-lg">
                    <div class="modal-header border-bottom py-3" style="border-color: var(--admin-border) !important;">
                        <h6 class="modal-title fw-bold text-white"><i class="bi bi-key-fill text-warning me-2"></i>Reset Password for {{ $passwordUserName }}</h6>
                        <button type="button" class="btn-close btn-close-white" wire:click="$set('showPasswordModal', false)"></button>
                    </div>
                    <div class="modal-body p-4">
                        <label class="form-label small text-muted">Enter New Password</label>
                        <input type="text" wire:model="newPassword" class="form-control bg-dark border-secondary text-white mono">
                        @error('newPassword') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                        <small class="text-muted d-block mt-2">Share this password with the user directly.</small>
                    </div>
                    <div class="modal-footer border-top py-2" style="border-color: var(--admin-border) !important;">
                        <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="$set('showPasswordModal', false)">Cancel</button>
                        <button type="button" class="btn btn-sm btn-warning text-dark fw-bold" wire:click="resetPassword">Save New Password</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ========================================== -->
    <!-- MODAL 3: EDIT WORKSPACE PLAN & LIMITS      -->
    <!-- ========================================== -->
    @if ($showWorkspaceModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.7); backdrop-filter: blur(6px);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content admin-card text-light border-0 shadow-lg">
                    <div class="modal-header border-bottom py-3" style="border-color: var(--admin-border) !important;">
                        <h6 class="modal-title fw-bold text-white"><i class="bi bi-pencil-square text-info me-2"></i>Edit Workspace & Quota</h6>
                        <button type="button" class="btn-close btn-close-white" wire:click="$set('showWorkspaceModal', false)"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small text-muted">Workspace Name</label>
                            <input type="text" wire:model="editWorkspaceName" class="form-control bg-dark border-secondary text-white">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted">Subscription Plan Tier</label>
                            <select wire:model="editPlanType" class="form-select bg-dark border-secondary text-white">
                                <option value="starter">Starter Plan (Basic)</option>
                                <option value="professional">Professional Plan</option>
                                <option value="agency">Agency Scale Plan</option>
                                <option value="enterprise">Enterprise Tier</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted">Maximum Connected Profiles Limit</label>
                            <input type="number" wire:model="editMaxProfiles" class="form-control bg-dark border-secondary text-white" min="1" max="1000">
                        </div>
                    </div>
                    <div class="modal-footer border-top py-2" style="border-color: var(--admin-border) !important;">
                        <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="$set('showWorkspaceModal', false)">Cancel</button>
                        <button type="button" class="btn btn-sm btn-primary" wire:click="saveWorkspacePlan">Save Changes</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ========================================== -->
    <!-- MODAL 4: WORKSPACE TEAM MEMBERS MODAL      -->
    <!-- ========================================== -->
    @if ($showTeamModal && $selectedWorkspace)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.7); backdrop-filter: blur(6px);">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content admin-card text-light border-0 shadow-lg">
                    <div class="modal-header border-bottom py-3" style="border-color: var(--admin-border) !important;">
                        <h6 class="modal-title fw-bold text-white">
                            <i class="bi bi-people-fill text-primary me-2"></i>Team Members in "{{ $selectedWorkspace->name }}"
                        </h6>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeTeamModal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 text-light" style="--bs-table-bg: transparent;">
                                <thead class="small text-uppercase text-muted" style="border-bottom: 1px solid var(--admin-border);">
                                    <tr>
                                        <th>Member Name</th>
                                        <th>Email</th>
                                        <th>Assigned Role</th>
                                        <th class="text-end">Change Role</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($selectedWorkspace->members as $mem)
                                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                                            <td>
                                                <div class="fw-bold text-white">{{ $mem->user?->name ?? 'User #' . $mem->user_id }}</div>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $mem->user?->email ?? 'N/A' }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-dark border text-light">{{ ucfirst($mem->role->value) }}</span>
                                            </td>
                                            <td class="text-end">
                                                <select class="form-select form-select-sm bg-dark border-secondary text-white d-inline-block w-auto"
                                                    wire:change="updateMemberRole({{ $mem->id }}, $event.target.value)">
                                                    <option value="owner" {{ $mem->role->value === 'owner' ? 'selected' : '' }}>Owner</option>
                                                    <option value="admin" {{ $mem->role->value === 'admin' ? 'selected' : '' }}>Admin</option>
                                                    <option value="manager" {{ $mem->role->value === 'manager' ? 'selected' : '' }}>Manager</option>
                                                    <option value="content_creator" {{ $mem->role->value === 'content_creator' ? 'selected' : '' }}>Creator</option>
                                                    <option value="client_reviewer" {{ $mem->role->value === 'client_reviewer' ? 'selected' : '' }}>Reviewer</option>
                                                    <option value="analyst" {{ $mem->role->value === 'analyst' ? 'selected' : '' }}>Analyst</option>
                                                </select>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">No members found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer border-top py-2" style="border-color: var(--admin-border) !important;">
                        <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="closeTeamModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
