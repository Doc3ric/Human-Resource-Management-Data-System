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
th { padding:6px 8px; font-size:7px; font-weight:700; border:1px solid #333; background:#e2e8f0; text-align:left; }
td { padding:4px 8px; font-size:7.5px; border:1px solid #ccc; vertical-align:middle; }
.group-hdr td { background:#ecfdf5; color:#065f46; font-weight:700; padding:5px 8px; font-size:7.5px; }
</style>
</head>
<body>

<div class="rpt-header">
    <h1>Newly Hired, Promoted &amp; Demoted Employees</h1>
    <p>Appointment changes recorded in {{ $year }}</p>
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
            <th>Status / Category</th>
            <th>Nature of Appt.</th>
        </tr>
    </thead>
    <tbody>
        @forelse($report3->groupBy('organizational_unit') as $office => $records)
        <tr class="group-hdr">
            <td colspan="5">Office: {{ $office }} ({{ count($records) }} records)</td>
        </tr>
        @foreach($records as $idx => $r)
        <tr>
            <td style="color:#888;">{{ $idx + 1 }}</td>
            <td style="font-weight:600;">{{ strtoupper($r->last_name ?? '') }}, {{ $r->first_name ?? '' }}</td>
            <td>{{ $r->position_title }}</td>
            <td>{{ $r->resolved_category ?? $r->employment_status }}</td>
            <td style="font-weight:600;color:#065f46;">{{ $r->nature_of_appointment ?: 'N/A' }}</td>
        </tr>
        @endforeach
        @empty
        <tr><td colspan="5" style="text-align:center;padding:12px;color:#666;font-style:italic;">No newly hired, promoted, or demoted employees recorded for {{ $year }}.</td></tr>
        @endforelse
    </tbody>
</table>

</body>
</html>
