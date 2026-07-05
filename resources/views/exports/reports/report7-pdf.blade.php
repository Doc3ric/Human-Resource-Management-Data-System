<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'DejaVu Sans',Arial,sans-serif; font-size:8px; color:#000; background:#fff; }
.rpt-header { border-bottom:2px solid #000; padding:8px 12px; margin-bottom:6px; }
.rpt-header h1 { font-size:13px; font-weight:800; }
.rpt-header p  { font-size:8px; color:#333; margin-top:2px; }
.rpt-meta { display:flex; justify-content:space-between; font-size:7px; color:#555; margin-bottom:8px; padding-bottom:4px; border-bottom:1px solid #aaa; }
table { width:100%; border-collapse:collapse; }
thead { display:table-header-group; }
th { padding:6px 8px; font-size:7px; font-weight:700; border:1px solid #333; background:#eef2ff; text-align:left; }
th.tc { text-align:center; }
td { padding:4px 8px; font-size:7.5px; border:1px solid #ccc; vertical-align:middle; }
td.tc { text-align:center; }
.sub-text { font-size:6.5px; color:#666; }
</style>
</head>
<body>

<div class="rpt-header">
    <h1>Custom Status Report: {{ $r7_status }}</h1>
    <p>
        Period: 
        @if($r7_period === 'range')
            From {{ $r7_from }} To {{ $r7_to }}
        @else
            As Of {{ $r7_asof }}
        @endif
    </p>
</div>
<div class="rpt-meta">
    <span>Provincial Government of Bukidnon</span>
    <span>Generated: {{ now()->format('m/d/Y h:i A') }}</span>
</div>

<table>
    <thead>
        <tr>
            <th style="width:30px;">#</th>
            <th>Employee Name</th>
            <th>Office / Unit</th>
            <th class="tc">Date Effective</th>
            <th>Status Detail</th>
        </tr>
    </thead>
    <tbody>
        @forelse($report7 as $idx => $r)
        <tr>
            <td style="color:#888;">{{ $idx + 1 }}</td>
            <td style="font-weight:600;">{{ strtoupper($r->last_name ?? '') }}, {{ $r->first_name ?? '' }}</td>
            <td>
                <div style="font-weight:600;">{{ $r->position_title }}</div>
                <div class="sub-text">{{ $r->office_department }}</div>
            </td>
            <td class="tc">
                @if($r7_status === 'Newly Hired')
                    {{ $r->date_original_appointment ? \Carbon\Carbon::parse($r->date_original_appointment)->format('m/d/Y') : '—' }}
                @elseif($r7_status === 'Promoted')
                    {{ $r->date_last_promotion ? \Carbon\Carbon::parse($r->date_last_promotion)->format('m/d/Y') : '—' }}
                @elseif($r7_status === 'Retired' || $r7_status === 'Terminated')
                    {{ $r->date_separated ? \Carbon\Carbon::parse($r->date_separated)->format('m/d/Y') : '—' }}
                @else
                    —
                @endif
            </td>
            <td style="font-weight:600;color:#4338ca;">
                @if($r7_status === 'Newly Hired' || $r7_status === 'Promoted')
                    {{ $r->nature_of_appointment ?: 'N/A' }}
                @else
                    {{ $r->nature_of_separation ?: 'N/A' }}
                @endif
            </td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;padding:12px;color:#666;font-style:italic;">No records found for the selected criteria.</td></tr>
        @endforelse
    </tbody>
</table>

</body>
</html>
