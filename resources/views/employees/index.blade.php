<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #e9e9ff;
        }

        .sidebar {
            width: 280px;
            background: linear-gradient(to bottom, #2D5A4C, #1a3a34);
            position: fixed;
            height: 100vh;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
        }

        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .sidebar-logo {
            width: 48px;
            height: 48px;
            object-fit: contain;
            border-radius: 10px;
            flex-shrink: 0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            background: #ffffff;
            padding: 2px;
            transition: transform 0.2s ease;
        }

        .sidebar-logo:hover {
            transform: scale(1.05);
        }

        .sidebar-title-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .sidebar-title-main {
            color: #2563eb;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        .sidebar-title-sub {
            color: #64748b;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #e5e7eb;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 15px;
            font-weight: 500;
        }

        .nav-link:hover {
            color: #ffffff;
            background-color: #4A8B7D;
        }

        .nav-link.active {
            background-color: #0d6efd;
            color: #ffffff;
            font-weight: 600;
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
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Header -->
                <div class="sidebar-header">
            <img src="{{ asset('img/logo.png') }}" alt="CSC Logo" class="sidebar-logo" onerror="this.style.display='none'">
            <div class="sidebar-title-container">
                <span class="sidebar-title-main">CSC</span>
                <span class="sidebar-title-sub">Plantilla System</span>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="sidebar-nav" style="flex: 1; padding: 16px 0; overflow-y: auto;">
            <div style="margin: 8px 12px;">
                <a href="{{ route('dashboard') }}" class="nav-link">
                    <i class="bi bi-house-door" style="font-size: 20px;"></i>
                    <span>Dashboard</span>
                </a>
            </div>
                        <div style="margin: 8px 12px;">
                <a href="{{ route('dashboard') }}" class="nav-link">
                    <i class="bi bi-house-door" style="font-size: 20px;"></i>
                    <span>Dashboard</span>
                </a>
            </div>

            @if(auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin()))
            <div style="margin: 8px 12px;">
                <a href="{{ route('plantilla.index') }}" class="nav-link {{ request()->routeIs('plantilla.*') ? 'active' : '' }}">
                    <i class="bi bi-person-badge" style="font-size: 20px;"></i>
                    <span>Inventory of Personnel</span>
                </a>
            </div>
            @endif

            @if(auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->isSalaryAdmin()))
            <div style="margin: 8px 12px;">
                <a href="{{ route('step-increment.index') }}" class="nav-link {{ request()->routeIs('step-increment.*') ? 'active' : '' }}">
                    <i class="bi bi-graph-up-arrow" style="font-size: 20px;"></i>
                    <span>Step Increment</span>
                </a>
            </div>
            @endif

            @if(auth()->check() && auth()->user()->isSuperAdmin())
            <div style="margin: 8px 12px;">
                <a href="{{ route('imports.index') }}" class="nav-link {{ request()->routeIs('imports.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-spreadsheet" style="font-size: 20px;"></i>
                    <span>Import Data</span>
                </a>
            </div>
            @endif

            <div style="margin: 8px 12px;">
                <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <i class="bi bi-person-circle" style="font-size: 20px;"></i>
                    <span>Profile</span>
                </a>
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1>Employees</h1>
            <p>Manage employee records</p>
        </div>

        <!-- Alerts -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Filter Section -->
        <div style="background: white; border-radius: 12px; padding: 20px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);">
            <div style="font-size: 14px; font-weight: 700; color: #1f2937; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-funnel"></i> Filter & Search
            </div>
            <form method="GET" action="{{ route('employees.index') }}">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px;">
                    <input 
                        type="text" 
                        name="search" 
                        style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;" 
                        placeholder="Search by name, employee number, or email..."
                        value="{{ request('search') }}"
                    >
                    
                    <select name="status" style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                        <option value="">All Statuses</option>
                        <option value="Active" @selected(request('status') === 'Active')>Active</option>
                        <option value="Inactive" @selected(request('status') === 'Inactive')>Inactive</option>
                        <option value="Retired" @selected(request('status') === 'Retired')>Retired</option>
                        <option value="Resigned" @selected(request('status') === 'Resigned')>Resigned</option>
                    </select>

                    <select name="gender" style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                        <option value="">All Genders</option>
                        <option value="Male" @selected(request('gender') === 'Male')>Male</option>
                        <option value="Female" @selected(request('gender') === 'Female')>Female</option>
                    </select>

                    <select name="civil_status" style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                        <option value="">All Civil Status</option>
                        <option value="Single" @selected(request('civil_status') === 'Single')>Single</option>
                        <option value="Married" @selected(request('civil_status') === 'Married')>Married</option>
                        <option value="Divorced" @selected(request('civil_status') === 'Divorced')>Divorced</option>
                        <option value="Widowed" @selected(request('civil_status') === 'Widowed')>Widowed</option>
                    </select>
                </div>

                <div style="display: flex; gap: 12px;">
                    <button type="submit" style="background-color: #0d6efd; color: white; padding: 10px 20px; border-radius: 8px; border: none; font-size: 14px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="bi bi-search"></i> Search & Filter
                    </button>
                    <a href="{{ route('employees.index') }}" style="background-color: #e5e7eb; color: #374151; padding: 10px 20px; border-radius: 8px; border: none; font-size: 14px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </a>
                    <button type="button" style="background-color: #e5e7eb; color: #374151; padding: 10px 20px; border-radius: 8px; border: none; font-size: 14px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;" onclick="alert('Export feature coming soon!')">
                        <i class="bi bi-file-excel"></i> Export Excel
                    </button>
                </div>
            </form>
        </div>

        <!-- Content Container -->
        <div class="container-fluid">
            @if (session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
