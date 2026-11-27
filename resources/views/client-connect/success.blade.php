<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Account Connected Successfully — {{ $workspace->name }} | PostSync</title>

    <!-- Google Fonts (Inter) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-light py-4 py-md-5 px-2 d-flex align-items-center" style="min-height: 100dvh;">
    <div class="container px-2 px-sm-3" style="max-width: 580px;">
        
        <div class="card border-0 shadow-sm p-4 p-md-5 text-center">
            <!-- Success Icon -->
            <div class="d-inline-flex align-items-center justify-content-center bg-success-subtle text-success rounded-circle p-4 mb-4 mx-auto" style="width: 88px; height: 88px;">
                <i class="bi bi-check-circle-fill display-4"></i>
            </div>

            <h3 class="fw-bold text-dark mb-2">Connection Successful!</h3>
            <p class="text-muted mb-4">
                Your social media profile(s) have been securely linked to <strong>{{ $workspace->name }}</strong>. 
                Your agency manager <strong>{{ $workspace->owner?->name ?? 'Agency Team' }}</strong> can now compose, schedule, and publish content for your brand.
            </p>

            @if(session('success'))
                <div class="alert alert-success border-0 shadow-xs small mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- List of currently connected profiles -->
            @if ($connectedProfiles->count() > 0)
                <div class="card bg-light border text-start mb-4">
                    <div class="card-header bg-transparent py-2 border-bottom">
                        <span class="small fw-bold text-dark text-uppercase">Active Profiles for this Workspace ({{ $connectedProfiles->count() }})</span>
                    </div>
                    <ul class="list-group list-group-flush bg-transparent">
                        @foreach ($connectedProfiles as $prof)
                            <li class="list-group-item bg-transparent d-flex align-items-center justify-content-between py-2">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-check2 text-success fw-bold"></i>
                                    <span class="fw-semibold text-dark small">{{ $prof->name }}</span>
                                    <span class="badge bg-secondary-subtle text-dark border" style="font-size: 0.7rem;">{{ ucfirst($prof->platform->value ?? $prof->platform) }}</span>
                                </div>
                                <span class="badge bg-success-subtle text-success small">Ready</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                <a href="{{ route('client-connect.show', ['uuid' => $workspace->uuid]) }}" class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-plus-circle"></i>
                    <span>Connect Another Account</span>
                </a>
            </div>

            <p class="text-muted small mt-4 mb-0">
                You can safely close this browser window or tab.
            </p>
        </div>

        <div class="text-center text-muted small mt-4">
            &copy; {{ date('Y') }} PostSync Cloud. Powered by official Meta, LinkedIn & Google APIs.
        </div>
    </div>
</body>
</html>

