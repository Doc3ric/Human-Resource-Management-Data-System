<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Layout B - Public Testing Center Copy</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #111; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h1 { font-size: 16px; margin: 0; font-weight: bold; }
        .header h2 { font-size: 14px; margin: 5px 0 0; color: #333; }
        .header h3 { font-size: 12px; margin: 5px 0 0; font-weight: normal; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 25px; page-break-inside: auto; }
        tr { page-break-inside: avoid; page-break-after: auto; }
        th, td { border: 1px solid #555; padding: 8px; text-align: left; vertical-align: top; }
        th { background-color: #e2e8f0; font-weight: bold; }
        .text-center { text-align: center; }
        .footer-notice { margin-top: 30px; font-size: 10px; color: #444; border-top: 1px dashed #ccc; padding-top: 10px; text-align: justify; }
        .footer-notice p { margin: 5px 0; line-height: 1.4; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Provincial Government of Bukidnon — PHRMO — TWG Applicant Profile</h1>
        <h2>Examination Roster (LAYOUT B — PUBLIC TESTING CENTER COPY)</h2>
        <h3>Classification: {{ $classification === 'pgb_jo' ? 'PGB Job Order' : 'External' }} | Date: {{ $date !== 'none' ? \Carbon\Carbon::parse($date)->format('F d, Y') : 'TBA' }} | Room: {{ $room !== 'none' ? $room : 'TBA' }}</h3>
    </div>

    @if($applicants->isEmpty())
        <p>No applicants found for this schedule.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;" class="text-center">Table No.</th>
                    <th style="width: 20%;">Date/Time</th>
                    <th style="width: 25%;">Privacy-Masked Identifier</th>
                    <th style="width: 20%;">Contact Number</th>
                    <th style="width: 25%;">Barangay/City Location</th>
                </tr>
            </thead>
            <tbody>
                @foreach($applicants as $app)
                    @php
                        // Obfuscation formula: date of birth in DDMMYYYY format followed by first name's initial
                        $dobFormat = $app->date_of_birth ? $app->date_of_birth->format('dmY') : '00000000';
                        $initial = substr($app->first_name, 0, 1);
                        $maskedId = $dobFormat . strtoupper($initial);

                        // Mask phone number (e.g. 0917***4567)
                        $phone = $app->phone_number;
                        if ($phone && strlen($phone) >= 10) {
                            $phone = substr($phone, 0, 4) . '***' . substr($phone, -4);
                        } else {
                            $phone = 'N/A';
                        }

                        // Basic address masking (try to strip house/street if present)
                        // If it has commas, grab the last two segments (usually Brgy, City)
                        $address = $app->address ?: 'N/A';
                        if ($address !== 'N/A') {
                            $parts = array_map('trim', explode(',', $address));
                            if (count($parts) > 2) {
                                $address = implode(', ', array_slice($parts, -2));
                            }
                        }
                    @endphp
                    <tr>
                        <td class="text-center">{{ $app->table_number ?: 'N/A' }}</td>
                        <td>{{ $app->exam_date_time }}</td>
                        <td><strong>{{ $maskedId }}</strong></td>
                        <td>{{ $phone }}</td>
                        <td>{{ $address }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer-notice">
        <p><strong>DATA PRIVACY ACT DISCLAIMER:</strong> In accordance with Republic Act No. 10173 (Data Privacy Act of 2012), the Provincial Government of Bukidnon protects the personal data of all applicants. Full names are masked on public postings. Examinees can verify their schedules using their unique date of birth and first name initial format (ddmmyyyy[Initial]). For identity verification, please present a valid government-issued ID to the Examiner inside the room.</p>
        <p><strong>NOTICE ON EXAMINATION EXEMPTIONS:</strong> Only qualified applicants who are legally required to undergo the screening process are listed above. In accordance with the 2025 Omnibus Rules on Appointments and Other Human Resource Actions (ORAOHRA) and at the proper discretion of the Board/Examiners, specific technical positions exempt from written examination processes (such as licensed Medical Doctors, specialized Information Technology personnel, and other highly specialized categories under CSC guidelines) may bypass this phase and advance directly to technical assessment or HRMPSB deliberation.</p>
    </div>
</body>
</html>
