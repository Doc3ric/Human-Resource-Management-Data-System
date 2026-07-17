<style>
    body { font-family: Arial, sans-serif; font-size: 11px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    th, td { padding: 4px; }
</style>
<table>
    <thead>
        <tr>
            <th colspan="4" style="text-align: left;">LBP FORM 3</th>
            <th></th>
            <th colspan="4" style="text-align: right;">ANNEX F</th>
        </tr>
        <tr>
            <th colspan="9" style="text-align: center; font-weight: bold; font-size: 14px;">PLANTILLA OF PERSONNEL CY <u>{{ $year }}</u></th>
        </tr>
        <tr>
            <th colspan="9" style="text-align: center;">Province: Bukidnon</th>
        </tr>
        <tr>
            <th colspan="9"></th>
        </tr>
    </thead>
    <tbody>
        @foreach($groupedRecords as $officeName => $records)
            <tr>
                <th colspan="9" style="border: 1px solid #000000; font-weight: bold; text-align: left;">Department/Office: {{ strtoupper($officeName ?: 'NO OFFICE ASSIGNED') }}</th>
            </tr>
            <tr>
                <th colspan="2" style="border: 1px solid #000000; text-align: center;">Item Number</th>
                <th rowspan="3" style="border: 1px solid #000000; text-align: center;">Position Title</th>
                <th rowspan="3" style="border: 1px solid #000000; text-align: center;">Name of<br>Incumbent</th>
                <th colspan="2" style="border: 1px solid #000000; text-align: center;">Current Year Authorized</th>
                <th colspan="2" style="border: 1px solid #000000; text-align: center;">Budget Year Proposed</th>
                <th rowspan="3" style="border: 1px solid #000000; text-align: center;">Increase/<br>Decrease<br>(August to<br>December)</th>
            </tr>
            <tr>
                <th rowspan="2" style="border: 1px solid #000000; text-align: center;">Old</th>
                <th rowspan="2" style="border: 1px solid #000000; text-align: center;">New</th>
                <th colspan="2" style="border: 1px solid #000000; text-align: center;">Rate/Annum {{ $year }}</th>
                <th colspan="2" style="border: 1px solid #000000; text-align: center;">Rate/Annum {{ $year }}</th>
            </tr>
            <tr>
                <th style="border: 1px solid #000000; text-align: center;">SG/<br>Step</th>
                <th style="border: 1px solid #000000; text-align: center;">{!! $currentTranche !!}</th>
                <th style="border: 1px solid #000000; text-align: center;">SG/<br>Step</th>
                <th style="border: 1px solid #000000; text-align: center;">{!! $proposedTranche !!}</th>
            </tr>
            <tr>
                <th style="border: 1px solid #000000; text-align: center;">(1)</th>
                <th style="border: 1px solid #000000; text-align: center;">(2)</th>
                <th style="border: 1px solid #000000; text-align: center;">(3)</th>
                <th style="border: 1px solid #000000; text-align: center;">(4)</th>
                <th style="border: 1px solid #000000; text-align: center;">(5)</th>
                <th style="border: 1px solid #000000; text-align: center;">(6)</th>
                <th style="border: 1px solid #000000; text-align: center;">(7)</th>
                <th style="border: 1px solid #000000; text-align: center;">(8)</th>
                <th style="border: 1px solid #000000; text-align: center;">(9)</th>
            </tr>

            @php
                $totalCurrent = 0;
                $totalProposed = 0;
                $totalIncrease = 0;
            @endphp

            @foreach($records as $index => $record)
                @php
                    $isLast = $loop->last;
                    $borderStyle = $isLast ? 'border-bottom: 1px solid #000000;' : '';

                    // Get values, default to 0 if null
                    $currentRate = (float)($record->base_salary_amount ?? 0);
                    // Use proposed rate from DB if available, else fallback to current rate
                    $proposedRate = (float)($record->salary_proposed ?? $currentRate);
                    // Use increase_decrease from DB if available, else dynamically calculate a basic difference
                    $increaseDecrease = (float)($record->increase_decrease ?? max(0, $proposedRate - $currentRate));

                    $totalCurrent += $currentRate;
                    $totalProposed += $proposedRate;
                    $totalIncrease += $increaseDecrease;
                @endphp
                <tr>
                    <td style="border-left: 1px solid #000000; border-right: 1px solid #000000; {{ $borderStyle }} text-align: center;">{{ $record->item_no_old }}</td>
                    <td style="border-right: 1px solid #000000; {{ $borderStyle }} text-align: center;">{{ $record->item_no_new }}</td>
                    <td style="border-right: 1px solid #000000; {{ $borderStyle }}">{{ $record->position_title }}</td>
                    <td style="border-right: 1px solid #000000; {{ $borderStyle }}">{{ $record->full_name }}</td>
                    
                    <td style="border-right: 1px solid #000000; {{ $borderStyle }} text-align: center;">{{ $record->salary_grade }}/{{ $record->step }}</td>
                    <td style="border-right: 1px solid #000000; {{ $borderStyle }} text-align: right;">{{ number_format($currentRate, 2) }}</td>
                    
                    <td style="border-right: 1px solid #000000; {{ $borderStyle }} text-align: center;">{{ $record->sg_proposed ?? $record->salary_grade }}/{{ $record->step_proposed ?? $record->step }}</td>
                    <td style="border-right: 1px solid #000000; {{ $borderStyle }} text-align: right;">{{ number_format($proposedRate, 2) }}</td>
                    
                    <td style="border-right: 1px solid #000000; {{ $borderStyle }} text-align: right;">{{ $increaseDecrease > 0 ? number_format($increaseDecrease, 2) : '-' }}</td>
                </tr>
            @endforeach

            <tr>
                <td colspan="5" style="border: 1px solid #000000; font-weight: bold; text-align: center;">TOTAL</td>
                <td style="border: 1px solid #000000; font-weight: bold; text-align: right;">{{ number_format($totalCurrent, 2) }}</td>
                <td style="border: 1px solid #000000;"></td>
                <td style="border: 1px solid #000000; font-weight: bold; text-align: right;">{{ number_format($totalProposed, 2) }}</td>
                <td style="border: 1px solid #000000; font-weight: bold; text-align: right;">{{ $totalIncrease > 0 ? number_format($totalIncrease, 2) : '-' }}</td>
            </tr>
            
            <tr><td colspan="9"></td></tr>
            <tr><td colspan="9"></td></tr>
        @endforeach

        <tr>
            <td colspan="9"></td>
        </tr>
        <tr>
            <td colspan="3" style="text-align: left; padding-left: 20px;">Prepared by:</td>
            <td colspan="3" style="text-align: left; padding-left: 20px;">Reviewed by:</td>
            <td colspan="3" style="text-align: left; padding-left: 20px;">Approved by:</td>
        </tr>
        <tr>
            <td colspan="9"></td>
        </tr>
        <tr>
            <td colspan="9"></td>
        </tr>
        <tr>
            <td colspan="3" style="text-align: left; padding-left: 50px; font-weight: bold;">{{ $preparedBy }}</td>
            <td colspan="3" style="text-align: left; padding-left: 50px; font-weight: bold;">{{ $reviewedBy ?? 'MAYFE P. ALERTA' }}</td>
            <td colspan="3" style="text-align: left; padding-left: 50px; font-weight: bold;">{{ $approvedBy ?? 'ROGELIO NEIL P. ROQUE' }}</td>
        </tr>
        <tr>
            <td colspan="3" style="text-align: left; padding-left: 50px;">PG Department Head</td>
            <td colspan="3" style="text-align: left; padding-left: 50px;">Provincial Budget Officer</td>
            <td colspan="3" style="text-align: left; padding-left: 50px;">Provincial Governor</td>
        </tr>
    </tbody>
</table>
