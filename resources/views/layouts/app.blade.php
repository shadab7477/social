<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'SocialPilotX') }} — Social Media Management SaaS</title>

    <!-- Google Fonts (Inter) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles & Scripts via Vite -->
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body>
    <div class="app-container">
        <!-- Sidebar Navigation -->
        <aside class="app-sidebar">
            <div class="sidebar-brand d-flex align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <div class="brand-icon">
                        <i class="bi bi-send-fill"></i>
                    </div>
                    <div class="brand-name">
                        SocialPilot<span class="text-primary">X</span>
                    </div>
                </div>
                <button class="btn-close d-lg-none ms-auto" type="button" onclick="toggleMobileSidebar()" aria-label="Close"></button>
            </div>

            <div class="sidebar-menu">
                <div class="menu-header">Publishing & Content</div>
                <nav class="nav flex-column">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-grid-1x2"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('composer') }}" class="nav-link {{ request()->routeIs('composer') ? 'active' : '' }}">
                        <i class="bi bi-pencil-square"></i>
                        <span>Create Post</span>
                    </a>
                    <a href="{{ route('calendar') }}" class="nav-link {{ request()->routeIs('calendar') ? 'active' : '' }}">
                        <i class="bi bi-calendar3"></i>
                        <span>Calendar</span>
                    </a>
                    <a href="{{ route('posts.index') }}" class="nav-link {{ request()->routeIs('posts.*') ? 'active' : '' }}">
                        <i class="bi bi-collection"></i>
                        <span>Posts Directory</span>
                    </a>
                    <a href="{{ route('media.index') }}" class="nav-link {{ request()->routeIs('media.*') ? 'active' : '' }}">
                        <i class="bi bi-images"></i>
                        <span>Media Library</span>
                    </a>
                </nav>

                <div class="menu-header mt-3">Engagement & Audience</div>
                <nav class="nav flex-column">
                    <a href="{{ route('social-accounts.index') }}" class="nav-link {{ request()->routeIs('social-accounts.*') ? 'active' : '' }}">
                        <i class="bi bi-share"></i>
                        <span>Social Accounts</span>
                    </a>
                    <a href="{{ route('inbox.index') }}" class="nav-link {{ request()->routeIs('inbox.*') ? 'active' : '' }}">
                        <i class="bi bi-chat-dots"></i>
                        <span>Social Inbox</span>
                    </a>
                    <a href="{{ route('analytics.index') }}" class="nav-link {{ request()->routeIs('analytics.*') ? 'active' : '' }}">
                        <i class="bi bi-graph-up"></i>
                        <span>Analytics</span>
                    </a>
                    <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-bar-graph"></i>
                        <span>Client Reports</span>
                    </a>
                </nav>

                <div class="menu-header mt-3">Agency & Collaboration</div>
                <nav class="nav flex-column">
                    <a href="{{ route('clients.index') }}" class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                        <i class="bi bi-building"></i>
                        <span>Clients</span>
                    </a>
                    <a href="{{ route('approvals.index') }}" class="nav-link {{ request()->routeIs('approvals.*') ? 'active' : '' }}">
                        <i class="bi bi-check2-circle"></i>
                        <span>Approvals</span>
                    </a>
                    <a href="{{ route('team.index') }}" class="nav-link {{ request()->routeIs('team.*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i>
                        <span>Team & Roles</span>
                    </a>
                    <a href="{{ route('automations.index') }}" class="nav-link {{ request()->routeIs('automations.*') ? 'active' : '' }}">
                        <i class="bi bi-cpu"></i>
                        <span>Automations</span>
                    </a>
                </nav>

                <div class="menu-header mt-3">Administration</div>
                <nav class="nav flex-column">
                    <a href="{{ route('billing.index') }}" class="nav-link {{ request()->routeIs('billing.*') ? 'active' : '' }}">
                        <i class="bi bi-credit-card"></i>
                        <span>Billing & Plans</span>
                    </a>
                    <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <i class="bi bi-gear"></i>
                        <span>Settings & Audit</span>
                    </a>
                </nav>

                @auth
                    @if (auth()->user()->is_super_admin)
                        <div class="menu-header mt-3 text-danger d-flex align-items-center justify-content-between">
                            <span><i class="bi bi-shield-lock-fill me-1"></i> Governance</span>
                            <span class="badge bg-danger" style="font-size: 0.6rem;">SUPER ADMIN</span>
                        </div>
                        <nav class="nav flex-column">
                            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }} text-danger fw-semibold">
                                <i class="bi bi-speedometer2 text-danger"></i>
                                <span>Admin Panel</span>
                            </a>
                        </nav>
                    @endif
                @endauth
            </div>

            <div class="sidebar-footer">
                <div class="d-flex align-items-center justify-content-between p-2 rounded bg-light">
                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.85rem; font-weight: 600;">
                            {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                        </div>
                        <div class="text-truncate" style="max-width: 130px;">
                            <div class="fw-semibold text-truncate small">{{ auth()->user()->name ?? 'Demo User' }}</div>
                            <div class="text-muted text-truncate" style="font-size: 0.75rem;">{{ auth()->user()->email ?? 'demo@socialpilotx.com' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Mobile Sidebar Backdrop Overlay -->
        <div class="sidebar-backdrop d-lg-none" id="sidebarBackdrop" onclick="toggleMobileSidebar()"></div>

        <!-- Main Content Area -->
        <div class="app-content">
            <!-- Top Navbar -->
            <header class="app-header">
                <div class="d-flex align-items-center gap-2 gap-sm-3">
                    <button class="btn btn-sm btn-outline-secondary d-lg-none" type="button" onclick="toggleMobileSidebar()" aria-label="Toggle Navigation">
                        <i class="bi bi-list fs-5"></i>
                    </button>

                    <!-- Active Workspace Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-light border text-dark dropdown-toggle d-flex align-items-center gap-1 gap-sm-2 fw-medium" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-briefcase-fill text-primary"></i>
                            <span class="text-truncate d-inline-block" style="max-width: 130px;">{{ session('active_workspace_name', 'Select Workspace') }}</span>
                        </button>
                        <ul class="dropdown-menu shadow-sm">
                            <li class="dropdown-header text-uppercase small fw-bold">Switch Workspace</li>
                            @auth
                                @foreach(auth()->user()->workspaces as $ws)
                                    <li>
                                        <form method="POST" action="{{ route('workspace.switch', $ws->id) }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item d-flex align-items-center justify-content-between {{ $ws->id == session('active_workspace_id') ? 'active fw-bold' : '' }}">
                                                <span>{{ $ws->name }}</span>
                                                @if($ws->id == session('active_workspace_id'))
                                                    <i class="bi bi-check2"></i>
                                                @endif
                                            </button>
                                        </form>
                                    </li>
                                @endforeach
                            @endauth
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('settings.index') }}"><i class="bi bi-plus-circle me-2"></i>Manage Workspaces</a></li>
                        </ul>
                    </div>

                    @if(isset($headerTitle))
                        <div class="vr mx-2"></div>
                        <span class="fs-6 fw-semibold text-secondary">{{ $headerTitle }}</span>
                    @endif
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('composer') }}" class="btn btn-sm btn-primary d-none d-sm-inline-flex align-items-center gap-2">
                        <i class="bi bi-plus-lg"></i>
                        <span>New Post</span>
                    </a>

                    <!-- User Profile Dropdown -->
                    <div class="dropdown ms-2">
                        <button class="btn btn-link p-0 text-decoration-none dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px; font-weight: 600;">
                                {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                            </div>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li class="dropdown-header">
                                <div class="fw-bold">{{ auth()->user()->name ?? 'Guest User' }}</div>
                                <div class="text-muted small">{{ auth()->user()->email ?? 'guest@socialpilotx.com' }}</div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('settings.index') }}"><i class="bi bi-person me-2"></i>My Profile</a></li>
                            <li><a class="dropdown-item" href="{{ route('billing.index') }}"><i class="bi bi-credit-card me-2"></i>Subscription</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i>Sign Out
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Main Body -->
            <main class="main-body">
                <!-- Flash Alerts -->
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                        <div>{{ session('success') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        <div>{{ session('error') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                        <i class="bi bi-exclamation-circle-fill fs-5"></i>
                        <div>{{ session('warning') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Page Content Slot -->
                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>
    </div>

    @livewireScripts
    @stack('scripts')
    <script>
        function toggleMobileSidebar() {
            const sidebar = document.querySelector('.app-sidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            if (sidebar && backdrop) {
                sidebar.classList.toggle('show-mobile');
                backdrop.classList.toggle('show');
                document.body.classList.toggle('overflow-hidden');
            }
        }
    </script>
</body>
</html>

