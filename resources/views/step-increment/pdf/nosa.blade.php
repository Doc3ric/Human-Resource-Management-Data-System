<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Notice of Salary Adjustment (NOSA)</title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 10pt;
            margin: 20px 30px;
        }
        /* Header comes from exports.partials.official-header */
        .title {
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            margin-top: 15px;
            margin-bottom: 25px;
            letter-spacing: 1px;
        }
        .date-section {
            text-align: right;
            margin-bottom: 20px;
            margin-right: 15px;
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
            margin-bottom: 25px;
            line-height: 1.2;
        }
        .emp-name { font-weight: bold; text-decoration: underline; display: block; }
        .emp-unit { font-weight: bold; display: block; }
        .emp-address { font-weight: bold; text-decoration: underline; display: block;}
        
        .salutation {
            margin-bottom: 15px;
        }
        
        .body-text {
            text-indent: 40px;
            text-align: justify;
            line-height: 1.4;
            margin-bottom: 20px;
        }
        
        .computation {
            margin-left: 40px;
            margin-right: 15px;
            margin-bottom: 20px;
            line-height: 1.5;
        }
        .comp-row {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .comp-label {
            display: table-cell;
            width: 65%;
            vertical-align: bottom;
        }
        .comp-value-col {
            display: table-cell;
            width: 35%;
            vertical-align: bottom;
            white-space: nowrap;
        }
        .comp-peso {
            float: left;
            font-weight: bold;
            text-decoration: underline;
        }
        .comp-amount {
            display: block;
            margin-left: 15px;
            border-bottom: 1px solid #000;
            text-align: center;
            font-weight: bold;
            min-width: 100px;
        }
        
        .closing {
            text-indent: 40px;
            text-align: justify;
            margin-bottom: 25px;
        }
        
        .signoff {
            text-align: right;
            margin-right: 40px;
            margin-bottom: 30px;
        }
        
        .governor-name {
            font-weight: bold;
            display: block;
        }
        
        .footer-info {
            margin-top: 20px;
            margin-bottom: 15px;
            line-height: 1.3;
        }
        
        .contact-info {
            margin-top: 30px;
            border-top: 1px solid #000;
            padding-top: 5px;
            font-size: 8pt;
        }
    </style>
</head>
<body>

    @include('exports.partials.official-header')

    <div class="title">
        NOTICE OF SALARY ADJUSTMENT
    </div>

    <div class="date-section">
        <div style="display:inline-block;">
            <span class="date-line">{{ now()->format('F j, Y') }}</span>
            <span class="date-label">Date</span>
        </div>
    </div>

    <div class="employee-info">
        @php
            // Salutation parsing
            $titlePrefix = '';
            $posTitle = strtolower($employee->position_title ?? '');
            if (str_contains($posTitle, 'medical') || str_contains($posTitle, 'health officer') || str_contains($posTitle, 'doctor')) {
                $titlePrefix = 'Dr. ';
            } else {
                $titlePrefix = ($employee->sex == 'Female' || $employee->sex === 'F') ? 'Ms. ' : 'Mr. ';
            }
            
            $firstName = ucfirst(strtolower($employee->first_name));
            $middleInitial = $employee->middle_name ? strtoupper(substr($employee->middle_name, 0, 1)) . '.' : '';
            $lastName = ucfirst(strtolower($employee->last_name));
            $fullName = trim($titlePrefix . $firstName . ' ' . $middleInitial . ' ' . $lastName);
        @endphp
        <span class="emp-name">{{ strtoupper($fullName) }}</span>
        <span class="emp-unit">{{ $employee->office_department }}</span>
        <span class="emp-address">Malaybalay City</span>
    </div>

    <div class="salutation">
        Dear {{ $titlePrefix }}{{ $lastName }}:
    </div>

    <div class="body-text">
        Pursuant to 
        @if(!empty($schedule))
            {{ $schedule->law_name ?? 'Executive Order No. 64' }}, implementing the {{ $schedule->name }} of the Salary Schedule dated {{ $schedule->effective_date ? $schedule->effective_date->format('F j, Y') : 'August 2, 2024' }}
            @if($schedule->lbc_number)
                ({{ $schedule->lbc_number }})
            @endif
        @else
            Executive Order No. 64, implementing the Third (3rd) Tranche of the Salary Schedule dated August 2, 2024
        @endif
        , your salary is hereby adjusted effective <strong>{{ $effectiveDate->format('F j, Y') }}</strong>, as follows:
    </div>

    <div class="computation">
        
        <div class="comp-row">
            <div class="comp-label">
                1. &nbsp; Adjusted monthly basic salary effective <strong>{{ $effectiveDate->format('M. j, Y') }}</strong><br>
                &nbsp;&nbsp;&nbsp;&nbsp; under the new Salary Schedule; SG <u>{{ $employee->salary_grade }}</u> Step <u>{{ $employee->step ?: 1 }}</u>
            </div>
            <div class="comp-value-col">
                <span class="comp-peso">P</span>
                <span class="comp-amount">{{ number_format($newSalary, 2) }}</span>
            </div>
        </div>

        <div class="comp-row">
            <div class="comp-label">
                @php $previousDate = clone $effectiveDate; $previousDate->subDay(); @endphp
                2. &nbsp; Actual monthly basic salary as of <strong>{{ $previousDate->format('M. j, Y') }}</strong>;<br>
                &nbsp;&nbsp;&nbsp;&nbsp; SG <u>{{ $employee->salary_grade }}</u> Step <u>{{ $employee->step ?: 1 }}</u>
            </div>
            <div class="comp-value-col">
                <span class="comp-peso">P</span>
                <span class="comp-amount">{{ number_format($previousSalary, 2) }}</span>
            </div>
        </div>
        
        <div class="comp-row">
            <div class="comp-label">
                3. &nbsp; Monthly salary adjustment effective <strong>{{ $effectiveDate->format('M. j, Y') }}</strong>
            </div>
            <div class="comp-value-col">
                <span class="comp-peso">P</span>
                <span class="comp-amount">{{ number_format($newSalary - $previousSalary, 2) }}</span>
            </div>
        </div>

    </div>

    <div class="closing">
        It is understood that this salary/daily wage adjustment is subject to the usual accounting and auditing rules and regulations, and to appropriate re-adjustment and refund if found not in order.
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
        Position Title: <strong><u>{{ $employee->position_title }}</u></strong><br>
        Salary Grade: &nbsp; <u>&nbsp;{{ $employee->salary_grade }}/{{ $employee->step ?: 1 }}&nbsp;</u><br>
        Item No. FY {{ now()->year }}: Plantilla of Personnel: <strong><u>{{ $employee->item_no_new }}</u></strong><br><br>
        <span style="font-size: 10pt;">CF: GSIS</span>
    </div>

    <div class="contact-info">
        Tel. No.: (088) 537-4813<br>
        Email address: governor@bukidnon.gov.ph
    </div>

</body>
</html>
