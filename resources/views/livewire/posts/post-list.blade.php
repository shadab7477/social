<div class="posts-directory-wrapper">
    <!-- Header & Action Row -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.02em;">
                <i class="bi bi-collection me-2 text-dark"></i> Posts Directory
            </h4>
            <p class="text-muted small mb-0">Manage scheduled posts, inspect delivery status across platforms, and retry failed publications.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('calendar') }}" class="btn btn-sm btn-white bg-white border rounded-pill px-3 py-2 text-dark shadow-xs" style="font-size: 0.82rem; font-weight: 600;">
                <i class="bi bi-calendar3 me-1"></i> Calendar View
            </a>
            <a href="{{ route('composer') }}" class="btn-black-pill text-decoration-none">
                <i class="bi bi-plus-lg"></i> <span>Create Post</span>
            </a>
        </div>
    </div>

    <!-- Flash message -->
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4 py-2 d-flex align-items-center rounded-3" role="alert">
            <i class="bi bi-exclamation-octagon-fill me-2"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4 py-2 d-flex align-items-center rounded-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($feedbackMessage)
        <div class="alert alert-{{ $feedbackType }} alert-dismissible fade show mb-4 py-2 d-flex align-items-center rounded-3" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>
            <div>{{ $feedbackMessage }}</div>
            <button type="button" class="btn-close py-2" wire:click="$set('feedbackMessage', null)"></button>
        </div>
    @endif

    <!-- Status Pills Nav -->
    <div class="card-frosted p-3 mb-4">
        <div class="status-pills-scroller gap-2 mb-3 pb-1">
            <button wire:click="setStatusFilter('all')" class="btn btn-sm rounded-pill px-3 py-2 fw-semibold transition {{ $statusFilter === 'all' ? 'btn-dark' : 'btn-light border bg-white' }}">
                All Posts
                <span class="badge {{ $statusFilter === 'all' ? 'bg-white text-dark' : 'bg-light text-secondary border' }} ms-1 rounded-pill">{{ $metrics['all'] }}</span>
            </button>
            <button wire:click="setStatusFilter('scheduled')" class="btn btn-sm rounded-pill px-3 py-2 fw-semibold transition {{ $statusFilter === 'scheduled' ? 'btn-dark' : 'btn-light border bg-white' }}">
                <i class="bi bi-clock me-1"></i> Scheduled
                <span class="badge {{ $statusFilter === 'scheduled' ? 'bg-white text-dark' : 'bg-light text-secondary border' }} ms-1 rounded-pill">{{ $metrics['scheduled'] }}</span>
            </button>
            <button wire:click="setStatusFilter('published')" class="btn btn-sm rounded-pill px-3 py-2 fw-semibold transition {{ $statusFilter === 'published' ? 'btn-dark' : 'btn-light border bg-white' }}">
                <i class="bi bi-check-circle me-1 text-success"></i> Published
                <span class="badge {{ $statusFilter === 'published' ? 'bg-white text-dark' : 'bg-light text-secondary border' }} ms-1 rounded-pill">{{ $metrics['published'] }}</span>
            </button>
            <button wire:click="setStatusFilter('failed')" class="btn btn-sm rounded-pill px-3 py-2 fw-semibold transition {{ $statusFilter === 'failed' ? 'btn-danger' : 'btn-light border bg-white text-danger' }}">
                <i class="bi bi-exclamation-triangle me-1"></i> Failed
                <span class="badge {{ $statusFilter === 'failed' ? 'bg-white text-danger' : 'bg-danger text-white' }} ms-1 rounded-pill">{{ $metrics['failed'] }}</span>
            </button>
            <button wire:click="setStatusFilter('pending_approval')" class="btn btn-sm rounded-pill px-3 py-2 fw-semibold transition {{ $statusFilter === 'pending_approval' ? 'btn-dark' : 'btn-light border bg-white' }}">
                <i class="bi bi-hourglass-split me-1 text-warning"></i> Needs Approval
                <span class="badge {{ $statusFilter === 'pending_approval' ? 'bg-white text-dark' : 'bg-light text-secondary border' }} ms-1 rounded-pill">{{ $metrics['pending_approval'] }}</span>
            </button>
            <button wire:click="setStatusFilter('draft')" class="btn btn-sm rounded-pill px-3 py-2 fw-semibold transition {{ $statusFilter === 'draft' ? 'btn-dark' : 'btn-light border bg-white' }}">
                <i class="bi bi-file-earmark-text me-1"></i> Drafts
                <span class="badge {{ $statusFilter === 'draft' ? 'bg-white text-dark' : 'bg-light text-secondary border' }} ms-1 rounded-pill">{{ $metrics['draft'] }}</span>
            </button>
        </div>

        <!-- Filter Controls -->
        <div class="row g-2 align-items-center pt-2 border-top">
            <div class="col-12 col-md-5">
                <div class="search-pill-box w-100 bg-white">
                    <i class="bi bi-search text-muted small"></i>
                    <input type="text" wire:model.live.debounce.300ms="search" class="w-100 border-0 bg-transparent" style="outline: none;" placeholder="Search caption, title, or client...">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select wire:model.live="clientId" class="form-select form-select-sm rounded-pill px-3 py-2 border bg-white shadow-xs">
                    <option value="">All Clients</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select wire:model.live="platform" class="form-select form-select-sm rounded-pill px-3 py-2 border bg-white shadow-xs">
                    <option value="">All Social Platforms</option>
                    @foreach($platforms as $p)
                        <option value="{{ $p->value }}">{{ $p->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-1 text-end">
                @if($search || $clientId || $platform || $statusFilter !== 'all')
                    <button wire:click="$set('search', ''); $set('clientId', null); $set('platform', ''); $set('statusFilter', 'all');" class="btn btn-sm btn-link text-muted p-0" title="Reset Filters">
                        <i class="bi bi-x-circle fs-5"></i>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Posts Table / Cards -->
    <div class="card border-0 shadow-sm">
        <div class="table-wrapper table-responsive" tabindex="0" role="region" aria-label="Posts directory">
            <table class="table table-hover align-middle mb-0 table-stack-mobile">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th style="width: 320px;">Content</th>
                        <th>Client</th>
                        <th>Platforms & Targets</th>
                        <th>Schedule / Published</th>
                        <th>Status</th>
                        <th class="text-end" style="width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody wire:loading.remove wire:target="setStatusFilter, search, clientId, platform">
                    @forelse($posts as $post)
                        @php
                            $firstVariant = $post->variants->first();
                            $firstMedia = $firstVariant?->mediaAssets?->first();
                            $hasFailedVariant = $post->variants->contains(fn($v) => $v->status === \App\Enums\PostStatus::FAILED);
                        @endphp
                        <tr>
                            <!-- Content & Thumbnail -->
                            <td class="td-primary-stack" data-label="Content">
                                <div class="d-flex align-items-center gap-3 min-w-0">
                                    <div class="rounded bg-light border d-flex align-items-center justify-content-center overflow-hidden flex-shrink-0" style="width: 48px; height: 48px;">
                                        @if($firstMedia)
                                            @if($firstMedia->isVideo())
                                                <div class="w-100 h-100 bg-dark text-white d-flex align-items-center justify-content-center">
                                                    <i class="bi bi-play-circle-fill fs-5"></i>
                                                </div>
                                            @else
                                                <img src="{{ $firstMedia->public_url }}" alt="Thumb" class="w-100 h-100 object-fit-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <div class="w-100 h-100 align-items-center justify-content-center text-muted" style="display: none;">
                                                    <i class="bi bi-image fs-5"></i>
                                                </div>
                                            @endif
                                        @else
                                            <i class="bi bi-card-text text-muted fs-4"></i>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-grow-1">
                                        <div class="fw-semibold text-dark text-truncate mb-0" style="font-size: 0.9rem;">
                                            {{ $post->title ?: Str::limit($post->base_caption, 40) }}
                                        </div>
                                        <small class="text-muted text-truncate d-block">
                                            {{ Str::limit($post->base_caption, 50) ?: 'No text caption' }}
                                        </small>
                                        @if($post->destination_link)
                                            <small class="text-primary d-inline-block text-truncate w-100">
                                                <i class="bi bi-link-45deg"></i> {{ $post->destination_link }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Client -->
                            <td data-label="Client">
                                @if($post->client)
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi bi-building me-1 text-primary"></i> {{ $post->client->name }}
                                    </span>
                                @else
                                    <span class="text-muted small">Workspace</span>
                                @endif
                            </td>

                            <!-- Platforms & Variants Status -->
                            <td data-label="Platforms">
                                <div class="d-flex flex-wrap gap-1 align-items-center justify-content-end justify-content-md-start">
                                    @foreach($post->variants as $variant)
                                        @php
                                            $vPlatform = $variant->socialProfile?->platform;
                                            $statusIcon = match($variant->status) {
                                                \App\Enums\PostStatus::PUBLISHED => 'bi-check-circle-fill text-success',
                                                \App\Enums\PostStatus::FAILED => 'bi-exclamation-circle-fill text-danger',
                                                \App\Enums\PostStatus::SCHEDULED => 'bi-clock-fill text-primary',
                                                \App\Enums\PostStatus::PUBLISHING => 'bi-arrow-repeat text-info',
                                                default => 'bi-circle text-muted',
                                            };
                                        @endphp
                                        <span class="badge bg-light border text-dark p-1 d-inline-flex align-items-center gap-1" title="{{ $variant->socialProfile?->name }}: {{ $variant->status->label() }}">
                                            <i class="bi {{ $vPlatform?->icon() }} {{ $vPlatform?->cssClass() }}"></i>
                                            <i class="bi {{ $statusIcon }}" style="font-size: 0.75rem;"></i>
                                        </span>
                                    @endforeach
                                </div>
                            </td>

                            <!-- Timing -->
                            <td data-label="Schedule">
                                <div class="small">
                                    @if($post->status === \App\Enums\PostStatus::PUBLISHED)
                                        <span class="text-success fw-medium">
                                            <i class="bi bi-check2"></i> {{ $post->variants->first()?->published_at?->format('M d, Y h:i A') ?? $post->updated_at->format('M d, Y') }}
                                        </span>
                                    @elseif($post->scheduled_at)
                                        <span class="text-indigo fw-medium">
                                            <i class="bi bi-clock"></i> {{ $post->scheduled_at->format('M d, Y h:i A') }}
                                        </span>
                                        <small class="text-muted d-block" style="font-size: 0.75rem;">
                                            {{ $post->scheduled_at->diffForHumans() }}
                                        </small>
                                    @else
                                        <span class="text-muted">Not scheduled</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Status Badge -->
                            <td data-label="Status">
                                <span class="badge {{ $post->status->badgeClass() }}">
                                    {{ $post->status->label() }}
                                </span>
                                @if($hasFailedVariant && $post->status !== \App\Enums\PostStatus::FAILED)
                                    <span class="badge bg-danger-subtle text-danger ms-1" title="Some variants failed">
                                        <i class="bi bi-exclamation-triangle"></i> Partial Failure
                                    </span>
                                @endif
                            </td>

                            <!-- Action Buttons -->
                            <td class="text-end" data-label="Actions">
                                <div class="d-flex justify-content-end gap-1">
                                    <button wire:click="openDetailModal({{ $post->id }})" class="btn btn-sm btn-outline-secondary" title="Inspect Post Details" aria-label="Inspect Post Details">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    <button wire:click="clonePost({{ $post->id }})" class="btn btn-sm btn-outline-secondary" title="Duplicate as Draft" aria-label="Duplicate as Draft">
                                        <i class="bi bi-copy"></i>
                                    </button>

                                    <button wire:click="deletePost({{ $post->id }})" wire:confirm="Are you sure you want to delete this post?" class="btn btn-sm btn-outline-danger" title="Delete Post" aria-label="Delete Post">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x fs-1 d-block mb-2 text-secondary"></i>
                                <h6 class="fw-semibold">No posts found</h6>
                                <p class="small mb-3">No posts match your selected status and filter criteria.</p>
                                <a href="{{ route('composer') }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil-square me-1"></i> Create Your First Post
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <!-- Skeleton Shimmer Table Rows for Instant Visual Feedback -->
                <tbody wire:loading.delay wire:target="setStatusFilter, search, clientId, platform">
                    @for($i = 0; $i < 5; $i++)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="skeleton-box" style="width: 48px; height: 48px; border-radius: 8px;"></div>
                                    <div class="flex-grow-1">
                                        <div class="skeleton-box mb-2" style="width: 70%; height: 16px;"></div>
                                        <div class="skeleton-box" style="width: 45%; height: 12px;"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="skeleton-box skeleton-pill" style="width: 90px; height: 22px;"></div>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <div class="skeleton-box" style="width: 28px; height: 28px; border-radius: 6px;"></div>
                                    <div class="skeleton-box" style="width: 28px; height: 28px; border-radius: 6px;"></div>
                                </div>
                            </td>
                            <td>
                                <div class="skeleton-box" style="width: 120px; height: 16px;"></div>
                            </td>
                            <td>
                                <div class="skeleton-box skeleton-pill" style="width: 80px; height: 22px;"></div>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <div class="skeleton-box" style="width: 32px; height: 32px; border-radius: 6px;"></div>
                                    <div class="skeleton-box" style="width: 32px; height: 32px; border-radius: 6px;"></div>
                                </div>
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        @if($posts->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $posts->links() }}
            </div>
        @endif
    </div>

    <!-- Post Details Modal -->
    @if($showDetailModal && $selectedPost)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
                <div class="modal-content border-0 shadow-lg rounded-3">
                    <div class="modal-header border-bottom py-3">
                        <div class="d-flex flex-wrap align-items-center gap-2 min-w-0">
                            <span class="badge {{ $selectedPost->status->badgeClass() }}">
                                {{ $selectedPost->status->label() }}
                            </span>
                            <h6 class="modal-title fw-bold mb-0 text-truncate">
                                {{ $selectedPost->title ?: 'Post Details' }}
                            </h6>
                            @if($selectedPost->client)
                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-building me-1 text-primary"></i> {{ $selectedPost->client->name }}
                                </span>
                            @endif
                        </div>
                        <button type="button" class="btn-close" wire:click="closeDetailModal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-3 p-md-4">
                        <div class="row g-4">
                            <!-- Left Column: Master Post Info -->
                            <div class="col-md-5 border-md-end">
                                <h6 class="fw-bold text-dark mb-3">Master Post Details</h6>

                                <div class="mb-3">
                                    <div class="small text-muted fw-medium mb-1">Base Caption</div>
                                    <div class="p-3 bg-light rounded text-dark fs-6" style="white-space: pre-wrap; max-height: 220px; overflow-y: auto;">
                                        {{ $selectedPost->base_caption ?: 'No text caption provided.' }}
                                    </div>
                                </div>

                                @if($selectedPost->destination_link)
                                    <div class="mb-3">
                                        <div class="small text-muted fw-medium mb-1">Destination URL</div>
                                        <a href="{{ $selectedPost->destination_link }}" target="_blank" class="text-primary small text-break">
                                            <i class="bi bi-link-45deg me-1"></i>{{ $selectedPost->destination_link }}
                                        </a>
                                    </div>
                                @endif

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <div class="small text-muted">Author</div>
                                        <div class="small fw-semibold">{{ $selectedPost->author?->name ?? 'System' }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="small text-muted">Created Date</div>
                                        <div class="small fw-semibold">{{ $selectedPost->created_at->format('M d, Y h:i A') }}</div>
                                    </div>
                                </div>

                                @if($selectedPost->scheduled_at)
                                    <div class="p-3 bg-indigo-subtle rounded border border-indigo-subtle mb-3">
                                        <div class="small text-indigo fw-bold mb-1">Scheduled Dispatch Time</div>
                                        <div class="fw-semibold text-dark">
                                            <i class="bi bi-calendar-event me-1 text-indigo"></i>
                                            {{ $selectedPost->scheduled_at->format('M d, Y h:i A') }}
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Right Column: Target Profiles & Variant Execution -->
                            <div class="col-md-7">
                                <h6 class="fw-bold text-dark mb-3">
                                    Platform Variants & Publishing Status ({{ $selectedPost->variants->count() }})
                                </h6>

                                <div class="d-flex flex-column gap-3" style="max-height: 480px; overflow-y: auto;">
                                    @foreach($selectedPost->variants as $variant)
                                        <div class="card border p-3">
                                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge {{ $variant->socialProfile?->platform?->cssClass() ?? 'bg-primary' }} p-1 px-2">
                                                        <i class="bi {{ $variant->socialProfile?->platform?->icon() }} me-1"></i>
                                                        {{ $variant->socialProfile?->platform?->label() }}
                                                    </span>
                                                    <span class="fw-semibold text-dark small">
                                                        {{ $variant->socialProfile?->name }}
                                                    </span>
                                                </div>

                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge {{ $variant->status->badgeClass() }}">
                                                        {{ $variant->status->label() }}
                                                    </span>

                                                    @if($variant->status === \App\Enums\PostStatus::FAILED)
                                                        <button wire:click="retryVariant({{ $variant->id }})" class="btn btn-xs btn-outline-danger d-inline-flex align-items-center">
                                                            <i class="bi bi-arrow-repeat me-1"></i> Retry
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>

                                            @if($variant->custom_caption)
                                                <div class="small bg-light p-2 rounded mb-2 text-dark">
                                                    <span class="text-muted fw-bold">Custom Override:</span> {{ $variant->custom_caption }}
                                                </div>
                                            @endif

                                            @if($variant->status === \App\Enums\PostStatus::PUBLISHED && $variant->external_post_url)
                                                <div class="small mb-1">
                                                    <a href="{{ $variant->external_post_url }}" target="_blank" class="text-success text-decoration-none">
                                                        <i class="bi bi-box-arrow-up-right me-1"></i> View Live Post on {{ $variant->socialProfile?->platform?->label() }}
                                                    </a>
                                                    <span class="text-muted ms-2">(ID: {{ $variant->external_post_id }})</span>
                                                </div>
                                            @endif

                                            @if($variant->status === \App\Enums\PostStatus::FAILED && $variant->error_message)
                                                <div class="alert alert-danger p-3 mb-2 rounded-3 border-danger shadow-xs">
                                                    <div class="d-flex align-items-start gap-2">
                                                        <i class="bi bi-exclamation-octagon-fill fs-5 text-danger flex-shrink-0 mt-1"></i>
                                                        <div>
                                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                                <h6 class="fw-bold mb-0 text-danger small">Publishing Failed</h6>
                                                                @if($variant->retry_count > 0)
                                                                    <span class="badge bg-danger">Attempts: {{ $variant->retry_count }}</span>
                                                                @endif
                                                            </div>
                                                            <div class="small text-dark mb-2">
                                                                {{ $variant->error_message }}
                                                            </div>
                                                            @if(str_contains($variant->error_message, '200') || str_contains(strtolower($variant->error_message), 'permission'))
                                                                <div class="p-2 bg-white bg-opacity-75 rounded border border-danger-subtle small mt-1">
                                                                    <strong class="d-block text-dark mb-1"><i class="bi bi-lightbulb-fill text-warning me-1"></i> How to fix:</strong>
                                                                    <ol class="mb-0 ps-3 text-secondary" style="font-size: 0.75rem;">
                                                                        <li>Open <a href="https://developers.facebook.com/apps" target="_blank" class="fw-bold text-decoration-underline text-danger">developers.facebook.com</a> & select your App.</li>
                                                                        <li>Go to <strong>Use Cases ➡️ Manage and access Pages</strong> & add <strong>pages_manage_posts</strong>.</li>
                                                                        <li>Go to <strong>App Roles ➡️ Roles</strong> and ensure your Facebook profile is listed as <strong>Admin</strong> or <strong>Developer</strong>.</li>
                                                                        <li>Go to <a href="{{ route('social-accounts.index') }}" class="fw-bold text-decoration-underline text-primary">Social Accounts</a> and click <strong>Reconnect</strong>.</li>
                                                                    </ol>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Publishing Record & Attempts Log -->
                                            @if($variant->publishingRecord && $variant->publishingRecord->attempts->isNotEmpty())
                                                <div class="mt-2 pt-2 border-top">
                                                    <small class="fw-bold text-muted d-block mb-1">Execution Attempts Log:</small>
                                                    @foreach($variant->publishingRecord->attempts as $attempt)
                                                        <div class="d-flex justify-content-between small text-muted py-1 border-bottom border-light">
                                                            <span>
                                                                Attempt #{{ $attempt->attempt_number }}
                                                                <span class="badge {{ $attempt->response_status_code === 200 ? 'bg-success' : 'bg-danger' }} ms-1">
                                                                    {{ $attempt->response_status_code ?? 'Err' }}
                                                                </span>
                                                            </span>
                                                            <span>{{ $attempt->duration_ms }}ms</span>
                                                            <span>{{ $attempt->created_at->format('H:i:s') }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top py-2">
                        <button type="button" class="btn btn-sm btn-light" wire:click="closeDetailModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

