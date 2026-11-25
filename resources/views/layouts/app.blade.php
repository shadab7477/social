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
        <!-- Sidebar Navigation -->
        <aside class="app-sidebar">
            <div class="sidebar-brand d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="brand-icon">
                        <i class="bi bi-send-fill"></i>
                    </div>
                    <div>
                        <div class="brand-name lh-1">
                            Post<span style="color: #818cf8;">Sync</span>
                        </div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-1 py-0" style="font-size: 0.62rem; letter-spacing: 0.05em;">SAAS PRO</span>
                    </div>
                </div>
                <button class="btn-close btn-close-white d-lg-none" type="button" onclick="toggleMobileSidebar()" aria-label="Close"></button>
            </div>

            <!-- Workspace Switcher Pill inside Sidebar -->
            <div class="px-3 pt-3 pb-1">
                <div class="dropdown">
                    <button class="btn btn-sm w-100 text-start d-flex align-items-center justify-content-between p-2 rounded-3 text-white border" style="background: rgba(255, 255, 255, 0.05); border-color: rgba(255, 255, 255, 0.1) !important;" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="d-flex align-items-center gap-2 text-truncate">
                            <span class="pulse-online"></span>
                            <div class="text-truncate">
                                <div class="text-white small fw-bold text-truncate" style="max-width: 140px;">
                                    {{ session('active_workspace_name', 'Select Workspace') }}
                                </div>
                                <span class="text-muted text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.05em;">Active Workspace</span>
                            </div>
                        </div>
                        <i class="bi bi-chevron-expand text-muted small"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark shadow border-0 p-2" style="min-width: 230px; background: #0f172a;">
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
                        <li><hr class="dropdown-divider border-secondary my-1"></li>
                        <li>
                            <button type="button" class="dropdown-item text-primary fw-semibold d-flex align-items-center py-2 rounded" data-bs-toggle="modal" data-bs-target="#createWorkspaceModal">
                                <i class="bi bi-plus-circle me-2"></i>+ New Client Workspace
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="sidebar-menu">
                <div class="menu-header">Publishing & Content</div>
                <nav class="nav flex-column">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-grid-1x2-fill"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('composer') }}" class="nav-link {{ request()->routeIs('composer') ? 'active' : '' }}">
                        <i class="bi bi-pencil-square"></i>
                        <span>Create Post</span>
                        <span class="badge bg-primary text-white ms-auto" style="font-size: 0.65rem;">New</span>
                    </a>
                    <a href="{{ route('calendar') }}" class="nav-link {{ request()->routeIs('calendar') ? 'active' : '' }}">
                        <i class="bi bi-calendar3"></i>
                        <span>Calendar</span>
                    </a>
                    <a href="{{ route('posts.index') }}" class="nav-link {{ request()->routeIs('posts.*') ? 'active' : '' }}">
                        <i class="bi bi-collection-fill"></i>
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
                        <i class="bi bi-share-fill"></i>
                        <span>Social Accounts</span>
                    </a>
                    <a href="{{ route('inbox.index') }}" class="nav-link {{ request()->routeIs('inbox.*') ? 'active' : '' }}">
                        <i class="bi bi-chat-dots-fill"></i>
                        <span>Social Inbox</span>
                    </a>
                    <a href="{{ route('analytics.index') }}" class="nav-link {{ request()->routeIs('analytics.*') ? 'active' : '' }}">
                        <i class="bi bi-graph-up-arrow"></i>
                        <span>Analytics</span>
                    </a>
                    <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-bar-graph-fill"></i>
                        <span>Client Reports</span>
                    </a>
                </nav>

                <div class="menu-header mt-3">Agency & Collaboration</div>
                <nav class="nav flex-column">
                    <a href="{{ route('clients.index') }}" class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                        <i class="bi bi-buildings-fill"></i>
                        <span>Clients</span>
                    </a>
                    <a href="{{ route('approvals.index') }}" class="nav-link {{ request()->routeIs('approvals.*') ? 'active' : '' }}">
                        <i class="bi bi-check2-circle"></i>
                        <span>Approvals</span>
                    </a>
                    <a href="{{ route('team.index') }}" class="nav-link {{ request()->routeIs('team.*') ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i>
                        <span>Team & Roles</span>
                    </a>
                    <a href="{{ route('automations.index') }}" class="nav-link {{ request()->routeIs('automations.*') ? 'active' : '' }}">
                        <i class="bi bi-cpu-fill"></i>
                        <span>Automations</span>
                    </a>
                </nav>

                <div class="menu-header mt-3">Administration</div>
                <nav class="nav flex-column">
                    <a href="{{ route('billing.index') }}" class="nav-link {{ request()->routeIs('billing.*') ? 'active' : '' }}">
                        <i class="bi bi-credit-card-2-front-fill"></i>
                        <span>Billing & Plans</span>
                    </a>
                    <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <i class="bi bi-gear-wide-connected"></i>
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
                <div class="d-flex align-items-center justify-content-between p-2 rounded-3 border" style="background: rgba(255, 255, 255, 0.04); border-color: rgba(255, 255, 255, 0.08) !important;">
                    <div class="d-flex align-items-center gap-2 text-truncate">
                        <div class="avatar bg-gradient text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 34px; height: 34px; font-size: 0.85rem; font-weight: 700; background: linear-gradient(135deg, #4f46e5, #9333ea);">
                            {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                        </div>
                        <div class="text-truncate" style="max-width: 130px;">
                            <div class="fw-semibold text-white text-truncate small">{{ auth()->user()->name ?? 'Agency User' }}</div>
                            <div class="text-muted text-truncate" style="font-size: 0.72rem;">{{ auth()->user()->email ?? 'user@postsync.cloud' }}</div>
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
            <!-- Top Navbar -->
            <header class="app-header">
                <div class="d-flex align-items-center gap-2 gap-sm-3 flex-grow-1">
                    <button class="btn btn-sm btn-outline-secondary d-lg-none" type="button" onclick="toggleMobileSidebar()" aria-label="Toggle Navigation">
                        <i class="bi bi-list fs-5"></i>
                    </button>

                    <!-- Workspace Breadcrumb Pill -->
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-dark border px-2 py-1 d-flex align-items-center gap-1">
                            <i class="bi bi-briefcase-fill text-primary"></i>
                            <span class="fw-semibold">{{ session('active_workspace_name', 'Workspace') }}</span>
                        </span>
                        @if(isset($headerTitle))
                            <span class="text-muted small d-none d-md-inline">/</span>
                            <span class="fs-6 fw-semibold text-secondary d-none d-md-inline">{{ $headerTitle }}</span>
                        @endif
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <!-- Client Direct Link Shortcut -->
                    @php
                        $currWs = app('current_workspace');
                    @endphp
                    @if ($currWs)
                        <a href="{{ route('social-accounts.index') }}" class="btn btn-sm btn-outline-primary d-none d-md-inline-flex align-items-center gap-1 shadow-sm">
                            <i class="bi bi-link-45deg fs-6"></i>
                            <span>🔗 Share Client Link</span>
                        </a>
                    @endif

                    <a href="{{ route('composer') }}" class="btn btn-sm btn-gradient-primary d-inline-flex align-items-center gap-2 shadow-sm">
                        <i class="bi bi-plus-lg"></i>
                        <span class="d-none d-sm-inline">New Post</span>
                    </a>

                    <!-- User Profile Dropdown -->
                    <div class="dropdown ms-1 ms-sm-2">
                        <button class="btn btn-link p-0 text-decoration-none dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="rounded-circle text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px; font-weight: 700; background: linear-gradient(135deg, #4f46e5, #06b6d4);">
                                {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                            </div>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2" style="min-width: 220px;">
                            <li class="px-2 py-2 border-bottom mb-1">
                                <div class="fw-bold text-dark">{{ auth()->user()->name ?? 'User' }}</div>
                                <div class="text-muted small">{{ auth()->user()->email ?? 'user@postsync.cloud' }}</div>
                            </li>
                            <li><a class="dropdown-item small py-2 rounded" href="{{ route('settings.index') }}"><i class="bi bi-person me-2"></i>My Profile</a></li>
                            <li><a class="dropdown-item small py-2 rounded" href="{{ route('billing.index') }}"><i class="bi bi-credit-card me-2"></i>Subscription & Plans</a></li>
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

