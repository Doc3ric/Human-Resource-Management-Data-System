<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Layout A - Applicant Profile</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h1 { font-size: 18px; margin: 0; }
        .header h2 { font-size: 14px; margin: 5px 0 0; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f5f5f5; width: 30%; }
        .section-title { background-color: #e0e0e0; font-weight: bold; padding: 5px; margin-top: 15px; margin-bottom: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Provincial Government of Bukidnon — PHRMO</h1>
        <h2>TWG Applicant Profile (Layout A — Examiner's Copy)</h2>
        <h2>{{ $applicant->position_applied }} - {{ $applicant->item_no }}</h2>
    </div>

    <div class="section-title">I. Personal Information</div>
    <table>
        <tr>
            <th>Name</th>
            <td>{{ $applicant->last_name }}, {{ $applicant->first_name }} {{ $applicant->middle_name }} {{ $applicant->name_extension }}</td>
        </tr>
        <tr>
            <th>Date of Birth</th>
            <td>{{ $applicant->date_of_birth ? $applicant->date_of_birth->format('F d, Y') : 'N/A' }}</td>
        </tr>
        <tr>
            <th>Sex</th>
            <td>{{ $applicant->sex ?: 'N/A' }}</td>
        </tr>
        <tr>
            <th>Contact No.</th>
            <td>{{ $applicant->phone_number ?: 'N/A' }}</td>
        </tr>
        <tr>
            <th>Address</th>
            <td>{{ $applicant->address ?: 'N/A' }}</td>
        </tr>
    </table>

    <div class="section-title">II. Education</div>
    <table>
        <tr>
            <th>Highest Attainment</th>
            <td>{{ $applicant->highest_educational_attainment ?: 'N/A' }}</td>
        </tr>
        <tr>
            <th>Course</th>
            <td>{{ $applicant->degree ?: 'N/A' }}</td>
        </tr>
    </table>

    <div class="section-title">III. Experience</div>
    <table>
        @if($applicant->is_pgb_employee)
        <tr>
            <th>Current PGB Position</th>
            <td>{{ $applicant->current_position ?: 'N/A' }} ({{ $applicant->pgb_status ?: 'N/A' }})</td>
        </tr>
        <tr>
            <th>Length of Service</th>
            <td>{{ $applicant->length_of_service ?: 'N/A' }}</td>
        </tr>
        @endif
        @if($applicant->has_non_pgb_employment)
        <tr>
            <th>Non-PGB Employer</th>
            <td>{{ $applicant->np_designation ?: 'N/A' }} at {{ $applicant->np_employer ?: 'N/A' }} ({{ $applicant->np_period ?: 'N/A' }})</td>
        </tr>
        @endif
        @if($applicant->responsibilities)
        <tr>
            <th>Key Responsibilities</th>
            <td>{{ $applicant->responsibilities }}</td>
        </tr>
        @endif
        @if(!$applicant->is_pgb_employee && !$applicant->has_non_pgb_employment && !$applicant->responsibilities)
        <tr><td colspan="2">No experience records found.</td></tr>
        @endif
    </table>

    <div class="section-title">IV. Eligibility</div>
    <table>
        @if($applicant->eligibility && strtolower(trim($applicant->eligibility)) !== 'n/a')
        <tr><td>{{ $applicant->eligibility }}</td></tr>
        @else
        <tr><td>No eligibility records found.</td></tr>
        @endif
    </table>

    <div class="section-title">V. Training</div>
    <table>
        @if($applicant->training_hours)
        <tr><td>{{ $applicant->training_hours }} relevant training hours on file.</td></tr>
        @else
        <tr><td>No training records found.</td></tr>
        @endif
    </table>
</body>
</html>
