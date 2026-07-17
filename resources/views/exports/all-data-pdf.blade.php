<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 7px;
            color: #000;
            background: #fff;
        }

        /* ── Cover / header ─────────────────────────────────────── */
        .report-header {
            background: #fff;
            color: #000;
            padding: 10px 14px;
            margin-bottom: 6px;
            border-bottom: 2px solid #000;
        }

        .report-header h1 {
            font-size: 13px;
            font-weight: 800;
            letter-spacing: .5px;
        }

        .report-header p {
            font-size: 8px;
            color: #333;
            margin-top: 2px;
        }

        .report-meta {
            display: flex;
            justify-content: space-between;
            font-size: 7px;
            color: #333;
            margin-bottom: 8px;
            padding-bottom: 4px;
            border-bottom: 1px solid #999;
        }

        /* ── Table ───────────────────────────────────────────────── */
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            page-break-inside: auto;
        }

        thead {
            display: table-header-group;
        }

        thead th {
            background: #fff;
            color: #000;
            padding: 5px 3px;
            font-size: 6.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .3px;
            text-align: left;
            border: 1px solid #000;
            white-space: normal;
            word-wrap: break-word;
        }

        tbody td {
            padding: 3px 3px;
            font-size: 6.5px;
            border: 1px solid #bbb;
            color: #000;
            vertical-align: middle;
            word-wrap: break-word;
        }

        /* Col widths */
        .col-no {
            width: 2%;
            text-align: center;
        }

        .col-unit {
            width: 13%;
        }

        .col-item {
            width: 5%;
            font-family: monospace;
        }

        .col-title {
            width: 11%;
            font-weight: 600;
        }

        .col-sg {
            width: 3%;
            text-align: center;
            font-weight: 700;
        }

        .col-sal {
            width: 6%;
            text-align: right;
            font-family: monospace;
        }

        .col-step {
            width: 2%;
            text-align: center;
        }

        .col-area {
            width: 3%;
            text-align: center;
        }

        .col-name {
            width: 6%;
            text-transform: uppercase;
            font-weight: 600;
        }

        .col-fn {
            width: 6%;
        }

        .col-mn {
            width: 5%;
        }

        .col-sex {
            width: 2%;
            text-align: center;
        }

        .col-dob {
            width: 5%;
            text-align: center;
        }

        .col-tin {
            width: 5%;
            font-family: monospace;
        }

        .col-date {
            width: 5%;
            text-align: center;
        }

        .col-pwd {
            width: 3%;
            text-align: center;
        }

        .col-stat {
            width: 5%;
            text-align: center;
        }

        .col-term {
            width: 6%;
            text-align: center;
            font-size: 5.5px;
        }

        /* Status text — plain, no color */
        .s-p,
        .s-ct,
        .s-e,
        .s-ca,
        .s-jo {
            font-weight: 700;
            color: #000;
        }

        .s-v {
            font-style: italic;
            color: #000;
        }

        .page-break {
            page-break-after: always;
        }

        .text-gray {
            color: #555;
        }
    </style>
</head>

