@extends('layouts.public', ['title' => 'PostSync SaaS — Enterprise Social Media Management Platform'])

@section('content')
<!-- Hero Section -->
<section class="hero-aura-bg py-5 border-bottom">
    <div class="container py-3 py-lg-5">
        <div class="row align-items-center g-4 g-lg-5">
            <div class="col-lg-7 text-center text-lg-start">
                <div class="hero-badge mb-3">
                    <i class="bi bi-stars text-warning"></i>
                    <span>Built for Digital Agencies, Brands &amp; Creators</span>
                </div>

                <h1 class="hero-title mb-3">
                    Publish, Schedule &amp; Scale with <span class="text-gradient-indigo">PostSync SaaS</span> Across 10 Networks.
                </h1>

                <p class="hero-lead mx-auto mx-lg-0 mb-4">
                    One unified command center to connect client channels via official OAuth, collaborate with AI copywriting, route posts through client approvals, and track live analytics — with zero shared passwords.
                </p>

                <div class="d-grid d-sm-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
                    <a href="{{ route('register') }}" class="btn-hero-primary">
                        <i class="bi bi-rocket-takeoff-fill"></i>
                        <span>Start 14-Day Free Trial</span>
                    </a>
                    <a href="{{ route('login') }}" class="btn-hero-secondary">
                        <i class="bi bi-envelope-check"></i>
                        <span>Sign In with Gmail OTP</span>
                    </a>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-3 gap-md-4 mt-4 pt-2 text-muted small justify-content-center justify-content-lg-start fw-medium">
                    <span><i class="bi bi-shield-lock-fill text-success me-1"></i>AES-256 Encrypted OAuth</span>
                    <span><i class="bi bi-people-fill text-primary me-1"></i>Passwordless Client Portal</span>
                    <span><i class="bi bi-file-earmark-bar-graph-fill text-info me-1"></i>Branded PDF Reports</span>
                </div>
            </div>

            <!-- Live Workspace UI Preview Mockup -->
            <div class="col-lg-5">
                <div class="showcase-card p-3 p-sm-4">
                    <!-- Mock Window Bar -->
                    <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            <span class="rounded-circle bg-danger d-inline-block" style="width: 10px; height: 10px;"></span>
                            <span class="rounded-circle bg-warning d-inline-block" style="width: 10px; height: 10px;"></span>
                            <span class="rounded-circle bg-success d-inline-block" style="width: 10px; height: 10px;"></span>
                            <span class="small fw-bold text-dark ms-1 text-truncate">Agency Studio • Live Queue</span>
                        </div>
                        <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-1 small flex-shrink-0">
                            <span class="pulse-online me-1"></span>Sync Active
                        </span>
                    </div>

                    <!-- Mini Pastel Metric Pills matching Dashboard -->
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <div class="p-2 p-sm-3 rounded-4" style="background: #d4f1eb; color: #0f3830;">
                                <div class="fw-bold" style="font-size: 0.7rem;">Published</div>
                                <div class="fs-5 fw-bold lh-sm mt-1">1,248</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 p-sm-3 rounded-4" style="background: #feea9f; color: #523e02;">
                                <div class="fw-bold" style="font-size: 0.7rem;">Queued</div>
                                <div class="fs-5 fw-bold lh-sm mt-1">42</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 p-sm-3 rounded-4" style="background: #e4ddff; color: #2b1b54;">
                                <div class="fw-bold" style="font-size: 0.7rem;">Approvals</div>
                                <div class="fs-5 fw-bold lh-sm mt-1">100%</div>
                            </div>
                        </div>
                    </div>

                    <!-- Connected Channel Strip -->
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <span class="platform-icon facebook"><i class="bi bi-facebook"></i></span>
                        <span class="platform-icon instagram"><i class="bi bi-instagram"></i></span>
                        <span class="platform-icon youtube"><i class="bi bi-youtube"></i></span>
                        <span class="platform-icon linkedin"><i class="bi bi-linkedin"></i></span>
                        <span class="platform-icon pinterest"><i class="bi bi-pin-angle-fill"></i></span>
                        <span class="platform-icon google_business"><i class="bi bi-google"></i></span>
                    </div>

                    <!-- Sample Post Card -->
                    <div class="p-3 rounded-4 bg-light border mb-3">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                            <span class="small fw-bold" style="color: #4f46e5;">Campaign: Autumn Brand Launch</span>
                            <span class="badge bg-white text-dark border rounded-pill" style="font-size: 0.68rem;">Client Approved</span>
                        </div>
                        <p class="small text-secondary mb-0">
                            "Fresh seasonal visuals + AI-tailored captions dispatched seamlessly to Facebook Reels, Instagram &amp; YouTube Shorts."
                        </p>
                    </div>

                    <div class="d-flex align-items-center justify-content-between gap-2 pt-1">
                        <span class="small text-muted fw-medium">
                            <i class="bi bi-calendar2-check text-success me-1"></i>Next dispatch: Today, 6:30 PM
                        </span>
                        <a href="{{ route('register') }}" class="btn btn-sm btn-dark rounded-pill px-3 fw-semibold">Try Studio &rarr;</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Key Workflow Capabilities (#features) -->
<section id="features" class="py-5 bg-white">
    <div class="container py-3 py-md-4">
        <div class="text-center mb-5 mx-auto" style="max-width: 680px;">
            <span class="text-uppercase fw-bold small d-block mb-2" style="color: #4f46e5; letter-spacing: 0.08em;">Why Agencies Switch to PostSync</span>
            <h2 class="fw-bold text-dark mb-2">Everything Your Team &amp; Clients Need in One Place</h2>
            <p class="text-secondary mb-0">Stop juggling spreadsheets, risky shared logins, and disconnected analytics tools.</p>
        </div>

        <div class="row g-4">
            <div class="col-12 col-md-6 col-lg-4">
                <article class="glass-feature-card">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-4 p-3 mb-3" style="width: 54px; height: 54px; background: #eef2ff; color: #4f46e5;">
                        <i class="bi bi-buildings-fill fs-4"></i>
                    </div>
                    <h5 class="fw-bold text-dark">Multi-Tenant Workspaces &amp; Client Portal</h5>
                    <p class="text-secondary small mb-0">Organize every client brand inside isolated workspaces. Invite clients with their Gmail via 1-time OTP codes so they review posts, calendars, analytics &amp; reports without seeing agency billing.</p>
                </article>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <article class="glass-feature-card">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-4 p-3 mb-3" style="width: 54px; height: 54px; background: #d4f1eb; color: #0f3830;">
                        <i class="bi bi-calendar3-week fs-4"></i>
                    </div>
                    <h5 class="fw-bold text-dark">4-Step Composer, Calendar &amp; Queue</h5>
                    <p class="text-secondary small mb-0">Compose once, customize Reels, Shorts, Stories or custom thumbnails per channel, drag-and-drop on an interactive calendar, and let background queues publish reliably with retry safety.</p>
                </article>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <article class="glass-feature-card">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-4 p-3 mb-3" style="width: 54px; height: 54px; background: #fedde5; color: #4a1523;">
                        <i class="bi bi-chat-heart-fill fs-4"></i>
                    </div>
                    <h5 class="fw-bold text-dark">Approvals, Social Inbox &amp; White-Label Reports</h5>
                    <p class="text-secondary small mb-0">Streamline client sign-offs with threaded feedback, reply to live comments inside one Unified Inbox, and export branded executive PDF &amp; CSV reports on autopilot.</p>
                </article>
            </div>
        </div>
    </div>
</section>

<!-- Supported Platforms Bento Grid (#platforms) -->
<section id="platforms" class="py-5 border-top border-bottom" style="background: #f8fafc;">
    <div class="container py-3 py-md-4 text-center">
        <span class="text-uppercase fw-bold small d-block mb-2" style="color: #4f46e5; letter-spacing: 0.08em;">Native OAuth Integrations</span>
        <h2 class="fw-bold text-dark mb-3">Supported Social Media Platforms</h2>
        <p class="text-secondary mb-5 mx-auto" style="max-width: 640px;">
            Connect pages and channels directly through official OAuth 2.0 consent screens or share a secure 1-click Client Connect link.
        </p>

        <div class="bento-platforms-grid">
            @php
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
            @endphp

            @foreach ($platforms as $p)
                <article class="platform-bento-card">
                    <div class="d-flex align-items-center gap-2">
                        <span class="platform-icon {{ $p['class'] }} flex-shrink-0"><i class="bi {{ $p['icon'] }}"></i></span>
                        <span class="fw-bold small text-dark">{{ $p['name'] }}</span>
                    </div>
                    <p class="text-secondary small mb-0">{{ $p['desc'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

<!-- Transparent Pricing Section (#pricing) -->
<section id="pricing" class="py-5 bg-white">
    <div class="container py-3 py-md-4">
        <div class="text-center mb-5 mx-auto" style="max-width: 620px;">
            <span class="text-uppercase fw-bold small d-block mb-2" style="color: #4f46e5; letter-spacing: 0.08em;">Transparent Plans</span>
            <h2 class="fw-bold text-dark mb-2">Scalable Pricing for Every Agency Stage</h2>
            <p class="text-secondary mb-0">Start with a 14-day free trial. Upgrade or switch tiers anytime inside Billing.</p>
        </div>

        <div class="row g-4 align-items-stretch">
            <!-- Starter -->
            <div class="col-12 col-lg-4">
                <div class="pricing-card">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h5 class="fw-bold text-dark mb-0">Starter</h5>
                        <span class="badge bg-light text-secondary border rounded-pill">Solo</span>
                    </div>
                    <p class="text-muted small mb-0">For freelancers &amp; boutique creators</p>
                    <div class="my-4">
                        <span class="fs-1 fw-bold text-dark">₹1,999</span><span class="text-muted small"> / month</span>
                    </div>
                    <ul class="list-unstyled small text-secondary flex-grow-1 mb-4 d-flex flex-column gap-2">
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>5 Social Profiles</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>1 Team Member</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>1 Client Brand</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>AI Composer &amp; Calendar</li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn-outline-dark rounded-pill py-2 fw-semibold w-100">Get Started</a>
                </div>
            </div>

            <!-- Professional -->
            <div class="col-12 col-lg-4">
                <div class="pricing-card pricing-card-featured">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h5 class="fw-bold text-dark mb-0">Professional</h5>
                        <span class="badge rounded-pill text-white px-3 py-1" style="background: #4f46e5;">Most Popular</span>
                    </div>
                    <p class="text-muted small mb-0">For growing marketing teams</p>
                    <div class="my-4">
                        <span class="fs-1 fw-bold text-dark">₹4,999</span><span class="text-muted small"> / month</span>
                    </div>
                    <ul class="list-unstyled small text-secondary flex-grow-1 mb-4 d-flex flex-column gap-2">
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>20 Social Profiles</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>5 Team Members &amp; Client Portal</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Client Approval Workflows</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Analytics &amp; White-Label Reports</li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn-hero-primary w-100 py-2">Start Free Trial</a>
                </div>
            </div>

            <!-- Agency -->
            <div class="col-12 col-lg-4">
                <div class="pricing-card">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h5 class="fw-bold text-dark mb-0">Agency</h5>
                        <span class="badge bg-light text-secondary border rounded-pill">Scale</span>
                    </div>
                    <p class="text-muted small mb-0">For multi-brand digital agencies</p>
                    <div class="my-4">
                        <span class="fs-1 fw-bold text-dark">₹11,999</span><span class="text-muted small"> / month</span>
                    </div>
                    <ul class="list-unstyled small text-secondary flex-grow-1 mb-4 d-flex flex-column gap-2">
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>60 Social Profiles</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Unlimited Workspaces &amp; 20 Clients</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Unified Inbox &amp; Automated Dispatches</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Branded PDF Executive Summaries</li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn-outline-dark rounded-pill py-2 fw-semibold w-100">Get Started</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Security, Google API Integration & Policy Compliance (#about-purpose) -->
<section id="about-purpose" class="py-5 border-top" style="background: #f8fafc;">
    <div class="container py-2 py-md-3">
        <div class="row g-4 align-items-stretch">
            <div class="col-12 col-lg-6">
                <div class="compliance-panel bg-white">
                    <span class="badge bg-light text-dark border rounded-pill px-3 py-1 small fw-semibold mb-3">
                        <i class="bi bi-youtube text-danger me-1"></i> Application Purpose &amp; Google API Usage
                    </span>
                    <h4 class="fw-bold text-dark mb-2">How PostSync SaaS Operates</h4>
                    <p class="text-secondary small mb-3">
                        <strong>PostSync SaaS</strong> (<a href="https://postsync.cloud" class="text-decoration-none">https://postsync.cloud</a>) is a multi-tenant social media scheduling and publishing application. Users connect their authorized social channels to plan campaigns, review drafts, and publish scheduled media seamlessly.
                    </p>
                    <p class="text-muted small mb-0">
                        When you connect Google/YouTube, PostSync requests <code>youtube.upload</code> and <code>youtube.readonly</code> strictly to publish videos and custom thumbnails to your channel, configure video metadata, and show analytics inside your private workspace.
                    </p>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="compliance-panel bg-white">
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
