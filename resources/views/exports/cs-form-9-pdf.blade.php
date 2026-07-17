<!DOCTYPE html>
<html>
<head>
<style>
    @page { size: A4; margin: 0.5in; }
    body { font-family: 'Times New Roman', serif; font-size: 10.5pt; color: #000; }
    h1 { text-align: center; font-size: 13pt; margin-bottom: 2px; }
    .subtitle { text-align: center; font-size: 9.5pt; margin-bottom: 16px; }
    table.form-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    table.form-table th, table.form-table td { border: 1px solid #000; padding: 4px 6px; vertical-align: top; font-size: 10pt; }
    table.form-table th { background: #f0f0f0; text-align: left; width: 32%; }
    .section-title { font-weight: bold; margin: 12px 0 4px; text-transform: uppercase; font-size: 10.5pt; }
    .signature-block { margin-top: 40px; width: 45%; }
    .signature-line { border-top: 1px solid #000; margin-top: 40px; padding-top: 2px; text-align: center; }
</style>
</head>
<body>

@include('exports.partials.official-header')

<h1>Request for Publication of Vacant Positions</h1>
<div class="subtitle">CS Form No. 9, Revised 2025 &mdash; per RA 7041 / 2025 ORAOHRA Sec. 26</div>

<table class="form-table">
    <tr><th>Agency</th><td>{{ $publicationRequest->agency_name }}</td></tr>
    <tr><th>Contact Person</th><td>{{ $publicationRequest->agency_contact_person }}</td></tr>
    <tr><th>Contact Number</th><td>{{ $publicationRequest->agency_contact_number }}</td></tr>
    <tr><th>Contact Email</th><td>{{ $publicationRequest->agency_contact_email }}</td></tr>
    <tr><th>Submission Mode</th><td>{{ str_replace('_', ' ', $publicationRequest->submission_mode) }}</td></tr>
</table>

<div class="section-title">Position Details</div>
<table class="form-table">
    <tr><th>Position Title</th><td>{{ $vacancy->position_title }}{{ $vacancy->parenthetical_title ? ' (' . $vacancy->parenthetical_title . ')' : '' }}</td></tr>
    <tr><th>Salary Grade</th><td>{{ $vacancy->salary_grade }}</td></tr>
    <tr><th>Monthly Salary</th><td>{{ number_format($vacancy->monthly_salary, 2) }}</td></tr>
    <tr><th>Place of Assignment</th><td>{{ $vacancy->place_of_assignment }}</td></tr>
    <tr><th>Office/Division</th><td>{{ $vacancy->office_division }}</td></tr>
    <tr><th>Appointment Status</th><td>{{ $vacancy->appointment_status }}</td></tr>
    <tr><th>Nature of Vacancy</th><td>{{ $vacancy->vacancy_type }}{{ $vacancy->vice_whom ? ' — Vice ' . $vacancy->vice_whom : '' }}</td></tr>
    @if($vacancy->is_anticipated)
        <tr><th>Anticipated Vacancy</th><td>Yes — incumbent separation expected {{ $vacancy->anticipated_incumbent_separation_date?->format('F d, Y') }} (Sec. 31)</td></tr>
    @endif
</table>

<div class="section-title">Qualification Standards</div>
<table class="form-table">
    <tr><th>Education</th><td>{{ $vacancy->qs_education }}</td></tr>
    <tr><th>Training</th><td>{{ $vacancy->qs_training }}</td></tr>
    <tr><th>Experience</th><td>{{ $vacancy->qs_experience }}</td></tr>
    <tr><th>Eligibility</th><td>{{ $vacancy->qs_eligibility }}</td></tr>
</table>

@if($publicationRequest->signatures->isNotEmpty())
    @php $sig = $publicationRequest->signatures->last(); @endphp
    <div class="signature-block">
        <img src="{{ $sig->signature_image }}" style="max-width:220px;max-height:70px;">
        <div class="signature-line">
            <b>{{ $sig->signatory_name }}</b><br>
            {{ $sig->signatory_position }}
        </div>
    </div>
@else
    <div class="signature-block">
        <div class="signature-line">
            Department Head / Appointing Authority
        </div>
    </div>
@endif

</body>
</html>
