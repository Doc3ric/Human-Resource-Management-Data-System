<table>
    {{-- ── Title header rows ── --}}
    <thead>
        <tr>
            <th colspan="9" style="text-align:center; font-weight:bold; font-size:14px;">PLANTILLA OF PERSONNEL CY
                {{ now()->year + 1 }}</th>
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
                style="font-weight:bold; border:1px solid #000; text-align:center; vertical-align:middle; background-color:#e2e8f0;">
                Item No. (Old)</th>
            <th
                style="font-weight:bold; border:1px solid #000; text-align:center; vertical-align:middle; background-color:#e2e8f0;">
                Item No. (New)</th>
            <th
                style="font-weight:bold; border:1px solid #000; text-align:center; vertical-align:middle; background-color:#e2e8f0;">
                Position Title</th>
            <th
                style="font-weight:bold; border:1px solid #000; text-align:center; vertical-align:middle; background-color:#e2e8f0;">
                Name of Incumbent</th>
            <th
                style="font-weight:bold; border:1px solid #000; text-align:center; vertical-align:middle; background-color:#e2e8f0;">
                Current SG/Step ({{ now()->year }})</th>
            <th
                style="font-weight:bold; border:1px solid #000; text-align:center; vertical-align:middle; background-color:#e2e8f0;">
                Current Annual Rate ({{ now()->year }})</th>
            <th
                style="font-weight:bold; border:1px solid #000; text-align:center; vertical-align:middle; background-color:#e2e8f0;">
                Proposed SG/Step ({{ now()->year + 1 }})</th>
            <th
                style="font-weight:bold; border:1px solid #000; text-align:center; vertical-align:middle; background-color:#e2e8f0;">
                Proposed Annual Rate ({{ now()->year + 1 }})</th>
            <th
                style="font-weight:bold; border:1px solid #000; text-align:center; vertical-align:middle; background-color:#e2e8f0;">
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
                <td colspan="9" style="font-weight:bold; background-color:#d1d5db; border:1px solid #000;">
                    DEPARTMENT / OFFICE: {{ strtoupper($office) }}
                </td>
            </tr>

            @foreach($records as $index => $rec)
                @php
                    $curSg = $rec->salary_grade;
                    $curStep = $rec->step ?: 1;

                    if ($rec->is_vacant) {
                        $curMonthly = \App\Models\SalaryGrade::getRate($curSg, 1);
                        $curStep = 1;
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
                    $extractedNum = preg_replace('/[^0-9]/', '', $rec->item ?? '');
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
                    style="border:1px solid #000; text-align:center; font-weight:bold; background-color:#f1f5f9;">
                    TOTAL — {{ strtoupper($office) }}
                </td>
                <td style="border:1px solid #000;"></td>
                <td style="border:1px solid #000; text-align:right; font-weight:bold; background-color:#f1f5f9;">
                    {{ number_format($officeTotalCurrent, 2) }}</td>
                <td style="border:1px solid #000;"></td>
                <td style="border:1px solid #000; text-align:right; font-weight:bold; background-color:#f1f5f9;">
                    {{ number_format($officeTotalProposed, 2) }}</td>
                <td style="border:1px solid #000; text-align:right; font-weight:bold; background-color:#f1f5f9;">
                    {{ $officeTotalIncrease > 0 ? number_format($officeTotalIncrease, 2) : '-' }}</td>
            </tr>
            <tr>
                <td colspan="9"></td>
            </tr>{{-- spacer between offices --}}
        @endforeach
    </tbody>
</table>