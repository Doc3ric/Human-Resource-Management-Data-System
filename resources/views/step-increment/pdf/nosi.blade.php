<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Notice of Step Increment (NOSI)</title>
    <style>
        @page {
            margin: 10mm 15mm 10mm 15mm;
            size: A4 portrait;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 10.5pt;
            margin: 0;
            padding: 0;
            line-height: 1.3;
        }

        /* Header now comes from exports.partials.official-header (scoped styles) */

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
            margin-bottom: 16px;
            line-height: 1.3;
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
            line-height: 1.4;
            margin-bottom: 16px;
        }

        .computation {
            margin-left: 50px;
            margin-bottom: 16px;
            line-height: 1.4;
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
            margin-bottom: 18px;
            line-height: 1.4;
        }

        .signoff {
            text-align: right;
            margin-right: 50px;
            margin-bottom: 12px;
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

    @include('exports.partials.official-header')

    <div class="title">
        NOTICE OF STEP INCREMENT DUE TO LENGTH OF SERVICE
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
        @endphp
        <span class="emp-name">{{ $title }}
            {{ strtoupper($employee->first_name . ' ' . ($employee->middle_name ? substr($employee->middle_name, 0, 1) . '. ' : '') . $employee->last_name) }}</span>
        <span class="emp-unit">{{ $employee->office_department }}</span>
        <span class="emp-address">Malaybalay City, Bukidnon</span>
    </div>

    <div class="salutation">
        Dear <strong>{{ $title }} {{ explode(' ', $employee->last_name)[0] }}:</strong>
    </div>

    <div class="body-text">
        @if($employee->employment_status === 'Elected' || $employee->employment_status === 'E')
        Pursuant to the Civil Service Commission and Department of Budget and Management
        Joint Circular No. 1, s. 2016, your salary as <strong><u>{{ $employee->position_title }}</u></strong> is hereby adjusted effective
        <strong><u>{{ $effectiveDate->format('F j, Y') }}</u></strong>, as follows:
        @else
        Pursuant to the Civil Service Commission and Department of Budget and Management
        Joint Circular No. 1 dated September 3, 2012, implementing item (4)(d) of the
        Senate and House of Representatives Joint Resolution No. 4, s. 2009, approved on June 17,
        2009, your salary as <strong><u>{{ $employee->position_title }}</u></strong> is hereby adjusted effective <strong><u>{{ $effectiveDate->format('F j, Y') }}</u></strong>, as follows:
        @endif
    </div>

    <div class="computation">

        <div class="comp-row">
            <div class="comp-label">
                1. &nbsp; Actual monthly basic salary as of
                <strong>{{ $effectiveDate->clone()->subDay()->format('M. j, Y') }}</strong><br>
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
                2. &nbsp; Add: One (1) Step Increment<br>
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
                <strong>{{ $effectiveDate->format('M. j, Y') }}</strong>
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
        <div style="text-align: left; display: inline-block;">
            Very truly yours,<br><br><br>
            <div style="text-align: center;">
                <span class="governor-name">ROGELIO NEIL P. ROQUE</span>
                <span>Provincial Governor</span>
            </div>
        </div>
    </div>

    <div class="footer-info">
        Item No. <strong>{{ $employee->item_no_new }}</strong> / Unique Item No. ______<br>
        FY <strong>{{ now()->year }}</strong> Personal Services Itemization and/or<br>
        Plantilla of Personnel<br>
        <span style="font-size: 9.5pt;">CF: GSIS</span>
    </div>

    <div class="contact-info">
        Tel. No.: (088) 537-4813 &nbsp;&nbsp;|&nbsp;&nbsp; Email address: governor@bukidnon.gov.ph
    </div>

</body>

</html>