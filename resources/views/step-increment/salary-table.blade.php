<x-dashboard-app>
<style>
/* ── Salary Table Page ──────────────────────────────────────────────── */
.sg-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #1a5276 55%, #0d6efd 100%);
    border-radius: 14px; padding: 26px 32px;
    position: relative; overflow: hidden; margin-bottom: 22px;
}
.sg-hero::before {
    content: ''; position: absolute; inset: 0;
    background-image: radial-gradient(circle, rgba(255,255,255,.07) 1px, transparent 1px);
    background-size: 22px 22px;
}
.sg-hero-inner { position: relative; z-index: 1; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
.sg-hero h1   { color: #fff; font-size: 24px; font-weight: 800; margin: 0; line-height: 1.2; }
.sg-hero p    { color: rgba(255,255,255,.65); font-size: 13px; margin: 5px 0 0; }
.sg-hero-badge {
    display: inline-flex; align-items: center; gap: 7px;
    background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.28);
    color: #fff; padding: 7px 16px; border-radius: 99px;
    font-size: 12px; font-weight: 700;
}
.sg-back-btn {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.3);
    color: #fff; padding: 8px 16px; border-radius: 10px;
    font-size: 13px; font-weight: 600; text-decoration: none;
    transition: background .2s;
}
.sg-back-btn:hover { background: rgba(255,255,255,.28); color: #fff; }

/* Legend / info strip */
.sg-info-strip {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 10px;
    background: var(--color-surface, #fff); border: 1px solid #e2e8f0;
    border-radius: 10px; padding: 12px 20px; margin-bottom: 16px;
    font-size: 12px; color: var(--color-text-muted, #64748b);
}
.sg-info-strip b { color: #1e3a5f; }

/* Matrix table wrapper */
.sg-table-wrap {
    background: var(--color-surface, #fff); border: 1px solid #e2e8f0;
    border-radius: 14px; overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,.05);
}
.sg-table-scroll { overflow-x: auto; }
.sg-table { width: 100%; border-collapse: separate; border-spacing: 0; }

/* Header row */
.sg-table thead tr {
    background: linear-gradient(135deg, #1e3a5f 0%, #1a5276 100%);
}
.sg-table th {
    padding: 13px 18px; font-size: 11px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .8px;
    color: rgba(255,255,255,.75); white-space: nowrap;
    border-right: 1px solid rgba(255,255,255,.1);
}
.sg-table th:first-child { text-align: left; min-width: 70px; border-radius: 14px 0 0 0; }
.sg-table th:last-child  { border-right: 0; border-radius: 0 14px 0 0; }
.sg-table th.step-head   { text-align: right; min-width: 115px; }

/* Body rows */
.sg-table tbody tr { transition: background .1s; }
.sg-table tbody tr:nth-child(even) td { background: var(--color-page-bg, #f8fafc); }
.sg-table tbody tr:hover td           { background: #eff6ff !important; }
.sg-table td {
    padding: 10px 18px; vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
    border-right: 1px solid #f1f5f9;
    font-size: 13px; color: #374151;
}
.sg-table td:last-child  { border-right: 0; }
.sg-table tbody tr:last-child td { border-bottom: 0; }

/* Grade label cell */
.sg-grade-cell {
    font-weight: 800; text-align: center; white-space: nowrap;
}
.sg-grade-pill {
    display: inline-flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, #1e3a5f, #1a5276);
    color: #fff; font-size: 11px; font-weight: 800;
    width: 36px; height: 36px; border-radius: 10px;
    box-shadow: 0 2px 8px rgba(30,58,95,.2);
}

/* Salary cells */
.sg-salary {
    text-align: right; font-family: ui-monospace, monospace;
    font-size: 13px; color: var(--color-text-primary, #1e293b); white-space: nowrap;
}
.sg-salary-annual {
    display: block; font-size: 10px; color: #94a3b8; margin-top: 2px;
}

/* Step-1 highlight */
.sg-table td.step-1 { background: #f0f9ff; }
.sg-table tbody tr:hover td.step-1 { background: #eff6ff !important; }

/* Footer */
.sg-footer {
    padding: 12px 20px; background: var(--color-page-bg, #f8fafc);
    border-top: 1px solid #f1f5f9;
    font-size: 11px; color: #94a3b8; text-align: center;
}
</style>

{{-- ▌▌ HERO ▌▌ --}}
<div class="sg-hero">
    <div class="sg-hero-inner">
        <div>
            <h1><i class="bi bi-table me-2"></i>Salary Grade Schedule</h1>
            <p>Second Tranche Monthly Salary Schedule — Effective January 1, 2025</p>
        </div>
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <span class="sg-hero-badge">
                <i class="bi bi-calendar-check"></i> Annex A · DBM Circular
            </span>
            <a href="{{ route('step-increment.index') }}" class="sg-back-btn">
                <i class="bi bi-arrow-left"></i> Back to Step Increment
            </a>
        </div>
    </div>
</div>

{{-- ▌▌ INFO STRIP ▌▌ --}}
<div class="sg-info-strip">
    <span><i class="bi bi-info-circle me-1" style="color:#3b82f6;"></i>
        Showing <b>all {{ count($matrix) }} Salary Grades</b> with monthly and annual (×12) rates.
        SG&nbsp;33 has only Steps 1–2.
    </span>
    <span style="font-size:11px;">
        <i class="bi bi-currency-exchange me-1"></i> Values in <b>Philippine Pesos (₱)</b>
    </span>
</div>

{{-- ▌▌ MATRIX TABLE ▌▌ --}}
<div class="sg-table-wrap">
    <div class="sg-table-scroll">
        <table class="sg-table">
            <thead>
                <tr>
                    <th>SG</th>
                    @for($s = 1; $s <= 8; $s++)
                    <th class="step-head">Step {{ $s }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @foreach($matrix as $grade => $steps)
                <tr>
                    <td class="sg-grade-cell">
                        <span class="sg-grade-pill">{{ $grade }}</span>
                    </td>
                    @for($s = 1; $s <= 8; $s++)
                    @php $monthly = $steps[$s] ?? null; @endphp
                    <td class="sg-salary{{ $s === 1 ? ' step-1' : '' }}">
                        @if($monthly)
                            ₱{{ number_format($monthly) }}
                            <span class="sg-salary-annual">₱{{ number_format($monthly * 12) }}/yr</span>
                        @else
                            <span style="color:#cbd5e1;">—</span>
                        @endif
                    </td>
                    @endfor
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="sg-footer">
        Second Tranche Monthly Salary Schedule for Civilian Personnel of the National Government
        — Effective January 1, 2025 &nbsp;|&nbsp; Per DBM Circular, Annex A
    </div>
</div>
</x-dashboard-app>
