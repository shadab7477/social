<div>
    <!-- Pro Dashboard Hero Banner -->
    <div class="dashboard-hero-banner mb-4">
        <div class="hero-glow"></div>
        <div class="row align-items-center g-3 position-relative">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-indigo-subtle text-white border border-indigo-subtle px-2 py-1 rounded-pill small" style="background: rgba(99, 102, 241, 0.25);">
                        <span class="pulse-online me-1"></span> Live Publishing Engine
                    </span>
                    <span class="text-white-50 small">&bull;</span>
                    <span class="text-white-50 small">Workspace: <strong>{{ $workspace->name }}</strong></span>
                </div>
                <h3 class="fw-bold text-white mb-2">
                    Welcome back, {{ auth()->user()->name ?? 'Agency Manager' }} 👋
                </h3>
                <p class="text-white-50 small mb-0" style="max-width: 620px;">
                    Here is your social content command center. Monitor upcoming publishing queues, check channel token health, and review client approvals across all official networks.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                    <a href="{{ route('composer') }}" class="btn btn-gradient-primary btn-sm px-3 py-2 d-inline-flex align-items-center gap-2 shadow">
                        <i class="bi bi-pencil-square fs-6"></i>
                        <span>Create Post</span>
                    </a>
                    <a href="{{ route('social-accounts.index') }}" class="btn btn-outline-light btn-sm px-3 py-2 d-inline-flex align-items-center gap-2" style="background: rgba(255, 255, 255, 0.08); border-color: rgba(255, 255, 255, 0.2);">
                        <i class="bi bi-plus-circle"></i>
                        <span>Connect Channels</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Failed Post Warning Alert (if any failed) -->
    @if ($failedPostsCount > 0)
        <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center justify-content-between p-3 mb-4 rounded-3" role="alert">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; font-size: 1.1rem;">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-danger">{{ $failedPostsCount }} post publication(s) failed or require attention</h6>
                    <span class="small text-danger-emphasis">Review the errors below to retry with updated permissions or adjusted media.</span>
                </div>
            </div>
            <a href="#pipeline-tabs" class="btn btn-sm btn-outline-danger">View Failed Posts</a>
        </div>
    @endif

    <!-- Top KPI Cards Row -->
    <div class="row g-3 mb-4">
        <!-- KPI 1: Connected Channels -->
        <div class="col-sm-6 col-xl-3">
            <div class="card kpi-card h-100 shadow-sm mb-0">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold text-uppercase letter-spacing-1">Connected Accounts</span>
                        <div class="stat-icon-box text-primary" style="background: #eef2ff;">
                            <i class="bi bi-share-fill"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mb-2">
                        <h2 class="fw-bold text-dark mb-0">{{ $totalConnectedProfiles }}</h2>
                        <span class="text-muted small">active channels</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                        @if ($totalConnectedProfiles > 0)
                            <span class="text-success small fw-medium d-flex align-items-center gap-1">
                                <i class="bi bi-check-circle-fill"></i> All Systems Normal
                            </span>
                        @else
                            <span class="text-warning small fw-medium d-flex align-items-center gap-1">
                                <i class="bi bi-exclamation-circle"></i> No accounts linked
                            </span>
                        @endif
                        <a href="{{ route('social-accounts.index') }}" class="text-primary small fw-semibold text-decoration-none">
                            Manage &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI 2: Scheduled Publishing Queue -->
        <div class="col-sm-6 col-xl-3">
            <div class="card kpi-card h-100 shadow-sm mb-0">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold text-uppercase letter-spacing-1">Publishing Queue</span>
                        <div class="stat-icon-box text-info" style="background: #e0f2fe;">
                            <i class="bi bi-calendar3"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mb-2">
                        <h2 class="fw-bold text-dark mb-0">{{ $scheduledPostsCount }}</h2>
                        <span class="text-muted small">queued posts</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                        @if ($nextScheduledVariant && $nextScheduledVariant->scheduled_at)
                            <span class="text-primary small fw-medium d-flex align-items-center gap-1 text-truncate" style="max-width: 160px;">
                                <i class="bi bi-clock-history"></i> Next: {{ $nextScheduledVariant->scheduled_at->diffForHumans() }}
                            </span>
                        @else
                            <span class="text-muted small">Queue is empty</span>
                        @endif
                        <a href="{{ route('calendar') }}" class="text-primary small fw-semibold text-decoration-none">
                            Calendar &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI 3: Pending Approvals -->
        <div class="col-sm-6 col-xl-3">
            <div class="card kpi-card h-100 shadow-sm mb-0">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold text-uppercase letter-spacing-1">Client Approvals</span>
                        <div class="stat-icon-box text-warning" style="background: #fef3c7;">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mb-2">
                        <h2 class="fw-bold text-dark mb-0">{{ $pendingApprovalsCount }}</h2>
                        <span class="text-muted small">pending review</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                        @if ($pendingApprovalsCount > 0)
                            <span class="text-warning-emphasis small fw-medium d-flex align-items-center gap-1">
                                <i class="bi bi-hourglass-split"></i> Requires sign-off
                            </span>
                        @else
                            <span class="text-success small fw-medium d-flex align-items-center gap-1">
                                <i class="bi bi-check2-all"></i> All drafts approved
                            </span>
                        @endif
                        <a href="{{ route('approvals.index') }}" class="text-primary small fw-semibold text-decoration-none">
                            Review &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI 4: Delivered Posts -->
        <div class="col-sm-6 col-xl-3">
            <div class="card kpi-card h-100 shadow-sm mb-0">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold text-uppercase letter-spacing-1">Delivered Posts</span>
                        <div class="stat-icon-box text-success" style="background: #dcfce7;">
                            <i class="bi bi-send-check-fill"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mb-2">
                        <h2 class="fw-bold text-dark mb-0">{{ $publishedPostsCount }}</h2>
                        <span class="text-muted small">total published</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                        <span class="text-success small fw-medium d-flex align-items-center gap-1">
                            <i class="bi bi-arrow-up-right"></i> 99.8% Success
                        </span>
                        <a href="{{ route('posts.index') }}" class="text-primary small fw-semibold text-decoration-none">
                            History &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Platform Connectivity Matrix Strip -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3 px-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-broadcast text-primary fs-5"></i>
                    <div>
                        <span class="fw-bold text-dark small d-block">Channel Integration Status</span>
                        <span class="text-muted" style="font-size: 0.75rem;">Official API Handshakes & Publishing Health</span>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    @foreach ($platformList as $key => $pInfo)
                        @php
                            $isConn = $connectedProfiles->contains(fn($p) => $p->platform instanceof \App\Enums\SocialPlatform ? $p->platform->value === $key : $p->platform === $key);
                        @endphp
                        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill border {{ $isConn ? 'bg-white border-success-subtle shadow-sm' : 'bg-light border-light-subtle opacity-75' }}">
                            <span class="platform-icon {{ $pInfo['class'] }}" style="width: 22px; height: 22px; font-size: 0.72rem;">
                                <i class="bi {{ $pInfo['icon'] }}"></i>
                            </span>
                            <span class="small fw-semibold text-dark">{{ $pInfo['name'] }}</span>
                            @if ($isConn)
                                <i class="bi bi-check-circle-fill text-success small" title="Connected & Active"></i>
                            @else
                                <a href="{{ route('oauth.connect', ['platform' => $key]) }}" class="badge bg-light text-muted border text-decoration-none" style="font-size: 0.65rem;" title="Click to Connect">
                                    + Add
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Two-Column Power Layout -->
    <div class="row g-4" id="pipeline-tabs">
        <!-- Left Main Column (8 cols): Publishing Pipeline & Queue -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4 overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-layers-fill text-primary fs-5"></i>
                            <h6 class="fw-bold mb-0 text-dark">Publishing Pipeline</h6>
                        </div>
                        <ul class="nav nav-pills small" id="dashboardTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active px-3 py-1 rounded-pill fw-semibold" id="upcoming-tab" data-bs-toggle="pill" data-bs-target="#upcoming-pane" type="button" role="tab">
                                    Upcoming Queue <span class="badge bg-primary ms-1">{{ $upcomingVariants->count() }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link px-3 py-1 rounded-pill fw-semibold" id="recent-tab" data-bs-toggle="pill" data-bs-target="#recent-pane" type="button" role="tab">
                                    Published <span class="badge bg-secondary ms-1">{{ $recentPublishedVariants->count() }}</span>
                                </button>
                            </li>
                            @if ($failedVariants->count() > 0)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link px-3 py-1 rounded-pill fw-semibold text-danger" id="failed-tab" data-bs-toggle="pill" data-bs-target="#failed-pane" type="button" role="tab">
                                        Failed <span class="badge bg-danger ms-1">{{ $failedVariants->count() }}</span>
                                    </button>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <div class="tab-content" id="dashboardTabContent">
                    <!-- Tab 1: Upcoming Queue -->
                    <div class="tab-pane fade show active p-0" id="upcoming-pane" role="tabpanel">
                        @if ($upcomingVariants->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light small text-uppercase">
                                        <tr>
                                            <th class="ps-4">Content</th>
                                            <th>Target Channel</th>
                                            <th>Client</th>
                                            <th>Schedule Time</th>
                                            <th>Status</th>
                                            <th class="pe-4 text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($upcomingVariants as $v)
                                            @php
                                                $plat = $v->socialProfile?->platform instanceof \App\Enums\SocialPlatform ? $v->socialProfile->platform : \App\Enums\SocialPlatform::tryFrom($v->socialProfile?->platform ?? '');
                                                $captionText = $v->custom_caption ?: ($v->post?->base_caption ?: 'No caption');
                                                $firstMedia = $v->mediaAssets->first();
                                            @endphp
                                            <tr>
                                                <td class="ps-4 py-3">
                                                    <div class="d-flex align-items-center gap-2">
                                                        @if ($firstMedia)
                                                            @if ($firstMedia->media_type === 'image')
                                                                <img src="{{ $firstMedia->public_url }}" alt="Media" class="rounded object-fit-cover shadow-sm" style="width: 40px; height: 40px; flex-shrink: 0;">
                                                            @else
                                                                <div class="rounded bg-dark text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px; flex-shrink: 0;">
                                                                    <i class="bi bi-play-circle fs-5"></i>
                                                                </div>
                                                            @endif
                                                        @else
                                                            <div class="rounded bg-light border d-flex align-items-center justify-content-center text-muted shadow-sm" style="width: 40px; height: 40px; flex-shrink: 0;">
                                                                <i class="bi bi-chat-left-text"></i>
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <div class="fw-semibold text-dark text-truncate" style="max-width: 220px;" title="{{ $captionText }}">
                                                                {{ $captionText }}
                                                            </div>
                                                            @if ($v->mediaAssets->count() > 1)
                                                                <span class="text-muted small" style="font-size: 0.72rem;">
                                                                    <i class="bi bi-images"></i> {{ $v->mediaAssets->count() }} media files
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="platform-icon {{ $plat?->value ?? 'facebook' }}" style="width: 26px; height: 26px; font-size: 0.75rem;">
                                                            <i class="bi {{ $plat?->icon() ?? 'bi-share' }}"></i>
                                                        </span>
                                                        <span class="small fw-semibold text-dark text-truncate" style="max-width: 120px;">
                                                            {{ $v->socialProfile?->name ?? 'Channel' }}
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
                                                    <div class="small fw-semibold text-dark">
                                                        {{ $v->scheduled_at ? $v->scheduled_at->format('M d, g:i A') : 'Immediately' }}
                                                    </div>
                                                    <div class="text-muted" style="font-size: 0.72rem;">
                                                        {{ $v->scheduled_at ? $v->scheduled_at->diffForHumans() : 'Ready' }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge {{ $v->status->badgeClass() }}">
                                                        {{ $v->status->label() }}
                                                    </span>
                                                </td>
                                                <td class="pe-4 text-end">
                                                    <a href="{{ route('calendar') }}" class="btn btn-sm btn-outline-secondary p-1 px-2" title="View in Calendar">
                                                        <i class="bi bi-calendar3"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5 px-3">
                                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center p-3 mb-3 border">
                                    <i class="bi bi-calendar-plus text-primary fs-3"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">Your Publishing Queue is Ready</h6>
                                <p class="text-muted small mb-3 mx-auto" style="max-width: 400px;">
                                    There are currently no posts queued. Click below to draft and schedule posts with automated multi-channel delivery.
                                </p>
                                <a href="{{ route('composer') }}" class="btn btn-primary btn-sm px-3 shadow-sm">
                                    <i class="bi bi-pencil-square me-1"></i> Create New Post
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Tab 2: Recently Published -->
                    <div class="tab-pane fade p-0" id="recent-pane" role="tabpanel">
                        @if ($recentPublishedVariants->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light small text-uppercase">
                                        <tr>
                                            <th class="ps-4">Delivered Post</th>
                                            <th>Channel</th>
                                            <th>Published At</th>
                                            <th class="pe-4 text-end">Live Link</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($recentPublishedVariants as $rv)
                                            @php
                                                $rvPlat = $rv->socialProfile?->platform instanceof \App\Enums\SocialPlatform ? $rv->socialProfile->platform : \App\Enums\SocialPlatform::tryFrom($rv->socialProfile?->platform ?? '');
                                                $rvCaption = $rv->custom_caption ?: ($rv->post?->base_caption ?: 'No caption');
                                            @endphp
                                            <tr>
                                                <td class="ps-4 py-3">
                                                    <div class="fw-semibold text-dark text-truncate" style="max-width: 280px;" title="{{ $rvCaption }}">
                                                        {{ $rvCaption }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="platform-icon {{ $rvPlat?->value ?? 'facebook' }}" style="width: 26px; height: 26px; font-size: 0.75rem;">
                                                            <i class="bi {{ $rvPlat?->icon() ?? 'bi-share' }}"></i>
                                                        </span>
                                                        <span class="small fw-semibold text-dark text-truncate" style="max-width: 140px;">
                                                            {{ $rv->socialProfile?->name }}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="small text-muted">
                                                        {{ $rv->published_at ? $rv->published_at->diffForHumans() : 'Recently' }}
                                                    </span>
                                                </td>
                                                <td class="pe-4 text-end">
                                                    @if ($rv->external_post_url)
                                                        <a href="{{ $rv->external_post_url }}" target="_blank" class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1 py-1">
                                                            <i class="bi bi-box-arrow-up-right"></i>
                                                            <span>Live Post</span>
                                                        </a>
                                                    @else
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Published</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5 text-muted small">
                                <i class="bi bi-inbox fs-3 text-secondary opacity-50 d-block mb-1"></i>
                                No published posts recorded yet.
                            </div>
                        @endif
                    </div>

                    <!-- Tab 3: Failed Posts (if any) -->
                    @if ($failedVariants->count() > 0)
                        <div class="tab-pane fade p-0" id="failed-pane" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light small text-uppercase">
                                        <tr>
                                            <th class="ps-4">Failed Content</th>
                                            <th>Target Channel</th>
                                            <th>Error Reason</th>
                                            <th class="pe-4 text-end">Resolution</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($failedVariants as $fv)
                                            @php
                                                $fvPlat = $fv->socialProfile?->platform instanceof \App\Enums\SocialPlatform ? $fv->socialProfile->platform : \App\Enums\SocialPlatform::tryFrom($fv->socialProfile?->platform ?? '');
                                                $fvCaption = $fv->custom_caption ?: ($fv->post?->base_caption ?: 'No caption');
                                            @endphp
                                            <tr>
                                                <td class="ps-4 py-3">
                                                    <div class="fw-semibold text-dark text-truncate" style="max-width: 220px;">
                                                        {{ $fvCaption }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="platform-icon {{ $fvPlat?->value ?? 'facebook' }}" style="width: 24px; height: 24px; font-size: 0.72rem;">
                                                            <i class="bi {{ $fvPlat?->icon() ?? 'bi-share' }}"></i>
                                                        </span>
                                                        <span class="small fw-semibold text-dark">{{ $fv->socialProfile?->name }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="text-danger small fw-medium text-truncate" style="max-width: 260px;" title="{{ $fv->error_message }}">
                                                        <i class="bi bi-exclamation-triangle me-1"></i>{{ $fv->error_message ?: 'Publishing rejected by platform' }}
                                                    </div>
                                                </td>
                                                <td class="pe-4 text-end">
                                                    <a href="{{ route('calendar') }}" class="btn btn-sm btn-outline-danger py-1">
                                                        Retry / Fix
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="card-footer bg-light py-2 px-4 d-flex align-items-center justify-content-between">
                    <span class="text-muted small">Showing up to latest 6 items</span>
                    <a href="{{ route('posts.index') }}" class="text-primary small fw-semibold text-decoration-none">
                        View All Posts &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Right Column (4 cols): Channels Health, Clients & Quick Launch -->
        <div class="col-lg-4">
            <!-- Fast Compose Launcher Card -->
            <div class="card border-0 shadow-sm p-4 text-white mb-4 position-relative overflow-hidden" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="badge bg-white text-primary fw-bold px-2 py-1 small rounded-pill">1-Click Publish</span>
                    <i class="bi bi-lightning-charge-fill fs-5 text-warning"></i>
                </div>
                <h5 class="fw-bold mb-1">Create & Distribute Post</h5>
                <p class="small text-white-50 mb-3">
                    Compose once, customize per network with custom YouTube thumbnails, video titles, and Instagram aspect ratios.
                </p>
                <div class="d-grid gap-2">
                    <a href="{{ route('composer') }}" class="btn btn-light fw-bold text-primary shadow-sm">
                        <i class="bi bi-pencil-square me-1"></i> Open Post Composer
                    </a>
                    <a href="{{ route('social-accounts.index') }}" class="btn btn-outline-light btn-sm" style="border-color: rgba(255, 255, 255, 0.4);">
                        <i class="bi bi-link-45deg me-1"></i> Share Client Connect Link
                    </a>
                </div>
            </div>

            <!-- Connected Channels Health Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 px-3 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check text-success"></i> Connected Channels ({{ $connectedProfiles->count() }})
                    </h6>
                    <a href="{{ route('social-accounts.index') }}" class="btn btn-xs btn-outline-primary">Manage</a>
                </div>
                <div class="card-body p-0">
                    @if ($connectedProfiles->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach ($connectedProfiles as $prof)
                                @php
                                    $pPlat = $prof->platform instanceof \App\Enums\SocialPlatform ? $prof->platform : \App\Enums\SocialPlatform::tryFrom($prof->platform ?? '');
                                @endphp
                                <li class="list-group-item d-flex align-items-center justify-content-between p-3">
                                    <div class="d-flex align-items-center gap-2">
                                        @if ($prof->avatar_url)
                                            <img src="{{ $prof->avatar_url }}" alt="{{ $prof->name }}" class="rounded-circle object-fit-cover shadow-sm" style="width: 36px; height: 36px;">
                                        @else
                                            <span class="platform-icon {{ $pPlat?->value ?? 'facebook' }}" style="width: 36px; height: 36px; font-size: 0.95rem;">
                                                <i class="bi {{ $pPlat?->icon() ?? 'bi-share' }}"></i>
                                            </span>
                                        @endif
                                        <div>
                                            <div class="fw-semibold text-dark small text-truncate" style="max-width: 140px;">
                                                {{ $prof->name }}
                                            </div>
                                            <span class="text-muted" style="font-size: 0.72rem;">
                                                {{ $pPlat?->label() }}
                                            </span>
                                        </div>
                                    </div>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle d-inline-flex align-items-center gap-1" style="font-size: 0.68rem;">
                                        <span class="pulse-online"></span> Active
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center p-4 text-muted">
                            <i class="bi bi-share text-secondary fs-2 d-block mb-2 opacity-50"></i>
                            <p class="small text-muted mb-2">No channels connected in this workspace.</p>
                            <a href="{{ route('social-accounts.index') }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-plug-fill me-1"></i> Connect Channels
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Active Clients Brand Widget -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 px-3 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-buildings-fill text-primary"></i> Client Brands ({{ $clientsCount }})
                    </h6>
                    <a href="{{ route('clients.index') }}" class="btn btn-xs btn-outline-secondary">View All</a>
                </div>
                <div class="card-body p-0">
                    @if ($activeClients->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach ($activeClients as $client)
                                <li class="list-group-item d-flex align-items-center justify-content-between px-3 py-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded bg-light border d-flex align-items-center justify-content-center text-primary" style="width: 32px; height: 32px; font-weight: bold; font-size: 0.8rem;">
                                            {{ substr($client->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark small text-truncate" style="max-width: 140px;">
                                                {{ $client->name }}
                                            </div>
                                            <span class="text-muted" style="font-size: 0.72rem;">
                                                {{ $client->profiles_count }} channel(s)
                                            </span>
                                        </div>
                                    </div>
                                    <span class="badge bg-light text-secondary border">Client</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center py-3 px-2 text-muted small">
                            <span>No dedicated client brands created yet.</span>
                            <div class="mt-2">
                                <a href="{{ route('clients.index') }}" class="btn btn-xs btn-outline-primary">+ Add Client</a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- System Architecture & Security Status -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <h6 class="fw-bold text-dark small text-uppercase mb-3">Enterprise Status</h6>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small"><i class="bi bi-clock me-2"></i>Workspace Timezone</span>
                        <span class="fw-semibold text-dark small">{{ $workspace->timezone ?? 'Asia/Kolkata' }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small"><i class="bi bi-shield-lock-fill text-success me-2"></i>Token Security</span>
                        <span class="badge bg-success-subtle text-success">AES-256-GCM</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2">
                        <span class="text-muted small"><i class="bi bi-cpu-fill text-primary me-2"></i>Queue Engine</span>
                        <span class="badge bg-primary-subtle text-primary">Redis &bull; Active</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
