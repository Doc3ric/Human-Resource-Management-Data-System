<x-dashboard-app>
<style>
/* ── Reports Page Styles ───────────────────────────────────────────────── */
/* Hero Banner (Matches Inventory) */
.inv-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #1a5276 55%, #117a65 100%);
    border-radius: 14px;
    padding: 28px 32px;
    position: relative;
    overflow: hidden;
    margin-bottom: 20px;
}
.inv-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: radial-gradient(circle, rgba(255,255,255,.08) 1px, transparent 1px);
    background-size: 22px 22px;
}
.inv-hero-inner { position: relative; z-index: 1; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
.inv-hero h1 { color: #fff; font-size: 26px; font-weight: 800; margin: 0; line-height: 1.2; }
.inv-hero p  { color: rgba(255,255,255,.65); font-size: 13px; margin: 6px 0 0; }
.hero-btn {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.3);
    color: #fff; padding: 9px 18px; border-radius: 10px;
    font-size: 13px; font-weight: 600; text-decoration: none;
    transition: background .2s;
}
.hero-btn:hover { background: rgba(255,255,255,.28); color:#fff; }

/* Filter Bar */
.filter-bar {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 12px;
    padding: 16px 20px; margin-bottom: 20px;
}
.filter-bar form { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; }
.filter-field label { display: block; font-size: 11px; font-weight: 600; color: #9ca3af; text-transform: uppercase; letter-spacing: .6px; margin-bottom: 6px; }
.filter-field input, .filter-field select {
    border: 1px solid #e5e7eb; border-radius: 8px;
    padding: 8px 12px; font-size: 13px; outline: none;
    transition: border .15s;
}
.filter-field input:focus, .filter-field select:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
.filter-field-sm { min-width: 160px; }

/* Report Cards (Matches Office Accordions) */
.office-card {
    background: #ffffff; border: 1px solid #eef2f6; border-radius: 12px;
    margin-bottom: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 2px 4px -2px rgba(0,0,0,0.02);
    overflow: hidden;
}
.office-btn {
    width: 100%; border: none; background: #ffffff; cursor: pointer;
    display: flex; align-items: center; justify-content: space-between; gap: 24px;
    padding: 24px 32px; text-align: left; transition: background 0.2s;
}
.office-btn:hover { background: #f8fafc; }
@media(max-width: 1024px) {
    .office-btn { flex-direction: column; align-items: stretch; gap: 16px; padding: 20px; }
}
.off-left { display: flex; align-items: center; gap: 16px; flex: 1.5; min-width: 250px; }
.off-icon-box {
    width: 54px; height: 54px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; flex-shrink: 0;
}
.off-name { font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 4px; line-height: 1.2; word-break: break-word; }
.off-sub { font-size: 13px; color: #64748b; }
.off-right { display: flex; align-items: center; justify-content: flex-end; flex: 1; min-width: 120px; }
.off-view-btn { color: #2563eb; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 6px; }
.chevron { font-size: 14px; line-height: 1; transition: transform .2s; }

/* Premium Personnel Table (Redesigned) */
.personnel-table {
    width: 100%; border-collapse: collapse; border-spacing: 0;
}
.personnel-table thead tr { border-bottom: 1px solid #eef2f6; }
.personnel-table th {
    text-align: left; font-size: 11px; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.8px; color: #64748b;
    padding: 16px 20px; white-space: nowrap;
}
.personnel-table th.tc { text-align: center; }
.personnel-table tbody tr {
    transition: background 0.15s;
    border-bottom: 1px solid #f1f5f9;
}
.personnel-table tbody tr:hover { background: #f8fafc; }
.personnel-table td {
    padding: 16px 20px; vertical-align: middle;
    font-size: 14px; color: #334155;
}
.personnel-table td.tc { text-align: center; }
.personnel-table tbody tr:last-child { border-bottom: 0; }

.emp-name-text { font-weight: 600; color: #0f172a; font-size: 14px; }
.pos-title-text { font-weight: 600; color: #0f172a; font-size: 14px; margin-bottom: 2px; }
.pos-dept-text { font-size: 12px; color: #64748b; }
.num-text { font-size: 14px; font-weight: 600; color: #0f172a; }

/* Group rows */
.group-row td { background: #f8fafc; font-weight: 700; font-size: 13px; color: #1e293b; padding: 12px 20px; border-top: 2px solid #e2e8f0; }
/* ── Clean Report Table (Professional & Readable) ───────────────────────── */
.report-table {
    width: 100%; border-collapse: collapse; border-spacing: 0;
    font-size: 13px; color: #1f2937;
    border: 1px solid #cbd5e1;
}
.report-table thead tr {
    background-color: #f8fafc;
    border-top: 1px solid #cbd5e1;
    border-bottom: 2px solid #94a3b8;
}
.report-table th {
    text-align: left; font-weight: 700; color: #334155;
    padding: 12px 16px; white-space: nowrap; vertical-align: bottom;
    border-right: 1px solid #e2e8f0;
}
.report-table th:last-child { border-right: none; }
.report-table th.tc { text-align: center; }
.report-table th.tr { text-align: right; }
.report-table tbody tr {
    border-bottom: 1px solid #e2e8f0;
}
.report-table tbody tr:nth-child(even):not(.group-header) {
    background-color: #fbfbfc;
}
.report-table tbody tr:hover:not(.group-header) {
    background-color: #f1f5f9;
}
.report-table td {
    padding: 12px 16px; vertical-align: middle;
    border-right: 1px solid #e2e8f0;
}
.report-table td:last-child { border-right: none; }
.report-table td.tc { text-align: center; }
.report-table td.tr { text-align: right; }

/* Group Headers */
.report-table tr.group-header td {
    background-color: #e2e8f0;
    color: #0f172a; font-weight: 700;
    padding: 10px 16px;
    border-top: 2px solid #cbd5e1;
    border-right: none;
}
.report-table tr.group-sub-header td {
    background-color: #f1f5f9;
    color: #334155; font-weight: 600; font-style: italic;
    padding: 8px 16px; border-bottom: 1px solid #cbd5e1;
    border-right: none;
}
.report-table tr.footer-row td {
    font-weight: 700; background-color: #f8fafc;
    border-top: 2px solid #64748b; border-bottom: 2px solid #64748b;
    border-right: 1px solid #e2e8f0;
}
.report-table tr.footer-row td:last-child { border-right: none; }

/* Empty state */
.empty-state { text-align: center; padding: 30px 20px; color: #64748b; font-style: italic; }

/* Status Pills */
.status-pill {
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 600; padding: 4px 12px; border-radius: 99px; border: 1px solid transparent; text-transform: uppercase; letter-spacing: 0.5px;
}
.status-permanent { background: #eff6ff; color: #2563eb; border-color: #bfdbfe; }
.status-contractual { background: #fff7ed; color: #ea580c; border-color: #fed7aa; }
.status-probationary { background: #fefce8; color: #ca8a04; border-color: #fef08a; }
.status-elected { background: #f5f3ff; color: #7c3aed; border-color: #ddd6fe; }
.status-coterminous { background: #eef2ff; color: #4338ca; border-color: #c7d2fe; }
.status-danger { background: #fef2f2; color: #dc2626; border-color: #fecaca; }
.status-default { background: #f3f4f6; color: #4b5563; border-color: #d1d5db; }

/* Export Buttons */
.export-btn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 6px 13px; border-radius: 8px; font-size: 12px; font-weight: 600;
    text-decoration: none; border: 1px solid transparent;
    transition: opacity .15s, transform .1s;
    white-space: nowrap; line-height: 1;
}
.export-btn:hover { opacity: .82; transform: translateY(-1px); }
.export-btn-pdf  { background: #fff1f2; color: #dc2626; border-color: #fecaca; }
.export-btn-xlsx { background: #f0fdf4; color: #16a34a; border-color: #bbf7d0; }

/* Print adjustments */
@media print {
    .inv-hero, .filter-bar, .office-btn, .no-print { display: none !important; }
    .office-card > div { display: block !important; border: none !important; }
    .office-card { box-shadow: none; border: none; break-inside: avoid; margin-bottom: 30px; }
    .report-table { font-size: 11px; }
    .report-table th, .report-table td { padding: 8px 10px; }
    .report-table tbody tr:nth-child(even):not(.group-header) { background-color: #f8fafc !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .report-table tr.group-header td { background-color: #e2e8f0 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>

{{-- ▌▌ HERO ▌▌ --}}
<div class="inv-hero">
    <div class="inv-hero-inner">
        <div>
            <h1><i class="bi bi-bar-chart-fill me-2"></i>Personnel Statistical Reports</h1>
            <p>Provincial Government of Bukidnon — Official Personnel Reports</p>
        </div>
        <div style="display:flex;gap:12px;align-items:center;">
            <a href="{{ route('plantilla.index') }}" class="hero-btn">
                <i class="bi bi-arrow-left"></i> Back to Inventory
            </a>
            <button onclick="window.print()" class="hero-btn no-print" style="background: rgba(16,185,129,.22); border-color: rgba(16,185,129,.5);">
                <i class="bi bi-printer"></i> Print Reports
            </button>
        </div>
    </div>
</div>

{{-- ▌▌ DATE FILTER ▌▌ --}}
<div class="filter-bar no-print">
    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#9ca3af;margin-bottom:12px;">
        <i class="bi bi-calendar-check me-1"></i> Report Period
    </div>
    <form method="GET" action="{{ route('plantilla.reports') }}">
        <div class="filter-field filter-field-sm">
            <label>Year–Month</label>
            <input type="month" name="as_of" value="{{ $asOf }}" style="width:100%;">
        </div>
        <div style="display:flex;gap:8px;align-items:flex-end;">
            <button type="submit" style="background:#2563eb;color:#fff;border:none;padding:9px 20px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
                <i class="bi bi-arrow-clockwise me-1"></i> Update Reports
            </button>
        </div>
        <div style="margin-left:auto;font-size:13px;color:#64748b;align-self:center;">
            Data actively constrained up to <span style="font-weight:700;color:#0f172a;">{{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y') }}</span>
        </div>
    </form>
</div>

@php
function getCatPill($cat) {
    if(stripos($cat, 'permanent') !== false) return 'status-permanent';
    if(stripos($cat, 'casual') !== false) return 'status-contractual';
    if(stripos($cat, 'job order') !== false) return 'status-probationary';
    if(stripos($cat, 'elected') !== false) return 'status-elected';
    if(stripos($cat, 'coterminous') !== false || stripos($cat, 'co-terminous') !== false) return 'status-coterminous';
    return 'status-default';
}
@endphp

{{-- ─────────────────────────────────────────────────────────────────────────
     REPORT 1: Inventory by Office × Appointment Type
 ──────────────────────────────────────────────────────────────────────────── --}}
<div class="office-card">
    <button type="button" onclick="rptToggle('r1')" class="office-btn">
        <div class="off-left">
            <div class="off-icon-box" style="background: #eff6ff; color: #2563eb;">
                <i class="bi bi-1-square-fill"></i>
            </div>
            <div>
                <div class="off-name">Inventory of Provincial Government Personnel</div>
                <div class="off-sub">Count by office and appointment type</div>
            </div>
        </div>
        <div class="off-right no-print" style="gap:10px;">
            <div style="display:flex;gap:6px;">
                <a href="{{ route('plantilla.reports.export.pdf',   1) . '?as_of=' . $asOf }}" class="export-btn export-btn-pdf"  title="Export PDF">
                    <i class="bi bi-file-earmark-pdf-fill"></i> PDF
                </a>
                <a href="{{ route('plantilla.reports.export.excel', 1) . '?as_of=' . $asOf }}" class="export-btn export-btn-xlsx" title="Export Excel">
                    <i class="bi bi-file-earmark-excel-fill"></i> Excel
                </a>
            </div>
            <span class="off-view-btn">
                View Report <i class="bi bi-chevron-down chevron" id="r1-chev"></i>
            </span>
        </div>
    </button>
    <div id="r1" style="border-top:1px solid #f3f4f6; display: none;">
        <div class="overflow-x-auto p-4 max-w-full">
        <table class="report-table">
            <thead>
                <tr>
                    <th>OFFICE</th>
                    <th class="tc">REGULAR</th>
                    <th class="tc">Elected</th>
                    <th class="tc">Co-Terminous</th>
                    <th class="tc">Casual</th>
                    <th class="tc">Job Order</th>
                    <th class="tc" style="color:#0f172a; font-weight:800;">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report1 as $office => $counts)
                <tr>
                    <td style="font-weight: 600; max-width: 350px; white-space: normal; line-height: 1.3;">{{ $office }}</td>
                    <td class="tc">{{ $counts['Permanent']    ?: '—' }}</td>
                    <td class="tc">{{ $counts['Elected']      ?: '—' }}</td>
                    <td class="tc">{{ $counts['Co-Terminous'] ?: '—' }}</td>
                    <td class="tc">{{ $counts['Casual']       ?: '—' }}</td>
                    <td class="tc">{{ $counts['Job Order']    ?: '—' }}</td>
                    <td class="tc" style="color:#1d4ed8; font-weight:700;">{{ $counts['Total'] }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="empty-state">No active personnel records found.</td></tr>
                @endforelse
            </tbody>
            @if(count($report1) > 0)
            <tfoot>
                <tr class="footer-row">
                    <td>GRAND TOTAL</td>
                    @php
                    $totPerm = array_sum(array_column($report1, 'Permanent'));
                    $totElec = array_sum(array_column($report1, 'Elected'));
                    $totCT   = array_sum(array_column($report1, 'Co-Terminous'));
                    $totCas  = array_sum(array_column($report1, 'Casual'));
                    $totJO   = array_sum(array_column($report1, 'Job Order'));
                    $totAll  = array_sum(array_column($report1, 'Total'));
                    @endphp
                    <td class="tc">{{ $totPerm ?: '—' }}</td>
                    <td class="tc">{{ $totElec ?: '—' }}</td>
                    <td class="tc">{{ $totCT   ?: '—' }}</td>
                    <td class="tc">{{ $totCas  ?: '—' }}</td>
                    <td class="tc">{{ $totJO   ?: '—' }}</td>
                    <td class="tc" style="color:#1d4ed8; font-size:15px;">{{ $totAll }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
        </div>
    </div>
</div>

{{-- ─────────────────────────────────────────────────────────────────────────
     REPORT 2: Inventory by Status + Employee + SEX + Age
 ──────────────────────────────────────────────────────────────────────────── --}}
<div class="office-card">
    <button type="button" onclick="rptToggle('r2')" class="office-btn">
        <div class="off-left">
            <div class="off-icon-box" style="background: #fdf2f8; color: #db2777;">
                <i class="bi bi-2-square-fill"></i>
            </div>
            <div>
                <div class="off-name">Inventory of Personnel by Appointment Status</div>
                <div class="off-sub">With SEX & Age profiles for active filled positions</div>
            </div>
        </div>
        <div class="off-right no-print" style="gap:10px;">
            <div style="display:flex;gap:6px;">
                <a href="{{ route('plantilla.reports.export.pdf',   2) . '?as_of=' . $asOf }}" class="export-btn export-btn-pdf"  title="Export PDF">
                    <i class="bi bi-file-earmark-pdf-fill"></i> PDF
                </a>
                <a href="{{ route('plantilla.reports.export.excel', 2) . '?as_of=' . $asOf }}" class="export-btn export-btn-xlsx" title="Export Excel">
                    <i class="bi bi-file-earmark-excel-fill"></i> Excel
                </a>
            </div>
            <span class="off-view-btn" style="color:#db2777;">
                View Report <i class="bi bi-chevron-down chevron" id="r2-chev"></i>
            </span>
        </div>
    </button>
    <div id="r2" style="border-top:1px solid #f3f4f6; display: none;">
        <div class="overflow-x-auto p-4">
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width:50px;">#</th>
                    <th>Employee Name</th>
                    <th>Position Title</th>
                    <th class="tc">SEX</th>
                    <th class="tc">Age</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report2 as $cat => $items)
                <tr class="group-header">
                    <td colspan="5">
                        Status: <span style="text-transform:uppercase;">{{ $cat }}</span>
                        <span style="font-weight:400; color:#64748b; margin-left:6px;">({{ count($items) }} personnel)</span>
                    </td>
                </tr>
                @foreach($items as $idx => $item)
                @php $r = $item['record']; @endphp
                <tr>
                    <td style="color:#94a3b8;">{{ $idx + 1 }}</td>
                    <td style="font-weight:600;">{{ strtoupper($r->last_name ?? '') }}, {{ $r->first_name ?? '' }}</td>
                    <td>{{ $r->position_title }}</td>
                    <td class="tc">{{ $r->sex ?: '—' }}</td>
                    <td class="tc">{{ $item['age'] ?: '—' }}</td>
                </tr>
                @endforeach
                @empty
                <tr><td colspan="5" class="empty-state">No active personnel records found.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>

{{-- ─────────────────────────────────────────────────────────────────────────
     REPORT 3: Newly Hired / Promoted / Demoted
 ──────────────────────────────────────────────────────────────────────────── --}}
<div class="office-card">
    <button type="button" onclick="rptToggle('r3')" class="office-btn">
        <div class="off-left">
            <div class="off-icon-box" style="background: #ecfdf5; color: #059669;">
                <i class="bi bi-3-square-fill"></i>
            </div>
            <div>
                <div class="off-name">Newly Hired, Promoted, & Demoted Employees</div>
                <div class="off-sub">Appointment changes recorded in {{ $year }}</div>
            </div>
        </div>
        <div class="off-right no-print" style="gap:10px;">
            <div style="display:flex;gap:6px;">
                <a href="{{ route('plantilla.reports.export.pdf',   3) . '?as_of=' . $asOf }}" class="export-btn export-btn-pdf"  title="Export PDF">
                    <i class="bi bi-file-earmark-pdf-fill"></i> PDF
                </a>
                <a href="{{ route('plantilla.reports.export.excel', 3) . '?as_of=' . $asOf }}" class="export-btn export-btn-xlsx" title="Export Excel">
                    <i class="bi bi-file-earmark-excel-fill"></i> Excel
                </a>
            </div>
            <span class="off-view-btn" style="color:#059669;">
                View Report <i class="bi bi-chevron-down chevron" id="r3-chev"></i>
            </span>
        </div>
    </button>
    <div id="r3" style="border-top:1px solid #f3f4f6; display: none;">
        <div class="overflow-x-auto p-4">
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width:50px;">#</th>
                    <th>Employee Name</th>
                    <th>Position Title</th>
                    <th>Status / Category</th>
                    <th>Nature of Appt.</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report3->groupBy('office_department') as $office => $records)
                <tr class="group-header">
                    <td colspan="5">
                        Office: {{ $office }}
                        <span style="font-weight:400; color:#64748b; margin-left:6px;">({{ count($records) }} records)</span>
                    </td>
                </tr>
                @foreach($records as $idx => $r)
                <tr>
                    <td style="color:#94a3b8;">{{ $idx + 1 }}</td>
                    <td style="font-weight:600;">{{ strtoupper($r->last_name ?? '') }}, {{ $r->first_name ?? '' }}</td>
                    <td>{{ $r->position_title }}</td>
                    <td>{{ $r->resolved_category ?? $r->employment_status }}</td>
                    <td style="font-weight:600; color:#059669;">{{ $r->nature_of_appointment ?: 'N/A' }}</td>
                </tr>
                @endforeach
                @empty
                <tr><td colspan="5" class="empty-state">No newly hired, promoted, or demoted employees recorded for {{ $year }}.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>

{{-- ─────────────────────────────────────────────────────────────────────────
     REPORT 4: List of Retirees
 ──────────────────────────────────────────────────────────────────────────── --}}
<div class="office-card">
    <button type="button" onclick="rptToggle('r4')" class="office-btn">
        <div class="off-left">
            <div class="off-icon-box" style="background: #fef2f2; color: #dc2626;">
                <i class="bi bi-4-square-fill"></i>
            </div>
            <div>
                <div class="off-name">List of Retirees</div>
                <div class="off-sub">Employees who successfully retired in {{ $year }}</div>
            </div>
        </div>
        <div class="off-right no-print" style="gap:10px;">
            <div style="display:flex;gap:6px;">
                <a href="{{ route('plantilla.reports.export.pdf',   4) . '?as_of=' . $asOf }}" class="export-btn export-btn-pdf"  title="Export PDF">
                    <i class="bi bi-file-earmark-pdf-fill"></i> PDF
                </a>
                <a href="{{ route('plantilla.reports.export.excel', 4) . '?as_of=' . $asOf }}" class="export-btn export-btn-xlsx" title="Export Excel">
                    <i class="bi bi-file-earmark-excel-fill"></i> Excel
                </a>
            </div>
            <span class="off-view-btn" style="color:#dc2626;">
                View Report <i class="bi bi-chevron-down chevron" id="r4-chev"></i>
            </span>
        </div>
    </button>
    <div id="r4" style="border-top:1px solid #f3f4f6; display: none;">
        <div class="overflow-x-auto p-4">
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width:50px;">#</th>
                    <th>Employee Name</th>
                    <th>Position Title & Office</th>
                    <th class="tc">Date Retired</th>
                    <th class="tr">Years in Service</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report4 as $idx => $item)
                @php $r = $item['record']; $yrs = $item['years_in_service']; @endphp
                <tr>
                    <td style="color:#94a3b8;">{{ $idx + 1 }}</td>
                    <td style="font-weight:600;">{{ strtoupper($r->last_name ?? '') }}, {{ $r->first_name ?? '' }}</td>
                    <td>
                        <div style="font-weight:600;">{{ $r->position_title }}</div>
                        <div style="color:#64748b; font-size:12px; margin-top:2px;">{{ $r->office_department }}</div>
                    </td>
                    <td class="tc" style="color:#dc2626; font-weight:600;">
                        {{ $r->date_separated ? \Carbon\Carbon::parse($r->date_separated)->format('m/d/Y') : '—' }}
                    </td>
                    <td class="tr">{{ $yrs !== null ? $yrs . ' yrs' : '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="empty-state">No retirees recorded for {{ $year }}.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>

{{-- ─────────────────────────────────────────────────────────────────────────
     REPORT 5: Terminated / Separated Employees
 ──────────────────────────────────────────────────────────────────────────── --}}
<div class="office-card">
    <button type="button" onclick="rptToggle('r5')" class="office-btn">
        <div class="off-left">
            <div class="off-icon-box" style="background: #fffbeb; color: #d97706;">
                <i class="bi bi-5-square-fill"></i>
            </div>
            <div>
                <div class="off-name">Separated / Terminated Employees</div>
                <div class="off-sub">Employees who resigned, dropped, or ended contract in {{ $year }}</div>
            </div>
        </div>
        <div class="off-right no-print" style="gap:10px;">
            <div style="display:flex;gap:6px;">
                <a href="{{ route('plantilla.reports.export.pdf',   5) . '?as_of=' . $asOf }}" class="export-btn export-btn-pdf"  title="Export PDF">
                    <i class="bi bi-file-earmark-pdf-fill"></i> PDF
                </a>
                <a href="{{ route('plantilla.reports.export.excel', 5) . '?as_of=' . $asOf }}" class="export-btn export-btn-xlsx" title="Export Excel">
                    <i class="bi bi-file-earmark-excel-fill"></i> Excel
                </a>
            </div>
            <span class="off-view-btn" style="color:#d97706;">
                View Report <i class="bi bi-chevron-down chevron" id="r5-chev"></i>
            </span>
        </div>
    </button>
    <div id="r5" style="border-top:1px solid #f3f4f6; display: none;">
        <div class="overflow-x-auto p-4">
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width:50px;">#</th>
                    <th>Employee Name</th>
                    <th>Office / Unit</th>
                    <th class="tc">Date Effectivity</th>
                    <th>Nature of Separation</th>
                    <th>Basis Reference</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report5 as $idx => $r)
                <tr>
                    <td style="color:#94a3b8;">{{ $idx + 1 }}</td>
                    <td style="font-weight:600;">{{ strtoupper($r->last_name ?? '') }}, {{ $r->first_name ?? '' }}</td>
                    <td>
                        <div style="font-weight:600;">{{ $r->position_title }}</div>
                        <div style="color:#64748b; font-size:12px; margin-top:2px;">{{ $r->office_department }}</div>
                    </td>
                    <td class="tc">
                        {{ $r->date_separated ? \Carbon\Carbon::parse($r->date_separated)->format('m/d/Y') : '—' }}
                    </td>
                    <td style="font-weight:600; color:#b45309;">{{ $r->nature_of_separation ?: 'N/A' }}</td>
                    <td>{{ $r->basis_reference ?: '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="empty-state">No separation records for {{ $year }}.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>

{{-- ─────────────────────────────────────────────────────────────────────────
     REPORT 6: Report by Employment Status per Office
 ──────────────────────────────────────────────────────────────────────────── --}}
<div class="office-card">
    <button type="button" onclick="rptToggle('r6')" class="office-btn">
        <div class="off-left">
            <div class="off-icon-box" style="background: #f5f3ff; color: #6d28d9;">
                <i class="bi bi-6-square-fill"></i>
            </div>
            <div>
                <div class="off-name">Report by Employment Status per Office</div>
                <div class="off-sub">Grouped by active office assignments and categories</div>
            </div>
        </div>
        <div class="off-right no-print" style="gap:10px;">
            <div style="display:flex;gap:6px;">
                <a href="{{ route('plantilla.reports.export.pdf',   6) . '?as_of=' . $asOf }}" class="export-btn export-btn-pdf"  title="Export PDF">
                    <i class="bi bi-file-earmark-pdf-fill"></i> PDF
                </a>
                <a href="{{ route('plantilla.reports.export.excel', 6) . '?as_of=' . $asOf }}" class="export-btn export-btn-xlsx" title="Export Excel">
                    <i class="bi bi-file-earmark-excel-fill"></i> Excel
                </a>
            </div>
            <span class="off-view-btn" style="color:#6d28d9;">
                View Report <i class="bi bi-chevron-down chevron" id="r6-chev"></i>
            </span>
        </div>
    </button>
    <div id="r6" style="border-top:1px solid #f3f4f6; display: none;">
        <div class="overflow-x-auto p-4">
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width:50px;">#</th>
                    <th>Employee Name</th>
                    <th>Position Title</th>
                    <th class="tc">SEX</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report6 as $office => $catGroups)
                <tr class="group-header">
                    <td colspan="4" style="background:#334155; color:#fff; border-top:none; text-transform:uppercase; letter-spacing:1px; font-size:12px;">
                        Office: {{ $office }}
                    </td>
                </tr>
                @foreach($catGroups as $cat => $records)
                <tr class="group-sub-header">
                    <td colspan="4">
                        Status: <span style="text-transform:uppercase; font-style:normal; font-weight:700;">{{ $cat }}</span>
                        <span style="font-size:12px; font-weight:400; color:#64748b; margin-left:6px;">({{ count($records) }} records)</span>
                    </td>
                </tr>
                @foreach($records as $idx => $r)
                <tr>
                    <td style="color:#94a3b8;">{{ $idx + 1 }}</td>
                    <td style="font-weight:600;">{{ strtoupper($r->last_name ?? '') }}, {{ $r->first_name ?? '' }}</td>
                    <td>{{ $r->position_title }}</td>
                    <td class="tc">{{ $r->sex ?: '—' }}</td>
                </tr>
                @endforeach
                @endforeach
                @empty
                <tr><td colspan="4" class="empty-state">No active personnel records found.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>

{{-- ─────────────────────────────────────────────────────────────────────────
     REPORT 7: Custom Status Report
 ──────────────────────────────────────────────────────────────────────────── --}}
<div class="office-card" id="r7-container">
    <button type="button" onclick="rptToggle('r7')" class="office-btn">
        <div class="off-left">
            <div class="off-icon-box" style="background: #eef2ff; color: #4338ca;">
                <i class="bi bi-7-square-fill"></i>
            </div>
            <div>
                <div class="off-name">Custom Status Report</div>
                <div class="off-sub">Dynamically generate reports by status and period</div>
            </div>
        </div>
        <div class="off-right no-print" style="gap:10px;">
            @if($r7_status)
            <div style="display:flex;gap:6px;">
                <a href="{{ route('plantilla.reports.custom.pdf', ['status' => $r7_status, 'period' => $r7_period, 'from' => $r7_from, 'to' => $r7_to, 'asof' => $r7_asof]) }}" class="export-btn export-btn-pdf"  title="Export PDF">
                    <i class="bi bi-file-earmark-pdf-fill"></i> PDF
                </a>
                <a href="{{ route('plantilla.reports.custom.excel', ['status' => $r7_status, 'period' => $r7_period, 'from' => $r7_from, 'to' => $r7_to, 'asof' => $r7_asof]) }}" class="export-btn export-btn-xlsx" title="Export Excel">
                    <i class="bi bi-file-earmark-excel-fill"></i> Excel
                </a>
            </div>
            @endif
            <span class="off-view-btn" style="color:#4338ca;">
                View Report <i class="bi bi-chevron-down chevron" id="r7-chev"></i>
            </span>
        </div>
    </button>
    <div id="r7" style="border-top:1px solid #f3f4f6; display: {{ $r7_status ? 'block' : 'none' }};">
        <div class="p-4 bg-gray-50 border-b border-gray-200">
            <form method="GET" action="{{ route('plantilla.reports') }}#r7-container" style="display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end;">
                <input type="hidden" name="as_of" value="{{ $asOf }}">
                <div class="filter-field">
                    <label>Status / Category</label>
                    <select name="r7_status" required style="width: 200px;">
                        <option value="">Select Status...</option>
                        <option value="Newly Hired" {{ $r7_status == 'Newly Hired' ? 'selected' : '' }}>Newly Hired</option>
                        <option value="Promoted" {{ $r7_status == 'Promoted' ? 'selected' : '' }}>Promoted</option>
                        <option value="Terminated" {{ $r7_status == 'Terminated' ? 'selected' : '' }}>Terminated / Separated</option>
                        <option value="Retired" {{ $r7_status == 'Retired' ? 'selected' : '' }}>Retired</option>
                    </select>
                </div>
                
                <div class="filter-field">
                    <label>Period Type</label>
                    <select name="r7_period" id="r7_period_select" onchange="toggleR7Period()" style="width: 150px;">
                        <option value="range" {{ $r7_period == 'range' ? 'selected' : '' }}>Date Range (From-To)</option>
                        <option value="asof" {{ $r7_period == 'asof' ? 'selected' : '' }}>As Of Date</option>
                    </select>
                </div>

                <div class="filter-field" id="r7_from_div" style="display: {{ $r7_period == 'range' ? 'block' : 'none' }};">
                    <label>From Date</label>
                    <input type="date" name="r7_from" value="{{ $r7_from }}">
                </div>

                <div class="filter-field" id="r7_to_div" style="display: {{ $r7_period == 'range' ? 'block' : 'none' }};">
                    <label>To Date</label>
                    <input type="date" name="r7_to" value="{{ $r7_to }}">
                </div>

                <div class="filter-field" id="r7_asof_div" style="display: {{ $r7_period == 'asof' ? 'block' : 'none' }};">
                    <label>As Of Date</label>
                    <input type="date" name="r7_asof" value="{{ $r7_asof }}">
                </div>

                <button type="submit" style="background:#4338ca;color:#fff;border:none;padding:9px 20px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
                    <i class="bi bi-funnel-fill me-1"></i> Generate
                </button>
                @if($r7_status)
                <a href="{{ route('plantilla.reports') }}#r7-container" style="background:#f3f4f6;color:#4b5563;border:1px solid #d1d5db;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;">
                    Clear
                </a>
                @endif
            </form>
        </div>
        
        @if($r7_status)
        <div class="overflow-x-auto p-4">
            <table class="report-table">
                <thead>
                    <tr>
                        <th style="width:50px;">#</th>
                        <th>Employee Name</th>
                        <th>Position Title & Office</th>
                        <th class="tc">Date Effective</th>
                        <th>Status Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($report7 as $idx => $r)
                    <tr>
                        <td style="color:#94a3b8;">{{ $idx + 1 }}</td>
                        <td style="font-weight:600;">{{ strtoupper($r->last_name ?? '') }}, {{ $r->first_name ?? '' }}</td>
                        <td>
                            <div style="font-weight:600;">{{ $r->position_title }}</div>
                            <div style="color:#64748b; font-size:12px; margin-top:2px;">{{ $r->office_department }}</div>
                        </td>
                        <td class="tc" style="font-weight:600; color:#4338ca;">
                            @if($r7_status === 'Newly Hired')
                                {{ $r->date_original_appointment ? \Carbon\Carbon::parse($r->date_original_appointment)->format('m/d/Y') : '—' }}
                            @elseif($r7_status === 'Promoted')
                                {{ $r->date_last_promotion ? \Carbon\Carbon::parse($r->date_last_promotion)->format('m/d/Y') : '—' }}
                            @elseif($r7_status === 'Retired' || $r7_status === 'Terminated')
                                {{ $r->date_separated ? \Carbon\Carbon::parse($r->date_separated)->format('m/d/Y') : '—' }}
                            @endif
                        </td>
                        <td style="font-weight:600; color:#0f172a;">
                            @if($r7_status === 'Newly Hired' || $r7_status === 'Promoted')
                                {{ $r->nature_of_appointment ?: 'N/A' }}
                            @else
                                {{ $r->nature_of_separation ?: 'N/A' }}
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="empty-state">No records found for the selected criteria.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @else
        <div class="p-8 text-center text-gray-400" style="font-size:14px; font-style:italic;">
            Please select a Status and Period above, then click Generate to view the custom report.
        </div>
        @endif
    </div>
</div>

{{-- ─────────────────────────────────────────────────────────────────────────
     REPORT 8: Detailed Employees (Enhancement Spec Sec. 5)
 ──────────────────────────────────────────────────────────────────────────── --}}
<div class="office-card">
    <button type="button" onclick="rptToggle('r8')" class="office-btn">
        <div class="off-left">
            <div class="off-icon-box" style="background: #eff6ff; color: #1d4ed8;">
                <i class="bi bi-8-square-fill"></i>
            </div>
            <div>
                <div class="off-name">Detailed Employees</div>
                <div class="off-sub">Employees currently on detail to another unit</div>
            </div>
        </div>
        <div class="off-right no-print" style="gap:10px;">
            <div style="display:flex;gap:6px;">
                <a href="{{ route('plantilla.reports.export.pdf', 8) . '?as_of=' . $asOf . ($r8_include_recalled ? '&r8_include_recalled=1' : '') }}" class="export-btn export-btn-pdf" title="Export PDF">
                    <i class="bi bi-file-earmark-pdf-fill"></i> PDF
                </a>
                <a href="{{ route('plantilla.reports.export.excel', 8) . '?as_of=' . $asOf . ($r8_include_recalled ? '&r8_include_recalled=1' : '') }}" class="export-btn export-btn-xlsx" title="Export Excel">
                    <i class="bi bi-file-earmark-excel-fill"></i> Excel
                </a>
            </div>
            <span class="off-view-btn" style="color:#1d4ed8;">
                View Report <i class="bi bi-chevron-down chevron" id="r8-chev"></i>
            </span>
        </div>
    </button>
    <div id="r8" style="border-top:1px solid #f3f4f6; display: none;">
        <div class="p-4 no-print" style="border-bottom:1px solid #f3f4f6;">
            <form method="GET" style="display:flex; align-items:center; gap:8px;">
                <input type="hidden" name="as_of" value="{{ $asOf }}">
                <label style="font-size:13px; color:#374151; display:flex; align-items:center; gap:6px;">
                    <input type="checkbox" name="r8_include_recalled" value="1" onchange="this.form.submit()" {{ $r8_include_recalled ? 'checked' : '' }}>
                    Include Recalled / Historical (for COA audit trail)
                </label>
            </form>
        </div>
        <div class="overflow-x-auto p-4">
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width:50px;">#</th>
                    <th>Employee Name</th>
                    <th>Detailed Unit</th>
                    <th class="tc">Date of Movement</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report8 as $idx => $order)
                <tr>
                    <td style="color:#94a3b8;">{{ $idx + 1 }}</td>
                    <td style="font-weight:600;">{{ strtoupper($order->plantillaRecord?->last_name ?? '') }}, {{ $order->plantillaRecord?->first_name ?? '' }}</td>
                    <td>{{ $order->detailed_unit }}</td>
                    <td class="tc">{{ $order->date_effective_start->format('m/d/Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="empty-state">No detail orders to report.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>

<script>
function rptToggle(id) {
    const body = document.getElementById(id);
    const chev = document.getElementById(id + '-chev');
    const open = body.style.display === 'block';
    body.style.display = open ? 'none' : 'block';
    if(chev) chev.style.transform = open ? 'rotate(0deg)' : 'rotate(180deg)';
}
// Open Report 1 by default
document.addEventListener('DOMContentLoaded', () => {
    // Only toggle r1 if r7 isn't active
    if (!'{{ $r7_status }}') {
        rptToggle('r1');
    }
});

function toggleR7Period() {
    const val = document.getElementById('r7_period_select').value;
    document.getElementById('r7_from_div').style.display = (val === 'range') ? 'block' : 'none';
    document.getElementById('r7_to_div').style.display = (val === 'range') ? 'block' : 'none';
    document.getElementById('r7_asof_div').style.display = (val === 'asof') ? 'block' : 'none';
}
</script>
</x-dashboard-app>
