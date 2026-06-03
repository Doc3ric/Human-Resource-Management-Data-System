<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Notice of Longevity Pay (NOLP)</title>
    <style>
        @page {
            margin: 15mm 20mm 15mm 20mm;
            size: A4 portrait;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            margin: 0;
            padding: 0;
            line-height: 1.5;
        }

        .header table {
            width: 100%;
            border-collapse: collapse;
        }

        .header td {
            vertical-align: middle;
            padding: 0;
        }

        .header-logo-left {
            width: 85px;
            text-align: left;
        }

        .header-logo-left img {
            width: 80px;
            height: 80px;
        }

        .header-center {
            text-align: center;
            vertical-align: middle;
        }

        .header-logo-right {
            width: 85px;
            text-align: right;
            vertical-align: middle;
        }

        .header-logo-right img {
            width: 80px;
            height: 80px;
        }

        .repub {
            font-size: 11pt;
            margin: 0;
        }

        .province {
            font-size: 11pt;
            font-weight: bold;
            margin: 2px 0;
        }

        .city {
            font-size: 11pt;
            margin: 2px 0;
        }

        .office {
            font-size: 12pt;
            font-weight: bold;
            color: #0f4c81;
            letter-spacing: 0.5px;
            margin-top: 6px;
        }

        .divider {
            border-top: 2px solid #000;
            margin: 10px 0 14px 0;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            margin: 14px 0 18px 0;
        }

        .date-section {
            text-align: right;
            margin-bottom: 20px;
        }

        .date-line {
            display: inline-block;
            border-bottom: 1px solid #000;
            min-width: 150px;
            text-align: center;
            padding-bottom: 1px;
            font-weight: bold;
        }

        .date-label {
            display: block;
            text-align: center;
            margin-top: 2px;
            font-size: 10pt;
        }

        .employee-info {
            margin-bottom: 22px;
            line-height: 1.5;
        }

        .emp-name {
            font-weight: bold;
            text-decoration: underline;
            display: block;
        }

        .emp-unit {
            font-weight: bold;
            display: block;
        }

        .emp-address {
            font-weight: bold;
            text-decoration: underline;
            display: block;
        }

        .salutation {
            margin-bottom: 16px;
        }

        .body-text {
            text-indent: 40px;
            text-align: justify;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .computation {
            margin-left: 50px;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .comp-row {
            display: table;
            width: 100%;
            margin-bottom: 14px;
        }

        .comp-label {
            display: table-cell;
            width: 60%;
            vertical-align: bottom;
        }

        .comp-value-col {
            display: table-cell;
            width: 40%;
            vertical-align: bottom;
            white-space: nowrap;
        }

        .comp-peso {
            float: left;
            font-weight: bold;
        }

        .comp-amount {
            display: block;
            margin-left: 20px;
            border-bottom: 1px solid #000;
            text-align: center;
            font-weight: bold;
            min-width: 110px;
        }

        .closing {
            text-indent: 40px;
            margin-bottom: 24px;
            line-height: 1.6;
        }

        .signoff {
            text-align: right;
            margin-right: 50px;
            margin-bottom: 16px;
        }

        .governor-name {
            font-weight: bold;
            display: block;
        }

        .footer-info {
            margin-top: 12px;
            margin-bottom: 6px;
            line-height: 1.5;
        }

        .contact-info {
            margin-top: 8px;
            border-top: 1px solid #000;
            padding-top: 4px;
            font-size: 9.5pt;
        }
    </style>
</head>

<body>
    @foreach($records as $data)
        @php extract($data); @endphp
    <div class="header">
        <table>
            <tr>
                <td class="header-logo-left">
                    <img src="{{ public_path('img/logo.png') }}" alt="Seal">
                </td>
                <td class="header-center">
                    <div class="repub">Republic of the Philippines</div>
                    <div class="province">PROVINCE OF BUKIDNON</div>
                    <div class="city">Malaybalay City</div>
                    <div class="office">OFFICE OF THE PROVINCIAL GOVERNOR</div>
                </td>
                <td class="header-logo-right">
                    @if(file_exists(public_path('img/bagong-pilipinas.png')))
                        <img src="{{ public_path('img/bagong-pilipinas.png') }}" alt="Bagong Pilipinas">
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="divider"></div>

    <div class="title">
        NOTICE OF LONGEVITY PAY
    </div>

    <div class="date-section">
        <div style="display:inline-block;">
            <span class="date-line"><strong>{{ now()->format('F d, Y') }}</strong></span>
            <span class="date-label">Date</span>
        </div>
    </div>

    <div class="employee-info">
        @php
            $title = ($employee->sex == 'Female' || $employee->sex === 'F') ? 'Ms.' : 'Mr.';
            $ou = strtoupper($employee->organizational_unit ?? '');
            $locationMap = [
                'KIBAWE' => 'Kibawe, Bukidnon',
                'MARAMAG' => 'Maramag, Bukidnon',
                'MALAYBALAY' => 'Malaybalay City, Bukidnon',
                'MANOLO' => 'Manolo Fortich, Bukidnon',
                'QUEZON' => 'Quezon, Bukidnon',
                'TALAKAG' => 'Talakag, Bukidnon',
                'CABANGLASAN' => 'Cabanglasan, Bukidnon',
                'WAO' => 'Wao, Lanao del Sur',
            ];
            $location = 'Malaybalay City, Bukidnon';
            foreach ($locationMap as $key => $city) {
                if (str_contains($ou, $key)) {
                    $location = $city;
                    break;
                }
            }
        @endphp
        <span class="emp-name">{{ $title }}
            {{ strtoupper($employee->first_name . ' ' . ($employee->middle_name ? substr($employee->middle_name, 0, 1) . '. ' : '') . $employee->last_name) }}</span>
        <span class="emp-unit">{{ $employee->organizational_unit }}</span>
        <span class="emp-address">{{ $location }}</span>
    </div>

    <div class="salutation">
        Dear <strong>{{ $title }} {{ explode(' ', $employee->last_name)[0] }}:</strong>
    </div>

    <div class="body-text">
        Pursuant to the Department of Health and Department of Budget and Management
        Joint Circular No. 1 dated November 29, 2012, implementing item (4)(d) of the Senate and
        House of Representatives Joint Resolution No. 4, s. 2009, approved on June 17, 2009, your
        salary as <strong><u>{{ $employee->position_title }}</u></strong> is hereby adjusted effective
        <strong><u>{{ $effectiveDate->format('F j, Y') }}</u></strong>, as follows:
    </div>

    <div class="computation">

        <div class="comp-row">
            <div class="comp-label">
                1. &nbsp; Actual monthly basic salary as of
                <strong>{{ $effectiveDate->clone()->subDay()->format('F j, Y') }}</strong><br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; (SG - <u>{{ $employee->salary_grade }}</u>, Step
                <u>{{ $currentStep }}</u>)
            </div>
            <div class="comp-value-col">
                <span class="comp-peso">P</span>
                <span class="comp-amount">{{ number_format($currentSalary, 2) }}</span>
            </div>
        </div>

        <div class="comp-row">
            <div class="comp-label">
                2. &nbsp; Add: Two (2) Step Increment<br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Due to Length of Service; (SG - <u>{{ $employee->salary_grade }}</u>,
                Step <u>{{ $newStep }}</u>)
            </div>
            <div class="comp-value-col">
                <span class="comp-peso">P</span>
                <span class="comp-amount">{{ number_format($diff, 2) }}</span>
            </div>
        </div>

        <div class="comp-row" style="margin-top: 12px;">
            <div class="comp-label">
                3. &nbsp; Adjusted monthly basic salary effective
                <strong>{{ $effectiveDate->format('F j, Y') }}</strong>
            </div>
            <div class="comp-value-col">
                <span class="comp-peso">P</span>
                <span class="comp-amount">{{ number_format($newSalary, 2) }}</span>
            </div>
        </div>

    </div>

    <div class="closing">
        This salary adjustment is subject to review and post-audit, and to appropriate
        re-adjustment and refund if found not in order.
    </div>

    <div class="signoff">
        Very truly yours,<br><br><br>
        <span class="governor-name">ROGELIO NEIL P. ROQUE</span>
        <span>Provincial Governor</span>
    </div>

    <div class="footer-info">
        Item No. <strong>{{ $employee->item }}</strong> / Unique Item No. ______<br>
        FY <strong>{{ now()->year }}</strong> Personal Services Itemization and/or<br>
        Plantilla of Personnel<br>
        <span style="font-size: 9.5pt;">CF: GSIS</span>
    </div>

    <div class="contact-info">
        Tel. No.: (088) 537-4813 &nbsp;&nbsp;|&nbsp;&nbsp; Email address: governor@bukidnon.gov.ph
    </div>

        @if(!$loop->last)
            <div style="page-break-after: always;"></div>
        @endif
    @endforeach
</body>

</html>