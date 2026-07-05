<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Casual Employees Inventory</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: Arial, sans-serif; font-size: 8pt; color: #000; }

        .page-title {
            text-align: center; font-size: 13pt; font-weight: bold;
            margin-bottom: 2px; text-transform: uppercase;
        }
        .page-sub { text-align: center; font-size: 9pt; margin-bottom: 2px; }
        .page-date { text-align: center; font-size: 8pt; color: #555; margin-bottom: 10px; }

        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th {
            background: #831843; color: #fff; font-size: 7pt; font-weight: bold;
            padding: 5px 6px; text-align: center; border: 1px solid #9d174d;
        }
        td { padding: 4px 6px; border: 1px solid #e5e7eb; font-size: 7.5pt; vertical-align: top; }
        tr:nth-child(even) td { background: #fdf2f8; }

        .vacant-cell { color: #dc2626; font-style: italic; font-weight: bold; }
        .sg-badge { font-weight: bold; color: #9d174d; }
        .inc-pos { color: #16a34a; }
        .inc-neg { color: #dc2626; }

        .office-header {
            background: #f9a8d4; font-weight: bold; font-size: 8pt;
            padding: 5px 6px; border: 1px solid #f472b6;
            text-transform: uppercase; color: #831843;
        }

        .total-row td {
            background: #831843 !important; color: #fff;
            font-weight: bold; text-align: right;
        }
        .total-row td:first-child { text-align: left; }

        .footer { margin-top: 24px; font-size: 8pt; }
        .sig-table { width: 100%; border-collapse: collapse; }
        .sig-table td { padding: 16px 10px 4px; text-align: center; border: none; font-size: 8pt; }
        .sig-line { border-top: 1px solid #000; margin-top: 2px; padding-top: 2px; font-weight: bold; }
    </style>
</head>
<body>

<div class="page-title">Casual Employees Inventory</div>
<div class="page-sub">Province of Bukidnon — PHRMO</div>
<div class="page-date">As of: {{ now()->format('F d, Y') }}</div>

<table>
    <thead>
        @php
            $leftColspan = 1 
                + (empty($columns) || in_array('office', $columns) ? 1 : 0)
                + (empty($columns) || in_array('item_new', $columns) || in_array('item_old', $columns) ? 1 : 0)
                + (empty($columns) || in_array('position_title', $columns) ? 1 : 0)
                + (empty($columns) || in_array('name', $columns) ? 1 : 0)
                + (empty($columns) || in_array('legislative_district', $columns) ? 1 : 0)
                + (empty($columns) || in_array('sg_step_current', $columns) ? 1 : 0);
            
            $hasCurSal = empty($columns) || in_array('annual_salary_current', $columns);
            $hasPropSg = empty($columns) || in_array('sg_step_proposed', $columns);
            $hasPropSal = empty($columns) || in_array('annual_salary_proposed', $columns);
            $hasIncDec = empty($columns) || in_array('increase_decrease', $columns);
            $hasMonthRate = empty($columns) || in_array('monthly_rate', $columns);

            $rightColspan = ($hasIncDec ? 1 : 0) + ($hasMonthRate ? 1 : 0);
            $totalColspan = $leftColspan + ($hasCurSal ? 1 : 0) + ($hasPropSg ? 1 : 0) + ($hasPropSal ? 1 : 0) + $rightColspan;
        @endphp
        <tr>
            <th style="width:3%;">#</th>
            @if(empty($columns) || in_array('office', $columns))<th style="width:15%;">OFFICE</th>@endif
            @if(empty($columns) || in_array('item_new', $columns) || in_array('item_old', $columns))<th style="width:4%;">Item No.</th>@endif
            @if(empty($columns) || in_array('position_title', $columns))<th style="width:14%;">Position Title</th>@endif
            @if(empty($columns) || in_array('name', $columns))<th style="width:14%;">Name of Incumbent</th>@endif
            @if(empty($columns) || in_array('legislative_district', $columns))<th style="width:10%;">Legislative District</th>@endif
            @if(empty($columns) || in_array('sg_step_current', $columns))<th style="width:6%;">SG/Step (Cur)</th>@endif
            @if($hasCurSal)<th style="width:8%;">Annual Salary (Cur) ₱</th>@endif
            @if($hasPropSg)<th style="width:6%;">SG/Step (Prop)</th>@endif
            @if($hasPropSal)<th style="width:8%;">Annual Salary (Prop) ₱</th>@endif
            @if($hasIncDec)<th style="width:6%;">Increase/ Decrease ₱</th>@endif
            @if($hasMonthRate)<th style="width:6%;">Monthly Rate ₱</th>@endif
        </tr>
    </thead>
    <tbody>
        @php
            $grouped = $records->groupBy('office');
            $grandTotalCur  = 0;
            $grandTotalProp = 0;
            $rowNum = 0;
        @endphp

        @forelse($grouped as $office => $group)
            <tr>
                <td colspan="{{ $totalColspan }}" class="office-header">
                    <i>{{ strtoupper($office ?: 'UNASSIGNED') }}</i>
                </td>
            </tr>

            @php $offTotalCur = 0; $offTotalProp = 0; @endphp

            @foreach($group as $r)
                @php
                    $rowNum++;
                    $offTotalCur  += (float)($r->salary_current  ?? 0);
                    $offTotalProp += (float)($r->salary_proposed ?? 0);
                    $grandTotalCur  += (float)($r->salary_current  ?? 0);
                    $grandTotalProp += (float)($r->salary_proposed ?? 0);
                    $inc = $r->increase_decrease !== null ? (float)$r->increase_decrease : null;
                @endphp
                <tr>
                    <td style="text-align:center;color:#9ca3af;">{{ $rowNum }}</td>
                    @if(empty($columns) || in_array('office', $columns))<td>{{ $r->office }}</td>@endif
                    @if(empty($columns) || in_array('item_new', $columns) || in_array('item_old', $columns))<td style="text-align:center;">{{ $r->item_no_new_no_new ?? $r->item_no_new_no_old }}</td>@endif
                    @if(empty($columns) || in_array('position_title', $columns))<td>{{ $r->position_title }}</td>@endif
                    @if(empty($columns) || in_array('name', $columns))<td>
                        @if($r->is_vacant)
                            <span class="vacant-cell">VACANT</span>
                        @else
                            {{ $r->full_name }}
                        @endif
                    </td>@endif
                    @if(empty($columns) || in_array('legislative_district', $columns))<td>{{ $r->legislative_district }}</td>@endif
                    @if(empty($columns) || in_array('sg_step_current', $columns))<td style="text-align:center;" class="sg-badge">
                        {{ $r->sg_current ? "SG-{$r->sg_current}/S{$r->step_current}" : '—' }}
                    </td>@endif
                    @if($hasCurSal)<td style="text-align:right;">
                        {{ $r->salary_current ? number_format($r->salary_current, 2) : '—' }}
                    </td>@endif
                    @if($hasPropSg)<td style="text-align:center;color:#3730a3;font-weight:bold;">
                        {{ $r->sg_proposed ? "SG-{$r->sg_proposed}/S{$r->step_proposed}" : '—' }}
                    </td>@endif
                    @if($hasPropSal)<td style="text-align:right;">
                        {{ $r->salary_proposed ? number_format($r->salary_proposed, 2) : '—' }}
                    </td>@endif
                    @if($hasIncDec)<td style="text-align:right;" class="{{ $inc !== null && $inc >= 0 ? 'inc-pos' : 'inc-neg' }}">
                        {{ $inc !== null ? ($inc >= 0 ? '+' : '') . number_format($inc, 2) : '—' }}
                    </td>@endif
                    @if($hasMonthRate)<td style="text-align:right;">
                        {{ $r->current_rate ? number_format($r->current_rate, 2) : '—' }}
                    </td>@endif
                </tr>
            @endforeach

            {{-- Office subtotal --}}
            <tr>
                <td colspan="{{ $leftColspan }}" style="text-align:right;font-weight:bold;background:#fce7f3;color:#831843;border:1px solid #f9a8d4;">
                    Subtotal — {{ $group->count() }} records
                </td>
                @if($hasCurSal)<td style="text-align:right;font-weight:bold;background:#fce7f3;color:#831843;border:1px solid #f9a8d4;">
                    {{ number_format($offTotalCur, 2) }}
                </td>@endif
                @if($hasPropSg)<td style="background:#fce7f3;border:1px solid #f9a8d4;"></td>@endif
                @if($hasPropSal)<td style="text-align:right;font-weight:bold;background:#fce7f3;color:#831843;border:1px solid #f9a8d4;">
                    {{ number_format($offTotalProp, 2) }}
                </td>@endif
                @if($rightColspan > 0)<td colspan="{{ $rightColspan }}" style="background:#fce7f3;border:1px solid #f9a8d4;"></td>@endif
            </tr>
        @empty
            <tr>
                <td colspan="{{ $totalColspan }}" style="text-align:center;padding:20px;color:#94a3b8;">
                    No casual records found.
                </td>
            </tr>
        @endforelse

        {{-- Grand Total --}}
        @if($records->count() > 0)
        <tr class="total-row">
            <td colspan="{{ $leftColspan }}" style="text-align:right;">GRAND TOTAL ({{ $records->count() }} records)</td>
            @if($hasCurSal)<td style="text-align:right;">{{ number_format($grandTotalCur, 2) }}</td>@endif
            @if($hasPropSg)<td></td>@endif
            @if($hasPropSal)<td style="text-align:right;">{{ number_format($grandTotalProp, 2) }}</td>@endif
            @if($rightColspan > 0)<td colspan="{{ $rightColspan }}"></td>@endif
        </tr>
        @endif
    </tbody>
</table>

<div class="footer">
    <table class="sig-table">
        <tr>
            <td>
                <div class="sig-line">Prepared by:</div>
                <div>HR Officer</div>
            </td>
            <td>
                <div class="sig-line">Reviewed by:</div>
                <div>Provincial Budget Officer</div>
            </td>
            <td>
                <div class="sig-line">Approved by:</div>
                <div>Provincial Governor</div>
            </td>
        </tr>
    </table>
</div>

</body>
</html>
