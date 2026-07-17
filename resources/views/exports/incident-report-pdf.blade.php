<!DOCTYPE html>
<html>
<head>
<style>
    @page { size: A4; margin: 0.5in 0.5in 0.1in 0.5in; }
    body { font-family: 'Times New Roman', serif; font-size: 10.5pt; padding: 0; line-height: 1.15; color: #000; }

    .report-title { text-align: center; font-size: 13pt; font-weight: bold; text-transform: uppercase; margin: 6px 0 14px; }
    .ref-line { text-align: center; font-size: 9.5pt; margin-bottom: 18px; }

    .field-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    .field-table td { padding: 3px 4px; vertical-align: top; }
    .field-table td.label { width: 160px; font-weight: bold; }

    .section-title { font-size: 11pt; font-weight: bold; text-transform: uppercase; margin: 14px 0 6px; border-bottom: 1px solid #000; padding-bottom: 2px; }
    .narrative { text-align: justify; margin-bottom: 10px; }

    .advisory-box { border: 1px solid #000; padding: 8px 10px; margin: 10px 0; font-size: 9.5pt; }
    .advisory-box .label { font-weight: bold; text-transform: uppercase; }

    .closing { margin-top: 15px; margin-bottom: 60px; }
    .signatory { line-height: 1.2; margin-bottom: 2px; }
    .signatory b { font-weight: bold; text-transform: uppercase; }
    .signatory-gap { height: 17px; }

    .cc-block { margin-top: 0; font-size: 8.5pt; }

    .footer { position: fixed; bottom: 0.02in; left: 0; font-size: 8pt; font-weight: bold; }
</style>
</head>
<body>

@include('exports.partials.official-header')

<div class="report-title">Incident Report</div>
<div class="ref-line">Reference No. {{ $incident->reference_no }}</div>

<table class="field-table">
    <tr>
        <td class="label">Date/Time of Incident</td>
        <td>{{ $incident->incident_datetime->format('F d, Y g:i A') }}</td>
        <td class="label">Date Reported</td>
        <td>{{ $incident->reported_at->format('F d, Y') }}</td>
    </tr>
    <tr>
        <td class="label">Location</td>
        <td>{{ $incident->location ?? 'N/A' }}</td>
        <td class="label">Category</td>
        <td>{{ ucfirst($incident->category) }}</td>
    </tr>
    <tr>
        <td class="label">Reported By</td>
        <td colspan="3">{{ optional($incident->reporter)->name ?? 'N/A' }}</td>
    </tr>
</table>

<div class="section-title">Involved Person/Employee</div>
<table class="field-table">
    <tr>
        <td class="label">Name</td>
        <td>{{ $incident->involved_person_name ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td class="label">Position</td>
        <td>{{ $incident->involved_position ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td class="label">Office/Department</td>
        <td>{{ $incident->involved_office ?? 'N/A' }}</td>
    </tr>
</table>

<div class="section-title">Narrative of Facts</div>
<div class="narrative">{{ $incident->narrative }}</div>

@if($incident->witnesses)
<div class="section-title">Witnesses</div>
<div class="narrative">{{ $incident->witnesses }}</div>
@endif

@if($incident->immediate_action_taken)
<div class="section-title">Immediate Action Taken</div>
<div class="narrative">{{ $incident->immediate_action_taken }}</div>
@endif

@if($incident->advisory_generated_at)
<div class="advisory-box">
    <div class="label">{{ \App\Support\Incident\RulesAdvisoryEngine::ADVISORY_LABEL }}</div>
    <ul>
        @foreach($incident->advisory_citations ?? [] as $c)
            <li><b>{{ $c['provision'] }}</b> ({{ $c['classification'] }}) — {{ $c['penalty_range'] }}</li>
        @endforeach
    </ul>
    <div>Suggested Recommendation: {{ $incident->advisory_recommendation }}</div>
</div>
@endif

@if($incident->status === 'finalized')
<div class="section-title">Finalization</div>
<table class="field-table">
    <tr>
        <td class="label">Track</td>
        <td>{{ ucfirst($incident->final_track) }}</td>
    </tr>
    <tr>
        <td class="label">Reviewed/Finalized By</td>
        <td>{{ optional($incident->finalizer)->name ?? 'N/A' }} on {{ $incident->finalized_at?->format('F d, Y') }}</td>
    </tr>
    @if($incident->review_notes)
    <tr>
        <td class="label">Review Notes</td>
        <td>{{ $incident->review_notes }}</td>
    </tr>
    @endif
</table>
@endif

<div class="closing">Prepared by:</div>

<div class="signatory">
    <b>{{ optional($incident->reporter)->name ?? 'HR Management Officer' }}</b><br>
    Reporting Officer
</div>

<div class="signatory-gap"></div>

@if($ccRecipients)
<div class="cc-block">
    <strong>Copy Furnished:</strong><br>
    {!! $ccRecipients !!}
</div>
@endif

<div class="footer">
    Reference No. {{ $incident->reference_no }}
</div>

</body>
</html>
