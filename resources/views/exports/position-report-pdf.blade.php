<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: "DejaVu Sans", Arial, Helvetica, sans-serif; font-size: 10pt; color: #000; }

    .page { padding: 18mm 14mm 14mm 14mm; }

    .form-ref  { font-size: 9pt; font-weight: bold; margin-bottom: 6px; }

    .report-title {
        text-align: center; font-size: 12pt; font-weight: bold;
        text-transform: uppercase; margin-bottom: 4px;
    }
    .province { font-size: 10pt; margin-bottom: 8px; }

    /* Main table */
    table.main-table {
        width: 100%; border-collapse: collapse; font-size: 9pt;
    }
    table.main-table th, table.main-table td {
        border: 1px solid #000; padding: 4px 5px;
        vertical-align: middle; text-align: center;
    }
    /* Header rows */
    table.main-table .th-main {
        background: transparent; font-weight: bold; font-size: 9pt;
    }
    /* Sub-header for Status of Appointment */
    table.main-table .th-sub {
        background: transparent; font-weight: bold; font-size: 8.5pt;
    }

    /* Data rows */
    table.main-table td.name-col  { text-align: left; font-weight: bold; }
    table.main-table td.pos-col   { text-align: left; }
    table.main-table td.perm-col  { font-size: 14pt; }
    table.main-table td.date-col  { font-size: 9pt; }

    .empty-row td { height: 22px; }

    /* Footer section */
    .footer-section {
        margin-top: 18px;
        display: table; width: 100%;
    }
    .footer-left  { display: table-cell; width: 50%; vertical-align: top; }
    .footer-right { display: table-cell; width: 50%; vertical-align: top; padding-left: 20px; }

    .footer-label { font-size: 9.5pt; margin-bottom: 22px; }
    .footer-sig-line {
        border-top: 1px solid #000; font-weight: bold;
        font-size: 9.5pt; padding-top: 3px; margin-bottom: 4px;
    }
    .footer-date { font-size: 9.5pt; margin-top: 10px; }

    /* Instructions box */
    .instructions {
        margin-top: 22px; border: 2px solid #000; padding: 8px 12px;
    }
    .instructions-title {
        text-align: center; font-weight: bold; font-size: 10pt; margin-bottom: 8px;
    }
    .instr-row { display: table; width: 100%; margin-bottom: 5px; font-size: 9pt; }
    .instr-col { display: table-cell; }
    .instr-col.label { width: 80px; font-weight: bold; vertical-align: top; }
</style>
</head>
<body>
<div class="page">

    {{-- Form reference --}}
    <div class="form-ref">MFMP 001-2023</div>

    {{-- Report title: dynamic based on position --}}
    @php
        if (count($position) === 1) {
            $posUpper   = strtoupper($position[0]);
            $words      = explode(' ', $posUpper);
            $lastWord   = end($words);
            $titleLabel = implode(' ', $words) . (str_ends_with($lastWord, 'S') ? '' : 'S');
        } else {
            $titleLabel = 'VARIOUS POSITIONS';
        }
    @endphp
    <div class="report-title">REPORT ON THE STATUS ON {{ $titleLabel }} PERSONNEL</div>

    <div class="province">Province: Bukidnon</div>

    {{-- Main table --}}
    <table class="main-table">
        <thead>
            {{-- Header row 1 --}}
            <tr>
                <th class="th-main" rowspan="2" style="width:20%;">
                    Name of Personnel<br><small>(01)</small>
                </th>
                <th class="th-main" rowspan="2" style="width:15%;">
                    Designation/Position<br><small>(02)</small>
                </th>
                <th class="th-main" rowspan="2" style="width:22%;">
                    Scope of Work/Tasks and<br>Responsibilities<br><small>(03)</small>
                </th>
                <th class="th-main" colspan="3">
                    Status of Appointment<br><small>(04)</small>
                </th>
                <th class="th-main" rowspan="2" style="width:10%;">
                    Remarks<br><small>(5)</small>
                </th>
            </tr>
            {{-- Header row 2 (sub-headers for Status) --}}
            <tr>
                <th class="th-sub" style="width:9%;">Permanent<br><small>(4.1)</small></th>
                <th class="th-sub" style="width:9%;">Contractual<br><small>(4.2)</small></th>
                <th class="th-sub" style="width:15%;">Date of<br>Appointment<br><small>(4.3)</small></th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $r)
                @php
                    $isPermanent = in_array(strtolower(trim($r->employment_status ?? '')), ['p', 'permanent']);
                    $mi = $r->middle_name ? strtoupper(substr($r->middle_name, 0, 1)) . '.' : '';
                    $fullName = trim(
                        strtoupper($r->last_name ?? '')
                        . ', '
                        . ($r->first_name ?? '')
                        . ($mi ? ' ' . $mi : '')
                    );
                    $dateRaw  = $r->date_original_appointment ?? $r->date_last_promotion;
                    $dateAppt = $dateRaw
                        ? \Carbon\Carbon::parse($dateRaw)->format('m/d/Y')
                        : '';
                @endphp
                <tr>
                    <td class="name-col">{{ $fullName }}</td>
                    <td class="pos-col">{{ $r->position_title }}</td>
                    <td></td>
                    <td class="perm-col">{{ $isPermanent ? '✓' : '' }}</td>
                    <td></td>
                    <td class="date-col">{{ $dateAppt }}</td>
                    <td></td>
                </tr>
            @empty
                <tr class="empty-row">
                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                </tr>
            @endforelse

            {{-- Always show at least 5 blank rows for the form --}}
            @for($blank = $records->count(); $blank < 5; $blank++)
                <tr class="empty-row">
                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                </tr>
            @endfor
        </tbody>
    </table>

    {{-- Footer: Prepared by / Attested by --}}
    <div class="footer-section">
        <div class="footer-left">
            <div class="footer-label">Prepared by:</div>
            <div class="footer-sig-line">LGU Human Resource Dev't. Officer/Personnel Officer</div>
            <div class="footer-date">Date: ___________________________</div>
        </div>
        <div class="footer-right">
            <div class="footer-label">Attested by:</div>
            <div class="footer-sig-line">Governor, Bukidnon Province</div>
            <div class="footer-date">Date: ___________________________</div>
        </div>
    </div>

    {{-- Instructions box --}}
    <div class="instructions">
        <div class="instructions-title">Instructions in Filling-up the Form</div>
        <div class="instr-row">
            <div class="instr-col label">Column 1:</div>
            <div class="instr-col">Indicate the name of midwife/personnel hired by the LGU</div>
        </div>
        <div class="instr-row">
            <div class="instr-col label">Column 02:</div>
            <div class="instr-col">State the official designation as stipulated the contract of service (COS), or the position stated in the plantilla of personnel, if permanent</div>
        </div>
        <div class="instr-row">
            <div class="instr-col label">Column 03:</div>
            <div class="instr-col">Indicate the TOR if COS/duties and responsibilities if permanent</div>
        </div>
        <div class="instr-row">
            <div class="instr-col label">Column 04:</div>
            <div class="instr-col">Put a check on the status of appointment whether COS in (4.1) or for Permanent (4.2) and the date of appointment</div>
        </div>
        <div class="instr-row">
            <div class="instr-col label">Column 05:</div>
            <div class="instr-col">Indicate other relevant information as maybe important which are not captured in the columns provided in this form.</div>
        </div>
    </div>

</div>
</body>
</html>
