<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: "DejaVu Sans", Arial, Helvetica, sans-serif; font-size: 10pt; color: #000; }

    .page { padding: 18mm 14mm 14mm 14mm; }

    .report-title {
        text-align: center; font-size: 12pt; font-weight: bold;
        text-transform: uppercase; margin-bottom: 4px;
    }
    .province { font-size: 10pt; margin-bottom: 12px; text-align: center; }

    /* Main table */
    table.main-table {
        width: 100%; border-collapse: collapse; font-size: 9pt;
        margin-bottom: 20px;
    }
    table.main-table th, table.main-table td {
        border: 1px solid #000; padding: 6px 8px;
        vertical-align: middle; text-align: center;
    }
    /* Header rows */
    table.main-table th {
        background: #f3f4f6; font-weight: bold; font-size: 9.5pt;
    }

    /* Data rows */
    table.main-table td.name-col  { text-align: left; font-weight: bold; }
    table.main-table td.pos-col   { text-align: left; }
    
    .empty-row td { height: 26px; }

    /* Footer section */
    .footer-section {
        margin-top: 30px;
        display: table; width: 100%;
    }
    .footer-left  { display: table-cell; width: 50%; vertical-align: top; }
    .footer-right { display: table-cell; width: 50%; vertical-align: top; padding-left: 20px; text-align: right;}

    .footer-label { font-size: 9.5pt; margin-bottom: 30px; }
    .footer-sig-line {
        border-top: 1px solid #000; font-weight: bold;
        font-size: 9.5pt; padding-top: 3px; margin-bottom: 4px; display: inline-block; width: 80%;
    }
</style>
</head>
<body>
<div class="page">
    <div class="report-title">REPORT ON LEAVE VIOLATIONS (LWOP / TARDINESS)</div>
    <div class="province">Province of Bukidnon</div>

    <table class="main-table">
        <thead>
            <tr>
                <th style="width:5%;">No.</th>
                <th style="width:25%;">Name of Personnel</th>
                <th style="width:25%;">Position / Office</th>
                <th style="width:15%;">Month / Year</th>
                <th style="width:10%;">Occurrences</th>
                <th style="width:20%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($entries as $index => $entry)
                @php
                    $name = strtoupper($entry->plantillaRecord->last_name ?? '') . ', ' . ($entry->plantillaRecord->first_name ?? '');
                    $position = ($entry->plantillaRecord->position_title ?? '') . ' / ' . ($entry->plantillaRecord->office_department ?? '');
                    $monthYear = ($entry->details['month'] ?? '') . ' ' . ($entry->details['year'] ?? '');
                    $occurrences = $entry->details['occurrences'] ?? '0';
                    $status = strtoupper(str_replace('_', ' ', $entry->status));
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="name-col">{{ $name }}</td>
                    <td class="pos-col">{{ $position }}</td>
                    <td>{{ $monthYear }}</td>
                    <td>{{ $occurrences }}</td>
                    <td>{{ $status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="padding: 20px;">No recorded leave violations found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-section">
        <div class="footer-left">
            <div class="footer-label">Prepared by:</div>
            <div class="footer-sig-line">HR Management Officer</div>
        </div>
        <div class="footer-right">
            <div class="footer-label">Noted by:</div>
            <div class="footer-sig-line">PHRMO Head</div>
        </div>
    </div>
</div>
</body>
</html>
