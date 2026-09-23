<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Weighbridge Management System') }}</title>

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- DataTables Bootstrap 5 CSS -->
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Select2 CSS & Bootstrap 5 Theme -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 260px;
        }

        body {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
        }

        #wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
        }

        #sidebar {
            min-width: var(--sidebar-width);
            max-width: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s;
            z-index: 1000;
        }

        #sidebar.collapsed {
            margin-left: calc(-1 * var(--sidebar-width));
        }

        .sidebar-heading {
            padding: 1.25rem 1.5rem;
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: .5px;
        }

        .nav-link {
            padding: 0.75rem 1.5rem;
            color: rgba(255, 255, 255, 0.75);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            border-radius: 0.375rem;
            margin: 2px 12px;
        }

        .nav-link:hover, .nav-link.active {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.12);
        }

        #content {
            width: 100%;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .top-navbar {
            padding: 0.75rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .hardware-badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.35em 0.65em;
            border-radius: 50rem;
        }

        .weight-display {
            font-family: 'Courier New', Courier, monospace;
            font-size: 2.75rem;
            font-weight: 800;
            letter-spacing: 2px;
            background: #000000;
            color: #00ff66;
            padding: 15px 20px;
            border-radius: 8px;
            text-align: center;
            border: 2px solid #004d1a;
            box-shadow: inset 0 0 10px rgba(0,255,102,0.2);
        }

        .camera-feed-container {
            position: relative;
            background-color: #111;
            border-radius: 8px;
            overflow: hidden;
            aspect-ratio: 16/9;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .camera-overlay-badge {
            position: absolute;
            bottom: 12px;
            left: 12px;
            background: rgba(0,0,0,0.8);
            border: 1px solid #28a745;
            color: #28a745;
            padding: 4px 12px;
            border-radius: 4px;
            font-family: monospace;
            font-weight: bold;
            font-size: 1.1rem;
        }

        /* Fullscreen mode helper */
        body.is-fullscreen #sidebar {
            display: none !important;
        }
        body.is-fullscreen .top-navbar {
            display: none !important;
        }
        body.is-fullscreen #content {
            padding: 0 !important;
        }
    </style>

    @stack('styles')
</head>
<body>

