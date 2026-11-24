<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Authentication' }} — SocialPilotX</title>

    <!-- Google Fonts (Inter) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100 py-5">
    <div class="container" style="max-width: 460px;">
        <!-- Logo -->
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-3 p-3 shadow-sm mb-3">
                <i class="bi bi-send-fill fs-3"></i>
            </div>
            <h3 class="fw-bold text-dark mb-1">SocialPilot<span class="text-primary">X</span></h3>
            <p class="text-muted small">Commercial Social Media SaaS for Agencies & Businesses</p>
        </div>

        <!-- Auth Card -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                {{ $slot ?? '' }}
                @yield('content')
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-4 text-muted small">
            &copy; {{ date('Y') }} SocialPilotX. All rights reserved.
        </div>
    </div>

    @livewireScripts
</body>
</html>

