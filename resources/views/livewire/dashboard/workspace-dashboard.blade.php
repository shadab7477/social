<div>
    <!-- Top KPI Row -->
    <div class="row g-3 mb-4">
        <!-- Stat 1: Connected Accounts -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-medium">Connected Accounts</span>
                        <h3 class="fw-bold mb-0 mt-1 text-dark">{{ $totalConnectedProfiles }}</h3>
                        @if ($totalConnectedProfiles > 0)
                            <span class="text-success small fw-medium"><i class="bi bi-check-circle-fill"></i> Operational</span>
                        @else
                            <a href="{{ route('social-accounts.index') }}" class="text-primary small fw-medium text-decoration-none">
                                <i class="bi bi-plus-circle"></i> Connect Profile
                            </a>
                        @endif
                    </div>
                    <div class="bg-primary-subtle text-primary rounded-3 p-3">
                        <i class="bi bi-share-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stat 2: Scheduled Posts -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-medium">Scheduled Posts</span>
                        <h3 class="fw-bold mb-0 mt-1 text-dark">{{ $scheduledPostsCount }}</h3>
                        @if ($nextScheduledVariant && $nextScheduledVariant->scheduled_at)
                            <span class="text-indigo small fw-medium">
                                <i class="bi bi-clock-history"></i> Next: {{ $nextScheduledVariant->scheduled_at->diffForHumans() }}
                            </span>
                        @else
                            <span class="text-muted small">No posts in queue</span>
                        @endif
                    </div>
                    <div class="bg-info-subtle text-info rounded-3 p-3">
                        <i class="bi bi-calendar3 fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stat 3: Pending Approvals -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-medium">Pending Approvals</span>
                        <h3 class="fw-bold mb-0 mt-1 text-dark">{{ $pendingApprovalsCount }}</h3>
                        @if ($pendingApprovalsCount > 0)
                            <a href="{{ route('approvals.index') }}" class="text-warning small fw-medium text-decoration-none">
                                <i class="bi bi-hourglass-split"></i> Review drafts
                            </a>
                        @else
                            <span class="text-success small"><i class="bi bi-check2"></i> All clear</span>
                        @endif
                    </div>
                    <div class="bg-warning-subtle text-warning rounded-3 p-3">
                        <i class="bi bi-check2-circle fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stat 4: Published Posts -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-medium">Total Published Posts</span>
                        <h3 class="fw-bold mb-0 mt-1 text-dark">{{ $publishedPostsCount }}</h3>
                        <a href="{{ route('posts.index') }}" class="text-primary small fw-medium text-decoration-none">
                            <i class="bi bi-arrow-right"></i> View directory
                        </a>
                    </div>
                    <div class="bg-success-subtle text-success rounded-3 p-3">
                        <i class="bi bi-send-check-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Column: Upcoming Queue & Recent Publishing -->
        <div class="col-lg-8">
            <!-- Upcoming Queue Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-clock-history text-primary me-2"></i>Upcoming Publishing Queue ({{ $upcomingVariants->count() }})
                    </h6>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('composer') }}" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1">
                            <i class="bi bi-plus-lg"></i> New Post
                        </a>
                        <a href="{{ route('calendar') }}" class="btn btn-sm btn-outline-secondary">Open Calendar</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if ($upcomingVariants->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light small text-uppercase">
                                    <tr>
                                        <th class="ps-3">Post Caption</th>
                                        <th>Target Profile</th>
                                        <th>Client</th>
                                        <th>Scheduled For</th>
                                        <th>Status</th>
                                        <th class="pe-3 text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($upcomingVariants as $v)
                                        @php
                                            $plat = $v->socialProfile?->platform;
                                            $captionText = $v->custom_caption ?: ($v->post?->base_caption ?: 'No caption');
                                        @endphp
                                        <tr>
                                            <td class="ps-3">
                                                <div class="fw-semibold text-dark text-truncate" style="max-width: 200px;">
                                                    {{ $captionText }}
                                                </div>
                                                @if ($v->mediaAssets->count() > 0)
                                                    <span class="text-muted small">
                                                        <i class="bi bi-paperclip"></i> {{ $v->mediaAssets->count() }} media
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-1">
                                                    <span class="badge {{ $plat?->cssClass() ?? 'bg-primary' }} p-1 px-2" style="font-size: 0.7rem;">
                                                        <i class="bi {{ $plat?->icon() }} me-1"></i> {{ $v->socialProfile?->name ?? 'Profile' }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                @if ($v->post?->client)
                                                    <span class="badge bg-light text-dark border">{{ $v->post->client->name }}</span>
                                                @else
                                                    <span class="text-muted small">&mdash;</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="small text-dark fw-medium">
                                                    {{ $v->scheduled_at ? $v->scheduled_at->format('M d, g:i A') : 'Immediately' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $v->status->badgeClass() }}">
                                                    {{ $v->status->label() }}
                                                </span>
                                            </td>
                                            <td class="pe-3 text-end">
                                                <a href="{{ route('calendar') }}" class="btn btn-xs btn-outline-secondary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-x fs-1 text-secondary opacity-50 d-block mb-2"></i>
                            <h6 class="fw-semibold text-dark mb-1">No Upcoming Scheduled Posts</h6>
                            <p class="small text-muted mb-3">Your publishing queue is currently empty. Compose a post to start scheduling!</p>
                            <a href="{{ route('composer') }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-pencil-square me-1"></i> Create Post Now
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recently Published Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-check-all text-success me-2"></i>Recently Published Content ({{ $recentPublishedVariants->count() }})
                    </h6>
                    <a href="{{ route('posts.index') }}" class="btn btn-sm btn-link text-primary p-0 text-decoration-none">View All Posts &rarr;</a>
                </div>
                <div class="card-body p-0">
                    @if ($recentPublishedVariants->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light small text-uppercase">
                                    <tr>
                                        <th class="ps-3">Post</th>
                                        <th>Platform</th>
                                        <th>Published At</th>
                                        <th class="pe-3 text-end">Live Link</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentPublishedVariants as $rv)
                                        @php
                                            $rvPlat = $rv->socialProfile?->platform;
                                            $rvCaption = $rv->custom_caption ?: ($rv->post?->base_caption ?: 'No caption');
                                        @endphp
                                        <tr>
                                            <td class="ps-3">
                                                <div class="fw-semibold text-dark text-truncate" style="max-width: 250px;">
                                                    {{ $rvCaption }}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge {{ $rvPlat?->cssClass() ?? 'bg-primary' }} p-1 px-2" style="font-size: 0.7rem;">
                                                    <i class="bi {{ $rvPlat?->icon() }} me-1"></i> {{ $rv->socialProfile?->name }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="small text-muted">
                                                    {{ $rv->published_at ? $rv->published_at->diffForHumans() : 'Recently' }}
                                                </span>
                                            </td>
                                            <td class="pe-3 text-end">
                                                @if ($rv->external_post_url)
                                                    <a href="{{ $rv->external_post_url }}" target="_blank" class="btn btn-xs btn-outline-success d-inline-flex align-items-center gap-1">
                                                        <i class="bi bi-box-arrow-up-right"></i>
                                                        <span>View Live</span>
                                                    </a>
                                                @else
                                                    <span class="badge bg-success-subtle text-success">Published</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted small">
                            <i class="bi bi-inbox fs-3 text-secondary opacity-50 d-block mb-1"></i>
                            No posts have been published yet in this workspace.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Sidebar: Connected Profiles & Quick Actions -->
        <div class="col-lg-4">
            <!-- Connected Profiles Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-shield-check text-primary me-2"></i>Connected Profiles
                    </h6>
                    <a href="{{ route('social-accounts.index') }}" class="btn btn-xs btn-outline-primary">Manage</a>
                </div>
                <div class="card-body p-0">
                    @if ($connectedProfiles->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach ($connectedProfiles as $prof)
                                @php $pPlat = $prof->platform; @endphp
                                <li class="list-group-item d-flex align-items-center justify-content-between p-3">
                                    <div class="d-flex align-items-center gap-2">
                                        @if ($prof->avatar_url)
                                            <img src="{{ $prof->avatar_url }}" alt="{{ $prof->name }}" class="rounded-circle object-fit-cover" style="width: 34px; height: 34px;">
                                        @else
                                            <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                                <i class="bi {{ $pPlat?->icon() ?? 'bi-share' }} text-dark"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <h6 class="fw-semibold text-dark mb-0 small text-truncate" style="max-width: 140px;">
                                                {{ $prof->name }}
                                            </h6>
                                            <span class="text-muted" style="font-size: 0.72rem;">
                                                {{ $pPlat?->label() }}
                                            </span>
                                        </div>
                                    </div>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.65rem;">
                                        Active
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center p-4 text-muted">
                            <i class="bi bi-share text-secondary fs-2 d-block mb-2 opacity-50"></i>
                            <p class="small text-muted mb-2">No social accounts connected yet.</p>
                            <a href="{{ route('social-accounts.index') }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-plug-fill me-1"></i> Connect Accounts
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Workspace Quick Stats -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-3">
                    <h6 class="fw-bold text-dark small text-uppercase mb-3">Workspace Overview</h6>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small"><i class="bi bi-building me-2"></i>Active Clients</span>
                        <span class="fw-bold text-dark">{{ $clientsCount }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small"><i class="bi bi-people me-2"></i>Team Members</span>
                        <span class="fw-bold text-dark">{{ $teamMembersCount }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2">
                        <span class="text-muted small"><i class="bi bi-clock me-2"></i>Workspace Timezone</span>
                        <span class="fw-semibold text-dark small">{{ $workspace->timezone ?? 'Asia/Kolkata' }}</span>
                    </div>
                </div>
            </div>

            <!-- Fast Quick Actions -->
            <div class="card border-0 shadow-sm bg-primary text-white p-3">
                <h6 class="fw-bold mb-2">Quick Shortcuts</h6>
                <div class="d-grid gap-2">
                    <a href="{{ route('composer') }}" class="btn btn-light btn-sm text-primary fw-semibold d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-pencil-square"></i> Create & Publish Post
                    </a>
                    <a href="{{ route('social-accounts.index') }}" class="btn btn-outline-light btn-sm d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-link-45deg"></i> Share Client Connect Link
                    </a>
                    <a href="{{ route('team.index') }}" class="btn btn-outline-light btn-sm d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-person-plus"></i> Invite Team Member
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

