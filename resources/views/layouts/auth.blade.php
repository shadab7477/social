<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Authentication' }} — PostSync</title>

    <!-- Google Fonts (Inter) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-light d-flex align-items-center justify-content-center py-4 px-2" style="min-height: 100dvh;">
    <div class="container px-2 px-sm-3" style="max-width: 460px;">
        <!-- Logo -->
        <div class="text-center mb-3 mb-sm-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-3 p-3 shadow-sm mb-3">
                <i class="bi bi-send-fill fs-3"></i>
            </div>
            <h3 class="fw-bold text-dark mb-1">Post<span class="text-primary">Sync</span></h3>
            <p class="text-muted small">Social Media Management SaaS for Brands & Agencies</p>
        </div>

        <!-- Auth Card -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-3 p-sm-4">
                {{ $slot ?? '' }}
                @yield('content')
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-4 text-muted small">
            &copy; {{ date('Y') }} PostSync. All rights reserved.
        </div>
    </div>

    @livewireScripts
</body>
</html>

