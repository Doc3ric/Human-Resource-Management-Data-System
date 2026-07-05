<x-dashboard-app>
<style>
/* ── Hero ── */
.arc-hero {
    background: linear-gradient(135deg,#1e1b4b 0%,#312e81 60%,#4338ca 100%);
    border-radius:14px; padding:24px 28px;
    position:relative; overflow:hidden; margin-bottom:20px;
}
.arc-hero::before {
    content:''; position:absolute; inset:0;
    background-image: radial-gradient(circle, rgba(255,255,255,.06) 1px, transparent 1px);
    background-size:22px 22px;
}
.arc-hero-inner { position:relative;z-index:1; display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; }
.arc-hero h1 { color:#fff; font-size:22px; font-weight:800; margin:0; }
.arc-hero p  { color:rgba(255,255,255,.6); font-size:12px; margin:4px 0 0; }
.arc-hero-badge {
    display:inline-flex; align-items:center; gap:6px;
    background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.25);
    color:#fff; padding:6px 14px; border-radius:8px; font-size:12px; font-weight:700;
}

/* ── Tab switcher ── */
.arc-tabs { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:20px; }
.arc-tab {
    display:inline-flex; align-items:center; gap:7px;
    padding:9px 16px; border-radius:10px; font-size:12px; font-weight:700;
    border:1px solid #e2e8f0; background:#fff; color:#475569;
    text-decoration:none; transition:all .15s; position:relative;
}
.arc-tab:hover { background:#f1f5f9; }
.arc-tab.active { background:#4338ca; color:#fff; border-color:#4338ca; }
.arc-tab .cnt {
    background:rgba(255,255,255,.25); color:#fff;
    padding:1px 7px; border-radius:99px; font-size:10px; font-weight:800;
}
.arc-tab:not(.active) .cnt { background:#f1f5f9; color:#6b7280; }
.arc-tab.active .cnt { background:rgba(255,255,255,.3); }

/* ── Table ── */
.table-wrap { background:#fff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.05); }
.arc-table  { width:100%; border-collapse:separate; border-spacing:0; }
.arc-table thead tr { background:linear-gradient(135deg,#1e1b4b 0%,#312e81 100%); }
.arc-table th { padding:10px 14px; text-align:left; font-size:9.5px; font-weight:800; text-transform:uppercase; letter-spacing:.8px; color:rgba(255,255,255,.75); white-space:nowrap; }
.arc-table td { padding:10px 14px; font-size:12px; color:#374151; border-bottom:1px solid #f1f5f9; white-space:nowrap; }
.arc-table tbody tr:last-child td { border-bottom:0; }
.arc-table tbody tr:hover td { background:#f5f3ff; }

/* Buttons */
.btn-restore {
    display:inline-flex; align-items:center; gap:5px;
    padding:5px 12px; border-radius:7px; font-size:11px; font-weight:700;
    background:#d1fae5; color:#065f46; border:1px solid #a7f3d0;
    cursor:pointer; text-decoration:none; transition:all .12s;
}
.btn-restore:hover { background:#059669; color:#fff; border-color:#059669; }
.btn-destroy {
    display:inline-flex; align-items:center; gap:5px;
    padding:5px 12px; border-radius:7px; font-size:11px; font-weight:700;
    background:#fee2e2; color:#991b1b; border:1px solid #fecaca;
    cursor:pointer; border:none; transition:all .12s;
}
.btn-destroy:hover { background:#dc2626; color:#fff; }

/* Flash */
.flash-success { background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; border-radius:10px; padding:11px 16px; margin-bottom:14px; font-size:13px; font-weight:600; display:flex; align-items:center; gap:8px; }
.flash-error   { background:#fef2f2; border:1px solid #fecaca; color:#991b1b; border-radius:10px; padding:11px 16px; margin-bottom:14px; font-size:13px; font-weight:600; display:flex; align-items:center; gap:8px; }

/* Empty state */
.empty-state { padding:60px 24px; text-align:center; color:#94a3b8; }
.empty-state i { font-size:48px; display:block; margin-bottom:12px; color:#c7d2fe; }

/* Footer */
.table-footer { padding:12px 18px; border-top:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
.table-count { font-size:12px; color:#6b7280; }
</style>

{{-- Hero --}}
<div class="arc-hero">
    <div class="arc-hero-inner">
        <div>
            <h1><i class="bi bi-archive-fill me-2"></i>Archives</h1>
            <p>Archived records — soft-deleted data, safely stored and restorable</p>
        </div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            @if(auth()->user()->isSuperAdmin() && Route::has('employee-codes.generate-all'))
            <button type="button"
                onclick="openArchiveCodeModal()"
                style="display:inline-flex;align-items:center;gap:7px;background:rgba(16,185,129,.2);border:1px solid rgba(16,185,129,.5);color:#6ee7b7;padding:7px 14px;border-radius:9px;font-size:12px;font-weight:700;cursor:pointer;transition:all .15s;"
                onmouseover="this.style.background='rgba(16,185,129,.35)'"
                onmouseout="this.style.background='rgba(16,185,129,.2)'">
                <i class="bi bi-qr-code"></i> Generate Codes
            </button>
            @endif
            <span class="arc-hero-badge"><i class="bi bi-shield-fill-check"></i> Recycle Bin</span>
        </div>
    </div>
</div>

{{-- Flash messages --}}
@if(session('success'))
<div class="flash-success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
<div class="flash-error"><i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}</div>
@endif

{{-- Tabs --}}
<div class="arc-tabs">
    @php
    $tabs = [
        ['key'=>'plantilla',    'label'=>'Plantilla Records', 'icon'=>'bi-table'],
        ['key'=>'employees',    'label'=>'Employees',         'icon'=>'bi-person-fill'],
        ['key'=>'positions',    'label'=>'Positions',         'icon'=>'bi-briefcase-fill'],
        ['key'=>'org_units',    'label'=>'Org. Units',        'icon'=>'bi-diagram-3-fill'],
        ['key'=>'users',        'label'=>'Users',             'icon'=>'bi-people-fill'],
        ['key'=>'appointments', 'label'=>'Appointments',      'icon'=>'bi-calendar2-check-fill'],
        ['key'=>'salary_schedules', 'label'=>'Salary Schedules', 'icon'=>'bi-cash-coin'],
    ];
    @endphp
    @foreach($tabs as $tab)
    <a href="{{ route('archives.index', ['type'=>$tab['key']]) }}"
       class="arc-tab {{ $type === $tab['key'] ? 'active' : '' }}">
        <i class="{{ $tab['icon'] }}"></i>
        {{ $tab['label'] }}
        <span class="cnt">{{ number_format($counts[$tab['key']]) }}</span>
    </a>
    @endforeach
</div>

{{-- Table & Actions --}}
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; flex-wrap:wrap; gap:10px;">
    <form method="GET" action="{{ route('archives.index') }}" style="display:flex; gap:8px; width:100%; max-width:400px;">
        <input type="hidden" name="type" value="{{ $type }}">
        <div style="position:relative; flex:1;">
            <i class="bi bi-search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#9ca3af; font-size:13px;"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search {{ str_replace('_', ' ', $type) }}..." style="width:100%; padding:8px 12px 8px 32px; border:1px solid #e5e7eb; border-radius:8px; font-size:13px; outline:none; transition:border-color .15s;">
        </div>
        <button type="submit" style="background:#4338ca; color:#fff; border:none; padding:8px 16px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">Search</button>
        @if(request('search'))
            <a href="{{ route('archives.index', ['type'=>$type]) }}" style="background:#f1f5f9; color:#475569; padding:8px 16px; border-radius:8px; font-size:13px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center;">Clear</a>
        @endif
    </form>
</div>

{{-- Table --}}
<div class="table-wrap">
    <div style="overflow-x:auto;">
        <table class="arc-table">
            <thead>
                <tr>
                    {{-- Dynamic columns per type --}}
                    @if($type === 'plantilla')
                        <th>Item No.</th><th>Position Title</th><th>Org. Unit</th>
                        <th>Name</th><th>Status</th><th>Archived On</th><th>Actions</th>
                    @elseif($type === 'employees')
                        <th>Employee #</th><th>Name</th><th>Email</th>
                        <th>Status</th><th>Archived On</th><th>Actions</th>
                    @elseif($type === 'positions')
                        <th>Code</th><th>Title</th><th>Org. Unit</th>
                        <th>SG</th><th>Archived On</th><th>Actions</th>
                    @elseif($type === 'org_units')
                        <th>Code</th><th>Name</th><th>Status</th>
                        <th>Archived On</th><th>Actions</th>
                    @elseif($type === 'users')
                        <th>Name</th><th>Email</th><th>Role</th>
                        <th>Archived On</th><th>Actions</th>
                    @elseif($type === 'appointments')
                        <th>Employee</th><th>Position</th><th>Type</th>
                        <th>Start</th><th>Archived On</th><th>Actions</th>
                    @elseif($type === 'salary_schedules')
                        <th>Schedule Name</th><th>Law Name</th><th>Effective Date</th>
                        <th>Archived On</th><th>Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                <tr>
                    {{-- Dynamic rows per type --}}
                    @if($type === 'plantilla')
                        <td style="font-family:monospace;font-size:11px;color:#1e40af;font-weight:700;">{{ $row->item_no_new }}</td>
                        <td style="font-weight:600;">{{ $row->position_title }}</td>
                        <td style="color:#6b7280;max-width:160px;overflow:hidden;text-overflow:ellipsis;" title="{{ $row->office_department }}">{{ Str::limit($row->office_department, 35) }}</td>
                        <td>
                            @if($row->is_vacant)
                                <span style="color:#dc2626;font-style:italic;font-size:10px;">VACANT</span>
                            @else
                                {{ strtoupper($row->last_name) }}, {{ $row->first_name }}
                            @endif
                        </td>
                        <td>
                            <span style="background:#e0e7ff;color:#3730a3;font-size:10px;font-weight:700;padding:2px 8px;border-radius:99px;">
                                {{ $row->employment_status ?: '—' }}
                            </span>
                        </td>

                    @elseif($type === 'employees')
                        <td style="font-family:monospace;font-size:11px;color:#1e40af;">{{ $row->employee_number }}</td>
                        <td style="font-weight:600;">{{ $row->full_name }}</td>
                        <td style="color:#6b7280;">{{ $row->email ?: '—' }}</td>
                        <td>
                            <span style="background:#fef3c7;color:#92400e;font-size:10px;font-weight:700;padding:2px 8px;border-radius:99px;">
                                {{ $row->status }}
                            </span>
                        </td>

                    @elseif($type === 'positions')
                        <td style="font-family:monospace;font-size:11px;color:#1e40af;font-weight:700;">{{ $row->code }}</td>
                        <td style="font-weight:600;">{{ $row->title }}</td>
                        <td style="color:#6b7280;">{{ $row->organizationalUnit?->name ?: '—' }}</td>
                        <td style="text-align:center;font-weight:700;">{{ $row->salary_grade ?: '—' }}</td>

                    @elseif($type === 'org_units')
                        <td style="font-family:monospace;font-size:11px;color:#1e40af;font-weight:700;">{{ $row->code }}</td>
                        <td style="font-weight:600;">{{ $row->name }}</td>
                        <td>
                            <span style="background:{{ $row->status==='Active' ? '#d1fae5' : '#fee2e2' }};color:{{ $row->status==='Active' ? '#065f46' : '#991b1b' }};font-size:10px;font-weight:700;padding:2px 8px;border-radius:99px;">
                                {{ $row->status }}
                            </span>
                        </td>

                    @elseif($type === 'users')
                        <td style="font-weight:600;">{{ $row->name }}</td>
                        <td style="color:#6b7280;">{{ $row->email }}</td>
                        <td>
                            <span style="background:#e0e7ff;color:#3730a3;font-size:10px;font-weight:700;padding:2px 8px;border-radius:99px;">
                                {{ $row->role_label }}
                            </span>
                        </td>

                    @elseif($type === 'appointments')
                        <td style="font-weight:600;">{{ $row->employee?->full_name ?: '—' }}</td>
                        <td style="color:#6b7280;">{{ $row->position?->title ?: '—' }}</td>
                        <td style="font-size:11px;">{{ $row->appointment_type ?: '—' }}</td>
                        <td style="font-size:11px;color:#6b7280;">{{ $row->appointment_start?->format('m/d/Y') ?: '—' }}</td>

                    @elseif($type === 'salary_schedules')
                        <td style="font-weight:600;">{{ $row->name }}</td>
                        <td style="color:#6b7280;">{{ $row->law_name ?: '—' }}</td>
                        <td style="text-align:center;font-size:11px;color:#6b7280;">{{ $row->effective_date ? $row->effective_date->format('M d, Y') : '—' }}</td>
                    @endif

                    {{-- Common: Archived On --}}
                    <td style="font-size:11px;color:#6b7280;">
                        <span title="{{ $row->deleted_at->format('Y-m-d H:i:s') }}">
                            {{ $row->deleted_at->diffForHumans() }}
                        </span>
                    </td>

                    {{-- Actions --}}
                    <td>
                        <div style="display:flex;gap:6px;align-items:center;">
                            {{-- Restore --}}
                            <form method="POST" action="{{ route('archives.restore', [$type, $row->id]) }}">
                                @csrf
                                <button type="submit" class="btn-restore">
                                    <i class="bi bi-arrow-counterclockwise"></i> Restore
                                </button>
                            </form>
                            {{-- Permanent Delete --}}
                            <button type="button" class="btn-destroy"
                                    onclick="confirmForceDelete('{{ $type }}','{{ $row->id }}')">
                                <i class="bi bi-trash3-fill"></i> Delete Forever
                            </button>
                            <form method="POST" id="force-del-{{ $type }}-{{ $row->id }}"
                                  action="{{ route('archives.force-delete', [$type, $row->id]) }}">
                                @csrf @method('DELETE')
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <i class="bi bi-archive"></i>
                            <p style="font-size:14px;font-weight:600;margin:0;">No archived records</p>
                            <p style="font-size:12px;margin:4px 0 0;">This category is clean — nothing has been archived yet.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($data->isNotEmpty())
    <div class="table-footer">
        <div class="table-count">
            Showing {{ $data->firstItem() }}–{{ $data->lastItem() }} of {{ number_format($data->total()) }} archived records
        </div>
        <div>{{ $data->links() }}</div>
    </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmForceDelete(type, id) {
    Swal.fire({
        title: 'Permanently Delete?',
        html: 'This action <strong>cannot be undone</strong>.<br>The record will be gone forever.',
        icon: 'warning',
        iconColor: '#dc2626',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete forever',
        cancelButtonText: 'Cancel',
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('force-del-' + type + '-' + id).submit();
        }
    });
}

function openArchiveCodeModal() {
    document.getElementById('archiveCodeModal').classList.add('active');
}
function closeArchiveCodeModal() {
    document.getElementById('archiveCodeModal').classList.remove('active');
}
document.addEventListener('DOMContentLoaded', function() {
    var m = document.getElementById('archiveCodeModal');
    if (m) m.addEventListener('click', function(e) {
        if (e.target === m) closeArchiveCodeModal();
    });
});
</script>

@if(auth()->user()->isSuperAdmin() && Route::has('employee-codes.generate-all'))
{{-- Generate Codes Confirmation Modal --}}
<div id="archiveCodeModal" class="logout-overlay">
    <div class="logout-modal-card">
        <button type="button" class="logout-modal-close" onclick="closeArchiveCodeModal()" title="Close">
            <i class="bi bi-x-lg"></i>
        </button>
        <div class="logout-modal-top" style="background:linear-gradient(135deg,#ecfdf5,#d1fae5);border-bottom-color:#a7f3d0;">
            <div class="logout-modal-icon-wrap" style="background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 8px 24px rgba(16,185,129,.3);animation:none;">
                <i class="bi bi-qr-code-scan"></i>
            </div>
            <h2 class="logout-modal-title">Generate Codes?</h2>
            <p class="logout-modal-subtitle">Bulk-generate unique employee codes for all records missing a code.</p>
        </div>
        <div class="logout-modal-body">
            <div class="logout-modal-info" style="background:#f0fdf4;border-color:#bbf7d0;color:#166534;">
                <i class="bi bi-info-circle-fill" style="color:#15803d;"></i>
                <span>Covers Plantilla, Casual, and Job Order tables. Any duplicates will be automatically resolved.</span>
            </div>
            <form id="archive-generate-codes-form" method="POST" action="{{ route('employee-codes.generate-all') }}">
                @csrf
                <input type="hidden" name="table" value="all">
                <div class="logout-modal-actions">
                    <button type="button" class="logout-btn-cancel" onclick="closeArchiveCodeModal()">
                        <i class="bi bi-arrow-left"></i> Cancel
                    </button>
                    <button type="submit" class="logout-btn-confirm" style="background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 4px 14px rgba(16,185,129,.3);">
                        <i class="bi bi-lightning-fill"></i> Run Now
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
</x-dashboard-app>
