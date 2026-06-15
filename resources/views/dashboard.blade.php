<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/theme-default.css') }}">
    <link rel="stylesheet" href="{{ asset('css/emerald-night.css') }}">
    <link rel="stylesheet" href="{{ asset('css/theme-financial.css') }}">
    <link rel="stylesheet" href="{{ asset('css/theme-corona.css') }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f1f5f9;
        }

        .sidebar {
            background-color: #ffffff;
            min-height: 100vh;
            padding: 0;
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.05);
            border-right: 1px solid #e5e7eb;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .sidebar-logo {
            width: 54px;
            height: 54px;
            object-fit: contain;
            border-radius: 12px;
            flex-shrink: 0;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.12);
            background: #ffffff;
            padding: 3px;
            transition: transform 0.2s ease;
        }

        .sidebar-logo:hover {
            transform: scale(1.06);
        }

        .sidebar-title-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .sidebar-title-main {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            color: #1e293b;
            font-size: 17px;
            font-weight: 700;
            letter-spacing: -0.2px;
            line-height: 1.25;
        }

        .sidebar-title-sub {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            color: #2563eb;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 3px;
        }

        .sidebar-nav {
            flex: 1;
            padding: 16px 0;
            overflow-y: auto;
        }

        .nav-item {
            margin: 8px 12px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #4b5563;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 15px;
            font-weight: 500;
        }

        .nav-link:hover {
            color: #111827;
            background-color: #f3f4f6;
        }

        .nav-link.active {
            background-color: #e0f2fe;
            color: #0284c7;
            font-weight: 600;
        }

        .nav-icon {
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .sidebar-footer {
            padding: 16px 12px;
            border-top: 1px solid #e5e7eb;
        }

        /* ── Premium Logout Modal ── */
        .logout-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            animation: fadeInBg 0.2s ease;
        }

        .logout-overlay.active {
            display: flex;
        }

        @keyframes fadeInBg {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .logout-modal-card {
            background: #fff;
            border-radius: 20px;
            padding: 0;
            max-width: 420px;
            width: calc(100% - 32px);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.18), 0 8px 20px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
            animation: slideUp 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .logout-modal-top {
            background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%);
            padding: 36px 32px 24px;
            text-align: center;
            border-bottom: 1px solid #fecdd3;
        }

        .logout-modal-icon-wrap {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            box-shadow: 0 8px 24px rgba(220, 38, 38, 0.3);
            animation: pulse-red 2s infinite;
        }

        @keyframes pulse-red {

            0%,
            100% {
                box-shadow: 0 8px 24px rgba(220, 38, 38, 0.30);
            }

            50% {
                box-shadow: 0 8px 32px rgba(220, 38, 38, 0.55);
            }
        }

        .logout-modal-icon-wrap i {
            font-size: 30px;
            color: #fff;
        }

        .logout-modal-title {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 6px;
            letter-spacing: -0.3px;
        }

        .logout-modal-subtitle {
            font-size: 13px;
            color: #64748b;
            margin: 0;
            line-height: 1.6;
        }

        .logout-modal-body {
            padding: 22px 28px 28px;
        }

        .logout-modal-info {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background: #fef9c3;
            border: 1px solid #fde68a;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 22px;
            font-size: 12.5px;
            color: #854d0e;
            line-height: 1.5;
        }

        .logout-modal-info i {
            font-size: 15px;
            color: #ca8a04;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .logout-modal-actions {
            display: flex;
            gap: 10px;
        }

        .logout-btn-cancel {
            flex: 1;
            padding: 12px 16px;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            font-size: 13.5px;
            font-weight: 600;
            color: #475569;
            background: #f8fafc;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .logout-btn-cancel:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            color: #0f172a;
        }

        .logout-btn-confirm {
            flex: 1;
            padding: 12px 16px;
            border: none;
            border-radius: 12px;
            font-size: 13.5px;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            box-shadow: 0 4px 14px rgba(220, 38, 38, 0.3);
        }

        .logout-btn-confirm:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            box-shadow: 0 6px 20px rgba(220, 38, 38, 0.45);
            transform: translateY(-1px);
        }

        .logout-btn-confirm:active {
            transform: translateY(0);
        }

        .logout-modal-close {
            position: absolute;
            top: 12px;
            right: 14px;
            width: 28px;
            height: 28px;
            background: rgba(0, 0, 0, 0.06);
            border: none;
            border-radius: 50%;
            color: #64748b;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .logout-modal-close:hover {
            background: rgba(0, 0, 0, 0.12);
            color: #0f172a;
        }

        .main-content {
            margin-left: 280px;
            padding: 32px;
            min-height: 100vh;
        }

        .page-header {
            color: #212529;
            margin-bottom: 24px;
        }

        .page-header h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .page-header p {
            color: #6b7280;
            font-size: 16px;
        }

        /* Scrollbar styling for sidebar */
        .sidebar-nav::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-nav::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 3px;
        }

        .sidebar-nav::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.2);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 250px;
            }

            .main-content {
                margin-left: 0;
                padding: 20px;
            }

            .sidebar-title,
            .sidebar-title-container {
                display: none;
            }

            .sidebar-header {
                justify-content: center;
            }

            .nav-link span {
                display: none;
            }

            .nav-icon {
                font-size: 24px;
            }
        }

        /* Modern Dashboard Cards */
        .dash-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            border: 1px solid #e2e8f0;
            height: 100%;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .dash-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6, #6366f1);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .dash-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            border-color: #cbd5e1;
        }

        .dash-card:hover::before {
            opacity: 1;
        }

        .dash-card-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .dash-card-title {
            margin: 0;
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .dash-card-value {
            margin: 8px 0 0 0;
            color: #0f172a;
            font-size: 32px;
            font-weight: 800;
            line-height: 1.1;
        }

        .dash-card-subtext {
            margin: 4px 0 0 0;
            color: #94a3b8;
            font-size: 12px;
        }

        .dash-card-icon {
            width: 54px;
            height: 54px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            background: #eff6ff;
            color: #3b82f6;
            /* Default */
        }

        /* Alert Box Restyling */
        .premium-alert {
            background: #fff1f2 !important;
            border: 1px solid #ffe4e6 !important;
            border-left: 4px solid #f43f5e !important;
            box-shadow: 0 4px 12px rgba(244, 63, 94, 0.08) !important;
            border-radius: 12px !important;
        }

        .premium-alert .alert-heading {
            color: #881337 !important;
        }

        .premium-alert p {
            color: #9f1239 !important;
        }

        .premium-alert a {
            color: #be123c !important;
            text-decoration: underline;
            font-weight: 700;
        }

        .premium-alert i {
            color: #f43f5e !important;
        }

        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Header -->
        <div class="sidebar-header">
            <img src="{{ asset('img/phrmologo.png') }}" alt="PHRMO Logo" class="sidebar-logo"
                onerror="this.style.display='none'">
            <div class="sidebar-title-container">
                <span class="sidebar-title-main" style="line-height: 1.3; font-size: 14.5px; padding-top: 2px;">Human
                    Resource<br>Data Management<br>System</span>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="sidebar-nav">
            <div class="nav-item">
                <a href="{{ route('dashboard') }}"
                    class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-house-door"></i>
                    <span>Dashboard</span>
                </a>
            </div>

            @if(auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin()))
                <div class="nav-item">
                    <a href="{{ route('all-data.index') }}"
                        class="nav-link {{ request()->routeIs('all-data.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-table"></i>
                        <span>All Data</span>
                    </a>
                </div>
            @endif

            @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                <div class="nav-item">
                    <a href="{{ route('plantilla.index') }}"
                        class="nav-link {{ request()->routeIs('plantilla.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-person-badge"></i>
                        <span>Inventory of Personnel</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('job-orders.index') }}"
                        class="nav-link {{ request()->routeIs('job-orders.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-file-earmark-person"></i>
                        <span>Job Orders</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('casual.index') }}"
                        class="nav-link {{ request()->routeIs('casual.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-person-lines-fill"></i>
                        <span>Casual</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('permanent.index') }}"
                        class="nav-link {{ request()->routeIs('permanent.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-shield-check"></i>
                        <span>Permanent</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('retirement.index') }}"
                        class="nav-link {{ request()->routeIs('retirement.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-clock-history"></i>
                        <span>Retirement</span>
                        @php $retCount = \App\Models\PlantillaRecord::retirementDue()->count(); @endphp
                        @if($retCount > 0)
                            <span
                                style="margin-left:auto; background:#dc3545; color:white; font-size:10px; font-weight:bold; padding:2px 8px; border-radius:99px;">{{ $retCount }}</span>
                        @endif
                    </a>
                </div>
            @endif

            @if(auth()->user()->isSuperAdmin() || auth()->user()->isSalaryAdmin())
                <div class="nav-item">
                    <a href="{{ route('step-increment.hub') }}"
                        class="nav-link {{ request()->routeIs('step-increment.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-building-fill-gear"></i>
                        <span>Plantilla of Personnel</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('salary-grades.index') }}"
                        class="nav-link {{ request()->routeIs('salary-grades.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-currency-dollar"></i>
                        <span>Salary Grades</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('salary-schedules.index') }}"
                        class="nav-link {{ request()->routeIs('salary-schedules.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-layers"></i>
                        <span>SSL Schedules</span>
                    </a>
                </div>
            @endif

            @if(auth()->user()->isSuperAdmin())
                <div class="nav-item">
                    <a href="{{ route('imports.index') }}"
                        class="nav-link {{ request()->routeIs('imports.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-file-earmark-spreadsheet"></i>
                        <span>Import Data</span>
                    </a>
                </div>
            @endif


            <div class="nav-item">
                <a href="{{ route('profile.edit') }}"
                    class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-person-circle"></i>
                    <span>Profile</span>
                </a>
            </div>

            @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                <div class="nav-item">
                    <a href="{{ route('users.index') }}"
                        class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-people-fill"></i>
                        <span>User Management</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('audit-logs.index') }}"
                        class="nav-link {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-shield-lock-fill"></i>
                        <span>Audit Trail</span>
                    </a>
                </div>
            @endif
        </nav>

        <!-- Footer -->
        <div class="sidebar-footer">
            <button type="button" class="nav-link"
                style="width: 100%; border: none; background: none; cursor: pointer; text-align: left;"
                onclick="openLogoutModal()">
                <i class="nav-icon bi bi-box-arrow-right"></i>
                <span>Log Out</span>
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header Navbar -->
        <nav
            style="background-color: #ffffff; color: #111827; margin: -32px -32px 0 -32px; padding: 0 1.5rem; display: flex; justify-content: space-between; align-items: center; height: 70px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border-bottom: 1px solid #e5e7eb; width: calc(100% + 64px);">
            <!-- Left Side - Search (Removed) -->
            <div style="flex: 1; max-width: 600px; position: relative;">
            </div>


            <!-- Right Side - Actions -->
            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 1.5rem; height: 100%;">
                <!-- Theme Settings Dropdown -->
                <div class="dropdown">
                    <button class="theme-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"
                        style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; padding: 0.5rem 1rem; border-radius: 0.5rem; display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-weight: 500; font-size: 14px; height: fit-content; transition: all 0.2s;">
                        <i class="bi bi-palette"></i> <span>Theme</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li>
                            <h6 class="dropdown-header">Appearance</h6>
                        </li>
                        <li><a class="dropdown-item d-flex align-items-center" href="#"
                                onclick="setTheme('default'); return false;"><i class="bi bi-sun me-2"></i> Default
                                Light</a></li>
                        <li><a class="dropdown-item d-flex align-items-center" href="#"
                                onclick="setTheme('emerald-night'); return false;"><i class="bi bi-moon-stars me-2"></i>
                                Emerald Night</a></li>
                        <li><a class="dropdown-item d-flex align-items-center" href="#"
                                onclick="setTheme('theme-financial'); return false;"><i
                                    class="bi bi-graph-up-arrow me-2"></i> Financial (Teal)</a></li>
                        <li><a class="dropdown-item d-flex align-items-center" href="#"
                                onclick="setTheme('theme-corona'); return false;"><i class="bi bi-moon me-2"></i> Corona
                                (Dark)</a></li>
                    </ul>
                </div>

                <!-- Add New Employee Button -->
                <a href="{{ route('all-data.create') }}"
                    style="background-color: #0d6efd; color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.5rem; display: flex; align-items: center; gap: 0.5rem; cursor: pointer; text-decoration: none; font-weight: 500; font-size: 14px; height: fit-content; position: relative; z-index: 10;">
                    <i class="bi bi-plus-lg"></i>
                    Add New Employee
                </a>

                <div class="dropdown">
                    <button class="dropdown-toggle"
                        style="background: none; border: none; color: inherit; font-size: 1.5rem; cursor: pointer; position: relative; display: flex; align-items: center;"
                        type="button" data-bs-toggle="dropdown" aria-expanded="false"
                        onclick="markNotificationsAsRead(this)">
                        <i class="bi bi-bell"></i>
                        @if(isset($unreadNotifCount) && $unreadNotifCount > 0)
                            <span class="notif-badge"
                                style="position: absolute; top: -8px; right: -8px; background-color: #ff4444; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold;">{{ $unreadNotifCount }}</span>
                        @endif
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow" style="width: 300px; padding: 0; z-index: 1050;">
                        <li class="p-3 border-bottom bg-light fw-bold text-dark" style="font-size: 14px;">Recent
                            Activity</li>
                        @forelse($recentActivity ?? [] as $log)
                            <li>
                                <a class="dropdown-item py-2 border-bottom" href="{{ route('activity-logs.index') }}"
                                    style="white-space: normal; line-height: 1.4;">
                                    <div class="fw-bold" style="font-size: 13px;">
                                        {{ $log->user ? $log->user->name : 'System' }}
                                    </div>
                                    <div class="text-muted" style="font-size: 12px; margin-top: 2px;">
                                        {{ \Illuminate\Support\Str::limit($log->description, 80) }}
                                    </div>
                                    <div class="text-muted mt-1" style="font-size: 10px;">
                                        {{ $log->created_at->diffForHumans() }}
                                    </div>
                                </a>
                            </li>
                        @empty
                            <li class="p-3 text-center text-muted" style="font-size: 13px;">No recent activity</li>
                        @endforelse
                        <li class="text-center bg-light">
                            <a class="dropdown-item py-2 fw-bold text-primary" href="{{ route('activity-logs.index') }}"
                                style="font-size: 13px;">View All Activity Menu</a>
                        </li>
                    </ul>
                </div>

                <div
                    style="display: flex; align-items: center; gap: 1rem; padding-left: 1rem; border-left: 1px solid #e5e7eb; height: 100%;">
                    <div style="text-align: right; font-size: 15px;">
                        <p style="margin: 0; font-weight: 600;">{{ auth()->user()->name }}</p>
                        <p style="margin: 0; color: #6b7280; font-size: 13px;">{{ auth()->user()->role_label }}</p>
                    </div>
                    <div
                        style="background-color: #0d6efd; color: white; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px; flex-shrink: 0; overflow: hidden;">
                        @if(auth()->check() && auth()->user()->profile_picture)
                            <img src="{{ Storage::url(auth()->user()->profile_picture) }}" alt="Avatar"
                                style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            {{ auth()->check() ? substr(auth()->user()->name, 0, 1) : 'U' }}
                        @endif
                    </div>
                </div>

            </div>
        </nav>

        <!-- Content Area -->
        <div class="container-fluid" style="padding: 20px;">
            <!-- Proactive Reminders Alert -->
            @if(($retirementDueCount ?? 0) > 0 || ($stepDueCount ?? 0) > 0)
                <div class="alert premium-alert d-flex align-items-center mb-4" role="alert">
                    <i class="bi bi-bell-fill fs-3 me-3"></i>
                    <div>
                        <h5 class="alert-heading mb-1" style="font-size: 16px; font-weight: 800;">Action Required
                        </h5>
                        <p class="mb-0" style="font-size: 14px;">
                            You have
                            @if(($retirementDueCount ?? 0) > 0)
                                <a href="{{ route('retirement.index') }}">{{ $retirementDueCount }} employees
                                    due for retirement</a>
                            @endif
                            @if(($retirementDueCount ?? 0) > 0 && ($stepDueCount ?? 0) > 0) and @endif
                            @if(($stepDueCount ?? 0) > 0)
                                <a href="{{ route('step-increment.index') }}">{{ $stepDueCount }} employees due for
                                    step increment</a>
                            @endif
                            that need your attention.
                        </p>
                    </div>
                </div>
            @endif

            <!-- ROW 1: 4 Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <!-- 1. Total Employees -->
                <a href="{{ route('all-data.index', ['vacant' => 'filled']) }}" style="text-decoration: none; color: inherit; display: block;">
                    <div class="dash-card">
                        <div class="dash-card-content">
                            <div>
                                <p class="dash-card-title">Total Employees</p>
                                <h3 class="dash-card-value">{{ number_format($totalEmployeesUnique ?? 0) }}</h3>
                                <p class="dash-card-subtext">Active Only, No Duplication</p>
                            </div>
                            <div class="dash-card-icon icon-blue">
                                <i class="bi bi-people"></i>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- 2. REGULAR -->
                <a href="{{ route('all-data.index', ['vacant' => 'filled', 'status' => 'P']) }}" style="text-decoration: none; color: inherit; display: block;">
                    <div class="dash-card">
                        <div class="dash-card-content">
                            <div>
                                <p class="dash-card-title">Regular</p>
                                <h3 class="dash-card-value">{{ number_format($regularTotalCount ?? 0) }}</h3>
                                <p class="dash-card-subtext" style="font-size:10px;">Elected, Coterminus, Permanent...</p>
                            </div>
                            <div class="dash-card-icon icon-green">
                                <i class="bi bi-person-badge"></i>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- 3. MALE vs FEMALE (REGULAR) -->
                <div class="dash-card">
                    <div class="dash-card-content" style="width: 100%;">
                        <div style="width: 100%;">
                            <p class="dash-card-title mb-2">Male vs Female <span style="font-size: 11px; color:#6b7280; text-transform:none;">(Regular)</span></p>
                            <div style="display: flex; justify-content: space-around; align-items: center; margin-top: 10px;">
                                <div style="display: flex; flex-direction: column; align-items: center;">
                                    <i class="bi bi-gender-male" style="font-size: 24px; color: #3b82f6;"></i>
                                    <h4 style="margin: 0; font-weight: 700; color: #0f172a;">{{ number_format($regularMale ?? 0) }}</h4>
                                </div>
                                <div style="width: 1px; height: 30px; background: #e2e8f0;"></div>
                                <div style="display: flex; flex-direction: column; align-items: center;">
                                    <i class="bi bi-gender-female" style="font-size: 24px; color: #ec4899;"></i>
                                    <h4 style="margin: 0; font-weight: 700; color: #0f172a;">{{ number_format($regularFemale ?? 0) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. CASUAL : Total -->
                <a href="{{ route('casual.index') }}" style="text-decoration: none; color: inherit; display: block;">
                    <div class="dash-card">
                        <div class="dash-card-content">
                            <div>
                                <p class="dash-card-title">Casual</p>
                                <h3 class="dash-card-value">{{ number_format($casualOnlyTotal ?? 0) }}</h3>
                                <p class="dash-card-subtext">Total</p>
                            </div>
                            <div class="dash-card-icon icon-orange">
                                <i class="bi bi-person-lines-fill"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- ROW 2: 4 Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
                
                <!-- 5. MALE vs FEMALE (CASUAL) -->
                <div class="dash-card">
                    <div class="dash-card-content" style="width: 100%;">
                        <div style="width: 100%;">
                            <p class="dash-card-title mb-2">Male vs Female <span style="font-size: 11px; color:#6b7280; text-transform:none;">(Casual)</span></p>
                            <div style="display: flex; justify-content: space-around; align-items: center; margin-top: 10px;">
                                <div style="display: flex; flex-direction: column; align-items: center;">
                                    <i class="bi bi-gender-male" style="font-size: 24px; color: #3b82f6;"></i>
                                    <h4 style="margin: 0; font-weight: 700; color: #0f172a;">{{ number_format($casualMale ?? 0) }}</h4>
                                </div>
                                <div style="width: 1px; height: 30px; background: #e2e8f0;"></div>
                                <div style="display: flex; flex-direction: column; align-items: center;">
                                    <i class="bi bi-gender-female" style="font-size: 24px; color: #ec4899;"></i>
                                    <h4 style="margin: 0; font-weight: 700; color: #0f172a;">{{ number_format($casualFemale ?? 0) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 6. JOB ORDER -->
                <a href="{{ route('job-orders.index') }}" style="text-decoration: none; color: inherit; display: block;">
                    <div class="dash-card">
                        <div class="dash-card-content">
                            <div>
                                <p class="dash-card-title">Job Order</p>
                                <h3 class="dash-card-value">{{ number_format($joTotalActive ?? 0) }}</h3>
                            </div>
                            <div class="dash-card-icon icon-purple">
                                <i class="bi bi-file-earmark-person-fill"></i>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- 7. VACANT POSITIONS -->
                <a href="{{ route('all-data.index', ['vacant' => 'vacant']) }}" style="text-decoration: none; color: inherit; display: block;">
                    <div class="dash-card">
                        <div class="dash-card-content">
                            <div>
                                <p class="dash-card-title">Vacant Positions</p>
                                <h3 class="dash-card-value">{{ number_format($vacantPositionsTotal ?? 0) }}</h3>
                            </div>
                            <div class="dash-card-icon" style="background:#fff1f2; color:#e11d48;">
                                <i class="bi bi-exclamation-circle"></i>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- 8. MALE vs. FEMALE (JO) -->
                <div class="dash-card">
                    <div class="dash-card-content" style="width: 100%;">
                        <div style="width: 100%;">
                            <p class="dash-card-title mb-2">Male vs Female <span style="font-size: 11px; color:#6b7280; text-transform:none;">(JO)</span></p>
                            <div style="display: flex; justify-content: space-around; align-items: center; margin-top: 10px;">
                                <div style="display: flex; flex-direction: column; align-items: center;">
                                    <i class="bi bi-gender-male" style="font-size: 24px; color: #3b82f6;"></i>
                                    <h4 style="margin: 0; font-weight: 700; color: #0f172a;">{{ number_format($joMale ?? 0) }}</h4>
                                </div>
                                <div style="width: 1px; height: 30px; background: #e2e8f0;"></div>
                                <div style="display: flex; flex-direction: column; align-items: center;">
                                    <i class="bi bi-gender-female" style="font-size: 24px; color: #ec4899;"></i>
                                    <h4 style="margin: 0; font-weight: 700; color: #0f172a;">{{ number_format($joFemale ?? 0) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- FOURTH ROW: Charts -->
            <div
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <!-- Pie Chart: Filled vs Vacant (REGULAR PLANTILLA ONLY) -->
                <div
                    style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                        <h3 style="margin:0; color: #111827; font-size: 18px; font-weight: 600;">Filled vs Vacant
                            Positions <span style="font-size:13px; font-weight:500; color:#6b7280; text-transform:none;">(Regular Plantilla Only)</span></h3>
                        <span
                            style="font-size:12px; color:#6b7280; background:#f3f4f6; padding:4px 10px; border-radius:99px;">Total:
                            {{ ($regularTotalCount ?? 0) + ($vacantPositionsTotal ?? 0) }}</span>
                    </div>
                    <div style="position: relative; height: 260px;">
                        <canvas id="pieChart"></canvas>
                    </div>
                    @php
                        $pvTotal = ($regularTotalCount ?? 0) + ($vacantPositionsTotal ?? 0);
                        $pvFilledPct = $pvTotal > 0 ? round(($regularTotalCount ?? 0) / $pvTotal * 100, 1) : 0;
                        $pvVFundPct = $pvTotal > 0 ? round(($vacantPositionsTotal ?? 0) / $pvTotal * 100, 1) : 0;
                    @endphp
                    <div style="display:flex; gap:8px; flex-wrap:wrap; margin-top:14px; justify-content:center;">
                        <div
                            style="display:flex; align-items:center; gap:6px; background:#eff6ff; border-radius:8px; padding:7px 12px; flex:1; min-width:120px;">
                            <span
                                style="width:10px;height:10px;border-radius:50%;background:#3b82f6;flex-shrink:0;"></span>
                            <div>
                                <div style="font-size:11px;color:#6b7280;font-weight:500;">Filled (Regular)</div>
                                <div style="font-size:15px;font-weight:700;color:#3b82f6;">{{ $pvFilledPct }}%</div>
                            </div>
                        </div>
                        <div
                            style="display:flex; align-items:center; gap:6px; background:#fff5f5; border-radius:8px; padding:7px 12px; flex:1; min-width:120px;">
                            <span
                                style="width:10px;height:10px;border-radius:50%;background:#f43f5e;flex-shrink:0;"></span>
                            <div>
                                <div style="font-size:11px;color:#6b7280;font-weight:500;">Vacant</div>
                                <div style="font-size:15px;font-weight:700;color:#f43f5e;">{{ $pvVFundPct }}%</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bar Chart: Employees per Unit -->
                <div
                    style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <h3 style="margin: 0 0 20px 0; color: #111827; font-size: 18px; font-weight: 600;">Employees per
                        Organizational Unit</h3>
                    <div style="position: relative; height: 300px;">
                        <canvas id="barChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Extra charts removed as per arrangement -->

            <!-- BOTTOM ROW: 3 Special Counts -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <!-- PWD Count Card -->
                <a href="{{ route('all-data.index', ['pwd' => 1]) }}"
                    style="text-decoration: none; color: inherit; display: block;">
                    <div
                        style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 5px solid #0d6efd; height: 100%; transition: transform 0.2s;">
                        <div style="display: flex; justify-content: flex-start; align-items: flex-start;">
                            <div>
                                <p style="margin: 0; color: #6b7280; font-size: 14px; font-weight: 500;">PWD Count</p>
                                <h3 style="margin: 8px 0 0 0; color: #111827; font-size: 32px; font-weight: 700;">
                                    {{ $pwdCount ?? 0 }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- IP Count Card -->
                <a href="{{ route('all-data.index', ['ip' => 1]) }}"
                    style="text-decoration: none; color: inherit; display: block;">
                    <div
                        style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 5px solid #0d6efd; height: 100%; transition: transform 0.2s;">
                        <div style="display: flex; justify-content: flex-start; align-items: flex-start;">
                            <div>
                                <p style="margin: 0; color: #6b7280; font-size: 14px; font-weight: 500;">IP Count</p>
                                <h3 style="margin: 8px 0 0 0; color: #111827; font-size: 32px; font-weight: 700;">
                                    {{ $ipCount ?? 0 }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Solo Parent Count Card -->
                <a href="{{ route('all-data.index', ['solo_parent' => 1]) }}"
                    style="text-decoration: none; color: inherit; display: block;">
                    <div
                        style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 5px solid #0d6efd; height: 100%; transition: transform 0.2s;">
                        <div style="display: flex; justify-content: flex-start; align-items: flex-start;">
                            <div>
                                <p style="margin: 0; color: #6b7280; font-size: 14px; font-weight: 500;">Solo Parent
                                    Count</p>
                                <h3 style="margin: 8px 0 0 0; color: #111827; font-size: 32px; font-weight: 700;">
                                    {{ $soloParentCount ?? 0 }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- BIRTHDAYS OF THE MONTH WIDGET -->
            @php
                $todayCelebrants = isset($monthlyBirthdays) ? $monthlyBirthdays->filter(fn($e) => $e->date_of_birth->day == now()->day) : collect();
                $otherCelebrants = isset($monthlyBirthdays) ? $monthlyBirthdays->filter(fn($e) => $e->date_of_birth->day != now()->day) : collect();
            @endphp

            <style>
                /* â•â• Birthday Widget — WONDERFUL EDITION â•â• */

                /* ── Keyframes ── */
                @keyframes bdaySlideIn {
                    from {
                        opacity: 0;
                        transform: translateY(20px);
                    }

                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                @keyframes bdayFloat {

                    0%,
                    100% {
                        transform: translateY(0) rotate(-5deg);
                        opacity: 0;
                    }

                    15% {
                        opacity: 1;
                    }

                    85% {
                        opacity: .8;
                    }

                    100% {
                        transform: translateY(-55px) rotate(18deg);
                        opacity: 0;
                    }
                }

                @keyframes bdayBounce {

                    0%,
                    100% {
                        transform: translateY(0) scale(1);
                    }

                    40% {
                        transform: translateY(-7px) scale(1.07);
                    }

                    60% {
                        transform: translateY(-3px) scale(1.02);
                    }
                }

                @keyframes bdayShimmer {
                    0% {
                        background-position: 200% 0;
                    }

                    100% {
                        background-position: -200% 0;
                    }
                }

                @keyframes bdayPulse {

                    0%,
                    100% {
                        opacity: 1;
                        transform: scale(1);
                    }

                    50% {
                        opacity: .6;
                        transform: scale(.96);
                    }
                }

                @keyframes bdayGlow {

                    0%,
                    100% {
                        box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3);
                    }

                    50% {
                        box-shadow: 0 4px 22px rgba(13, 110, 253, 0.65);
                    }
                }

                @keyframes bdayGlowOrange {

                    0%,
                    100% {
                        box-shadow: 0 4px 12px rgba(234, 88, 12, 0.35);
                    }

                    50% {
                        box-shadow: 0 6px 28px rgba(234, 88, 12, 0.7), 0 0 0 3px rgba(251, 146, 60, 0.15);
                    }
                }

                @keyframes bdaySunRay {
                    0% {
                        transform: rotate(0deg);
                    }

                    100% {
                        transform: rotate(360deg);
                    }
                }

                /* Aurora body background shift */
                @keyframes bdayAurora {
                    0% {
                        background-position: 0% 50%;
                    }

                    50% {
                        background-position: 100% 50%;
                    }

                    100% {
                        background-position: 0% 50%;
                    }
                }

                /* Body confetti dots drifting up */
                @keyframes bdayDot {
                    0% {
                        transform: translateY(0) scale(.8) rotate(0deg);
                        opacity: 0;
                    }

                    10% {
                        opacity: .7;
                    }

                    90% {
                        opacity: .4;
                    }

                    100% {
                        transform: translateY(-320px) scale(1.1) rotate(360deg);
                        opacity: 0;
                    }
                }

                /* Orb pulse */
                @keyframes bdayOrb {

                    0%,
                    100% {
                        transform: scale(1);
                        opacity: .55;
                    }

                    50% {
                        transform: scale(1.15);
                        opacity: .8;
                    }
                }

                /* ── Widget outer ── */
                .bday-fx-widget {
                    border-radius: 22px;
                    overflow: hidden;
                    box-shadow: 0 20px 60px rgba(13, 110, 253, 0.14), 0 4px 16px rgba(0, 0, 0, 0.06);
                    border: 1px solid #e0e7ff;
                    animation: bdaySlideIn .65s ease both;
                }

                /* ── Header ── */
                .bday-fx-header {
                    background: linear-gradient(135deg, #1e1b4b 0%, #1d4ed8 30%, #7c3aed 58%, #db2777 80%, #ea580c 100%);
                    padding: 28px 30px;
                    position: relative;
                    overflow: hidden;
                    box-shadow: 0 8px 32px rgba(99, 102, 241, 0.2);
                }

                .bday-fx-header .hblob {
                    position: absolute;
                    border-radius: 50%;
                    pointer-events: none;
                }

                .bday-fx-header .hblob1 {
                    width: 220px;
                    height: 220px;
                    background: rgba(255, 255, 255, 0.07);
                    top: -80px;
                    right: -55px;
                }

                .bday-fx-header .hblob2 {
                    width: 150px;
                    height: 150px;
                    background: rgba(255, 255, 255, 0.05);
                    bottom: -60px;
                    left: 6%;
                }

                .bday-fx-header .hblob3 {
                    width: 90px;
                    height: 90px;
                    background: rgba(255, 255, 255, 0.08);
                    top: 10px;
                    left: 38%;
                }

                .bday-fx-particles {
                    position: absolute;
                    inset: 0;
                    overflow: hidden;
                    pointer-events: none;
                    z-index: 0;
                }

                .bday-fx-particles span {
                    position: absolute;
                    font-size: 18px;
                    opacity: 0;
                    animation: bdayFloat 4.5s ease-in-out infinite;
                }

                .bday-fx-header-inner {
                    position: relative;
                    z-index: 2;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    flex-wrap: wrap;
                    gap: 12px;
                }

                .bday-fx-title-row {
                    display: flex;
                    align-items: center;
                    gap: 14px;
                }

                .bday-fx-icon {
                    width: 54px;
                    height: 54px;
                    border-radius: 15px;
                    background: rgba(255, 255, 255, 0.17);
                    backdrop-filter: blur(10px);
                    border: 1.5px solid rgba(255, 255, 255, 0.35);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 26px;
                    animation: bdayBounce 2.4s ease-in-out infinite;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
                }

                .bday-fx-header h3 {
                    margin: 0;
                    color: #fff;
                    font-size: 21px;
                    font-weight: 900;
                    letter-spacing: -.4px;
                    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
                }

                .bday-fx-header .sub {
                    margin: 5px 0 0;
                    color: rgba(255, 255, 255, .75);
                    font-size: 13px;
                    font-weight: 500;
                }

                .bday-fx-badge {
                    background: rgba(255, 255, 255, 0.2);
                    backdrop-filter: blur(10px);
                    color: #fff;
                    border: 1.5px solid rgba(255, 255, 255, .4);
                    padding: 8px 20px;
                    border-radius: 99px;
                    font-size: 13px;
                    font-weight: 800;
                    white-space: nowrap;
                    letter-spacing: .2px;
                    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
                }

                /* ── Body — aurora animated background ── */
                .bday-fx-body {
                    position: relative;
                    overflow: hidden;
                    background: linear-gradient(-45deg, #f0f4ff, #fff, #f5f0ff, #f0faff, #fff7f0);
                    background-size: 400% 400%;
                    animation: bdayAurora 10s ease infinite;
                    padding: 26px 28px;
                }

                /* Dot-grid overlay on body */
                .bday-fx-body::before {
                    content: '';
                    position: absolute;
                    inset: 0;
                    background-image: radial-gradient(circle, rgba(99, 102, 241, 0.08) 1px, transparent 1px);
                    background-size: 22px 22px;
                    pointer-events: none;
                    z-index: 0;
                }

                /* Glowing orbs */
                .bday-orb {
                    position: absolute;
                    border-radius: 50%;
                    pointer-events: none;
                    filter: blur(55px);
                    z-index: 0;
                    animation: bdayOrb 5s ease-in-out infinite;
                }

                .bday-orb1 {
                    width: 220px;
                    height: 220px;
                    background: rgba(99, 102, 241, 0.12);
                    top: -50px;
                    right: -60px;
                }

                .bday-orb2 {
                    width: 180px;
                    height: 180px;
                    background: rgba(236, 72, 153, 0.09);
                    bottom: -50px;
                    left: -40px;
                    animation-delay: 2.5s;
                }

                .bday-orb3 {
                    width: 130px;
                    height: 130px;
                    background: rgba(245, 158, 11, 0.08);
                    top: 30%;
                    left: 45%;
                    animation-delay: 1.2s;
                }

                /* Body floating confetti dots */
                .bday-confetti {
                    position: absolute;
                    inset: 0;
                    overflow: hidden;
                    pointer-events: none;
                    z-index: 0;
                }

                .bday-confetti span {
                    position: absolute;
                    border-radius: 50%;
                    opacity: 0;
                    animation: bdayDot linear infinite;
                }

                /* Make card content sit above background */
                .bday-fx-body>*:not(.bday-orb):not(.bday-confetti) {
                    position: relative;
                    z-index: 1;
                }

                /* ── Today spotlight ── */
                .bday-fx-today {
                    position: relative;
                    overflow: hidden;
                    background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 55%, #fef9c3 100%);
                    border: 2px solid #fb923c;
                    border-radius: 18px;
                    padding: 20px 22px;
                    margin-bottom: 24px;
                    box-shadow: 0 6px 28px rgba(234, 88, 12, 0.14), inset 0 1px 0 rgba(255, 255, 255, 0.9);
                }

                /* shimmer sweep */
                .bday-fx-today::after {
                    content: '';
                    position: absolute;
                    inset: 0;
                    background: linear-gradient(110deg, transparent 30%, rgba(255, 255, 255, .7) 50%, transparent 70%);
                    background-size: 200% 100%;
                    animation: bdayShimmer 2.2s ease-in-out infinite;
                    pointer-events: none;
                    border-radius: 18px;
                }

                /* Sun-ray decoration */
                .bday-fx-today::before {
                    content: 'â˜€ï¸';
                    position: absolute;
                    right: 18px;
                    top: 50%;
                    transform: translateY(-50%);
                    font-size: 56px;
                    opacity: .1;
                    pointer-events: none;
                    line-height: 1;
                }

                .bday-fx-today-label {
                    font-size: 12px;
                    font-weight: 900;
                    background: linear-gradient(90deg, #ea580c, #d97706);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                    background-clip: text;
                    text-transform: uppercase;
                    letter-spacing: 1.3px;
                    margin: 0 0 16px;
                    display: flex;
                    align-items: center;
                    gap: 7px;
                    animation: bdayPulse 1.8s ease-in-out infinite;
                    position: relative;
                    z-index: 1;
                }

                /* Fix icon color inside gradient text label */
                .bday-fx-today-label i {
                    -webkit-text-fill-color: #ea580c;
                    color: #ea580c;
                }

                /* ── Cards ── */
                .bday-fx-card {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    border-radius: 14px;
                    padding: 13px 15px;
                    transition: all .28s cubic-bezier(.34, 1.56, .64, 1);
                    position: relative;
                    z-index: 1;
                    animation: bdaySlideIn .5s ease both;
                    backdrop-filter: blur(4px);
                }

                .bday-fx-card.today-card {
                    background: linear-gradient(120deg, rgba(255, 237, 213, 0.55) 0%, rgba(255, 255, 255, 0.96) 100%);
                    border: 1.5px solid rgba(251, 146, 60, 0.35);
                    border-left: 4px solid #ea580c;
                    box-shadow: 0 3px 14px rgba(234, 88, 12, 0.1);
                }

                .bday-fx-card.other-card {
                    background: linear-gradient(120deg, rgba(239, 246, 255, 0.45) 0%, rgba(255, 255, 255, 0.93) 100%);
                    border: 1px solid rgba(191, 219, 254, 0.5);
                    border-left: 4px solid #3b82f6;
                    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.07);
                }

                .bday-fx-card:hover {
                    transform: translateY(-4px) scale(1.015);
                }

                .bday-fx-card.today-card:hover {
                    box-shadow: 0 12px 32px rgba(234, 88, 12, 0.22);
                    border-color: #fb923c;
                }

                .bday-fx-card.other-card:hover {
                    box-shadow: 0 12px 28px rgba(13, 110, 253, 0.16);
                    border-color: #93c5fd;
                }

                /* ── Card internals ── */
                .bday-fx-info {
                    min-width: 0;
                    flex: 1;
                }

                .bday-fx-name {
                    font-weight: 800;
                    font-size: 14px;
                    color: #0f172a;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    letter-spacing: -.1px;
                }

                .bday-fx-pos {
                    font-size: 12px;
                    color: #64748b;
                    margin-top: 2px;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }

                /* ── Date display — no rectangle, clean text ── */
                .bday-date-text {
                    font-size: 13px;
                    font-weight: 800;
                    letter-spacing: .3px;
                    display: flex;
                    align-items: center;
                    justify-content: flex-end;
                    gap: 4px;
                    white-space: nowrap;
                }

                .bday-date-text.is-orange {
                    color: #ea580c;
                }

                .bday-date-text.is-blue {
                    color: #64748b;
                }

                .bday-fx-turns-orange {
                    font-size: 10.5px;
                    font-weight: 700;
                    color: #f97316;
                    margin-top: 4px;
                    text-align: right;
                }

                .bday-fx-turns-blue {
                    font-size: 10.5px;
                    color: #94a3b8;
                    font-weight: 600;
                    margin-top: 4px;
                    text-align: right;
                }

                /* ── Section divider ── */
                .bday-fx-section {
                    font-size: 11px;
                    font-weight: 800;
                    text-transform: uppercase;
                    letter-spacing: 1.2px;
                    color: #94a3b8;
                    margin: 0 0 14px;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }

                .bday-fx-section::after {
                    content: '';
                    flex: 1;
                    height: 1px;
                    background: linear-gradient(90deg, #c7d2fe, transparent);
                }

                /* ── Scroll ── */
                .bday-fx-scroll {
                    max-height: 380px;
                    overflow-y: auto;
                    overflow-x: hidden;
                    padding-right: 6px;
                }

                .bday-fx-scroll::-webkit-scrollbar {
                    width: 5px;
                }

                .bday-fx-scroll::-webkit-scrollbar-track {
                    background: rgba(248, 250, 252, 0.8);
                    border-radius: 10px;
                }

                .bday-fx-scroll::-webkit-scrollbar-thumb {
                    background: linear-gradient(#c7d2fe, #a5b4fc);
                    border-radius: 10px;
                }

                /* ── Card stagger ── */
                .bday-fx-card:nth-child(1) {
                    animation-delay: .04s
                }

                .bday-fx-card:nth-child(2) {
                    animation-delay: .10s
                }

                .bday-fx-card:nth-child(3) {
                    animation-delay: .16s
                }

                .bday-fx-card:nth-child(4) {
                    animation-delay: .22s
                }

                .bday-fx-card:nth-child(5) {
                    animation-delay: .28s
                }

                .bday-fx-card:nth-child(6) {
                    animation-delay: .34s
                }

                /* ── Empty state ── */
                .bday-fx-empty {
                    text-align: center;
                    padding: 52px 24px;
                    border-radius: 16px;
                    border: 2px dashed #e0e7ff;
                    background: rgba(248, 250, 252, 0.7);
                }

                }
            </style>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="bday-fx-widget">

                        {{-- Animated Header --}}
                        <div class="bday-fx-header">
                            <div class="hblob hblob1"></div>
                            <div class="hblob hblob2"></div>
                            <div class="hblob hblob3"></div>
                            <div class="bday-fx-particles">
                                <span style="left:4%;animation-delay:0s;animation-duration:4s;">🎂</span>
                                <span style="left:15%;animation-delay:.8s;animation-duration:5s;">✨</span>
                                <span style="left:28%;animation-delay:1.5s;animation-duration:4.2s;">🎁</span>
                                <span style="left:45%;animation-delay:.4s;animation-duration:4.8s;">🎊</span>
                                <span style="left:60%;animation-delay:1.1s;animation-duration:3.9s;">🎈</span>
                                <span style="left:75%;animation-delay:.6s;animation-duration:4.5s;">⭐</span>
                                <span style="left:88%;animation-delay:1.9s;animation-duration:4.3s;">🎉</span>
                            </div>
                            <div class="bday-fx-header-inner">
                                <div class="bday-fx-title-row">
                                    <div class="bday-fx-icon">🎁</div>
                                    <div>
                                        <h3>🎂 Birthdays This Month</h3>
                                        <p class="sub">{{ now()->format('F Y') }} &nbsp;·&nbsp; Celebrating our team!
                                        </p>
                                    </div>
                                </div>
                                <div class="bday-fx-badge">
                                    🎈 {{ isset($monthlyBirthdays) ? count($monthlyBirthdays) : 0 }}
                                    Celebrant{{ (isset($monthlyBirthdays) ? count($monthlyBirthdays) : 0) != 1 ? 's' : '' }}
                                </div>
                            </div>
                        </div>

                        {{-- Body with animated background --}}
                        <div class="bday-fx-body">
                            <!-- Glowing orbs -->
                            <div class="bday-orb bday-orb1"></div>
                            <div class="bday-orb bday-orb2"></div>
                            <div class="bday-orb bday-orb3"></div>
                            <!-- Floating confetti dots -->
                            <div class="bday-confetti">
                                <span
                                    style="left:7%;width:8px;height:8px;background:#6366f1;animation-duration:6s;animation-delay:0s;"></span>
                                <span
                                    style="left:18%;width:6px;height:6px;background:#ec4899;animation-duration:7.5s;animation-delay:.8s;"></span>
                                <span
                                    style="left:30%;width:10px;height:10px;background:#f59e0b;animation-duration:5.8s;animation-delay:1.4s;"></span>
                                <span
                                    style="left:42%;width:7px;height:7px;background:#10b981;animation-duration:6.8s;animation-delay:.3s;"></span>
                                <span
                                    style="left:55%;width:9px;height:9px;background:#3b82f6;animation-duration:7s;animation-delay:1.1s;"></span>
                                <span
                                    style="left:65%;width:6px;height:6px;background:#a855f7;animation-duration:6.2s;animation-delay:.6s;"></span>
                                <span
                                    style="left:76%;width:8px;height:8px;background:#ef4444;animation-duration:7.3s;animation-delay:1.8s;"></span>
                                <span
                                    style="left:85%;width:7px;height:7px;background:#0ea5e9;animation-duration:5.5s;animation-delay:.9s;"></span>
                                <span
                                    style="left:93%;width:9px;height:9px;background:#f97316;animation-duration:6.6s;animation-delay:2s;"></span>
                                <span
                                    style="left:12%;width:5px;height:5px;background:#8b5cf6;animation-duration:8s;animation-delay:2.5s;"></span>
                                <span
                                    style="left:48%;width:6px;height:6px;background:#14b8a6;animation-duration:7.8s;animation-delay:1.6s;"></span>
                                <span
                                    style="left:70%;width:8px;height:8px;background:#f43f5e;animation-duration:6.4s;animation-delay:3s;"></span>
                            </div>
                            @if(isset($monthlyBirthdays) && count($monthlyBirthdays) > 0)

                                {{-- Today's Celebrants --}}
                                @if($todayCelebrants->count() > 0)
                                    <div class="bday-fx-today">
                                        <p class="bday-fx-today-label">
                                            <i class="bi bi-stars"></i> 🎉 Today's Celebrants — {{ now()->format('F j') }}
                                        </p>
                                        <div class="row g-2">
                                            @foreach($todayCelebrants as $emp)
                                                @php $age = now()->year - $emp->date_of_birth->year; @endphp
                                                <div class="col-12 col-md-6 col-xl-4">
                                                    <div class="bday-fx-card today-card">
                                                        <div class="bday-fx-info">
                                                            <div class="bday-fx-name">{{ $emp->first_name }} {{ $emp->last_name }}
                                                            </div>
                                                            <div class="bday-fx-pos" title="{{ $emp->position_title }}">
                                                                {{ $emp->position_title }}
                                                            </div>
                                                        </div>
                                                        <div style="flex-shrink:0;text-align:right;margin-left:12px;">
                                                            <div class="bday-date-text is-orange">🗓
                                                                {{ $emp->date_of_birth->format('M d') }}
                                                            </div>
                                                            <div class="bday-fx-turns-orange">🎂 Turns {{ $age }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- Other Celebrants --}}
                                @if($otherCelebrants->count() > 0)
                                    @if($todayCelebrants->count() > 0)
                                        <p class="bday-fx-section">Other Celebrants This Month</p>
                                    @endif
                                    <div class="bday-fx-scroll">
                                        <div class="row g-2">
                                            @foreach($otherCelebrants as $emp)
                                                @php
                                                    $age = now()->year - $emp->date_of_birth->year;
                                                    $bday = \Carbon\Carbon::create(now()->year, $emp->date_of_birth->month, $emp->date_of_birth->day);
                                                    $daysUntil = now()->startOfDay()->diffInDays($bday->startOfDay(), false);
                                                    $daysLabel = $daysUntil > 0 ? "in {$daysUntil}d" : (abs($daysUntil) . 'd ago');
                                                @endphp
                                                <div class="col-12 col-md-6 col-xl-4">
                                                    <div class="bday-fx-card other-card">
                                                        <div class="bday-fx-info">
                                                            <div class="bday-fx-name">{{ $emp->first_name }} {{ $emp->last_name }}
                                                            </div>
                                                            <div class="bday-fx-pos" title="{{ $emp->position_title }}">
                                                                {{ $emp->position_title }}
                                                            </div>
                                                        </div>
                                                        <div style="flex-shrink:0;text-align:right;margin-left:12px;">
                                                            <div class="bday-date-text is-blue">🗓
                                                                {{ $emp->date_of_birth->format('M d') }}
                                                            </div>
                                                            <div class="bday-fx-turns-blue">Turns {{ $age }} · {{ $daysLabel }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                            @else
                                <div class="bday-fx-empty">
                                    <div
                                        style="width:72px;height:72px;background:white;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto;box-shadow:0 4px 10px rgba(0,0,0,0.06);font-size:2rem;animation:bdayBounce 2.5s ease-in-out infinite;">
                                        🎂</div>
                                    <p style="font-size:16px;font-weight:700;color:#1e293b;margin:20px 0 6px;">No Birthdays
                                        This Month</p>
                                    <p style="font-size:14px;color:#94a3b8;margin:0;">Check back next month!</p>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Premium Logout Confirmation Modal -->
    <div id="logoutModal" class="logout-overlay">
        <div class="logout-modal-card">
            <button type="button" class="logout-modal-close" onclick="closeLogoutModal()" title="Close">
                <i class="bi bi-x-lg"></i>
            </button>
            <div class="logout-modal-top">
                <div class="logout-modal-icon-wrap">
                    <i class="bi bi-box-arrow-right"></i>
                </div>
                <h2 class="logout-modal-title">Sign Out?</h2>
                <p class="logout-modal-subtitle">You're about to leave the HDMS- Human Resource Data Management System
                    portal.</p>
            </div>
            <div class="logout-modal-body">
                <div class="logout-modal-info">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span>Any unsaved changes will be lost. Make sure you've saved your work before signing out.</span>
                </div>
                <div class="logout-modal-actions">
                    <button type="button" class="logout-btn-cancel" onclick="closeLogoutModal()">
                        <i class="bi bi-arrow-left"></i> Stay
                    </button>
                    <form method="POST" action="{{ route('logout') }}" style="flex:1;margin:0;" id="logoutForm">
                        @csrf
                        <button type="submit" class="logout-btn-confirm" style="width:100%;">
                            <i class="bi bi-box-arrow-right"></i> Yes, Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <!-- Modal Control Scripts -->
    <script>
        function openLogoutModal() {
            document.getElementById('logoutModal').classList.add('active');
        }

        function closeLogoutModal() {
            document.getElementById('logoutModal').classList.remove('active');
        }

        // Close modal when clicking outside the card
        document.getElementById('logoutModal').addEventListener('click', function (event) {
            if (event.target === this) {
                closeLogoutModal();
            }
        });

        let myPieChart, myBarChart;
        // Initialize Pie Chart - Filled vs Vacant
        const pieCtx = document.getElementById('pieChart')?.getContext('2d');
        if (pieCtx) {
            const chartData = {
                regularFilled: {{ $regularTotalCount ?? 0 }},
                vacantTotal: {{ $vacantPositionsTotal ?? 0 }}
            };

            // Register center-text plugin for doughnut charts
            const centerTextPlugin = {
                id: 'centerText',
                afterDraw(chart) {
                    if (chart.config.options.plugins.centerText && chart.config.options.plugins.centerText.display) {
                        const { ctx, chartArea: { left, top, right, bottom } } = chart;
                        const cx = (left + right) / 2;
                        const cy = (top + bottom) / 2;
                        const cfg = chart.config.options.plugins.centerText;
                        ctx.save();
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.font = `bold ${cfg.fontSize || 28}px Inter, Segoe UI, sans-serif`;
                        ctx.fillStyle = cfg.color || '#111827';
                        ctx.fillText(cfg.text, cx, cy - 10);
                        ctx.font = `500 ${(cfg.fontSize || 28) * 0.45}px Inter, Segoe UI, sans-serif`;
                        ctx.fillStyle = '#6b7280';
                        ctx.fillText(cfg.subText || '', cx, cy + 14);
                        ctx.restore();
                    }
                }
            };
            Chart.register(centerTextPlugin);

            myPieChart = new Chart(pieCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Filled (Regular)', 'Vacant'],
                    datasets: [{
                        data: [chartData.regularFilled, chartData.vacantTotal],
                        backgroundColor: ['#3b82f6', '#f43f5e'],
                        borderColor: ['#fff', '#fff'],
                        borderWidth: 3,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    onClick: function (event, elements) {
                        if (elements.length > 0) {
                            const index = elements[0].index;
                            const label = this.data.labels[index];
                            if (label === 'Filled (Regular)') {
                                window.location.href = "{{ route('all-data.index', ['vacant' => 'filled']) }}";
                            } else if (label === 'Vacant') {
                                window.location.href = "{{ route('all-data.index', ['vacant' => 'vacant']) }}";
                            }
                        }
                    },
                    onHover: (event, chartElement) => {
                        event.native.target.style.cursor = chartElement[0] ? 'pointer' : 'default';
                    },
                    plugins: {
                        legend: { display: false },
                        centerText: {
                            display: true,
                            text: chartData.regularFilled + chartData.vacantTotal,
                            subText: 'Total Positions',
                            fontSize: 26,
                            color: '#111827'
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15,23,42,0.9)',
                            titleFont: { size: 13, weight: 'bold' },
                            bodyFont: { size: 13 },
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                label: function (context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const value = context.parsed;
                                    const pct = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return ` ${context.label}: ${value} (${pct}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Initialize Bar Chart - Employees per Unit
        const barCtx = document.getElementById('barChart')?.getContext('2d');
        if (barCtx) {
            const barChartData = @json($employeesPerUnit ?? []);

            // Prepare data for chart, truncate long names for x-axis
            const originalNames = Object.keys(barChartData).length > 0 ? Object.keys(barChartData) : ['No Data'];
            const unitNames = originalNames.map(n => n.length > 20 ? n.substring(0, 20) + '...' : n);
            const unitCounts = Object.values(barChartData).length > 0 ? Object.values(barChartData) : [0];

            // Create a premium vertical gradient for the bars
            const gradient = barCtx.createLinearGradient(0, 0, 0, 350);
            gradient.addColorStop(0, '#3b82f6'); // Bright blue
            gradient.addColorStop(1, '#1e3a8a'); // Deep dark blue

            // Set global font settings for a modern look
            Chart.defaults.font.family = "'Inter', 'Segoe UI', 'Roboto', sans-serif";
            Chart.defaults.color = '#4b5563';


            // Initialize Pie Chart - Workforce Distribution (Permanent vs Casual vs Job Order)
            const distCtx = document.getElementById('distChart')?.getContext('2d');
            if (distCtx) {
                const distData = {
                    permanent: {{ $permanentEmployeesCount ?? 0 }},
                    casual: {{ $casualFilled ?? 0 }},
                    jobOrder: {{ $jobOrderTotal ?? 0 }}
            };

                new Chart(distCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Plantilla (Permanent)', 'Casual', 'Job Order'],
                        datasets: [{
                            data: [distData.permanent, distData.casual, distData.jobOrder],
                            backgroundColor: ['#10b981', '#3b82f6', '#f59e0b'],
                            borderColor: ['#fff', '#fff', '#fff'],
                            borderWidth: 3,
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: { display: false },
                            centerText: {
                                display: true,
                                text: distData.permanent + distData.casual + distData.jobOrder,
                                subText: 'Total Employees',
                                fontSize: 26,
                                color: '#111827'
                            },
                            tooltip: {
                                backgroundColor: 'rgba(15, 23, 42, 0.9)',
                                titleFont: { size: 13, weight: 'bold' },
                                bodyFont: { size: 13 },
                                padding: 12,
                                cornerRadius: 8,
                                callbacks: {
                                    label: function (context) {
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const value = context.parsed;
                                        const pct = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return ` ${context.label}: ${value} (${pct}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Initialize Pie Chart - Plantilla Positions (Regular)
            const plantillaRegularCtx = document.getElementById('plantillaRegularPieChart')?.getContext('2d');
            if (plantillaRegularCtx) {
                const ppData = {
                    permanent: {{ $plantillaPermCount ?? 0 }},
                    elected:   {{ $plantillaElectCount ?? 0 }},
                    coter:     {{ $plantillaCoterCount ?? 0 }},
                    temporary: {{ $plantillaTempCount ?? 0 }},
                    parttime:  {{ $plantillaPartCount ?? 0 }},
                };
                const ppTotal = ppData.permanent + ppData.elected + ppData.coter + ppData.temporary + ppData.parttime;
                new Chart(plantillaRegularCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Permanent', 'Elected', 'Co-Terminous', 'Temporary', 'Part-Time'],
                        datasets: [{
                            data: [ppData.permanent, ppData.elected, ppData.coter, ppData.temporary, ppData.parttime],
                            backgroundColor: ['#10b981', '#7c3aed', '#3b82f6', '#f59e0b', '#f43f5e'],
                            borderColor: ['#fff', '#fff', '#fff', '#fff', '#fff'],
                            borderWidth: 3,
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: { display: false },
                            centerText: {
                                display: true,
                                text: ppTotal,
                                subText: 'Regular Positions',
                                fontSize: 26,
                                color: '#111827'
                            },
                            tooltip: {
                                backgroundColor: 'rgba(15, 23, 42, 0.9)',
                                titleFont: { size: 13, weight: 'bold' },
                                bodyFont: { size: 13 },
                                padding: 12,
                                cornerRadius: 8,
                                callbacks: {
                                    label: function (context) {
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const value = context.parsed;
                                        const pct = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return ` ${context.label}: ${value} (${pct}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Initialize Pie Chart - Casual Filled vs Unfilled
            const casualPieCtx = document.getElementById('casualPieChart')?.getContext('2d');
            if (casualPieCtx) {
                const casData = {
                    filled:  {{ $casualFilled ?? 0 }},
                    unfilled: {{ $casualVacant ?? 0 }},
                };
                new Chart(casualPieCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Filled', 'Unfilled'],
                        datasets: [{
                            data: [casData.filled, casData.unfilled],
                            backgroundColor: ['#10b981', '#f43f5e'],
                            borderColor: ['#fff', '#fff'],
                            borderWidth: 3,
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: { display: false },
                            centerText: {
                                display: true,
                                text: casData.filled + casData.unfilled,
                                subText: 'Casual Total',
                                fontSize: 22,
                                color: '#111827'
                            },
                            tooltip: {
                                backgroundColor: 'rgba(15, 23, 42, 0.9)',
                                titleFont: { size: 13, weight: 'bold' },
                                bodyFont: { size: 13 },
                                padding: 12,
                                cornerRadius: 8,
                                callbacks: {
                                    label: function (context) {
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const value = context.parsed;
                                        const pct = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return ` ${context.label}: ${value} (${pct}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Initialize Bar Chart - Age Demographics
            const ageCtx = document.getElementById('ageChart')?.getContext('2d');
            if (ageCtx) {
                const ageBrackets = @json($ageBrackets ?? []);

                const ageLabels = Object.keys(ageBrackets);
                const ageData = Object.values(ageBrackets);

                // Create a gradient for the age bars
                const ageGradient = ageCtx.createLinearGradient(0, 0, 0, 350);
                ageGradient.addColorStop(0, '#f43f5e'); // Rose
                ageGradient.addColorStop(1, '#be123c'); // Dark Rose

                new Chart(ageCtx, {
                    type: 'bar',
                    data: {
                        labels: ageLabels,
                        datasets: [{
                            label: 'Number of Employees',
                            data: ageData,
                            backgroundColor: ageGradient,
                            borderRadius: 6,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(15, 23, 42, 0.9)',
                                titleFont: { size: 14, weight: 'bold' },
                                bodyFont: { size: 14 },
                                padding: 12,
                                cornerRadius: 8,
                                displayColors: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: '#f1f5f9', drawBorder: false },
                                ticks: { font: { size: 12 }, precision: 0 }
                            },
                            x: {
                                grid: { display: false, drawBorder: false },
                                ticks: {
                                    font: { size: 12, weight: '500' }
                                }
                            }
                        }
                    }
                });
            }

            myBarChart = new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: unitNames,
                    datasets: [{
                        label: 'Total Employees',
                        data: unitCounts,
                        backgroundColor: gradient,
                        borderRadius: 8,
                        borderSkipped: false,
                        barThickness: 'flex',
                        maxBarThickness: 45
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    devicePixelRatio: Math.max(window.devicePixelRatio || 1, 2), // Force HD Rendering (minimum 2x)
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    onClick: function (event, elements) {
                        if (elements.length > 0) {
                            const index = elements[0].index;
                            const office = originalNames[index];
                            if (office && office !== 'No Data') {
                                window.location.href = "{{ route('all-data.index') }}?office=" + encodeURIComponent(office);
                            }
                        }
                    },
                    onHover: (event, chartElement) => {
                        event.native.target.style.cursor = chartElement[0] ? 'pointer' : 'default';
                    },
                    plugins: {
                        legend: {
                            display: false, // Cleaner without the legend since it's just one dataset
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.95)',
                            titleFont: { size: 14, weight: 'bold' },
                            bodyFont: { size: 13 },
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                title: function (tooltipItems) {
                                    // Show full unmodified office name on hover
                                    return originalNames[tooltipItems[0].dataIndex] || tooltipItems[0].label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                font: { size: 12, weight: '500' },
                                color: '#64748b'
                            },
                            grid: {
                                color: '#f1f5f9',
                                drawBorder: false,
                            },
                            border: { display: false }
                        },
                        x: {
                            ticks: {
                                font: { size: 11, weight: '600' },
                                color: '#475569',
                                maxRotation: 45,
                                minRotation: 45
                            },
                            grid: {
                                display: false,
                                drawBorder: false,
                            },
                            border: { display: false }
                        }
                    },
                    animation: {
                        y: {
                            duration: 1500,
                            easing: 'easeOutQuart'
                        }
                    }
                }
            });
        }

        function setTheme(theme) {
            document.body.classList.remove('emerald-night', 'theme-financial', 'theme-corona');
            if (theme !== 'default') {
                document.body.classList.add(theme);
            }
            localStorage.setItem('app-theme', theme);
            updateThemeToggleText(theme);

            const isDark = theme === 'emerald-night' || theme === 'theme-corona';
            window.dispatchEvent(new CustomEvent('theme-toggled', { detail: { isDark: isDark, themeName: theme } }));
        }

        function updateThemeToggleText(theme) {
            const toggles = document.querySelectorAll('.theme-toggle span');
            toggles.forEach(t => {
                if (theme === 'emerald-night') t.innerText = ' Emerald Night';
                else if (theme === 'theme-financial') t.innerText = ' Financial';
                else if (theme === 'theme-corona') t.innerText = ' Corona Dark';
                else t.innerText = ' Default Light';
            });
            const toggleIcons = document.querySelectorAll('.theme-toggle i');
            toggleIcons.forEach(i => {
                i.className = '';
                if (theme === 'emerald-night') i.className = 'bi bi-moon-stars';
                else if (theme === 'theme-financial') i.className = 'bi bi-graph-up-arrow';
                else if (theme === 'theme-corona') i.className = 'bi bi-moon';
                else i.className = 'bi bi-sun';
            });
        }

        // Listener for chart theme toggling
        window.addEventListener('theme-toggled', function (e) {
            const theme = e.detail.themeName;
            let textColor = '#666';
            let gridColor = 'rgba(0, 0, 0, 0.05)';
            let barChartBrandColor = '#0d6efd';
            let pieBorderColor = ['#fff', '#fff', '#fff'];

            if (theme === 'emerald-night') {
                textColor = '#f3f4f6';
                gridColor = 'rgba(255, 255, 255, 0.1)';
                barChartBrandColor = '#28a745';
                pieBorderColor = ['#0F2D21', '#0F2D21', '#0F2D21'];
            } else if (theme === 'theme-corona') {
                textColor = '#f3f4f6';
                gridColor = 'rgba(255, 255, 255, 0.1)';
                barChartBrandColor = '#dc3545';
                pieBorderColor = ['#191C24', '#191C24', '#191C24'];
            } else if (theme === 'theme-financial') {
                textColor = '#111827';
                gridColor = 'rgba(0, 0, 0, 0.05)';
                barChartBrandColor = '#0d6efd';
                pieBorderColor = ['#fff', '#fff', '#fff'];
            }

            if (myPieChart) {
                myPieChart.options.plugins.legend.labels.color = textColor;
                myPieChart.data.datasets[0].borderColor = pieBorderColor;
                myPieChart.update();
            }
            if (myBarChart) {
                myBarChart.data.datasets[0].backgroundColor = barChartBrandColor;
                myBarChart.options.plugins.legend.labels.color = textColor;
                myBarChart.options.scales.x.ticks.color = textColor;
                myBarChart.options.scales.y.ticks.color = textColor;
                myBarChart.options.scales.y.grid.color = gridColor;
                myBarChart.options.scales.x.grid.color = gridColor;
                myBarChart.update();
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            // Check legacy preference first
            let savedTheme = localStorage.getItem('app-theme');
            if (!savedTheme && localStorage.getItem('emerald-night') === '1') {
                savedTheme = 'emerald-night';
                localStorage.setItem('app-theme', 'emerald-night');
                localStorage.removeItem('emerald-night');
            }
            if (savedTheme) {
                setTheme(savedTheme);
            } else {
                setTheme('default');
            }
        });

        function markNotificationsAsRead(button) {
            const badge = button.querySelector('.notif-badge');
            if (badge) {
                badge.style.display = 'none';
                fetch('{{ route("activity-logs.mark-read") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }).catch(error => console.error('Error marking notifications as read:', error));
            }
        }
    </script>
</body>

</html>