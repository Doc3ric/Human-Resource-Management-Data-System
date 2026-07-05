<x-dashboard-app>
<style>
/* Page-specific styles for IP Report */
.ip-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #b45309 55%, #92400e 100%);
    border-radius: 14px;
    padding: 24px 32px;
    position: relative;
    overflow: hidden;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.ip-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: radial-gradient(circle, rgba(255,255,255,.08) 1px, transparent 1px);
    background-size: 22px 22px;
}
.ip-hero-inner { position: relative; z-index: 1; display: flex; align-items: center; justify-content: space-between; gap: 16px; width: 100%; }
.ip-hero h1 { color: #fff; font-size: 24px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 10px; }
.ip-hero p  { color: rgba(255,255,255,.75); font-size: 13px; margin: 4px 0 0; }

.back-btn {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.2);
    color: #fff; padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 600;
    text-decoration: none; transition: background .15s;
}
.back-btn:hover { background: rgba(255,255,255,.25); color:#fff; }

/* Table Card */
.table-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 2px 4px -2px rgba(0,0,0,0.02);
    overflow: hidden;
}

.table-header {
    padding: 16px 20px; border-bottom: 1px solid #f1f5f9;
    display: flex; justify-content: space-between; align-items: center;
}

/* ── Premium Personnel Table ─────────────────────────────────────────── */
.ip-table { width: 100%; border-collapse: collapse; border-spacing: 0; }
.ip-table thead tr { border-bottom: 2px solid #eef2f6; background: #fafafa; }
.ip-table th {
    text-align: left; font-size: 11px; font-weight: 800;
    text-transform: uppercase; letter-spacing: 0.8px; color: #4b5563;
    padding: 14px 20px; white-space: nowrap;
}
.ip-table tbody tr { transition: background 0.15s; border-bottom: 1px solid #f1f5f9; }
.ip-table tbody tr:hover { background: #f8fafc; }
.ip-table td { padding: 14px 20px; vertical-align: middle; font-size: 13px; color: #334155; }
.ip-table tbody tr:last-child { border-bottom: 0; }

/* Cell Styles */
.item-code { font-family: ui-monospace, monospace; font-size: 13px; font-weight: 600; color: #64748b; }
.emp-name-text { font-weight: 700; color: #0f172a; font-size: 14px; text-transform: uppercase; }
.emp-pos { font-weight: 600; color: #334155; }
.emp-office { font-size: 12px; color: #64748b; }

.ip-pill {
    display: inline-flex; align-items: center; background: #fff7ed; color: #c2410c;
    border: 1px solid #fed7aa; padding: 4px 12px; border-radius: 99px; font-size: 11px; font-weight: 700;
}

.status-label { font-size: 12px; font-weight: 600; color: #4b5563; }
</style>

{{-- HERO --}}
<div class="ip-hero">
    <div class="ip-hero-inner">
        <div>
            <h1><i class="bi bi-people-fill"></i> Indigenous People (IP) Personnel Report</h1>
            <p>List of all filled plantilla positions occupied by Indigenous Peoples.</p>
        </div>
        <div>
            <div style="display:flex;align-items:center;gap:8px;">
                <a href="{{ route('plantilla.ip-report.export.pdf') }}" target="_blank" style="display:inline-flex;align-items:center;gap:5px;background:#dc2626;color:#fff;padding:8px 14px;border-radius:10px;font-size:13px;font-weight:700;text-decoration:none;" title="Export PDF">
                    <i class="bi bi-file-earmark-pdf"></i> Export PDF
                </a>
                <a href="{{ route('plantilla.ip-report.export.excel') }}" style="display:inline-flex;align-items:center;gap:5px;background:#16a34a;color:#fff;padding:8px 14px;border-radius:10px;font-size:13px;font-weight:700;text-decoration:none;" title="Export Excel">
                    <i class="bi bi-file-earmark-excel"></i> Export Excel
                </a>
                <a href="{{ route('plantilla.index') }}" class="back-btn">
                    <i class="bi bi-arrow-left"></i> Back to Inventory
                </a>
            </div>
        </div>
    </div>
</div>

{{-- DATA TABLE --}}
<div class="table-card">
    <div class="table-header">
        <div style="font-weight: 700; color: #0f172a; font-size: 15px;">
            <i class="bi bi-list-ul me-1"></i> Records Found ({{ $ipRecords->count() }})
        </div>
    </div>
    
    <div style="overflow-x: auto;">
        <table class="ip-table">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Name</th>
                    <th>IP Group</th>
                    <th>OFFICE</th>
                    <th>Position</th>
                    <th>Appointment Status</th>
                    <th style="text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ipRecords as $index => $rec)
                <tr>
                    <td style="color:#9ca3af; font-size:12px; font-weight:600;">{{ $index + 1 }}</td>
                    <td>
                        <div class="emp-name-text">
                            {{ $rec->last_name }}, {{ $rec->first_name }}
                            {{ $rec->middle_name ? strtoupper(substr($rec->middle_name,0,1)).'.' : '' }}
                        </div>
                    </td>
                    <td>
                        @if($rec->indigenous_people && $rec->indigenous_people !== 'Y')
                            <span class="ip-pill">{{ $rec->indigenous_people }}</span>
                        @else
                            <span class="ip-pill" style="background:#f3f4f6; color:#6b7280; border-color:#d1d5db;">Unspecified</span>
                        @endif
                    </td>
                    <td>
                        <div class="emp-office">{{ $rec->office_department }}</div>
                    </td>
                    <td>
                        <div class="emp-pos">{{ $rec->position_title }}</div>
                        <div class="item-code">Item No. {{ $rec->item_no_new }}</div>
                    </td>
                    <td>
                        <span class="status-label">{{ strtoupper($rec->employment_status) ?: '—' }}</span>
                    </td>
                    <td style="text-align:center;">
                        @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                        <a href="{{ route('all-data.edit', $rec) }}" 
                           style="display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:6px; background:#fffbeb; color:#d97706; border:1px solid #fde68a; font-size:12px; transition:all 0.15s; text-decoration:none;" 
                           title="Edit Employee Data"
                           onmouseover="this.style.background='#d97706'; this.style.color='#fff';"
                           onmouseout="this.style.background='#fffbeb'; this.style.color='#d97706';">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center; padding: 48px; color: #9ca3af;">
                        <i class="bi bi-inbox" style="font-size:32px; display:block; margin-bottom:12px;"></i>
                        No IP personnel records found in the database.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

</x-dashboard-app>
