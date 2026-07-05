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
th { padding:6px 8px; font-size:7px; font-weight:700; border:1px solid #333; background:#fef2f2; text-align:left; }
th.tc { text-align:center; }
th.tr { text-align:right; }
td { padding:4px 8px; font-size:7.5px; border:1px solid #ccc; vertical-align:middle; }
td.tc { text-align:center; }
td.tr { text-align:right; }
.sub-text { font-size:6.5px; color:#666; }
</style>
</head>
<body>

<div class="rpt-header">
    <h1>List of Retirees</h1>
    <p>Employees who successfully retired in {{ $year }}</p>
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
            <th>Position Title &amp; Office</th>
            <th class="tc">Date Retired</th>
            <th class="tr">Years in Service</th>
        </tr>
    </thead>
    <tbody>
        @forelse($report4 as $idx => $item)
        @php $r = $item['record']; @endphp
        <tr>
            <td style="color:#888;">{{ $idx + 1 }}</td>
            <td style="font-weight:600;">{{ strtoupper($r->last_name ?? '') }}, {{ $r->first_name ?? '' }}</td>
            <td>
                <div style="font-weight:600;">{{ $r->position_title }}</div>
                <div class="sub-text">{{ $r->office_department }}</div>
            </td>
            <td class="tc" style="color:#991b1b;font-weight:600;">
                {{ $r->date_separated ? \Carbon\Carbon::parse($r->date_separated)->format('m/d/Y') : '—' }}
            </td>
            <td class="tr">{{ $item['years_in_service'] !== null ? $item['years_in_service'] . ' yrs' : '—' }}</td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;padding:12px;color:#666;font-style:italic;">No retirees recorded for {{ $year }}.</td></tr>
        @endforelse
    </tbody>
</table>

</body>
</html>
