<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>List of Applicants</title>
    <style>
        @page { margin: 30px 40px; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            color: #000;
        }
        .header-container {
            width: 100%;
            text-align: center;
            margin-bottom: 5px;
        }
        .logos-wrapper {
            margin-bottom: 5px;
            text-align: center;
        }
        .logos-wrapper img {
            height: 70px;
            vertical-align: middle;
            margin: 0 10px;
        }
        .header-text {
            font-size: 11px;
            text-align: center;
        }
        .header-text b {
            font-size: 12px;
        }
        .title-block {
            background-color: #dce6f1;
            border: 1px solid #7f9db9;
            text-align: center;
            padding: 5px;
            font-weight: bold;
            color: #1f497d;
            margin-top: 5px;
        }
        .title-block .subtitle {
            font-weight: normal;
            font-size: 10px;
            color: #000;
            margin-top: 2px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #a5a5a5;
            padding: 4px 5px;
            vertical-align: top;
        }
        table.data-table th {
            background-color: #dce6f1;
            font-weight: bold;
            text-align: left;
        }
        table.sig-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.sig-table td {
            border: 1px solid #a5a5a5;
            padding: 5px;
            vertical-align: middle;
        }
        .sig-label {
            font-weight: bold;
            width: 40%;
        }
        .sig-line {
            color: #7f7f7f;
            font-style: italic;
            width: 60%;
        }
    </style>
</head>
<body>

    <div class="header-container">
        <div class="logos-wrapper">
            @if(file_exists(public_path('img/logo.png')))
                <img src="{{ public_path('img/logo.png') }}" alt="Provincial Seal">
            @endif

            @if(file_exists(public_path('img/phrmologo.jpg')))
                <img src="{{ public_path('img/phrmologo.jpg') }}" alt="PHRMO Logo">
            @elseif(file_exists(public_path('img/phrmologo.png')))
                <img src="{{ public_path('img/phrmologo.png') }}" alt="PHRMO Logo">
            @endif

            @if(file_exists(public_path('img/bagong-pilipinas.png')))
                <img src="{{ public_path('img/bagong-pilipinas.png') }}" alt="Bagong Pilipinas">
            @endif
        </div>
        
        <div class="header-text">
            Republic of the Philippines<br>
            <b>PROVINCE OF BUKIDNON</b><br>
            Provincial Capitol
        </div>
    </div>

    <div class="title-block">
        PROVINCIAL HUMAN RESOURCE MANAGEMENT OFFICE<br>
        LIST OF APPLICANTS<br>
        <div class="subtitle">
            Period: {{ \Carbon\Carbon::parse($startDate)->format('F j, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('F j, Y') }}
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%;">OFFICE</th>
                <th style="width: 20%;">VACANT<br>POSITION</th>
                <th style="width: 15%;">ITEM NO.</th>
                <th style="width: 5%;">SG</th>
                <th style="width: 45%;">LIST OF APPLICANTS<br><span style="font-weight:normal; font-size:9px;">(Last Name, First Name, Middle Name)</span></th>
            </tr>
        </thead>
        <tbody>
            @forelse($applicants as $applicant)
                <tr>
                    <td style="font-style: italic;">{{ $applicant->office ?: '-' }}</td>
                    <td style="font-style: italic;">{{ $applicant->position_applied }}</td>
                    <td>{{ $applicant->item_no ?: '-' }}</td>
                    <td>{{ $applicant->sg }}</td>
                    <td>{{ mb_strtoupper($applicant->last_name) }}, {{ mb_strtoupper($applicant->first_name) }} {{ mb_strtoupper($applicant->middle_name) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px;">No applicants found for the selected criteria.</td>
                </tr>
            @endforelse
            
            <!-- Generate a few empty rows to match the look of the template -->
            @for($i = 0; $i < (10 - count($applicants)); $i++)
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            @endfor
        </tbody>
    </table>

    <table class="sig-table">
        <tr>
            <td class="sig-label">Prepared by (HRMO-Appt. Section Head)</td>
            <td class="sig-line">Signature / Date</td>
        </tr>
        <tr>
            <td class="sig-label">Reviewed by (HRMO IV / Division Head)</td>
            <td class="sig-line">Signature / Date</td>
        </tr>
        <tr>
            <td class="sig-label">Certified by (PHRMO Dept. Head)</td>
            <td class="sig-line">Signature / Date / Official Stamp</td>
        </tr>
    </table>

</body>
</html>