<body>

    {{-- Header --}}
    <div class="report-header">
        <h1>&#x1F4CB; HDMS- Human Resource Data Management System — Complete Data Report</h1>
        <p>All plantilla records as of {{ now()->format('F d, Y') }}</p>
    </div>

    <div class="report-meta">
        <span>Generated: {{ now()->format('m/d/Y h:i A') }}</span>
        <span>Total Records: {{ number_format($records->count()) }}</span>
        @if(!empty($filters))
            <span>Filtered view</span>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-no">#</th>
                @if(empty($columns) || in_array('office_department', $columns))
                <th class="col-unit">OFFICE</th>@endif
                @if(empty($columns) || in_array('item_no_new', $columns))
                <th class="col-item">Item</th>@endif
                @if(empty($columns) || in_array('position_title', $columns))
                <th class="col-title">Position Title</th>@endif
                @if(empty($columns) || in_array('salary_grade', $columns))
                <th class="col-sg">SG</th>@endif
                @if(empty($columns) || in_array('authorized_annual_salary', $columns))
                <th class="col-sal">Auth. Annual Salary</th>@endif
                @if(empty($columns) || in_array('base_salary_amount', $columns))
                <th class="col-sal">Actual Monthly Salary</th>@endif
                @if(empty($columns) || in_array('step', $columns))
                <th class="col-step">Step</th>@endif
                @if(empty($columns) || in_array('area_code', $columns))
                <th class="col-area">Area Code</th>@endif
                @if(empty($columns) || in_array('area_type', $columns))
                <th class="col-area">Area Type</th>@endif
                @if(empty($columns) || in_array('level', $columns))
                <th class="col-area">Level</th>@endif
                @if(empty($columns) || in_array('last_name', $columns))
                <th class="col-name">Last Name</th>@endif
                @if(empty($columns) || in_array('first_name', $columns))
                <th class="col-fn">First Name</th>@endif
                @if(empty($columns) || in_array('middle_name', $columns))
                <th class="col-mn">Middle Name</th>@endif
                @if(empty($columns) || in_array('name_extension', $columns))
                <th class="col-mn">Suffix</th>@endif
                @if(empty($columns) || in_array('sex', $columns))
                <th class="col-sex">Sex</th>@endif
                @if(empty($columns) || in_array('religion', $columns))
                <th class="col-sex">Religion</th>@endif
                @if(empty($columns) || in_array('date_of_birth', $columns))
                <th class="col-dob">Birthday</th>@endif
                @if(empty($columns) || in_array('tin', $columns))
                <th class="col-tin">TIN</th>@endif
                @if(empty($columns) || in_array('date_original_appointment', $columns))
                <th class="col-date">Date Orig. Appt.</th>@endif
                @if(empty($columns) || in_array('date_last_promotion', $columns))
                <th class="col-date">Date Last Promo.</th>@endif
                @if(empty($columns) || in_array('status', $columns))
                <th class="col-stat">Status</th>@endif
                @if(empty($columns) || in_array('admin_charges', $columns))
                <th class="col-stat">Admin Charges</th>@endif
                @if(empty($columns) || in_array('nature_of_separation', $columns))
                <th class="col-term">Termination</th>@endif
            </tr>
        </thead>
        <tbody>
            @foreach($records as $i => $r)
                @php
                    $s = $r->employment_status;
                    $statusText = match (true) {
                        in_array($s, ['P', 'Permanent']) => ['P', 's-p'],
                        in_array($s, ['CT', 'Co-Terminous', 'Coterminous']) => ['CT', 's-ct'],
                        in_array($s, ['E', 'Elected']) => ['E', 's-e'],
                        in_array($s, ['Casual', 'Cas']) => ['CAS', 's-ca'],
                        in_array($s, ['JO', 'Job Order', 'J.O.']) => ['JO', 's-jo'],
                        $r->is_vacant => ['VAC', 's-v'],
                        default => [$s ?? '—', ''],
                    };
                @endphp
                <tr>
                    <td class="col-no text-gray">{{ $i + 1 }}</td>
                    @if(empty($columns) || in_array('office_department', $columns))
                    <td class="col-unit">{{ $r->office_department }}</td>@endif
                    @if(empty($columns) || in_array('item_no_new', $columns))
                    <td class="col-item">{{ $r->item_no_new }}</td>@endif
                    @if(empty($columns) || in_array('position_title', $columns))
                    <td class="col-title">{{ $r->position_title }}</td>@endif
                    @if(empty($columns) || in_array('salary_grade', $columns))
                    <td class="col-sg">{{ $r->salary_grade }}</td>@endif
                    @if(empty($columns) || in_array('authorized_annual_salary', $columns))
                        <td class="col-sal">
                            {{ $r->authorized_annual_salary ? number_format($r->authorized_annual_salary, 2) : '—' }}
                    </td>@endif
                    @if(empty($columns) || in_array('base_salary_amount', $columns))
                        <td class="col-sal">{{ $r->base_salary_amount ? number_format($r->base_salary_amount, 2) : '—' }}
                    </td>@endif
                    @if(empty($columns) || in_array('step', $columns))
                    <td class="col-step">{{ $r->step }}</td>@endif
                    @if(empty($columns) || in_array('area_code', $columns))
                    <td class="col-area">{{ $r->area_code ?: '—' }}</td>@endif
                    @if(empty($columns) || in_array('area_type', $columns))
                    <td class="col-area">{{ $r->area_type ?: '—' }}</td>@endif
                    @if(empty($columns) || in_array('level', $columns))
                    <td class="col-area">{{ $r->level ?: '—' }}</td>@endif
                    @if(empty($columns) || in_array('last_name', $columns))
                    <td class="col-name">{{ $r->is_vacant ? 'VACANT' : strtoupper($r->last_name ?? '—') }}</td>@endif
                    @if(empty($columns) || in_array('first_name', $columns))
                    <td class="col-fn">{{ $r->first_name ?: '—' }}</td>@endif
                    @if(empty($columns) || in_array('middle_name', $columns))
                    <td class="col-mn">{{ $r->middle_name ?: '—' }}</td>@endif
                    @if(empty($columns) || in_array('name_extension', $columns))
                    <td class="col-mn">{{ $r->name_extension ?: '—' }}</td>@endif
                    @if(empty($columns) || in_array('sex', $columns))
                    <td class="col-sex">{{ $r->sex ?: '—' }}</td>@endif
                    @if(empty($columns) || in_array('religion', $columns))
                    <td class="col-sex">{{ $r->religion ?: '—' }}</td>@endif
                    @if(empty($columns) || in_array('date_of_birth', $columns))
                    <td class="col-dob">{{ $r->date_of_birth?->format('m/d/Y') ?: '—' }}</td>@endif
                    @if(empty($columns) || in_array('tin', $columns))
                    <td class="col-tin">{{ $r->tin ?: '—' }}</td>@endif
                    @if(empty($columns) || in_array('date_original_appointment', $columns))
                    <td class="col-date">{{ $r->date_original_appointment?->format('m/d/Y') ?: '—' }}</td>@endif
                    @if(empty($columns) || in_array('date_last_promotion', $columns))
                    <td class="col-date">{{ $r->date_last_promotion?->format('m/d/Y') ?: '—' }}</td>@endif
                    @if(empty($columns) || in_array('pwd', $columns))
                    <td class="col-pwd">{{ $r->is_pwd ? 'YES' : '—' }}</td>@endif
                    @if(empty($columns) || in_array('status', $columns))
                    <td class="col-stat {{ $statusText[1] }}">{{ $statusText[0] }}</td>@endif
                    @if(empty($columns) || in_array('admin_charges', $columns))
                        <td class="col-stat" style="font-size: 5px; text-align: left;">
                            @if(is_array($r->admin_charges) && count($r->admin_charges) > 0)
                                @foreach($r->admin_charges as $charge)
                                    F:{{ $charge['from'] ?? '-' }} T:{{ $charge['to'] ?? '-' }} {{ $charge['type'] ?? '-' }}<br>
                                @endforeach
                            @elseif($r->admin_charge_from || $r->admin_charge_type)
                                F:{{ $r->admin_charge_from?->format('Y-m-d') ?? '-' }}
                                T:{{ $r->admin_charge_to?->format('Y-m-d') ?? '-' }} {{ $r->admin_charge_type ?? '-' }}
                            @else
                                —
                            @endif
                        </td>
                    @endif
                    @if(empty($columns) || in_array('nature_of_separation', $columns))
                    <td class="col-term">{{ $r->nature_of_separation ?: '—' }}</td>@endif
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>