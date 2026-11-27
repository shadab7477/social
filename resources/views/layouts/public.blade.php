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
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,600;12..96,700;12..96,800&family=Geist+Mono:wght@400;500;600&family=Geist:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="public-site">
    <!-- Sticky Architectural Header (responsive popover + adaptive wide bar) -->
    <header class="site-header" id="siteHeader">
        <div class="ps-shell header-inner">
            <a class="site-logo" href="{{ route('home') }}">
                <span class="site-logo-mark" aria-hidden="true">
                    <i class="bi bi-send-fill"></i>
                </span>
                <span>PostSync<span class="ps-dot">.</span></span>
            </a>

            <nav class="site-nav" aria-label="Primary">
                <button
                    class="menu-button"
                    id="siteMenuBtn"
                    type="button"
                    popovertarget="site-menu"
                    popovertargetaction="toggle"
                    aria-controls="site-menu"
                    aria-expanded="false"
                >
                    <i class="bi bi-list fs-6" aria-hidden="true"></i>
                    <span>Menu</span>
                </button>

                <ul id="site-menu" class="site-menu" popover="auto">
                    <li><a class="nav-anchor" href="{{ route('home') }}#workflow">Workflow</a></li>
                    <li><a class="nav-anchor" href="{{ route('home') }}#features">Features</a></li>
                    <li><a class="nav-anchor" href="{{ route('home') }}#platforms">Platforms</a></li>
                    <li><a class="nav-anchor" href="{{ route('home') }}#pricing">Pricing</a></li>
                    <li><a class="nav-anchor" href="{{ route('home') }}#faq">FAQ</a></li>
                    <li>
                        <a
                            class="nav-anchor"
                            href="{{ route('legal.privacy') }}"
                            @if (request()->routeIs('legal.privacy')) aria-current="page" @endif
                        >
                            Privacy
                        </a>
                    </li>
                    <li class="nav-actions-item">
                        <a href="{{ route('login') }}" class="ps-btn-outline ps-nav-cta py-2 px-3" style="font-size: 0.85rem;">Sign In</a>
                        <a href="{{ route('register') }}" class="ps-btn-dark ps-nav-cta py-2 px-3" style="font-size: 0.85rem;">Start Free Trial</a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Conversion CTA + Dark Warm-Aura Footer -->
    <footer class="ps-footer-band">
        <div class="ps-footer-glow" aria-hidden="true"></div>

        <div class="ps-footer-cta">
            <div class="ps-shell text-center">
                <span class="ps-mono text-uppercase d-inline-block mb-3 px-3 py-1 rounded-pill" style="font-size: 0.72rem; letter-spacing: 0.08em; border: 1px solid rgba(255,255,255,0.2); background: rgba(255,255,255,0.06); color: #ffb399;">
                    Ready in Under 5 Minutes
                </span>
                <h2 class="text-white mx-auto mb-3" style="font-size: clamp(2rem, 1.4rem + 2.5vw, 3.35rem); font-weight: 700; max-inline-size: 18ch;">
                    Ready to scale your client workflows<span class="ps-dot">.</span>
                </h2>
                <p class="mx-auto mb-4" style="color: rgba(255, 255, 255, 0.72); max-inline-size: 52ch; font-size: 1rem;">
                    Launch your 14-day free agency trial or invite your first client into a zero-password approval portal today.
                </p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="{{ route('register') }}" class="ps-btn-primary">
                        <span>Start 14-Day Free Trial</span>
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-outline-light px-4 py-2 d-inline-flex align-items-center fw-semibold" style="border-radius: 10px; border-color: rgba(255,255,255,0.28);">
                        Client Portal Login
                    </a>
                </div>
            </div>
        </div>

        <div class="ps-shell py-5 position-relative" style="z-index: 2;">
            <div class="row g-4 text-start">
                <div class="col-12 col-md-4">
                    <div class="d-flex align-items-center gap-2 fw-bold fs-5 text-white mb-2 ps-display">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-2" style="width: 30px; height: 30px; background: var(--ps-orange); color: #fff;">
                            <i class="bi bi-send-fill" style="font-size: 0.85rem;"></i>
                        </span>
                        PostSync SaaS
                    </div>
                    <p class="small mb-0" style="color: rgba(255, 255, 255, 0.64); max-inline-size: 38ch;">
                        The commercial multi-tenant social media publishing, client approval, and reporting suite built for digital marketing agencies and modern brands.
                    </p>
                </div>

                <div class="col-6 col-md-2">
                    <h3 class="ps-mono text-uppercase text-white small fw-semibold mb-3" style="font-size: 0.72rem; letter-spacing: 0.08em;">Product</h3>
                    <ul class="list-unstyled small mb-0 d-flex flex-column gap-2">
                        <li><a href="{{ route('home') }}#workflow" class="text-decoration-none" style="color: rgba(255, 255, 255, 0.68);">Workflow</a></li>
                        <li><a href="{{ route('home') }}#features" class="text-decoration-none" style="color: rgba(255, 255, 255, 0.68);">Features</a></li>
                        <li><a href="{{ route('home') }}#platforms" class="text-decoration-none" style="color: rgba(255, 255, 255, 0.68);">Platforms</a></li>
                        <li><a href="{{ route('home') }}#pricing" class="text-decoration-none" style="color: rgba(255, 255, 255, 0.68);">Pricing</a></li>
                    </ul>
                </div>

                <div class="col-6 col-md-2">
                    <h3 class="ps-mono text-uppercase text-white small fw-semibold mb-3" style="font-size: 0.72rem; letter-spacing: 0.08em;">Legal</h3>
                    <ul class="list-unstyled small mb-0 d-flex flex-column gap-2">
                        <li><a href="{{ route('legal.privacy') }}" class="text-decoration-none" style="color: rgba(255, 255, 255, 0.68);">Privacy Policy</a></li>
                        <li><a href="{{ route('legal.terms') }}" class="text-decoration-none" style="color: rgba(255, 255, 255, 0.68);">Terms of Service</a></li>
                        <li><a href="{{ route('legal.deletion') }}" class="text-decoration-none" style="color: rgba(255, 255, 255, 0.68);">Data Deletion</a></li>
                        <li><a href="{{ route('login') }}" class="text-decoration-none" style="color: rgba(255, 255, 255, 0.68);">Gmail OTP Sign In</a></li>
                    </ul>
                </div>

                <div class="col-12 col-md-4">
                    <h3 class="ps-mono text-uppercase text-white small fw-semibold mb-3" style="font-size: 0.72rem; letter-spacing: 0.08em;">Enterprise Trust</h3>
                    <p class="small mb-0" style="color: rgba(255, 255, 255, 0.64);">
                        Isolated multi-tenant workspaces, AES-256-GCM encrypted OAuth storage, passwordless Gmail OTP authentication, and official platform APIs.
                    </p>
                </div>
            </div>

            <hr class="my-4" style="border-color: rgba(255, 255, 255, 0.14);">

            <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2 small" style="color: rgba(255, 255, 255, 0.56);">
                <span>&copy; {{ date('Y') }} PostSync SaaS (postsync.cloud). All rights reserved.</span>
                <span class="ps-mono" style="font-size: 0.74rem;">10 Official Networks &bull; AES-256-GCM &bull; Multi-Tenant Ready</span>
            </div>
        </div>
    </footer>

    <script>
        (function () {
            const header = document.getElementById('siteHeader');
            const menuBtn = document.getElementById('siteMenuBtn');
            const menu = document.getElementById('site-menu');
            if (!header || !menu) return;

            const inner = header.querySelector('.header-inner');
            const supportsPopover = Object.hasOwn(HTMLElement.prototype, 'popover');
            let lastWidth = 0;

            if (!supportsPopover && menuBtn) {
                menu.removeAttribute('popover');
                menuBtn.addEventListener('click', function () {
                    const isOpen = menu.classList.toggle('is-fallback-open');
                    menuBtn.setAttribute('aria-expanded', String(isOpen));
                });
            } else if (menuBtn) {
                menu.addEventListener('toggle', function (event) {
                    menuBtn.setAttribute('aria-expanded', String(event.newState === 'open'));
                });
            }

            function syncNavLayout(forceMeasure) {
                const headerWidth = header.clientWidth;
                const isWideNow = header.classList.contains('is-wide');
                if (!forceMeasure && !isWideNow && headerWidth <= lastWidth) {
                    lastWidth = headerWidth;
                    return;
                }
                lastWidth = headerWidth;

                const wasOpen = supportsPopover ? menu.matches(':popover-open') : menu.classList.contains('is-fallback-open');

                header.classList.add('is-measuring', 'is-wide');
                menu.removeAttribute('popover');
                menu.classList.remove('is-fallback-open');

                const fitsInline = inner.scrollWidth <= inner.clientWidth + 1 && window.innerWidth >= 992;

                header.classList.toggle('is-wide', fitsInline);

                if (!fitsInline) {
                    if (supportsPopover) {
                        menu.setAttribute('popover', 'auto');
                        if (wasOpen) {
                            try { menu.showPopover(); } catch (_) {}
                        }
                    } else if (wasOpen) {
                        menu.classList.add('is-fallback-open');
                    }
                } else if (menuBtn) {
                    menuBtn.setAttribute('aria-expanded', 'false');
                }

                requestAnimationFrame(function () {
                    header.classList.remove('is-measuring');
                });
            }

            if (typeof ResizeObserver !== 'undefined') {
                const ro = new ResizeObserver(function () { syncNavLayout(true); });
                ro.observe(header);
            } else {
                window.addEventListener('resize', function () { syncNavLayout(true); });
            }

            if (document.fonts && document.fonts.ready) {
                document.fonts.ready.then(function () { syncNavLayout(true); });
            }
            syncNavLayout(true);

            // Close mobile popover when clicking any navigation link
            menu.querySelectorAll('a').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (header.classList.contains('is-wide')) return;
                    if (supportsPopover && menu.matches(':popover-open')) {
                        try { menu.hidePopover(); } catch (_) {}
                    } else {
                        menu.classList.remove('is-fallback-open');
                        if (menuBtn) menuBtn.setAttribute('aria-expanded', 'false');
                    }
                });
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
