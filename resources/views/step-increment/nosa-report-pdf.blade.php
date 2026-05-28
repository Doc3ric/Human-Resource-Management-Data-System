<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Notice of Salary Adjustment</title>
    <style>
        @page {
            margin: 0.4in 0.5in;
            size: legal landscape;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 14px;
        }

        .header .title-1 {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header .title-2 {
            font-size: 11px;
            text-transform: uppercase;
            margin-top: 3px;
        }

        .header .title-3 {
            font-size: 10px;
            color: #333;
            margin-top: 4px;
        }

        .header .dept {
            font-size: 11px;
            text-align: left;
            margin-top: 10px;
        }

        .header .dept strong {
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
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

        td {
            font-size: 9px;
        }

        .tc {
            text-align: center;
        }

        .tr {
            text-align: right;
        }

        .tl {
            text-align: left;
            padding-left: 4px;
        }

        .total-row td {
            font-weight: bold;
            background-color: #f1f5f9;
        }

        .office-header td {
            background-color: #fecdd3;
            font-weight: bold;
            font-size: 10px;
            padding: 5px 6px;
        }

        .col-item {
            width: 4%;
        }

        .col-pos {
            width: 20%;
        }

        .col-name {
            width: 18%;
        }

        .col-sg {
            width: 7%;
        }

        .col-amt {
            width: 12%;
        }

        .col-inc {
            width: 11%;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="title-1">Notice of Salary Adjustment</div>
        <div class="title-2">Province of Bukidnon — {{ strtoupper($type ?? 'Permanent') }}</div>
        @if($activeSchedule)
            <div class="title-3">
                Pursuant to: <strong>{{ $activeSchedule->name }}</strong>
                @if($activeSchedule->lbc_number)
                    &nbsp;|&nbsp; LBC # <strong>{{ $activeSchedule->lbc_number }}</strong>
                @endif
                @if($activeSchedule->effective_date)
                    &nbsp;|&nbsp; Effective: <strong>{{ $activeSchedule->effective_date->format('F j, Y') }}</strong>
                @endif
            </div>
        @endif
        @if(!empty($officeName))
            <div class="dept">Department/Office: <strong>{{ $officeName }}</strong></div>
        @endif
    </div>

    @php $grandTotalPrev = 0;
        $grandTotalNew = 0;
    $grandTotalInc = 0; @endphp

    @foreach($grouped as $office => $records)
        @php $officeTotalPrev = 0;
            $officeTotalNew = 0;
        $officeTotalInc = 0; @endphp

        <table style="margin-bottom:10px;">
            <thead>
                <tr class="office-header">
                    <td colspan="9">&nbsp;DEPARTMENT / OFFICE: {{ strtoupper($office) }}</td>
                </tr>
                <tr>
                    <th colspan="2" rowspan="3" style="vertical-align:bottom;">Item<br>Number<br><br></th>
                    <th rowspan="3" class="col-pos">Position Title</th>
                    <th rowspan="3" class="col-name">Name of Incumbent</th>
                    <th colspan="2">Current Year Authorized<br>Rate/Annum
                        {{ $previousSchedule && $previousSchedule->effective_date ? $previousSchedule->effective_date->format('Y') : now()->year }}
                    </th>
                    <th colspan="2">Budget Year Proposed<br>Rate/Annum
                        {{ $activeSchedule && $activeSchedule->effective_date ? $activeSchedule->effective_date->format('Y') : now()->year }}
                    </th>
                    <th rowspan="3" class="col-inc">Increase/<br>Decrease</th>
                </tr>
                <tr>
                    <th class="col-sg">SG/<br>Step</th>
                    <th class="col-amt">
                        @if($previousSchedule && $previousSchedule->lbc_number)<strong>{{ $previousSchedule->lbc_number }}</strong><br>@endif
                        @if($previousSchedule){{ $previousSchedule->name }}<br>@endif
                        Amount
                    </th>
                    <th class="col-sg">SG/<br>Step</th>
                    <th class="col-amt">
                        @if($activeSchedule && $activeSchedule->lbc_number)<strong>{{ $activeSchedule->lbc_number }}</strong><br>@endif
                        @if($activeSchedule){{ $activeSchedule->name }}<br>@endif
                        Amount
                    </th>
                </tr>
                <tr>
                    <th style="font-size:7px;">Old</th>
                    <th style="font-size:7px;">Old<br>(1)</th>
                    <th style="font-size:7px;">New</th>
                    <th style="font-size:7px;">New<br>(2)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $index => $rec)
                    @php
                        $sg = $rec->salary_grade;
                        $step = $rec->step ?: 1;
                        $extractedNum = preg_replace('/[^0-9]/', '', $rec->item ?? '');
                        $itemNum = $extractedNum !== '' ? $extractedNum : ($index + 1);

                        if ($rec->is_vacant) {
                            $prevAnnual = 0;
                            $newAnnual = 0;
                        } else {
                            if ($previousSchedule) {
                                $prevMonthly = \App\Models\SalaryGrade::getRateForSchedule($previousSchedule->id, $sg, $step);
                                $prevAnnual = $prevMonthly * 12;
                            } else {
                                $prevAnnual = (float) ($rec->actual_annual_salary ?: 0);
                            }
                            $newMonthly = \App\Models\SalaryGrade::getRateForSchedule($activeSchedule->id, $sg, $step);
                            $newAnnual = $newMonthly * 12;
                        }

                        $increase = $newAnnual - $prevAnnual;
                        $officeTotalPrev += $prevAnnual;
                        $officeTotalNew += $newAnnual;
                        $officeTotalInc += $increase;

                        if ($rec->is_vacant) {
                            $incumbent = '<span style="color:#dc2626;font-weight:bold;">VACANT</span>';
                        } else {
                            $last = ucfirst(strtolower($rec->last_name));
                            $first = ucfirst(strtolower($rec->first_name));
                            $mi = $rec->middle_name ? strtoupper(substr($rec->middle_name, 0, 1)) . '.' : '';
                            $incumbent = trim("$first $mi $last");
                        }
                        $rowStyle = $rec->is_vacant ? 'background:#f8fafc;font-style:italic;color:#64748b;' : '';
                    @endphp
                    <tr style="{{ $rowStyle }}">
                        <td class="tc">{{ $itemNum }}</td>
                        <td class="tc">{{ $itemNum }}</td>
                        <td class="tl">{{ $rec->position_title }}</td>
                        <td class="tl">{!! $incumbent !!}</td>
                        <td class="tc">{{ $sg }}/{{ $step }}</td>
                        <td class="tr">{{ $prevAnnual > 0 ? number_format($prevAnnual, 2) : '-' }}</td>
                        <td class="tc">{{ $sg }}/{{ $step }}</td>
                        <td class="tr">{{ $newAnnual > 0 ? number_format($newAnnual, 2) : '-' }}</td>
                        <td class="tr">{{ $increase != 0 ? number_format($increase, 2) : '-' }}</td>
                    </tr>
                @endforeach

                @php
                    $grandTotalPrev += $officeTotalPrev;
                    $grandTotalNew += $officeTotalNew;
                    $grandTotalInc += $officeTotalInc;
                @endphp
                <tr class="total-row">
                    <td colspan="4" class="tc">TOTAL</td>
                    <td class="tc"></td>
                    <td class="tr">{{ number_format($officeTotalPrev, 2) }}</td>
                    <td class="tc"></td>
                    <td class="tr">{{ number_format($officeTotalNew, 2) }}</td>
                    <td class="tr">{{ $officeTotalInc != 0 ? number_format($officeTotalInc, 2) : '-' }}</td>
                </tr>
            </tbody>
        </table>
    @endforeach

    @if(count($grouped) > 1)
        <table style="margin-top:10px;">
            <thead>
                <tr style="background:#7f1d1d;">
                    <th colspan="9" style="color:#fff;font-size:11px;text-align:left;padding:5px 8px;">
                        GRAND TOTAL — ALL OFFICES
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr class="total-row">
                    <td colspan="4" class="tc">&nbsp;</td>
                    <td class="tc"></td>
                    <td class="tr">{{ number_format($grandTotalPrev, 2) }}</td>
                    <td class="tc"></td>
                    <td class="tr">{{ number_format($grandTotalNew, 2) }}</td>
                    <td class="tr">{{ $grandTotalInc != 0 ? number_format($grandTotalInc, 2) : '-' }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    <div style="margin-top:40px;">
        <table style="border:none;width:50%;margin-left:auto;margin-right:0;">
            <tr style="border:none;">
                <td style="border:none;text-align:center;font-weight:bold;">AIDA B. LOVERES</td>
            </tr>
            <tr style="border:none;">
                <td style="border:none;text-align:center;">P.G. Department Head / PHRM Officer</td>
            </tr>
        </table>
    </div>

</body>

</html>