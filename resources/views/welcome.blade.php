@extends('layouts.public', ['title' => 'PostSync SaaS — Enterprise Social Media Management Platform'])

@section('content')
@php
    $workflowSteps = [
        [
            'num' => '01',
            'label' => 'Connect',
            'badge' => 'OFFICIAL OAUTH 2.0',
            'title' => 'No shared passwords — direct OAuth links your clients trust.',
            'metric' => 'Setup per client takes under 2 minutes',
            'row1Title' => 'Self-Serve Client Connect Links',
            'row1Body' => 'Generate an expiring onboarding link for any client brand. They authorize Facebook, Instagram, YouTube, or LinkedIn directly — zero passwords exchanged over WhatsApp.',
            'row2Title' => 'Isolated Agency Workspaces',
            'row2Body' => 'Keep media libraries, teams, billing, and social tokens scoped cleanly to each workspace with AES-256-GCM encryption at rest.',
            'uiTag' => 'OAUTH CONNECTION FLOW',
            'uiHeading' => 'Client Onboarding Link Ready',
            'uiItems' => [
                ['Meta Pages & IG Pro', 'Authorized via Official Graph API', 'Connected'],
                ['YouTube Channel', 'youtube.upload + readonly scopes active', 'Synced'],
                ['LinkedIn Company', 'Posts API v2 token encrypted', 'Verified'],
            ],
        ],
        [
            'num' => '02',
            'label' => 'Composer',
            'badge' => '4-STEP STUDIO COMPOSER',
            'title' => 'Draft once, adapt format rules per social network automatically.',
            'metric' => 'Publish standard posts, Reels, Shorts & Carousels',
            'row1Title' => 'Channel-Specific Media Overrides',
            'row1Body' => 'Customize captions, hashtags, YouTube titles & custom thumbnails, Instagram Stories, and first comments per platform inside one unified studio.',
            'row2Title' => 'AI Copy & Brand Voice Assist',
            'row2Body' => 'Tighten hooks, generate platform variants, and reuse approved workspace media without switching tabs or copy-pasting spreadsheets.',
            'uiTag' => 'COMPOSER PREVIEW',
            'uiHeading' => 'Q4 Launch Campaign • 4 Targets',
            'uiItems' => [
                ['Instagram Reels', '9:16 video + trending caption override', 'Ready'],
                ['YouTube Shorts', 'Title + custom thumbnail attached', 'Ready'],
                ['LinkedIn & X', 'Executive announcement + link preview', 'Queued'],
            ],
        ],
        [
            'num' => '03',
            'label' => 'Approvals',
            'badge' => 'CLIENT PORTAL & SIGN-OFF',
            'title' => 'Zero WhatsApp screenshots — structured client review in one click.',
            'metric' => 'Clients log in passwordlessly via 1-time Gmail OTP',
            'row1Title' => 'Friction-Free Client Access',
            'row1Body' => 'Clients sign in using only a 6-digit Gmail verification code and immediately see only their assigned campaigns, calendar, approvals, and reports.',
            'row2Title' => 'Threaded Revisions & Audit Trail',
            'row2Body' => 'Clients click Approve or Request Changes with inline notes; rejected drafts route right back to creators before anything can go live.',
            'uiTag' => 'APPROVAL QUEUE',
            'uiHeading' => 'Brand: Kaizen Studio (GAJE-01)',
            'uiItems' => [
                ['Product Teaser Carousel', 'Approved by client@kaizengroup', 'Approved'],
                ['Founder Behind-the-Scenes', 'Revision requested on opening hook', 'In Review'],
                ['Weekend Promo Story', 'Scheduled automatically upon approval', 'Scheduled'],
            ],
        ],
        [
            'num' => '04',
            'label' => 'Calendar',
            'badge' => 'INTERACTIVE QUEUE',
            'title' => 'Visual scheduling that adapts cleanly across every brand timezone.',
            'metric' => 'Timezone-accurate queues with retry protection',
            'row1Title' => 'Live Month & Week Grid',
            'row1Body' => 'Spot content gaps at a glance, filter by client or platform, reschedule slots cleanly, and monitor queued vs published statuses live.',
            'row2Title' => 'Queue & Rate-Limit Safety',
            'row2Body' => 'Background worker pipelines honor platform media processing delays (such as IG containers & YouTube uploads) with idempotent retries.',
            'uiTag' => 'DISPATCH SCHEDULE',
            'uiHeading' => 'Asia/Kolkata • Active Queue',
            'uiItems' => [
                ['Today, 6:30 PM', 'FB Page + IG Pro • Product Reel', 'Queued'],
                ['Tomorrow, 11:00 AM', 'YouTube Channel • Tutorial Upload', 'Scheduled'],
                ['Fri, 9:15 AM', 'LinkedIn + X • Industry Case Study', 'Drafted'],
            ],
        ],
        [
            'num' => '05',
            'label' => 'Inbox & Reports',
            'badge' => 'ENGAGEMENT & WHITE-LABEL ROI',
            'title' => 'Reply to comments and ship client-ready reports in minutes.',
            'metric' => 'Export branded PDF & CSV performance decks',
            'row1Title' => 'Unified Social Inbox',
            'row1Body' => 'Monitor and reply to Facebook, Instagram, and YouTube comments across client brands from one clean conversational stream.',
            'row2Title' => 'Automated Client Reporting',
            'row2Body' => 'Aggregate impressions, reach, engagement, and top-performing posts into crisp branded PDF & CSV summaries clients actually read.',
            'uiTag' => 'EXECUTIVE SUMMARY',
            'uiHeading' => 'Monthly Client Performance',
            'uiItems' => [
                ['Total Reach', '284.6K across 6 active channels', '+32%'],
                ['Inbox Resolution', '98% comments replied under 2 hrs', 'SLA Met'],
                ['Executive PDF Export', 'White-label report ready for client download', 'Generated'],
            ],
        ],
    ];

    $edgeCases = [
        [
            'key' => 'approvals',
            'label' => 'Client Approvals',
            'todayTitle' => 'Scattered PDFs, Google Docs & WhatsApp screenshots',
            'todayBody' => 'Agencies export slide decks every Friday, clients reply asynchronously in long chat threads, and copy edits get missed before scheduling.',
            'withTitle' => 'Live Campaign Portal + Gmail OTP',
            'withBody' => 'Clients receive an instant workspace invite, verify with a 1-time Gmail code, preview exact post media, and approve or comment with full revision history.',
        ],
        [
            'key' => 'oauth',
            'label' => 'OAuth Security',
            'todayTitle' => 'Shared spreadsheets full of raw client passwords',
            'todayBody' => '2FA codes lock out social managers whenever team members change, violating platform policies and breaking client trust.',
            'withTitle' => '1-Click Expiring Client Connect Links',
            'withBody' => 'Share a secure onboarding link; clients connect Meta, YouTube, LinkedIn, X, or TikTok directly via official OAuth consent while tokens stay AES-256-GCM encrypted.',
        ],
        [
            'key' => 'video',
            'label' => 'YouTube & Reels',
            'todayTitle' => 'Uploading large MP4s 5 separate times across tabs',
            'todayBody' => 'Social managers upload videos one platform at a time, losing custom thumbnails, Shorts metadata, and consistent release timing.',
            'withTitle' => 'Single Upload + Per-Channel Metadata',
            'withBody' => 'Attach media once in the 4-Step Composer, override YouTube titles, descriptions, tags & custom thumbnails, and schedule Reels alongside Shorts cleanly.',
        ],
        [
            'key' => 'workspaces',
            'label' => 'Multi-Brand Scoping',
            'todayTitle' => 'Accidental cross-posting onto the wrong brand account',
            'todayBody' => 'Switching between browser profiles or agency seats in a single flat tool leads to embarrassing brand mix-ups.',
            'withTitle' => 'Strict Client & Team Assignment',
            'withBody' => 'Each client brand owns dedicated channels, media, posts, and team assignments — so external clients never glimpse another brand or agency billing.',
        ],
        [
            'key' => 'inbox',
            'label' => 'Unified Inbox',
            'todayTitle' => 'Missing urgent buyer questions across 10 open tabs',
            'todayBody' => 'Comments on boosted Reels, Page updates, and YouTube launches sit unanswered for days while managers hop between apps.',
            'withTitle' => 'Real-Time Cross-Network Comment Stream',
            'withBody' => 'Sync comments into one searchable inbox filtered by client workspace and status, and reply immediately from PostSync.',
        ],
        [
            'key' => 'reports',
            'label' => 'Client Reports',
            'todayTitle' => 'Hours burned screenshotting charts on the 1st of the month',
            'todayBody' => 'Account managers manually pull CSVs from 6 native apps and paste screenshots into slide templates every billing cycle.',
            'withTitle' => 'One-Click Branded PDF & CSV Exports',
            'withBody' => 'Present live engagement metrics inside the client portal and download clean executive PDF and spreadsheet summaries anytime.',
        ],
    ];

    $platforms = [
        ['name' => 'Facebook Pages', 'icon' => 'bi-facebook', 'class' => 'facebook', 'desc' => 'Pages feed, images, videos & Reels via Meta Graph API.'],
        ['name' => 'Instagram Pro', 'icon' => 'bi-instagram', 'class' => 'instagram', 'desc' => 'Business single posts, carousels, Stories & Reels.'],
        ['name' => 'YouTube', 'icon' => 'bi-youtube', 'class' => 'youtube', 'desc' => 'Resumable standard & Shorts uploads with thumbnails.'],
        ['name' => 'LinkedIn', 'icon' => 'bi-linkedin', 'class' => 'linkedin', 'desc' => 'Company Pages & personal updates via official Posts API.'],
        ['name' => 'Pinterest', 'icon' => 'bi-pin-angle-fill', 'class' => 'pinterest', 'desc' => 'Rich Pins, videos & destination links straight to boards.'],
        ['name' => 'Google Business', 'icon' => 'bi-google', 'class' => 'google_business', 'desc' => 'Local updates, offers & CTAs on Search and Maps.'],
        ['name' => 'TikTok', 'icon' => 'bi-tiktok', 'class' => 'tiktok', 'desc' => 'Official Content Posting API & creator capability sync.'],
        ['name' => 'X (Twitter)', 'icon' => 'bi-twitter-x', 'class' => 'x', 'desc' => 'OAuth 2.0 PKCE publishing & media uploads.'],
        ['name' => 'Threads', 'icon' => 'bi-at', 'class' => 'threads', 'desc' => 'Official Threads Graph API container publishing.'],
        ['name' => 'Bluesky', 'icon' => 'bi-cloud-sun-fill', 'class' => 'bluesky', 'desc' => 'AT Protocol authentication & rich link facets.'],
    ];

    $capabilities = [
        [
            'tag' => 'MULTI-CHANNEL PUBLISHING',
            'title' => 'Write Once, Tailor Per Network',
            'subtitle' => 'From Reels & Shorts to LinkedIn Carousels',
            'body' => 'Our 4-step studio composer keeps core campaign copy unified while allowing per-platform overrides, aspect-ratio media previews, and first-comment triggers.',
            'pillA' => 'Studio Composer',
            'pillB' => '10 APIs Synced',
            'metrics' => ['Custom YT Thumbnails', 'IG Carousel & Story Modes', 'Auto Hashtag Blocks'],
        ],
        [
            'tag' => 'ZERO-FRICTION PORTAL',
            'title' => 'An Interface Clients Actually Use',
            'subtitle' => 'Passwordless Gmail OTP sign-in for every client',
            'body' => 'Invite stakeholders with their email address. Clients log in cleanly via one-time OTP without learning agency tools or risking workspace credentials.',
            'pillA' => 'Client Portal',
            'pillB' => 'No Passwords',
            'metrics' => ['Strict Client Data Scoping', 'Billing Hidden from Clients', '1-Click Post Approval'],
        ],
        [
            'tag' => 'ADAPTIVE CALENDAR',
            'title' => 'Scheduling That Knows Your Timezone',
            'subtitle' => 'Visual clarity across weekly & monthly drops',
            'body' => 'Filter posts by client, status, or platform inside an interactive calendar. Built-in queue workers safeguard against API hiccups with reliable retries.',
            'pillA' => 'Interactive Calendar',
            'pillB' => 'Queue Safety',
            'metrics' => ['Per-Client Timezone Sync', 'Failed Post One-Click Retry', 'Needs-Approval Gate'],
        ],
        [
            'tag' => 'ANALYTICS DATA BACKBONE',
            'title' => 'Performance Proof Without Screenshots',
            'subtitle' => 'Clean engagement tracking across every brand',
            'body' => 'Pull official reach, views, likes, comments, and follower momentum automatically into clean executive dashboards ready for stakeholder calls.',
            'pillA' => 'Live Telemetry',
            'pillB' => 'PDF & CSV Export',
            'metrics' => ['Cross-Platform Rollups', 'White-Label Branded Exports', 'Client Self-Serve Access'],
        ],
        [
            'tag' => 'UNIFIED ENGAGEMENT',
            'title' => 'Your Brand\'s Searchable Comment Stream',
            'subtitle' => 'Keep Meta, Instagram & YouTube threads active',
            'body' => 'Stop tab-hopping to catch customer replies. Moderate comments inside a unified workspace inbox scoped directly to the active brand.',
            'pillA' => 'Unified Inbox',
            'pillB' => 'Thread Sync',
            'metrics' => ['Comment Reply Dispatch', 'Status Triage Filters', 'Workspace Team Routing'],
        ],
        [
            'tag' => 'UNIVERSAL OAUTH CONNECT',
            'title' => 'Direct Integration Into Official APIs',
            'subtitle' => 'Zero brittle scrapers or browser extensions',
            'body' => 'Every channel uses official OAuth 2.0 consent flows or AT Protocol auth with AES-256-GCM token encryption and instant revocation anytime.',
            'pillA' => 'AES-256-GCM',
            'pillB' => 'Revoke Anytime',
            'metrics' => ['Client-Connect Expiring URLs', 'Per-Page Permission Scope', 'Instant Token Purge'],
        ],
    ];
