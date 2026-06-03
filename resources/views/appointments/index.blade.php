<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments - Dashboard</title>
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
                <span class="sidebar-title-main" style="line-height: 1.3; font-size: 14.5px; padding-top: 2px;">Human Resource<br>Data Management<br>System</span>
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
            <div>
                <h1>Appointments</h1>
                <p style="color: #6b7280;">Manage employee appointments to positions</p>
            </div>
            <a href="{{ route('appointments.create') }}" class="btn-primary-custom">
                <i class="bi bi-plus-lg"></i>
                New Appointment
            </a>
        </div>

        <!-- Alerts -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Filter Section -->
        <div style="background: white; border-radius: 12px; padding: 20px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);">
            <div style="font-size: 14px; font-weight: 700; color: #1f2937; margin-bottom: 16px;">
                <i class="bi bi-funnel" style="margin-right: 8px;"></i> Filter & Search
            </div>
            <form method="GET" action="{{ route('appointments.index') }}">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px;">
                    <input 
                        type="text" 
                        name="search" 
                        style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;" 
                        placeholder="Search by employee or position..."
                        value="{{ request('search') }}"
                    >
                    <select name="status" style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                        <option value="">All Statuses</option>
                        <option value="Active" @selected(request('status') === 'Active')>Active</option>
                        <option value="Ended" @selected(request('status') === 'Ended')>Ended</option>
                    </select>
                    <select name="type" style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                        <option value="">All Types</option>
                        <option value="Regular" @selected(request('type') === 'Regular')>Regular</option>
                        <option value="Temporary" @selected(request('type') === 'Temporary')>Temporary</option>
                    </select>
                </div>
                <div style="display: flex; gap: 12px;">
                    <button type="submit" style="background-color: #0d6efd; color: white; padding: 10px 20px; border-radius: 8px; border: none; font-size: 14px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="bi bi-search"></i> Search
                    </button>
                    <a href="{{ route('appointments.index') }}" style="background-color: #e5e7eb; color: #374151; padding: 10px 20px; border-radius: 8px; border: none; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Content Container -->
        <div class="container-fluid">
            @if ($appointments->count() > 0)
                <div style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); padding: 24px;">
                    <table class="table table-hover">
                        <thead style="background-color: #f5f5f5;">
                            <tr>
                                <th>Employee</th>
                                <th>Position</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th width="150">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($appointments as $apt)
                                <tr>
                                    <td>{{ $apt->employee->full_name }}</td>
                                    <td>{{ $apt->position->title }}</td>
                                    <td>{{ $apt->appointment_start->format('M d, Y') }}</td>
                                    <td>{{ $apt->appointment_end?->format('M d, Y') ?? '-' }}</td>
                                    <td>
                                        <span style="display: inline-block; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; background-color: {{ $apt->status === 'Active' ? '#dcfce7' : '#fee2e2' }}; color: {{ $apt->status === 'Active' ? '#166534' : '#991b1b' }};">
                                            {{ $apt->status }}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 8px;">
                                            <a href="{{ route('appointments.show', $apt->id) }}" style="padding: 8px 12px; border-radius: 6px; border: none; font-size: 13px; background-color: #f3e5f5; color: #7b1fa2; text-decoration: none; cursor: pointer;" title="View">
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1>Appointments</h1>
                <p style="color: #6b7280;">Manage employee appointments to positions</p>
            </div>
            <a href="{{ route('appointments.create') }}" class="btn-primary-custom">
                <i class="bi bi-plus-lg"></i>
                New Appointment
            </a>
        </div>

        <!-- Alerts -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Filter Section -->
        <div style="background: white; border-radius: 12px; padding: 20px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);">
            <div style="font-size: 14px; font-weight: 700; color: #1f2937; margin-bottom: 16px;">
                <i class="bi bi-funnel" style="margin-right: 8px;"></i> Filter & Search
            </div>
            <form method="GET" action="{{ route('appointments.index') }}">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px;">
                    <input 
                        type="text" 
                        name="search" 
                        style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;" 
                        placeholder="Search by employee or position..."
                        value="{{ request('search') }}"
                    >
                    <select name="status" style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                        <option value="">All Statuses</option>
                        <option value="Active" @selected(request('status') === 'Active')>Active</option>
                        <option value="Ended" @selected(request('status') === 'Ended')>Ended</option>
                    </select>
                    <select name="type" style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                        <option value="">All Types</option>
                        <option value="Regular" @selected(request('type') === 'Regular')>Regular</option>
                        <option value="Temporary" @selected(request('type') === 'Temporary')>Temporary</option>
                    </select>
                </div>
                <div style="display: flex; gap: 12px;">
                    <button type="submit" style="background-color: #0d6efd; color: white; padding: 10px 20px; border-radius: 8px; border: none; font-size: 14px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="bi bi-search"></i> Search
                    </button>
                    <a href="{{ route('appointments.index') }}" style="background-color: #e5e7eb; color: #374151; padding: 10px 20px; border-radius: 8px; border: none; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Content Container -->
        <div class="container-fluid">
            @if ($appointments->count() > 0)
                <div style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); padding: 24px;">
                    <table class="table table-hover">
                        <thead style="background-color: #f5f5f5;">
                            <tr>
                                <th>Employee</th>
                                <th>Position</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th width="150">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($appointments as $apt)
                                <tr>
                                    <td>{{ $apt->employee->full_name }}</td>
                                    <td>{{ $apt->position->title }}</td>
                                    <td>{{ $apt->appointment_start->format('M d, Y') }}</td>
                                    <td>{{ $apt->appointment_end?->format('M d, Y') ?? '-' }}</td>
                                    <td>
                                        <span style="display: inline-block; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; background-color: {{ $apt->status === 'Active' ? '#dcfce7' : '#fee2e2' }}; color: {{ $apt->status === 'Active' ? '#166534' : '#991b1b' }};">
                                            {{ $apt->status }}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 8px;">
                                            <a href="{{ route('appointments.show', $apt->id) }}" style="padding: 8px 12px; border-radius: 6px; border: none; font-size: 13px; background-color: #f3e5f5; color: #7b1fa2; text-decoration: none; cursor: pointer;" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('appointments.edit', $apt->id) }}" style="padding: 8px 12px; border-radius: 6px; border: none; font-size: 13px; background-color: #e3f2fd; color: #1976d2; text-decoration: none; cursor: pointer;" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('appointments.destroy', $apt->id) }}" method="POST" id="form-delete-apt-{{ $apt->id }}" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" onclick="confirmDeleteApt('{{ $apt->id }}')" style="padding: 8px 12px; border-radius: 6px; border: none; font-size: 13px; background-color: #ffebee; color: #c62828; cursor: pointer;" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="background: white; border-radius: 12px; padding: 48px; text-align: center; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);">
                    <i class="bi bi-inbox" style="font-size: 48px; color: #d1d5db; display: block; margin-bottom: 16px;"></i>
                    <h3 style="color: #6b7280;">No Appointments Found</h3>
                    <a href="{{ route('appointments.create') }}" style="margin-top: 16px; background-color: #0d6efd; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="bi bi-plus-lg"></i>
                        Create First Appointment
                    </a>
                </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDeleteApt(id) {
            Swal.fire({
                title: 'Delete Appointment?',
                text: "Are you sure you want to delete this appointment? This action cannot be undone.",
                icon: 'warning',
                iconColor: '#dc3545',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-delete-apt-' + id).submit();
                }
            });
        }
    </script>
</body>
</html>
