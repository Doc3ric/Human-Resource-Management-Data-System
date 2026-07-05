<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Layout C - Scoring Sheet</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h1 { font-size: 16px; margin: 0; }
        .header h2 { font-size: 13px; margin: 5px 0 0; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #aaa; padding: 5px; text-align: left; }
        th { background-color: #f0f0f0; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>SCORING SHEET (LAYOUT C)</h1>
        <h2>{{ $applicant->last_name }}, {{ $applicant->first_name }} - {{ $applicant->position_applied }}</h2>
    </div>

    <h3>I. TWG Evaluation</h3>
    @if($twgSubmission)
    <table>
        <thead>
            <tr>
                <th>Criterion</th>
                <th class="text-center">Auto Score</th>
                <th class="text-center">Assessor Score</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($twgScores as $score)
            <tr>
                <td>{{ $score->criterion->criterion_name ?? 'Unknown Criterion' }}</td>
                <td class="text-center">{{ $score->auto_populated_value ?? '-' }}</td>
                <td class="text-center">{{ $score->assessor_value ?? '-' }}</td>
                <td>{{ $score->assessor_notes }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th class="text-right fw-bold">TOTAL:</th>
                <th class="text-center fw-bold">{{ $twgSubmission->total_auto }}</th>
                <th class="text-center fw-bold">{{ $twgSubmission->total_assessor }}</th>
                <th>Overall: {{ $twgSubmission->percentage }}% ({{ $twgSubmission->adjectival_classification }})</th>
            </tr>
        </tfoot>
    </table>
    <p><strong>Recommendation:</strong> {{ $twgSubmission->recommendation }}</p>
    @else
    <p>No TWG evaluation submitted yet.</p>
    @endif

    <h3>II. HRMPSB Interview Evaluation</h3>
    @if($hrmpsbEvaluations->count() > 0)
    <table>
        <thead>
            <tr>
                <th>Panel Member</th>
                <th class="text-center">Score</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($hrmpsbEvaluations as $eval)
            <tr>
                <td>{{ $eval->rater_display_name }}</td>
                <td class="text-center">{{ $eval->total_score }}</td>
                <td>{{ $eval->remarks }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th class="text-right fw-bold">AVERAGE SCORE:</th>
                <th class="text-center fw-bold">{{ number_format($hrmpsbEvaluations->avg('total_score'), 2) }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>
    @else
    <p>No HRMPSB interview evaluations recorded yet.</p>
    @endif
</body>
</html>
