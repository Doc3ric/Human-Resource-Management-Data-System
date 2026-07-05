<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View OFFICE</title>
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
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .page-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin: 0;
        }

        .page-header p {
            color: #6b7280;
            font-size: 14px;
            margin: 0;
        }

        .back-link {
            color: #6b7280;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: #0d6efd;
        }

        .detail-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            padding: 32px;
            max-width: 600px;
            margin-top: 24px;
        }

        .detail-group {
            margin-bottom: 28px;
            padding-bottom: 28px;
            border-bottom: 1px solid #e5e7eb;
        }

        .detail-group:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .detail-label {
            display: block;
            font-weight: 600;
            color: #6b7280;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .detail-value {
            font-size: 16px;
            color: #1f2937;
            font-weight: 500;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
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

        .code-box {
            background-color: #f3f4f6;
            padding: 8px 12px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: #374151;
        }

        .actions {
            display: flex;
            gap: 12px;
            margin-top: 32px;
        }

        .btn-action {
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-edit {
            background-color: #0d6efd;
            color: white;
        }

        .btn-edit:hover {
            background-color: #0b5ed7;
            color: white;
        }

        .btn-back {
            background-color: #e5e7eb;
            color: #374151;
        }

        .btn-back:hover {
            background-color: #d1d5db;
            color: #1f2937;
        }

        .btn-delete {
            background-color: #fecaca;
            color: #991b1b;
        }

        .btn-delete:hover {
            background-color: #fca5a5;
            color: #7f1d1d;
        }

        .meta-info {
            background-color: #f9fafb;
            padding: 16px;
            border-radius: 8px;
            margin-top: 24px;
            font-size: 13px;
            color: #6b7280;
        }

        .meta-info p {
            margin: 4px 0;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
                <div class="sidebar-header">
            <img src="{{ asset('img/logo.png') }}" alt="CSC Logo" class="sidebar-logo" onerror="this.style.display='none'">
            <div class="sidebar-title-container">
                <span class="sidebar-title-main" style="line-height: 1.3; font-size: 14.5px; padding-top: 2px;">Human Resource<br>Data Management<br>System</span>
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
        <div class="page-header">
            <div>
                <a href="{{ route('organizational-units.index') }}" class="back-link">
                    <i class="bi bi-arrow-left"></i>
                    Back to Units
                </a>
                <h1>{{ $organizationalUnit->name }}</h1>
            </div>
        </div>

        <div class="detail-container">
            <div class="detail-group">
                <span class="detail-label">Name</span>
                <div class="detail-value">{{ $organizationalUnit->name }}</div>
            </div>

            <div class="detail-group">
                <span class="detail-label">Code</span>
                <div class="detail-value">
                    <span class="code-box">{{ $organizationalUnit->code }}</span>
                </div>
            </div>

            <div class="detail-group">
                <span class="detail-label">Status</span>
                <div class="detail-value">
                    @if ($organizationalUnit->status === 'Active')
                        <span class="status-badge status-active">
                            <i class="bi bi-check-circle"></i> Active
                        </span>
                    @else
                        <span class="status-badge status-inactive">
                            <i class="bi bi-x-circle"></i> Inactive
                        </span>
                    @endif
                </div>
            </div>

            <div class="meta-info">
                <p><strong>Created:</strong> {{ $organizationalUnit->created_at->format('F d, Y \a\t h:i A') }}</p>
                <p><strong>Last Updated:</strong> {{ $organizationalUnit->updated_at->format('F d, Y \a\t h:i A') }}</p>
            </div>

            <div class="actions">
                <a href="{{ route('organizational-units.edit', $organizationalUnit->id) }}" class="btn-action btn-edit">
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <div>
                <a href="{{ route('organizational-units.index') }}" class="back-link">
                    <i class="bi bi-arrow-left"></i>
                    Back to Units
                </a>
                <h1>{{ $organizationalUnit->name }}</h1>
            </div>
        </div>

        <div class="detail-container">
            <div class="detail-group">
                <span class="detail-label">Name</span>
                <div class="detail-value">{{ $organizationalUnit->name }}</div>
            </div>

            <div class="detail-group">
                <span class="detail-label">Code</span>
                <div class="detail-value">
                    <span class="code-box">{{ $organizationalUnit->code }}</span>
                </div>
            </div>

            <div class="detail-group">
                <span class="detail-label">Status</span>
                <div class="detail-value">
                    @if ($organizationalUnit->status === 'Active')
                        <span class="status-badge status-active">
                            <i class="bi bi-check-circle"></i> Active
                        </span>
                    @else
                        <span class="status-badge status-inactive">
                            <i class="bi bi-x-circle"></i> Inactive
                        </span>
                    @endif
                </div>
            </div>

            <div class="meta-info">
                <p><strong>Created:</strong> {{ $organizationalUnit->created_at->format('F d, Y \a\t h:i A') }}</p>
                <p><strong>Last Updated:</strong> {{ $organizationalUnit->updated_at->format('F d, Y \a\t h:i A') }}</p>
            </div>

            <div class="actions">
                <a href="{{ route('organizational-units.edit', $organizationalUnit->id) }}" class="btn-action btn-edit">
                    <i class="bi bi-pencil"></i>
                    Edit
                </a>
                <a href="{{ route('organizational-units.index') }}" class="btn-action btn-back">
                    <i class="bi bi-arrow-left"></i>
                    Back to List
                </a>
                @if (auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                <form action="{{ route('organizational-units.destroy', $organizationalUnit->id) }}" method="POST" id="form-delete-ou-show" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="button" onclick="confirmDeleteOuShow()" class="btn-primary-custom" style="background-color: transparent; border: 1px solid #fee2e2; color: #dc2626;">
                        <i class="bi bi-trash"></i>
                        Delete
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDeleteOuShow() {
            Swal.fire({
                title: 'Delete OFFICE?',
                text: "Are you sure you want to delete this OFFICE? This cannot be undone.",
                icon: 'warning',
                iconColor: '#dc3545',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-delete-ou-show').submit();
                }
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
