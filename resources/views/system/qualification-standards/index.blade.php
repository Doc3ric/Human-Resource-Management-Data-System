<x-dashboard-app>
    <style>
        .qs-hero {
            background: linear-gradient(135deg, #1e3a5f 0%, #0f4c75 100%);
            border-radius: 14px;
            padding: 28px 32px;
            position: relative;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .qs-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255, 255, 255, .08) 1px, transparent 1px);
            background-size: 22px 22px;
        }

        .qs-hero-inner {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .qs-hero h1 {
            color: #fff;
            font-size: 24px;
            font-weight: 800;
            margin: 0;
        }

        .qs-hero p {
            color: rgba(255, 255, 255, .6);
            font-size: 13px;
            margin: 6px 0 0;
        }

        .qs-btn-primary {
            background: #10b981;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            text-decoration: none;
        }
        
        .qs-btn-primary:hover {
            background: #059669;
            transform: translateY(-1px);
            color: #fff;
        }

        .qs-btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }

        .qs-btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        .qs-search-bar {
            background: #fff;
            border-radius: 14px;
            padding: 16px 20px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .qs-search-input {
            flex: 1;
            padding: 10px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }

        .qs-search-input:focus {
            border-color: #3b82f6;
        }

        .qs-table-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .qs-table {
            width: 100%;
            border-collapse: collapse;
        }

        .qs-table th {
            background: #f8fafc;
            color: #475569;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            padding: 14px 20px;
            text-align: left;
            border-bottom: 2px solid #e2e8f0;
        }

        .qs-table td {
            padding: 16px 20px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            font-size: 13.5px;
            vertical-align: middle;
        }

        .qs-table tr:hover {
            background: #f8fafc;
        }

        .qs-title-cell {
            font-weight: 700;
            color: #0f172a;
        }

        .qs-trunc {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .qs-action-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            background: #f1f5f9;
            color: #64748b;
            transition: all 0.2s;
            cursor: pointer;
        }

        .qs-action-btn:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .qs-action-btn.edit:hover { background: #dbeafe; color: #2563eb; }
        .qs-action-btn.delete:hover { background: #fee2e2; color: #dc2626; }

        /* Modal Overrides for better aesthetics */
        .modal-content {
            border-radius: 16px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .modal-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            border-radius: 16px 16px 0 0;
            padding: 20px 24px;
        }
        .modal-title {
            font-weight: 800;
            color: #0f172a;
        }
        .modal-body {
            padding: 24px;
        }
        .modal-footer {
            border-top: 1px solid #e2e8f0;
            padding: 16px 24px;
            background: #f8fafc;
            border-radius: 0 0 16px 16px;
        }
        
        .form-label {
            font-weight: 700;
            color: #334155;
            font-size: 13px;
        }
    </style>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 12px; font-size: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid #bbf7d0;">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error') || $errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 12px; font-size: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid #fecaca;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> 
            {{ session('error') ?? 'There was an error saving the record. Please check the inputs.' }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Hero --}}
    <div class="qs-hero">
        <div class="qs-hero-inner">
            <div>
                <h1><i class="bi bi-journal-bookmark-fill me-2"></i>CSC Qualification Standards</h1>
                <p>Master Reference for auto-populating Position Qualifications (Education, Training, Experience, Eligibility)</p>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('administration.index') }}" class="qs-btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Admin
                </a>
                <button type="button" class="qs-btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                    <i class="bi bi-plus-lg"></i> Add New Standard
                </button>
            </div>
        </div>
    </div>

    {{-- Search Bar --}}
    <div class="qs-search-bar">
        <form method="GET" action="{{ route('system.qualification-standards.index') }}" style="display: flex; gap: 12px; width: 100%;">
            <div style="flex: 1; position: relative;">
                <i class="bi bi-search" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                <input type="text" name="search" class="qs-search-input" style="padding-left: 44px;" placeholder="Search Position Title..." value="{{ request('search') }}">
            </div>
            <button type="submit" class="qs-btn-primary" style="background: #3b82f6; box-shadow: none;">Search</button>
            @if(request('search'))
                <a href="{{ route('system.qualification-standards.index') }}" class="qs-btn-secondary" style="background: #f1f5f9; color: #475569; border-color: #e2e8f0;">Clear</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="qs-table-card">
        <div class="table-responsive">
            <table class="qs-table">
                <thead>
                    <tr>
                        <th>Position Title</th>
                        <th>Education</th>
                        <th>Training</th>
                        <th>Experience</th>
                        <th>Eligibility</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($standards as $qs)
                        <tr>
                            <td class="qs-title-cell">{{ $qs->position_title }}</td>
                            <td><div class="qs-trunc" title="{{ $qs->education }}">{{ $qs->education ?: '-' }}</div></td>
                            <td><div class="qs-trunc" title="{{ $qs->training }}">{{ $qs->training ?: '-' }}</div></td>
                            <td><div class="qs-trunc" title="{{ $qs->experience }}">{{ $qs->experience ?: '-' }}</div></td>
                            <td><div class="qs-trunc" title="{{ $qs->eligibility }}">{{ $qs->eligibility ?: '-' }}</div></td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                    <button type="button" class="qs-action-btn edit" data-bs-toggle="modal" data-bs-target="#editModal{{ $qs->id }}" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <form action="{{ route('system.qualification-standards.destroy', $qs) }}" method="POST" style="display: inline;" onsubmit="return confirm('Delete this qualification standard?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="qs-action-btn delete" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- Edit Modal for each record -->
                        <div class="modal fade" id="editModal{{ $qs->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $qs->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content">
                                    <form action="{{ route('system.qualification-standards.update', $qs) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editModalLabel{{ $qs->id }}">Edit Qualification Standard</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-4">
                                                <label class="form-label">Position Title <span class="text-danger">*</span></label>
                                                <input type="text" name="position_title" class="form-control" value="{{ $qs->position_title }}" required>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Education</label>
                                                    <textarea name="education" rows="3" class="form-control">{{ $qs->education }}</textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Training</label>
                                                    <textarea name="training" rows="3" class="form-control">{{ $qs->training }}</textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Experience</label>
                                                    <textarea name="experience" rows="3" class="form-control">{{ $qs->experience }}</textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Eligibility</label>
                                                    <textarea name="eligibility" rows="3" class="form-control">{{ $qs->eligibility }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="qs-btn-primary"><i class="bi bi-save"></i> Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 60px 20px;">
                                <div style="color: #94a3b8; margin-bottom: 12px;">
                                    <i class="bi bi-inbox" style="font-size: 48px;"></i>
                                </div>
                                <h5 style="color: #475569; font-weight: 700;">No Qualification Standards Found</h5>
                                <p style="color: #64748b; font-size: 14px;">Create your first CSC Qualification Standard to automate the New Vacancy form.</p>
                                <button type="button" class="qs-btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#createModal">
                                    <i class="bi bi-plus-lg"></i> Add New Standard
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($standards->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid #f1f5f9;">
                {{ $standards->links() }}
            </div>
        @endif
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form action="{{ route('system.qualification-standards.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="createModalLabel">Add Qualification Standard</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-4">
                            <label class="form-label">Position Title <span class="text-danger">*</span></label>
                            <input type="text" name="position_title" class="form-control" placeholder="e.g. Administrative Aide I" required>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Education</label>
                                <textarea name="education" rows="3" class="form-control"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Training</label>
                                <textarea name="training" rows="3" class="form-control"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Experience</label>
                                <textarea name="experience" rows="3" class="form-control"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Eligibility</label>
                                <textarea name="eligibility" rows="3" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="qs-btn-primary"><i class="bi bi-save"></i> Save Standard</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-dashboard-app>
