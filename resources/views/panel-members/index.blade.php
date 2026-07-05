<x-dashboard-app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Panel Member Accounts</h2>
    </x-slot>

    <div class="content-wrapper p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-1"><i class="bi bi-person-badge-fill me-2 text-primary"></i>HRMPSB / TWG Panel Accounts</h3>
                <p class="text-muted mb-0" style="font-size:13px;">
                    Manage login accounts for HRMPSB board members and TWG evaluators.
                    They access the portal at <strong>/panel/login</strong>.
                </p>
            </div>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                <i class="bi bi-plus-circle me-1"></i> Add Member
            </button>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert" style="font-size:13px;">
                <i class="bi bi-check-circle-fill me-1"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($members->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-people" style="font-size:3rem;"></i>
                <p class="mt-3">No panel member accounts yet. Add one to get started.</p>
            </div>
        @else
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size:13px;">
                        <thead style="background:#f8fafc;">
                            <tr>
                                <th class="px-4 py-3">Name</th>
                                <th>Email</th>
                                <th>Type</th>
                                <th>Position</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($members as $member)
                                <tr>
                                    <td class="px-4 fw-semibold">{{ $member->name }}</td>
                                    <td class="text-muted">{{ $member->email }}</td>
                                    <td>
                                        @if($member->type === 'twg')
                                            <span class="badge" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;">
                                                <i class="bi bi-star-fill me-1"></i> TWG
                                            </span>
                                        @else
                                            <span class="badge" style="background:#dbeafe;color:#1e40af;border:1px solid #bfdbfe;">
                                                <i class="bi bi-people-fill me-1"></i> HRMPSB
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-muted">{{ $member->position ?? '—' }}</td>
                                    <td>
                                        @if($member->is_active)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-muted">
                                        {{ $member->last_login_at ? $member->last_login_at->diffForHumans() : 'Never' }}
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex gap-2 justify-content-end">
                                            {{-- Toggle Active --}}
                                            <form action="{{ route('panel-members.toggle-active', $member) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm {{ $member->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $member->is_active ? 'Deactivate' : 'Activate' }}">
                                                    <i class="bi bi-{{ $member->is_active ? 'pause-circle' : 'play-circle' }}"></i>
                                                </button>
                                            </form>
                                            {{-- Reset Password --}}
                                            <button class="btn btn-sm btn-outline-secondary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#resetPwModal"
                                                    data-id="{{ $member->id }}"
                                                    data-name="{{ $member->name }}"
                                                    title="Reset Password">
                                                <i class="bi bi-key"></i>
                                            </button>
                                            {{-- Delete --}}
                                            <form action="{{ route('panel-members.destroy', $member) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('Delete account for {{ addslashes($member->name) }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
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
            </div>
        @endif
    </div>

    {{-- Add Member Modal --}}
    <div class="modal fade" id="addMemberModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill me-2 text-primary"></i>Add Panel Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('panel-members.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size:13px;">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-sm" required placeholder="e.g., Juan dela Cruz">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size:13px;">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control form-control-sm" required placeholder="email@example.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size:13px;">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control form-control-sm" required minlength="8">
                            <div class="form-text">Minimum 8 characters.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size:13px;">Member Type <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" id="type_hrmpsb" value="hrmpsb" checked>
                                    <label class="form-check-label small" for="type_hrmpsb">HRMPSB Member</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" id="type_twg" value="twg">
                                    <label class="form-check-label small" for="type_twg">TWG Member</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size:13px;">Position / Designation</label>
                            <input type="text" name="position" class="form-control form-control-sm" placeholder="e.g., Chief Education Program Specialist">
                        </div>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary fw-bold"><i class="bi bi-save me-1"></i> Create Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Reset Password Modal --}}
    <div class="modal fade" id="resetPwModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom">
                    <h6 class="modal-title fw-bold"><i class="bi bi-key me-2"></i>Reset Password</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="resetPwForm" action="" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body">
                        <p class="small text-muted mb-2">Setting new password for <strong id="resetPwName"></strong></p>
                        <input type="password" name="password" class="form-control form-control-sm" required minlength="8" placeholder="New password">
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-warning fw-bold"><i class="bi bi-key me-1"></i> Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('resetPwModal').addEventListener('show.bs.modal', function (e) {
        const btn   = e.relatedTarget;
        const id    = btn.dataset.id;
        const name  = btn.dataset.name;
        document.getElementById('resetPwName').textContent = name;
        document.getElementById('resetPwForm').action = '/panel-members/' + id + '/reset-password';
    });
    </script>
</x-dashboard-app>
