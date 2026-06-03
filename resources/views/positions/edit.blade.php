<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Position</title>
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
            font-size: 28px;
            font-weight: 700;
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
            margin-bottom: 12px;
        }

        .back-link:hover {
            color: #0d6efd;
        }

        .form-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            padding: 32px;
            max-width: 700px;
            margin-top: 24px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: 'Segoe UI', sans-serif;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 32px;
        }

        .btn-submit {
            background-color: #0d6efd;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-submit:hover {
            background-color: #0b5ed7;
        }

        .btn-cancel {
            background-color: #e5e7eb;
            color: #374151;
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

        .btn-cancel:hover {
            background-color: #d1d5db;
        }

        .invalid-feedback {
            display: block;
            color: #dc2626;
            font-size: 13px;
            margin-top: 6px;
        }

        .form-group.has-error input,
        .form-group.has-error select {
            border-color: #dc2626;
            background-color: #fef2f2;
        }

        .alert {
            margin-bottom: 24px;
            border-radius: 8px;
            border: none;
        }

        .form-help-text {
            color: #9ca3af;
            font-size: 13px;
            margin-top: 4px;
        }

        .info-box {
            background-color: #f0f9ff;
            border-left: 4px solid #0d6efd;
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 24px;
            font-size: 13px;
            color: #1e40af;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
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
        <a href="{{ route('positions.index') }}" class="back-link">
            <i class="bi bi-arrow-left"></i>
            Back to Positions
        </a>

        <div class="page-header">
            <h1>Edit Position</h1>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Validation Error!</strong>
                <ul class="mb-0 ms-3 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="form-container">
            <div class="info-box">
                <i class="bi bi-info-circle"></i>
                Editing: <strong>{{ $position->title }}</strong> ({{ $position->code }})
            </div>

            <form action="{{ route('positions.update', $position->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-row">
                    <div class="form-group @error('title') has-error @enderror">
                        <label for="title">Position Title <span style="color: #dc2626;">*</span></label>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            value="{{ old('title', $position->title) }}"
                            placeholder="e.g., Finance Manager"
                            required
                        >
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-help-text">The official position title</div>
                    </div>

                    <div class="form-group @error('code') has-error @enderror">
                        <label for="code">Position Code <span style="color: #dc2626;">*</span></label>
                        <input 
                            type="text" 
                            id="code" 
                            name="code" 
                            value="{{ old('code', $position->code) }}"
                            placeholder="e.g., POS-FIN-001"
                            required
                        >
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-help-text">Unique position identifier</div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group @error('organizational_unit_id') has-error @enderror">
                        <label for="organizational_unit_id">Department <span style="color: #dc2626;">*</span></label>
                        <select id="organizational_unit_id" name="organizational_unit_id" required>
                            <option value="">Select Department</option>
                            @foreach ($organizationalUnits as $unit)
                                <option value="{{ $unit->id }}" @selected(old('organizational_unit_id', $position->organizational_unit_id) == $unit->id)>
                                    {{ $unit->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('organizational_unit_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group @error('salary_grade') has-error @enderror">
                        <label for="salary_grade">Salary Grade</label>
                        <input 
                            type="text" 
                            id="salary_grade" 
                            name="salary_grade" 
                            value="{{ old('salary_grade', $position->salary_grade) }}"
                            placeholder="e.g., SG-15"
                        >
                        @error('salary_grade')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-help-text">Optional salary classification</div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group @error('status') has-error @enderror">
                        <label for="status">Status <span style="color: #dc2626;">*</span></label>
                        <select id="status" name="status" required>
                            <option value="">Select Status</option>
                            <option value="Active" @selected(old('status', $position->status) === 'Active')>Active</option>
                            <option value="Inactive" @selected(old('status', $position->status) === 'Inactive')>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="abolished">
                            <input type="checkbox" id="abolished" name="abolished" value="1" @checked(old('abolished', $position->abolished))>
                            <span style="margin-left: 8px; color: #374151; font-weight: 400;">Mark as Abolished</span>
                        </label>
                        <div class="form-help-text">Check if this position is no longer available</div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-save"></i>
                        Update Position
                    </button>
                    <a href="{{ route('positions.index') }}" class="btn-cancel">
                        <i class="bi bi-x-lg"></i>
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
