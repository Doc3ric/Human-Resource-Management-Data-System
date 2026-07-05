<x-dashboard-app>
<style>
/* ── Hero ───────────────────────────────────────────────────────────── */
.ret-hero {
    background: linear-gradient(135deg, #0f2942 0%, #1e3a5f 100%);
    border-radius: 14px; padding: 24px 28px;
    position: relative; overflow: hidden; margin-bottom: 20px;
    display: flex; justify-content: space-between; align-items: center;
    flex-wrap: wrap; gap: 16px;
}
.ret-hero::before {
    content: ''; position: absolute; inset: 0;
    background-image: radial-gradient(circle, rgba(255,255,255,.07) 1px, transparent 1px);
    background-size: 22px 22px; pointer-events: none;
}
.ret-hero-left { position: relative; z-index: 1; }
.ret-hero h1 { color: #fff; font-size: 22px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 10px; }
.ret-hero p  { color: rgba(255,255,255,.6); font-size: 13px; margin: 4px 0 0; }

.ret-hero-stats {
    position: relative; z-index: 1; display: flex; gap: 12px;
}
.hero-stat {
    display: flex; flex-direction: column; background: rgba(255,255,255,.1);
    border: 1px solid rgba(255,255,255,.15); border-radius: 10px;
    padding: 10px 16px; min-width: 130px;
}
.hero-stat-label { color: rgba(255,255,255,.7); font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; }
.hero-stat-value { color: #fff; font-size: 22px; font-weight: 800; }
.hero-stat.alert-stat { border-color: rgba(239, 68, 68, 0.4); background: rgba(239, 68, 68, 0.15); }
.hero-stat.alert-stat .hero-stat-value { color: #fca5a5; }

/* ── Tabs ───────────────────────────────────────────────────────────── */
.tabs-wrap {
    display: flex; gap: 8px; margin-bottom: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 1px;
}
.tab-link {
    text-decoration: none; padding: 10px 20px; border-radius: 10px 10px 0 0;
    font-size: 14px; font-weight: 600; color: #64748b; background: transparent;
    transition: all .2s; position: relative; display: flex; align-items: center; gap: 8px;
}
.tab-link:hover { color: #0f172a; background: #f8fafc; }
.tab-link.active {
    color: #0f172a; background: #fff; border: 1px solid #e2e8f0; border-bottom: 1px solid #fff;
    margin-bottom: -1px; border-top: 3px solid #3b82f6; box-shadow: 0 -2px 10px rgba(0,0,0,.02);
}
.tab-badge {
    background: #e2e8f0; color: #475569; font-size: 11px; padding: 2px 7px; border-radius: 99px;
}
.tab-link.active .tab-badge.alert { background: #ef4444; color: #fff; }

/* ── Controls Bar ───────────────────────────────────────────────────── */
.controls-bar {
    display: flex; justify-content: space-between; align-items: center;
    background: #fff; padding: 12px 16px; border-radius: 10px; border: 1px solid #e2e8f0;
    margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.03);
}

.btn-process-all {
    background: linear-gradient(135deg, #ef4444, #b91c1c); color: #fff;
    border: none; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 700;
    cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 8px rgba(239,68,68,.3);
    transition: transform .15s, box-shadow .15s; text-decoration: none;
}
.btn-process-all:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(239,68,68,.4); color: #fff; }

/* ── Flash ──────────────────────────────────────────────────────────── */
.flash-msg {
    padding: 12px 16px; border-radius: 10px; font-size: 13px; font-weight: 600; margin-bottom: 16px;
    display: flex; align-items: center; gap: 8px;
}
.flash-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
.flash-error   { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
.flash-info    { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }

/* ── Table ──────────────────────────────────────────────────────────── */
.ret-table { width: 100%; border-collapse: separate; border-spacing: 0; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; }
.ret-table thead th {
    background: #f8fafc; padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase;
    color: #475569; text-align: left; border-bottom: 1px solid #e2e8f0; letter-spacing: .5px;
}
.ret-table tbody td {
    padding: 12px 16px; font-size: 13px; color: #334155; border-bottom: 1px solid #f1f5f9; vertical-align: middle;
}
.ret-table tbody tr:last-child td { border-bottom: 0; }
.ret-table tbody tr:hover td { background: #f8fafc; }

.emp-name { font-weight: 700; color: #0f172a; text-transform: uppercase; display: block; }
.emp-item { font-size: 11px; color: #64748b; font-family: monospace; }
.pos-title { font-weight: 600; }
.pos-office { font-size: 11px; color: #64748b; }

.age-badge {
    display: inline-block; padding: 3px 8px; border-radius: 6px; font-size: 12px; font-weight: 700;
}
.age-overdue { background: #fee2e2; color: #991b1b; }
.age-near    { background: #fef3c7; color: #92400e; }

.btn-vacate {
    background: #fff; border: 1px solid #e2e8f0; color: #ef4444; padding: 6px 12px; border-radius: 6px;
    font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;
    transition: all .2s;
}
.btn-vacate:hover { background: #fef2f2; border-color: #fecaca; }

.pagination-wrap { margin-top: 16px; padding: 0 4px; }
</style>

<div class="ret-hero">
    <div class="ret-hero-left">
        <h1><i class="bi bi-person-x"></i> Retirement Automation</h1>
        <p>Manage compulsory retirements (Age 65) for plantilla personnel.</p>
    </div>
    <div class="ret-hero-stats">
        <div class="hero-stat {{ $stats['overdue'] > 0 ? 'alert-stat' : '' }}">
            <span class="hero-stat-label">Overdue for Retirement</span>
            <span class="hero-stat-value">{{ number_format($stats['overdue']) }}</span>
        </div>
        <div class="hero-stat">
            <span class="hero-stat-label">Near Retirement (<1yr)</span>
            <span class="hero-stat-value">{{ number_format($stats['near']) }}</span>
        </div>
        <div class="hero-stat" style="opacity: 0.8;">
            <span class="hero-stat-label" style="line-height:1.2;">Processed<br>Retirements</span>
            <span class="hero-stat-value">{{ number_format($stats['total_processed']) }}</span>
        </div>
        <div class="hero-stat">
            <span class="hero-stat-label">Optional to Retire</span>
            <span class="hero-stat-value" style="color:#fcd34d;">{{ number_format($stats['optional']) }}</span>
        </div>
    </div>
</div>

{{-- Retirement stats with compliance analysis --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px;">
    <x-stat-card icon="bi-alarm-fill" color="red" label="Overdue Retirement" :value="$stats['overdue']"
        :alert="$stats['overdue'] > 0"
        compliance="RA 8291 / CSC"
        analysis="{{ $stats['overdue'] > 0 ? $stats['overdue'].' employee(s) have already reached age 65. Compulsory retirement MUST be processed immediately per RA 8291 (GSIS Act) and CSC MC 27, s.2001. Delay may result in unauthorized GSIS contributions.' : 'No overdue retirements. All age-65 cases have been processed per RA 8291 (GSIS Act).' }}" />

    <x-stat-card icon="bi-clock-history" color="orange" label="Near Retirement" :value="$stats['near']"
        sub="Will reach 65 within 12 months"
        compliance="RA 8291 / GSIS"
        analysis="Employees turning 65 within the year. Prepare retirement papers at least 6 months in advance per GSIS guidelines. Notify employee and HR of upcoming separation." />

    <x-stat-card icon="bi-person-check-fill" color="yellow" label="Optional (Age 60–64)" :value="$stats['optional']"
        sub="May opt to retire"
        compliance="RA 8291 §13-B"
        analysis="Employees aged 60–64 with at least 15 years of service may apply for optional retirement under RA 8291 §13-B. HR must counsel and compute projected benefits." />

    <x-stat-card icon="bi-check2-circle" color="green" label="Processed Retirements" :value="$stats['total_processed']"
        sub="Vacated retirement items"
        compliance="DBM / GSIS"
        analysis="Successfully vacated retirement positions. Vacant items must be reported to DBM within 30 days. GSIS separation benefits are computed based on years of service and final salary." />
</div>

@if(session('success'))
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'success', title: 'Success!', text: "{{ session('success') }}", confirmButtonColor: '#3b82f6', timer: 3000 });
            }
        });
    </script>
@endif
@if(session('error'))
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Error!', text: "{{ session('error') }}", confirmButtonColor: '#ef4444' });
            }
        });
    </script>
@endif
@if(session('info'))
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'info', title: 'Information', text: "{{ session('info') }}", confirmButtonColor: '#3b82f6' });
            }
        });
    </script>
@endif

<div class="tabs-wrap">
    <a href="{{ route('retirement.index', ['tab' => 'overdue']) }}" class="tab-link {{ $tab === 'overdue' ? 'active' : '' }}">
        <i class="bi bi-exclamation-triangle"></i> Overdue
        <span class="tab-badge {{ $stats['overdue'] > 0 ? 'alert' : '' }}">{{ $stats['overdue'] }}</span>
    </a>
    <a href="{{ route('retirement.index', ['tab' => 'near']) }}" class="tab-link {{ $tab === 'near' ? 'active' : '' }}">
        <i class="bi bi-hourglass-split"></i> Near Retirement
        <span class="tab-badge">{{ $stats['near'] }}</span>
    </a>
    <a href="{{ route('retirement.index', ['tab' => 'optional']) }}" class="tab-link {{ $tab === 'optional' ? 'active' : '' }}">
        <i class="bi bi-person-hearts" style="color:#d97706;"></i> Optional to Retire
        <span class="tab-badge" style="background:#fef3c7; color:#d97706;">{{ $stats['optional'] }}</span>
    </a>
    <a href="{{ route('retirement.history') }}" class="tab-link">
        <i class="bi bi-clock-history"></i> History
        <span class="tab-badge" style="background:#dbeafe; color:#1e40af;">{{ $stats['total_processed'] }}</span>
    </a>
</div>

@if($tab === 'overdue')
    <div class="controls-bar">
        <div style="font-size: 13px; color: #475569;">
            Showing employees who have reached <strong>age 65</strong> and are currently occupying a position.
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            <a href="{{ route('retirement.export.pdf', ['tab'=>'overdue']) }}" target="_blank" style="background:#dc2626;color:#fff;border:none;padding:8px 14px;border-radius:8px;font-size:13px;font-weight:700;display:flex;align-items:center;gap:6px;text-decoration:none;">
                <i class="bi bi-file-earmark-pdf"></i> Export PDF
            </a>
            <a href="{{ route('retirement.export.excel', ['tab'=>'overdue']) }}" style="background:#16a34a;color:#fff;border:none;padding:8px 14px;border-radius:8px;font-size:13px;font-weight:700;display:flex;align-items:center;gap:6px;text-decoration:none;">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
            @if($stats['overdue'] > 0)
            <form action="{{ route('retirement.process-all') }}" method="POST" id="form-process-all">
                @csrf
                <button type="button" class="btn-process-all" onclick="confirmProcessAll({{ $stats['overdue'] }})">
                    <i class="bi bi-lightning-charge-fill"></i> Retire All Overdue ({{ $stats['overdue'] }})
                </button>
            </form>
            @else
            <button class="btn-process-all" style="opacity: 0.4; cursor: not-allowed;" disabled>
                <i class="bi bi-check2-all"></i> All Caught Up
            </button>
            @endif
        </div>
    </div>

    <div style="overflow-x: auto;">
        <table class="ret-table">
            <thead>
                <tr>
                    <th>Employee / Item</th>
                    <th>Age / DOB</th>
                    <th>Position / Org Unit</th>
                    <th>Salary Grade</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($overdue as $r)
                <tr>
                    <td>
                        <span class="emp-name">{{ $r->last_name }}, {{ $r->first_name }}</span>
                        <span class="emp-item">{{ $r->item_no_new }}</span>
                    </td>
                    <td>
                        <span class="age-badge age-overdue">{{ $r->age }} yrs</span>
                        <br>
                        <span style="font-size:11px;color:#64748b;">{{ $r->date_of_birth?->format('M d, Y') }}</span>
                    </td>
                    <td>
                        <div class="pos-title">{{ $r->position_title }}</div>
                        <div class="pos-office">{{ $r->office_department }}</div>
                    </td>
                    <td>SG-{{ $r->salary_grade }} (Step {{ $r->step }})</td>
                    <td>
                        <button type="button" class="btn-vacate" onclick="confirmVacate('{{ $r->id }}', '{{ addslashes($r->last_name . ', ' . $r->first_name) }}', 'COMPULSORY RETIREMENT')">
                            <i class="bi bi-box-arrow-right"></i> Mark Vacant
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center; padding: 40px; color: #64748b;">
                        <i class="bi bi-check-circle" style="font-size: 24px; color: #10b981; display: block; margin-bottom: 8px;"></i>
                        No employees are currently overdue for retirement.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">{{ $overdue->links() }}</div>

@elseif($tab === 'near')
    {{-- Near Retirement Tab --}}
    <div class="controls-bar">
        <div style="font-size: 13px; color: #475569;">
            Showing employees turning <strong>65 years old</strong> within the next 12 months.
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            <a href="{{ route('retirement.export.pdf', ['tab'=>'near']) }}" target="_blank" style="background:#dc2626;color:#fff;border:none;padding:8px 14px;border-radius:8px;font-size:13px;font-weight:700;display:flex;align-items:center;gap:6px;text-decoration:none;">
                <i class="bi bi-file-earmark-pdf"></i> Export PDF
            </a>
            <a href="{{ route('retirement.export.excel', ['tab'=>'near']) }}" style="background:#16a34a;color:#fff;border:none;padding:8px 14px;border-radius:8px;font-size:13px;font-weight:700;display:flex;align-items:center;gap:6px;text-decoration:none;">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
        </div>
    </div>

    <div style="overflow-x: auto;">
        <table class="ret-table">
            <thead>
                <tr>
                    <th>Employee / Item</th>
                    <th>Age / DOB</th>
                    <th>Retirement Date</th>
                    <th>Position / Org Unit</th>
                    <th>Salary Grade</th>
                </tr>
            </thead>
            <tbody>
                @forelse($near as $r)
                <tr>
                    <td>
                        <span class="emp-name">{{ $r->last_name }}, {{ $r->first_name }}</span>
                        <span class="emp-item">{{ $r->item_no_new }}</span>
                    </td>
                    <td>
                        <span class="age-badge age-near">{{ $r->age }} yrs</span>
                        <br>
                        <span style="font-size:11px;color:#64748b;">{{ $r->date_of_birth?->format('M d, Y') }}</span>
                    </td>
                    <td>
                        <div style="font-weight:700; color:#0f172a;">
                            {{ $r->retirement_date?->format('F d, Y') }}
                        </div>
                        <div style="font-size:11px; color:#64748b;">
                            {{ $r->retirement_date?->diffForHumans() }}
                        </div>
                    </td>
                    <td>
                        <div class="pos-title">{{ $r->position_title }}</div>
                        <div class="pos-office">{{ $r->office_department }}</div>
                    </td>
                    <td>SG-{{ $r->salary_grade }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center; padding: 40px; color: #64748b;">
                        No employees are retiring in the next 12 months.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">{{ $near->links() }}</div>

@else
    {{-- Optional to Retire Tab --}}
    <div class="controls-bar">
        <div style="font-size: 13px; color: #475569;">
            Showing employees who are <strong>60 to 64 years old</strong> and are eligible for optional retirement.
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            <a href="{{ route('retirement.export.pdf', ['tab'=>'optional']) }}" target="_blank" style="background:#dc2626;color:#fff;border:none;padding:8px 14px;border-radius:8px;font-size:13px;font-weight:700;display:flex;align-items:center;gap:6px;text-decoration:none;">
                <i class="bi bi-file-earmark-pdf"></i> Export PDF
            </a>
            <a href="{{ route('retirement.export.excel', ['tab'=>'optional']) }}" style="background:#16a34a;color:#fff;border:none;padding:8px 14px;border-radius:8px;font-size:13px;font-weight:700;display:flex;align-items:center;gap:6px;text-decoration:none;">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
        </div>
    </div>

    <div style="overflow-x: auto;">
        <table class="ret-table">
            <thead>
                <tr>
                    <th>Employee / Item</th>
                    <th>Age / DOB</th>
                    <th>Position / Org Unit</th>
                    <th>Salary Grade</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($optional as $r)
                <tr>
                    <td>
                        <span class="emp-name">{{ $r->last_name }}, {{ $r->first_name }}</span>
                        <span class="emp-item">{{ $r->item_no_new }}</span>
                    </td>
                    <td>
                        <span class="age-badge" style="background:#fef3c7; color:#d97706;">{{ $r->age }} yrs</span>
                        <br>
                        <span style="font-size:11px;color:#64748b;">{{ $r->date_of_birth?->format('M d, Y') }}</span>
                    </td>
                    <td>
                        <div class="pos-title">{{ $r->position_title }}</div>
                        <div class="pos-office">{{ $r->office_department }}</div>
                    </td>
                    <td>SG-{{ $r->salary_grade }} (Step {{ $r->step }})</td>
                    <td>
                        <button type="button" class="btn-vacate" style="color:#ca8a04; border-color:#fef08a; background:#fefce8;" onclick="confirmVacate('{{ $r->id }}', '{{ addslashes($r->last_name . ', ' . $r->first_name) }}', 'OPTIONAL RETIREMENT')">
                            <i class="bi bi-box-arrow-right"></i> Process Optional
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center; padding: 40px; color: #64748b;">
                        No employees found in the 60-64 age range.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">{{ $optional->links() }}</div>
@endif

{{-- SweetAlert2 for Popup Modals --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmProcessAll(count) {
    Swal.fire({
        title: 'Retire All Overdue?',
        text: `This will process ${count} employee(s) for compulsory retirement. Their positions will be marked as vacant and personal info cleared. This action will permanently modify the database.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, proceed!',
        cancelButtonText: 'Cancel',
        customClass: {
            popup: 'custom-swal',
            icon: 'custom-swal-icon',
            title: 'custom-swal-title',
            htmlContainer: 'custom-swal-text',
            actions: 'custom-swal-actions',
            confirmButton: 'custom-swal-confirm',
            cancelButton: 'custom-swal-cancel',
            footer: 'custom-swal-footer'
        },
        buttonsStyling: false,
        footer: 'HDMS- Human Resource Data Management System'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('form-process-all').submit();
        }
    });
}

function confirmVacate(recordId, employeeName, defaultType = 'COMPULSORY RETIREMENT') {
    Swal.fire({
        title: 'Process Retirement',
        html: `
            <div style="font-size:13px;color:#64748b;margin-bottom:16px;">
                You are about to process retirement for <strong style="color:#0f172a;">${employeeName}</strong>. This will mark the position as vacant.
            </div>
            <div style="text-align:left; margin-bottom:12px;">
                <label style="font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:0.5px;display:block;margin-bottom:6px;">Effective Date of Retirement</label>
                <input type="date" id="swal-ret-date" value="{{ now()->toDateString() }}" style="width:100%; border:1px solid #cbd5e1; border-radius:8px; padding:10px; font-size:14px; outline:none; box-sizing:border-box;">
            </div>
            <div style="text-align:left; margin-bottom:16px;">
                <label style="font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:0.5px;display:block;margin-bottom:6px;">Retirement Type</label>
                <select id="swal-ret-type" onchange="toggleOtherRetType()" style="width:100%; border:1px solid #cbd5e1; border-radius:8px; padding:10px; font-size:14px; outline:none; box-sizing:border-box;">
                    <option value="COMPULSORY RETIREMENT" ${defaultType === 'COMPULSORY RETIREMENT' ? 'selected' : ''}>Compulsory Retirement (Age 65)</option>
                    <option value="OPTIONAL RETIREMENT" ${defaultType === 'OPTIONAL RETIREMENT' ? 'selected' : ''}>Optional Retirement (Early)</option>
                    <option value="DISABILITY RETIREMENT" ${defaultType === 'DISABILITY RETIREMENT' ? 'selected' : ''}>Disability Retirement</option>
                    <option value="OTHERS">Others (Please Specify)</option>
                </select>
                <input type="text" id="swal-ret-other" placeholder="Specify retirement type..." style="display:none; width:100%; margin-top:8px; border:1px solid #cbd5e1; border-radius:8px; padding:10px; font-size:14px; outline:none; box-sizing:border-box;">
            </div>
            <div style="background:#fff1f2; border:1px solid #fecdd3; border-radius:8px; padding:10px 12px; font-size:12px; color:#be123c; display:flex; gap:8px; align-items:flex-start;">
                <i class="bi bi-exclamation-triangle-fill" style="margin-top:2px;"></i>
                <div>This action will permanently modify the database and remove the employee from active payroll.</div>
            </div>
        `,
        icon: 'warning',
        iconColor: '#ef4444',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-check2-circle"></i> Confirm & Vacate',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        preConfirm: () => {
            const date = document.getElementById('swal-ret-date').value;
            if (!date) {
                Swal.showValidationMessage('Effective date is required');
                return false;
            }
            const typeSelect = document.getElementById('swal-ret-type').value;
            let retType = typeSelect;
            if (typeSelect === 'OTHERS') {
                const otherText = document.getElementById('swal-ret-other').value.trim();
                if (!otherText) {
                    Swal.showValidationMessage('Please specify the retirement type');
                    return false;
                }
                retType = otherText.toUpperCase();
            }
            return {
                date: date,
                type: retType
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            let form = document.createElement('form');
            form.method = 'POST';
            form.action = '/retirement/' + recordId;
            
            let csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);

            let dateInput = document.createElement('input');
            dateInput.type = 'hidden';
            dateInput.name = 'effective_date';
            dateInput.value = result.value.date;
            form.appendChild(dateInput);

            let typeInput = document.createElement('input');
            typeInput.type = 'hidden';
            typeInput.name = 'retirement_type';
            typeInput.value = result.value.type;
            form.appendChild(typeInput);

            document.body.appendChild(form);
            form.submit();
        }
    });
}

function toggleOtherRetType() {
    const sel = document.getElementById('swal-ret-type');
    const inp = document.getElementById('swal-ret-other');
    if (!sel || !inp) return;
    if (sel.value === 'OTHERS') {
        inp.style.display = 'block';
        inp.focus();
    } else {
        inp.style.display = 'none';
        inp.value = '';
    }
}
</script>

</x-dashboard-app>
