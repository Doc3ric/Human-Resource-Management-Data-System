<x-dashboard-app>
<style>
.sa-hero {
    background: linear-gradient(135deg, #4c1d95 0%, #6d28d9 55%, #7c3aed 100%);
    border-radius: 14px; padding: 28px 32px;
    position: relative; overflow: hidden; margin-bottom: 20px;
}
.sa-hero::before {
    content:''; position:absolute; inset:0;
    background-image: radial-gradient(circle, rgba(255,255,255,.07) 1px, transparent 1px);
    background-size: 22px 22px;
}
.sa-hero-inner { position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap; }
.sa-hero h1 { color:#fff; font-size:24px; font-weight:800; margin:0; line-height:1.2; }
.sa-hero p  { color:rgba(255,255,255,.65); font-size:13px; margin:5px 0 0; }
.sa-hero-btn {
    display:inline-flex;align-items:center;gap:6px;
    background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.3);
    color:#fff;padding:9px 18px;border-radius:10px;
    font-size:13px;font-weight:600;text-decoration:none;
    transition:background .2s;
}
.sa-hero-btn:hover { background:rgba(255,255,255,.28);color:#fff; }

.sa-bucket-bar { display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap; }
.sa-bucket {
    display:inline-flex; align-items:center; gap:8px;
    padding:10px 16px; border-radius:10px; font-size:13px; font-weight:700;
    text-decoration:none; border:1px solid #e5e7eb; color:#6b7280; background:#fff;
    transition:all .15s;
}
.sa-bucket:hover { color:#374151; background:#f9fafb; }
.sa-bucket.active { color:#fff; border-color:transparent; }
.sa-bucket.active.b-misaligned { background:linear-gradient(135deg,#dc2626,#b91c1c); }
.sa-bucket.active.b-unresolvable { background:linear-gradient(135deg,#64748b,#475569); }
.sa-bucket.active.b-aligned { background:linear-gradient(135deg,#16a34a,#15803d); }
.sa-bucket .count { background:rgba(0,0,0,.15); padding:1px 8px; border-radius:99px; font-size:11px; }
.sa-bucket:not(.active) .count { background:#f1f5f9; color:#475569; }

.sa-filter-bar {
    display:flex; gap:10px; flex-wrap:wrap; align-items:center;
    background:var(--color-surface,#fff); border:1px solid var(--color-border,#e5e7eb);
    border-radius:12px; padding:12px 16px; margin-bottom:16px;
}
.sa-filter-bar input, .sa-filter-bar select {
    border:1px solid #e2e8f0; border-radius:8px; padding:8px 12px; font-size:13px;
    background:var(--color-surface,#fff); color:var(--color-text-primary,#1e293b);
}

.sa-bulk-bar {
    display:none; background:#f5f3ff; border:1px solid #ddd6fe; border-radius:10px;
    padding:12px 16px; margin-bottom:16px; align-items:center; justify-content:space-between;
}

.sa-table-wrap {
    background: var(--color-surface, #fff);border: 1px solid var(--color-border, #e5e7eb);border-radius:14px;
    overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.04);
}
.sa-table { width:100%;border-collapse:separate;border-spacing:0; }
.sa-table thead tr { background:linear-gradient(135deg,#4c1d95 0%,#7c3aed 100%); }
.sa-table th {
    text-align:left;font-size:10px;font-weight:700;
    text-transform:uppercase;letter-spacing:.8px;
    color:rgba(255,255,255,.72);padding:12px 14px;white-space:nowrap;
    border-bottom:2px solid rgba(255,255,255,.1);
}
.sa-table th.tc { text-align:center; }
.sa-table tbody tr { transition:background .12s; }
.sa-table tbody tr:nth-child(even) td { background:#faf5ff; }
.sa-table tbody tr:hover td { background:#f3e8ff !important; }
.sa-table td {
    padding:10px 14px;vertical-align:middle;
    border-bottom:1px solid #f1f5f9;font-size:13px;color:#374151;
}
.sa-table td.tc { text-align:center; }
.sa-table tbody tr:last-child td { border-bottom:0; }
.sa-emp-name { font-weight:700;color:#0f172a;font-size:13px;letter-spacing:-.1px; }
.sa-emp-unit { font-size:11px;color:#94a3b8;margin-top:2px; }
.sa-sg-badge {
    display:inline-flex;align-items:center;justify-content:center;
    background:linear-gradient(135deg,#4c1d95,#7c3aed);
    color:#fff;font-size:11px;font-weight:800;
    width:30px;height:30px;border-radius:8px;
}
.sa-rate { font-family:ui-monospace,monospace; font-size:13px; }
.sa-type-chip {
    display:inline-block; font-size:10px; font-weight:700; text-transform:uppercase;
    letter-spacing:.4px; padding:2px 7px; border-radius:6px;
    background:#f1f5f9; color:#475569; border:1px solid #e2e8f0;
}
.sa-status-chip {
    display:inline-flex; align-items:center; gap:4px; white-space:nowrap;
    font-size:11px; font-weight:800; padding:4px 10px; border-radius:99px;
}
.sa-status-aligned { background:#dcfce7; color:#15803d; }
.sa-status-underpaid { background:#fee2e2; color:#b91c1c; }
.sa-status-overpaid { background:#ffedd5; color:#c2410c; }
.sa-status-unresolvable { background:#f1f5f9; color:#475569; }
.sa-reconcile-btn {
    display:inline-flex;align-items:center;gap:5px;white-space:nowrap;
    background:linear-gradient(135deg,#7c3aed,#6d28d9);color:#fff;
    padding:6px 12px;border-radius:8px;font-size:12px;font-weight:700;
    border:none;cursor:pointer;box-shadow:0 2px 8px rgba(124,58,237,.3);
}
.sa-empty { text-align:center;padding:60px 20px; }
.sa-empty-icon {
    width:64px;height:64px;margin:0 auto 16px;
    background:linear-gradient(135deg,#ede9fe,#ddd6fe);
    border-radius:50%;display:flex;align-items:center;justify-content:center;
    font-size:28px;color:#7c3aed;
}
</style>

{{-- HERO --}}
<div class="sa-hero">
    <div class="sa-hero-inner">
        <div>
            <h1><i class="bi bi-shuffle me-2"></i>Salary Alignment</h1>
            <p>Reconcile Permanent/Co-Terminous pay against the active Salary Schedule (Grade + Step)</p>
        </div>
        <a href="{{ route('step-increment.hub') }}" class="sa-hero-btn">
            <i class="bi bi-arrow-left"></i> Back to Hub
        </a>
    </div>
</div>

{{-- Flash Messages --}}
@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:12px 18px;border-radius:10px;margin-bottom:16px;font-size:13px;font-weight:600;">
    <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 18px;border-radius:10px;margin-bottom:16px;font-size:13px;font-weight:600;">
    <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('error') }}
</div>
@endif
@if(session('info'))
<div style="background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;padding:12px 18px;border-radius:10px;margin-bottom:16px;font-size:13px;font-weight:600;">
    <i class="bi bi-info-circle-fill me-1"></i> {{ session('info') }}
</div>
@endif

{{-- Active Schedule banner --}}
<div style="background:var(--color-surface,#fff);border:1px solid var(--color-border,#e5e7eb);border-radius:12px;padding:14px 18px;margin-bottom:16px;display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
    <span style="display:inline-flex;align-items:center;gap:6px;background:#f5f3ff;border:1px solid #ddd6fe;color:#6d28d9;padding:5px 12px;border-radius:8px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;">
        <i class="bi bi-calendar-check-fill"></i> Active Schedule
    </span>
    @if($activeSchedule)
        <span style="font-size:13px;font-weight:700;color:#0f172a;">{{ $activeSchedule->name }}</span>
        @if($activeSchedule->law_name)
            <span style="font-size:12px;color:#64748b;">{{ $activeSchedule->law_name }}</span>
        @endif
        @if($activeSchedule->effective_date)
            <span style="font-size:12px;color:#94a3b8;">Effective {{ $activeSchedule->effective_date->format('M d, Y') }}</span>
        @endif
    @else
        <span style="font-size:13px;color:#b91c1c;font-weight:700;">No active Salary Schedule set — all records will read as Unresolvable.</span>
    @endif
</div>

{{-- Bucket toggle --}}
<div class="sa-bucket-bar">
    <a href="{{ route('step-increment.salary-alignment', array_filter(['status'=>'misaligned','search'=>$search,'office'=>$officeFilter])) }}"
       class="sa-bucket {{ $statusFilter === 'misaligned' ? 'active b-misaligned' : '' }}">
        <i class="bi bi-exclamation-triangle-fill"></i> Misaligned <span class="count">{{ $counts['misaligned'] }}</span>
    </a>
    <a href="{{ route('step-increment.salary-alignment', array_filter(['status'=>'unresolvable','search'=>$search,'office'=>$officeFilter])) }}"
       class="sa-bucket {{ $statusFilter === 'unresolvable' ? 'active b-unresolvable' : '' }}">
        <i class="bi bi-question-circle-fill"></i> Unresolvable <span class="count">{{ $counts['unresolvable'] }}</span>
    </a>
    <a href="{{ route('step-increment.salary-alignment', array_filter(['status'=>'aligned','search'=>$search,'office'=>$officeFilter])) }}"
       class="sa-bucket {{ $statusFilter === 'aligned' ? 'active b-aligned' : '' }}">
        <i class="bi bi-check-circle-fill"></i> Aligned <span class="count">{{ $counts['aligned'] }}</span>
    </a>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('step-increment.salary-alignment') }}" class="sa-filter-bar">
    <input type="hidden" name="status" value="{{ $statusFilter }}">
    <input type="text" name="search" value="{{ $search }}" placeholder="Search name or item no..." style="flex:1;min-width:200px;">
    <select name="office">
        <option value="">All Offices</option>
        @foreach($offices as $office)
            <option value="{{ $office }}" {{ $officeFilter === $office ? 'selected' : '' }}>{{ $office }}</option>
        @endforeach
    </select>
    <button type="submit" style="background:#7c3aed;color:#fff;border:none;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;">
        <i class="bi bi-search"></i> Filter
    </button>
    @if($search || $officeFilter)
        <a href="{{ route('step-increment.salary-alignment', ['status'=>$statusFilter]) }}" style="font-size:12px;color:#64748b;text-decoration:none;">Reset</a>
    @endif
</form>

{{-- Bulk action bar (only meaningful on the Misaligned bucket) --}}
@if($statusFilter === 'misaligned')
<div id="sa-bulk-bar" class="sa-bulk-bar">
    <div style="display:flex;align-items:center;gap:12px;">
        <span style="background:#7c3aed;color:#fff;border-radius:9999px;padding:2px 10px;font-size:12px;font-weight:700;" id="sa-bulk-count">0</span>
        <span style="font-size:13px;font-weight:600;color:#334155;">records selected</span>
    </div>
    <form id="sa-bulk-form" method="POST" action="{{ route('step-increment.reconcile-salary.selected') }}" style="margin:0;">
        @csrf
        <div id="sa-bulk-ids-container"></div>
        <button type="button" onclick="confirmBulkReconcile()" class="sa-reconcile-btn">
            <i class="bi bi-shuffle"></i> Reconcile Selected
        </button>
    </form>
</div>
@endif

<div class="sa-table-wrap">
    <div class="overflow-x-auto">
        <table class="sa-table">
            <thead>
                <tr>
                    @if($statusFilter === 'misaligned')
                        <th class="tc" style="width:36px;"><input type="checkbox" id="sa-select-all" style="cursor:pointer;width:14px;height:14px;"></th>
                    @endif
                    <th>Employee</th>
                    <th>Office</th>
                    <th class="tc">Grade</th>
                    <th class="tc">Step</th>
                    <th class="tc">Salary Type</th>
                    <th class="tc">Current Rate</th>
                    <th class="tc">Expected Rate</th>
                    <th class="tc">Variance</th>
                    <th class="tc">Status</th>
                    <th class="tc">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $r)
                <tr>
                    @if($statusFilter === 'misaligned')
                        <td class="tc"><input type="checkbox" class="sa-record-checkbox" value="{{ $r->id }}" style="cursor:pointer;width:14px;height:14px;"></td>
                    @endif
                    <td>
                        <div class="sa-emp-name">{{ $r->full_name }}</div>
                        <div class="sa-emp-unit">{{ $r->position_title }} @if($r->item_no_new) &middot; {{ $r->item_no_new }} @endif</div>
                    </td>
                    <td style="font-size:12px;color:#475569;">{{ $r->office_department }}</td>
                    <td class="tc"><span class="sa-sg-badge">{{ $r->salary_grade ?? '—' }}</span></td>
                    <td class="tc">{{ $r->step ?? '—' }}</td>
                    <td class="tc"><span class="sa-type-chip">{{ $r->salary_type ?: 'blank' }}</span></td>
                    <td class="tc"><span class="sa-rate">{{ $r->current_monthly_rate !== null ? '₱'.number_format($r->current_monthly_rate, 2) : '—' }}</span></td>
                    <td class="tc"><span class="sa-rate">{{ $r->expected_monthly_rate !== null ? '₱'.number_format($r->expected_monthly_rate, 2) : '—' }}</span></td>
                    <td class="tc">
                        @if($r->salary_variance_amount !== null)
                            <span class="sa-rate" style="font-weight:700;color:{{ $r->salary_variance_amount > 0 ? '#c2410c' : '#b91c1c' }};">
                                {{ $r->salary_variance_amount > 0 ? '+' : '' }}₱{{ number_format($r->salary_variance_amount, 2) }}
                            </span>
                            <div style="font-size:10px;color:#94a3b8;">{{ $r->salary_variance_percent > 0 ? '+' : '' }}{{ number_format($r->salary_variance_percent, 1) }}%</div>
                        @else
                            <span style="color:#cbd5e1;">—</span>
                        @endif
                    </td>
                    <td class="tc">
                        @php $status = $r->salary_alignment_status; @endphp
                        <span class="sa-status-chip sa-status-{{ $status }}">
                            @if($status === 'aligned') <i class="bi bi-check-circle-fill"></i> Aligned
                            @elseif($status === 'underpaid') <i class="bi bi-arrow-down-circle-fill"></i> Underpaid
                            @elseif($status === 'overpaid') <i class="bi bi-arrow-up-circle-fill"></i> Overpaid
                            @else <i class="bi bi-question-circle-fill"></i> Unresolvable
                            @endif
                        </span>
                    </td>
                    <td class="tc">
                        @if($status === 'underpaid' || $status === 'overpaid')
                            <button type="button"
                                onclick="confirmReconcile({{ $r->id }}, '{{ addslashes($r->full_name) }}', '{{ number_format($r->expected_monthly_rate, 2) }}')"
                                class="sa-reconcile-btn">
                                <i class="bi bi-shuffle"></i> Reconcile
                            </button>
                            <form id="sa-form-{{ $r->id }}" method="POST" action="{{ route('step-increment.reconcile-salary', $r) }}" style="display:none;">
                                @csrf
                            </form>
                        @else
                            <span style="color:#cbd5e1;font-size:12px;">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10">
                        <div class="sa-empty">
                            <div class="sa-empty-icon"><i class="bi bi-shuffle"></i></div>
                            <div style="font-size:16px;font-weight:700;color:#4c1d95;margin-bottom:6px;">No records in this bucket</div>
                            <div style="font-size:13px;color:#94a3b8;">
                                @if($statusFilter === 'misaligned') Every Permanent/Co-Terminous employee's pay matches the active schedule.
                                @elseif($statusFilter === 'unresolvable') No records are missing Grade/Step/Salary Type data.
                                @else No aligned records match this filter.
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($records->hasPages())
    <div style="padding:14px 20px;border-top:1px solid #f1f5f9;">
        {{ $records->links() }}
    </div>
    @endif
</div>

<script>
function confirmReconcile(id, name, expectedRate) {
    Swal.fire({
        title: 'Reconcile Salary?',
        html: `<div style="text-align:center;margin-top:10px;">
                <div style="background:#f5f3ff;border:1px solid #ddd6fe;border-radius:12px;padding:16px 20px;margin-bottom:12px;">
                    <div style="font-size:15px;font-weight:800;color:#1e293b;margin-bottom:4px;">${name}</div>
                    <div style="font-size:13px;color:#64748b;">New monthly rate: <strong>₱${expectedRate}</strong></div>
                </div>
                <div style="font-size:12px;color:#64748b;">This updates the employee's recorded pay to match the active Salary Schedule and logs an SSL_ADJUSTMENT history entry.</div>
              </div>`,
        icon: 'question',
        iconColor: '#7c3aed',
        showCancelButton: true,
        confirmButtonColor: '#7c3aed',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="bi bi-shuffle"></i> Yes, Reconcile',
        cancelButtonText: 'Cancel',
        width: 460
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('sa-form-' + id).submit();
        }
    });
}

function confirmBulkReconcile() {
    const count = document.getElementById('sa-bulk-count').textContent;
    Swal.fire({
        title: `Reconcile ${count} record(s)?`,
        html: 'Each selected employee\'s pay will be updated to match the active Salary Schedule.<br>An SSL_ADJUSTMENT history entry is logged for every change.',
        icon: 'question',
        iconColor: '#7c3aed',
        showCancelButton: true,
        confirmButtonColor: '#7c3aed',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, reconcile selected'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('sa-bulk-form').submit();
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('sa-select-all');
    const checkboxes = document.querySelectorAll('.sa-record-checkbox');
    const bulkBar = document.getElementById('sa-bulk-bar');
    const bulkCount = document.getElementById('sa-bulk-count');
    const bulkIdsContainer = document.getElementById('sa-bulk-ids-container');
    if (!selectAll || !bulkBar) return;

    function updateBulkBar() {
        const checked = document.querySelectorAll('.sa-record-checkbox:checked');
        bulkCount.textContent = checked.length;
        bulkBar.style.display = checked.length > 0 ? 'flex' : 'none';
        bulkIdsContainer.innerHTML = '';
        checked.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = cb.value;
            bulkIdsContainer.appendChild(input);
        });
    }

    selectAll.addEventListener('change', function () {
        checkboxes.forEach(cb => { cb.checked = selectAll.checked; });
        updateBulkBar();
    });
    checkboxes.forEach(cb => {
        cb.addEventListener('change', function () {
            selectAll.checked = document.querySelectorAll('.sa-record-checkbox:checked').length === checkboxes.length && checkboxes.length > 0;
            updateBulkBar();
        });
    });
});
</script>
</x-dashboard-app>
