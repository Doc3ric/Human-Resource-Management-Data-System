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
th { padding:6px 8px; font-size:7px; font-weight:700; border:1px solid #333; background:#eff6ff; text-align:left; }
th.tc { text-align:center; }
td { padding:4px 8px; font-size:7.5px; border:1px solid #ccc; vertical-align:middle; }
td.tc { text-align:center; }
.sub-text { font-size:6.5px; color:#666; }
</style>
</head>
<body>

<div class="rpt-header">
    <h1>Detailed Employees</h1>
    <p>Employees currently on detail to another unit, sorted by detailed unit</p>
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
            <th>Detailed Unit</th>
            <th class="tc">Date of Movement</th>
        </tr>
    </thead>
    <tbody>
        @forelse($report8 as $idx => $order)
        <tr>
            <td style="color:#888;">{{ $idx + 1 }}</td>
            <td style="font-weight:600;">{{ strtoupper($order->plantillaRecord?->last_name ?? '') }}, {{ $order->plantillaRecord?->first_name ?? '' }}</td>
            <td>{{ $order->detailed_unit }}</td>
            <td class="tc">{{ $order->date_effective_start->format('m/d/Y') }}</td>
        </tr>
        @empty
        <tr><td colspan="4" style="text-align:center;padding:12px;color:#666;font-style:italic;">No detail orders to report.</td></tr>
        @endforelse
    </tbody>
</table>

</body>
</html>
