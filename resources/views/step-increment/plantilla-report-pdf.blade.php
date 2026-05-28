<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ isset($mode) && $mode === 'nosi' ? 'NOSI/NOLP Plantilla Basis CY ' . (now()->year + 1) : 'Plantilla of Personnel CY ' . (now()->year + 1) }}</title>
    <style>
        @page { margin: 0.4in 0.5in; size: legal landscape; }
        body { font-family: Arial, sans-serif; font-size: 9px; color: #000; }

        .header { text-align: center; margin-bottom: 14px; }
        .header .title-1 { font-size: 14px; font-weight: bold; text-transform: uppercase; }
        .header .title-2 { font-size: 12px; font-weight: bold; text-transform: uppercase; margin-top: 4px; }
        .header .dept    { font-size: 11px; text-align: left; margin-top: 10px; }
        .header .dept strong { text-transform: uppercase; }

        table { width: 100%; border-collapse: collapse; }
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
        td { font-size: 9px; }
        .tc  { text-align: center; }
        .tr  { text-align: right;  }
        .tl  { text-align: left; padding-left: 4px; }
        .total-row td { font-weight: bold; background-color: #f1f5f9; }

        .office-header td {
            background-color: #d1d5db;
            font-weight: bold;
            font-size: 10px;
            padding: 5px 6px;
        }

        .footer { margin-top: 40px; }

        /* Column widths */
        .col-item  { width: 5%; }
        .col-pos   { width: 20%; }
        .col-name  { width: 18%; }
        .col-sg    { width: 7%; }
        .col-amt   { width: 12%; }
        .col-inc   { width: 11%; }
    </style>
</head>
<body>

<div class="header">
    @if(isset($mode) && $mode === 'nosi')
        <div class="title-1">NOSI/NOLP PLANTILLA BASIS CY {{ now()->year + 1 }}</div>
    @else
        <div class="title-1">PLANTILLA OF PERSONNEL CY {{ now()->year + 1 }}</div>
    @endif
    <div class="title-2">Province of Bukidnon</div>
    @if(!empty($officeName))
    <div class="dept">Department/Office: <strong>{{ $officeName }}</strong></div>
    @endif
</div>

@php $grandTotalCurrent = 0; $grandTotalProposed = 0; $grandTotalIncrease = 0; @endphp

@foreach($grouped as $office => $records)
@php
    $officeTotalCurrent  = 0;
    $officeTotalProposed = 0;
    $officeTotalIncrease = 0;
@endphp

<table style="margin-bottom: 10px;">
    <thead>
        <tr class="office-header">
            <td colspan="9">&nbsp;DEPARTMENT / OFFICE: {{ strtoupper($office) }}</td>
        </tr>
        <tr>
            <th colspan="2">Item Number</th>
            <th class="col-pos"  rowspan="2">Position Title</th>
            <th class="col-name" rowspan="2">Name of Incumbent</th>
            <th colspan="2">Current Year Authorized<br>Rate/Annum {{ now()->year }}</th>
            <th colspan="2">Budget Year Proposed<br>Rate/Annum {{ now()->year + 1 }}</th>
            <th class="col-inc"  rowspan="2">Increase/<br>Decrease</th>
        </tr>
        <tr>
            <th style="font-size:7px;">Old</th>
            <th style="font-size:7px;">New</th>
            <th class="col-sg">SG/Step</th>
            <th class="col-amt">Amount</th>
            <th class="col-sg">SG/Step</th>
            <th class="col-amt">Step Increment Amount</th>
        </tr>
        <tr>
            <th style="font-size:8px;">(1)</th>
            <th style="font-size:8px;">(2)</th>
            <th style="font-size:8px;">(3)</th>
            <th style="font-size:8px;">(4)</th>
            <th style="font-size:8px;">(5)</th>
            <th style="font-size:8px;">(6)</th>
            <th style="font-size:8px;">(7)</th>
            <th style="font-size:8px;">(8)</th>
            <th style="font-size:8px;">(9)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($records as $index => $rec)
        @php
            $extractedNum = preg_replace('/[^0-9]/', '', $rec->item ?? '');
            $itemNum = $extractedNum !== '' ? $extractedNum : ($index + 1);

            $curSg   = $rec->salary_grade;
            $curStep = $rec->step ?: 1;

            if ($rec->is_vacant) {
                $curMonthly = \App\Models\SalaryGrade::getRate($curSg, 1);
                $curStep    = 1;
            } else {
                $curMonthly = \App\Models\SalaryGrade::getRate($curSg, $curStep);
            }
            $curAnnual = $curMonthly * 12;

            // Proposed rates & Prorated increase
            $propStep = $curStep;
            $propAnnual = $curAnnual;
            $increase = 0;
            $increaseNote = '';
            
            $due = $rec->next_step_due_date;
            $budgetYear = now()->year + 1;
            $mode = request('mode', 'annual');

            if ($mode === 'nosi' && !$rec->is_vacant && $due && $curStep < 8) {
                $dueType = $rec->due_type;
                $stepIncreaseNum = ($dueType === 'nolp' || $dueType === 'both') ? 2 : 1;
                $newStep = min(8, $curStep + $stepIncreaseNum);
                
                if ($due->year < $budgetYear) {
                    $propStep = $newStep;
                    $propAnnual = \App\Models\SalaryGrade::getRate($curSg, $propStep) * 12;
                    $increase = $propAnnual - $curAnnual;
                } elseif ($due->year == $budgetYear) {
                    $propStep = $newStep;
                    $propAnnual = \App\Models\SalaryGrade::getRate($curSg, $propStep) * 12;
                    
                    $monthlyDiff = ($propAnnual - $curAnnual) / 12;
                    $activeMonths = 12 - $due->month + 1;
                    $increase = $monthlyDiff * $activeMonths;
                    $increaseNote = "<br><span style='font-size:7px;color:#4b5563;'>(" . $due->format('M') . " - Dec)</span>";
                }
            }
            $officeTotalCurrent  += $curAnnual;
            $officeTotalProposed += $propAnnual;
            $officeTotalIncrease += $increase;

            $incumbent = 'Vacant';
            if (!$rec->is_vacant) {
                $last      = ucfirst(strtolower($rec->last_name));
                $first     = ucfirst(strtolower($rec->first_name));
                $mi        = $rec->middle_name ? strtoupper(substr($rec->middle_name, 0, 1)) . '.' : '';
                $incumbent = trim("$first $mi $last");
            } else {
                $incumbent = '<span style="color:#dc2626; font-weight:bold; font-size:9px;">VACANT</span>';
            }
        @endphp
        @php
            $rowStyle = $rec->is_vacant ? 'background-color: #f8fafc; font-style: italic; color: #64748b;' : '';
        @endphp
        <tr style="{{ $rowStyle }}">
            <td class="tc">{{ $itemNum }}</td>
            <td class="tc">{{ $itemNum }}</td>
            <td class="tl">{{ $rec->position_title }}</td>
            <td class="tl">{!! $incumbent !!}</td>
            <td class="tc">{{ $curSg }}/{{ $curStep }}</td>
            <td class="tr">{{ $curAnnual > 0 ? number_format($curAnnual, 2) : '-' }}</td>
            <td class="tc">{{ $curSg }}/{{ $propStep }}</td>
            <td class="tr">{{ $propAnnual > 0 ? number_format($propAnnual, 2) : '-' }}</td>
            <td class="tr">{!! $increase > 0 ? number_format($increase, 2) . $increaseNote : '-' !!}</td>
        </tr>
        @endforeach

        @php
            $grandTotalCurrent  += $officeTotalCurrent;
            $grandTotalProposed += $officeTotalProposed;
            $grandTotalIncrease += $officeTotalIncrease;
        @endphp
        <tr class="total-row">
            <td colspan="4" class="tc">TOTAL</td>
            <td class="tc"></td>
            <td class="tr">{{ number_format($officeTotalCurrent, 2) }}</td>
            <td class="tc"></td>
            <td class="tr">{{ number_format($officeTotalProposed, 2) }}</td>
            <td class="tr">{{ $officeTotalIncrease > 0 ? number_format($officeTotalIncrease, 2) : '-' }}</td>
        </tr>
    </tbody>
</table>
@endforeach

@if(count($grouped) > 1)
<table style="margin-top:10px;">
    <thead>
        <tr style="background-color:#374151;">
            <th colspan="9" style="color:#fff; font-size:11px; text-align:left; padding: 5px 8px;">
                GRAND TOTAL — ALL OFFICES
            </th>
        </tr>
    </thead>
    <tbody>
        <tr class="total-row">
            <td colspan="4" class="tc">&nbsp;</td>
            <td class="tc"></td>
            <td class="tr">{{ number_format($grandTotalCurrent, 2) }}</td>
            <td class="tc"></td>
            <td class="tr">{{ number_format($grandTotalProposed, 2) }}</td>
            <td class="tr">{{ $grandTotalIncrease > 0 ? number_format($grandTotalIncrease, 2) : '-' }}</td>
        </tr>
    </tbody>
</table>
@endif

<div class="footer">
    <table style="border:none; width:50%; margin-left:auto; margin-right:0;">
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
