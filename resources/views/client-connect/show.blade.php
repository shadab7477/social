<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connect Social Accounts — {{ $workspace->name }} | PostSync</title>

    <!-- Google Fonts (Inter) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-light min-vh-100 py-5">
    <div class="container" style="max-width: 680px;">
        
        <!-- Header Brand & Workspace -->
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-3 p-3 shadow-sm mb-3">
                <i class="bi bi-send-fill fs-3"></i>
            </div>
            <h3 class="fw-bold text-dark mb-1">Post<span class="text-primary">Sync</span> Client Connect</h3>
            <div class="d-inline-flex align-items-center gap-2 mt-2 px-3 py-1 bg-white border rounded-pill shadow-xs">
                <i class="bi bi-building text-primary small"></i>
                <span class="small fw-semibold text-dark">Workspace: {{ $workspace->name }}</span>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center gap-2 mb-4">
                <i class="bi bi-exclamation-triangle-fill fs-5 text-danger flex-shrink-0"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm d-flex align-items-center gap-2 mb-4">
                <i class="bi bi-check-circle-fill fs-5 text-success flex-shrink-0"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        <!-- Invitation Notice Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4 text-center">
                <h5 class="fw-bold text-dark mb-2">Connect Your Official Social Media Pages</h5>
                <p class="text-muted small mb-3">
                    Your agency manager <strong>{{ $workspace->owner?->name ?? 'Agency Team' }}</strong> is requesting access to connect your official social accounts so they can schedule, publish, and manage social media content for <strong>{{ $workspace->name }}</strong>.
                </p>
                <div class="p-3 bg-light rounded-3 d-inline-flex align-items-center gap-2 text-start text-muted small border">
                    <i class="bi bi-shield-check text-success fs-4"></i>
                    <div>
                        <strong class="text-dark d-block">100% Privacy & Security Guaranteed</strong>
                        <span>We use official OAuth 2.0 authorization. PostSync never sees, asks for, or stores your passwords.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Available Connect Options -->
        <h6 class="fw-bold text-uppercase text-muted small mb-3 px-1">Choose a Platform to Connect</h6>

        <div class="row g-3 mb-4">
            <!-- Facebook & Instagram -->
            <div class="col-12">
                <div class="card border shadow-xs hover-shadow transition">
                    <div class="card-body p-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center justify-content-center bg-primary text-white rounded-3" style="width: 48px; height: 48px;">
                                <i class="bi bi-facebook fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Facebook Pages & Instagram</h6>
                                <p class="text-muted small mb-0">Connect Facebook Pages, Reels, and linked Instagram Business accounts.</p>
                            </div>
                        </div>
                        <a href="{{ route('client-connect.oauth', ['uuid' => $workspace->uuid, 'platform' => 'facebook']) }}" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3">
                            <i class="bi bi-plug-fill"></i>
                            <span>Connect Facebook & IG</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- LinkedIn -->
            <div class="col-12">
                <div class="card border shadow-xs hover-shadow transition">
                    <div class="card-body p-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center justify-content-center text-white rounded-3" style="width: 48px; height: 48px; background-color: #0077b5;">
                                <i class="bi bi-linkedin fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">LinkedIn Company Pages & Profiles</h6>
                                <p class="text-muted small mb-0">Publish professional articles, image posts, and company updates.</p>
                            </div>
                        </div>
                        <a href="{{ route('client-connect.oauth', ['uuid' => $workspace->uuid, 'platform' => 'linkedin']) }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-2 px-3">
                            <i class="bi bi-plug-fill"></i>
                            <span>Connect LinkedIn</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- YouTube -->
            <div class="col-12">
                <div class="card border shadow-xs hover-shadow transition">
                    <div class="card-body p-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center justify-content-center bg-danger text-white rounded-3" style="width: 48px; height: 48px;">
                                <i class="bi bi-youtube fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">YouTube Channel</h6>
                                <p class="text-muted small mb-0">Direct video and Shorts publishing to your official brand channel.</p>
                            </div>
                        </div>
                        <a href="{{ route('client-connect.oauth', ['uuid' => $workspace->uuid, 'platform' => 'youtube']) }}" class="btn btn-outline-danger d-inline-flex align-items-center gap-2 px-3">
                            <i class="bi bi-plug-fill"></i>
                            <span>Connect YouTube</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Currently Active Profiles (if any) -->
        @if ($connectedProfiles->count() > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-check2-circle text-success me-2"></i>Currently Connected Accounts ({{ $connectedProfiles->count() }})
                    </h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach ($connectedProfiles as $prof)
                            <li class="list-group-item d-flex align-items-center justify-content-between p-3">
                                <div class="d-flex align-items-center gap-3">
                                    @if ($prof->avatar_url)
                                        <img src="{{ $prof->avatar_url }}" alt="{{ $prof->name }}" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center text-dark" style="width: 40px; height: 40px;">
                                            <i class="bi bi-share"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0 small">{{ $prof->name }}</h6>
                                        <span class="text-muted" style="font-size: 0.75rem;">
                                            {{ ucfirst($prof->platform->value ?? $prof->platform) }} &bull; Active & Ready
                                        </span>
                                    </div>
                                </div>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    <i class="bi bi-check-lg me-1"></i>Connected
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="text-center text-muted small mt-4">
            &copy; {{ date('Y') }} PostSync Cloud. Powered by official Meta, LinkedIn & Google APIs.
        </div>
    </div>
</body>
</html>
