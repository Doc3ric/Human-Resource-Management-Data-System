<x-dashboard-app>
<style>
.cs-hero {
    background: linear-gradient(135deg, #1e1b4b 0%, #312e81 55%, #4338ca 100%);
    border-radius: 14px; padding: 24px 28px;
    position: relative; overflow: hidden; margin-bottom: 20px;
}
.cs-hero::before {
    content: ''; position: absolute; inset: 0;
    background-image: radial-gradient(circle, rgba(255,255,255,.07) 1px, transparent 1px);
    background-size: 22px 22px;
}
.cs-hero-title { font-size: 1.55rem; font-weight: 700; color: #fff; position: relative; z-index: 1; }
.cs-hero-sub   { font-size: .85rem; color: rgba(255,255,255,.75); position: relative; z-index: 1; margin-top: 2px; }

/* Section label */
.cs-section-label {
    font-size: .7rem; font-weight: 800; text-transform: uppercase;
    letter-spacing: .1em; color: #94a3b8; margin: 18px 0 8px 2px;
    display: flex; align-items: center; gap: 8px;
}
.cs-section-label::after {
    content: ''; flex: 1; height: 1px; background: #e5e7eb;
}

/* Summary cards */
.cs-cards {
    display: grid; gap: 12px; margin-bottom: 4px;
}
.cs-cards.cols-3 { grid-template-columns: repeat(3, 1fr); }
.cs-cards.cols-2 { grid-template-columns: repeat(2, 1fr); }
@media (max-width: 860px)  { .cs-cards.cols-3, .cs-cards.cols-2 { grid-template-columns: repeat(2,1fr); } }
@media (max-width: 560px)  { .cs-cards.cols-3, .cs-cards.cols-2 { grid-template-columns: 1fr; } }

.cs-card {
    background: #fff; border-radius: 12px; border: 1px solid #e5e7eb;
    padding: 14px 18px; display: flex; flex-direction: column; gap: 3px;
    position: relative; overflow: hidden;
}
.cs-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; }
.cs-card.c-active::before   { background: #059669; }
.cs-card.c-lapsed::before   { background: #d97706; }
.cs-card.c-separated::before{ background: #dc2626; }

.cs-card-group { font-size: .68rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .06em; }
.cs-card-label { font-size: .72rem; font-weight: 600; color: #64748b; }
.cs-card-value { font-size: 1.9rem; font-weight: 800; line-height: 1.1; }
.cs-card.c-active .cs-card-value    { color: #059669; }
.cs-card.c-lapsed .cs-card-value    { color: #d97706; }
.cs-card.c-separated .cs-card-value { color: #dc2626; }
.cs-card-desc { font-size: .7rem; color: #94a3b8; }

/* Separation mini-breakdown */
.sep-pills { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 8px; }
.sep-pill {
    padding: 2px 10px; border-radius: 999px; font-size: .7rem; font-weight: 600;
    background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;
}

/* Totals strip */
.cs-totals { display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; }
.cs-total-chip {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 9px;
    padding: 10px 16px; display: flex; align-items: center; gap: 10px;
}
.cs-total-chip .ct-icon { font-size: 1.1rem; }
.cs-total-chip .ct-label { font-size: .68rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .06em; }
.cs-total-chip .ct-val   { font-size: 1.25rem; font-weight: 800; color: #0f172a; }

/* Filter */
.cs-filter {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 10px;
    padding: 14px 18px; margin-bottom: 16px;
    display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;
}
.cs-filter label { font-size: .7rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: .05em; display: block; margin-bottom: 4px; }
.cs-filter select {
    border: 1px solid #d1d5db; border-radius: 7px; padding: 7px 10px;
    font-size: .85rem; background: #f9fafb; outline: none; min-width: 280px;
}
.cs-filter select:focus { border-color: #4338ca; background: #fff; box-shadow: 0 0 0 3px rgba(67,56,202,.1); }

/* Breakdown table */
.cs-table-wrap { background: #fff; border-radius: 12px; border: 1px solid #e5e7eb; overflow: hidden; }
.cs-table { width: 100%; border-collapse: collapse; font-size: .8rem; }
.cs-table thead th {
    background: #f8fafc; padding: 9px 10px;
    font-size: .66rem; font-weight: 700; color: #64748b;
    text-transform: uppercase; letter-spacing: .06em;
    border-bottom: 1px solid #e5e7eb; white-space: nowrap; text-align: center;
}
.cs-table thead th.col-office { text-align: left; }
.cs-table thead .gh { padding: 5px 10px; }
.cs-table thead .gh.casual { background: #fefce8; color: #92400e; }
.cs-table thead .gh.jo     { background: #eff6ff; color: #1e40af; }
.cs-table thead .gh.perm   { background: #f0fdf4; color: #064e3b; }
.cs-table tbody td {
    padding: 8px 10px; border-bottom: 1px solid #f1f5f9;
    text-align: center; vertical-align: middle;
}
.cs-table tbody td.col-office { text-align: left; font-weight: 600; color: #0f172a; font-size: .79rem; padding-left: 14px; }
.cs-table tbody tr:last-child td { border-bottom: none; }
.cs-table tbody tr:hover { background: #f8fafc; }
.cs-table tfoot td {
    padding: 9px 10px; font-weight: 700; font-size: .8rem;
    background: #f1f5f9; border-top: 2px solid #e2e8f0; text-align: center;
}
.cs-table tfoot td.col-office { text-align: left; padding-left: 14px; }

.cs-count {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 34px; padding: 2px 9px; border-radius: 999px;
    font-weight: 700; font-size: .8rem; cursor: pointer;
    transition: all .13s; border: 1px solid transparent;
}
.cs-count:hover { transform: scale(1.1); filter: brightness(.92); }
.cs-count.c-active    { background: #d1fae5; color: #065f46; border-color: #a7f3d0; }
.cs-count.c-lapsed    { background: #fef3c7; color: #92400e; border-color: #fde68a; }
.cs-count.c-separated { background: #fee2e2; color: #991b1b; border-color: #fecaca; }
.cs-count.zero        { background: transparent; color: #d1d5db; cursor: default; font-weight: 400; }
.cs-count.zero:hover  { transform: none; filter: none; }

/* Modal */
.cs-modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.45); z-index: 1050;
    align-items: center; justify-content: center;
}
.cs-modal-overlay.show { display: flex; }
.cs-modal {
    background: #fff; border-radius: 14px;
    width: min(860px, 96vw); max-height: 88vh;
    display: flex; flex-direction: column;
    box-shadow: 0 25px 60px rgba(0,0,0,.3);
}
.cs-modal-header {
    padding: 18px 22px 14px; border-bottom: 1px solid #e5e7eb;
    display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;
}
.cs-modal-title  { font-size: 1rem; font-weight: 700; color: #0f172a; }
.cs-modal-close  { background: none; border: none; font-size: 1.3rem; color: #94a3b8; cursor: pointer; }
.cs-modal-close:hover { color: #475569; }
.cs-modal-body   { padding: 16px 22px; overflow-y: auto; flex: 1; }
.cs-modal-footer { padding: 12px 22px; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; flex-shrink: 0; }

.dd-table { width: 100%; border-collapse: collapse; font-size: .8rem; }
.dd-table thead th {
    background: #f8fafc; padding: 8px 10px;
    font-size: .67rem; font-weight: 700; color: #64748b;
    text-transform: uppercase; letter-spacing: .05em;
    border-bottom: 1px solid #e5e7eb; white-space: nowrap; text-align: left;
}
.dd-table tbody td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.dd-table tbody tr:last-child td { border-bottom: none; }
.dd-table tbody tr:hover { background: #f8fafc; }

.dd-badge { padding: 2px 9px; border-radius: 999px; font-size: .7rem; font-weight: 700; white-space: nowrap; }
.dd-badge.c-active    { background: #d1fae5; color: #065f46; }
.dd-badge.c-lapsed    { background: #fef3c7; color: #92400e; }
.dd-badge.c-separated { background: #fee2e2; color: #991b1b; }

.spinner-ring {
    display: inline-block; width: 32px; height: 32px;
    border: 3px solid #e5e7eb; border-top-color: #4338ca;
    border-radius: 50%; animation: spin .7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>

@php
    $cas  = $summary['casual'];
    $jo   = $summary['jo'];
    $perm = $summary['permanent'];
    $grandActive    = $cas['active']    + $jo['active']    + $perm['active'];
    $grandLapsed    = $cas['lapsed']    + $jo['lapsed'];
    $grandSeparated = $cas['separated'] + $jo['separated'] + $perm['separated'];
    $grandTotal     = $grandActive + $grandLapsed + $grandSeparated;
@endphp

<div style="padding:16px;">

    {{-- Hero --}}
    <div class="cs-hero">
        <div class="cs-hero-title"><i class="bi bi-bar-chart-steps me-2"></i>Employment Status Inventory</div>
        <div class="cs-hero-sub">
            Real-time headcount by contract/employment state across all personnel types — as of {{ now()->format('F d, Y') }}
        </div>
    </div>

    {{-- ── CASUAL ── --}}
    <div class="cs-section-label"><i class="bi bi-person-lines-fill" style="color:#d97706;"></i> Casual</div>
    <div class="cs-cards cols-3" style="margin-bottom:16px;">
        <div class="cs-card c-active">
            <div class="cs-card-label">Active</div>
            <div class="cs-card-value">{{ number_format($cas['active']) }}</div>
            <div class="cs-card-desc">Contract end date is current</div>
        </div>
        <div class="cs-card c-lapsed">
            <div class="cs-card-label">Lapsed / Not Renewed</div>
            <div class="cs-card-value">{{ number_format($cas['lapsed']) }}</div>
            <div class="cs-card-desc">Contract expired, no new renewal</div>
        </div>
        <div class="cs-card c-separated">
            <div class="cs-card-label">Separated</div>
            <div class="cs-card-value">{{ number_format($cas['separated']) }}</div>
            <div class="cs-card-desc">Officially resigned / terminated</div>
        </div>
    </div>

    {{-- ── JOB ORDER ── --}}
    <div class="cs-section-label"><i class="bi bi-file-earmark-person" style="color:#1d4ed8;"></i> Job Order</div>
    <div class="cs-cards cols-3" style="margin-bottom:16px;">
        <div class="cs-card c-active">
            <div class="cs-card-label">Active</div>
            <div class="cs-card-value">{{ number_format($jo['active']) }}</div>
            <div class="cs-card-desc">Contract end date is current</div>
        </div>
        <div class="cs-card c-lapsed">
            <div class="cs-card-label">Lapsed / Not Renewed</div>
            <div class="cs-card-value">{{ number_format($jo['lapsed']) }}</div>
            <div class="cs-card-desc">Contract expired, no new renewal</div>
        </div>
        <div class="cs-card c-separated">
            <div class="cs-card-label">Separated</div>
            <div class="cs-card-value">{{ number_format($jo['separated']) }}</div>
            <div class="cs-card-desc">Officially resigned / terminated</div>
        </div>
    </div>

    {{-- ── PERMANENT / REGULAR ── --}}
    <div class="cs-section-label"><i class="bi bi-person-badge-fill" style="color:#059669;"></i> Regular / Permanent</div>
    <div class="cs-cards cols-2" style="margin-bottom:8px;">
        <div class="cs-card c-active">
            <div class="cs-card-label">Active</div>
            <div class="cs-card-value">{{ number_format($perm['active']) }}</div>
            <div class="cs-card-desc">Currently employed, no separation on record</div>
        </div>
        <div class="cs-card c-separated">
            <div class="cs-card-label">Separated</div>
            <div class="cs-card-value">{{ number_format($perm['separated']) }}</div>
            <div class="cs-card-desc">Resignation, transfer, retirement, termination, or death on record</div>
            @if($permSepBreakdown->isNotEmpty())
            <div class="sep-pills">
                @foreach($permSepBreakdown as $reason => $count)
                    <span class="sep-pill">{{ $reason }}: {{ $count }}</span>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- ── Grand Totals with compliance analysis ── --}}
    <div class="cs-section-label" style="margin-top:20px;"><i class="bi bi-sigma"></i> Grand Totals &amp; Compliance Analysis</div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px;">
        <x-stat-card icon="bi-check-circle-fill" color="green" label="Total Active Contracts" :value="$grandActive"
            compliance="CSC / RA 6656"
            analysis="Active contracts require monthly renewal endorsement for casual staff (RA 6656) and quarterly renewal for JO (COA Circular 2012-001)." />

        <x-stat-card icon="bi-clock-history" color="orange" label="Lapsed / Not Renewed" :value="$grandLapsed"
            :alert="$grandLapsed > 0"
            compliance="CSC Rules"
            analysis="{{ $grandLapsed > 0 ? $grandLapsed.' contract(s) have lapsed. Lapsed casual contracts may not have service credit for the gap period. Immediate action required to prevent unauthorized employment.' : 'No lapsed contracts. Contract renewal status is current.' }}" />

        <x-stat-card icon="bi-x-circle-fill" color="red" label="Total Separated" :value="$grandSeparated"
            compliance="CSC / GSIS"
            analysis="Separated personnel must be reported to CSC within 15 days per CSC MC 18, s.1995. GSIS contributions must be terminated immediately upon separation." />

        <x-stat-card icon="bi-people-fill" color="indigo" label="Grand Total Tracked" :value="$grandTotal"
            compliance="CSC / DBM"
            analysis="Combined headcount across all employment types and statuses. Report to DBM for quarterly position accountability and workforce planning purposes." />
    </div>

    {{-- ── Filter ── --}}
    <div class="cs-filter">
        <form method="GET" action="{{ route('contract-status.index') }}" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;width:100%;">
            <div>
                <label>Filter by Office / Department</label>
                <select name="office" onchange="this.form.submit()">
                    <option value="">— All Offices —</option>
                    @foreach($allOffices as $o)
                        <option value="{{ $o }}" {{ request('office') === $o ? 'selected' : '' }}>{{ $o }}</option>
                    @endforeach
                </select>
            </div>
            @if(request('office'))
                <a href="{{ route('contract-status.index') }}"
                   style="display:inline-flex;align-items:center;gap:5px;padding:7px 14px;border-radius:7px;font-size:.82rem;font-weight:600;background:#f1f5f9;color:#475569;text-decoration:none;border:1px solid #e2e8f0;align-self:flex-end;">
                    <i class="bi bi-x"></i> Clear
                </a>
            @endif
        </form>
    </div>

    {{-- ── Breakdown Table ── --}}
    <div class="cs-table-wrap">
        <table class="cs-table">
            <thead>
                <tr>
                    <th class="col-office" rowspan="2" style="vertical-align:bottom;border-right:1px solid #e5e7eb;">Office / Department</th>
                    <th colspan="3" class="gh casual">CASUAL</th>
                    <th colspan="3" class="gh jo">JOB ORDER</th>
                    <th colspan="2" class="gh perm">REGULAR / PERMANENT</th>
                    <th rowspan="2" style="vertical-align:bottom;background:#f1f5f9;color:#475569;">TOTAL</th>
                </tr>
                <tr>
                    <th style="color:#059669;">Active</th>
                    <th style="color:#d97706;">Lapsed</th>
                    <th style="color:#dc2626;">Separated</th>
                    <th style="color:#059669;">Active</th>
                    <th style="color:#d97706;">Lapsed</th>
                    <th style="color:#dc2626;">Separated</th>
                    <th style="color:#059669;">Active</th>
                    <th style="color:#dc2626;">Separated</th>
                </tr>
            </thead>
            <tbody>
                @forelse($breakdown as $row)
                @php
                    $rowTotal = $row['casual_active']  + $row['casual_lapsed']  + $row['casual_separated']
                              + $row['jo_active']      + $row['jo_lapsed']      + $row['jo_separated']
                              + $row['perm_active']    + $row['perm_separated'];
                @endphp
                <tr>
                    <td class="col-office" style="border-right:1px solid #e5e7eb;max-width:220px;">{{ $row['office'] }}</td>
                    @foreach([
                        ['casual','active',    'c-active'],
                        ['casual','lapsed',    'c-lapsed'],
                        ['casual','separated', 'c-separated'],
                        ['jo',    'active',    'c-active'],
                        ['jo',    'lapsed',    'c-lapsed'],
                        ['jo',    'separated', 'c-separated'],
                        ['perm',  'active',    'c-active'],
                        ['perm',  'separated', 'c-separated'],
                    ] as [$type, $state, $cls])
                    @php $val = $row["{$type}_{$state}"]; @endphp
                    <td>
                        @if($val > 0)
                            <span class="cs-count {{ $cls }}"
                                onclick="drilldown('{{ addslashes($row['office']) }}','{{ $type === 'perm' ? 'permanent' : $type }}','{{ $state }}')">
                                {{ $val }}
                            </span>
                        @else
                            <span class="cs-count zero">0</span>
                        @endif
                    </td>
                    @endforeach
                    <td style="font-weight:700;color:#0f172a;">{{ $rowTotal }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="text-align:center;padding:48px;color:#94a3b8;">
                        <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:8px;"></i>
                        No records found.
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($breakdown->count() > 1)
            <tfoot>
                <tr>
                    <td class="col-office" style="border-right:1px solid #e2e8f0;">TOTAL</td>
                    <td style="color:#059669;">{{ $breakdown->sum('casual_active') }}</td>
                    <td style="color:#d97706;">{{ $breakdown->sum('casual_lapsed') }}</td>
                    <td style="color:#dc2626;">{{ $breakdown->sum('casual_separated') }}</td>
                    <td style="color:#059669;">{{ $breakdown->sum('jo_active') }}</td>
                    <td style="color:#d97706;">{{ $breakdown->sum('jo_lapsed') }}</td>
                    <td style="color:#dc2626;">{{ $breakdown->sum('jo_separated') }}</td>
                    <td style="color:#059669;">{{ $breakdown->sum('perm_active') }}</td>
                    <td style="color:#dc2626;">{{ $breakdown->sum('perm_separated') }}</td>
                    <td>{{ $breakdown->sum(fn($r) =>
                        $r['casual_active']  + $r['casual_lapsed']  + $r['casual_separated'] +
                        $r['jo_active']      + $r['jo_lapsed']      + $r['jo_separated'] +
                        $r['perm_active']    + $r['perm_separated']
                    ) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    <div style="font-size:.71rem;color:#94a3b8;margin-top:10px;padding-left:4px;line-height:1.7;">
        <i class="bi bi-info-circle me-1"></i>
        <strong>Active</strong> — contract end date is today or future (Casual/JO), or no separation on record (Regular) &nbsp;·&nbsp;
        <strong>Lapsed</strong> — contract expired with no renewal, not yet officially separated (Casual/JO only) &nbsp;·&nbsp;
        <strong>Separated</strong> — separation reason recorded (resignation, transfer, retirement, termination, death) &nbsp;·&nbsp;
        Click any count to view employees.
    </div>

</div>

{{-- ══ DRILLDOWN MODAL ══ --}}
<div class="cs-modal-overlay" id="ddModal">
    <div class="cs-modal">
        <div class="cs-modal-header">
            <div class="cs-modal-title" id="ddTitle">—</div>
            <button class="cs-modal-close" onclick="closeDd()">×</button>
        </div>
        <div class="cs-modal-body" id="ddBody">
            <div style="text-align:center;padding:40px;"><div class="spinner-ring"></div></div>
        </div>
        <div class="cs-modal-footer">
            <button onclick="closeDd()"
                style="padding:7px 18px;border-radius:7px;border:1px solid #d1d5db;background:#fff;font-size:.85rem;font-weight:600;cursor:pointer;color:#475569;">
                Close
            </button>
        </div>
    </div>
</div>

<script>
const DD_URL = '{{ route('contract-status.drilldown') }}';
const TYPE_LABEL  = { casual: 'Casual', jo: 'Job Order', permanent: 'Regular/Permanent' };
const STATE_LABEL = { active: 'Active', lapsed: 'Lapsed', separated: 'Separated' };

function drilldown(office, type, state) {
    document.getElementById('ddTitle').textContent =
        `${office} — ${TYPE_LABEL[type]} · ${STATE_LABEL[state]}`;
    document.getElementById('ddBody').innerHTML =
        '<div style="text-align:center;padding:40px;"><div class="spinner-ring"></div></div>';
    document.getElementById('ddModal').classList.add('show');

    fetch(`${DD_URL}?office=${encodeURIComponent(office)}&type=${type}&state=${state}`)
        .then(r => r.json())
        .then(renderDrilldown)
        .catch(() => {
            document.getElementById('ddBody').innerHTML =
                '<div style="color:#dc2626;text-align:center;padding:32px;">Failed to load. Please try again.</div>';
        });
}

function renderDrilldown(data) {
    if (data.count === 0) {
        document.getElementById('ddBody').innerHTML =
            '<div style="text-align:center;padding:40px;color:#94a3b8;"><i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:8px;"></i>No records found.</div>';
        return;
    }

    const isPermanent = data.type === 'permanent';

    const rows = data.records.map(r => {
        const name = `${(r.last_name||'').toUpperCase()}, ${r.first_name||''}${r.name_extension ? ' '+r.name_extension : ''}`;

        // Contract period cell (only relevant for Casual/JO)
        let periodCell = '—';
        if (!isPermanent && (r.contract_start || r.contract_end)) {
            let color = '#059669', note = '';
            if (r.days_left !== null) {
                if      (r.days_left < 0)   { color = '#dc2626'; note = `<div style="font-size:.7rem;color:#dc2626;">Expired ${Math.abs(r.days_left)}d ago</div>`; }
                else if (r.days_left <= 14)  { color = '#d97706'; note = `<div style="font-size:.7rem;color:#d97706;">${r.days_left}d remaining</div>`; }
                else                         { note = `<div style="font-size:.7rem;color:#059669;">${r.days_left}d remaining</div>`; }
            }
            periodCell = `<span style="font-weight:600;color:${color};">${r.contract_start||'?'} → ${r.contract_end||'?'}</span>${note}`;
        }

        // Separation cell
        let sepCell = '—';
        if (data.state === 'separated' && r.nature_of_separation) {
            const classMap = {
                Resigned: '#dbeafe:#1e40af', Transferred: '#f0fdf4:#065f46',
                Retired: '#fef3c7:#92400e',  Terminated: '#fee2e2:#991b1b',
                Deceased: '#f5f3ff:#5b21b6', Other: '#f1f5f9:#475569',
            };
            const parts = (classMap[r.sep_class] || '#f1f5f9:#475569').split(':');
            sepCell = `<span style="background:${parts[0]};color:${parts[1]};padding:2px 9px;border-radius:999px;font-size:.7rem;font-weight:700;">${r.sep_class}</span>
                       <div style="font-size:.72rem;color:#64748b;margin-top:2px;">${r.nature_of_separation}</div>`;
        }

        // State badge
        const badgeClass = { active:'c-active', lapsed:'c-lapsed', separated:'c-separated' }[data.state] || '';
        const stateBadge = `<span class="dd-badge ${badgeClass}">${STATE_LABEL[data.state]}</span>`;

        return `<tr>
            <td>
                <div style="font-weight:700;">${name}</div>
                <div style="font-size:.74rem;color:#64748b;">${r.middle_name||'—'}</div>
            </td>
            <td style="font-size:.78rem;">${r.position_title||'—'}</td>
            <td style="font-size:.75rem;">${r.employment_status||'—'}</td>
            ${!isPermanent ? `<td style="font-size:.78rem;">${periodCell}</td>` : ''}
            <td>${data.state === 'separated' ? sepCell : stateBadge}</td>
        </tr>`;
    }).join('');

    const periodHeader = isPermanent ? '' : '<th>Contract Period</th>';
    const lastHeader   = data.state === 'separated' ? '<th>Separation</th>' : '<th>State</th>';

    document.getElementById('ddBody').innerHTML = `
        <div style="font-size:.8rem;color:#64748b;margin-bottom:12px;">
            <strong>${data.count}</strong> employee(s) &nbsp;·&nbsp;
            ${TYPE_LABEL[data.type]} / ${STATE_LABEL[data.state]}
        </div>
        <table class="dd-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Status</th>
                    ${periodHeader}
                    ${lastHeader}
                </tr>
            </thead>
            <tbody>${rows}</tbody>
        </table>`;
}

function closeDd() { document.getElementById('ddModal').classList.remove('show'); }
document.getElementById('ddModal').addEventListener('click', e => {
    if (e.target === document.getElementById('ddModal')) closeDd();
});
</script>
</x-dashboard-app>
