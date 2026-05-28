<x-dashboard-app>
<style>
    /* ── Hero ────────────────────────────────────────────────────── */
    .vf-hero {
        background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 55%, #2563eb 100%);
        border-radius: 14px;
        padding: 24px 28px;
        position: relative;
        overflow: hidden;
        margin-bottom: 20px;
    }
    .vf-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: radial-gradient(circle, rgba(255,255,255,.07) 1px, transparent 1px);
        background-size: 22px 22px;
    }
    .vf-hero-inner {
        position: relative; z-index: 1;
        display: flex; align-items: center; justify-content: space-between;
        gap: 16px; flex-wrap: wrap;
    }
    .vf-hero h1 { color: #fff; font-size: 20px; font-weight: 800; margin: 0; }
    .vf-hero .pos-name { color: #93c5fd; font-size: 14px; font-weight: 600; margin: 4px 0 0; }
    .vf-hero .sub { color: rgba(255,255,255,.6); font-size: 12px; margin: 2px 0 0; }

    .vf-back-btn {
        display: inline-flex; align-items: center; gap: 6px;
        background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.3);
        color: #fff; padding: 8px 16px; border-radius: 9px;
        font-size: 12px; font-weight: 600; text-decoration: none;
        transition: background .18s;
    }
    .vf-back-btn:hover { background: rgba(255,255,255,.25); color: #fff; }

    .export-btn {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 8px 16px; border-radius: 9px;
        font-size: 12px; font-weight: 700;
        text-decoration: none; transition: opacity .15s, transform .15s;
        border: none; cursor: pointer; white-space: nowrap;
    }
    .export-btn:hover { opacity: .88; transform: translateY(-1px); }
    .export-btn.excel { background: #16a34a; color: #fff; }
    .export-btn.pdf   { background: #dc2626; color: #fff; }

    /* ── Summary cards ───────────────────────────────────────────── */
    .vf-summary { display: flex; gap: 14px; margin-bottom: 20px; flex-wrap: wrap; }
    .vf-stat {
        flex: 1; min-width: 160px;
        background: #fff; border: 1px solid #e5e7eb;
        border-radius: 12px; padding: 18px 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,.04);
    }
    .vf-stat-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .7px; color: #6b7280; margin-bottom: 6px; }
    .vf-stat-value { font-size: 28px; font-weight: 900; color: #1e3a8a; line-height: 1; }
    .vf-stat-sub   { font-size: 11px; color: #9ca3af; margin-top: 4px; }

    /* ── Table ───────────────────────────────────────────────────── */
    .vf-table-wrap {
        background: #fff; border: 1px solid #e5e7eb;
        border-radius: 12px; overflow: hidden;
        box-shadow: 0 1px 4px rgba(0,0,0,.05);
    }
    .vf-table-header {
        padding: 14px 20px; border-bottom: 1px solid #f3f4f6;
        display: flex; align-items: center; justify-content: space-between;
        background: linear-gradient(135deg, #eff6ff, #dbeafe);
    }
    .vf-table-header-title {
        font-size: 14px; font-weight: 700; color: #1e40af;
        display: flex; align-items: center; gap: 8px;
    }
    .vf-table-scroll { overflow-x: auto; }
    .vf-table {
        width: 100%; border-collapse: collapse; min-width: 720px;
    }
    .vf-table thead tr {
        background: linear-gradient(135deg, #1e3a8a, #1d4ed8);
    }
    .vf-table th {
        padding: 10px 14px; text-align: left;
        font-size: 10px; font-weight: 800; text-transform: uppercase;
        letter-spacing: .7px; color: rgba(255,255,255,.8);
        border-right: 1px solid rgba(255,255,255,.1); white-space: nowrap;
    }
    .vf-table th:last-child { border-right: 0; }
    .vf-table tbody tr:nth-child(even) td { background: #f0f7ff; }
    .vf-table tbody tr:hover td { background: #dbeafe !important; transition: background .1s; }
    .vf-table td {
        padding: 11px 14px; font-size: 12.5px; color: #374151;
        border-bottom: 1px solid #f1f5f9; border-right: 1px solid #f1f5f9;
        white-space: nowrap;
    }
    .vf-table td:last-child { border-right: 0; }
    .vf-table tbody tr:last-child td { border-bottom: 0; }
    .item-code { font-family: ui-monospace, monospace; color: #1d4ed8; font-weight: 600; font-size: 11px; }
    .sg-badge {
        display: inline-flex; align-items: center; justify-content: center;
        background: #dbeafe; color: #1e40af;
        font-size: 11px; font-weight: 800; padding: 2px 8px;
        border-radius: 99px; white-space: nowrap;
    }
    .salary-cell { text-align: right; font-family: ui-monospace, monospace; font-size: 11.5px; color: #065f46; font-weight: 700; }
    .office-cell { max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    .vf-empty {
        text-align: center; padding: 60px 20px; color: #9ca3af;
    }
    .vf-empty-icon {
        font-size: 42px; margin-bottom: 12px;
        background: #eff6ff; width: 72px; height: 72px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        color: #93c5fd;
    }

    /* ── Total row ───────────────────────────────────────────────── */
    .vf-total-row td {
        background: #dbeafe !important;
        font-weight: 800; color: #1e40af;
        border-top: 2px solid #1d4ed8;
    }
</style>

{{-- Hero --}}
<div class="vf-hero">
    <div class="vf-hero-inner">
        <div>
            <h1><i class="bi bi-building-add me-2"></i>Vacant Funded — Position Detail</h1>
            <div class="pos-name">{{ $position ?: 'All Positions' }}</div>
            <div class="sub">{{ $records->count() }} slot(s) currently open</div>
        </div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <a href="{{ route('plantilla.vacant-funded.detail.export.excel', ['position' => $position]) }}"
               class="export-btn excel">
                <i class="bi bi-file-earmark-excel-fill"></i> Export Excel
            </a>
            <a href="{{ route('plantilla.vacant-funded.detail.export.pdf', ['position' => $position]) }}"
               class="export-btn pdf" target="_blank">
                <i class="bi bi-file-earmark-pdf-fill"></i> Export PDF
            </a>
            <a href="{{ route('plantilla.index') }}" class="vf-back-btn">
                <i class="bi bi-arrow-left"></i> Back to Inventory
            </a>
        </div>
    </div>
</div>

{{-- Summary Cards --}}
@php
    $totalSlots   = $records->count();
    $totalSalary  = $records->sum('authorized_annual_salary');
    $officeCount  = $records->pluck('organizational_unit')->filter()->unique()->count();
    $avgSalary    = $totalSlots ? $totalSalary / $totalSlots : 0;
    $sgList       = $records->pluck('salary_grade')->filter()->unique()->sort()->values();
@endphp
<div class="vf-summary">
    <div class="vf-stat">
        <div class="vf-stat-label">Open Slots</div>
        <div class="vf-stat-value">{{ number_format($totalSlots) }}</div>
        <div class="vf-stat-sub">Vacant funded positions</div>
    </div>
    <div class="vf-stat">
        <div class="vf-stat-label">Offices Affected</div>
        <div class="vf-stat-value">{{ number_format($officeCount) }}</div>
        <div class="vf-stat-sub">Unique org. units</div>
    </div>
    <div class="vf-stat">
        <div class="vf-stat-label">Total Auth. Annual Salary</div>
        <div class="vf-stat-value" style="font-size:20px;">₱{{ number_format($totalSalary, 0) }}</div>
        <div class="vf-stat-sub">Combined authorized salary</div>
    </div>
    <div class="vf-stat">
        <div class="vf-stat-label">Salary Grade(s)</div>
        <div class="vf-stat-value" style="font-size:20px;">
            {{ $sgList->isEmpty() ? '—' : $sgList->implode(', ') }}
        </div>
        <div class="vf-stat-sub">Assigned grades</div>
    </div>
</div>

{{-- Table --}}
<div class="vf-table-wrap">
    <div class="vf-table-header">
        <div class="vf-table-header-title">
            <i class="bi bi-table"></i>
            All Open Slots for "{{ $position }}"
        </div>
        <span style="font-size:12px;color:#3b82f6;font-weight:600;">
            {{ number_format($totalSlots) }} record(s)
        </span>
    </div>
    <div class="vf-table-scroll">
        <table class="vf-table">
            <thead>
                <tr>
                    <th style="width:42px;">#</th>
                    <th>Item No.</th>
                    <th>Organizational Unit</th>
                    <th>SG</th>
                    <th>Step</th>
                    <th>Auth. Annual Salary</th>
                    <th>Area Code</th>
                    <th>Area Type</th>
                    <th>Level</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $i => $r)
                    <tr>
                        <td style="color:#9ca3af;font-size:10px;font-weight:600;">{{ $i + 1 }}</td>
                        <td><span class="item-code">{{ $r->item }}</span></td>
                        <td class="office-cell" title="{{ $r->organizational_unit }}">
                            {{ $r->organizational_unit ?: '—' }}
                        </td>
                        <td><span class="sg-badge">SG-{{ $r->salary_grade }}</span></td>
                        <td style="text-align:center;font-weight:600;">{{ $r->step ?: '—' }}</td>
                        <td class="salary-cell">
                            @if($r->authorized_annual_salary)
                                ₱{{ number_format($r->authorized_annual_salary, 2) }}
                            @else
                                <span style="color:#d1d5db;">—</span>
                            @endif
                        </td>
                        <td>{{ $r->area_code ?: '—' }}</td>
                        <td>{{ $r->area_type ?: '—' }}</td>
                        <td>{{ $r->level ?: '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            <div class="vf-empty">
                                <div class="vf-empty-icon"><i class="bi bi-inbox"></i></div>
                                <div style="font-size:14px;font-weight:600;color:#374151;margin-bottom:6px;">No Records Found</div>
                                <div style="font-size:12px;">No vacant funded positions match this title.</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
                @if($records->isNotEmpty())
                    <tr class="vf-total-row">
                        <td colspan="5" style="text-align:right;font-size:11px;">TOTAL</td>
                        <td class="salary-cell" style="color:#1e40af;">₱{{ number_format($totalSalary, 2) }}</td>
                        <td colspan="3"></td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
</x-dashboard-app>