@endphp

<!-- 1. Hero Section -->
<section class="ps-framed-band">
    <div class="ps-shell ps-inner-frame">
        <div class="ps-hero-header">
            <span class="ps-pill">Built for Digital Agencies, Brands &amp; Creators</span>
            <h1 class="ps-hero-h1">
                Your Agency's Next 10 Years of Social Growth<span class="ps-dot">.</span>
            </h1>
        </div>

        <div class="ps-hero-split">
            <div class="ps-hero-copy-col">
                <div>
                    <p class="ps-hero-kicker">
                        Stop managing 10 networks with scattered spreadsheets, screenshots &amp; shared passwords.
                    </p>
                    <p class="ps-hero-lead">
                        One multi-tenant command center to connect client channels via official OAuth, draft campaigns in a 4-step studio composer, route posts through a passwordless Gmail OTP client approval portal, and prove ROI with live analytics.
                    </p>
                </div>

                <div class="d-flex flex-column gap-3">
                    <div class="d-grid d-sm-flex flex-wrap gap-3">
                        <a href="{{ route('register') }}" class="ps-btn-primary">
                            <span>Start 14-Day Free Trial</span>
                            <i class="bi bi-arrow-right" aria-hidden="true"></i>
                        </a>
                        <a href="{{ route('login') }}" class="ps-btn-outline">
                            <i class="bi bi-envelope-check" aria-hidden="true"></i>
                            <span>Sign In with Gmail OTP</span>
                        </a>
                    </div>

                    <div class="d-flex flex-wrap gap-3 pt-1 ps-mono" style="font-size: 0.74rem; color: var(--ps-ink-2);">
                        <span><i class="bi bi-shield-lock-fill text-success me-1"></i>AES-256 Encrypted OAuth</span>
                        <span><i class="bi bi-people-fill text-primary me-1"></i>Passwordless Client Portal</span>
                        <span><i class="bi bi-file-earmark-bar-graph-fill text-danger me-1"></i>Branded PDF Reports</span>
                    </div>
                </div>
            </div>

            <div class="ps-hero-visual-col">
                <div class="ps-dot-surface ps-hero-operator-card">
                    <div>
                        <span class="badge bg-white text-dark border rounded-pill ps-mono mb-2" style="font-size: 0.68rem;">
                            <span class="pulse-online me-1"></span>LIVE STUDIO QUEUE
                        </span>
                        <h2 class="h5 fw-bold mb-1">Agency Workspace • FIS</h2>
                        <p class="small mb-3" style="color: var(--ps-ink-2);">
                            Fresh launch Reels, Shorts &amp; executive carousels synced across client brands with zero password sharing.
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="platform-icon facebook"><i class="bi bi-facebook"></i></span>
                            <span class="platform-icon instagram"><i class="bi bi-instagram"></i></span>
                            <span class="platform-icon youtube"><i class="bi bi-youtube"></i></span>
                            <span class="platform-icon linkedin"><i class="bi bi-linkedin"></i></span>
                            <span class="platform-icon pinterest"><i class="bi bi-pin-angle-fill"></i></span>
                            <span class="platform-icon google_business"><i class="bi bi-google"></i></span>
                        </div>
                    </div>
                    <img
                        src="{{ asset('images/homepage/agency-operator.jpg') }}"
                        alt="Agency strategist reviewing scheduled client posts on a tablet"
                        class="ps-hero-operator-img"
                        width="300"
                        height="310"
                    >
                </div>

                <div class="row g-2">
                    <div class="col-4">
                        <div class="p-3 bg-white border rounded-3 h-100">
                            <div class="ps-mono text-uppercase" style="font-size: 0.68rem; color: var(--ps-ink-3);">Published</div>
                            <div class="fs-4 fw-bold mt-1 ps-display">1,248</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 bg-white border rounded-3 h-100">
                            <div class="ps-mono text-uppercase" style="font-size: 0.68rem; color: var(--ps-ink-3);">Queued</div>
                            <div class="fs-4 fw-bold mt-1 ps-display" style="color: var(--ps-orange);">42</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 bg-white border rounded-3 h-100">
                            <div class="ps-mono text-uppercase" style="font-size: 0.68rem; color: var(--ps-ink-3);">Approvals</div>
                            <div class="fs-4 fw-bold mt-1 ps-display">100%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Architectural Showcase Visual -->
        <div class="pb-4 pb-lg-5">
            <div class="ps-campus-showcase">
                <img
                    src="{{ asset('images/homepage/agency-campus.jpg') }}"
                    alt="PostSync Studio command architecture across multi-brand agencies"
                    width="1320"
                    height="390"
                    loading="eager"
                >
                <div class="ps-campus-caption">
                    <div>
                        <span class="ps-mono text-uppercase d-block mb-1" style="font-size: 0.7rem; color: #ff8c66;">Unified Command Hub</span>
                        <span class="fw-semibold">From a single creative brief to 10 live social networks — draft, approve, publish &amp; measure in one place.</span>
                    </div>
                    <a href="#workflow" class="btn btn-sm btn-light fw-semibold px-3 py-2 flex-shrink-0" style="border-radius: 8px;">
                        Explore 5-Step Flow &darr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 2. Atmospheric Horizon Intro ("Meet PostSync") -->
