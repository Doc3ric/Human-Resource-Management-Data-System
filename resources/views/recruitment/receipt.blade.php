<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employment Application Receipt</title>
    <style>
        @page { margin: 40px 50px; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
        }
        .header-container {
            width: 100%;
            text-align: center;
            margin-bottom: 20px;
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
        .office-title {
            font-weight: bold;
            font-size: 14px;
            color: #1f497d;
            margin-top: 5px;
        }
        .doc-title {
            font-weight: bold;
            font-size: 12px;
            margin-top: 5px;
            text-decoration: underline;
        }
        .info-row {
            margin-bottom: 5px;
        }
        .divider {
            margin: 15px 0;
            border-bottom: 1px dashed #000;
            line-height: 0;
        }
        .content {
            text-align: justify;
            margin-top: 10px;
        }
        .content p {
            text-indent: 40px;
            margin-bottom: 10px;
        }
        .signature-block {
            margin-top: 40px;
            margin-left: 55%;
            text-align: center;
        }
        .disclaimer {
            margin-top: 20px;
            font-style: italic;
            font-size: 11px;
            text-align: justify;
        }
        .disclaimer-title {
            font-weight: bold;
            font-style: normal;
            font-size: 12px;
        }
        .agreement-list {
            margin-left: 20px;
            margin-top: 5px;
            line-height: 1.2;
        }
        .applicant-signature {
            margin-top: 30px;
            margin-left: 50%;
            text-align: center;
        }
        .applicant-name {
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
            display: inline-block;
            width: 80%;
            padding-bottom: 2px;
            margin-bottom: 3px;
        }
        .note {
            margin-top: 15px;
        }
        .note-title {
            font-weight: bold;
        }
        .note-text {
            font-style: italic;
            margin-left: 30px;
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
        
        <div class="office-title">PROVINCIAL HUMAN RESOURCE MANAGEMENT OFFICE</div>
        <div class="doc-title">Employment Application Acknowledgment and Agreement Receipt</div>
    </div>

    <div class="info-row">Reference No <u>{{ $applicant->reference_no }}</u></div>
    <div class="info-row">Date Receive <u>{{ $applicant->created_at->format('n/j/Y') }}</u></div>
    
    <div class="divider">========================================================================================================</div>

    <div class="content">
        Sir/Madam<br><br>
        <p>This is to acknowledge the receipt of your application for employment for the position of {{ $applicant->position_applied }} at {{ $applicant->office ?? '_________________' }}. We appreciate your interest in joining our workforce. Your application will be recorded in our database of applicants. Should the position you are applying for be scheduled for deliberation or should a vacancy be open which commensurate your qualification, you will be considered and be notified</p>
        <p>We thank you for considering the Provincial Government of Bukidnon as your potential employer.</p>
    </div>

    <div class="signature-block">
        Sincerely yours,<br><br><br>
        <b>(Sgd) AIDA B. LOVERES, MPA, MHRM</b><br>
        P.G Dept. Head-PHRMO
    </div>

    <div class="disclaimer">
        <span class="disclaimer-title">Disclaimer</span><br>
        Your privacy matters to us. We strictly adhere to the Data Privacy Act of 2012. Any personal information collected is solely used to enhance your experience with our services. We do not share your information without your consent. Also, this is a system generated receipt. No need for original signature.
    </div>

    <div class="divider">========================================================================================================</div>

    <div class="content">
        Sir/Madam<br><br>
        <p>By submitting my application, I confirm that the information provided is accurate and complete to the best of my knowledge. I understand that any false statements or omissions may result in disqualification from consideration of my employment application.</p>
        
        Furthermore, I agree that:
        <div class="agreement-list">
            1. The application I have submitted is valid for the position applied for only.<br>
            2. The application will remain active and valid for a period of one year from the date of receipt.<br>
            3. If there are any changes or updates to my application during this one-year period, it is my responsibility to inform the Human Resources Department in writing.<br>
            4. The receipt of my application does not guarantee employment or an interview.<br>
            5. The PGB reserves the right to disqualify or consider other candidates for the position, provided that it is in accordance with the Civil Service Commission rules and regulation on employment.
        </div>
    </div>

    <div class="applicant-signature">
        <div class="applicant-name">{{ $applicant->full_name }}</div>
        (Name and Signature of applicant)
    </div>

    <div class="note">
        <div class="note-title">Note to Applicant</div>
        <div class="note-text">Please retain a copy of this acknowledgement receipt for your reference. Should you have any questions or need further information, please do not hesitate to contact us.</div>
    </div>

</body>
</html>
