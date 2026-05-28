<table>
    <thead>
        <tr>
            <th colspan="11" style="text-align: center; font-weight: bold;">List of Officials and Employees</th>
        </tr>
        <tr>
            <th colspan="11" style="text-align: center; font-weight: bold;">Granted Step Increments Pursuant to</th>
        </tr>
        <tr>
            <th colspan="11" style="text-align: center; font-weight: bold;">Joint Civil Service Commission</th>
        </tr>
        <tr>
            <th colspan="11" style="text-align: center; font-weight: bold;">Department of Budget and Management</th>
        </tr>
        <tr>
            <th colspan="11" style="text-align: center; font-weight: bold;">Circular No. 1 s. 1990</th>
        </tr>
        <tr><th colspan="11"></th></tr>{{-- spacer --}}
        <tr>
            <th colspan="11" style="text-align: center; font-weight: bold; font-size: 12px;">Step Increment Based on Length of Service</th>
        </tr>
        <tr>
            <th colspan="11" style="text-align: center; font-weight: bold; font-size: 12px;">For CY {{ now()->year }}</th>
        </tr>
        <tr><th colspan="11"></th></tr>{{-- spacer --}}
        <tr>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center; vertical-align: middle;">EFFECTIVITY DATE</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center; vertical-align: middle;">TO</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center; vertical-align: middle;">NAME</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center; vertical-align: middle;">ITEM NO.</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center; vertical-align: middle;">DESIGNATION</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center; vertical-align: middle;">SG / STEP</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center; vertical-align: middle;">STEP GRANTED</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center; vertical-align: middle;">MONTHLY SALARY PRIOR TO INCREMENT</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center; vertical-align: middle;">ADJUSTED SG/STEP AFTER INCREMENT</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center; vertical-align: middle;">MONTHLY SALARY AFTER INCREMENT</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center; vertical-align: middle;">DIFFERENCE</th>
        </tr>
    </thead>

    <tbody>
        @foreach($grouped as $office => $records)
            <tr>
                <td colspan="11" style="font-weight: bold; text-align: left; background-color: #d1d5db;">
                    DEPARTMENT / OFFICE:  {{ strtoupper($office) }}
                </td>
            </tr>
            @foreach($records as $rec)
                @php
                    $sg      = $rec->salary_grade;
                    $curStep = $rec->step ?: 1;
                    $curMonthly = \App\Models\SalaryGrade::getRate($sg, $curStep) ?: 0;

                    $dueType     = $rec->due_type;
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
                    <td style="border: 1px solid #000; text-align: center;">{{ $effectivityFrom }}</td>
                    <td style="border: 1px solid #000; text-align: center;">-</td>
                    <td style="border: 1px solid #000;">{{ $name }}</td>
                    <td style="border: 1px solid #000; text-align: center;">{{ $rec->item }}</td>
                    <td style="border: 1px solid #000;">{{ $rec->position_title }}</td>
                    <td style="border: 1px solid #000; text-align: center;">{{ $sg }}/{{ $curStep }}</td>
                    <td style="border: 1px solid #000; text-align: center;">{{ $stepIncrease }}</td>
                    <td style="border: 1px solid #000; text-align: right;">{{ number_format($curMonthly, 2) }}</td>
                    <td style="border: 1px solid #000; text-align: center;">{{ $sg }}/{{ $newStep }}</td>
                    <td style="border: 1px solid #000; text-align: right;">{{ number_format($newMonthly, 2) }}</td>
                    <td style="border: 1px solid #000; text-align: right;">{{ number_format($diff, 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="11" style="border: 1px solid #000;"></td>
            </tr>
        @endforeach
    </tbody>
</table>