<section class="ps-horizon-band">
    <img src="{{ asset('images/homepage/sky-horizon.jpg') }}" alt="" class="ps-horizon-bg" aria-hidden="true">
    <div class="ps-shell ps-horizon-content">
        <div class="row g-4 align-items-end">
            <div class="col-lg-8">
                <span class="ps-pill mb-3">Meet PostSync</span>
                <h2 class="ps-meet-title mb-3">
                    Your Multi-Brand Social Engine,<br class="d-none d-md-inline"> Scheduled Overnight<span class="ps-dot">.</span>
                </h2>
                <p class="fs-5 mb-0" style="color: var(--ps-ink-2); max-inline-size: 56ch;">
                    Your channels stay yours. Your clients never share a single password. PostSync connects 10 official social APIs cleanly — replacing scattered docs, broken extensions, and WhatsApp approval chaos.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="p-4 bg-white border rounded-4 text-start shadow-sm">
                    <div class="ps-mono small text-uppercase mb-2" style="color: var(--ps-orange); font-size: 0.72rem;">One Source of Truth</div>
                    <div class="fw-semibold text-dark">Agency team drafts &rarr; Client approves via Gmail OTP &rarr; Workers publish on schedule.</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 3. Interactive 5-Step Workflow Tabs -->
<section id="workflow" class="ps-framed-band is-white py-5">
    <div class="ps-shell py-2 py-lg-4">
        <div class="mb-4">
            <span class="ps-pill mb-2">Interactive Tour</span>
            <h2 class="fw-bold mb-1" style="font-size: clamp(1.75rem, 1.3rem + 1.8vw, 2.75rem);">
                One truth agencies &amp; clients actually trust<span class="ps-dot">.</span>
            </h2>
        </div>

        <div class="ps-workflow-bar mb-3">
            <div class="ps-tab-scroller" role="tablist" aria-label="PostSync workflow steps">
                @foreach ($workflowSteps as $index => $step)
                    <button
                        type="button"
                        class="ps-step-tab"
                        role="tab"
                        id="wf-tab-{{ $index }}"
                        aria-controls="wf-panel-{{ $index }}"
                        aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                        data-step-index="{{ $index }}"
                    >
                        {{ $step['num'] }} {{ $step['label'] }}
                    </button>
                @endforeach
            </div>

            <div class="d-flex align-items-center gap-3 flex-shrink-0">
                <span class="ps-mono fw-semibold" id="wfCounter" style="font-size: 0.85rem;">1/5</span>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="ps-step-nav-btn" id="wfPrev" aria-label="Previous step" disabled>
                        <i class="bi bi-arrow-left" aria-hidden="true"></i>
                    </button>
                    <button type="button" class="ps-step-nav-btn" id="wfNext" aria-label="Next step">
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="ps-progress-track" aria-hidden="true">
            <div class="ps-progress-fill" id="wfProgressBar" style="width: 20%;"></div>
        </div>

        @foreach ($workflowSteps as $index => $step)
            <div
                id="wf-panel-{{ $index }}"
                role="tabpanel"
                aria-labelledby="wf-tab-{{ $index }}"
                class="ps-workflow-panel"
                @if ($index !== 0) hidden @endif
            >
                <div class="row g-4 align-items-stretch">
                    <div class="col-12 col-lg-6 d-flex flex-column justify-content-between gap-4">
                        <div>
                            <span class="ps-mono text-uppercase d-inline-block mb-2" style="font-size: 0.72rem; color: var(--ps-orange);">{{ $step['badge'] }}</span>
                            <h3 class="h2 fw-bold mb-2">{{ $step['title'] }}</h3>
                            <p class="ps-mono small mb-0" style="color: var(--ps-ink-3);">{{ $step['metric'] }}</p>
                        </div>

                        <div class="d-flex flex-column gap-3">
                            <div class="p-3 border rounded-3" style="background: var(--ps-canvas);">
                                <div class="fw-bold mb-1">{{ $step['row1Title'] }}</div>
                                <p class="small mb-0" style="color: var(--ps-ink-2);">{{ $step['row1Body'] }}</p>
                            </div>
                            <div class="p-3 border rounded-3" style="background: var(--ps-canvas);">
                                <div class="fw-bold mb-1">{{ $step['row2Title'] }}</div>
                                <p class="small mb-0" style="color: var(--ps-ink-2);">{{ $step['row2Body'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="ps-dot-surface p-3 p-sm-4 h-100 d-flex flex-column justify-content-between gap-3">
                            <div class="d-flex align-items-center justify-content-between gap-2 pb-2 border-bottom">
                                <span class="ps-mono text-uppercase fw-semibold" style="font-size: 0.7rem; color: var(--ps-orange);">{{ $step['uiTag'] }}</span>
                                <span class="badge bg-white text-dark border rounded-pill ps-mono" style="font-size: 0.68rem;">Step {{ $step['num'] }}</span>
                            </div>
                            <div class="fw-bold fs-5">{{ $step['uiHeading'] }}</div>
                            <div class="d-flex flex-column gap-2">
                                @foreach ($step['uiItems'] as $uiItem)
                                    <div class="p-3 bg-white border rounded-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                                        <div>
                                            <div class="fw-semibold small text-dark">{{ $uiItem[0] }}</div>
                                            <div class="small" style="color: var(--ps-ink-3); font-size: 0.78rem;">{{ $uiItem[1] }}</div>
                                        </div>
                                        <span class="badge rounded-pill px-3 py-1 ps-mono" style="background: var(--ps-orange-soft); color: var(--ps-orange-dark); font-size: 0.7rem;">
                                            {{ $uiItem[2] }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="pt-2 border-top d-flex align-items-center justify-content-between small">
                                <span class="ps-mono" style="color: var(--ps-ink-3); font-size: 0.74rem;">PostSync Workspace Engine</span>
                                <a href="{{ route('register') }}" class="fw-semibold text-decoration-none" style="color: var(--ps-orange);">Launch Workspace &rarr;</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</section>

<!-- 4. FolderCards ("Today" vs "With PostSync") -->
<section class="ps-framed-band py-5">
    <div class="ps-shell py-2 py-lg-4">
        <div class="row g-4 align-items-end mb-4">
            <div class="col-lg-8">
                <span class="ps-pill mb-2">Built Around Agency Edge Cases</span>
                <h2 class="fw-bold mb-2" style="font-size: clamp(1.75rem, 1.3rem + 1.8vw, 2.75rem);">
                    Conventional tools force you to ignore agency realities — or patch with spreadsheets<span class="ps-dot">.</span>
                </h2>
                <p class="mb-0" style="color: var(--ps-ink-2);">PostSync makes multi-client sign-offs, passwordless portals, and 10-channel media routing standard work.</p>
            </div>
        </div>

        <div class="ps-folder-tabs" role="tablist" aria-label="Before and after PostSync scenarios">
            @foreach ($edgeCases as $index => $case)
                <button
                    type="button"
                    class="ps-folder-tab"
                    role="tab"
                    id="folder-tab-{{ $case['key'] }}"
                    aria-controls="folder-panel-{{ $case['key'] }}"
                    aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                    data-folder-index="{{ $index }}"
                >
                    {{ $case['label'] }}
                </button>
            @endforeach
        </div>

        @foreach ($edgeCases as $index => $case)
            <div
                id="folder-panel-{{ $case['key'] }}"
                role="tabpanel"
                aria-labelledby="folder-tab-{{ $case['key'] }}"
                class="ps-folder-panel"
                @if ($index !== 0) hidden @endif
            >
                <div class="ps-compare-split">
                    <div class="ps-before-box">
                        <span class="ps-mono text-uppercase d-inline-block mb-2 px-2 py-1 bg-white border rounded small" style="font-size: 0.7rem; color: var(--ps-ink-2);">Today</span>
                        <h3 class="h5 fw-bold mb-2">{{ $case['todayTitle'] }}</h3>
                        <p class="small mb-0" style="color: var(--ps-ink-2); line-height: 1.65;">{{ $case['todayBody'] }}</p>
                    </div>

                    <div class="ps-after-box">
                        <span class="ps-mono text-uppercase d-inline-block mb-2 px-2 py-1 rounded small fw-semibold" style="font-size: 0.7rem; background: var(--ps-orange-soft); color: var(--ps-orange-dark);">With PostSync</span>
                        <h3 class="h5 fw-bold mb-2">{{ $case['withTitle'] }}</h3>
                        <p class="small mb-0" style="color: var(--ps-ink-2); line-height: 1.65;">{{ $case['withBody'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</section>

<!-- 5. Comparison Table -->
<section class="ps-framed-band is-white py-5">
    <div class="ps-shell py-2 py-lg-4">
        <div class="mb-4">
            <span class="ps-pill mb-2">PostSync Platform</span>
            <h2 class="fw-bold mb-2" style="font-size: clamp(1.75rem, 1.3rem + 1.8vw, 2.75rem);">
                Why PostSync is safer — and beats legacy tools<span class="ps-dot">.</span>
            </h2>
            <p class="mb-0" style="color: var(--ps-ink-2); max-inline-size: 62ch;">
                Stop paying per extra client seat on legacy schedulers or duct-taping approvals together in chat apps.
            </p>
        </div>

        <div class="ps-table-wrap">
            <table class="ps-compare-table">
                <thead>
                    <tr>
                        <th scope="col">Capability</th>
                        <th scope="col" class="ps-col-highlight">PostSync SaaS</th>
                        <th scope="col">Legacy Schedulers</th>
                        <th scope="col">Spreadsheets &amp; WhatsApp</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th scope="row" class="fw-semibold">Client Portal Login</th>
                        <td class="ps-col-highlight fw-semibold">Passwordless 1-time Gmail OTP + isolated brand scope</td>
                        <td>Extra paid seat or awkward read-only share links</td>
                        <td>None — static exports emailed manually</td>
                    </tr>
                    <tr>
                        <th scope="row" class="fw-semibold">OAuth Token Onboarding</th>
                        <td class="ps-col-highlight fw-semibold">Self-serve Client Connect links with AES-256-GCM storage</td>
                        <td>Manual screen-share calls or risky admin invites</td>
                        <td>Raw account passwords &amp; 2FA codes in spreadsheets</td>
                    </tr>
                    <tr>
                        <th scope="row" class="fw-semibold">Video &amp; Short-Form Studio</th>
                        <td class="ps-col-highlight fw-semibold">4-step composer with Reels, Shorts &amp; custom YT thumbnails</td>
                        <td>Basic single caption box; limited per-channel overrides</td>
                        <td>Uploading MP4 files separately in 10 tabs</td>
                    </tr>
                    <tr>
                        <th scope="row" class="fw-semibold">Approval Workflow</th>
                        <td class="ps-col-highlight fw-semibold">Built-in client sign-off, revision notes &amp; audit trail</td>
                        <td>Locked behind high enterprise tiers</td>
                        <td>Lost chat messages &amp; unapproved edits</td>
                    </tr>
                    <tr>
                        <th scope="row" class="fw-semibold">Unified Inbox &amp; Reporting</th>
                        <td class="ps-col-highlight fw-semibold">Live comment inbox + branded PDF &amp; CSV summaries</td>
                        <td>Separate paid add-ons</td>
                        <td>Manual copy-pasting screenshots on billing day</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- 6. 6-Card Capability Bento (#features) -->
<section id="features" class="ps-framed-band py-5">
    <div class="ps-shell py-2 py-lg-4">
        <div class="mb-4">
            <span class="ps-pill mb-2">The Social OS That Grows With You</span>
            <h2 class="fw-bold mb-1" style="font-size: clamp(1.75rem, 1.3rem + 1.8vw, 2.75rem);">
                Every PostSync Workspace Includes<span class="ps-dot">.</span>
            </h2>
        </div>

        <div class="ps-bento-6">
            @foreach ($capabilities as $cap)
                <article class="ps-capability-card">
                    <div>
                        <span class="ps-mono text-uppercase d-inline-block mb-2" style="font-size: 0.7rem; color: var(--ps-orange);">{{ $cap['tag'] }}</span>
                        <h3 class="h3 fw-bold mb-1">{{ $cap['title'] }}</h3>
                        <p class="fw-semibold small mb-2" style="color: var(--ps-ink);">{{ $cap['subtitle'] }}</p>
                        <p class="small mb-0" style="color: var(--ps-ink-2); line-height: 1.6;">{{ $cap['body'] }}</p>
                    </div>

                    <div class="ps-dot-surface p-3">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                            <span class="badge bg-dark text-white rounded-pill ps-mono" style="font-size: 0.68rem;">{{ $cap['pillA'] }}</span>
                            <span class="badge bg-white text-dark border rounded-pill ps-mono" style="font-size: 0.68rem;">{{ $cap['pillB'] }}</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($cap['metrics'] as $metric)
                                <span class="px-2 py-1 bg-white border rounded-2 small fw-medium text-dark" style="font-size: 0.78rem;">
                                    <i class="bi bi-check2 text-success me-1"></i>{{ $metric }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

<!-- 7. Supported Platforms Architectural Grid (#platforms) & Growth Roadmap -->
<section id="platforms" class="ps-framed-band is-white py-5">
    <div class="ps-shell py-2 py-lg-4">
        <div class="text-center mx-auto mb-5" style="max-inline-size: 660px;">
            <span class="ps-pill mb-2">Native OAuth Integrations</span>
            <h2 class="fw-bold mb-2" style="font-size: clamp(1.75rem, 1.3rem + 1.8vw, 2.75rem);">
                Supported Social Media Platforms<span class="ps-dot">.</span>
            </h2>
            <p class="mb-0" style="color: var(--ps-ink-2);">
                Connect pages and channels through official OAuth 2.0 consent screens or share a secure Client Connect link.
            </p>
        </div>

        <div class="ps-hairline-grid mb-5">
            @foreach ($platforms as $p)
                <article class="ps-platform-cell">
                    <div class="d-flex align-items-center gap-2">
                        <span class="platform-icon {{ $p['class'] }} flex-shrink-0"><i class="bi {{ $p['icon'] }}"></i></span>
                        <span class="fw-bold small text-dark">{{ $p['name'] }}</span>
                    </div>
                    <p class="small mb-0" style="color: var(--ps-ink-2); font-size: 0.84rem;">{{ $p['desc'] }}</p>
                </article>
            @endforeach
        </div>

        <!-- 4-Card Growth Roadmap -->
        <div class="pt-4 border-top">
            <div class="mb-4">
                <span class="ps-pill mb-2">Agency Onboarding Roadmap</span>
                <h2 class="fw-bold mb-1" style="font-size: clamp(1.5rem, 1.2rem + 1.4vw, 2.3rem);">
                    Your Roadmap to Multi-Client Scale, Accelerated<span class="ps-dot">.</span>
                </h2>
            </div>

            <div class="ps-roadmap-grid">
                @foreach ([
                    ['Week 1', 'Launch your first workspace, send Client Connect links, and ship approved posts live in minutes.'],
                    ['Month 1', 'Move three client retainers onto PostSync; retire scattered spreadsheets and approval screenshots cleanly.'],
                    ['Quarter 1', 'Run timezone-accurate publishing calendars, live inbox replies, and executive PDF reports effortlessly.'],
                    ['Year 1', 'Scale up to dozens of client workspaces and 60+ social profiles without hiring extra coordinators.'],
                ] as $tileIndex => $tile)
                    <div class="ps-roadmap-tile">
                        <div class="ps-tick-bar" aria-hidden="true">
                            @for ($t = 0; $t < 8; $t++)
                                <span class="ps-tick-line"></span>
                            @endfor
                            <span class="ps-tick-blip"></span>
                        </div>
                        <div class="ps-mono fw-semibold" style="color: var(--ps-orange); font-size: 0.8rem;">{{ $tile[0] }}</div>
                        <p class="small mb-0" style="color: var(--ps-ink-2); line-height: 1.6;">{{ $tile[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<!-- 8. Transparent Pricing (#pricing) -->
<section id="pricing" class="ps-framed-band py-5">
    <div class="ps-shell py-2 py-lg-4">
        <div class="text-center mx-auto mb-5" style="max-inline-size: 620px;">
            <span class="ps-pill mb-2">Transparent Plans</span>
            <h2 class="fw-bold mb-2" style="font-size: clamp(1.75rem, 1.3rem + 1.8vw, 2.75rem);">
                Scalable Pricing for Every Agency Stage<span class="ps-dot">.</span>
            </h2>
            <p class="mb-0" style="color: var(--ps-ink-2);">Start with a 14-day free trial. Upgrade or switch tiers anytime inside Billing.</p>
        </div>

        <div class="row g-4 align-items-stretch">
            <!-- Starter -->
            <div class="col-12 col-lg-4">
                <div class="ps-pricing-card">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h3 class="h5 fw-bold mb-0">Starter</h3>
                        <span class="badge bg-light text-dark border rounded-pill ps-mono">Solo</span>
                    </div>
                    <p class="small mb-0" style="color: var(--ps-ink-3);">For freelancers &amp; boutique creators</p>
                    <div class="my-4">
                        <span class="fs-1 fw-bold ps-display">₹1,999</span><span class="small" style="color: var(--ps-ink-3);"> / month</span>
                    </div>
                    <ul class="list-unstyled small flex-grow-1 mb-4 d-flex flex-column gap-2" style="color: var(--ps-ink-2);">
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>5 Social Profiles</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>1 Team Member</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>1 Client Brand</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>AI Composer &amp; Calendar</li>
                    </ul>
                    <a href="{{ route('register') }}" class="ps-btn-outline w-100 py-2">Get Started</a>
                </div>
            </div>

            <!-- Professional -->
            <div class="col-12 col-lg-4">
                <div class="ps-pricing-card is-featured">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h3 class="h5 fw-bold mb-0">Professional</h3>
                        <span class="badge rounded-pill text-white px-3 py-1 ps-mono" style="background: var(--ps-orange); font-size: 0.7rem;">Most Popular</span>
                    </div>
                    <p class="small mb-0" style="color: var(--ps-ink-3);">For growing marketing teams</p>
                    <div class="my-4">
                        <span class="fs-1 fw-bold ps-display">₹4,999</span><span class="small" style="color: var(--ps-ink-3);"> / month</span>
                    </div>
                    <ul class="list-unstyled small flex-grow-1 mb-4 d-flex flex-column gap-2" style="color: var(--ps-ink-2);">
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>20 Social Profiles</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>5 Team Members &amp; Client Portal</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Client Approval Workflows</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Analytics &amp; White-Label Reports</li>
                    </ul>
                    <a href="{{ route('register') }}" class="ps-btn-primary w-100 py-2">Start Free Trial</a>
                </div>
            </div>

            <!-- Agency -->
            <div class="col-12 col-lg-4">
                <div class="ps-pricing-card">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h3 class="h5 fw-bold mb-0">Agency</h3>
                        <span class="badge bg-light text-dark border rounded-pill ps-mono">Scale</span>
                    </div>
                    <p class="small mb-0" style="color: var(--ps-ink-3);">For multi-brand digital agencies</p>
                    <div class="my-4">
                        <span class="fs-1 fw-bold ps-display">₹11,999</span><span class="small" style="color: var(--ps-ink-3);"> / month</span>
                    </div>
                    <ul class="list-unstyled small flex-grow-1 mb-4 d-flex flex-column gap-2" style="color: var(--ps-ink-2);">
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>60 Social Profiles</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Unlimited Workspaces &amp; 20 Clients</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Unified Inbox &amp; Automated Dispatches</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Branded PDF Executive Summaries</li>
                    </ul>
                    <a href="{{ route('register') }}" class="ps-btn-outline w-100 py-2">Get Started</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 9. FAQ Accordion (#faq — built using native accessible <details>) -->
<section id="faq" class="ps-framed-band is-white py-5" aria-labelledby="faq-heading">
    <div class="ps-shell py-2 py-lg-4">
        <div class="row g-4">
            <div class="col-lg-4">
                <span class="ps-pill mb-2">Got Questions</span>
                <h2 id="faq-heading" class="fw-bold mb-2" style="font-size: clamp(1.75rem, 1.3rem + 1.8vw, 2.75rem);">
                    FAQ<span class="ps-dot">.</span>
                </h2>
                <p class="small mb-0" style="color: var(--ps-ink-2);">
                    Clear answers about workspace isolation, Gmail OTP login, official OAuth scopes, and client onboarding.
                </p>
            </div>

            <div class="col-lg-8">
                <div class="ps-faq-accordion">
                    <details>
                        <summary><span>How do clients review posts without seeing agency billing or other brands?</span></summary>
                        <div class="ps-faq-answer">
                            When you invite a client contact, they authenticate via a one-time Gmail OTP code into a strict Client Portal view scoped exclusively to their assigned workspace. Billing, internal team notes, and unrelated client brands remain completely hidden.
                        </div>
                    </details>

                    <details>
                        <summary><span>Do clients have to give us their Meta, YouTube, or Instagram passwords?</span></summary>
                        <div class="ps-faq-answer">
                            Never. Generate a 1-click expiring Client Connect link inside PostSync and send it to your client. They click Authorize via official OAuth consent screens, and PostSync encrypts the resulting tokens with AES-256-GCM at rest.
                        </div>
                    </details>

                    <details>
                        <summary><span>Can I customize YouTube Shorts titles, descriptions, and thumbnails separately from Instagram?</span></summary>
                        <div class="ps-faq-answer">
                            Yes. Our 4-Step Studio Composer lets you write one master campaign draft and override metadata, hashtags, custom thumbnails, or first comments per individual platform before queueing or submitting for approval.
                        </div>
                    </details>

                    <details>
                        <summary><span>How fast can our agency revoke Google or Meta API permissions?</span></summary>
                        <div class="ps-faq-answer">
                            Immediately. Removing a connected account under Social Accounts permanently purges stored encrypted tokens from PostSync's database, and users can also revoke consent anytime inside Google Security Settings or Meta Business Integrations.
                        </div>
                    </details>

                    <details>
                        <summary><span>Can we upgrade or switch plans cleanly as we add more clients?</span></summary>
                        <div class="ps-faq-answer">
                            Yes. Every plan starts with a 14-day free trial and scales seamlessly from Starter (5 profiles) through Professional (20 profiles) and Agency (60 profiles, unlimited workspaces) directly from your workspace Billing settings.
                        </div>
                    </details>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 10. Security, Google API Integration & Policy Compliance (#about-purpose) -->
<section id="about-purpose" class="ps-framed-band py-5">
    <div class="ps-shell py-2 py-md-3">
        <div class="row g-4 align-items-stretch">
            <div class="col-12 col-lg-6">
                <div class="ps-compliance-panel">
                    <span class="badge bg-light text-dark border rounded-pill px-3 py-1 small fw-semibold mb-3">
                        <i class="bi bi-youtube text-danger me-1"></i> Application Purpose &amp; Google API Usage
                    </span>
                    <h3 class="h4 fw-bold text-dark mb-2">How PostSync SaaS Operates</h3>
                    <p class="text-secondary small mb-3">
                        <strong>PostSync SaaS</strong> (<a href="https://postsync.cloud" class="text-decoration-none">https://postsync.cloud</a>) is a multi-tenant social media scheduling and publishing application. Users connect their authorized social channels to plan campaigns, review drafts, and publish scheduled media seamlessly.
                    </p>
                    <p class="text-muted small mb-0">
                        When you connect Google/YouTube, PostSync requests <code>youtube.upload</code> and <code>youtube.readonly</code> strictly to publish videos and custom thumbnails to your channel, configure video metadata, and show analytics inside your private workspace.
                    </p>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="ps-compliance-panel">
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 small fw-semibold mb-3">
                        <i class="bi bi-shield-check me-1"></i> Privacy, Security &amp; API Policy Compliance
                    </span>
                    <ul class="text-secondary small ps-3 mb-3 d-flex flex-column gap-2">
                        <li><strong>Encrypted Tokens:</strong> OAuth credentials are encrypted at rest with AES-256-GCM and never sold or transferred.</li>
                        <li><strong>Strict Compliance:</strong> Complies with the <a href="https://developers.google.com/terms" target="_blank" class="text-decoration-underline text-secondary">Google APIs Terms of Service</a> &amp; User Data Policy, and strictly prohibits AI-generated Non-Consensual Intimate Imagery (AI NCII).</li>
                        <li><strong>Instant Revocation:</strong> Disconnect channels anytime from Social Accounts or <a href="https://myaccount.google.com/permissions" target="_blank" class="text-decoration-underline text-secondary">Google Security Settings</a>.</li>
                    </ul>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('legal.privacy') }}" class="btn btn-sm btn-outline-dark rounded-pill px-3">Privacy Policy</a>
                        <a href="{{ route('legal.terms') }}" class="btn btn-sm btn-outline-dark rounded-pill px-3">Terms of Service</a>
                        <a href="{{ route('legal.deletion') }}" class="btn btn-sm btn-outline-dark rounded-pill px-3">Data Deletion</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    (function () {
        // 5-Step Interactive Workflow Tabs
        const wfTabs = Array.from(document.querySelectorAll('.ps-step-tab'));
        const wfPanels = Array.from(document.querySelectorAll('.ps-workflow-panel'));
        const wfCounter = document.getElementById('wfCounter');
        const wfProgress = document.getElementById('wfProgressBar');
        const wfPrev = document.getElementById('wfPrev');
        const wfNext = document.getElementById('wfNext');
        let activeStep = 0;

        function setWorkflowStep(nextIndex) {
            if (!wfTabs.length) return;
            activeStep = Math.max(0, Math.min(nextIndex, wfTabs.length - 1));

            wfTabs.forEach(function (tab, i) {
                tab.setAttribute('aria-selected', String(i === activeStep));
            });
            wfPanels.forEach(function (panel, i) {
                panel.hidden = i !== activeStep;
            });

            if (wfCounter) wfCounter.textContent = (activeStep + 1) + '/' + wfTabs.length;
            if (wfProgress) wfProgress.style.width = (((activeStep + 1) / wfTabs.length) * 100) + '%';
            if (wfPrev) wfPrev.disabled = activeStep === 0;
            if (wfNext) wfNext.disabled = activeStep === wfTabs.length - 1;
        }

        wfTabs.forEach(function (tab, index) {
            tab.addEventListener('click', function () {
                setWorkflowStep(index);
            });
        });

        if (wfPrev) wfPrev.addEventListener('click', function () { setWorkflowStep(activeStep - 1); });
        if (wfNext) wfNext.addEventListener('click', function () { setWorkflowStep(activeStep + 1); });

        // Edge-Case FolderCards Tabs
        const folderTabs = Array.from(document.querySelectorAll('.ps-folder-tab'));
        const folderPanels = Array.from(document.querySelectorAll('.ps-folder-panel'));

        folderTabs.forEach(function (tab, index) {
            tab.addEventListener('click', function () {
                folderTabs.forEach(function (t, i) {
                    t.setAttribute('aria-selected', String(i === index));
                });
                folderPanels.forEach(function (p, i) {
                    p.hidden = i !== index;
                });
            });
        });
    })();
</script>
@endpush