<div id="wrapper">
    <!-- Sidebar -->
    <nav id="sidebar" class="bg-dark border-end border-secondary border-opacity-25">
        <div class="sidebar-heading border-bottom border-secondary border-opacity-25 text-primary d-flex align-items-center justify-content-between">
            <span class="d-flex align-items-center gap-2">
                <i class="bi bi-truck-front-fill fs-4"></i>
                <span>WEIGHBRIDGE</span>
            </span>
            <span class="badge bg-primary-subtle text-primary fs-6 px-2 py-1">v1.0</span>
        </div>

        <div class="py-3">
            <div class="px-3 mb-2 text-uppercase text-secondary fs-7 fw-bold" style="font-size: 0.75rem;">Main Navigation</div>
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('scale.index') }}" class="nav-link {{ request()->routeIs('scale.*') ? 'active' : '' }}">
                <i class="bi bi-aspect-ratio-fill"></i>
                <span>Scale Terminal</span>
            </a>

            <a href="{{ route('transactions.index') }}" class="nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
                <i class="bi bi-receipt-cutoff"></i>
                <span>Transactions</span>
            </a>

            @role('Super Admin')
            <div class="px-3 mt-4 mb-2 text-uppercase text-secondary fs-7 fw-bold" style="font-size: 0.75rem;">Administration</div>
            <a href="{{ route('admin.forms.index') }}" class="nav-link {{ request()->routeIs('admin.forms.*') ? 'active' : '' }}">
                <i class="bi bi-ui-checks-grid"></i>
                <span>Form Builder</span>
            </a>

            <a href="{{ route('admin.roles.index') }}" class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                <i class="bi bi-shield-lock-fill"></i>
                <span>Roles & Users</span>
            </a>
            @endrole
        </div>
    </nav>

    <!-- Page Content -->
    <div id="content" class="bg-body-tertiary">
        <!-- Top Navbar -->
        <nav class="navbar top-navbar navbar-expand bg-body border-bottom">
            <div class="container-fluid px-0">
                <button type="button" id="sidebarCollapse" class="btn btn-outline-secondary btn-sm me-3">
                    <i class="bi bi-list fs-5"></i>
                </button>

                <!-- System Hardware Status Indicators -->
                <div class="d-none d-md-flex align-items-center gap-3 me-auto">
                    <span class="hardware-badge bg-success-subtle text-success border border-success-subtle d-flex align-items-center gap-1">
                        <i class="bi bi-broadcast"></i> Scale Indicator: <strong>ONLINE</strong>
                    </span>
                    <span class="hardware-badge bg-info-subtle text-info border border-info-subtle d-flex align-items-center gap-1">
                        <i class="bi bi-camera-video-fill"></i> LPR Camera: <strong>CONNECTED</strong>
                    </span>
                </div>

                <!-- Right Header Actions -->
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <!-- Fullscreen Toggle -->
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="fullscreenToggle" title="Toggle Fullscreen View (F11 or Alt+F)">
                        <i class="bi bi-arrows-fullscreen"></i>
                        <span class="d-none d-lg-inline ms-1">Fullscreen</span>
                    </button>

                    <!-- Theme Toggle -->
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="themeToggle" title="Toggle Dark/Light Mode">
                        <i class="bi bi-moon-stars-fill" id="themeIcon"></i>
                    </button>

                    <!-- User Dropdown -->
                    <div class="dropdown ms-2">
                        <button class="btn btn-outline-primary btn-sm dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle fs-6"></i>
                            <span>{{ Auth::user()->name ?? 'Operator' }}</span>
                            <span class="badge bg-secondary ms-1">{{ Auth::user()->roles->pluck('name')->first() ?? 'Role' }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><span class="dropdown-item-text text-muted small">{{ Auth::user()->email ?? '' }}</span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2">
                                        <i class="bi bi-box-arrow-right"></i> Sign Out
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Body Container -->
        <main class="container-fluid p-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<!-- jQuery 3.7.1 -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap 5.3.3 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- SortableJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<!-- DataTables.net -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        // CSRF Token setup for jQuery AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // 1. Sidebar Toggle
        $('#sidebarCollapse').on('click', function() {
            $('#sidebar').toggleClass('collapsed');
        });

        // 2. Dark / Light Theme Switching logic using localStorage
        const themeToggleBtn = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');

        function setTheme(theme) {
            document.documentElement.setAttribute('data-bs-theme', theme);
            localStorage.setItem('wb_theme', theme);
            if (theme === 'dark') {
                themeIcon.className = 'bi bi-moon-stars-fill';
            } else {
                themeIcon.className = 'bi bi-sun-fill';
            }
        }

        const savedTheme = localStorage.getItem('wb_theme') || 'dark';
        setTheme(savedTheme);

        themeToggleBtn.addEventListener('click', function() {
            const currentTheme = document.documentElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            setTheme(newTheme);
        });

        // 3. HTML5 Fullscreen API & Keyboard Shortcut (F11 / Alt + F)
        const fullscreenBtn = document.getElementById('fullscreenToggle');

        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {
                    console.error(`Error attempting to enable fullscreen: ${err.message}`);
                });
                document.body.classList.add('is-fullscreen');
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
                document.body.classList.remove('is-fullscreen');
            }
        }

        if (fullscreenBtn) {
            fullscreenBtn.addEventListener('click', toggleFullScreen);
        }

        document.addEventListener('fullscreenchange', function() {
            if (!document.fullscreenElement) {
                document.body.classList.remove('is-fullscreen');
            } else {
                document.body.classList.add('is-fullscreen');
            }
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'F11' || (e.altKey && (e.key === 'f' || e.key === 'F'))) {
                e.preventDefault();
                toggleFullScreen();
            }
        });
    });
</script>

@stack('scripts')
</body>
</html>
