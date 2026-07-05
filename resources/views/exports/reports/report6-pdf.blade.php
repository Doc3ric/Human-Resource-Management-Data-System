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
th { padding:6px 8px; font-size:7px; font-weight:700; border:1px solid #333; background:#f5f3ff; text-align:left; }
th.tc { text-align:center; }
td { padding:4px 8px; font-size:7.5px; border:1px solid #ccc; vertical-align:middle; }
td.tc { text-align:center; }
.office-hdr td { background:#334155; color:#fff; font-weight:700; padding:6px 8px; font-size:7.5px; text-transform:uppercase; letter-spacing:.5px; }
.cat-hdr td { background:#f1f5f9; color:#334155; font-weight:600; font-style:italic; padding:4px 8px; font-size:7px; }
</style>
</head>
<body>

<div class="rpt-header">
    <h1>Report by Employment Status per Office</h1>
    <p>Grouped by active office assignments and categories — Period: {{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y') }}</p>
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
            <th>Position Title</th>
            <th class="tc">SEX</th>
        </tr>
    </thead>
    <tbody>
        @forelse($report6 as $office => $catGroups)
        <tr class="office-hdr">
            <td colspan="4">Office: {{ $office }}</td>
        </tr>
        @foreach($catGroups as $cat => $records)
        <tr class="cat-hdr">
            <td colspan="4">Status: {{ strtoupper($cat) }} ({{ count($records) }} records)</td>
        </tr>
        @foreach($records as $idx => $r)
        <tr>
            <td style="color:#888;">{{ $idx + 1 }}</td>
            <td style="font-weight:600;">{{ strtoupper($r->last_name ?? '') }}, {{ $r->first_name ?? '' }}</td>
            <td>{{ $r->position_title }}</td>
            <td class="tc">{{ $r->sex ?: '—' }}</td>
        </tr>
        @endforeach
        @endforeach
        @empty
        <tr><td colspan="4" style="text-align:center;padding:12px;color:#666;font-style:italic;">No active personnel records found.</td></tr>
        @endforelse
    </tbody>
</table>

</body>
</html>
