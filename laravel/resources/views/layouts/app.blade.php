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

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- DataTables Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

    <!-- Modern High-End UI Styles -->
    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 74px;
            --font-main: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;

            --wb-card-bg: #ffffff;
            --wb-card-border: rgba(0, 0, 0, 0.07);
            --wb-card-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05);
            --wb-gradient-primary: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            --wb-gradient-success: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --wb-gradient-warning: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        [data-bs-theme="dark"] {
            --wb-card-bg: #111827;
            --wb-card-border: rgba(255, 255, 255, 0.08);
            --wb-card-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.4);
            --wb-gradient-primary: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }

        body {
            font-family: var(--font-main);
            min-height: 100vh;
            background-color: var(--bs-body-bg);
            color: var(--bs-body-color);
            transition: background-color 0.2s ease, color 0.2s ease;
            letter-spacing: -0.01em;
        }

        /* Modern Card Styling */
        .card {
            background-color: var(--wb-card-bg);
            border: 1px solid var(--wb-card-border);
            border-radius: 16px;
            box-shadow: var(--wb-card-shadow);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid var(--wb-card-border);
            padding: 1.25rem 1.5rem;
        }

        /* App Layout Wrapper */
        #app-wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* Sleek Sidebar Navigation */
        #sidebar {
            width: var(--sidebar-width);
            min-width: var(--sidebar-width);
            background: var(--wb-card-bg);
            border-right: 1px solid var(--wb-card-border);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
        }

        #sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
            min-width: var(--sidebar-collapsed-width);
        }

        #sidebar.collapsed .sidebar-text,
        #sidebar.collapsed .brand-title {
            display: none;
        }

        #sidebar .nav-link {
            padding: 0.8rem 1.25rem;
            color: var(--bs-body-color);
            border-radius: 12px;
            margin: 0.25rem 0.75rem;
            display: flex;
            align-items: center;
            font-weight: 600;
            font-size: 0.925rem;
            white-space: nowrap;
            transition: all 0.2s ease;
            opacity: 0.8;
        }

        #sidebar .nav-link i {
            font-size: 1.25rem;
            margin-right: 0.85rem;
            transition: transform 0.2s ease;
        }

        #sidebar .nav-link:hover {
            opacity: 1;
            background-color: rgba(37, 99, 235, 0.08);
            color: var(--bs-primary);
            transform: translateX(2px);
        }

        #sidebar .nav-link.active {
            opacity: 1;
            background: var(--wb-gradient-primary);
            color: #ffffff !important;
            box-shadow: 0 4px 14px 0 rgba(37, 99, 235, 0.35);
        }

        #sidebar.collapsed .nav-link i {
            margin-right: 0;
        }

        /* Main Content Container */
        #main-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        /* Modern Top Navbar */
        .navbar-top {
            background-color: rgba(var(--bs-body-bg-rgb), 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--wb-card-border);
            position: sticky;
            top: 0;
            z-index: 999;
            padding: 0.85rem 1.5rem;
        }

        /* Buttons & Controls */
        .btn {
            border-radius: 10px;
            font-weight: 600;
            padding: 0.5rem 1.15rem;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-primary {
            background: var(--wb-gradient-primary);
            border: none;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
        }

        .btn-success {
            background: var(--wb-gradient-success);
            border: none;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
        }

        .form-control, .form-select {
            border-radius: 10px;
            padding: 0.6rem 0.9rem;
            border-color: var(--wb-card-border);
            font-weight: 500;
        }

        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
            border-color: #2563eb;
        }

        /* Fullscreen Operator Mode Adjustments */
        body.is-fullscreen #sidebar {
            display: none !important;
        }

        body.is-fullscreen .navbar-top {
            padding-top: 0.4rem;
            padding-bottom: 0.4rem;
        }

        body.is-fullscreen #main-content {
            padding: 0 !important;
        }

        /* High-Tech Hardware Indicator Displays */
        .digital-weight-display {
            font-family: 'Courier New', Courier, monospace;
            font-size: 3.25rem;
            font-weight: 900;
            letter-spacing: 3px;
            background: radial-gradient(circle, #0f172a 0%, #020617 100%);
            color: #00ff66;
            border-radius: 14px;
            padding: 1.25rem;
            text-shadow: 0 0 15px rgba(0, 255, 102, 0.6);
            border: 2px solid #1e293b;
            box-shadow: inset 0 2px 10px rgba(0,0,0,0.8);
        }

        .lpr-camera-viewport {
            background: #090d16;
            position: relative;
            border-radius: 14px;
            overflow: hidden;
            min-height: 230px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #1e293b;
            box-shadow: inset 0 0 20px rgba(0, 0, 0, 0.8);
        }

        .lpr-viewfinder-overlay {
            position: absolute;
            inset: 15px;
            border: 2px dashed rgba(59, 130, 246, 0.3);
            border-radius: 10px;
            pointer-events: none;
        }

        .lpr-plate-badge {
            position: absolute;
            bottom: 14px;
            left: 50%;
            transform: translateX(-50%);
            background: #facc15;
            color: #0f172a;
            font-weight: 900;
            font-size: 1.5rem;
            letter-spacing: 4px;
            padding: 6px 20px;
            border: 3px solid #000;
            border-radius: 8px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.6);
            text-shadow: none;
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
                    <div class="rounded-3 bg-primary text-white p-2 d-flex align-items-center justify-content-center me-2 shadow-sm" style="width: 38px; height: 38px;">
                        <i class="bi bi-truck fs-5"></i>
                    </div>
                    <span class="fs-5 fw-extrabold brand-title tracking-tight">WeighSys</span>
                </a>
                <button id="sidebarToggleBtn" class="btn btn-sm btn-outline-secondary border-0 d-none d-md-block" title="Toggle Sidebar">
                    <i class="bi bi-layout-sidebar"></i>
                </button>
            </div>

            <ul class="nav nav-pills flex-column mb-auto pt-3">
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-grid-1x2"></i>
                        <span class="sidebar-text">Dashboard</span>
                    </a>
                </li>

                @can('create-transactions')
                <li class="nav-item">
                    <a href="{{ route('transactions.create') }}" class="nav-link {{ request()->routeIs('transactions.create') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i>
                        <span class="sidebar-text">Scale Operator</span>
                    </a>
                </li>
                @endcan

                @can('view-transactions')
                <li class="nav-item">
                    <a href="{{ route('transactions.index') }}" class="nav-link {{ request()->routeIs('transactions.index') || request()->routeIs('transactions.show') ? 'active' : '' }}">
                        <i class="bi bi-receipt-cutoff"></i>
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
                        <i class="bi bi-shield-check"></i>
                        <span class="sidebar-text">Roles & Permissions</span>
                    </a>
                </li>
                @endcan
            </ul>

            <div class="p-3 border-top">
                @auth
                <div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-body-tertiary">
                    <div class="sidebar-text overflow-hidden me-2">
                        <div class="fw-bold text-truncate fs-7">{{ Auth::user()->name }}</div>
                        <small class="text-muted d-block text-truncate fs-8">{{ Auth::user()->roles->pluck('name')->implode(', ') ?: 'User' }}</small>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger border-0" title="Logout">
                            <i class="bi bi-box-arrow-right fs-6"></i>
                        </button>
                    </form>
                </div>
                @endauth
            </div>
        </nav>

        <!-- Main Content Wrapper -->
        <div id="main-content">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand navbar-top">
                <div class="container-fluid p-0">
                    <button class="btn btn-outline-secondary d-md-none me-2" id="sidebarMobileToggle">
                        <i class="bi bi-list"></i>
                    </button>

                    <h5 class="mb-0 fw-bold d-none d-sm-inline-block tracking-tight">@yield('header-title', 'Weighbridge Management System')</h5>

                    <div class="ms-auto d-flex align-items-center gap-2">
                        <!-- Fullscreen Operator Mode Button -->
                        <button id="fullscreenToggleBtn" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1.5 shadow-sm" title="Toggle Operator Fullscreen Mode (Alt + F)">
                            <i class="bi bi-arrows-fullscreen"></i>
                            <span class="d-none d-md-inline">Fullscreen View</span>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle d-none d-lg-inline ms-1" style="font-size: 0.65rem;">Alt+F</span>
                        </button>

                        <!-- Light/Dark Theme Switcher -->
                        <button id="themeToggleBtn" class="btn btn-outline-secondary btn-sm" title="Toggle Light/Dark Theme">
                            <i class="bi bi-moon-stars-fill" id="themeToggleIcon"></i>
                        </button>
                    </div>
                </div>
            </nav>

            <!-- Page Body Content -->
            <main class="p-3 p-md-4 flex-grow-1">
                @if(session('success'))
                    <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show mb-4 d-flex align-items-center" role="alert">
                        <i class="bi bi-check-circle-fill fs-5 me-2"></i> <div>{{ session('success') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show mb-4 d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i> <div>{{ session('error') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="footer mt-auto py-3 px-4 border-top text-center text-muted fs-7">
                <small>&copy; {{ date('Y') }} WeighSys Management Terminal | Modernized Industrial Solution</small>
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
                    themeToggleIcon.removeClass('bi-moon-stars-fill').addClass('bi-sun-fill');
                    themeToggleBtn.removeClass('btn-outline-secondary').addClass('btn-outline-warning');
                } else {
                    themeToggleIcon.removeClass('bi-sun-fill').addClass('bi-moon-stars-fill');
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
                    fullscreenToggleBtn.find('span.d-none.d-md-inline').text('Fullscreen View');
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
