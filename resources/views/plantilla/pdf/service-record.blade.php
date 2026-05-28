<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Service Record</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .title {
            font-size: 14pt;
            font-weight: bold;
            margin: 10px 0;
            text-decoration: underline;
        }
        .employee-info {
            width: 100%;
            margin-bottom: 20px;
        }
        .employee-info td {
            padding: 3px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .table th, .table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            font-size: 9pt;
        }
        .table th {
            font-weight: bold;
            background-color: #f3f4f6;
        }
        .footer {
            margin-top: 30px;
        }
        .signature-line {
            display: inline-block;
            border-bottom: 1px solid #000;
            width: 250px;
            text-align: center;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>

    <div class="header">
        <div style="font-size: 11pt;">Republic of the Philippines</div>
        <div style="font-size: 12pt; font-weight: bold;">PROVINCE OF BUKIDNON</div>
        <div style="font-size: 11pt;">Malaybalay City</div>
        <div class="title">SERVICE RECORD</div>
    </div>

    <table class="employee-info">
        <tr>
            <td width="15%"><strong>NAME:</strong></td>
            <td width="45%" style="border-bottom: 1px solid #000;">
                {{ strtoupper($employee->last_name) }}, {{ strtoupper($employee->first_name) }} {{ strtoupper($employee->middle_name) }}
            </td>
            <td width="15%" style="text-align: right;"><strong>BIRTH DATE:</strong></td>
            <td width="25%" style="border-bottom: 1px solid #000;">
                {{ $employee->date_of_birth ? $employee->date_of_birth->format('m/d/Y') : 'N/A' }}
            </td>
        </tr>
        <tr>
            <td><strong>BIRTH PLACE:</strong></td>
            <td style="border-bottom: 1px solid #000;">N/A</td>
            <td style="text-align: right;"><strong>GSIS NO.:</strong></td>
            <td style="border-bottom: 1px solid #000;">{{ $employee->gsis_bp_number ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td colspan="4" style="padding-top: 15px;">
                This is to certify that the employee named herein has the following employment record:
            </td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th colspan="2">SERVICE (Inclusive Dates)</th>
                <th colspan="3">RECORD OF APPOINTMENT</th>
                <th colspan="2">OFFICE / ENTITY</th>
                <th>LV / ABS w/o PAY</th>
                <th>SEP. CAUSE / AMT.</th>
            </tr>
            <tr>
                <th>From</th>
                <th>To</th>
                <th>Designation</th>
                <th>Status</th>
                <th>Salary</th>
                <th>Station/Place</th>
                <th>Branch</th>
                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <!-- Start Date -->
                <td>{{ $employee->date_original_appointment ? $employee->date_original_appointment->format('m/d/Y') : '-' }}</td>
                <!-- End Date -->
                <td>Present</td>
                <!-- Designation -->
                <td>{{ $employee->position_title }}</td>
                <!-- Status -->
                <td>{{ $employee->employment_status }}</td>
                <!-- Salary -->
                <td>{{ number_format($employee->actual_annual_salary, 2) }}/A</td>
                <!-- Station -->
                <td>{{ $employee->organizational_unit }}</td>
                <!-- Branch -->
                <td>Local</td>
                <!-- Leave W/O Pay -->
                <td>-</td>
                <!-- Separation Cause -->
                <td>-</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p><em>Issued in compliance with No. 54 dated August 10, 1954 and in accordance with Circular No. 58 of the System.</em></p>
        
        <table style="width: 100%; margin-top: 50px;">
            <tr>
                <td width="60%"></td>
                <td style="text-align: center;">
                    <div class="signature-line">
                        <strong>CERTIFIED CORRECT:</strong>
                    </div><br>
                    <span>(Signature over printed name)</span><br>
                    <strong>PHRMO / HRMO</strong>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
