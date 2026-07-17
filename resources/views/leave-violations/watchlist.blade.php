<x-dashboard-app>
<style>
    /* Sleek Modern Variables */
    :root {
        --primary: #2563eb;
        --surface: #ffffff;
        --surface-alt: #f8fafc;
        --border: #e2e8f0;
        --text-main: #0f172a;
        --text-muted: #64748b;
        --radius-lg: 16px;
        --radius-md: 12px;
        --radius-sm: 8px;
        --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
        --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -2px rgba(0,0,0,0.05);
        --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.05), 0 4px 6px -4px rgba(0,0,0,0.05);
    }

    .wl-header { 
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); 
        border-radius: var(--radius-lg); 
        padding: 32px; 
        margin-bottom: 24px; 
        color: #fff; 
        box-shadow: var(--shadow-lg);
        position: relative;
        overflow: hidden;
    }
    .wl-header::after {
        content: '';
        position: absolute;
        top: 0; right: 0; bottom: 0; left: 0;
        background: url('data:image/svg+xml;utf8,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="40" stroke="rgba(255,255,255,0.05)" stroke-width="2" fill="none"/></svg>') repeat;
        opacity: 0.4;
        pointer-events: none;
    }
    .wl-header-content { position: relative; z-index: 1; }
    .wl-header h1 { font-size: 24px; font-weight: 800; margin: 0 0 8px; letter-spacing: -0.5px; display: flex; align-items: center; gap: 10px;}
    .wl-header p { font-size: 14px; opacity: 0.8; margin: 0; font-weight: 400; max-width: 800px; line-height: 1.5; }
    
    .wl-card { 
        background: var(--surface); 
        border: 1px solid var(--border); 
        border-radius: var(--radius-lg); 
        margin-bottom: 24px; 
        box-shadow: var(--shadow-sm);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    /* Stats Row */
    .stats-container {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        padding: 20px;
        background: var(--surface-alt);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border);
        margin-bottom: 24px;
    }
    .stat-group { display: flex; flex-direction: column; gap: 12px; flex: 1; min-width: 300px; }
    .stat-group-title { font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--text-muted); letter-spacing: 0.5px; }
    .stat-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .stat-chip { 
        display: inline-flex; align-items: center; justify-content: space-between;
        background: var(--surface); border: 1px solid var(--border); 
        border-radius: 20px; padding: 6px 14px; font-size: 12px; font-weight: 600; color: var(--text-main);
        box-shadow: var(--shadow-sm); transition: all 0.2s ease;
    }
    .stat-chip:hover { border-color: #cbd5e1; transform: translateY(-1px); box-shadow: var(--shadow-md); }
    .stat-chip-count {
        background: #eff6ff; color: #1d4ed8; padding: 2px 8px; border-radius: 12px; margin-left: 8px; font-size: 11px; font-weight: 700;
    }

    /* Filters */
    .wl-filter-bar { padding: 20px; border-bottom: 1px solid var(--border); background: var(--surface); border-radius: var(--radius-lg) var(--radius-lg) 0 0; }
    .wl-filter-bar .form-label { font-size: 11.5px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 6px; }
    .wl-filter-bar .form-select, .wl-filter-bar .form-control { border-radius: var(--radius-sm); border-color: #cbd5e1; font-size: 13px; padding: 8px 12px; box-shadow: none; transition: border-color 0.2s, box-shadow 0.2s; }
    .wl-filter-bar .form-select:focus, .wl-filter-bar .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
    .filter-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; align-items: end; }
    .filter-actions { display: flex; gap: 10px; }
    .btn-custom { padding: 8px 16px; font-size: 13px; font-weight: 600; border-radius: var(--radius-sm); transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 6px; cursor: pointer;}
    .btn-filter { background: var(--primary); color: white; border: none; }
    .btn-filter:hover { background: #1d4ed8; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(37,99,235,0.2); color: white; }
    .btn-csv { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; text-decoration: none;}
    .btn-csv:hover { background: #dcfce7; border-color: #86efac; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(22,101,52,0.1); color: #166534;}

    /* Table */
    .table-wrapper { overflow-x: auto; border-radius: 0 0 var(--radius-lg) var(--radius-lg); }
    .wl-table { width: 100%; border-collapse: separate; border-spacing: 0; margin: 0; }
    .wl-table th { font-size: 11px; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.5px; background: var(--surface-alt); padding: 14px 20px; font-weight: 700; border-bottom: 1px solid var(--border); border-top: none; }
    .wl-table td { font-size: 13px; padding: 16px 20px; vertical-align: middle; color: var(--text-main); border-bottom: 1px solid var(--border); transition: background-color 0.2s; }
    .wl-table tbody tr:hover td { background-color: #f8fafc; }
    .wl-table tbody tr:last-child td { border-bottom: none; }
    
    .emp-name { font-weight: 700; color: #0f172a; font-size: 13.5px; }
    .emp-office { font-size: 12px; color: var(--text-muted); margin-top: 4px; }

    /* Badges */
    .status-badge { font-size: 10px; padding: 4px 10px; border-radius: 20px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.3px; display: inline-flex; align-items: center; gap: 4px; border: 1px solid transparent; }
    .status-detected { background: #fffbeb; color: #b45309; border-color: #fde68a; }
    .status-notice_pending { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
    .status-issued { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
    .status-responded { background: #eef2ff; color: #4338ca; border-color: #c7d2fe; }
    .status-resolved { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
    .status-escalated { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }

    /* Action Buttons */
    .action-btn { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; border: 1px solid; transition: all 0.2s; background: var(--surface); cursor: pointer; }
    .action-btn:hover { transform: translateY(-2px); }
    .btn-issue { color: #2563eb; border-color: #bfdbfe; }
    .btn-issue:hover { background: #eff6ff; border-color: #3b82f6; box-shadow: 0 4px 10px rgba(37,99,235,0.15); }
    .btn-show-cause { color: #d97706; border-color: #fde68a; }
    .btn-show-cause:hover { background: #fffbeb; border-color: #f59e0b; box-shadow: 0 4px 10px rgba(217,119,6,0.15); }
    .btn-respond { color: #0891b2; border-color: #a5f3fc; }
    .btn-respond:hover { background: #ecfeff; border-color: #06b6d4; box-shadow: 0 4px 10px rgba(8,145,178,0.15); }
    .btn-resolve { color: #16a34a; border-color: #bbf7d0; }
    .btn-resolve:hover { background: #f0fdf4; border-color: #22c55e; box-shadow: 0 4px 10px rgba(22,163,74,0.15); }
    .btn-pdf { color: #dc2626; border-color: #fecaca; }
    .btn-pdf:hover { background: #fef2f2; border-color: #ef4444; box-shadow: 0 4px 10px rgba(220,38,38,0.15); }

    .violation-text { font-weight: 700; color: #334155; }
    .tier-badge { background: #f1f5f9; color: #475569; padding: 2px 8px; border-radius: 6px; font-weight: 800; font-size: 11px; border: 1px solid #e2e8f0; }
</style>

<div class="wl-header">
    <div class="wl-header-content">
        <h1><i class="bi bi-shield-exclamation text-warning"></i> Leave Violation Watchlist</h1>
        <p>Monitor and manage attendance and leave-filing violations. Filter by office, violation type, or status to process notices and track resolution according to CSC guidelines.</p>
    </div>
</div>

@if(session('success'))
    <div style="background:#f0fdf4;color:#166534;padding:12px 16px;border-radius:12px;border:1px solid #bbf7d0;margin-bottom:20px;font-size:13px;display:flex;align-items:center;gap:8px;"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
@endif
@if($errors->any())
    <div style="background:#fef2f2;color:#991b1b;padding:12px 16px;border-radius:12px;border:1px solid #fecaca;margin-bottom:20px;font-size:13px;display:flex;align-items:flex-start;gap:8px;">
        <i class="bi bi-exclamation-triangle-fill" style="margin-top:2px;"></i>
        <div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
    </div>
@endif

<div class="stats-container shadow-sm">
    <div class="stat-group">
        <div class="stat-group-title">Active Violations by Type</div>
        <div class="stat-chips">
            @forelse($byType as $type => $count)
                <div class="stat-chip">
                    {{ str_replace('_', ' ', $type) }} <span class="stat-chip-count">{{ $count }}</span>
                </div>
            @empty
                <span class="text-muted" style="font-size:12px; font-style: italic;">No active violations.</span>
            @endforelse
        </div>
    </div>
    <div style="width:1px; background:var(--border); margin: 0 10px;"></div>
    <div class="stat-group">
        <div class="stat-group-title">Active Violations by Office</div>
        <div class="stat-chips">
            @forelse($byOffice as $office => $count)
                <div class="stat-chip">
                    {{ $office ?: 'Unassigned' }} <span class="stat-chip-count">{{ $count }}</span>
                </div>
            @empty
                <span class="text-muted" style="font-size:12px; font-style: italic;">All clear.</span>
            @endforelse
        </div>
    </div>
</div>

<div class="wl-card">
    <div class="wl-filter-bar">
        <form method="GET" action="{{ route('leave-violations.watchlist') }}" class="filter-grid">
            <div>
                <label class="form-label">Office</label>
                <select name="office" class="form-select">
                    <option value="">All Offices</option>
                    @foreach($offices as $office)
                        <option value="{{ $office }}" {{ request('office') === $office ? 'selected' : '' }}>{{ $office }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Violation Type</label>
                <select name="violation_type" class="form-select">
                    <option value="">All Types</option>
                    @foreach($violationTypes as $type)
                        <option value="{{ $type }}" {{ request('violation_type') === $type ? 'selected' : '' }}>{{ str_replace('_', ' ', $type) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Offense Tier</label>
                <input type="number" name="offense_tier" min="1" class="form-control" placeholder="Any" value="{{ request('offense_tier') }}">
            </div>
            <div>
                <label class="form-label">Rating Period</label>
                <select name="rating_period" class="form-select">
                    <option value="">All Periods</option>
                    @foreach($ratingPeriods as $period)
                        <option value="{{ $period }}" {{ request('rating_period') === $period ? 'selected' : '' }}>{{ $period }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    @foreach(['detected', 'notice_pending', 'issued', 'responded', 'resolved', 'escalated'] as $status)
                        <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn-custom btn-filter"><i class="bi bi-funnel"></i> Apply</button>
                <a href="{{ route('leave-violations.watchlist.export-csv', request()->query()) }}" class="btn-custom btn-csv" title="Export to CSV"><i class="bi bi-download"></i> CSV</a>
            </div>
        </form>
    </div>

    <div class="table-wrapper">
        <table class="table wl-table">
            <thead>
                <tr>
                    <th>Employee Details</th>
                    <th>Violation Info</th>
                    <th class="text-center">Tier</th>
                    <th>Period</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries as $entry)
                    <tr>
                        <td>
                            <div class="emp-name">{{ $entry->plantillaRecord->last_name ?? 'Unknown' }}, {{ $entry->plantillaRecord->first_name ?? '' }}</div>
                            <div class="emp-office"><i class="bi bi-building text-muted me-1"></i> {{ $entry->plantillaRecord->office_department ?? 'N/A' }}</div>
                        </td>
                        <td>
                            <div class="violation-text">{{ str_replace('_', ' ', $entry->violation_type) }}</div>
                        </td>
                        <td class="text-center">
                            <span class="tier-badge" title="Offense Tier">{{ $entry->offense_tier }}</span>
                        </td>
                        <td>
                            <span style="font-size: 12px; color: var(--text-muted);"><i class="bi bi-calendar3 me-1"></i> {{ str_replace('_', ' ', $entry->rating_period) }}</span>
                        </td>
                        <td class="text-center">
                            @php
                                $statusIcon = 'circle';
                                if($entry->status == 'detected') $statusIcon = 'exclamation-circle';
                                if(in_array($entry->status, ['notice_pending', 'issued'])) $statusIcon = 'envelope';
                                if($entry->status == 'responded') $statusIcon = 'reply-all';
                                if($entry->status == 'resolved') $statusIcon = 'check-circle';
                                if($entry->status == 'escalated') $statusIcon = 'exclamation-triangle';
                            @endphp
                            <span class="status-badge status-{{ $entry->status }}">
                                <i class="bi bi-{{ $statusIcon }}"></i> {{ str_replace('_', ' ', $entry->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-2 justify-content-center flex-wrap">
                                @if($entry->status === 'detected')
                                    <form method="POST" action="{{ route('leave-violations.issue-notice', $entry) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="action-btn btn-issue" title="Issue Notice"><i class="bi bi-send"></i></button>
                                    </form>
                                    <a href="{{ route('leave-violations.create-show-cause', ['violation_id' => $entry->id]) }}" class="action-btn btn-show-cause" title="Issue Show-Cause"><i class="bi bi-exclamation-triangle"></i></a>
                                @endif
                                @if(in_array($entry->status, ['notice_pending', 'issued']))
                                    <form method="POST" action="{{ route('leave-violations.respond', $entry) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="action-btn btn-respond" title="Mark Responded"><i class="bi bi-reply"></i></button>
                                    </form>
                                @endif
                                @if(in_array($entry->status, ['notice_pending', 'issued', 'responded']))
                                    <form method="POST" action="{{ route('leave-violations.resolve', $entry) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="action-btn btn-resolve" title="Mark Resolved"><i class="bi bi-check-lg"></i></button>
                                    </form>
                                @endif
                                @if($entry->document_id)
                                    <a href="{{ route('leave-violations.download-pdf', $entry) }}" target="_blank" class="action-btn btn-pdf" title="Download PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-shield-check" style="font-size: 32px; color: #cbd5e1; margin-bottom: 10px; display: block;"></i>
                            <div style="font-size: 14px; font-weight: 600; color: #475569;">No violations found</div>
                            <div style="font-size: 13px; color: #94a3b8; mt-1">Try adjusting your filters or checking a different period.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 bg-white" style="border-top: 1px solid var(--border); border-radius: 0 0 var(--radius-lg) var(--radius-lg);">
        {{ $entries->links() }}
    </div>
</div>
</x-dashboard-app>
