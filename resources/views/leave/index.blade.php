<x-dashboard-app>
<style>
.lv-hero { background: linear-gradient(135deg,#1e3a5f 0%,#1a5276 55%,#0f4c75 100%); border-radius:14px; padding:24px 28px; margin-bottom:20px; color:#fff; }
.lv-hero h1 { font-size:20px; font-weight:800; margin:0 0 4px; }
.lv-hero p { font-size:12.5px; opacity:.8; margin:0; }
.lv-table { width:100%; border-collapse:collapse; font-size:12.5px; }
.lv-table th { text-align:left; padding:8px 10px; font-size:10.5px; text-transform:uppercase; color:#9ca3af; border-bottom:2px solid #e5e7eb; }
.lv-table td { padding:8px 10px; border-bottom:1px solid #f3f4f6; }
.lv-status { font-size:10px; font-weight:700; padding:2px 8px; border-radius:99px; }
.status-pending { background:#fefce8; color:#854d0e; }
.status-approved { background:#f0fdf4; color:#166534; }
.status-disapproved { background:#fef2f2; color:#991b1b; }
</style>

<div class="lv-hero" style="display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;">
    <div>
        <h1><i class="bi bi-calendar-check-fill"></i> Leave Ledger</h1>
        <p>Omnibus Rules on Leave (CSC MC No. 41 s.1998). Job Order and Contract of Service personnel are excluded from leave-credit accrual per DOLE-CSC-COA-DBM JC No. 1 s.2017.</p>
    </div>
    <button type="button" class="btn btn-light fw-bold px-4 py-2" style="border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.15); white-space:nowrap;" data-bs-toggle="modal" data-bs-target="#fileLeaveModal">
        <i class="bi bi-plus-lg me-1"></i> File Leave Application
    </button>
</div>

<style>
.scoreboard-container { display: flex; gap: 16px; margin-bottom: 24px; flex-wrap: wrap; }
.kpi-card { flex: 1; min-width: 280px; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; display: flex; flex-direction: column; position: relative; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
.kpi-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
.kpi-pill { font-size: 10px; font-weight: 800; padding: 4px 10px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.5px; }
.kpi-icon-box { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 16px; }
.kpi-label { font-size: 11px; font-weight: 700; color: #4b5563; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
.kpi-value { font-size: 32px; font-weight: 900; color: #111827; line-height: 1; margin-bottom: 6px; }
.kpi-sub { font-size: 13px; font-weight: 600; margin-bottom: 16px; }
.kpi-divider { height: 2px; width: 100%; margin-bottom: 12px; border-radius: 2px; }
.kpi-footer { font-size: 11.5px; color: #6b7280; line-height: 1.4; display: flex; gap: 6px; }
.kpi-footer i { font-size: 12px; margin-top: 1px; }

.theme-primary .kpi-pill { background: #e0e7ff; color: #4338ca; }
.theme-primary .kpi-icon-box { background: #e0e7ff; color: #4338ca; }
.theme-primary .kpi-sub { color: #4338ca; }
.theme-primary .kpi-divider { background: #c7d2fe; }
.theme-primary .kpi-footer i { color: #818cf8; }

.theme-warning .kpi-pill { background: #fef3c7; color: #b45309; }
.theme-warning .kpi-icon-box { background: #fef3c7; color: #b45309; }
.theme-warning .kpi-sub { color: #b45309; }
.theme-warning .kpi-divider { background: #fde68a; }
.theme-warning .kpi-footer i { color: #fbbf24; }

.theme-info .kpi-pill { background: #e0f2fe; color: #0369a1; }
.theme-info .kpi-icon-box { background: #e0f2fe; color: #0369a1; }
.theme-info .kpi-sub { color: #0369a1; }
.theme-info .kpi-divider { background: #bae6fd; }
.theme-info .kpi-footer i { color: #38bdf8; }
</style>

<div class="scoreboard-container">
    <div class="kpi-card theme-primary">
        <div class="kpi-header">
            <span class="kpi-pill">CSC MC 41</span>
            <div class="kpi-icon-box"><i class="bi bi-folder2-open"></i></div>
        </div>
        <div class="kpi-label">Total Applications</div>
        <div class="kpi-value">{{ number_format($metrics['total']) }}</div>
        <div class="kpi-sub">100%</div>
        <div class="kpi-divider"></div>
        <div class="kpi-footer">
            <i class="bi bi-info-circle"></i>
            <span>Omnibus Rules on Leave govern all application credits and balances.</span>
        </div>
    </div>
    
    <div class="kpi-card theme-warning">
        <div class="kpi-header">
            <span class="kpi-pill">ARFC §4</span>
            <div class="kpi-icon-box"><i class="bi bi-hourglass-split"></i></div>
        </div>
        <div class="kpi-label">Pending Approval</div>
        <div class="kpi-value">{{ number_format($metrics['pending']) }}</div>
        <div class="kpi-sub">{{ $metrics['pending_pct'] }}%</div>
        <div class="kpi-divider"></div>
        <div class="kpi-footer">
            <i class="bi bi-info-circle"></i>
            <span>Ensure timely action within 3 working days per Anti-Red Tape Act (RA 11032).</span>
        </div>
    </div>

    <div class="kpi-card theme-info">
        <div class="kpi-header">
            <span class="kpi-pill">AUDIT RULE</span>
            <div class="kpi-icon-box"><i class="bi bi-check-circle"></i></div>
        </div>
        <div class="kpi-label">Approved Leaves</div>
        <div class="kpi-value">{{ number_format($metrics['approved']) }}</div>
        <div class="kpi-sub">{{ $metrics['approved_pct'] }}%</div>
        <div class="kpi-divider"></div>
        <div class="kpi-footer">
            <i class="bi bi-info-circle"></i>
            <span>Approved applications require valid attachments for post-audit.</span>
        </div>
    </div>
</div>

@if(session('success'))
    <div style="background:#f0fdf4;color:#166534;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:13px;">{{ session('success') }}</div>
@endif
@if($errors->has('leave'))
    <div style="background:#fef2f2;color:#991b1b;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:13px;">{{ $errors->first('leave') }}</div>
@endif

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;overflow-x:auto;">
    <table class="lv-table">
        <thead>
            <tr><th>Employee</th><th>Type</th><th>Dates</th><th>Days</th><th>Filed</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
            @forelse($applications as $app)
                <tr>
                    <td>{{ $app->plantillaRecord->last_name ?? '' }}, {{ $app->plantillaRecord->first_name ?? '' }}</td>
                    <td>{{ $app->leaveType->code ?? '' }}</td>
                    <td>{{ $app->date_from->format('M d, Y') }} - {{ $app->date_to->format('M d, Y') }}</td>
                    <td>{{ $app->days_requested }}</td>
                    <td>{{ $app->filed_at->format('M d, Y') }}</td>
                    <td><span class="lv-status status-{{ $app->status }}">{{ ucfirst($app->status) }}</span></td>
                    <td>
                        @if($app->status === 'pending')
                            <form method="POST" action="{{ route('leave.approve', $app) }}" style="display:inline;">
                                @csrf
                                <button class="btn btn-sm btn-outline-success">Approve</button>
                            </form>
                            <button type="button" class="btn btn-sm btn-outline-danger ms-1"
                                data-bs-toggle="modal" data-bs-target="#disapproveModal"
                                data-app-id="{{ $app->id }}"
                                data-app-action="{{ route('leave.disapprove', $app) }}"
                                data-app-name="{{ $app->plantillaRecord->last_name ?? '' }}, {{ $app->plantillaRecord->first_name ?? '' }}">
                                Disapprove
                            </button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center;color:#9ca3af;">No leave applications yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $applications->links() }}
</div>

{{-- File Leave Application modal --}}
<div class="modal fade" id="fileLeaveModal" tabindex="-1" aria-labelledby="fileLeaveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;">
            <div class="modal-header" style="background: linear-gradient(135deg,#1e3a5f 0%,#1a5276 55%,#0f4c75 100%); color:#fff; border-top-left-radius:16px; border-top-right-radius:16px; border-bottom:none;">
                <h5 class="modal-title fw-bold" id="fileLeaveModalLabel"><i class="bi bi-calendar-plus-fill me-2"></i>File Leave Application</h5>
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('leave.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-medium small text-muted mb-1">Employee <span class="text-danger">*</span></label>
                            <input type="text" id="fileLeaveEmployeeSearch" class="form-control" list="fileLeaveEmployeeList" placeholder="Type a name to search..." autocomplete="off" style="border-radius:8px;" required>
                            <datalist id="fileLeaveEmployeeList">
                                @foreach($employees as $emp)
                                    <option data-id="{{ $emp->id }}" value="{{ $emp->last_name }}, {{ $emp->first_name }} — {{ $emp->position_title }}"></option>
                                @endforeach
                            </datalist>
                            <input type="hidden" name="plantilla_record_id" id="fileLeaveEmployeeId">
                            <div id="fileLeaveBalances" class="mt-2" style="display:none; font-size:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:8px 12px;"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium small text-muted mb-1">Leave Type <span class="text-danger">*</span></label>
                            <select name="leave_type_id" class="form-select" style="border-radius:8px;" required>
                                <option value="" disabled selected>Select leave type...</option>
                                @foreach($leaveTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->code }} — {{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium small text-muted mb-1">Days Requested <span class="text-danger">*</span></label>
                            <input type="number" name="days_requested" step="0.5" min="0.5" class="form-control" style="border-radius:8px;" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium small text-muted mb-1">Date From <span class="text-danger">*</span></label>
                            <input type="date" name="date_from" class="form-control" style="border-radius:8px;" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium small text-muted mb-1">Date To <span class="text-danger">*</span></label>
                            <input type="date" name="date_to" class="form-control" style="border-radius:8px;" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium small text-muted mb-1">Date Filed <span class="text-danger">*</span></label>
                            <input type="date" name="filed_at" value="{{ now()->toDateString() }}" class="form-control" style="border-radius:8px;" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-medium small text-muted mb-1">Reason (Optional)</label>
                            <textarea name="reason" class="form-control" style="border-radius:8px; resize:none;" rows="2" maxlength="1000"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0" style="border-bottom-left-radius:16px; border-bottom-right-radius:16px;">
                    <button type="button" class="btn btn-light fw-medium px-4" data-bs-dismiss="modal" style="border-radius:8px; border:1px solid #e5e7eb;">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4" style="border-radius:8px;"><i class="bi bi-send-fill me-1"></i> File Application</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Disapprove modal --}}
<div class="modal fade" id="disapproveModal" tabindex="-1" aria-labelledby="disapproveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;">
            <div class="modal-header" style="background:#991b1b; color:#fff; border-top-left-radius:16px; border-top-right-radius:16px; border-bottom:none;">
                <h5 class="modal-title fw-bold" id="disapproveModalLabel"><i class="bi bi-x-circle-fill me-2"></i>Disapprove Leave Application</h5>
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="disapproveForm">
                @csrf
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Disapproving leave application for <strong id="disapproveEmployeeName">—</strong>. This reason will be recorded on the application.</p>
                    <label class="form-label fw-medium small text-muted mb-1">Reason for Disapproval <span class="text-danger">*</span></label>
                    <textarea name="disapproval_reason" class="form-control" style="border-radius:8px; resize:none;" rows="3" maxlength="500" required></textarea>
                </div>
                <div class="modal-footer bg-light border-top-0" style="border-bottom-left-radius:16px; border-bottom-right-radius:16px;">
                    <button type="button" class="btn btn-light fw-medium px-4" data-bs-dismiss="modal" style="border-radius:8px; border:1px solid #e5e7eb;">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-bold px-4" style="border-radius:8px;"><i class="bi bi-x-lg me-1"></i> Disapprove</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Employee search -> hidden ID + dynamic balance lookup
        const search = document.getElementById('fileLeaveEmployeeSearch');
        const hiddenId = document.getElementById('fileLeaveEmployeeId');
        const balancesBox = document.getElementById('fileLeaveBalances');
        const datalistOptions = document.querySelectorAll('#fileLeaveEmployeeList option');

        if (search) {
            search.addEventListener('input', function () {
                let matchedId = '';
                datalistOptions.forEach(opt => {
                    if (opt.value === search.value) matchedId = opt.getAttribute('data-id');
                });
                hiddenId.value = matchedId;

                if (!matchedId) {
                    balancesBox.style.display = 'none';
                    return;
                }

                fetch('{{ route("leave.balances", ["plantilla" => "__ID__"]) }}'.replace('__ID__', matchedId))
                    .then(r => r.json())
                    .then(data => {
                        if (data.excluded) {
                            balancesBox.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i>' + data.message + '</span>';
                            balancesBox.style.display = 'block';
                            return;
                        }
                        if (!data.balances || !data.balances.length) {
                            balancesBox.innerHTML = '<span class="text-muted">No leave balance records found for this employee yet.</span>';
                            balancesBox.style.display = 'block';
                            return;
                        }
                        balancesBox.innerHTML = '<strong>Leave Balances:</strong> ' + data.balances.map(b =>
                            `${b.code}: ${b.remaining} of ${b.earned} day(s) remaining`
                        ).join(' &nbsp;|&nbsp; ');
                        balancesBox.style.display = 'block';
                    })
                    .catch(() => { balancesBox.style.display = 'none'; });
            });
        }

        // Disapprove modal — populate form action + employee name from the triggering button
        const disapproveModal = document.getElementById('disapproveModal');
        if (disapproveModal) {
            disapproveModal.addEventListener('show.bs.modal', function (event) {
                const btn = event.relatedTarget;
                document.getElementById('disapproveForm').action = btn.getAttribute('data-app-action');
                document.getElementById('disapproveEmployeeName').textContent = btn.getAttribute('data-app-name');
            });
        }
    });
</script>
</x-dashboard-app>
