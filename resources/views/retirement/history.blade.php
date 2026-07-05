<x-dashboard-app>
<style>
/* ── Hero ─────────────────────────────────────────────────────────── */
.hist-hero {
    background: linear-gradient(135deg, #0f2942 0%, #1e3a5f 100%);
    border-radius: 14px; padding: 24px 28px;
    position: relative; overflow: hidden; margin-bottom: 20px;
    display: flex; justify-content: space-between; align-items: center;
    flex-wrap: wrap; gap: 16px;
}
.hist-hero::before {
    content: ''; position: absolute; inset: 0;
    background-image: radial-gradient(circle, rgba(255,255,255,.07) 1px, transparent 1px);
    background-size: 22px 22px; pointer-events: none;
}
.hist-hero-left { position: relative; z-index: 1; }
.hist-hero h1   { color: #fff; font-size: 22px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 10px; }
.hist-hero p    { color: rgba(255,255,255,.6); font-size: 13px; margin: 4px 0 0; }
.hero-stat {
    position: relative; z-index: 1;
    display: flex; flex-direction: column; background: rgba(255,255,255,.1);
    border: 1px solid rgba(255,255,255,.15); border-radius: 10px;
    padding: 10px 16px; min-width: 140px;
}
.hero-stat-label { color: rgba(255,255,255,.7); font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; }
.hero-stat-value { color: #fff; font-size: 26px; font-weight: 800; }

/* ── Tabs ─────────────────────────────────────────────────────────── */
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
.tab-badge.hist { background: #dbeafe; color: #1e40af; }

/* ── Controls Bar ─────────────────────────────────────────────────── */
.controls-bar {
    display: flex; justify-content: space-between; align-items: center;
    background: #fff; padding: 12px 16px; border-radius: 10px; border: 1px solid #e2e8f0;
    margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.03); flex-wrap: wrap; gap: 10px;
}
.controls-left  { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.controls-right { display: flex; align-items: center; gap: 8px; }

.search-wrap {
    position: relative;
}
.search-wrap i {
    position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
    color: #94a3b8; font-size: 14px; pointer-events: none;
}
.search-input {
    border: 1px solid #e2e8f0; border-radius: 8px; padding: 7px 12px 7px 32px;
    font-size: 13px; color: #334155; outline: none; width: 240px;
    transition: border-color .2s, box-shadow .2s;
}
.search-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.1); }

.year-select {
    border: 1px solid #e2e8f0; border-radius: 8px; padding: 7px 12px;
    font-size: 13px; color: #334155; outline: none; background: #fff;
    transition: border-color .2s;
}
.year-select:focus { border-color: #3b82f6; }

.btn-filter {
    background: #3b82f6; color: #fff; border: none;
    padding: 7px 14px; border-radius: 8px; font-size: 13px; font-weight: 600;
    cursor: pointer; display: flex; align-items: center; gap: 5px;
    transition: background .15s;
}
.btn-filter:hover { background: #2563eb; }

.btn-clear {
    background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0;
    padding: 7px 12px; border-radius: 8px; font-size: 13px; font-weight: 600;
    cursor: pointer; text-decoration: none; display: flex; align-items: center; gap: 5px;
    transition: background .15s;
}
.btn-clear:hover { background: #e2e8f0; color: #334155; }

/* ── Table ─────────────────────────────────────────────────────────── */
.hist-table { width: 100%; border-collapse: separate; border-spacing: 0; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; }
.hist-table thead th {
    background: #f8fafc; padding: 12px 16px; font-size: 11px; font-weight: 700;
    text-transform: uppercase; color: #475569; text-align: left;
    border-bottom: 1px solid #e2e8f0; letter-spacing: .5px; white-space: nowrap;
}
.hist-table tbody td {
    padding: 12px 16px; font-size: 13px; color: #334155; border-bottom: 1px solid #f1f5f9; vertical-align: middle;
}
.hist-table tbody tr:last-child td { border-bottom: 0; }
.hist-table tbody tr:hover td { background: #f8fafc; }
.hist-table .col-no { width: 48px; text-align: center; color: #94a3b8; font-size: 12px; }

.emp-name   { font-weight: 700; color: #0f172a; text-transform: uppercase; }
.emp-fname  { color: #334155; }
.pos-title  { font-weight: 600; color: #0f172a; }
.pos-office { font-size: 11px; color: #64748b; margin-top: 2px; }
.sg-badge {
    display: inline-block; background: #eff6ff; color: #1d4ed8;
    border: 1px solid #bfdbfe; padding: 2px 8px; border-radius: 6px;
    font-size: 12px; font-weight: 700;
}
.salary-val { font-weight: 600; color: #065f46; }
.ret-date   { font-size: 12px; color: #64748b; }
.ret-date strong { display: block; font-size: 13px; color: #334155; font-weight: 600; }

.empty-state { text-align: center; padding: 60px 20px; color: #64748b; }
.empty-state i { font-size: 36px; color: #cbd5e1; display: block; margin-bottom: 10px; }
.empty-state p { margin: 0; font-size: 14px; }

.badge-count {
    background: #dbeafe; color: #1e40af; font-size: 12px; font-weight: 600;
    padding: 2px 10px; border-radius: 99px; margin-left: 8px;
}

.pagination-wrap { margin-top: 16px; padding: 0 4px; }
</style>

{{-- Hero Header --}}
<div class="hist-hero">
    <div class="hist-hero-left">
        <h1><i class="bi bi-clock-history"></i> History of Retirement</h1>
        <p>All compulsory retirements processed through the system.</p>
    </div>
    <div class="hero-stat">
        <span class="hero-stat-label">Total Retired</span>
        <span class="hero-stat-value">{{ number_format($totalRetired) }}</span>
    </div>
</div>

{{-- Tabs --}}
<div class="tabs-wrap">
    <a href="{{ route('retirement.index', ['tab' => 'overdue']) }}" class="tab-link">
        <i class="bi bi-exclamation-triangle"></i> Overdue
    </a>
    <a href="{{ route('retirement.index', ['tab' => 'near']) }}" class="tab-link">
        <i class="bi bi-hourglass-split"></i> Near Retirement
    </a>
    <a href="{{ route('retirement.history') }}" class="tab-link active">
        <i class="bi bi-clock-history"></i> History
        <span class="tab-badge hist">{{ $totalRetired }}</span>
    </a>
</div>

{{-- Controls Bar --}}
<form method="GET" action="{{ route('retirement.history') }}" id="filter-form">
    <div class="controls-bar">
        <div class="controls-left">
            <div class="search-wrap">
                <i class="bi bi-search"></i>
                <input type="text" name="search" class="search-input"
                    placeholder="Search by name, office, or position…"
                    value="{{ $search }}">
            </div>
            <select name="year" class="year-select">
                <option value="">All Years</option>
                @foreach($years as $yr)
                    <option value="{{ $yr }}" {{ $year == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-filter">
                <i class="bi bi-funnel"></i> Filter
            </button>
            @if($search || $year)
            <a href="{{ route('retirement.history') }}" class="btn-clear">
                <i class="bi bi-x-circle"></i> Clear
            </a>
            @endif
        </div>
        <div class="controls-right">
            <a href="{{ route('retirement.export.pdf', ['tab' => 'history', 'search' => $search, 'year' => $year]) }}"
                target="_blank"
                style="background:#dc2626;color:#fff;border:none;padding:8px 14px;border-radius:8px;font-size:13px;font-weight:700;display:flex;align-items:center;gap:6px;text-decoration:none;">
                <i class="bi bi-file-earmark-pdf"></i> Export PDF
            </a>
            <a href="{{ route('retirement.export.excel', ['tab' => 'history', 'search' => $search, 'year' => $year]) }}"
                style="background:#16a34a;color:#fff;border:none;padding:8px 14px;border-radius:8px;font-size:13px;font-weight:700;display:flex;align-items:center;gap:6px;text-decoration:none;">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
        </div>
        <div style="font-size: 13px; color: #475569;">
            Showing <strong>{{ $records->total() }}</strong> retired record(s)
            @if($search || $year)
                <span class="badge-count">Filtered</span>
            @endif
        </div>
    </div>
</form>

{{-- Table --}}
<div style="overflow-x: auto;">
    <table class="hist-table">
        <thead>
            <tr>
                <th class="col-no">#</th>
                <th>Office / Org Unit</th>
                <th>Last Name</th>
                <th>First Name</th>
                <th>Position Title</th>
                <th>Salary Grade</th>
                <th>Salary</th>
                <th>Date Retired</th>
                <th style="width: 80px; text-align: center;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $index => $r)
            @php
                // Parse former name from remarks_annotation
                // Format: "Former employee: LAST NAME, First Name Middle Name."
                $formerLast  = '';
                $formerFirst = '';
                $annotation  = $r->remarks_annotation ?? '';

                if (preg_match('/Former employee:\s*([^.]+)\./i', $annotation, $m)) {
                    $namePart = trim($m[1]);
                    // Last name is before the first comma
                    if (str_contains($namePart, ',')) {
                        [$ln, $fn] = explode(',', $namePart, 2);
                        $formerLast  = strtoupper(trim($ln));
                        $formerFirst = trim($fn);
                    } else {
                        $formerLast = strtoupper(trim($namePart));
                    }
                }
            @endphp
            <tr>
                <td class="col-no">{{ $records->firstItem() + $index }}</td>
                <td>
                    <div style="font-weight:600; color:#0f172a; font-size:13px;">
                        {{ $r->office_department ?? '—' }}
                    </div>
                </td>
                <td>
                    <span class="emp-name">{{ $formerLast ?: '—' }}</span>
                </td>
                <td>
                    <span class="emp-fname">{{ $formerFirst ?: '—' }}</span>
                </td>
                <td>
                    <div class="pos-title">{{ $r->position_title ?? '—' }}</div>
                    <div class="pos-office">{{ $r->item_no_new ?? '' }}</div>
                </td>
                <td>
                    <span class="sg-badge">SG-{{ $r->salary_grade }} Step {{ $r->step }}</span>
                </td>
                <td>
                    <span class="salary-val">
                        ₱{{ $r->base_salary_amount ? number_format($r->base_salary_amount / 12, 2) : '—' }}
                        <span style="font-size:10px; color:#94a3b8; font-weight:400;">/mo</span>
                    </span>
                </td>
                <td>
                    <span class="ret-date">
                        <strong>{{ $r->retired_at?->format('M d, Y') }}</strong>
                        {{ $r->retired_at?->diffForHumans() }}
                    </span>
                </td>
                <td style="text-align: center;">
                    <button type="button" class="btn-clear" style="padding:4px 8px; font-size:12px; margin:0 auto;"
                        data-id="{{ $r->id }}"
                        data-date="{{ $r->retired_at?->format('Y-m-d') }}"
                        data-annotation="{{ $r->remarks_annotation ?? '' }}"
                        onclick="editHistory(this.dataset.id, this.dataset.date, this.dataset.annotation)">
                        <i class="bi bi-pencil-square"></i> Edit
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8">
                    <div class="empty-state">
                        <i class="bi bi-journal-x"></i>
                        <p>
                            @if($search || $year)
                                No retirement records match your search criteria.
                            @else
                                No retirement records have been processed yet.
                            @endif
                        </p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="pagination-wrap">{{ $records->links() }}</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    @if(session('success'))
        Swal.fire({ icon: 'success', title: 'Success!', text: "{{ session('success') }}", confirmButtonColor: '#3b82f6', timer: 3000 });
    @endif
    @if(session('error'))
        Swal.fire({ icon: 'error', title: 'Error!', text: "{{ session('error') }}", confirmButtonColor: '#ef4444' });
    @endif

function editHistory(recordId, currentDate, currentAnnotation) {
    Swal.fire({
        title: 'Edit Retirement Details',
        html: `
            <div style="text-align:left; margin-bottom:12px;">
                <label style="font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;margin-bottom:6px;display:block;">Effective Date</label>
                <input type="date" id="swal-hist-date" value="${currentDate}" style="width:100%; border:1px solid #cbd5e1; border-radius:8px; padding:10px; font-size:14px; outline:none; box-sizing:border-box;">
            </div>
            <div style="text-align:left;">
                <label style="font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;margin-bottom:6px;display:block;">Annotation / Comments</label>
                <textarea id="swal-hist-ann" rows="6" style="width:100%; border:1px solid #cbd5e1; border-radius:8px; padding:10px; font-size:13px; outline:none; box-sizing:border-box;">${currentAnnotation}</textarea>
            </div>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Save Changes',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#3b82f6',
        preConfirm: () => {
            const date = document.getElementById('swal-hist-date').value;
            if (!date) {
                Swal.showValidationMessage('Effective date is required');
                return false;
            }
            return {
                date: date,
                annotation: document.getElementById('swal-hist-ann').value
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            let form = document.createElement('form');
            form.method = 'POST';
            form.action = '/retirement/' + recordId + '/history';
            
            let csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);

            let methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'PUT';
            form.appendChild(methodInput);

            let dateInput = document.createElement('input');
            dateInput.type = 'hidden';
            dateInput.name = 'retired_at';
            dateInput.value = result.value.date;
            form.appendChild(dateInput);

            let annInput = document.createElement('input');
            annInput.type = 'hidden';
            annInput.name = 'remarks_annotation';
            annInput.value = result.value.annotation;
            form.appendChild(annInput);

            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>

</x-dashboard-app>
