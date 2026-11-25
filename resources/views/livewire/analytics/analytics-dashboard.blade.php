<div class="analytics-recur-layout">
    <style>
        .recur-wrapper {
            display: flex;
            gap: 1.5rem;
            min-height: calc(100vh - 120px);
        }

        /* Middle Sub-Sidebar (Social Profiles) */
        .recur-profiles-col {
            width: 310px;
            flex-shrink: 0;
            background: #ffffff;
            border-radius: 18px;
            border: 1px solid #eef2f6;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            padding: 1.25rem 1rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .recur-menu-item {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.75rem 0.85rem;
            border-radius: 12px;
            cursor: pointer;
            text-decoration: none;
            color: #334155;
            transition: all 0.15s ease;
            border: 1px solid transparent;
        }

        .recur-menu-item:hover {
            background: #f8fafc;
            color: #0f172a;
        }

        .recur-menu-item.active {
            background: #f0f7ff;
            border-color: #dbeafe;
            color: #1e40af;
            font-weight: 600;
        }

        .recur-menu-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .recur-profile-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem;
            border-radius: 14px;
            border: 1px solid #f1f5f9;
            background: #ffffff;
            cursor: pointer;
            transition: all 0.15s ease;
            margin-bottom: 0.5rem;
        }

        .recur-profile-card:hover {
            background: #f8fafc;
            border-color: #e2e8f0;
        }

        .recur-profile-card.selected {
            border-color: #2563eb;
            background: #ffffff;
            box-shadow: 0 0 0 1px #2563eb, 0 4px 12px rgba(37, 99, 235, 0.08);
        }

        .profile-avatar-box {
            position: relative;
            width: 38px;
            height: 38px;
            flex-shrink: 0;
        }

        .profile-avatar-img {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            object-fit: cover;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #475569;
            font-size: 0.9rem;
        }

        .profile-platform-badge {
            position: absolute;
            bottom: -3px;
            right: -3px;
            width: 17px;
            height: 17px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.65rem;
            border: 2px solid #ffffff;
            color: #ffffff;
        }

        /* Right Content Area */
        .recur-content-col {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        /* Profile Header Banner */
        .recur-banner-card {
            background: #ffffff;
            border-radius: 18px;
            border: 1px solid #eef2f6;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        /* Metric Cards */
        .recur-metric-box {
            background: #ffffff;
            border-radius: 18px;
            border: 1px solid #eef2f6;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
            padding: 1.35rem 1.5rem;
            height: 100%;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .recur-metric-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
        }

        .metric-icon-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.82rem;
            color: #64748b;
            font-weight: 500;
        }

        .metric-big-num {
            font-size: 2rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
            margin-top: 0.6rem;
            margin-bottom: 0.4rem;
        }

        .delta-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.78rem;
            font-weight: 600;
            color: #10b981;
        }

        .delta-tag.neutral {
            color: #64748b;
        }

        .btn-recur-pill {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: #1e293b;
            font-weight: 600;
            font-size: 0.85rem;
            border-radius: 50rem;
            padding: 0.5rem 1.15rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.15s ease;
        }

        .btn-recur-pill:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #0f172a;
        }

        .btn-recur-primary {
            background: #2563eb;
            border: 1px solid #2563eb;
            color: #ffffff;
            font-weight: 600;
            font-size: 0.85rem;
            border-radius: 50rem;
            padding: 0.5rem 1.25rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
            transition: all 0.15s ease;
        }

        .btn-recur-primary:hover {
            background: #1d4ed8;
            color: #ffffff;
        }

        @media (max-width: 991.98px) {
            .recur-wrapper {
                flex-direction: column;
            }
            .recur-profiles-col {
                width: 100%;
            }
        }
    </style>

    <!-- Top Trial Notification Banner (Matching Reference) -->


    <!-- Flash message -->
    @if($feedbackMessage)
        <div class="alert alert-{{ $feedbackType }} alert-dismissible fade show mb-4 py-2 d-flex align-items-center" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>
            <div>{{ $feedbackMessage }}</div>
            <button type="button" class="btn-close py-2" wire:click="$set('feedbackMessage', null)"></button>
        </div>
    @endif

    <div class="recur-wrapper">
        <!-- ============================================== -->
        <!-- MIDDLE COLUMN: SOCIAL PROFILES SUB-SIDEBAR     -->
        <!-- ============================================== -->
        <aside class="recur-profiles-col">
            <div class="d-flex align-items-center justify-content-between pb-1">
                <h6 class="fw-bold mb-0 text-dark">Social Profiles</h6>
                <button class="btn btn-sm btn-link text-muted p-0" title="Collapse Pane">
                    <i class="bi bi-chevron-left"></i>
                </button>
            </div>

            <!-- Top Filter Category Items -->
            <div class="d-flex flex-column gap-1">
                <div wire:click="setViewMode('overview')" class="recur-menu-item {{ $viewMode === 'overview' ? 'active' : '' }}">
                    <div class="recur-menu-icon" style="background: #eef2ff; color: #4f46e5;">
                        <i class="bi bi-grid-fill"></i>
                    </div>
                    <div>
                        <div class="small fw-bold">Overview</div>
                        <div class="text-muted" style="font-size: 0.74rem;">All social profiles</div>
                    </div>
                </div>

                <div wire:click="setViewMode('custom_reports')" class="recur-menu-item {{ $viewMode === 'custom_reports' ? 'active' : '' }}">
                    <div class="recur-menu-icon" style="background: #eff6ff; color: #2563eb;">
                        <i class="bi bi-sliders"></i>
                    </div>
                    <div>
                        <div class="small fw-bold">Custom Reports</div>
                        <div class="text-muted" style="font-size: 0.74rem;">Build your own report</div>
                    </div>
                </div>
            </div>

            <!-- Search Profile Input -->
            <div class="position-relative">
                <input type="text" wire:model.live.debounce.300ms="profileSearch" class="form-control form-control-sm rounded-pill px-3 py-2 border bg-light" placeholder="Search profiles...">
                <i class="bi bi-search position-absolute text-muted" style="right: 14px; top: 9px; font-size: 0.8rem;"></i>
            </div>

            <!-- Profile List Items -->
            <div class="overflow-y-auto pe-1" style="max-height: 520px;">
                @forelse($allProfiles as $prof)
                    @php
                        $isSelected = ($selectedProfileId === $prof->id) && ($viewMode === 'profile');
                        $badgeBg = match($prof->platform->value) {
                            'facebook' => '#1877f2',
                            'instagram' => '#e1306c',
                            'youtube' => '#ff0000',
                            'linkedin' => '#0a66c2',
                            'google_business' => '#4285f4',
                            default => '#6366f1',
                        };
                    @endphp
                    <div wire:click="selectProfile({{ $prof->id }})" class="recur-profile-card {{ $isSelected ? 'selected' : '' }}">
                        <div class="d-flex align-items-center gap-2 text-truncate">
                            <div class="profile-avatar-box">
                                @if($prof->avatar_url)
                                    <img src="{{ $prof->avatar_url }}" class="profile-avatar-img" alt="">
                                @else
                                    <div class="profile-avatar-img">
                                        {{ strtoupper(substr($prof->name, 0, 1)) }}
                                    </div>
                                @endif
                                <span class="profile-platform-badge" style="background: {{ $badgeBg }};">
                                    <i class="bi {{ $prof->platform->icon() }}"></i>
                                </span>
                            </div>
                            <div class="text-truncate">
                                <div class="text-dark small fw-bold text-truncate">{{ $prof->name }}</div>
                                <div class="text-muted text-truncate" style="font-size: 0.74rem;">
                                    {{ $prof->platform->label() }}
                                </div>
                            </div>
                        </div>

                        @if($isSelected)
                            <div class="text-primary fs-5 ms-2">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-4 text-muted small">
                        No connected profiles found.
                    </div>
                @endforelse
            </div>
        </aside>

        <!-- ============================================== -->
        <!-- RIGHT COLUMN: MAIN REPORTS & ANALYTICS PANE    -->
        <!-- ============================================== -->
        <main class="recur-content-col">
            <!-- Header bar with Title and Organic selector -->
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-4 text-primary"><i class="bi bi-file-earmark-bar-graph-fill"></i></span>
                    <h4 class="fw-bold mb-0 text-dark">Analytics & Reports</h4>
                </div>
                <div class="dropdown">
                    <button class="btn btn-sm btn-recur-pill dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="pulse-live me-1"></span> Organic
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2 rounded-4">
                        <li><a class="dropdown-item small py-2 rounded active" href="#">Organic Traffic</a></li>
                        <li><a class="dropdown-item small py-2 rounded" href="#">Paid Advertising</a></li>
                        <li><a class="dropdown-item small py-2 rounded" href="#">Combined Reach</a></li>
                    </ul>
                </div>
            </div>

            <!-- Dynamic Content (Hidden during loading) -->
            <div wire:loading.remove wire:target="selectProfile, setViewMode, dateRange, trafficType, profileSearch" class="d-flex flex-column gap-4">
                <!-- Profile Banner Card (Matching Screenshot exactly) -->
                <div class="recur-banner-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="profile-avatar-box" style="width: 48px; height: 48px;">
                        @if($selectedProfile && $selectedProfile->avatar_url)
                            <img src="{{ $selectedProfile->avatar_url }}" class="profile-avatar-img" style="width: 48px; height: 48px; border-radius: 12px;" alt="">
                        @else
                            <div class="profile-avatar-img" style="width: 48px; height: 48px; border-radius: 12px; background: #fedde5; color: #be185d; font-size: 1.1rem;">
                                {{ strtoupper(substr($selectedProfile->name ?? 'A', 0, 1)) }}
                            </div>
                        @endif
                        <span class="profile-platform-badge" style="width: 20px; height: 20px; font-size: 0.75rem; background: #1877f2;">
                            <i class="bi {{ $selectedProfile?->platform?->icon() ?? 'bi-share' }}"></i>
                        </span>
                    </div>

                    <div>
                        <h5 class="fw-bold text-dark mb-1">{{ $selectedProfile->name ?? 'All Social Profiles Combined' }}</h5>
                        <div class="d-flex align-items-center gap-1 text-primary small fw-semibold">
                            <i class="bi bi-check-circle-fill" style="font-size: 0.8rem;"></i>
                            <span>{{ $selectedProfile?->platform?->label() ?? 'Multi-Channel' }}</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <!-- Date Range Pill Button with comparison label -->
                    <div class="btn-recur-pill" style="font-size: 0.78rem; padding: 0.45rem 1rem;">
                        <i class="bi bi-calendar3 text-muted"></i>
                        <span>{{ $dateLabel }}</span>
                        <span class="text-muted ms-1" style="font-size: 0.72rem;">vs {{ $prevDateLabel }}</span>
                    </div>

                    <!-- Chat with AI Button -->
                    <button wire:click="openAiInsights" class="btn btn-recur-pill">
                        <i class="bi bi-chat-dots-fill text-primary"></i>
                        <span>Chat with AI</span>
                    </button>

                    <!-- Share / Export Button -->
                    <button wire:click="openShareModal" class="btn btn-recur-primary">
                        <i class="bi bi-share-fill"></i>
                        <span>Share</span>
                    </button>
                </div>
            </div>

            <!-- SECTION 1: Profile Overview -->
            <div>
                <div class="mb-3">
                    <h6 class="fw-bold text-dark mb-0">Profile Overview</h6>
                    <small class="text-muted">Live stats for your {{ $selectedProfile?->platform?->label() ?? 'social profiles' }}.</small>
                </div>

                <div class="row g-3">
                    <!-- Total Fans -->
                    <div class="col-md-6 col-lg-4">
                        <div class="recur-metric-box">
                            <div class="metric-icon-pill">
                                <span class="rounded-circle d-inline-flex align-items-center justify-content-center p-1" style="background: #fdf2f8; color: #ec4899; width: 26px; height: 26px;">
                                    <i class="bi bi-person"></i>
                                </span>
                                <span>Total Fans</span>
                            </div>
                            <div class="metric-big-num">{{ number_format($metrics['totalFans']) }}</div>
                        </div>
                    </div>

                    <!-- Talking About This -->
                    <div class="col-md-6 col-lg-4">
                        <div class="recur-metric-box">
                            <div class="metric-icon-pill">
                                <span class="rounded-circle d-inline-flex align-items-center justify-content-center p-1" style="background: #ecfeff; color: #06b6d4; width: 26px; height: 26px;">
                                    <i class="bi bi-chat-left-text"></i>
                                </span>
                                <span>Talking About This</span>
                            </div>
                            <div class="metric-big-num">{{ number_format($metrics['talkingAboutThis']) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: Performance Snapshot -->
            <div>
                <div class="mb-3">
                    <h6 class="fw-bold text-dark mb-0">Performance Snapshot</h6>
                    <small class="text-muted">A quick look at your key metrics compared to the previous period, showing what's improving and what's slipping.</small>
                </div>

                <div class="row g-3">
                    <!-- 1. Total Follows -->
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="recur-metric-box">
                            <div class="metric-icon-pill">
                                <span class="rounded-circle d-inline-flex align-items-center justify-content-center p-1" style="background: #f5f3ff; color: #8b5cf6; width: 26px; height: 26px;">
                                    <i class="bi bi-person-check"></i>
                                </span>
                                <span>Total Follows</span>
                            </div>
                            <div class="metric-big-num">{{ number_format($metrics['totalFollows']) }}</div>
                            <span class="delta-tag neutral">
                                <i class="bi bi-arrow-up-short"></i> 0.00%
                            </span>
                        </div>
                    </div>

                    <!-- 2. Total Views -->
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="recur-metric-box">
                            <div class="metric-icon-pill">
                                <span class="rounded-circle d-inline-flex align-items-center justify-content-center p-1" style="background: #ecfeff; color: #06b6d4; width: 26px; height: 26px;">
                                    <i class="bi bi-eye"></i>
                                </span>
                                <span>Total Impressions</span>
                            </div>
                            <div class="metric-big-num">{{ number_format($metrics['totalViews']) }}</div>
                            <span class="delta-tag">
                                <i class="bi bi-arrow-up-short"></i> {{ $metrics['viewsDelta'] }}
                            </span>
                        </div>
                    </div>

                    <!-- 3. Engagement -->
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="recur-metric-box">
                            <div class="metric-icon-pill">
                                <span class="rounded-circle d-inline-flex align-items-center justify-content-center p-1" style="background: #ecfdf5; color: #10b981; width: 26px; height: 26px;">
                                    <i class="bi bi-heart"></i>
                                </span>
                                <span>Engagement</span>
                            </div>
                            <div class="metric-big-num">{{ number_format($metrics['engagement']) }}</div>
                            <span class="delta-tag neutral">
                                <i class="bi bi-arrow-up-short"></i> Rate: {{ $metrics['engagementRate'] }}%
                            </span>
                        </div>
                    </div>

                    <!-- 4. Content Interactions -->
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="recur-metric-box">
                            <div class="metric-icon-pill">
                                <span class="rounded-circle d-inline-flex align-items-center justify-content-center p-1" style="background: #fffbeb; color: #f59e0b; width: 26px; height: 26px;">
                                    <i class="bi bi-hand-thumbs-up"></i>
                                </span>
                                <span>Content Interactions</span>
                            </div>
                            <div class="metric-big-num">{{ number_format($metrics['contentInteractions']) }}</div>
                            <span class="delta-tag neutral">
                                <i class="bi bi-arrow-up-short"></i> 0.00%
                            </span>
                        </div>
                    </div>

                    <!-- 5. Published Posts -->
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="recur-metric-box">
                            <div class="metric-icon-pill">
                                <span class="rounded-circle d-inline-flex align-items-center justify-content-center p-1" style="background: #fef2f2; color: #ef4444; width: 26px; height: 26px;">
                                    <i class="bi bi-share"></i>
                                </span>
                                <span>Published Posts</span>
                            </div>
                            <div class="metric-big-num">{{ number_format($metrics['publishedPosts']) }}</div>
                            <span class="delta-tag neutral">
                                <i class="bi bi-arrow-up-short"></i> 0.00%
                            </span>
                        </div>
                    </div>

                    <!-- 6. Page Views -->
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="recur-metric-box">
                            <div class="metric-icon-pill">
                                <span class="rounded-circle d-inline-flex align-items-center justify-content-center p-1" style="background: #ecfeff; color: #0891b2; width: 26px; height: 26px;">
                                    <i class="bi bi-display"></i>
                                </span>
                                <span>Page Views / Reach</span>
                            </div>
                            <div class="metric-big-num">{{ number_format($metrics['pageViews']) }}</div>
                            <span class="delta-tag">
                                <i class="bi bi-arrow-up-short"></i> {{ $metrics['pageViewsDelta'] }}
                            </span>
                        </div>
                    </div>

                    <!-- 7. Reactions -->
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="recur-metric-box">
                            <div class="metric-icon-pill">
                                <span class="rounded-circle d-inline-flex align-items-center justify-content-center p-1" style="background: #fdf2f8; color: #db2777; width: 26px; height: 26px;">
                                    <i class="bi bi-emoji-smile"></i>
                                </span>
                                <span>Reactions</span>
                            </div>
                            <div class="metric-big-num">{{ number_format($metrics['reactions']) }}</div>
                            <span class="delta-tag neutral">
                                <i class="bi bi-arrow-up-short"></i> 0.00%
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: Recent Content Performance -->
            <div class="recur-metric-box p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h6 class="fw-bold text-dark mb-0">Top Published Content</h6>
                        <small class="text-muted">Posts published to this channel during the current date period.</small>
                    </div>
                    <a href="{{ route('posts.index') }}" class="btn btn-sm btn-link text-primary text-decoration-none">View All Posts ↗</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="small text-muted text-uppercase" style="border-bottom: 1px solid #f1f5f9;">
                            <tr>
                                <th style="width: 320px;">Content</th>
                                <th>Published Date</th>
                                <th>Impressions</th>
                                <th>Reactions</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topVariants as $post)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if($post->mediaAssets->isNotEmpty())
                                                <img src="{{ $post->mediaAssets->first()->public_url }}" class="rounded" style="width: 40px; height: 40px; object-fit: cover;" alt="">
                                            @else
                                                <div class="rounded d-flex align-items-center justify-content-center bg-light text-muted" style="width: 40px; height: 40px;">
                                                    <i class="bi bi-card-text"></i>
                                                </div>
                                            @endif
                                            <div class="text-truncate" style="max-width: 250px;">
                                                <div class="fw-semibold text-dark text-truncate small">{{ $post->getEffectiveCaption() ?: 'Social Media Post' }}</div>
                                                <small class="text-muted">{{ $post->socialProfile?->platform?->label() ?? 'Social' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-dark">{{ $post->published_at?->format('M d, Y h:i A') ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ number_format($post->analyticsSnapshots->where('metric_name', 'impressions')->first()?->metric_value ?? 1) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ number_format($post->analyticsSnapshots->where('metric_name', 'likes')->first()?->metric_value ?? 0) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        @if($post->external_post_url)
                                            <a href="{{ $post->external_post_url }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-2">
                                                <i class="bi bi-box-arrow-up-right"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted small">
                                        No published posts found in this period.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Skeleton Loading State (Shown during profile/filter updates) -->
        <div wire:loading.block wire:target="selectProfile, setViewMode, dateRange, trafficType, profileSearch" class="w-100" style="display: none;">
            <div class="d-flex flex-column gap-4">
                <!-- Skeleton Banner -->
                <div class="recur-banner-card">
                    <div class="d-flex align-items-center gap-3">
                        <div class="skeleton-box" style="width: 48px; height: 48px; border-radius: 12px;"></div>
                        <div>
                            <div class="skeleton-box mb-2" style="width: 200px; height: 20px;"></div>
                            <div class="skeleton-box" style="width: 110px; height: 14px;"></div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="skeleton-box skeleton-pill" style="width: 160px; height: 36px;"></div>
                        <div class="skeleton-box skeleton-pill" style="width: 110px; height: 36px;"></div>
                        <div class="skeleton-box skeleton-pill" style="width: 90px; height: 36px;"></div>
                    </div>
                </div>

                <!-- Skeleton Profile Overview -->
                <div>
                    <div class="skeleton-box mb-3" style="width: 140px; height: 18px;"></div>
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-4">
                            <div class="recur-metric-box">
                                <div class="skeleton-box mb-3" style="width: 100px; height: 24px; border-radius: 9999px;"></div>
                                <div class="skeleton-box" style="width: 120px; height: 32px;"></div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="recur-metric-box">
                                <div class="skeleton-box mb-3" style="width: 130px; height: 24px; border-radius: 9999px;"></div>
                                <div class="skeleton-box" style="width: 80px; height: 32px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Skeleton Performance Snapshot -->
                <div>
                    <div class="skeleton-box mb-3" style="width: 180px; height: 18px;"></div>
                    <div class="row g-3">
                        @for($i = 0; $i < 7; $i++)
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <div class="recur-metric-box">
                                    <div class="skeleton-box mb-3" style="width: 120px; height: 24px; border-radius: 9999px;"></div>
                                    <div class="skeleton-box mb-2" style="width: 80px; height: 28px;"></div>
                                    <div class="skeleton-box" style="width: 65px; height: 18px; border-radius: 6px;"></div>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>

                <!-- Skeleton Content Table -->
                <div class="recur-metric-box p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="skeleton-box" style="width: 180px; height: 20px;"></div>
                        <div class="skeleton-box" style="width: 100px; height: 16px;"></div>
                    </div>
                    @for($i = 0; $i < 4; $i++)
                        <div class="skeleton-table-row">
                            <div class="skeleton-box" style="width: 40px; height: 40px; border-radius: 8px;"></div>
                            <div class="flex-grow-1">
                                <div class="skeleton-box mb-1" style="width: 55%; height: 14px;"></div>
                                <div class="skeleton-box" style="width: 30%; height: 12px;"></div>
                            </div>
                            <div class="skeleton-box" style="width: 90px; height: 16px;"></div>
                            <div class="skeleton-box" style="width: 60px; height: 16px;"></div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
        </main>
    </div>

    <!-- ============================================== -->
    <!-- MODAL 1: CHAT WITH AI INSIGHTS                 -->
    <!-- ============================================== -->
    @if($showAiChatModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header border-bottom py-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded-circle p-2 bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-robot fs-5"></i>
                            </span>
                            <div>
                                <h6 class="modal-title fw-bold text-dark mb-0">AI Performance Intelligence</h6>
                                <small class="text-muted">Instant analytical insights for {{ $selectedProfile->name ?? 'Social Profiles' }}</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeAiInsights"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="p-3 rounded-4 mb-3" style="background: #f8fafc; border: 1px solid #e2e8f0; font-size: 0.92rem; line-height: 1.6;">
                            {!! nl2br(e($aiAnalysisText)) !!}
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <input type="text" class="form-control rounded-pill px-3" placeholder="Ask AI a question about your engagement or reach...">
                            <button class="btn btn-primary rounded-pill px-4">Ask</button>
                        </div>
                    </div>

                    <div class="modal-footer border-top py-2">
                        <button type="button" class="btn btn-sm btn-light rounded-pill px-3" wire:click="closeAiInsights">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL 2: SHARE / EXPORT REPORT                 -->
    <!-- ============================================== -->
    @if($showShareModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header border-bottom py-3">
                        <h6 class="modal-title fw-bold text-dark mb-0">
                            <i class="bi bi-share-fill text-primary me-2"></i> Share & Download Performance Report
                        </h6>
                        <button type="button" class="btn-close" wire:click="closeShareModal"></button>
                    </div>

                    <div class="modal-body p-4">
                        <p class="small text-muted mb-4">Export or share client-ready social analytics reports with your agency branding.</p>

                        <div class="d-grid gap-3">
                            <button wire:click="exportReport('pdf')" class="btn btn-outline-primary text-start p-3 rounded-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-bold text-dark">Executive PDF Summary</div>
                                    <small class="text-muted">High-res, print-ready document with KPI breakdown and top posts.</small>
                                </div>
                                <i class="bi bi-file-earmark-pdf fs-3 text-danger"></i>
                            </button>

                            <button wire:click="exportReport('csv')" class="btn btn-outline-secondary text-start p-3 rounded-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-bold text-dark">Raw CSV Dataset Export</div>
                                    <small class="text-muted">Complete tabular numbers and daily metrics for spreadsheets.</small>
                                </div>
                                <i class="bi bi-file-earmark-spreadsheet fs-3 text-success"></i>
                            </button>
                        </div>
                    </div>

                    <div class="modal-footer border-top py-2">
                        <button type="button" class="btn btn-sm btn-light rounded-pill px-3" wire:click="closeShareModal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
