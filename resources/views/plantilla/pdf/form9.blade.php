<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>CSC Form 9 - Publication of Vacant Positions</title>
    <style>
        @page {
            margin: 0.5in;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #000;
        }
        .header {
            text-align: left;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .header .form-no {
            font-size: 10px;
            font-style: italic;
            font-weight: normal;
        }
        .center-heading {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .request-text {
            margin-bottom: 20px;
            text-align: justify;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-bottom: 20px;
        }
        .table th, .table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
        }
        .table th {
            background-color: #f3f4f6;
            font-weight: bold;
            padding: 8px 4px;
        }
        .signature-section {
            width: 100%;
            margin-top: 30px;
        }
        .signature-box {
            width: 300px;
            float: right;
            text-align: center;
        }
        .sign-line {
            border-bottom: 1px solid #000;
            margin-bottom: 5px;
            height: 20px;
        }
        .email-info {
            clear: both;
            margin-top: 50px;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="form-no">CS Form No. 9</div>
        <div class="form-no">Revised 2018</div>
    </div>

    <div class="center-heading">
        REPUBLIC OF THE PHILIPPINES<br>
        PROVINCIAL GOVERNMENT OF BUKIDNON<br>
        Request for Publication of Vacant Positions
    </div>

    <div class="request-text">
        <strong>To: CIVIL SERVICE COMMISSION (CSC)</strong><br><br>
        We hereby request the publication of the following vacant positions, which are authorized to be filled, at the <strong>PROVINCIAL GOVERNMENT OF BUKIDNON</strong>{{ isset($office) && $office ? " — {$office}" : '' }} in the CSC website:
    </div>

    <div class="signature-section" style="margin-top: -40px;">
        <div class="signature-box">
            <div class="sign-line"></div>
            <strong>HRMO</strong><br>
            Date: _________________
        </div>
        <div style="clear: both;"></div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th rowspan="2">No.</th>
                <th rowspan="2">Position Title<br>(Parenthetical Title, if applicable)</th>
                <th rowspan="2">Plantilla Item No.</th>
                <th rowspan="2">Salary/ Job/ Pay Grade</th>
                <th rowspan="2">Monthly Salary</th>
                <th colspan="5">Qualification Standards</th>
                <th rowspan="2">Place of Assignment</th>
            </tr>
            <tr>
                <th>Education</th>
                <th>Training</th>
                <th>Experience</th>
                <th>Eligibility</th>
                <th>Competency (if applicable)</th>
            </tr>
        </thead>
        <tbody>
            @php $count = 1; @endphp
            @forelse($vacantRecords as $record)
                <tr>
                    <td>{{ $count++ }}</td>
                    <td style="text-align: left;">{{ $record->position_title }}</td>
                    <td>{{ $record->item_no_new }}</td>
                    <td>{{ $record->salary_grade }}</td>
                    <td style="text-align: right;">{{ number_format((float)($record->authorized_annual_salary / 12), 2) }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>{{ $record->office_department }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="text-align: center; padding: 20px;">No vacant funded positions available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="email-info">
        Interested and qualified applicants should signify their interest in writing. Attach the following documents to the application letter and send to the address below not later than _________________________:<br><br>
        1. Fully accomplished Personal Data Sheet (PDS) with recent passport-sized picture (CS Form No. 212, Revised 2017) which can be downloaded at www.csc.gov.ph;<br>
        2. Performance rating in the last rating period (if applicable);<br>
        3. Photocopy of certificate of eligibility/rating/license; and<br>
        4. Photocopy of Transcript of Records.<br><br>
        <strong>QUALIFIED APPLICANTS are advised to hand in or send through courier/email their application to:</strong><br><br>
        _____________________________________________<br>
        _____________________________________________<br>
        _____________________________________________<br>
        _____________________________________________<br><br>
        <strong>APPLICATIONS WITH INCOMPLETE DOCUMENTS SHALL NOT BE ENTERTAINED.</strong>
    </div>

</body>
</html>
