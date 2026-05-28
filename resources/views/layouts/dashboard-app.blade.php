<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>HDMS- Human Resource Data Management System â€” {{ config('app.name', 'System') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">
        <!-- Bootstrap Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="stylesheet" href="{{ asset('css/theme-default.css') }}">
        <link rel="stylesheet" href="{{ asset('css/emerald-night.css') }}">
        <link rel="stylesheet" href="{{ asset('css/theme-financial.css') }}">
        <link rel="stylesheet" href="{{ asset('css/theme-corona.css') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            *, *::before, *::after { box-sizing: border-box; }
            body { font-family: 'Inter', sans-serif; margin: 0; transition: background-color .3s, color .3s; }

            /* â”€â”€ Dark Mode Toggle Button â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
            .dark-mode-toggle {
                width: 38px; height: 38px; border-radius: 10px;
                border: 1px solid #e2e8f0;
                background: #f1f5f9;
                color: #475569;
                display: flex; align-items: center; justify-content: center;
                font-size: 16px; cursor: pointer;
                transition: all .2s ease;
                flex-shrink: 0;
            }
            .dark-mode-toggle:hover { background: #e2e8f0; color: #0f172a; transform: scale(1.08); }
            .dark-mode-toggle.dark-active { background: #1e293b; border-color: #334155; color: #f8fafc; }
            .dark-mode-toggle.dark-active:hover { background: #334155; }

            /* â”€â”€ Dark Mode Overrides â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
            body.dark-mode { background: #0f172a !important; color: #f8fafc !important; }

            /* Sidebar */
            body.dark-mode .sidebar {
                background: #1e293b !important;
                border-right-color: #334155 !important;
                box-shadow: 4px 0 20px rgba(0,0,0,.3) !important;
            }
            body.dark-mode .sidebar-logo { border-bottom-color: #334155 !important; }
            body.dark-mode .sidebar-logo-text { color: #f1f5f9 !important; }
            body.dark-mode .sidebar-section-label { color: #64748b !important; }
            body.dark-mode .sidebar-link { color: #94a3b8 !important; }
            body.dark-mode .sidebar-link:hover { background: #334155 !important; color: #f1f5f9 !important; }
            body.dark-mode .sidebar-link.active { background: #0c4a6e !important; color: #38bdf8 !important; }
            body.dark-mode .sidebar-footer { border-top-color: #334155 !important; }
            body.dark-mode .sidebar-user { background: #0f172a !important; border-color: #334155 !important; }
            body.dark-mode .sidebar-user-name { color: #f1f5f9 !important; }
            body.dark-mode .sidebar-user-role { color: #64748b !important; }

            /* Topbar */
            body.dark-mode .topbar {
                background: #1e293b !important;
                border-bottom-color: #334155 !important;
                box-shadow: 0 1px 6px rgba(0,0,0,.2) !important;
            }
            body.dark-mode .topbar-page-title { color: #f1f5f9 !important; }
            body.dark-mode .topbar-clock { color: #64748b !important; }
            body.dark-mode .dark-mode-toggle { background: #334155 !important; border-color: #475569 !important; color: #fbbf24 !important; }
            body.dark-mode .dark-mode-toggle:hover { background: #475569 !important; }

            /* Theme dropdown */
            body.dark-mode .theme-toggle-header {
                background: #334155 !important; border-color: #475569 !important; color: #94a3b8 !important;
            }
            body.dark-mode .dropdown-menu {
                background: #1e293b !important; border-color: #334155 !important;
            }
            body.dark-mode .dropdown-menu li a { color: #94a3b8 !important; }
            body.dark-mode .dropdown-menu li a:hover { background: #334155 !important; color: #f1f5f9 !important; }

            /* Page / Card backgrounds */
            body.dark-mode main { background: #0f172a !important; }
            body.dark-mode .bg-white,
            body.dark-mode [class*="bg-white"] { background: #1e293b !important; }
            body.dark-mode .bg-gray-50 { background: #0f172a !important; }
            body.dark-mode .bg-gray-100 { background: #1e293b !important; }

            /* Borders */
            body.dark-mode .border-gray-200 { border-color: #334155 !important; }
            body.dark-mode .border-gray-100 { border-color: #334155 !important; }
            body.dark-mode .border-gray-300 { border-color: #475569 !important; }

            /* Text */
            body.dark-mode .text-gray-800 { color: #f1f5f9 !important; }
            body.dark-mode .text-gray-700 { color: #cbd5e1 !important; }
            body.dark-mode .text-gray-600 { color: #94a3b8 !important; }
            body.dark-mode .text-gray-500 { color: #64748b !important; }
            body.dark-mode .text-gray-400 { color: #475569 !important; }
            body.dark-mode h1, body.dark-mode h2, body.dark-mode h3 { color: #f1f5f9 !important; }

            /* Forms */
            body.dark-mode input, body.dark-mode select, body.dark-mode textarea {
                background: #0f172a !important;
                border-color: #475569 !important;
                color: #f1f5f9 !important;
            }
            body.dark-mode input::placeholder, body.dark-mode textarea::placeholder { color: #475569 !important; }
            body.dark-mode input:focus, body.dark-mode select:focus, body.dark-mode textarea:focus {
                border-color: #38bdf8 !important;
                box-shadow: 0 0 0 3px rgba(56,189,248,.15) !important;
            }
            body.dark-mode option { background: #1e293b !important; color: #f1f5f9 !important; }

            /* Tables */
            body.dark-mode table { color: #cbd5e1 !important; }
            body.dark-mode thead th {
                background: #1e293b !important;
                color: #94a3b8 !important;
                border-color: #334155 !important;
            }
            body.dark-mode tbody tr { border-color: #334155 !important; }
            body.dark-mode tbody tr:hover { background: #1e293b !important; }
            body.dark-mode td { color: #cbd5e1 !important; border-color: #334155 !important; }

            /* Badges */
            body.dark-mode .bg-blue-50 { background: #0c4a6e !important; }
            body.dark-mode .bg-green-50 { background: #052e16 !important; }
            body.dark-mode .bg-red-50 { background: #450a0a !important; }
            body.dark-mode .bg-yellow-50 { background: #422006 !important; }
            body.dark-mode .bg-orange-50 { background: #431407 !important; }
            body.dark-mode .bg-purple-50 { background: #2e1065 !important; }
            body.dark-mode .bg-indigo-50 { background: #1e1b4b !important; }

            /* Modals */
            body.dark-mode .logout-modal-card { background: #1e293b !important; }
            body.dark-mode .logout-modal-top {
                background: linear-gradient(135deg,#2d1515,#3b1212) !important;
                border-bottom-color: #4b2020 !important;
            }
            body.dark-mode .logout-modal-title { color: #f1f5f9 !important; }
            body.dark-mode .logout-modal-subtitle { color: #94a3b8 !important; }
            body.dark-mode .logout-modal-info { background: #1c1a07 !important; border-color: #854d0e !important; color: #fef08a !important; }
            body.dark-mode .logout-btn-cancel {
                background: #0f172a !important; border-color: #334155 !important; color: #94a3b8 !important;
            }

            /* Wizard steps (used in create form) */
            body.dark-mode .wizard-step-card {
                background: #1e293b !important;
                border-color: #334155 !important;
            }
            body.dark-mode .wizard-progress-bar { background: #334155 !important; }
            body.dark-mode .wizard-step-label { color: #64748b !important; }
            body.dark-mode .wizard-step-label.active { color: #38bdf8 !important; }
            body.dark-mode .wizard-nav-btn {
                background: #334155 !important;
                border-color: #475569 !important;
                color: #94a3b8 !important;
            }
            body.dark-mode .wizard-nav-btn:hover { background: #475569 !important; color: #f1f5f9 !important; }

            /* â”€â”€ Sidebar â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
            .sidebar {
                width: 240px; flex-shrink: 0;
                background: #ffffff;
                display: flex; flex-direction: column;
                min-height: 100vh; position: sticky; top: 0;
                box-shadow: 4px 0 20px rgba(0,0,0,.05);
                border-right: 1px solid #e5e7eb;
                z-index: 30;
            }
            .sidebar-logo {
                display: flex; align-items: center; gap: 12px;
                padding: 18px 20px 16px;
                border-bottom: 1px solid #e5e7eb;
            }
            .sidebar-logo-img {
                width: 54px; height: 54px; object-fit: contain;
                border-radius: 12px; flex-shrink: 0;
                background: #fff;
                box-shadow: 0 3px 10px rgba(0,0,0,.12);
                padding: 3px;
                transition: transform .2s ease;
            }
            .sidebar-logo-img:hover { transform: scale(1.06); }
            .sidebar-logo-text {
                font-family: 'Inter', sans-serif;
                font-size: 17px; font-weight: 700;
                color: #1e293b; letter-spacing: -.2px;
                line-height: 1.25;
            }
            .sidebar-logo-sub {
                font-family: 'Inter', sans-serif;
                font-size: 11px; font-weight: 600;
                color: #2563eb; letter-spacing: 1px;
                text-transform: uppercase; margin-top: 3px;
            }

            .sidebar-section-label {
                font-size: 9px; font-weight: 700; letter-spacing: 1.2px;
                text-transform: uppercase; color: #94a3b8;
                padding: 18px 20px 6px;
            }

            .sidebar nav { flex: 1; padding: 8px 12px; overflow-y: auto; }
            .sidebar-link {
                display: flex; align-items: center; gap: 11px;
                padding: 10px 14px; border-radius: 10px;
                color: #475569; font-size: 13px; font-weight: 500;
                text-decoration: none; transition: all .15s; margin-bottom: 2px;
            }
            .sidebar-link i { font-size: 16px; flex-shrink: 0; }
            .sidebar-link:hover { background: #f1f5f9; color: #0f172a; }
            .sidebar-link.active {
                background: #e0f2fe;
                color: #0284c7; font-weight: 700;
                box-shadow: none;
            }
            .sidebar-link .link-badge {
                margin-left: auto; background: rgba(239,68,68,.9);
                color: #fff; font-size: 10px; font-weight: 800;
                padding: 1px 7px; border-radius: 99px; min-width: 20px; text-align: center;
            }

            /* Sidebar footer */
            .sidebar-footer {
                padding: 14px 16px;
                border-top: 1px solid #e5e7eb;
            }
            .sidebar-user {
                display: flex; align-items: center; gap: 10px;
                padding: 10px 12px; border-radius: 10px;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
            }
            .sidebar-avatar {
                width: 34px; height: 34px; border-radius: 50%;
                background: linear-gradient(135deg,#3b82f6,#2563eb);
                display: flex; align-items: center; justify-content: center;
                font-size: 14px; font-weight: 800; color: #fff; flex-shrink: 0;
            }
            .sidebar-user-name  { font-size: 12px; font-weight: 700; color: #0f172a; }
            .sidebar-user-role  { font-size: 10px; color: #64748b; margin-top: 1px; }
            .sidebar-logout-btn {
                margin-left: auto; flex-shrink: 0;
                background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.2);
                color: #ef4444; width: 30px; height: 30px; border-radius: 8px;
                display: flex; align-items: center; justify-content: center;
                font-size: 14px; cursor: pointer; transition: all .15s;
            }
            .sidebar-logout-btn:hover { background: rgba(239,68,68,.2); color: #dc2626; transform: scale(1.08); }

            /* â”€â”€ Topbar â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
            .topbar {
                background: #fff; border-bottom: 1px solid #e5e7eb;
                padding: 0 24px; height: 60px;
                display: flex; align-items: center; justify-content: space-between;
                gap: 16px; position: sticky; top: 0; z-index: 20;
                box-shadow: 0 1px 6px rgba(0,0,0,.04);
            }
            .topbar-left { display: flex; align-items: center; gap: 12px; flex: 1; }
            .topbar-page-title { font-size: 15px; font-weight: 700; color: #0f172a; }
            .topbar-right { display: flex; align-items: center; gap: 14px; }

            .topbar-clock { font-size: 12px; font-weight: 600; color: #64748b; font-variant-numeric: tabular-nums; }

            .topbar-avatar {
                width: 34px; height: 34px; border-radius: 50%;
                background: linear-gradient(135deg,#3b82f6,#2563eb);
                display: flex; align-items: center; justify-content: center;
                font-size: 14px; font-weight: 800; color: #fff;
                cursor: pointer;
            }
            .topbar-logout-btn {
                display: inline-flex; align-items: center; gap: 6px;
                background: #fef2f2; border: 1px solid #fecaca;
                color: #dc2626; padding: 7px 14px; border-radius: 9px;
                font-size: 12px; font-weight: 700; cursor: pointer;
                transition: all .15s; border: none;
            }
            .topbar-logout-btn:hover { background: #fee2e2; transform: scale(1.02); }

            /* â”€â”€ Premium Logout Modal â”€â”€ */
            .logout-overlay {
                display: none; position: fixed; inset: 0;
                background: rgba(15,23,42,0.55);
                backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);
                z-index: 9999; justify-content: center; align-items: center;
                animation: fadeInBg 0.2s ease;
            }
            .logout-overlay.active { display: flex; }
            @keyframes fadeInBg { from { opacity:0; } to { opacity:1; } }
            .logout-modal-card {
                background:#fff; border-radius:20px; padding:0;
                max-width:420px; width:calc(100% - 32px);
                box-shadow: 0 25px 60px rgba(0,0,0,.18),0 8px 20px rgba(0,0,0,.08);
                position:relative; overflow:hidden;
                animation: slideUp 0.3s cubic-bezier(0.34,1.56,0.64,1);
            }
            @keyframes slideUp {
                from { opacity:0; transform:translateY(30px) scale(0.95); }
                to   { opacity:1; transform:translateY(0)   scale(1);    }
            }
            .logout-modal-top {
                background: linear-gradient(135deg,#fff1f2,#ffe4e6);
                padding: 36px 32px 24px; text-align:center;
                border-bottom: 1px solid #fecdd3;
            }
            .logout-modal-icon-wrap {
                width:72px; height:72px;
                background: linear-gradient(135deg,#ef4444,#dc2626);
                border-radius:50%; display:flex; align-items:center; justify-content:center;
                margin:0 auto 18px;
                box-shadow: 0 8px 24px rgba(220,38,38,.3);
                animation: pulse-red 2s infinite;
            }
            @keyframes pulse-red {
                0%,100% { box-shadow:0 8px 24px rgba(220,38,38,.30); }
                50%      { box-shadow:0 8px 32px rgba(220,38,38,.55); }
            }
            .logout-modal-icon-wrap i { font-size:30px; color:#fff; }
            .logout-modal-title  { font-size:22px; font-weight:800; color:#0f172a; margin:0 0 6px; letter-spacing:-.3px; }
            .logout-modal-subtitle { font-size:13px; color:#64748b; margin:0; line-height:1.6; }
            .logout-modal-body { padding:22px 28px 28px; }
            .logout-modal-info {
                display:flex; align-items:flex-start; gap:10px;
                background:#fef9c3; border:1px solid #fde68a; border-radius:10px;
                padding:12px 14px; margin-bottom:22px;
                font-size:12.5px; color:#854d0e; line-height:1.5;
            }
            .logout-modal-info i { font-size:15px; color:#ca8a04; flex-shrink:0; margin-top:1px; }
            .logout-modal-actions { display:flex; gap:10px; }
            .logout-btn-cancel {
                flex:1; padding:12px 16px;
                border:1.5px solid #e2e8f0; border-radius:12px;
                font-size:13.5px; font-weight:600; color:#475569; background:#f8fafc;
                cursor:pointer; transition:all .2s;
                display:flex; align-items:center; justify-content:center; gap:6px;
            }
            .logout-btn-cancel:hover { background:#f1f5f9; border-color:#cbd5e1; color:#0f172a; }
            .logout-btn-confirm {
                flex:1; padding:12px 16px;
                border:none; border-radius:12px;
                font-size:13.5px; font-weight:700; color:#fff;
                background:linear-gradient(135deg,#ef4444,#dc2626);
                cursor:pointer; transition:all .2s;
                display:flex; align-items:center; justify-content:center; gap:6px;
                box-shadow:0 4px 14px rgba(220,38,38,.3);
            }
            .logout-btn-confirm:hover { background:linear-gradient(135deg,#dc2626,#b91c1c); box-shadow:0 6px 20px rgba(220,38,38,.45); transform:translateY(-1px); }
            .logout-btn-confirm:active { transform:translateY(0); }
            .logout-modal-close {
                position:absolute; top:12px; right:14px;
                width:28px; height:28px;
                background:rgba(0,0,0,.06); border:none; border-radius:50%;
                color:#64748b; font-size:14px; cursor:pointer;
                display:flex; align-items:center; justify-content:center; transition:all .2s;
            }
            .logout-modal-close:hover { background:rgba(0,0,0,.12); color:#0f172a; }

            /* â”€â”€ Page Loading Overlay â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
            #page-loading-overlay {
                display: none;
                position: fixed; inset: 0; z-index: 99999;
                background: rgba(255,255,255,0.75);
                backdrop-filter: blur(3px);
                -webkit-backdrop-filter: blur(3px);
                justify-content: center; align-items: center;
                flex-direction: column; gap: 18px;
                animation: fadeInBg 0.15s ease;
            }
            body.dark-mode #page-loading-overlay { background: rgba(15,23,42,0.75); }
            #page-loading-overlay.active { display: flex; }
            .loading-spinner {
                width: 52px; height: 52px;
                border: 5px solid #e2e8f0;
                border-top-color: #3b82f6;
                border-radius: 50%;
                animation: spin 0.75s linear infinite;
            }
            body.dark-mode .loading-spinner { border-color: #334155; border-top-color: #38bdf8; }
            @keyframes spin { to { transform: rotate(360deg); } }
            .loading-label {
                font-size: 13px; font-weight: 600;
                color: #475569; letter-spacing: .3px;
            }
            body.dark-mode .loading-label { color: #94a3b8; }
        </style>
    </head>
    <body class="bg-gray-50 antialiased">
        <div style="display:flex;min-height:100vh;">

            {{-- â•â•â•â•â•â•â•â•â•â• SIDEBAR â•â•â•â•â•â•â•â•â•â• --}}
            <aside class="sidebar">
                {{-- Logo --}}
                <div class="sidebar-logo">
                    <img src="{{ asset('img/phrmologo.png') }}" alt="PHRMO" class="sidebar-logo-img">
                    <div>
                        <div class="sidebar-logo-text">Human Resource Data Management System</div>
                        <div class="sidebar-logo-sub">PHRMO Portal</div>
                    </div>
                </div>

                <nav>
                    <div class="sidebar-section-label">Main</div>

                    <a href="{{ route('dashboard') }}"
                       class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-house-door-fill"></i>
                        <span>Dashboard</span>
                    </a>

                    @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                    <a href="{{ route('all-data.index') }}"
                       class="sidebar-link {{ request()->routeIs('all-data.*') ? 'active' : '' }}">
                        <i class="bi bi-table"></i>
                        <span>All Data</span>
                    </a>
                    @endif

                    <a href="{{ route('plantilla.index') }}"
                       class="sidebar-link {{ request()->routeIs('plantilla.*') ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i>
                        <span>Inventory of Personnel</span>
                    </a>

                    @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                    <a href="{{ route('job-orders.index') }}"
                       class="sidebar-link {{ request()->routeIs('job-orders.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-person-fill"></i>
                        <span>Job Order</span>
                    </a>
                    @endif

                    @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                    <a href="{{ route('casual.index') }}"
                       class="sidebar-link {{ request()->routeIs('casual.*') ? 'active' : '' }}">
                        <i class="bi bi-person-lines-fill"></i>
                        <span>Casual</span>
                    </a>
                    @endif

                    @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                    @if(Route::has('permanent.index'))
                    <a href="{{ route('permanent.index') }}"
                       class="sidebar-link {{ request()->routeIs('permanent.*') ? 'active' : '' }}">
                        <i class="bi bi-person-badge-fill"></i>
                        <span>Permanent</span>
                    </a>
                    @endif
                    @endif

                    @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                    <a href="{{ route('retirement.index') }}"
                       class="sidebar-link {{ request()->routeIs('retirement.*') ? 'active' : '' }}">
                        <i class="bi bi-clock-history"></i>
                        <span>Retirement</span>
                        @php $retCount = \App\Models\PlantillaRecord::retirementDue()->count(); @endphp
                        @if($retCount > 0)
                            <span class="link-badge">{{ $retCount }}</span>
                        @endif
                    </a>
                    @endif

                    <a href="{{ route('step-increment.hub') }}"
                       class="sidebar-link {{ request()->routeIs('step-increment.*') ? 'active' : '' }}">
                        <i class="bi bi-building-fill-gear"></i>
                        <span>Plantilla of Personnel</span>
                    </a>

                    @if(Route::has('appointments.index'))
                    <a href="{{ route('appointments.index') }}"
                       class="sidebar-link {{ request()->routeIs('appointments.*') ? 'active' : '' }}">
                        <i class="bi bi-calendar2-check"></i>
                        <span>Appointments</span>
                    </a>
                    @endif

                    @if(Route::has('organizational-units.index'))
                    <a href="{{ route('organizational-units.index') }}"
                       class="sidebar-link {{ request()->routeIs('organizational-units.*') ? 'active' : '' }}">
                        <i class="bi bi-diagram-3"></i>
                        <span>Org. Units</span>
                    </a>
                    @endif

                    @if(Route::has('positions.index'))
                    <a href="{{ route('positions.index') }}"
                       class="sidebar-link {{ request()->routeIs('positions.*') ? 'active' : '' }}">
                        <i class="bi bi-briefcase"></i>
                        <span>Positions</span>
                    </a>
                    @endif

                    @if(Route::has('imports.index'))
                    <a href="{{ route('imports.index') }}"
                       class="sidebar-link {{ request()->routeIs('imports.*') ? 'active' : '' }}">
                        <i class="bi bi-cloud-upload"></i>
                        <span>Import Data</span>
                    </a>
                    @endif

                    @if(Route::has('reports.index'))
                    <a href="{{ route('reports.index') }}"
                       class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                        <i class="bi bi-bar-chart-line"></i>
                        <span>Reports</span>
                    </a>
                    @endif

                    <div class="sidebar-section-label">Account</div>

                    <a href="{{ route('profile.edit') }}"
                       class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                        <i class="bi bi-person-circle"></i>
                        <span>My Profile</span>
                    </a>

                    @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                    @if(Route::has('users.index'))
                    <a href="{{ route('users.index') }}"
                       class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i>
                        <span>User Management</span>
                    </a>
                    @endif

                    {{-- Archives / Recycle Bin --}}
                    <a href="{{ route('archives.index') }}"
                       class="sidebar-link {{ request()->routeIs('archives.*') ? 'active' : '' }}"
                       style="{{ request()->routeIs('archives.*') ? '' : 'color:#7c3aed;' }}">
                        <i class="bi bi-archive-fill"></i>
                        <span>Archives</span>
                        @php $arcCount = \App\Models\PlantillaRecord::onlyTrashed()->count() + \App\Models\User::onlyTrashed()->count(); @endphp
                        @if($arcCount > 0)
                            <span class="link-badge" style="background:rgba(124,58,237,.85);">{{ $arcCount }}</span>
                        @endif
                    </a>

                    @endif
                </nav>

                {{-- Sidebar footer with user + logout button --}}
                <div class="sidebar-footer">
                    <div class="sidebar-user">
                        <div class="sidebar-avatar">
                            @if(auth()->user()->profile_picture)
                                <img src="{{ Storage::url(auth()->user()->profile_picture) }}" alt="Avatar" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                            @else
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            @endif
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div class="sidebar-user-name" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ auth()->user()->name }}</div>
                            <div class="sidebar-user-role">{{ auth()->user()->role_label }}</div>
                        </div>
                        <button class="sidebar-logout-btn" onclick="openLogoutModal()" title="Log out">
                            <i class="bi bi-box-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </aside>

            {{-- â•â•â•â•â•â•â•â•â•â• MAIN â•â•â•â•â•â•â•â•â•â• --}}
            <div style="flex:1;display:flex;flex-direction:column;min-width:0;">

                {{-- Topbar --}}
                <header class="topbar">
                    <div class="topbar-left">
                        <div class="topbar-page-title">
                            <i class="bi bi-house-door me-1" style="color:#94a3b8;font-size:14px;"></i>
                            {{ config('app.name', 'HDMS- Human Resource Data Management System') }}
                        </div>
                    </div>
                    <div class="topbar-right">
                        <!-- Dark Mode Toggle -->
                        <button id="dark-mode-toggle-btn" class="dark-mode-toggle" onclick="toggleDarkMode()" title="Toggle Dark Mode" aria-label="Toggle Dark Mode">
                            <i class="bi bi-moon-fill" id="dark-mode-icon"></i>
                        </button>

                        <!-- Theme Settings Toggle -->
                        <div class="dropdown" style="position: relative;">
                            <button class="theme-toggle theme-toggle-header" type="button" style="background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 8px; font-weight: 600; font-size: 13px; color: #475569; display: flex; align-items: center; gap: 6px; cursor: pointer; padding: 6px 12px;">
                                <i class="bi bi-palette"></i> <span>Theme</span>
                            </button>
                            <ul class="dropdown-menu shadow" style="position: absolute; right: 0; background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 0; min-width: 180px; z-index: 1000; list-style: none; margin-top: 5px; display: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                                <li style="padding: 4px 16px; font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">Appearance</li>
                                <li><a class="dropdown-item" href="#" onclick="setTheme('default'); return false;" style="display: flex; align-items: center; padding: 8px 16px; color: #475569; text-decoration: none; font-size: 13px; cursor: pointer;" onmouseover="this.style.backgroundColor='#f1f5f9'" onmouseout="this.style.backgroundColor='transparent'"><i class="bi bi-sun" style="margin-right: 8px;"></i> Default Light</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setTheme('emerald-night'); return false;" style="display: flex; align-items: center; padding: 8px 16px; color: #475569; text-decoration: none; font-size: 13px; cursor: pointer;" onmouseover="this.style.backgroundColor='#f1f5f9'" onmouseout="this.style.backgroundColor='transparent'"><i class="bi bi-moon-stars" style="margin-right: 8px;"></i> Emerald Night</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setTheme('theme-financial'); return false;" style="display: flex; align-items: center; padding: 8px 16px; color: #475569; text-decoration: none; font-size: 13px; cursor: pointer;" onmouseover="this.style.backgroundColor='#f1f5f9'" onmouseout="this.style.backgroundColor='transparent'"><i class="bi bi-graph-up-arrow" style="margin-right: 8px;"></i> Financial (Teal)</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setTheme('theme-corona'); return false;" style="display: flex; align-items: center; padding: 8px 16px; color: #475569; text-decoration: none; font-size: 13px; cursor: pointer;" onmouseover="this.style.backgroundColor='#f1f5f9'" onmouseout="this.style.backgroundColor='transparent'"><i class="bi bi-moon" style="margin-right: 8px;"></i> Corona (Dark)</a></li>
                            </ul>
                        </div>

                        <div class="topbar-clock" id="topbar-clock"></div>
                        <div class="topbar-avatar" title="{{ auth()->user()->name }}">
                            @if(auth()->user()->profile_picture)
                                <img src="{{ Storage::url(auth()->user()->profile_picture) }}" alt="Avatar" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                            @else
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            @endif
                        </div>
                    </div>
                </header>

                {{-- Page content --}}
                <main style="flex:1;padding:24px;">
                    {{ $slot }}
                </main>
            </div>
        </div>

        {{-- Hidden Logout Form --}}
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>

        {{-- Page Loading Overlay --}}
        <div id="page-loading-overlay">
            <div class="loading-spinner"></div>
            <div class="loading-label">Loading, please waitâ€¦</div>
        </div>


        {{-- Premium Logout Modal --}}
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
                    <p class="logout-modal-subtitle">You're about to leave the HDMS- Human Resource Data Management System portal.</p>
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
                        <button type="button" class="logout-btn-confirm" onclick="document.getElementById('logout-form').submit()">
                            <i class="bi bi-box-arrow-right"></i> Yes, Sign Out
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
        // Premium Logout Modal
        function openLogoutModal() {
            document.getElementById('logoutModal').classList.add('active');
        }
        function closeLogoutModal() {
            document.getElementById('logoutModal').classList.remove('active');
        }
        document.addEventListener('DOMContentLoaded', function() {
            var overlay = document.getElementById('logoutModal');
            if (overlay) {
                overlay.addEventListener('click', function(e) {
                    if (e.target === overlay) closeLogoutModal();
                });
            }
        });

        // Live clock in topbar
        (function updateClock() {
            var el = document.getElementById('topbar-clock');
            if (el) {
                var now = new Date();
                el.textContent = now.toLocaleTimeString('en-PH', { hour:'2-digit', minute:'2-digit', second:'2-digit' })
                               + '  Â·  '
                               + now.toLocaleDateString('en-PH', { weekday:'short', month:'short', day:'numeric', year:'numeric' });
            }
            setTimeout(updateClock, 1000);
        })();

        // Theme Dropdown Toggle
        document.querySelector('.theme-toggle').addEventListener('click', function(e) {
            e.stopPropagation();
            const menu = this.nextElementSibling;
            if (menu.style.display === 'block') {
                menu.style.display = 'none';
            } else {
                // close other dropdowns first if multiple exist
                document.querySelectorAll('.dropdown-menu').forEach(m => m.style.display = 'none');
                menu.style.display = 'block';
            }
        });
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-menu').forEach(m => m.style.display = 'none');
            }
        });

        // Theme Management
        function setTheme(theme) {
            document.body.classList.remove('emerald-night', 'theme-financial', 'theme-corona');
            if (theme !== 'default') {
                document.body.classList.add(theme);
            }
            localStorage.setItem('app-theme', theme);
            updateThemeToggleText(theme);
            document.querySelectorAll('.dropdown-menu').forEach(m => m.style.display = 'none');
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

        // â”€â”€ Dark Mode Toggle â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        function toggleDarkMode() {
            const isDark = document.body.classList.toggle('dark-mode');
            localStorage.setItem('app-dark-mode', isDark ? '1' : '0');
            updateDarkModeIcon(isDark);
        }

        function updateDarkModeIcon(isDark) {
            const btn = document.getElementById('dark-mode-toggle-btn');
            const icon = document.getElementById('dark-mode-icon');
            if (!btn || !icon) return;
            if (isDark) {
                icon.className = 'bi bi-sun-fill';
                btn.classList.add('dark-active');
                btn.title = 'Switch to Light Mode';
            } else {
                icon.className = 'bi bi-moon-fill';
                btn.classList.remove('dark-active');
                btn.title = 'Switch to Dark Mode';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Restore dark mode preference (runs before theme)
            const darkPref = localStorage.getItem('app-dark-mode');
            if (darkPref === '1') {
                document.body.classList.add('dark-mode');
                updateDarkModeIcon(true);
            } else {
                updateDarkModeIcon(false);
            }

            // Restore theme
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

            // â”€â”€ Page Loading Overlay â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
            const overlay = document.getElementById('page-loading-overlay');

            // Show loader on sidebar link clicks (skip anchors that open modals / external)
            document.querySelectorAll('a.sidebar-link').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    var href = this.getAttribute('href');
                    if (!href || href === '#' || href.startsWith('javascript')) return;
                    overlay.classList.add('active');
                });
            });

            // Show loader on any regular form submit (except logout / modals)
            document.querySelectorAll('form:not(#logout-form):not(#sidebar-generate-codes-form)').forEach(function(form) {
                form.addEventListener('submit', function() {
                    overlay.classList.add('active');
                });
            });

            // â”€â”€ Tauri Interceptor for Downloads and PDF links â”€â”€
            if (window.__TAURI__ && window.__TAURI__.core) {
                console.log("[Tauri] Interceptor Active! IPC is available.");
                
                // 1. Intercept Link Clicks
                document.addEventListener('click', function(e) {
                    var a = e.target.closest('a');
                    if (a && a.href && (a.target === '_blank' || a.hasAttribute('download'))) {
                        e.preventDefault();
                        console.log("[Tauri] Intercepted link click: " + a.href);
                        // Call Tauri opener plugin to handle it via OS
                        window.__TAURI__.core.invoke('plugin:opener|open_url', { url: a.href })
                            .catch(function(err1) {
                                window.__TAURI__.core.invoke('plugin:opener|open', { path: a.href })
                                    .catch(function(err2) {
                                        console.error("[Tauri] Opener failed:", err1, err2);
                                        // fallback to normal behavior (which might be blocked)
                                        window.location.assign(a.href);
                                    });
                            });
                    }
                });

                // 2. Intercept window.open
                var originalWindowOpen = window.open;
                window.open = function(url, target, features) {
                    if (target === '_blank' || target == null || target === '') {
                        console.log("[Tauri] Intercepted window.open: " + url);
                        // Ensure URL is absolute
                        var fullUrl = new URL(url, window.location.href).href;
                        window.__TAURI__.core.invoke('plugin:opener|open_url', { url: fullUrl })
                            .catch(function() {
                                window.__TAURI__.core.invoke('plugin:opener|open', { path: fullUrl })
                                    .catch(function() {
                                        originalWindowOpen(url, target, features);
                                    });
                            });
                        return null;
                    }
                    return originalWindowOpen(url, target, features);
                };
            } else {
                console.warn("[Tauri] Interceptor inactive. IPC is not available.");
            }

            // Hide overlay when page becomes visible again (e.g. back button)
            window.addEventListener('pageshow', function() {
                overlay.classList.remove('active');
            });
        });

        // Sidebar Generate Codes Modal
        // (moved to Archives page â€” no longer needed here)
        </script>
    </body>

</html>
