<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="google-site-verification" content="tuBIHo4VEXVajmCWU_MtsqRhAjhxd8HpTUrYDicrg80" />
    <title>{{ $title ?? 'PostSync SaaS' }} — All-in-One Social Media Management SaaS</title>
    @stack('meta')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-white">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom py-3 sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold fs-4 text-dark" href="/">
                <div class="bg-primary text-white rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                    <i class="bi bi-send-fill fs-6"></i>
                </div>
                Post<span class="text-primary">Sync SaaS</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#publicNav" aria-controls="publicNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="publicNav">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 fw-medium">
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="#platforms">Supported Platforms</a></li>
                    <li class="nav-item"><a class="nav-link" href="#pricing">Pricing</a></li>
                </ul>

                <div class="d-grid d-lg-flex align-items-center gap-2 mt-3 mt-lg-0">
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary px-3">Sign In</a>
                    <a href="{{ route('register') }}" class="btn btn-primary px-3">Start Free Trial</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5 border-top">
        <div class="container text-center text-md-start">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-2 fw-bold fs-4 text-white mb-2">
                        <i class="bi bi-send-fill text-primary"></i> PostSync SaaS
                    </div>
                    <p class="text-secondary small">The commercial-grade, multi-tenant social media management SaaS built for modern digital marketing agencies, enterprises, and local businesses.</p>
                </div>
                <div class="col-md-2 col-6">
                    <h6 class="text-uppercase fw-bold text-white small mb-3">Product</h6>
                    <ul class="list-unstyled text-secondary small">
                        <li class="mb-2"><a href="#composer" class="text-secondary text-decoration-none">Composer</a></li>
                        <li class="mb-2"><a href="#calendar" class="text-secondary text-decoration-none">Calendar</a></li>
                        <li class="mb-2"><a href="#inbox" class="text-secondary text-decoration-none">Social Inbox</a></li>
                        <li class="mb-2"><a href="#analytics" class="text-secondary text-decoration-none">Analytics</a></li>
                    </ul>
                </div>
                <div class="col-md-2 col-6">
                    <h6 class="text-uppercase fw-bold text-white small mb-3">Platforms</h6>
                    <ul class="list-unstyled text-secondary small">
                        <li class="mb-2">Facebook & Instagram</li>
                        <li class="mb-2">LinkedIn & YouTube</li>
                        <li class="mb-2">TikTok & Pinterest</li>
                        <li class="mb-2">X & Google Business</li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6 class="text-uppercase fw-bold text-white small mb-3">Enterprise Grade</h6>
                    <p class="text-secondary small">Multi-tenant isolation, encrypted OAuth token storage (AES-256-GCM), Redis queues, and multi-tier approval workflows.</p>
                </div>
            </div>
            <hr class="border-secondary my-4">
            <div class="text-center text-secondary small">
                &copy; {{ date('Y') }} PostSync SaaS. Developed with Laravel 11, Livewire 3 & Bootstrap 5.
            </div>
        </div>
    </footer>
</body>
</html>

