<table>
    {{-- ── Title header rows ── --}}
    <thead>
        <tr>
            <th colspan="9" style="text-align:right; font-weight:bold; font-size:11px;">Annex F</th>
        </tr>
        <tr>
            <th colspan="9" style="text-align:left; font-weight:bold; font-size:11px;">LBP Form No. 3</th>
        </tr>
        <tr>
            <th colspan="9" style="text-align:center; font-weight:bold; font-size:14px;">PLANTILLA OF LGU PERSONNEL</th>
        </tr>
        <tr>
            <th colspan="9" style="text-align:center; font-weight:bold; font-size:12px;">BUDGET YEAR {{ now()->year + 1 }}</th>
        </tr>
        <tr>
            <th colspan="9" style="text-align:center; font-weight:bold; font-size:13px;">PROVINCE OF BUKIDNON</th>
        </tr>
        <tr>
            <th colspan="9"></th>
        </tr>{{-- spacer --}}

        {{-- Column headings --}}
        <tr>
            <th
                style="font-weight:bold; border:1px solid #000; text-align:center; vertical-align:middle;">
                Item No. (Old)</th>
            <th
                style="font-weight:bold; border:1px solid #000; text-align:center; vertical-align:middle;">
                Item No. (New)</th>
            <th
                style="font-weight:bold; border:1px solid #000; text-align:center; vertical-align:middle;">
                Position Title</th>
            <th
                style="font-weight:bold; border:1px solid #000; text-align:center; vertical-align:middle;">
                Name of Incumbent</th>
            <th
                style="font-weight:bold; border:1px solid #000; text-align:center; vertical-align:middle;">
                Current SG/Step ({{ now()->year }})</th>
            <th
                style="font-weight:bold; border:1px solid #000; text-align:center; vertical-align:middle;">
                Current Annual Rate ({{ now()->year }})</th>
            <th
                style="font-weight:bold; border:1px solid #000; text-align:center; vertical-align:middle;">
                Proposed SG/Step ({{ now()->year + 1 }})</th>
            <th
                style="font-weight:bold; border:1px solid #000; text-align:center; vertical-align:middle;">
                Proposed Annual Rate ({{ now()->year + 1 }})</th>
            <th
                style="font-weight:bold; border:1px solid #000; text-align:center; vertical-align:middle;">
                Increase/Decrease</th>
        </tr>
        <tr>
            <th style="border:1px solid #000; text-align:center; font-size:9px;">(1)</th>
            <th style="border:1px solid #000; text-align:center; font-size:9px;">(2)</th>
            <th style="border:1px solid #000; text-align:center; font-size:9px;">(3)</th>
            <th style="border:1px solid #000; text-align:center; font-size:9px;">(4)</th>
            <th style="border:1px solid #000; text-align:center; font-size:9px;">(5)</th>
            <th style="border:1px solid #000; text-align:center; font-size:9px;">(6)</th>
            <th style="border:1px solid #000; text-align:center; font-size:9px;">(7)</th>
            <th style="border:1px solid #000; text-align:center; font-size:9px;">(8)</th>
            <th style="border:1px solid #000; text-align:center; font-size:9px;">(9)</th>
        </tr>
    </thead>

    <tbody>
        @foreach($grouped as $office => $records)
            @php
                $officeTotalCurrent = 0;
                $officeTotalProposed = 0;
                $officeTotalIncrease = 0;
            @endphp
            {{-- Office header row --}}
            <tr>
                <td colspan="9" style="font-weight:bold; border:1px solid #000;">
                    DEPARTMENT / OFFICE: {{ strtoupper($office) }}
                </td>
            </tr>

            @foreach($records as $index => $rec)
                @php
                    $curSg = $rec->salary_grade;
                    $curStep = $rec->step ?: 1;

                    if ($rec->authorized_annual_salary > 0) {
                        $curAnnual = (float)$rec->authorized_annual_salary;
                    } else {
                        if ($rec->is_vacant) {
                            $curMonthly = \App\Models\SalaryGrade::getRate($curSg, 1);
                            $curStep = 1;
                        } else {
                            $curMonthly = \App\Models\SalaryGrade::getRate($curSg, $curStep);
                        }
                        $curAnnual = $curMonthly * 12;
                    }

                    // Proposed rates & Prorated increase
                    $propStep = $rec->step_proposed ?: $curStep;
                    $propAnnual = $rec->salary_proposed > 0 ? (float)$rec->salary_proposed : $curAnnual;
                    $increase = (float)$rec->increase_decrease;
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
                            $increaseNote = "<br>(" . $due->format('M') . " - Dec)";
                        }
                    }
                    $officeTotalCurrent += $curAnnual;
                    $officeTotalProposed += $propAnnual;
                    $officeTotalIncrease += $increase;

                    // Incumbent name
                    $incumbent = 'Vacant';
                    if (!$rec->is_vacant) {
                        $last = ucfirst(strtolower($rec->last_name));
                        $first = ucfirst(strtolower($rec->first_name));
                        $mi = $rec->middle_name ? strtoupper(substr($rec->middle_name, 0, 1)) . '.' : '';
                        $incumbent = trim("$first $mi $last");
                    }
                    $extractedNum = preg_replace('/[^0-9]/', '', $rec->item_no_new ?? '');
                    $itemNum = $extractedNum !== '' ? $extractedNum : ($index + 1);
                @endphp
                <tr>
                    <td style="border:1px solid #000; text-align:center;">{{ $itemNum }}</td>
                    <td style="border:1px solid #000; text-align:center;">{{ $itemNum }}</td>
                    <td style="border:1px solid #000; text-align:left; padding-left:4px;">{{ $rec->position_title }}</td>
                    <td style="border:1px solid #000; text-align:left; padding-left:4px;">{{ $incumbent }}</td>
                    <td style="border:1px solid #000; text-align:center;">{{ $curSg }}/{{ $curStep }}</td>
                    <td style="border:1px solid #000; text-align:right;">
                        {{ $curAnnual > 0 ? number_format($curAnnual, 2) : '-' }}</td>
                    <td style="border:1px solid #000; text-align:center;">{{ $curSg }}/{{ $propStep }}</td>
                    <td style="border:1px solid #000; text-align:right;">
                        {{ $propAnnual > 0 ? number_format($propAnnual, 2) : '-' }}</td>
                    <td style="border:1px solid #000; text-align:right;">{!! $increase > 0 ? number_format($increase, 2) . $increaseNote : '-' !!}
                    </td>
                </tr>
            @endforeach

            {{-- Office subtotal --}}
            <tr>
                <td colspan="4"
                    style="border:1px solid #000; text-align:center; font-weight:bold; ">
                    TOTAL — {{ strtoupper($office) }}
                </td>
                <td style="border:1px solid #000;"></td>
                <td style="border:1px solid #000; text-align:right; font-weight:bold; ">
                    {{ number_format($officeTotalCurrent, 2) }}</td>
                <td style="border:1px solid #000;"></td>
                <td style="border:1px solid #000; text-align:right; font-weight:bold; ">
                    {{ number_format($officeTotalProposed, 2) }}</td>
                <td style="border:1px solid #000; text-align:right; font-weight:bold; ">
                    {{ $officeTotalIncrease > 0 ? number_format($officeTotalIncrease, 2) : '-' }}</td>
            </tr>
            <tr>
                <td colspan="9"></td>
            </tr>{{-- spacer between offices --}}
        @endforeach
    </tbody>
</table>