<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>CSC Form No. 33 - Appointment</title>
    <style>
        @page {
            margin: 1in;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #000;
            line-height: 1.5;
        }
        .header {
            text-align: right;
            font-size: 10px;
            font-style: italic;
            font-weight: bold;
        }
        .title-area {
            text-align: center;
            margin-top: 30px;
            margin-bottom: 30px;
        }
        .title-area img {
            height: 80px;
            margin-bottom: 10px;
        }
        .agency-name {
            font-weight: bold;
            font-size: 14px;
        }
        .doc-title {
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 5px;
            margin-top: 20px;
        }
        .content {
            margin-top: 30px;
        }
        .field {
            display: inline-block;
            border-bottom: 1px solid #000;
            min-width: 200px;
            text-align: center;
            font-weight: bold;
            padding-bottom: 2px;
        }
        .field-large {
            display: inline-block;
            border-bottom: 1px solid #000;
            width: 100%;
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            margin-top: 20px;
            margin-bottom: 20px;
            padding-bottom: 2px;
        }
        .paragraph {
            text-indent: 40px;
            text-align: justify;
            margin-bottom: 15px;
        }
        .signature-area {
            margin-top: 60px;
            width: 100%;
        }
        .signature-box {
            float: right;
            width: 250px;
            text-align: center;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            margin-bottom: 5px;
            height: 30px;
        }
        .footer-note {
            margin-top: 50px;
            font-size: 11px;
            clear: both;
        }
    </style>
</head>
<body>

    <div class="header">
        CS Form No. 33-A<br>
        Revised 2017
    </div>

    <div class="title-area">
        <div>Republic of the Philippines</div>
        <div class="agency-name">PROVINCIAL GOVERNMENT OF BUKIDNON</div>
        <div class="doc-title">APPOINTMENT</div>
    </div>

    <div class="content">
        <div>To:</div>
        <div class="field-large">
            {{ $plantilla->first_name ? strtoupper($plantilla->first_name . ' ' . $plantilla->middle_name . ' ' . $plantilla->last_name) : '________________________________________' }}
        </div>
        
        <div class="paragraph">
            You are hereby appointed as <span class="field" style="min-width: 250px;">{{ strtoupper($plantilla->position_title) }}</span>
            (SG/JG/PG <span class="field" style="min-width: 50px;">{{ $plantilla->salary_grade }}</span>)
            under <span class="field" style="min-width: 150px;">{{ strtoupper($plantilla->employment_status ?? '______________') }}</span> status at the 
            <span class="field" style="min-width: 250px;">PROVINCIAL GOVERNMENT OF BUKIDNON</span>
            with a compensation rate of <span class="field" style="min-width: 150px;">{{ number_format((float)($plantilla->authorized_annual_salary / 12), 2) }}</span> Pesos per month.
        </div>

        <div class="paragraph">
            The nature of this appointment is <span class="field" style="min-width: 150px;">{{ strtoupper($plantilla->nature_of_appointment ?? '______________') }}</span>
            vice <span class="field" style="min-width: 200px;">________________________</span>, 
            who was <span class="field" style="min-width: 150px;">________________________</span>, with Plantilla Item No. 
            <span class="field" style="min-width: 100px;">{{ $plantilla->item }}</span>.
        </div>
        
        <div class="paragraph">
            This appointment shall take effect on the date of signing by the appointing officer/authority.
        </div>
    </div>

    <div class="signature-area">
        <div style="float: left; width: 300px; margin-top: 20px;">
            Very truly yours,<br><br><br>
            _____________________________<br>
            <strong>Appointing Officer/Authority</strong>
        </div>
        
        <div class="signature-box">
            <div class="signature-line"></div>
            <strong>Date of Signing</strong>
        </div>
    </div>

    <div class="footer-note">
        <strong>Certification:</strong><br><br>
        This is to certify that all requirements and supporting papers pursuant to CSC MC No. 24, s. 2017, as amended, have been complied with, reviewed and found to be in order.<br><br><br>
        _____________________________________<br>
        <strong>HRMO</strong>
    </div>

</body>
</html>
