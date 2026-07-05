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
thead tr { background:#e2e8f0; }
th { padding:6px 8px; font-size:7px; font-weight:700; text-transform:uppercase; border:1px solid #333; text-align:center; }
th:first-child { text-align:left; }
td { padding:4px 8px; font-size:7.5px; border:1px solid #aaa; }
td.tc { text-align:center; }
.footer-row td { font-weight:700; background:#f0f4f8; border-top:2px solid #333; }
</style>
</head>
<body>

<div class="rpt-header">
    <h1>Inventory of Provincial Government Personnel</h1>
    <p>Count by Office and Appointment Type — Period: {{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y') }}</p>
</div>
<div class="rpt-meta">
    <span>Provincial Government of Bukidnon</span>
    <span>Generated: {{ now()->format('m/d/Y h:i A') }}</span>
</div>

<table>
    <thead>
        <tr>
            <th style="width:40%;">OFFICE</th>
            <th>REGULAR</th>
            <th>Elected</th>
            <th>Co-Terminous</th>
            <th>Casual</th>
            <th>Job Order</th>
            <th style="font-size:8px;">Total</th>
        </tr>
    </thead>
    <tbody>
        @forelse($report1 as $office => $counts)
        <tr>
            <td>{{ $office }}</td>
            <td class="tc">{{ $counts['Permanent']    ?: '—' }}</td>
            <td class="tc">{{ $counts['Elected']      ?: '—' }}</td>
            <td class="tc">{{ $counts['Co-Terminous'] ?: '—' }}</td>
            <td class="tc">{{ $counts['Casual']       ?: '—' }}</td>
            <td class="tc">{{ $counts['Job Order']    ?: '—' }}</td>
            <td class="tc" style="font-weight:700;">{{ $counts['Total'] }}</td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center;padding:12px;color:#666;font-style:italic;">No active personnel records found.</td></tr>
        @endforelse
        @if(count($report1) > 0)
        @php
            $totPerm = array_sum(array_column($report1,'Permanent'));
            $totElec = array_sum(array_column($report1,'Elected'));
            $totCT   = array_sum(array_column($report1,'Co-Terminous'));
            $totCas  = array_sum(array_column($report1,'Casual'));
            $totJO   = array_sum(array_column($report1,'Job Order'));
            $totAll  = array_sum(array_column($report1,'Total'));
        @endphp
        <tr class="footer-row">
            <td>GRAND TOTAL</td>
            <td class="tc">{{ $totPerm ?: '—' }}</td>
            <td class="tc">{{ $totElec ?: '—' }}</td>
            <td class="tc">{{ $totCT   ?: '—' }}</td>
            <td class="tc">{{ $totCas  ?: '—' }}</td>
            <td class="tc">{{ $totJO   ?: '—' }}</td>
            <td class="tc" style="font-size:9px;">{{ $totAll }}</td>
        </tr>
        @endif
    </tbody>
</table>

</body>
</html>
