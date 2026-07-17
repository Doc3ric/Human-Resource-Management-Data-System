<style>
.matrix-wrap { overflow-x: auto; }
.matrix-table { width: 100%; border-collapse: collapse; font-size: 12px; min-width: 700px; }
.matrix-table th, .matrix-table td { padding: 7px 10px; border: 1px solid #e2e8f0; vertical-align: middle; }
.matrix-table thead th { background: #1e3a5f; color: #fff; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; text-align: center; white-space: nowrap; }
.matrix-table thead th.th-criteria { text-align: left; min-width: 280px; }
.matrix-table .row-category { background: #1e3a5f; color: #fff; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .6px; }
.matrix-table .row-category td { padding: 6px 10px; }
.matrix-table .row-criterion td.criteria-text { font-size: 11px; color: #374151; }
.matrix-table .row-criterion td.score-cell { text-align: center; font-weight: 700; }
.matrix-table .row-subtotal td { background: #f0f9ff; font-weight: 800; font-size: 11px; color: #0369a1; }
.matrix-table .row-subtotal td.criteria-text { text-transform: uppercase; font-size: 10px; letter-spacing: .4px; }
.matrix-table .row-total td { background: #082977; color: #fff; font-weight: 800; font-size: 13px; }
.matrix-table .row-total td.criteria-text { text-transform: uppercase; }
.col-avg { background: #fef9c3 !important; color: #713f12 !important; }
.member-header-name { font-size: 11px; font-weight: 800; }
.member-header-pos { font-size: 9px; font-weight: 500; opacity: .75; }
.score-1 { color: #dc2626; }
.score-2 { color: #d97706; }
.score-3 { color: #2563eb; }
.score-4 { color: #059669; }
.pct-cell { font-size: 12px; font-weight: 800; }
.badge-rater-count { background: rgba(255,255,255,.18); color: #fff; padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 700; }
@media print {
    .no-print { display: none !important; }
    .matrix-table { font-size: 10px; }
    .matrix-table th, .matrix-table td { padding: 4px 6px; }
}
</style>

<div class="content-wrapper p-4">

{{-- Header --}}
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3 no-print">
    <div>
        <h4 class="mb-1 fw-bold text-primary">
            <i class="bi bi-grid-3x3-gap-fill me-2"></i>HRMPSB Interview Scoring Matrix
        </h4>
        <p class="text-muted mb-0">
            {{ \App\Support\BlindScoringId::forApplicant($applicant) }}
            &nbsp;&middot;&nbsp; <em>{{ $applicant->position_applied ?? 'N/A' }}</em>
            &nbsp;&middot;&nbsp; {{ $applicant->office ?? '' }}
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('recruitment.hrmpsb.interview.index') }}"
           class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
        <a href="{{ route('recruitment.hrmpsb.interview.detail_report', $applicant->id) }}"
           class="btn btn-sm btn-warning fw-bold text-dark" target="_blank">
            <i class="bi bi-file-earmark-person-fill me-1"></i>Panel Interview Report
        </a>
        <a href="{{ route('recruitment.hrmpsb.comparative_report', ['position' => $applicant->position_applied, 'office' => $applicant->office]) }}"
           class="btn btn-sm btn-success">
            <i class="bi bi-file-earmark-bar-graph me-1"></i>Comparative Report
        </a>
        <button onclick="window.print()" class="btn btn-sm btn-outline-dark no-print">
            <i class="bi bi-printer me-1"></i>Print Matrix
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show no-print" role="alert">
        <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Stat cards --}}
<div class="row g-3 mb-4 no-print">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center" style="border-radius:12px;">
            <div class="card-body py-3">
                <div class="text-muted small fw-bold text-uppercase mb-1">Average Score</div>
                <div class="fs-3 fw-bold text-primary">{{ number_format($averageScore, 2) }}%</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center" style="border-radius:12px;">
            <div class="card-body py-3">
                <div class="text-muted small fw-bold text-uppercase mb-1">Panel Members</div>
                <div class="fs-3 fw-bold text-success">{{ $evaluations->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center" style="border-radius:12px;">
            <div class="card-body py-3">
                <div class="text-muted small fw-bold text-uppercase mb-1">Position</div>
                <div class="fw-bold text-dark" style="font-size:12px;line-height:1.3;">{{ $applicant->position_applied ?? '—' }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center" style="border-radius:12px;">
            <div class="card-body py-3">
                <div class="text-muted small fw-bold text-uppercase mb-1">Office</div>
                <div class="fw-bold text-dark" style="font-size:12px;line-height:1.3;">{{ $applicant->office ?? '—' }}</div>
            </div>
        </div>
    </div>
</div>

@if($evaluations->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="bi bi-inbox fs-1 opacity-50 d-block mb-3"></i>
        <h5>No evaluations yet</h5>
        <p class="small">Panel members have not scored this applicant yet.<br>
        They access the scoring form at <strong>/panel</strong>.</p>
    </div>
@else

@php
$members = $evaluations->map(function($eval) {
    return [
        'name'     => $eval->rater?->name
                      ?? $eval->panelMember?->name
                      ?? $eval->rater_name
                      ?? 'Unknown',
        'position' => $eval->rater?->roles->first()?->name
                      ?? ($eval->panelMember?->position ?: 'HRMPSB Member'),
        'ratings'  => $eval->ratings ?? [],
        'total'    => (float)$eval->total_score,
        'rated_at' => $eval->created_at,
        'remarks'  => $eval->remarks,
    ];
})->values();

$n = $members->count();

$categories = [
    'appearance' => [
        'label'  => 'Personal Appearance',
        'weight' => $weights['appearance'],
        'items'  => ['appearance_1'],
        'labels' => $criteriaLabels['appearance'],
    ],
    'knowledge' => [
        'label'  => 'Knowledge',
        'weight' => $weights['knowledge'],
        'items'  => ['knowledge_1','knowledge_2','knowledge_3','knowledge_4'],
        'labels' => $criteriaLabels['knowledge'],
    ],
    'communication' => [
        'label'  => 'Communication',
        'weight' => $weights['communication'],
        'items'  => ['comm_1','comm_2','comm_3'],
        'labels' => $criteriaLabels['communication'],
    ],
    'other' => [
        'label'  => 'Other Criteria',
        'weight' => $weights['other'],
        'items'  => ['other_1','other_2','other_3','other_4','other_5',
                     'other_6','other_7','other_8','other_9','other_10'],
        'labels' => $criteriaLabels['other'],
    ],
];

$catScore = function($ratings, $catKey) use ($categories) {
    $cfg     = $categories[$catKey];
    $sum     = collect($cfg['items'])->sum(fn($k) => (int)($ratings[$k] ?? 0));
    $maxPoss = count($cfg['items']) * 4;
    return $maxPoss > 0 ? ($sum / $maxPoss) * $cfg['weight'] : 0;
};
@endphp

{{-- Legend --}}
<div class="d-flex gap-3 flex-wrap mb-3 no-print" style="font-size:11px;font-weight:700;">
    <span class="text-muted">Rating Scale:</span>
    <span class="score-4"><i class="bi bi-circle-fill"></i> 4 – Outstanding</span>
    <span class="score-3"><i class="bi bi-circle-fill"></i> 3 – Very Satisfactory</span>
    <span class="score-2"><i class="bi bi-circle-fill"></i> 2 – Satisfactory</span>
    <span class="score-1"><i class="bi bi-circle-fill"></i> 1 – Poor</span>
</div>

{{-- FULL CRITERIA MATRIX --}}
<div class="card shadow-sm border-0 mb-4" style="border-radius:14px;overflow:hidden;">
    <div class="card-header d-flex align-items-center justify-content-between"
         style="background:#1e3a5f;color:#fff;padding:14px 20px;">
        <span class="fw-bold" style="font-size:14px;">
            <i class="bi bi-table me-2"></i>Individual Scores — All 18 Criteria
        </span>
        <span class="badge-rater-count">{{ $n }} rater{{ $n !== 1 ? 's' : '' }}</span>
    </div>
    <div class="matrix-wrap card-body p-0">
        <table class="matrix-table">
            <thead>
                <tr>
                    <th class="th-criteria">Criteria</th>
                    @foreach($members as $m)
                        <th>
                            <div class="member-header-name">{{ $m['name'] }}</div>
                            <div class="member-header-pos">{{ $m['position'] }}</div>
                        </th>
                    @endforeach
                    <th style="background:#b45309;color:#fff;">
                        <div class="member-header-name">AVERAGE</div>
                        <div class="member-header-pos">{{ $n }} rater{{ $n !== 1 ? 's' : '' }}</div>
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $catKey => $cat)
                    <tr class="row-category">
                        <td colspan="{{ $n + 2 }}">
                            {{ $cat['label'] }}
                            <span style="opacity:.65;font-size:10px;font-weight:600;margin-left:8px;">({{ $cat['weight'] }}% weight)</span>
                        </td>
                    </tr>

                    @foreach($cat['items'] as $criterionKey)
                        @php
                            $label     = $criteriaLabels[$catKey][$criterionKey] ?? $criterionKey;
                            $avgRating = round($members->avg(fn($m) => (int)($m['ratings'][$criterionKey] ?? 0)), 2);
                        @endphp
                        <tr class="row-criterion">
                            <td class="criteria-text">{{ $label }}</td>
                            @foreach($members as $m)
                                @php $v = (int)($m['ratings'][$criterionKey] ?? 0); @endphp
                                <td class="score-cell {{ $v > 0 ? 'score-'.$v : '' }}">
                                    {{ $v ?: '—' }}
                                </td>
                            @endforeach
                            <td class="score-cell" style="background:#fef9c3;color:#92400e;font-weight:800;">
                                {{ $avgRating > 0 ? number_format($avgRating, 2) : '—' }}
                            </td>
                        </tr>
                    @endforeach

                    @php $avgCatScore = $members->avg(fn($m) => $catScore($m['ratings'], $catKey)); @endphp
                    <tr class="row-subtotal">
                        <td class="criteria-text">
                            ↳ Category Score ({{ $cat['weight'] }}%)
                        </td>
                        @foreach($members as $m)
                            <td class="text-center pct-cell">{{ number_format($catScore($m['ratings'], $catKey), 2) }}%</td>
                        @endforeach
                        <td class="text-center pct-cell" style="background:#fef9c3;color:#92400e;">
                            {{ number_format($avgCatScore, 2) }}%
                        </td>
                    </tr>
                @endforeach

                <tr class="row-total">
                    <td class="criteria-text">TOTAL INTERVIEW SCORE</td>
                    @foreach($members as $m)
                        <td class="text-center" style="font-size:14px;">{{ number_format($m['total'], 2) }}%</td>
                    @endforeach
                    <td class="text-center" style="font-size:14px;background:#fef9c3;color:#92400e;">
                        {{ number_format($averageScore, 2) }}%
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Remarks --}}
@if($members->filter(fn($m) => !empty($m['remarks']))->isNotEmpty())
<div class="card shadow-sm border-0 mb-4" style="border-radius:14px;overflow:hidden;">
    <div class="card-header fw-bold"
         style="background:#f8fafc;border-bottom:1px solid #e2e8f0;font-size:13px;padding:12px 20px;">
        <i class="bi bi-chat-square-text me-2 text-secondary"></i>Remarks from Panel Members
    </div>
    <ul class="list-group list-group-flush">
        @foreach($members as $m)
            @if(!empty($m['remarks']))
                <li class="list-group-item" style="font-size:13px;">
                    <span class="fw-bold text-primary me-2">{{ $m['name'] }}:</span>
                    {{ $m['remarks'] }}
                    @if($m['rated_at'])
                        <span class="text-muted small ms-2">— {{ $m['rated_at']->format('M d, Y g:i A') }}</span>
                    @endif
                </li>
            @endif
        @endforeach
    </ul>
</div>
@endif

{{-- Consolidated score bar --}}
<div class="card border-0 shadow-sm mb-4" style="border-radius:14px;background:linear-gradient(135deg,#082977,#1e3a5f);overflow:hidden;">
    <div class="card-body p-4 text-white">
        <div class="row align-items-center">
            <div class="col-md-5">
                <div style="opacity:.7;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px;">
                    Consolidated Interview Score
                </div>
                <div style="font-size:48px;font-weight:900;line-height:1;letter-spacing:-2px;">
                    {{ number_format($averageScore, 2) }}%
                </div>
                <div style="opacity:.6;font-size:12px;margin-top:6px;">
                    Mean of {{ $n }} independent evaluation{{ $n !== 1 ? 's' : '' }}
                </div>
            </div>
            <div class="col-md-7 mt-3 mt-md-0">
                @foreach($categories as $catKey => $cat)
                    @php $avgCat = $members->avg(fn($m) => $catScore($m['ratings'], $catKey)); @endphp
                    <div class="d-flex justify-content-between mb-1" style="font-size:12px;">
                        <span style="opacity:.8;">{{ $cat['label'] }} <span style="opacity:.6;">({{ $cat['weight'] }}%)</span></span>
                        <span class="fw-bold">{{ number_format($avgCat, 2) }}%</span>
                    </div>
                    <div class="mb-2" style="height:5px;background:rgba(255,255,255,.15);border-radius:99px;">
                        <div style="height:5px;background:#facc15;border-radius:99px;
                                    width:{{ $cat['weight'] > 0 ? min(100, ($avgCat / $cat['weight']) * 100) : 0 }}%;"></div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Copy Panel Evaluations to Other Applications --}}
@php
    $siblingApplicants = \App\Models\Applicant::where('ain', $applicant->ain)
        ->where('id', '!=', $applicant->id)
        ->get();
@endphp
@if($siblingApplicants->count() > 0 && $evaluations->count() > 0)
<div class="card border-0 shadow-sm mb-4 no-print" style="border-left:4px solid #f59e0b !important; border-radius:12px;">
    <div class="card-header bg-warning bg-opacity-10 border-bottom d-flex align-items-center gap-2 py-3">
        <i class="bi bi-copy text-warning fs-5"></i>
        <div>
            <h6 class="mb-0 fw-bold">Copy Panel Evaluations to Other Applications</h6>
            <p class="mb-0 text-muted" style="font-size:12px;">
                <strong>{{ $applicant->full_name }}</strong> is also applying for {{ $siblingApplicants->count() }} other position(s).
                Ensure the applicant meets the qualifications of the other positions before copying.
            </p>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('recruitment.hrmpsb.interview.copy') }}" method="POST">
            @csrf
            <input type="hidden" name="source_applicant_id" value="{{ $applicant->id }}">
            <div class="table-responsive mb-3">
                <table class="table table-sm table-hover align-middle" style="font-size:13px;">
                    <thead class="table-light">
                        <tr>
                            <th style="width:36px;"><input type="checkbox" class="form-check-input" onchange="document.querySelectorAll('.eval-copy-chk').forEach(c=>c.checked=this.checked)"></th>
                            <th>Position Applied</th>
                            <th>Item No.</th>
                            <th>Office</th>
                            <th>Panel Evals?</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($siblingApplicants as $sib)
                        @php
                            $sibEvalCount = \App\Models\InterviewEvaluation::where('applicant_id', $sib->id)->count();
                        @endphp
                        <tr>
                            <td><input type="checkbox" name="target_applicant_ids[]" value="{{ $sib->id }}" class="form-check-input eval-copy-chk"></td>
                            <td class="fw-semibold">{{ $sib->position_applied }}</td>
                            <td>{{ $sib->item_no ?? '—' }}</td>
                            <td>{{ $sib->office ?? '—' }}</td>
                            <td>
                                @if($sibEvalCount > 0)
                                    <span class="badge bg-warning text-dark"><i class="bi bi-pencil-fill me-1"></i>Overwrite ({{ $sibEvalCount }})</span>
                                @else
                                    <span class="badge bg-light text-muted border">None yet</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="confirmEvalCopy" required>
                <label class="form-check-label fw-semibold" for="confirmEvalCopy" style="font-size:12px;">
                    I confirm the applicant has the same qualifications for the checked positions and approve copying these panel evaluations.
                </label>
            </div>
            <button type="submit" class="btn btn-warning fw-bold" onclick="return validateEvalCopyForm()">
                <i class="bi bi-copy me-1"></i> Copy Panel Evaluations to Checked Applications
            </button>
        </form>
    </div>
</div>
@endif

@endif {{-- end not empty evaluations --}}

</div>
<script>
function validateEvalCopyForm() {
    const checked = document.querySelectorAll('.eval-copy-chk:checked');
    if (checked.length === 0) { alert('Select at least one target application.'); return false; }
    const confirmed = document.getElementById('confirmEvalCopy');
    if (!confirmed || !confirmed.checked) { alert('Please check the confirmation checkbox.'); return false; }
    return confirm('Copy panel evaluations to ' + checked.length + ' application(s)? Existing evaluations for the same rater will be overwritten.');
}
</script>
