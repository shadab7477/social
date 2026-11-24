@extends('layouts.public', ['title' => 'SocialPilotX — Enterprise Social Media Management SaaS'])

@section('content')
<!-- Hero Section -->
<section class="py-5 bg-light border-bottom">
    <div class="container py-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-7 text-center text-lg-start">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-semibold mb-3">
                    <i class="bi bi-stars me-1"></i> Multi-Tenant SaaS Built for Digital Agencies
                </span>
                <h1 class="display-4 fw-bold text-dark lh-sm mb-3">
                    Manage, Schedule & Publish Across <span class="text-primary">10 Social Networks</span> in One Platform.
                </h1>
                <p class="lead text-secondary mb-4">
                    The all-in-one platform for marketing agencies, enterprises, and local businesses. Connect Facebook, Instagram, LinkedIn, YouTube, TikTok, Pinterest, X, Threads, Google Business, and Bluesky without mixing client data.
                </p>
                <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg px-4 shadow-sm">
                        <i class="bi bi-rocket-takeoff-fill me-2"></i> Start 14-Day Free Trial
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-lg px-4">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Sign In to Workspace
                    </a>
                </div>
                <div class="d-flex align-items-center gap-4 mt-4 pt-2 text-muted small justify-content-center justify-content-lg-start">
                    <div><i class="bi bi-shield-check text-success me-1"></i> AES-256 Encrypted OAuth</div>
                    <div><i class="bi bi-check-circle text-success me-1"></i> Multi-Tier Approvals</div>
                    <div><i class="bi bi-credit-card text-success me-1"></i> Razorpay & Stripe Ready</div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-dark text-white py-3 px-4 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-danger rounded-circle p-1"></span>
                            <span class="badge bg-warning rounded-circle p-1"></span>
                            <span class="badge bg-success rounded-circle p-1"></span>
                            <span class="small fw-semibold ms-2">SocialPilotX Composer</span>
                        </div>
                        <span class="badge bg-success-subtle text-success small">Live Engine</span>
                    </div>
                    <div class="card-body p-4 bg-white">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="platform-icon facebook"><i class="bi bi-facebook"></i></span>
                            <span class="platform-icon instagram"><i class="bi bi-instagram"></i></span>
                            <span class="platform-icon linkedin"><i class="bi bi-linkedin"></i></span>
                            <span class="platform-icon x"><i class="bi bi-twitter-x"></i></span>
                            <span class="platform-icon youtube"><i class="bi bi-youtube"></i></span>
                        </div>
                        <div class="p-3 bg-light rounded-3 mb-3 border">
                            <div class="fw-semibold small text-primary mb-1">Target Client: Sharma Restaurant</div>
                            <div class="small text-secondary">"Excited to announce our all-new Sunday Brunch Menu! Hand-crafted delicacies and live music."</div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-2">
                            <span class="badge bg-indigo-subtle text-primary"><i class="bi bi-calendar-event me-1"></i> Auto-Queued in Redis</span>
                            <span class="btn btn-sm btn-primary disabled">1-Click Publish</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Supported Platforms Section -->
