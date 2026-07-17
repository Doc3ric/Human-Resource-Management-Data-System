<x-dashboard-app>
<style>
.si-hero {
    background: linear-gradient(135deg, #b45309 0%, #d97706 55%, #f59e0b 100%);
    border-radius: 14px; padding: 28px 32px;
    position: relative; overflow: hidden; margin-bottom: 20px;
}
.si-hero::before {
    content: ''; position: absolute; inset: 0;
    background-image: radial-gradient(circle, rgba(255,255,255,.07) 1px, transparent 1px);
    background-size: 22px 22px;
}
.si-hero-inner { position: relative; z-index: 1; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; }
.si-hero h1   { color: #fff; font-size: 24px; font-weight: 800; margin: 0; line-height: 1.2; }
.si-hero p    { color: rgba(255,255,255,.8); font-size: 13px; margin: 5px 0 0; }

.si-hero-btn {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,.2); border: 1px solid rgba(255,255,255,.3);
    color: #fff; padding: 9px 18px; border-radius: 10px;
    font-size: 13px; font-weight: 600; text-decoration: none;
    transition: background .2s;
}
.si-hero-btn:hover { background: rgba(255,255,255,.3); color: #fff; }

.si-table-wrap {
    background: var(--color-surface, #fff); border: 1px solid var(--color-border, #e5e7eb); border-radius: 14px;
    overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.04);
}
.si-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.si-table thead tr {
    background: linear-gradient(135deg, #312e81 0%, #4338ca 100%);
}
.si-table th {
    text-align: left; font-size: 10px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .8px;
    color: rgba(255,255,255,.72); padding: 12px 16px; white-space: nowrap;
}
.si-table tbody tr { transition: background .12s; }
.si-table tbody tr:nth-child(even) td { background: var(--color-page-bg, #f8fafc); }
.si-table tbody tr:hover td { background: #eef2ff !important; }
.si-table td {
    padding: 11px 16px; vertical-align: middle;
    border-bottom: 1px solid #f1f5f9; font-size: 13px; color: #374151;
}
.si-table tbody tr:last-child td { border-bottom: 0; }

.history-type-NOSI { background: #e0f2fe; color: #0284c7; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 11px; }
.history-type-NOLP { background: #ede9fe; color: #5b21b6; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 11px; }
.history-type-NOSA { background: #ffe4e6; color: #e11d48; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 11px; }
.history-type-SSL_ADJUSTMENT { background: #f5f3ff; color: #6d28d9; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 11px; }

.progress-arrow { color: #94a3b8; margin: 0 6px; font-size: 11px; }
</style>

<div class="si-hero">
    <div class="si-hero-inner">
        <div>
            <h1><i class="bi bi-clock-history me-2"></i>History of Increments</h1>
            <p>A comprehensive log of all NOSI, NOLP, and NOSA issuances across all personnel</p>
        </div>
        <div>
            <a href="{{ route('step-increment.index') }}" class="si-hero-btn">
                <i class="bi bi-arrow-left"></i> Back to Processing
            </a>
        </div>
    </div>
</div>

<div class="si-table-wrap">
    <div style="padding: 16px; background: var(--color-surface, #fff); border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <form method="GET" action="{{ route('step-increment.history') }}" style="display:flex; align-items:center; gap:8px;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or item number..." 
                   style="border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px 14px; font-size: 13px; width: 260px; outline: none; transition: border-color .2s;">
            <button type="submit" style="background: #3b82f6; color: white; border: none; border-radius: 8px; padding: 8px 16px; font-size: 13px; font-weight: 600; cursor:pointer;">
                <i class="bi bi-search"></i> Search
            </button>
            @if(request('search'))
            <a href="{{ route('step-increment.history') }}" style="color:#ef4444; font-size: 13px; text-decoration: none; margin-left: 6px;"><i class="bi bi-x-circle-fill"></i> Clear</a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="si-table">
            <thead>
                <tr>
                    <th style="width: 120px;">Effective Date</th>
                    <th>Employee</th>
                    <th style="width: 90px; text-align:center;">Type</th>
                    <th style="width: 140px; text-align:center;">Salary Grade</th>
                    <th style="width: 140px; text-align:center;">Step</th>
                    <th style="width: 200px; text-align:right;">Annual Salary Change</th>
                </tr>
            </thead>
            <tbody>
                @forelse($histories as $log)
                <tr>
                    <td style="font-weight: 600; color: var(--color-text-primary, #1e293b);">
                        {{ $log->effective_date ? $log->effective_date->format('M d, Y') : '—' }}
                    </td>
                    <td>
                        @if($log->plantillaRecord)
                            <div style="font-weight: 700; color: #0f172a; font-size: 13px;">{{ $log->plantillaRecord->full_name }}</div>
                            <div style="font-size: 11px; color: var(--color-text-muted, #64748b); margin-top: 2px;">
                                Item No: {{ $log->plantillaRecord->item_no_new }} | {{ $log->plantillaRecord->position_title }}
                            </div>
                        @else
                            <span style="color:#ef4444; font-size: 12px;"><i class="bi bi-exclamation-triangle"></i> Record Removed</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <span class="history-type-{{ $log->type }}">{{ $log->type }}</span>
                    </td>
                    <td style="text-align: center; font-family: ui-monospace, monospace;">
                        @if($log->previous_salary_grade == $log->new_salary_grade)
                            <span style="color: var(--color-text-secondary, #475569);">SG {{ $log->new_salary_grade }}</span>
                        @else
                            <span style="color: var(--color-text-muted, #64748b);">SG {{ $log->previous_salary_grade }}</span>
                            <i class="bi bi-chevron-right progress-arrow"></i>
                            <span style="color:#16a34a; font-weight:700;">SG {{ $log->new_salary_grade }}</span>
                        @endif
                    </td>
                    <td style="text-align: center; font-family: ui-monospace, monospace;">
                        @if($log->previous_step == $log->new_step)
                            <span style="color: var(--color-text-secondary, #475569);">Step {{ $log->new_step }}</span>
                        @else
                            <span style="color: var(--color-text-muted, #64748b);">Step {{ $log->previous_step }}</span>
                            <i class="bi bi-chevron-right progress-arrow"></i>
                            <span style="color:#16a34a; font-weight:700;">Step {{ $log->new_step }}</span>
                        @endif
                    </td>
                    <td style="text-align: right; font-family: ui-monospace, monospace;">
                        <span style="color: var(--color-text-muted, #64748b); font-size:12px;">₱{{ number_format($log->previous_annual_salary, 2) }}</span>
                        <i class="bi bi-chevron-right progress-arrow"></i><br>
                        <span style="color:#16a34a; font-weight:700;">₱{{ number_format($log->new_annual_salary, 2) }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding: 40px;">
                        <div style="color:#94a3b8; font-size:14px;">
                            <i class="bi bi-clock-history" style="font-size:24px; display:block; margin-bottom:10px;"></i>
                            No step increment history found.
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($histories->hasPages())
    <div style="padding:14px 20px;border-top:1px solid #f1f5f9;">
        {{ $histories->links() }}
    </div>
    @endif
</div>
</x-dashboard-app>
