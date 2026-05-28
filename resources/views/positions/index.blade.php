<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Positions - Dashboard</title>
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
            margin-bottom: 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 0;
        }

        .page-header p {
            color: #6b7280;
            font-size: 16px;
            margin: 0;
        }

        .page-title-section {
            flex: 1;
        }

        .btn-primary-custom {
            background-color: #0d6efd;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary-custom:hover {
            background-color: #0b5ed7;
            color: white;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-left: 4px solid;
        }

        .stat-card.total {
            border-left-color: #0d6efd;
        }

        .stat-card.filled {
            border-left-color: #10b981;
        }

        .stat-card.vacant {
            border-left-color: #f59e0b;
        }

        .stat-card.abolished {
            border-left-color: #ef4444;
        }

        .stat-label {
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #1f2937;
        }

        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            padding: 24px;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background-color: #f5f5f5;
            border-color: #e5e7eb;
            font-weight: 600;
            color: #374151;
            padding: 16px;
        }

        .table tbody td {
            padding: 16px;
            border-color: #e5e7eb;
            color: #1f2937;
        }

        .table tbody tr:hover {
            background-color: #f9fafb;
        }

        .badge-filled {
            background-color: #dcfce7;
            color: #166534;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-vacant {
            background-color: #fef3c7;
            color: #92400e;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-abolished {
            background-color: #fee2e2;
            color: #991b1b;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-active {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-inactive {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-action {
            padding: 8px 12px;
            border-radius: 6px;
            border: none;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .btn-edit:hover {
            background-color: #bbdefb;
        }

        .btn-delete {
            background-color: #ffebee;
            color: #c62828;
        }

        .btn-delete:hover {
            background-color: #ffcdd2;
        }

        .btn-view {
            background-color: #f3e5f5;
            color: #7b1fa2;
        }

        .btn-view:hover {
            background-color: #e1bee7;
        }

        .alert {
            margin-bottom: 24px;
            border-radius: 8px;
            border: none;
        }

        .empty-state {
            text-align: center;
            padding: 48px 24px;
        }

        .empty-state i {
            font-size: 48px;
            color: #d1d5db;
            margin-bottom: 16px;
        }

        .empty-state h3 {
            color: #6b7280;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #9ca3af;
            margin-bottom: 24px;
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="{{ asset('img/logo.png') }}" alt="CSC Logo" class="sidebar-logo"
                onerror="this.style.display='none'">
            <div class="sidebar-title-container">
                <span class="sidebar-title-main">CSC</span>
                <span class="sidebar-title-sub">Plantilla System</span>
            </div>
        </div>

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
                    <a href="{{ route('plantilla.index') }}"
                        class="nav-link {{ request()->routeIs('plantilla.*') ? 'active' : '' }}">
                        <i class="bi bi-person-badge" style="font-size: 20px;"></i>
                        <span>Inventory of Personnel</span>
                    </a>
                </div>
            @endif

            @if(auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->isSalaryAdmin()))
                <div style="margin: 8px 12px;">
                    <a href="{{ route('step-increment.index') }}"
                        class="nav-link {{ request()->routeIs('step-increment.*') ? 'active' : '' }}">
                        <i class="bi bi-graph-up-arrow" style="font-size: 20px;"></i>
                        <span>Step Increment</span>
                    </a>
                </div>
            @endif

            @if(auth()->check() && auth()->user()->isSuperAdmin())
                <div style="margin: 8px 12px;">
                    <a href="{{ route('imports.index') }}"
                        class="nav-link {{ request()->routeIs('imports.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-spreadsheet" style="font-size: 20px;"></i>
                        <span>Import Data</span>
                    </a>
                </div>
            @endif

            <div style="margin: 8px 12px;">
                <a href="{{ route('profile.edit') }}"
                    class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
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
            <div class="page-title-section">
                <h1>Positions</h1>
                <p>Manage plantilla positions and vacancies</p>
            </div>
            <a href="{{ route('positions.create') }}" class="btn-primary-custom">
                <i class="bi bi-plus-lg"></i>
                Create Position
            </a>
        </div>

        <!-- Alerts -->
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Error!</strong>
                <ul class="mb-0 ms-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-label">Total Positions</div>
                <div class="stat-number">{{ $stats['total'] }}</div>
            </div>
            <div class="stat-card filled">
                <div class="stat-label">Filled Positions</div>
                <div class="stat-number">{{ $stats['filled'] }}</div>
            </div>
            <div class="stat-card vacant">
                <div class="stat-label">Vacant Positions</div>
                <div class="stat-number">{{ $stats['vacant'] }}</div>
            </div>
            <div class="stat-card abolished">
                <div class="stat-label">Abolished Positions</div>
                <div class="stat-number">{{ $stats['abolished'] }}</div>
            </div>
        </div>

        <!-- Table Container -->
        @if ($positions->count() > 0)
            <div class="table-container">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Code</th>
                            <th>Department</th>
                            <th>Salary Grade</th>
                            <th>Status</th>
                            <th>Filled</th>
                            <th width="200">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($positions as $position)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $position->title }}</td>
                                <td><code
                                        style="background-color: #f3f4f6; padding: 4px 8px; border-radius: 4px;">{{ $position->code }}</code>
                                </td>
                                <td>{{ $position->organizationalUnit->name }}</td>
                                <td>{{ $position->salary_grade ?? '-' }}</td>
                                <td>
                                    @if ($position->abolished)
                                        <span class="badge-abolished">
                                            <i class="bi bi-x-circle"></i> Abolished
                                        </span>
                                    @elseif ($position->status === 'Active')
                                        <span class="status-badge status-active">Active</span>
                                    @else
                                        <span class="status-badge status-inactive">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($position->abolished)
                                        <span class="badge-abolished">Abolished</span>
                                    @elseif ($position->isFilled())
                                        <span class="badge-filled">
                                            <i class="bi bi-check-circle"></i> Filled
                                        </span>
                                    @else
                                        <span class="badge-vacant">
                                            <i class="bi bi-exclamation-circle"></i> Vacant
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('positions.show', $position->id) }}" class="btn-action btn-view"
                                            title="View">
                                            <i class="bi bi-eye" style="font-size: 14px;"></i>
                                        </a>
                                        <a href="{{ route('positions.edit', $position->id) }}" class="btn-action btn-edit"
                                            title="Edit">
                                            <i class="bi bi-pencil" style="font-size: 14px;"></i>
                                        </a>
                                        <form action="{{ route('positions.destroy', $position) }}" method="POST"
                                            id="form-delete-position-{{ $position->id }}" style="display:inline;">
                                            @csrf @method('DELETE')
                                            <button type="button"
                                                onclick="confirmDeletePosition('{{ $position->id }}', '{{ addslashes($position->title) }}')"
                                                class="btn-action btn-delete" title="Delete">
                                                <i class="bi bi-trash" style="font-size: 14px;"></i>
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
            <div class="table-container">
                <div class="empty-state">
                    <i class="bi bi-briefcase"></i>
                    <h3>No Positions Found</h3>
                    <p>Get started by creating your first position</p>
                    <a href="{{ route('positions.create') }}" class="btn-primary-custom">
                        <i class="bi bi-plus-lg"></i>
                        Create First Position
                    </a>
                </div>
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>