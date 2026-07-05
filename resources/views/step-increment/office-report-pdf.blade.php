<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Schedule of Step Increments — CY {{ now()->year }}</title>
    <style>
        @page { margin: 0.4in 0.4in; size: legal landscape; }
        body { font-family: Arial, sans-serif; font-size: 9px; }
        .header { text-align: center; margin-bottom: 14px; }
        .header .main-title { font-weight: bold; font-size: 13px; }
        .header .sub-title  { font-weight: bold; font-size: 11px; margin-top: 4px; }
        table  { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td {
            border: 0.5px solid #000;
            padding: 4px 3px;
            vertical-align: middle;
            word-wrap: break-word;
        }
        th {
            background-color: #e5e7eb;
            text-align: center;
            font-weight: bold;
            font-size: 8px;
        }
        .dept-row  { background-color: #d1d5db; font-weight: bold; font-size: 9px; }
        .tc { text-align: center; }
        .tr { text-align: right; }
        .tl { text-align: left; }
        .footer { margin-top: 40px; }
    </style>
</head>
<body>

<div class="header">
    <div class="main-title">
        List of Officials and Employees<br>
        Granted Step Increments Pursuant to<br>
        Joint Civil Service Commission<br>
        Department of Budget and Management<br>
        Circular No. 1 s. 1990
    </div>
    <div class="sub-title">
        Step Increment Based on Length of Service<br>
        For CY {{ now()->year }}
    </div>
</div>

<table>
    <thead>
        <tr>
            <th style="width:9%;">EFFECTIVITY DATE</th>
            <th style="width:5%;">TO</th>
            <th style="width:16%;">NAME</th>
            <th style="width:5%;">ITEM NO.</th>
            <th style="width:14%;">DESIGNATION</th>
            <th style="width:5%;">SG / STEP</th>
            <th style="width:5%;">STEP GRANTED</th>
            <th style="width:10%;">MONTHLY SALARY PRIOR TO INCREMENT</th>
            <th style="width:8%;">ADJUSTED SG/STEP</th>
            <th style="width:10%;">MONTHLY SALARY AFTER INCREMENT</th>
            <th style="width:10%;">DIFFERENCE</th>
        </tr>
    </thead>
    <tbody>
        @foreach($grouped as $office => $records)
            <tr class="dept-row">
                <td colspan="11" class="tl">&nbsp;DEPARTMENT / OFFICE: {{ strtoupper($office) }}</td>
            </tr>
            @foreach($records as $rec)
                @php
                    $sg         = $rec->salary_grade;
                    $curStep    = $rec->step ?: 1;
                    $curMonthly = \App\Models\SalaryGrade::getRate($sg, $curStep) ?: 0;

                    $dueType      = $rec->due_type;
                    $stepIncrease = ($dueType === 'nolp' || $dueType === 'both') ? 2 : 1;
                    $newStep      = min(8, $curStep + $stepIncrease);
                    $newMonthly   = \App\Models\SalaryGrade::getRate($sg, $newStep) ?: $curMonthly;
                    $diff         = $newMonthly - $curMonthly;

                    $name = 'VACANT';
                    if (!$rec->is_vacant) {
                        $last  = ucwords(strtolower($rec->last_name));
                        $first = ucwords(strtolower($rec->first_name));
                        $mi    = $rec->middle_name ? strtoupper(substr($rec->middle_name, 0, 1)) . '.' : '';
                        $name  = trim("$last, $first $mi");
                    }

                    $effectivityFrom = $rec->next_step_due_date ? $rec->next_step_due_date->format('M d, Y') : '-';
                @endphp
                <tr>
                    <td class="tc">{{ $effectivityFrom }}</td>
                    <td class="tc">—</td>
                    <td class="tl">{{ $name }}</td>
                    <td class="tc">{{ $rec->item_no_new }}</td>
                    <td class="tl">{{ $rec->position_title }}</td>
                    <td class="tc">{{ $sg }}/{{ $curStep }}</td>
                    <td class="tc">{{ $stepIncrease }}</td>
                    <td class="tr">{{ number_format($curMonthly, 2) }}</td>
                    <td class="tc">{{ $sg }}/{{ $newStep }}</td>
                    <td class="tr">{{ number_format($newMonthly, 2) }}</td>
                    <td class="tr">{{ number_format($diff, 2) }}</td>
                </tr>
            @endforeach
            <tr><td colspan="11" style="border:none; height:6px;"></td></tr>
        @endforeach
    </tbody>
</table>

<div class="footer">
    <table style="border:none; width:60%; margin-left:auto; margin-right:0;">
        <tr style="border:none;">
            <td style="border:none; text-align:center; font-weight:bold;">AIDA B. LOVERES</td>
        </tr>
        <tr style="border:none;">
            <td style="border:none; text-align:center;">P.G. Department Head / PHRM Officer</td>
        </tr>
    </table>
</div>

</body>
</html>
