<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Job Order Inventory — {{ now()->format('Y-m-d') }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 7.5px; color: #1e293b; }

        .header { text-align: center; margin-bottom: 8px; }
        .header h1 { font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .header p  { font-size: 8px; color: #475569; margin-top: 2px; }

        table { width: 100%; border-collapse: collapse; page-break-inside: auto; }
        thead tr { background: #1e3a5f; color: #fff; }
        thead tr th {
            padding: 5px 4px; text-align: center; font-size: 6.5px;
            font-weight: bold; text-transform: uppercase; letter-spacing: .4px;
            border: 1px solid #2d5a8e;
        }
        thead tr.sub-hdr th {
            background: #2d5a8e; font-size: 6px; padding: 3px 4px;
        }
        tbody tr { page-break-inside: avoid; }
        tbody tr:nth-child(odd)  { background: #f8fafc; }
        tbody tr:nth-child(even) { background: #fff; }
        tbody td {
            padding: 4px 4px; border: 1px solid #e2e8f0;
            text-align: center; vertical-align: middle; font-size: 7px;
        }
        tbody td.tl { text-align: left; }
        .chk { color: #059669; font-weight: bold; font-size: 9px; }

        .footer { margin-top: 10px; font-size: 7px; color: #64748b; text-align: right; }
    </style>
</head>
<body>

<div class="header">
    <h1>Job Order Inventory</h1>
    <p>As of {{ now()->format('F d, Y') }} &nbsp;|&nbsp; Total Records: {{ $records->count() }}</p>
</div>

<table>
    <thead>
        @php
            $nameCols = 0;
            if(empty($columns) || in_array('family_name', $columns)) $nameCols++;
            if(empty($columns) || in_array('first_name', $columns)) $nameCols++;
            if(empty($columns) || in_array('mi', $columns)) $nameCols++;
            if(empty($columns) || in_array('ext', $columns)) $nameCols++;

            $lengthCols = 0;
            if(empty($columns) || in_array('length_yrs', $columns)) $lengthCols++;
            if(empty($columns) || in_array('length_mos', $columns)) $lengthCols++;

            $genderCols = 0;
            if(empty($columns) || in_array('gender_m', $columns)) $genderCols++;
            if(empty($columns) || in_array('gender_f', $columns)) $genderCols++;
            
            $totalCols = 1 
                + (empty($columns) || in_array('charges', $columns) ? 1 : 0)
                + $nameCols
                + (empty($columns) || in_array('position', $columns) ? 1 : 0)
                + (empty($columns) || in_array('nature_of_work', $columns) ? 1 : 0)
                + (empty($columns) || in_array('office', $columns) ? 1 : 0)
                + (empty($columns) || in_array('rate_day', $columns) ? 1 : 0)
                + (empty($columns) || in_array('first_day', $columns) ? 1 : 0)
                + $lengthCols
                + (empty($columns) || in_array('birthdate', $columns) ? 1 : 0)
                + (empty($columns) || in_array('address', $columns) ? 1 : 0)
                + (empty($columns) || in_array('eligibility', $columns) ? 1 : 0)
                + $genderCols
                + (empty($columns) || in_array('level_1', $columns) ? 1 : 0)
                + (empty($columns) || in_array('level_2', $columns) ? 1 : 0)
                + (empty($columns) || in_array('ip', $columns) ? 1 : 0)
                + (empty($columns) || in_array('solo_parent', $columns) ? 1 : 0)
                + (empty($columns) || in_array('remarks', $columns) ? 1 : 0);
        @endphp
        <tr>
            <th rowspan="2">NO.</th>
            @if(empty($columns) || in_array('charges', $columns))<th rowspan="2">CHARGES</th>@endif
            @if($nameCols > 0)<th colspan="{{ $nameCols }}">NAME</th>@endif
            @if(empty($columns) || in_array('position', $columns))<th rowspan="2">POSITION</th>@endif
            @if(empty($columns) || in_array('nature_of_work', $columns))<th rowspan="2">NATURE OF WORK</th>@endif
            @if(empty($columns) || in_array('office', $columns))<th rowspan="2">OFFICE</th>@endif
            @if(empty($columns) || in_array('rate_day', $columns))<th rowspan="2">RATE/<br>DAY</th>@endif
            @if(empty($columns) || in_array('first_day', $columns))<th rowspan="2">FIRST DAY<br>OF SERVICE</th>@endif
            @if($lengthCols > 0)<th colspan="{{ $lengthCols }}">LENGTH OF<br>SERVICE</th>@endif
            @if(empty($columns) || in_array('birthdate', $columns))<th rowspan="2">BIRTHDATE</th>@endif
            @if(empty($columns) || in_array('address', $columns))<th rowspan="2">ADDRESS</th>@endif
            @if(empty($columns) || in_array('eligibility', $columns))<th rowspan="2">ELIGIBILITY</th>@endif
            @if($genderCols > 0)<th colspan="{{ $genderCols }}">GENDER</th>@endif
            @if(empty($columns) || in_array('level_1', $columns))<th rowspan="2">1ST<br>LEVEL</th>@endif
            @if(empty($columns) || in_array('level_2', $columns))<th rowspan="2">2ND<br>LEVEL</th>@endif
            @if(empty($columns) || in_array('ip', $columns))<th rowspan="2">IP COMMUNITY<br>MEMBERSHIP</th>@endif
            @if(empty($columns) || in_array('solo_parent', $columns))<th rowspan="2">SOLO<br>PARENT</th>@endif
            @if(empty($columns) || in_array('remarks', $columns))<th rowspan="2">REMARKS</th>@endif
        </tr>
        <tr class="sub-hdr">
            @if(empty($columns) || in_array('family_name', $columns))<th>FAMILY</th>@endif
            @if(empty($columns) || in_array('first_name', $columns))<th>FIRST</th>@endif
            @if(empty($columns) || in_array('mi', $columns))<th>M.I.</th>@endif
            @if(empty($columns) || in_array('ext', $columns))<th>EXT</th>@endif
            @if(empty($columns) || in_array('length_yrs', $columns))<th>YRS</th>@endif
            @if(empty($columns) || in_array('length_mos', $columns))<th>MOS</th>@endif
            @if(empty($columns) || in_array('gender_m', $columns))<th>M</th>@endif
            @if(empty($columns) || in_array('gender_f', $columns))<th>F</th>@endif
        </tr>
    </thead>
    <tbody>
        @forelse($records as $i => $jo)
        <tr>
            <td>{{ $i + 1 }}</td>
            @if(empty($columns) || in_array('charges', $columns))<td>{{ $jo->charges }}</td>@endif
            @if(empty($columns) || in_array('family_name', $columns))<td class="tl" style="font-weight:bold;">{{ strtoupper($jo->last_name) }}</td>@endif
            @if(empty($columns) || in_array('first_name', $columns))<td class="tl">{{ $jo->first_name }}</td>@endif
            @if(empty($columns) || in_array('mi', $columns))<td>{{ $jo->middle_initial }}</td>@endif
            @if(empty($columns) || in_array('ext', $columns))<td>{{ $jo->name_extension }}</td>@endif
            @if(empty($columns) || in_array('position', $columns))<td class="tl">{{ $jo->position_title }}</td>@endif
            @if(empty($columns) || in_array('nature_of_work', $columns))<td>{{ $jo->nature_of_work }}</td>@endif
            @if(empty($columns) || in_array('office', $columns))<td>{{ $jo->office }}</td>@endif
            @if(empty($columns) || in_array('rate_day', $columns))<td>{{ $jo->rate_per_day ? number_format($jo->rate_per_day, 2) : '' }}</td>@endif
            @if(empty($columns) || in_array('first_day', $columns))<td>{{ $jo->first_day_of_service ? $jo->first_day_of_service->format('Y-m-d') : '' }}</td>@endif
            @if(empty($columns) || in_array('length_yrs', $columns))<td>{{ $jo->first_day_of_service ? $jo->years_of_service : '' }}</td>@endif
            @if(empty($columns) || in_array('length_mos', $columns))<td>{{ $jo->first_day_of_service ? $jo->months_of_service : '' }}</td>@endif
            @if(empty($columns) || in_array('birthdate', $columns))<td>{{ $jo->birthdate ? $jo->birthdate->format('Y-m-d') : '' }}</td>@endif
            @if(empty($columns) || in_array('address', $columns))<td class="tl">{{ $jo->address }}</td>@endif
            @if(empty($columns) || in_array('eligibility', $columns))<td>{{ $jo->eligibility }}</td>@endif
            @if(empty($columns) || in_array('gender_m', $columns))<td>{{ $jo->gender === 'M' ? '✓' : '' }}</td>@endif
            @if(empty($columns) || in_array('gender_f', $columns))<td>{{ $jo->gender === 'F' ? '✓' : '' }}</td>@endif
            @if(empty($columns) || in_array('level_1', $columns))<td class="chk">{{ $jo->first_level_eligibility ? '✓' : '' }}</td>@endif
            @if(empty($columns) || in_array('level_2', $columns))<td class="chk">{{ $jo->second_level_eligibility ? '✓' : '' }}</td>@endif
            @if(empty($columns) || in_array('ip', $columns))<td>{{ $jo->ip_community_membership }}</td>@endif
            @if(empty($columns) || in_array('solo_parent', $columns))<td class="chk">{{ $jo->solo_parent ? '✓' : '' }}</td>@endif
            @if(empty($columns) || in_array('remarks', $columns))<td class="tl">{{ $jo->remarks }}</td>@endif
        </tr>
        @empty
        <tr>
            <td colspan="{{ $totalCols ?? 23 }}" style="padding:14px;text-align:center;color:#94a3b8;">No records found.</td>
        </tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    Generated: {{ now()->format('F d, Y h:i A') }}
</div>

</body>
</html>
