@extends('layouts.app')

@section('content')
<style>
    .premium-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    .table-header { border-bottom: 1px solid #e5e7eb; background: #f9fafb; font-size: 10px; color: #6b7280; text-transform: uppercase; font-weight: 600; text-align: left; padding: 12px 24px; }
    .table-td { border-bottom: 1px solid #e5e7eb; padding: 16px 24px; font-size: 13px; color: #374151; vertical-align: middle; }
    .pill { display: inline-block; padding: 4px 10px; border-radius: 9999px; font-size: 11px; font-weight: 600; }
    .pill-tardy { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
    .pill-undertime { background: #dbeafe; color: #2563eb; border: 1px solid #bfdbfe; }
    .avatar-circle { width: 32px; height: 32px; border-radius: 50%; background: #2563eb; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; }
    .table-btn { font-size: 11px; padding: 6px 10px; border-radius: 4px; border: 1px solid transparent; background: transparent; display: inline-flex; align-items: center; gap: 4px; cursor: pointer; font-weight: 500; }
    .btn-edit { color: #2563eb; } .btn-edit:hover { background: #eff6ff; border-color: #bfdbfe; }
    .btn-generate { color: #059669; } .btn-generate:hover { background: #ecfdf5; border-color: #a7f3d0; }
    .btn-delete { color: #4b5563; } .btn-delete:hover { background: #f3f4f6; border-color: #e5e7eb; }
    .pill-reprimand { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
    
    .btn-dynamic { padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); text-decoration: none; border: none; color: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .btn-dynamic:hover { transform: translateY(-2px); box-shadow: 0 6px 12px rgba(0,0,0,0.15); color: #fff; }
    .btn-dynamic:active { transform: translateY(0); }
    .btn-tardy { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .btn-undertime { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    .btn-reprimand { background: linear-gradient(135deg, #ef4444, #dc2626); }

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

    .theme-danger .kpi-pill { background: #fee2e2; color: #be123c; }
    .theme-danger .kpi-icon-box { background: #fee2e2; color: #be123c; }
    .theme-danger .kpi-sub { color: #be123c; }
    .theme-danger .kpi-divider { background: #fecaca; }
    .theme-danger .kpi-footer i { color: #fb7185; }
</style>

<div style="margin-bottom: 14px;">
    <h2 style="font-size: 24px; font-weight: 700; color: #111827; margin: 0;">Generated Letters</h2>
    <p style="font-size: 14px; color: #6b7280; margin: 4px 0 0;">Storage for all created Tardy and Undertime letters.</p>
</div>

<div class="scoreboard-container">
    <div class="kpi-card theme-primary">
        <div class="kpi-header">
            <span class="kpi-pill">DUE PROCESS</span>
            <div class="kpi-icon-box"><i class="bi bi-envelope-paper"></i></div>
        </div>
        <div class="kpi-label">Total Letters</div>
        <div class="kpi-value">{{ number_format($metrics['total']) }}</div>
        <div class="kpi-sub">100%</div>
        <div class="kpi-divider"></div>
        <div class="kpi-footer">
            <i class="bi bi-info-circle"></i>
            <span>Official letters issued serve as formal notices for administrative proceedings.</span>
        </div>
    </div>
    
    <div class="kpi-card theme-warning">
        <div class="kpi-header">
            <span class="kpi-pill">CSC MC 1</span>
            <div class="kpi-icon-box"><i class="bi bi-clock-history"></i></div>
        </div>
        <div class="kpi-label">Tardy / Undertime</div>
        <div class="kpi-value">{{ number_format($metrics['tardy_undertime']) }}</div>
        <div class="kpi-sub">{{ $metrics['tardy_undertime_pct'] }}% of Letters</div>
        <div class="kpi-divider"></div>
        <div class="kpi-footer">
            <i class="bi bi-info-circle"></i>
            <span>Tardiness of 10 times in a month for 2 consecutive months is habitual.</span>
        </div>
    </div>

    <div class="kpi-card theme-danger">
        <div class="kpi-header">
            <span class="kpi-pill">DISCIPLINARY</span>
            <div class="kpi-icon-box"><i class="bi bi-exclamation-triangle"></i></div>
        </div>
        <div class="kpi-label">Reprimand Letters</div>
        <div class="kpi-value">{{ number_format($metrics['reprimand']) }}</div>
        <div class="kpi-sub">{{ $metrics['reprimand_pct'] }}% of Letters</div>
        <div class="kpi-divider"></div>
        <div class="kpi-footer">
            <i class="bi bi-info-circle"></i>
            <span>Reprimands are the first tier of administrative penalties prior to suspension.</span>
        </div>
    </div>
</div>

<div style="display:flex; justify-content: center; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 14px;">
    <div style="position:relative;">
        <input type="text" placeholder="Search reference, employee" style="padding:8px 12px 8px 32px; border-radius:8px; border:1px solid #d1d5db; font-size:13px; width:220px;">
        <i class="bi bi-search" style="position:absolute; left:12px; top:10px; color:#9ca3af; font-size:12px;"></i>
    </div>
    <button class="btn btn-outline-secondary btn-sm" style="background:#fff;"><i class="bi bi-funnel"></i> Filter</button>
    <a href="{{ route('leave-violations.create-tardy') }}" class="btn-dynamic btn-tardy"><i class="bi bi-plus-lg"></i> New Tardy Letter</a>
    <a href="{{ route('leave-violations.create-undertime') }}" class="btn-dynamic btn-undertime"><i class="bi bi-plus-lg"></i> New Undertime Letter</a>
    <a href="{{ route('leave-violations.create-reprimand') }}" class="btn-dynamic btn-reprimand"><i class="bi bi-exclamation-triangle-fill"></i> New Reprimand Letter</a>
</div>

<div class="premium-card">
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 24px; border-bottom: 1px solid #e5e7eb;">
        <h3 style="font-size: 14px; font-weight: 600; margin: 0;">All Generated Letters</h3>
        <div style="font-size: 12px; color: #9ca3af;">{{ $letters->total() }} letter(s) total</div>
    </div>
    
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th class="table-header">REFERENCE NO.</th>
                    <th class="table-header">TYPE</th>
                    <th class="table-header">EMPLOYEE</th>
                    <th class="table-header">POSITION / OFFICE</th>
                    <th class="table-header">MONTH / YEAR</th>
                    <th class="table-header">OCCURRENCES</th>
                    <th class="table-header">CREATED BY</th>
                    <th class="table-header">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($letters as $letter)
                <tr>
                    <td class="table-td" style="color: #6b7280;">{{ $letter->id }}</td>
                    <td class="table-td">
                        @if($letter->violation_type == 'HABITUAL_TARDINESS')
                            <span class="pill pill-tardy"><i class="bi bi-clock-history"></i> Tardy</span>
                        @elseif($letter->violation_type == 'UNDERTIME')
                            <span class="pill pill-undertime"><i class="bi bi-hourglass-split"></i> Undertime</span>
                        @elseif($letter->violation_type == 'REPRIMAND_HABITUAL_TARDINESS')
                            <span class="pill pill-reprimand"><i class="bi bi-exclamation-triangle"></i> Reprimand (Tardy)</span>
                        @elseif($letter->violation_type == 'REPRIMAND_UNDERTIME')
                            <span class="pill pill-reprimand"><i class="bi bi-exclamation-triangle"></i> Reprimand (Undertime)</span>
                        @else
                            <span class="pill pill-undertime"><i class="bi bi-hourglass-split"></i> {{ $letter->violation_type }}</span>
                        @endif
                    </td>
                    <td class="table-td" style="font-weight: 500;">
                        {{ $letter->details['prefix'] ?? '' }} {{ $letter->plantillaRecord->last_name }}, {{ $letter->plantillaRecord->first_name }}
                    </td>
                    <td class="table-td">
                        <div>{{ $letter->plantillaRecord->position_title }}</div>
                        <div style="font-size: 11px; color: #9ca3af; margin-top:2px;">{{ $letter->plantillaRecord->office_department }}</div>
                    </td>
                    <td class="table-td">{{ $letter->details['month'] ?? '—' }} {{ $letter->details['year'] ?? '' }}</td>
                    <td class="table-td"><b>{{ $letter->details['occurrences'] ?? '0' }}</b> time(s)</td>
                    <td class="table-td">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <div class="avatar-circle">{{ substr($letter->plantillaRecord->first_name, 0, 1) }}</div>
                            <div style="font-size:10px; color:#9ca3af; line-height:1.2;">
                                <div>{{ date('M d, Y', strtotime($letter->created_at)) }}</div>
                                <div>{{ date('h:i A', strtotime($letter->created_at)) }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="table-td">
                        <div style="display:flex; gap:4px;">
                            <a href="{{ route('leave-violations.edit-letter', $letter->id) }}" class="table-btn btn-edit" style="text-decoration:none;"><i class="bi bi-pencil-square"></i> Edit</a>
                            <a href="{{ route('leave-violations.download-pdf', $letter->id) }}" target="_blank" class="table-btn btn-generate" style="text-decoration:none;"><i class="bi bi-file-pdf"></i> Generate</a>
                            <form action="{{ route('leave-violations.destroy-letter', $letter->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this letter?');" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="table-btn btn-delete"><i class="bi bi-trash"></i> Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
                
                @if($letters->isEmpty())
                <tr><td colspan="8" class="table-td" style="text-align: center; padding: 40px; color: #9ca3af;">No generated letters found.</td></tr>
                @endif
            </tbody>
        </table>
    </div>
    
    <div style="padding: 16px 24px; font-size:12px; color:#9ca3af; border-top:1px solid #e5e7eb;">
        {{ $letters->total() }} letter(s) total
    </div>
</div>
@endsection
