<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | Villa Management System</title>

    <!-- Local Offline Assets - 100% Internal Network Compatible (No CDN) -->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/font/bootstrap-icons.min.css') }}">

    <style>
        :root {
            --vs-primary: #0f172a;       /* Deep Slate Navy */
            --vs-secondary: #1e293b;     /* Slate */
            --vs-accent: #c29b38;        /* Luxury Gold Accent */
            --vs-accent-hover: #a17f2c;
            --vs-gold-light: #fef3c7;
            --vs-bg-main: #f8fafc;
            --vs-card-border: #e2e8f0;
            --vs-text-muted: #64748b;
        }

        body {
            background-color: var(--vs-bg-main);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #1e293b;
            min-height: 100vh;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 270px;
            background: linear-gradient(180deg, #0f172a 0%, #090d16 100%);
            min-height: 100vh;
            color: #94a3b8;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 1040;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.12);
            transition: all 0.3s ease;
        }

        .sidebar-brand {
            padding: 1.5rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            text-decoration: none;
        }

        .brand-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #d4af37, #996515);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0f172a;
            font-size: 1.25rem;
            font-weight: 800;
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.35);
        }

        .brand-title {
            color: #ffffff;
            font-size: 1.2rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            line-height: 1.2;
        }

        .brand-subtitle {
            font-size: 0.7rem;
            color: #c29b38;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            font-weight: 600;
        }

        .sidebar-nav {
            padding: 1rem 0.75rem;
            flex: 1;
            overflow-y: auto;
        }

        .nav-category {
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 700;
            color: #475569;
            padding: 0.75rem 0.75rem 0.25rem;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.65rem 0.85rem;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s ease;
            margin-bottom: 0.25rem;
        }

        .sidebar-link i {
            font-size: 1.1rem;
            width: 22px;
            text-align: center;
            color: #64748b;
            transition: color 0.2s ease;
        }

        .sidebar-link:hover {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.06);
        }

        .sidebar-link:hover i {
            color: var(--vs-accent);
        }

        .sidebar-link.active {
            color: #ffffff;
            background: linear-gradient(90deg, rgba(194, 155, 56, 0.22) 0%, rgba(194, 155, 56, 0.08) 100%);
            border-left: 3px solid var(--vs-accent);
            font-weight: 600;
        }

        .sidebar-link.active i {
            color: var(--vs-accent);
        }

        .sidebar-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            background-color: rgba(0, 0, 0, 0.2);
        }

        /* Main Content Wrapper */
        .main-wrapper {
            margin-left: 270px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }

        /* Top Header Navbar */
        .top-navbar {
            background-color: #ffffff;
            height: 70px;
            border-bottom: 1px solid var(--vs-card-border);
            padding: 0 1.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1020;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        }

        .content-area {
            padding: 1.75rem;
            flex: 1;
        }

        /* Luxury Cards */
        .card-luxury {
            background: #ffffff;
            border: 1px solid var(--vs-card-border);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-luxury:hover {
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.05);
        }

        .card-luxury .card-header {
            background-color: #ffffff;
            border-bottom: 1px solid var(--vs-card-border);
            font-weight: 600;
            padding: 1rem 1.25rem;
            border-radius: 12px 12px 0 0;
        }

        /* Buttons & Badges */
        .btn-gold {
            background: linear-gradient(135deg, #d4af37, #b48a28);
            color: #0f172a;
            font-weight: 600;
            border: none;
            box-shadow: 0 2px 6px rgba(212, 175, 55, 0.3);
            transition: all 0.2s ease;
        }

        .btn-gold:hover, .btn-gold:focus {
            background: linear-gradient(135deg, #c29b38, #996515);
            color: #ffffff;
            transform: translateY(-1px);
        }

        .badge-gold {
            background-color: var(--vs-gold-light);
            color: #92400e;
            font-weight: 600;
        }

        /* Offline indicator badge */
        .system-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.75rem;
            background-color: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            font-size: 0.78rem;
            color: #475569;
            font-weight: 500;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #10b981;
            display: inline-block;
        }

        @media (max-width: 992px) {
            .sidebar {
                margin-left: -270px;
            }
            .sidebar.active {
                margin-left: 0;
            }
            .main-wrapper {
                margin-left: 0;
            }
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Sidebar Navigation -->
    <aside class="sidebar" id="sidebar">
        <a href="{{ route('dashboard') }}" class="sidebar-brand">
            <div class="brand-icon">
                <i class="fa-solid fa-hotel"></i>
            </div>
            <div>
                <div class="brand-title">Villa Management</div>
                <div class="brand-subtitle">Luxury Resort Ops</div>
            </div>
        </a>

        <div class="sidebar-nav">
            <div class="nav-category">Main Overview</div>
            <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Command Dashboard</span>
            </a>

            <div class="nav-category">Resort Assets</div>
            <a href="{{ route('villas.index') }}" class="sidebar-link {{ request()->routeIs('villas.*') ? 'active' : '' }}">
                <i class="fa-solid fa-house-chimney-window"></i>
                <span>Luxury Villas</span>
            </a>

            <div class="nav-category">Operations</div>
            <a href="{{ route('bookings.index') }}" class="sidebar-link {{ request()->routeIs('bookings.index') || request()->routeIs('bookings.show') || request()->routeIs('bookings.create') || request()->routeIs('bookings.edit') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-check"></i>
                <span>Reservations</span>
            </a>
            <a href="{{ route('bookings.calendar') }}" class="sidebar-link {{ request()->routeIs('bookings.calendar') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-days"></i>
                <span>Tape Chart</span>
            </a>
            <a href="{{ route('guests.index') }}" class="sidebar-link {{ request()->routeIs('guests.*') ? 'active' : '' }}">
                <i class="fa-solid fa-users"></i>
                <span>Guests Directory</span>
            </a>
            <a href="{{ route('maintenance.index') }}" class="sidebar-link {{ request()->routeIs('maintenance.*') ? 'active' : '' }}">
                <i class="fa-solid fa-broom-ball"></i>
                <span>Housekeeping & Tasks</span>
            </a>

            @if(auth()->check() && auth()->user()->isManager())
                <div class="nav-category">Financials</div>
                <a href="{{ route('expenses.index') }}" class="sidebar-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    <span>Expenses & Accounts</span>
                </a>
            @endif

            @if(auth()->check() && auth()->user()->isAdmin())
                <div class="nav-category">Administration</div>
                <a href="{{ route('admin.bulk-import.index') }}" class="sidebar-link {{ request()->routeIs('admin.bulk-import.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-import text-warning"></i>
                    <span>Bulk Import Hub</span>
                    <span class="badge bg-warning text-dark ms-auto" style="font-size: 0.65rem;">ADMIN</span>
                </a>
            @endif
        </div>

        <div class="sidebar-footer">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; font-weight: 700;">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </div>
                <div class="overflow-hidden">
                    <div class="text-white small fw-bold text-truncate">{{ auth()->user()->name ?? 'Guest User' }}</div>
                    <div class="text-muted" style="font-size: 0.72rem; text-transform: uppercase;">{{ auth()->user()->role ?? 'Staff' }}</div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="ms-auto">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-link text-muted p-0" title="Logout">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Top Navigation -->
        <header class="top-navbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-light d-lg-none" id="sidebarToggle">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="d-none d-md-flex align-items-center">
                    <span class="system-pill">
                        <span class="status-dot"></span>
                        <strong>PostgreSQL 18</strong> | Internal Offline Mode
                    </span>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('bookings.create') }}" class="btn btn-sm btn-gold d-none d-sm-inline-flex align-items-center gap-2">
                    <i class="fa-solid fa-plus"></i>
                    <span>New Reservation</span>
                </a>
                <a href="{{ route('villas.create') }}" class="btn btn-sm btn-outline-dark d-none d-sm-inline-flex align-items-center gap-2">
                    <i class="fa-solid fa-house-chimney-medical"></i>
                    <span>Add Villa</span>
                </a>

                <!-- User Dropdown -->
                <div class="dropdown ms-2">
                    <button class="btn btn-light btn-sm dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-circle-user text-secondary"></i>
                        <span class="d-none d-md-inline">{{ auth()->user()->name ?? 'Account' }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><h6 class="dropdown-header">Signed in as {{ auth()->user()->email ?? '' }}</h6></li>
                        <li><span class="dropdown-item-text text-muted small">Role: {{ ucfirst(auth()->user()->role ?? '') }}</span></li>
                        <li><hr class="dropdown-divider"></li>
                        @if(auth()->check() && auth()->user()->isAdmin())
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.bulk-import.index') }}">
                                    <i class="fa-solid fa-file-import text-warning me-2"></i>Bulk Import Data
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                        @endif
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fa-solid fa-arrow-right-from-bracket me-2"></i>Sign Out
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="content-area">
            <!-- Flash Alert Notifications -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
                    <i class="fa-solid fa-circle-check fs-5"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
                    <i class="fa-solid fa-circle-exclamation fs-5"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
                    <i class="fa-solid fa-triangle-exclamation fs-5"></i>
                    <div>{{ session('warning') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
                    <i class="fa-solid fa-circle-info fs-5"></i>
                    <div>{{ session('info') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <div class="fw-bold mb-1"><i class="fa-solid fa-circle-xmark me-2"></i>Please resolve the following errors:</div>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Local Offline JS Assets -->
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/chartjs/chart.min.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');

            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function () {
                    sidebar.classList.toggle('active');
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
