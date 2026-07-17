<x-dashboard-app>
    <style>
        .page-hero {
            background: linear-gradient(135deg, #1e3a5f 0%, #1a5276 55%, #0f4c75 100%);
            border-radius: 14px;
            padding: 28px 32px;
            position: relative;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .page-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255, 255, 255, .08) 1px, transparent 1px);
            background-size: 22px 22px;
        }

        .page-hero-inner {
            position: relative;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .page-title {
            color: #ffffff;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0 0 4px 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.95rem;
            margin: 0;
        }

        .btn-add {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-add:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            transform: translateY(-1px);
        }

        .content-card {
            background: var(--color-surface, #ffffff);
            border-radius: 14px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            border: 1px solid var(--color-border, #e2e8f0);
            overflow: hidden;
        }

        .table-responsive {
            padding: 1rem;
        }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 0;
        }

        .table th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            padding: 12px 16px;
            border-bottom: 1px solid #e2e8f0;
        }

        .table td {
            padding: 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        .table tbody tr:hover td {
            background-color: #f8fafc;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-active {
            background: #dcfce7;
            color: #166534;
        }

        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .action-btns {
            display: flex;
            gap: 8px;
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            color: #64748b;
            background: #f1f5f9;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-icon:hover {
            color: #0f4c75;
            background: #e2e8f0;
        }
        
        .btn-icon.delete:hover {
            color: #dc2626;
            background: #fee2e2;
        }
    </style>

    <div class="px-4 py-4">
        <!-- Hero Header -->
        <div class="page-hero">
            <div class="page-hero-inner">
                <div>
                    <h1 class="page-title">
                        <i class="bi bi-building"></i> Offices / Organizational Units
                    </h1>
                    <p class="page-subtitle">Manage offices and departments across the organization</p>
                </div>
                <a href="{{ route('organizational-units.create') }}" class="btn-add">
                    <i class="bi bi-plus-lg"></i> Add New Office
                </a>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="background: #dcfce7; border: 1px solid #86efac; color: #166534; border-radius: 8px;">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <div class="content-card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Office Name</th>
                            <th>Sub-Office</th>
                            <th>Code</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($units as $unit)
                        <tr>
                            <td><strong>{{ $unit->name }}</strong></td>
                            <td>{{ $unit->sub_office ?? '-' }}</td>
                            <td>{{ $unit->code }}</td>
                            <td>
                                <span class="status-badge {{ $unit->status === 'Active' ? 'status-active' : 'status-inactive' }}">
                                    {{ $unit->status }}
                                </span>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="{{ route('organizational-units.edit', $unit->id) }}" class="btn-icon" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('organizational-units.destroy', $unit->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this office?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon delete" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                No offices found. Create one to get started.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-dashboard-app>