<section id="platforms" class="py-5 bg-white">
    <div class="container py-4 text-center">
        <h6 class="text-uppercase text-primary fw-bold letter-spacing-1 small mb-2">Native Integrations</h6>
        <h2 class="fw-bold mb-4">Supported Social Media Platforms</h2>
        <p class="text-secondary mb-5 mx-auto" style="max-width: 650px;">
            Every platform integration is independently architected according to official API standards, capability matrices, and token security requirements.
        </p>

        <div class="row g-4 justify-content-center">
            @php
                $platforms = [
                    ['name' => 'Facebook Pages', 'icon' => 'bi-facebook', 'class' => 'facebook', 'desc' => 'Text, images, videos & Meta Graph API container publishing.'],
                    ['name' => 'Instagram Professional', 'icon' => 'bi-instagram', 'class' => 'instagram', 'desc' => 'Business/Creator single images, carousel & reels container workflow.'],
                    ['name' => 'LinkedIn', 'icon' => 'bi-linkedin', 'class' => 'linkedin', 'desc' => 'Personal profiles & Organization pages via official Posts API.'],
                    ['name' => 'YouTube', 'icon' => 'bi-youtube', 'class' => 'youtube', 'desc' => 'Resumable video upload pipeline with daily quota tracking.'],
                    ['name' => 'TikTok', 'icon' => 'bi-tiktok', 'class' => 'tiktok', 'desc' => 'Login Kit & Content Posting API with creator capability verification.'],
                    ['name' => 'Pinterest', 'icon' => 'bi-pin-angle-fill', 'class' => 'pinterest', 'desc' => 'Boards, Pins & Destination URLs via official v5 API.'],
                    ['name' => 'X (Twitter)', 'icon' => 'bi-twitter-x', 'class' => 'x', 'desc' => 'OAuth 2.0 PKCE, cost-aware rate limiting & media upload.'],
                    ['name' => 'Threads', 'icon' => 'bi-at', 'class' => 'threads', 'desc' => 'Official Threads Graph API container-based publishing.'],
                    ['name' => 'Google Business', 'icon' => 'bi-google', 'class' => 'google_business', 'desc' => 'Multi-location local posts, offers and insights.'],
                    ['name' => 'Bluesky', 'icon' => 'bi-cloud-sun-fill', 'class' => 'bluesky', 'desc' => 'AT Protocol (ATP) authentication, facets, and record creation.'],
                ];
            @endphp

            @foreach ($platforms as $p)
                <div class="col-md-4 col-lg-2-4 col-sm-6">
                    <div class="card h-100 p-3 text-start shadow-sm border">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="platform-icon {{ $p['class'] }}"><i class="bi {{ $p['icon'] }}"></i></span>
                            <span class="fw-bold small text-dark">{{ $p['name'] }}</span>
                        </div>
                        <p class="text-muted small mb-0">{{ $p['desc'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Features Grid -->
<section id="features" class="py-5 bg-light border-top border-bottom">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h6 class="text-uppercase text-primary fw-bold small mb-2">Why Agencies Choose SocialPilotX</h6>
            <h2 class="fw-bold">Enterprise Architecture in a Single Laravel Monolith</h2>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm p-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-3 p-3 mb-3" style="width: 50px; height: 50px;">
                        <i class="bi bi-shield-lock-fill fs-4"></i>
                    </div>
                    <h5 class="fw-bold">True Multi-Tenancy</h5>
                    <p class="text-secondary small">Agency workspaces are logically partitioned with Eloquent Global Scopes. Agency clients (e.g. restaurants, e-commerce stores) are fully isolated.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm p-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-success-subtle text-success rounded-3 p-3 mb-3" style="width: 50px; height: 50px;">
                        <i class="bi bi-cpu-fill fs-4"></i>
                    </div>
                    <h5 class="fw-bold">Redis Publishing Engine</h5>
                    <p class="text-secondary small">Asynchronous queues powered by Redis and Supervisor. Atomic locks prevent duplicate posting and support exponential retry backoffs.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm p-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-warning-subtle text-warning rounded-3 p-3 mb-3" style="width: 50px; height: 50px;">
                        <i class="bi bi-check-all fs-4"></i>
                    </div>
                    <h5 class="fw-bold">Multi-Tier Approvals</h5>
                    <p class="text-secondary small">Assign editors, managers, and external client reviewers. Enforce client sign-off before content can ever be published.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Section -->
<section id="pricing" class="py-5 bg-white">
    <div class="container py-4 text-center">
        <h6 class="text-uppercase text-primary fw-bold small mb-2">Transparent Plans</h6>
        <h2 class="fw-bold mb-4">Scalable Pricing for Every Business Size</h2>

        <div class="row g-4 justify-content-center pt-3">
            <!-- Starter -->
            <div class="col-lg-3 col-md-6 text-start">
                <div class="card h-100 p-4 border shadow-sm">
                    <h5 class="fw-bold text-dark">Starter</h5>
                    <p class="text-muted small">For solo marketers & freelancers</p>
                    <div class="my-3">
                        <span class="display-6 fw-bold">₹1,999</span><span class="text-muted small">/mo</span>
                    </div>
                    <ul class="list-unstyled small text-secondary mb-4">
                        <li class="mb-2"><i class="bi bi-check text-success me-2"></i>5 Social Profiles</li>
                        <li class="mb-2"><i class="bi bi-check text-success me-2"></i>1 Team Member</li>
                        <li class="mb-2"><i class="bi bi-check text-success me-2"></i>1 Client Account</li>
                        <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Standard Scheduling</li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn-outline-primary w-100 mt-auto">Get Started</a>
                </div>
            </div>

            <!-- Professional -->
            <div class="col-lg-3 col-md-6 text-start">
                <div class="card h-100 p-4 border border-primary shadow">
                    <div class="badge bg-primary text-white w-50 mb-2">Most Popular</div>
                    <h5 class="fw-bold text-dark">Professional</h5>
                    <p class="text-muted small">For growing marketing teams</p>
                    <div class="my-3">
                        <span class="display-6 fw-bold">₹4,999</span><span class="text-muted small">/mo</span>
                    </div>
                    <ul class="list-unstyled small text-secondary mb-4">
                        <li class="mb-2"><i class="bi bi-check text-success me-2"></i>20 Social Profiles</li>
                        <li class="mb-2"><i class="bi bi-check text-success me-2"></i>3 Team Members</li>
                        <li class="mb-2"><i class="bi bi-check text-success me-2"></i>5 Client Accounts</li>
                        <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Approval Workflows</li>
                        <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Social Analytics</li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn-primary w-100 mt-auto">Start Free Trial</a>
                </div>
            </div>

            <!-- Agency -->
            <div class="col-lg-3 col-md-6 text-start">
                <div class="card h-100 p-4 border shadow-sm">
                    <h5 class="fw-bold text-dark">Agency</h5>
                    <p class="text-muted small">For digital agencies managing scale</p>
                    <div class="my-3">
                        <span class="display-6 fw-bold">₹11,999</span><span class="text-muted small">/mo</span>
                    </div>
                    <ul class="list-unstyled small text-secondary mb-4">
                        <li class="mb-2"><i class="bi bi-check text-success me-2"></i>60 Social Profiles</li>
                        <li class="mb-2"><i class="bi bi-check text-success me-2"></i>10 Team Members</li>
                        <li class="mb-2"><i class="bi bi-check text-success me-2"></i>20 Client Accounts</li>
                        <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Branded PDF Reports</li>
                        <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Unified Social Inbox</li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn-outline-primary w-100 mt-auto">Get Started</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
