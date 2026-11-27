<div>
    <div class="row g-4">
        <!-- Main Column (Left / Center, 8 cols) -->
        <div class="col-xl-8 col-lg-7">
            <!-- Section 1: Your activities today -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <h4 class="fw-bold text-dark mb-0" style="letter-spacing: -0.02em;">
                    Your activities today <span class="text-secondary fw-normal">({{ $totalConnectedProfiles }})</span>
                </h4>
                <a href="{{ route('social-accounts.index') }}" class="text-dark small fw-bold text-decoration-none d-flex align-items-center gap-1">
                    <span>Connected Accounts</span> <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <!-- Pastel Activities Cards Row -->
            <div class="row g-3 mb-4">
                <!-- Mint Card 1: Meta Suite -->
                <div class="col-md-6">
                    <div class="card-pastel-mint p-3 p-sm-4 h-100 position-relative">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <!-- Avatar Cluster Pile -->
                            <div class="avatar-pile">
                                <div class="avatar-item bg-primary text-white d-flex align-items-center justify-content-center" style="font-size: 0.75rem;">
                                    <i class="bi bi-facebook"></i>
                                </div>
                                <div class="avatar-item bg-danger text-white d-flex align-items-center justify-content-center" style="font-size: 0.75rem;">
                                    <i class="bi bi-instagram"></i>
                                </div>
                                <div class="avatar-item more-count">+{{ max(1, $totalConnectedProfiles) }}</div>
                            </div>
                            <span class="badge bg-white text-dark rounded-pill px-2 py-1 shadow-sm small fw-bold">
                                ⭐ 4.9
                            </span>
                        </div>
                        <div class="d-flex align-items-end justify-content-between gap-2">
                            <div class="min-w-0">
                                <h5 class="fw-bold mb-1">Meta & Instagram</h5>
                                <span class="small opacity-75">Feed, Reels & Stories Publishing</span>
                            </div>
                            <a href="{{ route('social-accounts.index') }}" class="btn-circle-arrow flex-shrink-0" title="Open Meta Channels" aria-label="Open Meta Channels">
                                ↗
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Blush Pink Card 2: YouTube Hub -->
                <div class="col-md-6">
                    <div class="card-pastel-pink p-3 p-sm-4 h-100 position-relative">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="avatar-pile">
                                <div class="avatar-item bg-danger text-white d-flex align-items-center justify-content-center" style="font-size: 0.75rem;">
                                    <i class="bi bi-youtube"></i>
                                </div>
                                <div class="avatar-item bg-dark text-white d-flex align-items-center justify-content-center" style="font-size: 0.75rem;">
                                    <i class="bi bi-tiktok"></i>
                                </div>
                                <div class="avatar-item more-count">+2</div>
                            </div>
                            <span class="badge bg-white text-dark rounded-pill px-2 py-1 shadow-sm small fw-bold">
                                ⭐ 4.8
                            </span>
                        </div>
                        <div class="d-flex align-items-end justify-content-between gap-2">
                            <div class="min-w-0">
                                <h5 class="fw-bold mb-1">Video & YouTube Hub</h5>
                                <span class="small opacity-75">Custom Thumbnails & Resumable Upload</span>
                            </div>
                            <a href="{{ route('composer') }}" class="btn-circle-arrow flex-shrink-0" title="Create Video Post" aria-label="Create Video Post">
                                ↗
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Publishing & Growth Progress (Metric Pills Row) -->
            <h4 class="fw-bold text-dark mb-3" style="letter-spacing: -0.02em;">
                Publishing progress
            </h4>

            <div class="row g-3 mb-4">
                <!-- Mint Metric: Completed / Delivered -->
                <div class="col-6 col-md-3 col-xl">
                    <div class="card-pastel-mint p-3 h-100 d-flex flex-column justify-content-between">
                        <span class="small fw-bold opacity-75 mb-2">Completed</span>
                        <div class="d-flex align-items-baseline justify-content-between gap-2">
                            <h2 class="fw-bold mb-0">{{ $publishedPostsCount }}</h2>
                            <a href="{{ route('posts.index') }}" class="btn-circle-arrow flex-shrink-0" style="width: 32px; height: 32px; font-size: 0.85rem;" title="View Posts" aria-label="View Posts">
                                ↗
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Butter Yellow Metric: Scheduled Queue -->
                <div class="col-6 col-md-3 col-xl">
                    <div class="card-pastel-yellow p-3 h-100 d-flex flex-column justify-content-between">
                        <span class="small fw-bold opacity-75 mb-2">Queued</span>
                        <div class="d-flex align-items-baseline justify-content-between gap-2">
                            <h2 class="fw-bold mb-0">{{ $scheduledPostsCount }}</h2>
                            <a href="{{ route('calendar') }}" class="btn-circle-arrow flex-shrink-0" style="width: 32px; height: 32px; font-size: 0.85rem;" title="View Calendar" aria-label="View Calendar">
                                ↗
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Lavender Metric: Pending Approvals -->
                <div class="col-6 col-md-3 col-xl">
                    <div class="card-pastel-lavender p-3 h-100 d-flex flex-column justify-content-between">
                        <span class="small fw-bold opacity-75 mb-2">Approvals</span>
                        <div class="d-flex align-items-baseline justify-content-between gap-2">
                            <h2 class="fw-bold mb-0">{{ $pendingApprovalsCount }}</h2>
                            <a href="{{ route('approvals.index') }}" class="btn-circle-arrow flex-shrink-0" style="width: 32px; height: 32px; font-size: 0.85rem;" title="View Approvals" aria-label="View Approvals">
                                ↗
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Ice Blue Metric: Active Clients -->
                <div class="col-6 col-md-3 col-xl">
                    <div class="card-pastel-blue p-3 h-100 d-flex flex-column justify-content-between">
                        <span class="small fw-bold opacity-75 mb-2">Clients</span>
                        <div class="d-flex align-items-baseline justify-content-between gap-2">
                            <h2 class="fw-bold mb-0">{{ $clientsCount }}</h2>
                            <a href="{{ route('clients.index') }}" class="btn-circle-arrow flex-shrink-0" style="width: 32px; height: 32px; font-size: 0.85rem;" title="View Clients" aria-label="View Clients">
                                ↗
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Feature Progress Bars (Pastel Yellow & Pink) -->
            <div class="card-pastel-yellow p-3 p-sm-4 mb-3 position-relative">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                    <div class="min-w-0">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-lightning-charge-fill text-dark"></i>
                            <span class="small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Next Scheduled Release</span>
                        </div>
                        <span class="small opacity-75 d-block mb-1">
                            <i class="bi bi-clock me-1"></i> {{ $nextScheduledVariant && $nextScheduledVariant->scheduled_at ? $nextScheduledVariant->scheduled_at->diffForHumans() : 'No posts in queue' }}
                        </span>
                        <h5 class="fw-bold text-dark mb-3">
                            {{ $nextScheduledVariant ? Str::limit($nextScheduledVariant->custom_caption ?: ($nextScheduledVariant->post?->base_caption ?: 'Campaign Post'), 50) : 'Active Multi-Channel Publishing Pipeline' }}
                        </h5>
                    </div>
                    <a href="{{ route('composer') }}" class="btn-circle-arrow" title="Create Post">
                        ↗
                    </a>
                </div>
                <!-- Progress Line Indicator -->
                <div class="w-100 bg-dark bg-opacity-10 rounded-pill" style="height: 5px;">
                    <div class="bg-dark rounded-pill" style="width: {{ $scheduledPostsCount > 0 ? '70%' : '100%' }}; height: 5px;"></div>
                </div>
            </div>

            <div class="card-pastel-pink p-3 p-sm-4 mb-4 position-relative">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                    <div class="min-w-0">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-broadcast text-dark"></i>
                            <span class="small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Social Channels Status</span>
                        </div>
                        <span class="small opacity-75 d-block mb-1">
                            {{ $totalConnectedProfiles }} active profiles &bull; {{ $workspace->timezone ?? 'Asia/Kolkata' }}
                        </span>
                        <h5 class="fw-bold text-dark mb-3">
                            Live Token Encryption & Background Queue Dispatched
                        </h5>
                    </div>
                    <a href="{{ route('social-accounts.index') }}" class="btn-circle-arrow flex-shrink-0" title="Manage Accounts" aria-label="Manage Accounts">
                        ↗
                    </a>
                </div>
                <div class="w-100 bg-dark bg-opacity-10 rounded-pill" style="height: 5px;">
                    <div class="bg-dark rounded-pill" style="width: 100%; height: 5px;"></div>
                </div>
            </div>

            <!-- Section 4: Upcoming Queue Table (Frosted Glass Card) -->
            <div class="card-frosted p-3 p-sm-4 mb-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <h5 class="fw-bold text-dark mb-0">Publishing Timeline</h5>
                    <a href="{{ route('posts.index') }}" class="text-dark small fw-bold text-decoration-none">
                        View All Posts &rarr;
                    </a>
                </div>

                @if ($upcomingVariants->count() > 0)
                    <div class="table-wrapper table-responsive" tabindex="0" role="region" aria-label="Publishing timeline">
                        <table class="table table-borderless align-middle mb-0 table-stack-mobile">
                            <thead>
                                <tr class="text-muted small text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                                    <th>Post Caption</th>
                                    <th>Channel</th>
                                    <th>Client</th>
                                    <th>Scheduled Time</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($upcomingVariants as $v)
                                    @php
                                        $plat = $v->socialProfile?->platform instanceof \App\Enums\SocialPlatform ? $v->socialProfile->platform : \App\Enums\SocialPlatform::tryFrom($v->socialProfile?->platform ?? '');
                                        $caption = $v->custom_caption ?: ($v->post?->base_caption ?: 'No caption');
                                    @endphp
                                    <tr class="border-top border-light-subtle">
                                        <td class="py-3 td-primary-stack" data-label="Post Caption">
                                            <div class="fw-bold text-dark text-truncate" style="max-width: 100%;" title="{{ $caption }}">
                                                {{ $caption }}
                                            </div>
                                        </td>
                                        <td data-label="Channel">
                                            <span class="badge bg-light text-dark border rounded-pill px-2 py-1 small">
                                                <i class="bi {{ $plat?->icon() ?? 'bi-share' }} me-1"></i> {{ $v->socialProfile?->name ?? 'Channel' }}
                                            </span>
                                        </td>
                                        <td data-label="Client">
                                            @if ($v->post?->client)
                                                <span class="badge bg-light text-dark border">{{ $v->post->client->name }}</span>
                                            @else
                                                <span class="text-muted small">&mdash;</span>
                                            @endif
                                        </td>
                                        <td data-label="Scheduled">
                                            <span class="small fw-semibold text-dark">
                                                {{ $v->scheduled_at ? $v->scheduled_at->format('M d, g:i A') : 'Immediately' }}
                                            </span>
                                        </td>
                                        <td class="text-end" data-label="Action">
                                            <a href="{{ route('calendar') }}" class="btn btn-sm btn-white bg-white border rounded-pill px-3 py-1 shadow-sm" style="font-size: 0.75rem; font-weight: 600;">
                                                Edit
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-calendar-check fs-2 text-secondary opacity-50 d-block mb-2"></i>
                        <h6 class="fw-bold text-dark mb-1">Queue is Clear</h6>
                        <p class="small text-muted mb-3">No posts waiting in queue right now.</p>
                        <a href="{{ route('composer') }}" class="btn-black-pill text-decoration-none">
                            <i class="bi bi-plus-lg"></i> Schedule Post
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Lesson Schedule / Calendar & Quick Actions (4 cols) -->
        <div class="col-xl-4 col-lg-5">
            <h4 class="fw-bold text-dark mb-3" style="letter-spacing: -0.02em;">
                Schedule & Calendar
            </h4>

            <!-- Mini Calendar Card (Reference Image 2) -->
            <div class="card-frosted mini-calendar-card p-3 p-sm-4 mb-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold text-dark mb-0" style="letter-spacing: -0.01em;">
                        {{ $currentMonthYear }}
                    </h5>
                    <div class="d-flex align-items-center gap-1">
                        <a href="{{ route('calendar') }}" class="btn btn-sm btn-link text-dark p-1 text-decoration-none" title="Previous Month">
                            <i class="bi bi-chevron-left fw-bold"></i>
                        </a>
                        <a href="{{ route('calendar') }}" class="btn btn-sm btn-link text-dark p-1 text-decoration-none" title="Next Month">
                            <i class="bi bi-chevron-right fw-bold"></i>
                        </a>
                    </div>
                </div>

                <!-- Calendar Weekdays -->
                <div class="calendar-grid mb-1">
                    <div class="calendar-weekday">MON</div>
                    <div class="calendar-weekday">TUE</div>
                    <div class="calendar-weekday">WED</div>
                    <div class="calendar-weekday">THU</div>
                    <div class="calendar-weekday">FRI</div>
                    <div class="calendar-weekday">SAT</div>
                    <div class="calendar-weekday">SUN</div>
                </div>

                <!-- Calendar Days Grid -->
                <div class="calendar-grid">
                    @foreach ($calendarDays as $cell)
                        <div class="calendar-day-cell {{ !$cell['inMonth'] ? 'other-month' : '' }} {{ $cell['hasPost'] ? 'has-post' : '' }} {{ $cell['isToday'] ? 'active-today' : '' }}" title="{{ $cell['hasPost'] ? 'Posts scheduled on this day' : '' }}">
                            {{ $cell['day'] }}
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Scheduled Posts Cards (Mint Pastel from Reference Image 2) -->
            <div class="d-flex flex-column gap-3 mb-4">
                @if ($upcomingVariants->count() > 0)
                    @foreach ($upcomingVariants->take(3) as $schItem)
                        @php
                            $sPlat = $schItem->socialProfile?->platform instanceof \App\Enums\SocialPlatform ? $schItem->socialProfile->platform : \App\Enums\SocialPlatform::tryFrom($schItem->socialProfile?->platform ?? '');
                            $sCaption = $schItem->custom_caption ?: ($schItem->post?->base_caption ?: 'Scheduled Post');
                        @endphp
                        <div class="card-pastel-mint p-3 position-relative">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px; flex-shrink: 0; color: #111827;">
                                    <i class="bi {{ $sPlat?->icon() ?? 'bi-send-fill' }} fs-5"></i>
                                </div>
                                <div class="flex-grow-1 text-truncate">
                                    <div class="fw-bold text-dark text-truncate small" title="{{ $sCaption }}">
                                        {{ $sCaption }}
                                    </div>
                                    <span class="small opacity-75 d-block" style="font-size: 0.75rem;">
                                        {{ $schItem->scheduled_at ? $schItem->scheduled_at->format('l \a\t g:i A') : 'Immediately' }}
                                    </span>
                                </div>
                                <a href="{{ route('calendar') }}" class="btn-circle-arrow" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                    ↗
                                </a>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="card-pastel-mint p-3 position-relative">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px; flex-shrink: 0; color: #111827;">
                                <i class="bi bi-youtube fs-5 text-danger"></i>
                            </div>
                            <div class="flex-grow-1 text-truncate">
                                <div class="fw-bold text-dark text-truncate small">
                                    Video: 'Product Feature Walkthrough'
                                </div>
                                <span class="small opacity-75 d-block" style="font-size: 0.75rem;">
                                    YouTube Resumable Publishing
                                </span>
                            </div>
                            <a href="{{ route('composer') }}" class="btn-circle-arrow" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                ↗
                            </a>
                        </div>
                    </div>

                    <div class="card-pastel-mint p-3 position-relative">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px; flex-shrink: 0; color: #111827;">
                                <i class="bi bi-instagram fs-5 text-danger"></i>
                            </div>
                            <div class="flex-grow-1 text-truncate">
                                <div class="fw-bold text-dark text-truncate small">
                                    Post: 'Client Psychology & Strategy'
                                </div>
                                <span class="small opacity-75 d-block" style="font-size: 0.75rem;">
                                    Instagram Reels & Carousel
                                </span>
                            </div>
                            <a href="{{ route('composer') }}" class="btn-circle-arrow" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                ↗
                            </a>
                        </div>
                    </div>

                    <div class="card-pastel-mint p-3 position-relative">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px; flex-shrink: 0; color: #111827;">
                                <i class="bi bi-facebook fs-5 text-primary"></i>
                            </div>
                            <div class="flex-grow-1 text-truncate">
                                <div class="fw-bold text-dark text-truncate small">
                                    Post: 'Brand Growth Announcement'
                                </div>
                                <span class="small opacity-75 d-block" style="font-size: 0.75rem;">
                                    Facebook Page Feed Container
                                </span>
                            </div>
                            <a href="{{ route('composer') }}" class="btn-circle-arrow" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                ↗
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Client Brands Quick Card -->
            <div class="card-frosted p-3 p-sm-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold text-dark mb-0">Client Workspaces</h6>
                    <a href="{{ route('clients.index') }}" class="small fw-bold text-dark text-decoration-none">
                        Manage &rarr;
                    </a>
                </div>
                @if ($activeClients->count() > 0)
                    <div class="d-flex flex-column gap-2">
                        @foreach ($activeClients->take(3) as $cl)
                            <div class="d-flex align-items-center justify-content-between gap-2 p-2 rounded-3 bg-light">
                                <div class="d-flex align-items-center gap-2 min-w-0">
                                    <div class="rounded-circle bg-white border d-flex align-items-center justify-content-center fw-bold text-dark shadow-sm flex-shrink-0" style="width: 30px; height: 30px; font-size: 0.75rem;">
                                        {{ substr($cl->name, 0, 1) }}
                                    </div>
                                    <span class="small fw-bold text-dark text-truncate">{{ $cl->name }}</span>
                                </div>
                                <span class="badge bg-white text-muted border small flex-shrink-0">{{ $cl->profiles_count }} channels</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="small text-muted mb-2">No client workspaces added yet.</p>
                    <a href="{{ route('clients.index') }}" class="btn btn-sm btn-outline-dark rounded-pill w-100">
                        + Add Client Brand
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
