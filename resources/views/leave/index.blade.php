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

<div class="lv-hero">
    <h1><i class="bi bi-calendar-check-fill"></i> Leave Ledger</h1>
    <p>Omnibus Rules on Leave (CSC MC No. 41 s.1998). Job Order and Contract of Service personnel are excluded from leave-credit accrual per DOLE-CSC-COA-DBM JC No. 1 s.2017.</p>
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
</x-dashboard-app>
