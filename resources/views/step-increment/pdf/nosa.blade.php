<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Notice of Salary Adjustment (NOSA)</title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            margin: 30px 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            position: relative;
        }
        .header table {
            width: 100%;
            border-collapse: collapse;
        }
        .header td {
            vertical-align: top;
            padding: 0;
        }
        .header-logo-left {
            width: 90px;
            text-align: left;
        }
        .header-logo-left img {
            width: 80px;
            height: auto;
        }
        .header-center {
            text-align: center;
            vertical-align: middle;
            padding-top: 5px;
        }
        .header-logo-right {
            width: 90px;
            text-align: right;
            vertical-align: middle;
        }
        .header-logo-right img {
            width: 90px;
            height: auto;
        }
        .repub {
            font-size: 11pt;
            margin-bottom: 2px;
        }
        .province {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .city {
            font-size: 11pt;
            margin-bottom: 12px;
        }
        .office {
            font-size: 12pt;
            font-weight: bold;
            color: #0f4c81;
            letter-spacing: 0.5px;
        }
        .title {
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            margin-top: 25px;
            margin-bottom: 35px;
        }
        .date-section {
            text-align: right;
            margin-bottom: 30px;
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
        }
        .employee-info {
            margin-bottom: 35px;
            line-height: 1.3;
        }
        .emp-name { font-weight: bold; text-decoration: underline; display: block; }
        .emp-unit { font-weight: bold; display: block; }
        .emp-address { font-weight: bold; text-decoration: underline; display: block;}
        
        .salutation {
            margin-bottom: 20px;
        }
        
        .body-text {
            text-indent: 40px;
            text-align: justify;
            line-height: 1.5;
            margin-bottom: 30px;
        }
        
        .computation {
            margin-left: 50px;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .comp-row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
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
            min-width: 100px;
        }
        
        .closing {
            text-indent: 40px;
            margin-bottom: 40px;
        }
        
        .signoff {
            text-align: right;
            margin-right: 50px;
            margin-bottom: 60px;
        }
        
        .governor-name {
            font-weight: bold;
            display: block;
        }
        
        .footer-info {
            margin-top: 50px;
            margin-bottom: 20px;
            line-height: 1.4;
        }
        
        .contact-info {
            margin-top: 50px;
            border-top: 1px solid #000;
            padding-top: 5px;
            font-size: 9pt;
        }
    </style>
</head>
<body>

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
                    <br>
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
    
    <div style="border-top: 2px solid #000; margin-bottom: 20px;"></div>

    <div class="title">
        NOTICE OF SALARY ADJUSTMENT
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
        <span class="emp-name">{{ $title }} {{ strtoupper($employee->first_name . ' ' . ($employee->middle_name ? substr($employee->middle_name, 0, 1) . '. ' : '') . $employee->last_name) }}</span>
        <span class="emp-unit">{{ $employee->organizational_unit }}</span>
        <span class="emp-address">Malaybalay City, Bukidnon</span>
    </div>

    <div class="salutation">
        Sir/Madam:
    </div>

    <div class="body-text">
        Pursuant to the implementation of
        @if(!empty($schedule))
            <strong><u>{{ $schedule->name }}</u></strong>
            @if($schedule->lbc_number)
                (LBC # <strong><u>{{ $schedule->lbc_number }}</u></strong>)
            @endif
        @else
            the Salary Standardization Law
        @endif
        , your salary as <strong><u>{{ $employee->position_title }}</u></strong> is hereby adjusted effective <strong><u>{{ $effectiveDate->format('F j, Y') }}</u></strong>, as follows:
    </div>

    <div class="computation">
        
        <div class="comp-row">
            <div class="comp-label">
                1. &nbsp; Actual monthly basic salary as of <strong>{{ clone $effectiveDate->subDay()->format('F j, Y') }}</strong><br>
                &nbsp;&nbsp;&nbsp;&nbsp; (SG - <u>{{ $employee->salary_grade }}</u>, Step <u>{{ $employee->step ?: 1 }}</u>)
            </div>
            <div class="comp-value-col">
                <span class="comp-peso">P</span>
                <span class="comp-amount">{{ number_format($previousSalary, 2) }}</span>
            </div>
        </div>

        <div class="comp-row" style="margin-top: 20px;">
            <div class="comp-label">
                2. &nbsp; Adjusted monthly basic salary effective <strong>{{ $effectiveDate->format('F j, Y') }}</strong>
            </div>
            <div class="comp-value-col">
                <span class="comp-peso">P</span>
                <span class="comp-amount">{{ number_format($newSalary, 2) }}</span>
            </div>
        </div>
        
        <div class="comp-row" style="margin-top: 20px;">
            <div class="comp-label">
                3. &nbsp; Difference
            </div>
            <div class="comp-value-col">
                <span class="comp-peso">P</span>
                <span class="comp-amount">{{ number_format($newSalary - $previousSalary, 2) }}</span>
            </div>
        </div>

    </div>

    <div class="closing">
        This salary adjustment is subject to review and post-audit, and to appropriate
        re-adjustment and refund if found not in order.
    </div>

    <div class="signoff">
        Very truly yours,<br><br><br><br>
        <span class="governor-name">ROGELIO NEIL P. ROQUE</span>
        <span>Provincial Governor</span>
    </div>

    <div class="footer-info">
        Item No. <strong>{{ $employee->item }}</strong> / Unique Item No. ______<br>
        FY <strong>{{ now()->year }}</strong> Personal Services Itemization and/or<br>
        Plantilla of Personnel<br><br>
        <span style="font-size: 9pt;">CF: GSIS</span>
    </div>

    <div class="contact-info">
        Tel. No.: (088) 537-4813<br>
        Email address: governor@bukidnon.gov.ph
    </div>

</body>
</html>
