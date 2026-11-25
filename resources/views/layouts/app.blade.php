<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="google-site-verification" content="tuBIHo4VEXVajmCWU_MtsqRhAjhxd8HpTUrYDicrg80" />

    <title>{{ $title ?? config('app.name', 'PostSync') }} — Social Media Management SaaS</title>

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
        <!-- Floating Rounded Sidebar (Reference Design) -->
        <aside class="app-sidebar">
            <div class="sidebar-brand d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="brand-icon">
                        <i class="bi bi-send-fill"></i>
                    </div>
                    <div>
                        <div class="brand-name lh-1">
                            Post<span>Sync</span>
                        </div>
                    </div>
                </div>
                <button class="btn-close d-lg-none" type="button" onclick="toggleMobileSidebar()" aria-label="Close"></button>
            </div>

            <!-- Workspace Switcher Pill inside Sidebar -->
            <div class="px-3 pt-1 pb-2">
                <div class="dropdown">
                    <button class="btn btn-sm w-100 text-start d-flex align-items-center justify-content-between p-2 rounded-3 border bg-light text-dark" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.8rem;">
                        <div class="d-flex align-items-center gap-2 text-truncate">
                            <span class="pulse-online"></span>
                            <div class="text-truncate">
                                <div class="text-dark small fw-bold text-truncate" style="max-width: 135px;">
                                    {{ session('active_workspace_name', 'Workspace') }}
                                </div>
                            </div>
                        </div>
                        <i class="bi bi-chevron-expand text-muted small"></i>
                    </button>
                    <ul class="dropdown-menu shadow border-0 p-2" style="min-width: 220px; border-radius: 16px;">
                        <li class="dropdown-header text-uppercase small text-muted fw-bold">Switch Workspace</li>
                        @auth
                            @foreach(auth()->user()->workspaces as $ws)
                                <li>
                                    <form method="POST" action="{{ route('workspace.switch', $ws->id) }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item d-flex align-items-center justify-content-between rounded py-2 {{ $ws->id == session('active_workspace_id') ? 'active fw-bold' : '' }}">
                                            <span class="text-truncate">{{ $ws->name }}</span>
                                            @if($ws->id == session('active_workspace_id'))
                                                <i class="bi bi-check2 text-primary"></i>
                                            @endif
                                        </button>
                                    </form>
                                </li>
                            @endforeach
                        @endauth
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <button type="button" class="dropdown-item text-primary fw-semibold d-flex align-items-center py-2 rounded" data-bs-toggle="modal" data-bs-target="#createWorkspaceModal">
                                <i class="bi bi-plus-circle me-2"></i>+ New Client Workspace
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="sidebar-menu">
                <nav class="nav flex-column">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-grid-fill"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('calendar') }}" class="nav-link {{ request()->routeIs('calendar') ? 'active' : '' }}">
                        <i class="bi bi-calendar3"></i>
                        <span>Calendar</span>
                    </a>
                    <a href="{{ route('posts.index') }}" class="nav-link {{ request()->routeIs('posts.*') ? 'active' : '' }}">
                        <i class="bi bi-check2-square"></i>
                        <span>My Posts</span>
                    </a>
                    <a href="{{ route('analytics.index') }}" class="nav-link {{ request()->routeIs('analytics.*') ? 'active' : '' }}">
                        <i class="bi bi-graph-up"></i>
                        <span>Analytics</span>
                    </a>
                    <a href="{{ route('media.index') }}" class="nav-link {{ request()->routeIs('media.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-text"></i>
                        <span>Media & Docs</span>
                    </a>
                </nav>

                <div class="menu-header">INTEGRATIONS</div>
                <nav class="nav flex-column">
                    <a href="{{ route('social-accounts.index') }}" class="nav-link {{ request()->routeIs('social-accounts.*') ? 'active' : '' }}">
                        <i class="bi bi-share"></i>
                        <span>Social Accounts</span>
                    </a>
                    <a href="{{ route('inbox.index') }}" class="nav-link {{ request()->routeIs('inbox.*') ? 'active' : '' }}">
                        <i class="bi bi-chat-dots"></i>
                        <span>Social Inbox</span>
                    </a>
                    <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-bar-graph"></i>
                        <span>Client Reports</span>
                    </a>
                </nav>

                <div class="menu-header">TEAMS & CLIENTS</div>
                <nav class="nav flex-column">
                    <a href="{{ route('clients.index') }}" class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                        <i class="bi bi-buildings"></i>
                        <span>Clients</span>
                    </a>
                    <a href="{{ route('approvals.index') }}" class="nav-link {{ request()->routeIs('approvals.*') ? 'active' : '' }}">
                        <i class="bi bi-check-circle"></i>
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

                <div class="menu-header">SETTINGS</div>
                <nav class="nav flex-column">
                    <a href="{{ route('billing.index') }}" class="nav-link {{ request()->routeIs('billing.*') ? 'active' : '' }}">
                        <i class="bi bi-credit-card"></i>
                        <span>Billing & Plans</span>
                    </a>
                    <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <i class="bi bi-gear"></i>
                        <span>Settings</span>
                    </a>
                </nav>

                @auth
                    @if (auth()->user()->is_super_admin)
                        <div class="menu-header text-danger">SUPER ADMIN</div>
                        <nav class="nav flex-column">
                            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }} text-danger">
                                <i class="bi bi-speedometer2 text-danger"></i>
                                <span>Admin Panel</span>
                            </a>
                        </nav>
                    @endif
                @endauth
            </div>

            <div class="sidebar-footer">
                <div class="d-flex align-items-center justify-content-between p-2 rounded-4 bg-light border">
                    <div class="d-flex align-items-center gap-2 text-truncate">
                        <div class="avatar text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 34px; height: 34px; font-size: 0.82rem; background: #111827;">
                            {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                        </div>
                        <div class="text-truncate" style="max-width: 120px;">
                            <div class="fw-bold text-dark text-truncate small">{{ auth()->user()->name ?? 'User' }}</div>
                            <div class="text-muted text-truncate" style="font-size: 0.7rem;">{{ auth()->user()->email ?? 'user@postsync.cloud' }}</div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-link text-muted p-1 text-decoration-none" title="Sign Out">
                            <i class="bi bi-box-arrow-right fs-6"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Mobile Sidebar Backdrop Overlay -->
        <div class="sidebar-backdrop d-lg-none" id="sidebarBackdrop" onclick="toggleMobileSidebar()"></div>

        <!-- Main Content Area -->
        <div class="app-content">
            <!-- Top Navbar (Reference Design) -->
            <header class="app-header">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-outline-secondary d-lg-none rounded-circle header-circle-btn" type="button" onclick="toggleMobileSidebar()" aria-label="Toggle Navigation">
                        <i class="bi bi-list fs-5"></i>
                    </button>
                    <div>
                        <h3 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 1.65rem; letter-spacing: -0.02em;">
                            Welcome back <span class="fs-4">👋</span>
                        </h3>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 gap-sm-3">
                    <!-- Search Pill Input -->
                    <div class="search-pill-box d-none d-md-flex">
                        <i class="bi bi-search text-muted small"></i>
                        <input type="text" placeholder="Search something...">
                    </div>

                    <!-- Client Link Pill -->
                    @php
                        $currWs = app('current_workspace');
                    @endphp
                    @if ($currWs)
                        <a href="{{ route('social-accounts.index') }}" class="btn btn-sm btn-white bg-white border text-dark rounded-pill px-3 py-2 d-none d-sm-inline-flex align-items-center gap-1 shadow-sm" style="font-size: 0.82rem; font-weight: 600;">
                            <i class="bi bi-link-45deg fs-6"></i>
                            <span>🔗 Share Client Link</span>
                        </a>
                    @endif

                    <!-- + Create Black Pill Button -->
                    <a href="{{ route('composer') }}" class="btn-black-pill text-decoration-none">
                        <i class="bi bi-plus-lg"></i>
                        <span>Create</span>
                    </a>

                    <!-- User Avatar Circle -->
                    <div class="dropdown">
                        <button class="btn btn-link p-0 text-decoration-none dropdown-toggle header-circle-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; background: #111827; font-size: 0.85rem;">
                                {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                            </div>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2" style="min-width: 220px; border-radius: 18px;">
                            <li class="px-2 py-2 border-bottom mb-1">
                                <div class="fw-bold text-dark">{{ auth()->user()->name ?? 'User' }}</div>
                                <div class="text-muted small">{{ auth()->user()->email ?? 'user@postsync.cloud' }}</div>
                            </li>
                            <li><a class="dropdown-item small py-2 rounded" href="{{ route('settings.index') }}"><i class="bi bi-person me-2"></i>My Profile</a></li>
                            <li><a class="dropdown-item small py-2 rounded" href="{{ route('billing.index') }}"><i class="bi bi-credit-card me-2"></i>Subscription & Plans</a></li>
                            @if (auth()->user()?->is_super_admin)
                                <li>
                                    <a class="dropdown-item small py-2 rounded text-danger fw-semibold" href="{{ route('admin.dashboard') }}">
                                        <i class="bi bi-shield-lock-fill me-2 text-danger"></i>Admin Control Panel
                                    </a>
                                </li>
                            @endif
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item small py-2 rounded text-danger">
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

    <!-- Create Workspace Modal -->
    <div class="modal fade" id="createWorkspaceModal" tabindex="-1" aria-labelledby="createWorkspaceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form method="POST" action="{{ route('workspaces.create') }}">
                    @csrf
                    <div class="modal-header border-bottom py-3">
                        <h5 class="modal-title fs-6 fw-bold mb-0" id="createWorkspaceModalLabel">
                            <i class="bi bi-briefcase-fill text-primary me-2"></i>Create New Client Workspace
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="text-muted small mb-3">
                            Workspaces provide 100% data isolation. Create separate workspaces for each client or brand to manage their own dedicated social channels, media assets, content calendars, and team members.
                        </p>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-dark">Client Brand / Workspace Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Nike India, Starbucks Digital, Apex Media" required minlength="2" maxlength="60" autofocus>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-dark">Timezone</label>
                            <select name="timezone" class="form-select form-select-sm">
                                <option value="Asia/Kolkata" selected>Asia/Kolkata (IST - UTC+05:30)</option>
                                <option value="UTC">UTC (Universal Time)</option>
                                <option value="America/New_York">America/New_York (EST)</option>
                                <option value="America/Los_Angeles">America/Los_Angeles (PST)</option>
                                <option value="Europe/London">Europe/London (GMT/BST)</option>
                                <option value="Asia/Dubai">Asia/Dubai (GST)</option>
                                <option value="Asia/Singapore">Asia/Singapore (SGT)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top py-2">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-2">
                            <i class="bi bi-check-lg"></i>
                            <span>Create & Enter Workspace</span>
                        </button>
                    </div>
                </form>
            </div>
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

