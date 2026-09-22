<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Weighbridge Management System')</title>

    <!-- Native Theme Handler Script (Prevents Dark/Light flash) -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('wb_theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
        })();
    </script>

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- DataTables Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

    <!-- Custom System Styles -->
    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
        }

        body {
            min-height: 100vh;
            background-color: var(--bs-body-bg);
            color: var(--bs-body-color);
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        /* App Wrapper Layout */
        #app-wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* Sidebar Styling */
        #sidebar {
            width: var(--sidebar-width);
            min-width: var(--sidebar-width);
            background: var(--bs-tertiary-bg);
            border-right: 1px solid var(--bs-border-color);
            transition: all 0.2s ease-in-out;
            z-index: 1000;
        }

        #sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
            min-width: var(--sidebar-collapsed-width);
        }

        #sidebar.collapsed .sidebar-text {
            display: none;
        }

        #sidebar.collapsed .brand-title {
            display: none;
        }

        #sidebar .nav-link {
            padding: 0.75rem 1.25rem;
            color: var(--bs-body-color);
            border-radius: 0.375rem;
            margin: 0.2rem 0.5rem;
            display: flex;
            align-items: center;
            white-space: nowrap;
        }

        #sidebar .nav-link i {
            font-size: 1.25rem;
            margin-right: 0.75rem;
        }

        #sidebar.collapsed .nav-link i {
            margin-right: 0;
        }

        #sidebar .nav-link.active, #sidebar .nav-link:hover {
            background-color: var(--bs-primary);
            color: #ffffff !important;
        }

        /* Main Content Container */
        #main-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        /* Top Navbar */
        .navbar-top {
            background-color: var(--bs-body-bg);
            border-bottom: 1px solid var(--bs-border-color);
        }

        /* Fullscreen Operator Mode Adjustments */
        body.is-fullscreen #sidebar {
            display: none !important;
        }

        body.is-fullscreen .navbar-top {
            padding-top: 0.25rem;
            padding-bottom: 0.25rem;
        }

        body.is-fullscreen .fullscreen-hide {
            display: none !important;
        }

        body.is-fullscreen #main-content {
            padding: 0 !important;
        }

        /* Hardware Indicator Cards styling */
        .digital-weight-display {
            font-family: 'Courier New', Courier, monospace;
            font-size: 3rem;
            font-weight: 800;
            letter-spacing: 2px;
            background: #0d1117;
            color: #00ff66;
            border-radius: 8px;
            padding: 1rem;
            text-shadow: 0 0 10px rgba(0, 255, 102, 0.5);
        }

        [data-bs-theme="dark"] .digital-weight-display {
            background: #000000;
            color: #00ff66;
            border: 1px solid #1f2937;
        }

        .lpr-camera-viewport {
            background: #111827;
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            min-height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
        }

        .lpr-plate-badge {
            position: absolute;
            bottom: 12px;
            left: 50%;
            transform: translateX(-50%);
            background: #facc15;
            color: #000;
            font-weight: 900;
            font-size: 1.4rem;
            letter-spacing: 3px;
            padding: 4px 16px;
            border: 3px solid #000;
            border-radius: 6px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.5);
        }
    </style>

    @stack('styles')
