<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pre-Evaluation Assessment Matrix</title>
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
            height: 60px;
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
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            margin-top: 10px;
            margin-bottom: 15px;
        }
        table.info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-weight: bold;
        }
        table.info-table td {
            border: 1px solid #000;
            padding: 4px 5px;
            font-size: 11px;
        }
        .info-label {
            background-color: #f2f2f2;
            width: 25%;
        }
        
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: middle;
            text-align: center;
        }
        table.data-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        table.data-table td.text-left {
            text-align: left;
        }
        
        .sign-off-section {
            margin-top: 30px;
        }
        .sign-off-title {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .signature-table {
            width: 50%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        .signature-table td {
            padding-bottom: 20px;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            margin-bottom: 5px;
        }
        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    @foreach($groupedApplicants as $groupKey => $applicants)
        @php
            $firstApp = $applicants->first();
            $position = $firstApp->position_applied;
            $office = $firstApp->office ?: 'N/A';
            
            // Collect unique item numbers
            $itemNos = $applicants->pluck('item_no')->filter()->unique()->implode(', ');
            $sg = $firstApp->sg ?: '';
        @endphp

        <div class="header-container">
            <div class="logos-wrapper">
                @if(file_exists(public_path('img/logo.png')))
                    <img src="{{ public_path('img/logo.png') }}" alt="Provincial Seal">
                @endif
                
                {{-- Use PHRMO Logo as placeholder if HRMPSB isn't uploaded --}}
                @if(file_exists(public_path('img/hrmpsb.jpg')))
                    <img src="{{ public_path('img/hrmpsb.jpg') }}" alt="HRMPSB Logo">
                @elseif(file_exists(public_path('img/hrmpsb.png')))
                    <img src="{{ public_path('img/hrmpsb.png') }}" alt="HRMPSB Logo">
                @elseif(file_exists(public_path('img/phrmologo.png')))
                    <img src="{{ public_path('img/phrmologo.png') }}" alt="PHRMO Logo Placeholder">
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
            HUMAN RESOURCE MERIT PROMOTION AND SELECTION BOARD<br>
            <hr style="border: 2px solid #000; margin-top: 5px; margin-bottom: 5px;">
            PRE-EVALUATION PASSED/FAILED ASSESSMENT MATRIX
        </div>

        <b>I. Evaluation Screening Tracker</b>
        
        <table class="info-table">
            <tr>
                <td class="info-label">Position Vacant:</td>
                <td>{{ mb_strtoupper($position) }}</td>
            </tr>
            <tr>
                <td class="info-label">Item Number(s):</td>
                <td>{{ $itemNos }}</td>
            </tr>
            <tr>
                <td class="info-label">Salary Grade:</td>
                <td>{{ $sg }}</td>
            </tr>
            <tr>
                <td class="info-label">Office Assignment:</td>
                <td>{{ mb_strtoupper($office) }}</td>
            </tr>
        </table>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%;">App.<br>No.</th>
                    <th style="width: 20%;">Applicant Name<br>(Last, First, M.I.)</th>
                    <th style="width: 10%;">Basic QS<br>Compliance (Met<br>/ Unmet)</th>
                    <th style="width: 12%;">Examination Status<br>(Passed / Failed /<br>Absent)</th>
                    <th style="width: 10%;">Complete<br>Documents<br>Submitted On-<br>Time? (Yes / No)</th>
                    <th style="width: 13%;">FINAL RATING<br>(QUALIFIED /<br>DISQUALIFIED)</th>
                    <th style="width: 30%;">Remarks / Specific Grounds for Disqualification</th>
                </tr>
            </thead>
            <tbody>
                @foreach($applicants as $index => $applicant)
                @php
                    $ev = $applicant->evaluation;
                    $isQualified    = $ev && $ev->final_rating === 'Qualified';
                    $isDisqualified = $ev && $ev->final_rating === 'Disqualified';
                    $rowBg = $isQualified ? '#f0fff4' : ($isDisqualified ? '#fff5f5' : '#ffffff');
                @endphp
                    <tr style="background:{{ $rowBg }};">
                        <td>{{ $index + 1 }}</td>
                        <td class="text-left">
                            {{ mb_strtoupper($applicant->last_name) }}, {{ mb_strtoupper($applicant->first_name) }}
                            @if($applicant->middle_name) {{ mb_strtoupper(substr($applicant->middle_name, 0, 1)) }}. @endif
                        </td>
                        <td style="font-weight:bold; color:{{ $ev && $ev->qs_requirement === 'Met' ? '#155724' : ($ev && $ev->qs_requirement === 'Unmet' ? '#721c24' : '#888') }};">
                            {{ $ev->qs_requirement ?? '—' }}
                        </td>
                        <td style="color:{{ $ev && $ev->exam_status === 'Passed' ? '#155724' : ($ev && $ev->exam_status === 'Failed' ? '#721c24' : '#333') }};">
                            {{ $ev->exam_status ?? '—' }}
                        </td>
                        <td style="color:{{ $ev && $ev->docs_complete === 'Yes' ? '#155724' : ($ev && $ev->docs_complete === 'No' ? '#721c24' : '#333') }};">
                            {{ $ev->docs_complete ?? '—' }}
                        </td>
                        <td style="font-weight:bold; color:{{ $isQualified ? '#155724' : ($isDisqualified ? '#721c24' : '#333') }};">
                            {{ $ev->final_rating ?? '—' }}
                        </td>
                        <td class="text-left" style="font-size:9px;">{{ $ev->remarks ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="sign-off-section">
            <b>III. Sign-off and Endorsement</b><br>
            <i>Evaluated and Certified True and Correct by:</i><br><br>
            <b>THE PHRMO PRE-EVALUATION TEAM</b>
            
            <table class="signature-table">
                <tr>
                    <td>
                        <div class="signature-line"></div>
                        P.G Assistant Department Head
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="signature-line"></div>
                        HRMO IV
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="signature-line"></div>
                        HRMO III
                    </td>
                </tr>
            </table>
            
            <i>Date Signed: ________________________</i>
        </div>

        @if (!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

</body>
</html>
