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
<body class="public-site">
    <!-- Sticky Glass Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light public-navbar py-3 sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold fs-4 text-dark text-decoration-none" href="{{ route('home') }}">
                <div class="text-white rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px; background: linear-gradient(135deg, #4f46e5 0%, #6d28d9 100%);">
                    <i class="bi bi-send-fill fs-6"></i>
                </div>
                <span style="letter-spacing: -0.03em;">Post<span class="text-gradient-indigo">Sync</span></span>
            </a>

            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#publicNav" aria-controls="publicNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="publicNav">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-lg-2 fw-semibold">
                    <li class="nav-item"><a class="nav-link px-3 text-dark" href="{{ route('home') }}#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link px-3 text-dark" href="{{ route('home') }}#platforms">Platforms</a></li>
                    <li class="nav-item"><a class="nav-link px-3 text-dark" href="{{ route('home') }}#pricing">Pricing</a></li>
                    <li class="nav-item"><a class="nav-link px-3 text-dark" href="{{ route('legal.privacy') }}">Privacy</a></li>
                </ul>

                <div class="d-grid d-lg-flex align-items-center gap-2 mt-3 mt-lg-0">
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold">Sign In</a>
                    <a href="{{ route('register') }}" class="btn rounded-pill px-4 fw-semibold text-white border-0 shadow-sm" style="background: #111827;">Start Free Trial</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Modern SaaS Footer -->
    <footer class="text-white py-5 border-top" style="background-color: #0b1120;">
        <div class="container text-center text-md-start">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-2 fw-bold fs-4 text-white mb-2">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-3 text-white" style="width: 32px; height: 32px; background: linear-gradient(135deg, #4f46e5, #7c3aed);">
                            <i class="bi bi-send-fill fs-6"></i>
                        </span>
                        PostSync SaaS
                    </div>
                    <p class="small mb-0" style="color: #94a3b8;">The commercial multi-tenant social media publishing, approval, and analytics suite built for digital marketing agencies and growing brands.</p>
                </div>
                <div class="col-md-2 col-6">
                    <h6 class="text-uppercase fw-bold text-white small mb-3">Product</h6>
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2"><a href="{{ route('home') }}#features" class="text-decoration-none" style="color: #94a3b8;">Features</a></li>
                        <li class="mb-2"><a href="{{ route('home') }}#platforms" class="text-decoration-none" style="color: #94a3b8;">Platforms</a></li>
                        <li class="mb-2"><a href="{{ route('home') }}#pricing" class="text-decoration-none" style="color: #94a3b8;">Pricing</a></li>
                        <li class="mb-2"><a href="{{ route('login') }}" class="text-decoration-none" style="color: #94a3b8;">Client Portal Login</a></li>
                    </ul>
                </div>
                <div class="col-md-2 col-6">
                    <h6 class="text-uppercase fw-bold text-white small mb-3">Legal</h6>
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2"><a href="{{ route('legal.privacy') }}" class="text-decoration-none" style="color: #94a3b8;">Privacy Policy</a></li>
                        <li class="mb-2"><a href="{{ route('legal.terms') }}" class="text-decoration-none" style="color: #94a3b8;">Terms of Service</a></li>
                        <li class="mb-2"><a href="{{ route('legal.deletion') }}" class="text-decoration-none" style="color: #94a3b8;">Data Deletion</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6 class="text-uppercase fw-bold text-white small mb-3">Enterprise Trust</h6>
                    <p class="small mb-0" style="color: #94a3b8;">Isolated multi-tenant workspaces, AES-256-GCM encrypted OAuth storage, passwordless OTP authentication, and official platform APIs.</p>
                </div>
            </div>
            <hr class="my-4" style="border-color: rgba(255,255,255,0.12);">
            <div class="text-center small" style="color: #94a3b8;">
                &copy; {{ date('Y') }} PostSync SaaS (postsync.cloud). All rights reserved.
            </div>
        </div>
    </footer>
</body>
</html>