</head>
<body>
    <div id="app-wrapper">
        <!-- Sidebar Navigation -->
        <nav id="sidebar" class="d-flex flex-column flex-shrink-0">
            <div class="p-3 d-flex align-items-center justify-content-between border-bottom">
                <a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none color-inherit">
                    <i class="bi bi-truck text-primary fs-3 me-2"></i>
                    <span class="fs-5 fw-bold brand-title">WeighSys</span>
                </a>
                <button id="sidebarToggleBtn" class="btn btn-sm btn-outline-secondary border-0 d-none d-md-block" title="Toggle Sidebar">
                    <i class="bi bi-list"></i>
                </button>
            </div>

            <ul class="nav nav-pills flex-column mb-auto pt-3">
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i>
                        <span class="sidebar-text">Dashboard</span>
                    </a>
                </li>

                @can('create-transactions')
                <li class="nav-item">
                    <a href="{{ route('transactions.create') }}" class="nav-link {{ request()->routeIs('transactions.create') ? 'active' : '' }}">
                        <i class="bi bi-aspect-ratio"></i>
                        <span class="sidebar-text">Scale Operator</span>
                    </a>
                </li>
                @endcan

                @can('view-transactions')
                <li class="nav-item">
                    <a href="{{ route('transactions.index') }}" class="nav-link {{ request()->routeIs('transactions.index') || request()->routeIs('transactions.show') ? 'active' : '' }}">
                        <i class="bi bi-receipt"></i>
                        <span class="sidebar-text">Transactions</span>
                    </a>
                </li>
                @endcan

                @can('manage-forms')
                <li class="nav-item">
                    <a href="{{ route('form-builder.index') }}" class="nav-link {{ request()->routeIs('form-builder.*') ? 'active' : '' }}">
                        <i class="bi bi-ui-checks-grid"></i>
                        <span class="sidebar-text">Form Builder</span>
                    </a>
                </li>
                @endcan

                @can('manage-roles')
                <li class="nav-item">
                    <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                        <i class="bi bi-shield-lock"></i>
                        <span class="sidebar-text">Roles & Permissions</span>
                    </a>
                </li>
                @endcan
            </ul>

            <div class="p-3 border-top">
                @auth
                <div class="d-flex align-items-center justify-content-between">
                    <div class="sidebar-text overflow-hidden me-2">
                        <div class="fw-bold text-truncate">{{ Auth::user()->name }}</div>
                        <small class="text-muted d-block text-truncate">{{ Auth::user()->roles->pluck('name')->implode(', ') ?: 'User' }}</small>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Logout">
                            <i class="bi bi-box-arrow-right"></i>
                        </button>
                    </form>
                </div>
                @endauth
            </div>
        </nav>

        <!-- Main Content Wrapper -->
        <div id="main-content">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand navbar-top px-3">
                <div class="container-fluid p-0">
                    <button class="btn btn-outline-secondary d-md-none me-2" id="sidebarMobileToggle">
                        <i class="bi bi-list"></i>
                    </button>

                    <h5 class="mb-0 fw-bold d-none d-sm-inline-block">@yield('header-title', 'Weighbridge Management System')</h5>

                    <div class="ms-auto d-flex align-items-center gap-2">
                        <!-- Fullscreen Operator Mode Button -->
                        <button id="fullscreenToggleBtn" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1" title="Toggle Operator Fullscreen Mode (Alt + F)">
                            <i class="bi bi-arrows-fullscreen"></i>
                            <span class="d-none d-md-inline">Fullscreen</span>
                            <span class="badge bg-secondary d-none d-lg-inline ms-1" style="font-size: 0.65rem;">Alt+F</span>
                        </button>

                        <!-- Light/Dark Theme Switcher -->
                        <button id="themeToggleBtn" class="btn btn-outline-secondary btn-sm" title="Toggle Light/Dark Theme">
                            <i class="bi bi-moon-stars" id="themeToggleIcon"></i>
                        </button>
                    </div>
                </div>
            </nav>

            <!-- Page Body Content -->
            <main class="p-3 p-md-4 flex-grow-1">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="footer mt-auto py-2 px-3 border-top text-center text-muted fs-7">
                <small>&copy; {{ date('Y') }} WeighSys Weighbridge Management System | Built with Laravel 11 & Bootstrap 5.3.3</small>
            </footer>
        </div>
    </div>

    <!-- Required Scripts: jQuery, Bootstrap Bundle, SortableJS, DataTables -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <!-- Native JavaScript Layout & Interaction Controller -->
    <script>
        $(document).ready(function() {
            // Setup CSRF Token for jQuery AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // 1. Theme Toggler Handler
            const themeToggleBtn = $('#themeToggleBtn');
            const themeToggleIcon = $('#themeToggleIcon');

            function updateThemeUI(theme) {
                document.documentElement.setAttribute('data-bs-theme', theme);
                localStorage.setItem('wb_theme', theme);
                if (theme === 'dark') {
                    themeToggleIcon.removeClass('bi-moon-stars').addClass('bi-sun-fill');
                    themeToggleBtn.removeClass('btn-outline-secondary').addClass('btn-outline-warning');
                } else {
                    themeToggleIcon.removeClass('bi-sun-fill').addClass('bi-moon-stars');
                    themeToggleBtn.removeClass('btn-outline-warning').addClass('btn-outline-secondary');
                }
            }

            // Sync icon on load
            const currentTheme = localStorage.getItem('wb_theme') || 'light';
            updateThemeUI(currentTheme);

            themeToggleBtn.on('click', function() {
                const activeTheme = document.documentElement.getAttribute('data-bs-theme');
                const newTheme = activeTheme === 'dark' ? 'light' : 'dark';
                updateThemeUI(newTheme);
            });

            // 2. Sidebar Collapse Handler
            $('#sidebarToggleBtn, #sidebarMobileToggle').on('click', function() {
                $('#sidebar').toggleClass('collapsed');
            });

            // 3. Fullscreen Operator Mode Handler
            const fullscreenToggleBtn = $('#fullscreenToggleBtn');

            function toggleFullScreen() {
                if (!document.fullscreenElement && !document.mozFullScreenElement && !document.webkitFullscreenElement && !document.msFullscreenElement) {
                    if (document.documentElement.requestFullscreen) {
                        document.documentElement.requestFullscreen();
                    } else if (document.documentElement.msRequestFullscreen) {
                        document.documentElement.msRequestFullscreen();
                    } else if (document.documentElement.mozRequestFullScreen) {
                        document.documentElement.mozRequestFullScreen();
                    } else if (document.documentElement.webkitRequestFullscreen) {
                        document.documentElement.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
                    }
                } else {
                    if (document.exitFullscreen) {
                        document.exitFullscreen();
                    } else if (document.msExitFullscreen) {
                        document.msExitFullscreen();
                    } else if (document.mozCancelFullScreen) {
                        document.mozCancelFullScreen();
                    } else if (document.webkitExitFullscreen) {
                        document.webkitExitFullscreen();
                    }
                }
            }

            fullscreenToggleBtn.on('click', toggleFullScreen);

            // React to Fullscreen state changes
            $(document).on('fullscreenchange webkitfullscreenchange mozfullscreenchange MSFullscreenChange', function() {
                if (document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement) {
                    $('body').addClass('is-fullscreen');
                    fullscreenToggleBtn.find('span.d-none.d-md-inline').text('Exit Fullscreen');
                    fullscreenToggleBtn.find('i').removeClass('bi-arrows-fullscreen').addClass('bi-fullscreen-exit');
                } else {
                    $('body').removeClass('is-fullscreen');
                    fullscreenToggleBtn.find('span.d-none.d-md-inline').text('Fullscreen');
                    fullscreenToggleBtn.find('i').removeClass('bi-fullscreen-exit').addClass('bi-arrows-fullscreen');
                }
            });

            // Shortcut listener for Alt+F or F11
            $(document).on('keydown', function(e) {
                if ((e.altKey && (e.key === 'f' || e.key === 'F')) || e.key === 'F11') {
                    e.preventDefault();
                    toggleFullScreen();
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
