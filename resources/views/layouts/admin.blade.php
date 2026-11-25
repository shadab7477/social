<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Super Admin Control Center' }} — PostSync Platform</title>

    <!-- Google Fonts (Inter + JetBrains Mono) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">

    <!-- Styles & Scripts via Vite -->
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    @livewireStyles

    <style>
        :root {
            --admin-bg: #090d16;
            --admin-card: rgba(18, 24, 38, 0.85);
            --admin-border: rgba(255, 255, 255, 0.08);
            --admin-accent: #6366f1;
            --admin-accent-glow: rgba(99, 102, 241, 0.25);
            --admin-danger: #ef4444;
            --admin-success: #10b981;
            --admin-warning: #f59e0b;
        }

        body.admin-body {
            background-color: var(--admin-bg);
            background-image: 
                radial-gradient(at 10% 20%, rgba(99, 102, 241, 0.12) 0px, transparent 40%),
                radial-gradient(at 90% 10%, rgba(239, 68, 68, 0.08) 0px, transparent 40%),
                radial-gradient(at 50% 90%, rgba(16, 185, 129, 0.08) 0px, transparent 40%);
            background-attachment: fixed;
            color: #f1f5f9;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }

        .admin-shell {
            display: flex;
            min-height: 100vh;
        }

        /* Dedicated Admin Sidebar */
        .admin-sidebar {
            width: 280px;
            background: rgba(13, 17, 28, 0.95);
            backdrop-filter: blur(20px);
            border-right: 1px solid var(--admin-border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1040;
            padding: 1.5rem 1rem;
            transition: all 0.3s ease;
        }

        .admin-brand {
            padding: 0.5rem 0.75rem 1.5rem;
            border-bottom: 1px solid var(--admin-border);
            margin-bottom: 1.5rem;
        }

        .admin-brand-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.25rem;
            box-shadow: 0 4px 14px rgba(239, 68, 68, 0.4);
        }

        .admin-nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            color: #94a3b8;
            font-weight: 500;
            font-size: 0.88rem;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            border: 1px solid transparent;
            margin-bottom: 0.35rem;
            background: transparent;
            width: 100%;
            text-align: left;
        }

        .admin-nav-item:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.05);
        }

        .admin-nav-item.active {
            color: #fff;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.2), rgba(99, 102, 241, 0.08));
            border-color: rgba(99, 102, 241, 0.4);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.15);
            font-weight: 600;
        }

        .admin-nav-item.active i {
            color: #818cf8;
        }

        /* Content Area */
        .admin-main {
            flex: 1;
            margin-left: 280px;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .admin-topbar {
            height: 72px;
            background: rgba(13, 17, 28, 0.8);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--admin-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        .admin-content {
            padding: 2rem;
            flex: 1;
        }

        /* Glass Cards */
        .admin-card {
            background: var(--admin-card);
            backdrop-filter: blur(16px);
            border: 1px solid var(--admin-border);
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.37);
        }

        .admin-metric-card {
            background: rgba(18, 24, 38, 0.7);
            border: 1px solid var(--admin-border);
            border-radius: 16px;
            padding: 1.25rem;
            transition: transform 0.2s ease, border-color 0.2s ease;
        }

        .admin-metric-card:hover {
            transform: translateY(-2px);
            border-color: rgba(99, 102, 241, 0.3);
        }

        .pulse-live {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 10px #10b981;
            animation: pulse-dot 2s infinite;
        }

        @keyframes pulse-dot {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        .mono {
            font-family: 'JetBrains Mono', monospace;
        }

        @media (max-width: 991.98px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }
            .admin-sidebar.show {
                transform: translateX(0);
            }
            .admin-main {
                margin-left: 0;
            }
        }
    </style>
</head>
<body class="admin-body">
    <div class="admin-shell">
        <!-- Dedicated Master Admin Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="admin-brand d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="admin-brand-icon">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-white fs-6 lh-1">PostSync</div>
                        <div class="small text-danger fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">MASTER CONTROL</div>
                    </div>
                </div>
                <button class="btn-close btn-close-white d-lg-none" type="button" onclick="document.getElementById('adminSidebar').classList.remove('show')"></button>
            </div>

            <!-- Server Status Widget -->
            <div class="p-2 mb-3 rounded-3" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.06);">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="small text-muted" style="font-size: 0.75rem;">PLATFORM STATUS</span>
                    <span class="d-flex align-items-center gap-1 small text-success fw-bold" style="font-size: 0.75rem;">
                        <span class="pulse-live"></span> ONLINE
                    </span>
                </div>
                <div class="d-flex justify-content-between small text-muted mono" style="font-size: 0.72rem;">
                    <span>PHP {{ PHP_VERSION }}</span>
                    <span>Laravel 11</span>
                </div>
            </div>

            <!-- Navigation Links (Livewire Tab Switchers) -->
            <div class="flex-grow-1">
                <div class="text-uppercase small text-muted fw-bold px-2 mb-2" style="font-size: 0.68rem; letter-spacing: 0.8px;">Governance</div>

                <a href="#overview" class="admin-nav-item" onclick="Livewire.dispatch('set-admin-tab', { tab: 'overview' })">
                    <i class="bi bi-speedometer2"></i>
                    <span>Master Overview</span>
                </a>

                <a href="#users" class="admin-nav-item" onclick="Livewire.dispatch('set-admin-tab', { tab: 'users' })">
                    <i class="bi bi-people-fill"></i>
                    <span>Users & Admins</span>
                </a>

                <a href="#workspaces" class="admin-nav-item" onclick="Livewire.dispatch('set-admin-tab', { tab: 'workspaces' })">
                    <i class="bi bi-buildings-fill"></i>
                    <span>Agency Workspaces</span>
                </a>

                <a href="#social" class="admin-nav-item" onclick="Livewire.dispatch('set-admin-tab', { tab: 'social_profiles' })">
                    <i class="bi bi-share-fill"></i>
                    <span>Connected Socials</span>
                </a>

                <a href="#posts" class="admin-nav-item" onclick="Livewire.dispatch('set-admin-tab', { tab: 'posts' })">
                    <i class="bi bi-send-check-fill"></i>
                    <span>Publishing & Queue</span>
                </a>

                <div class="text-uppercase small text-muted fw-bold px-2 mt-3 mb-2" style="font-size: 0.68rem; letter-spacing: 0.8px;">Maintenance</div>

                <a href="#diagnostics" class="admin-nav-item" onclick="Livewire.dispatch('set-admin-tab', { tab: 'diagnostics' })">
                    <i class="bi bi-cpu-fill"></i>
                    <span>Diagnostics & Tools</span>
                </a>
            </div>

            <!-- Exit to Workspace link -->
            <div class="pt-3 border-top" style="border-color: var(--admin-border) !important;">
                <a href="{{ route('dashboard') }}" class="btn btn-outline-light btn-sm w-100 d-flex align-items-center justify-content-center gap-2 rounded-3 py-2">
                    <i class="bi bi-arrow-left"></i>
                    <span>Exit to Workspace</span>
                </a>
            </div>
        </aside>

        <!-- Main Wrapper -->
        <div class="admin-main">
            <!-- Topbar -->
            <header class="admin-topbar">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-dark d-lg-none p-1 px-2 border" type="button" onclick="document.getElementById('adminSidebar').classList.add('show')">
                        <i class="bi bi-list fs-5"></i>
                    </button>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-danger text-white rounded-pill px-2 py-1 small" style="font-size: 0.72rem;">SUPER ADMIN</span>
                            <span class="text-muted small">|</span>
                            <span class="text-white small fw-medium">Global Multi-Tenant Control Hub</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <!-- Quick System Actions -->
                    <button onclick="Livewire.dispatch('trigger-cron')" class="btn btn-sm btn-outline-warning d-inline-flex align-items-center gap-1 rounded-pill px-3" title="Trigger Background Scheduler">
                        <i class="bi bi-clock-history"></i>
                        <span class="d-none d-md-inline">Run Cron</span>
                    </button>

                    <button onclick="Livewire.dispatch('clear-cache')" class="btn btn-sm btn-outline-info d-inline-flex align-items-center gap-1 rounded-pill px-3" title="Clear Application Cache">
                        <i class="bi bi-lightning-charge"></i>
                        <span class="d-none d-md-inline">Flush Cache</span>
                    </button>

                    <!-- Super Admin User Profile Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-dark d-flex align-items-center gap-2 rounded-pill px-3 py-1 border" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 0.8rem; font-weight: bold;">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <span class="small fw-semibold text-white d-none d-sm-inline">{{ auth()->user()->name }}</span>
                            <i class="bi bi-chevron-down text-muted small"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border p-2" style="background: #141b2d; min-width: 220px; border-radius: 14px;">
                            <li class="px-3 py-2 border-bottom mb-2" style="border-color: rgba(255,255,255,0.08) !important;">
                                <div class="fw-bold text-white small">{{ auth()->user()->name }}</div>
                                <div class="text-muted small text-truncate" style="font-size: 0.75rem;">{{ auth()->user()->email }}</div>
                                <span class="badge bg-danger text-white mt-1" style="font-size: 0.65rem;">Platform Super Admin</span>
                            </li>
                            <li>
                                <a class="dropdown-item rounded py-2 small text-light" href="{{ route('dashboard') }}">
                                    <i class="bi bi-grid me-2 text-primary"></i> Workspace Dashboard
                                </a>
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item rounded py-2 small text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i> Log Out
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Admin Body Content -->
            <main class="admin-content">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
