<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Interview Evaluation Report – {{ $applicant->full_name }}</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; color: #000; background: #fff; }

/* Screen wrapper */
.screen-controls {
    position: fixed; top: 0; left: 0; right: 0; z-index: 999;
    background: #1e3a5f; color: #fff; padding: 10px 20px;
    display: flex; align-items: center; gap: 12px; box-shadow: 0 2px 8px rgba(0,0,0,.3);
}
.screen-controls a, .screen-controls button {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 700;
    text-decoration: none; border: none; cursor: pointer;
}
.btn-back { background: rgba(255,255,255,.15); color: #fff; }
.btn-print { background: #facc15; color: #000; }
.screen-controls .title-text { flex: 1; font-size: 13px; font-weight: 700; opacity: .85; }

.page-wrap { max-width: 960px; margin: 70px auto 40px; padding: 0 20px; }

/* Report document */
.report-doc {
    background: #fff;
    border: 1px solid #ccc;
    padding: 32px 36px;
    box-shadow: 0 2px 12px rgba(0,0,0,.08);
}

/* Header */
.report-header { text-align: center; margin-bottom: 18px; }
.report-header .gov-label { font-size: 10pt; }
.report-header .province { font-size: 13pt; font-weight: bold; }
.report-header .city { font-size: 10pt; margin-bottom: 12px; }
.report-header .board-name { font-size: 11pt; font-weight: bold; text-transform: uppercase; margin-bottom: 2px; }
.report-header .report-title {
    font-size: 14pt; font-weight: bold; text-transform: uppercase;
    letter-spacing: 1px; margin-top: 10px;
    border-top: 2.5px solid #000; border-bottom: 2.5px solid #000;
    padding: 5px 0;
}

/* Info block */
.info-grid {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 6px 24px; margin: 16px 0; border: 1px solid #999; padding: 10px 14px;
    background: #f9f9f9;
}
.info-row { display: flex; gap: 6px; font-size: 10pt; }
.info-label { font-weight: bold; white-space: nowrap; min-width: 130px; }
.info-val { border-bottom: 1px solid #555; flex: 1; }

/* Rating scale legend */
.scale-legend {
    display: flex; gap: 16px; font-size: 9pt;
    margin-bottom: 10px; padding: 6px 10px;
    background: #f0f0f0; border: 1px solid #ccc;
}
.scale-legend strong { font-size: 9pt; }

/* Criteria table */
.criteria-table { width: 100%; border-collapse: collapse; font-size: 9.5pt; margin-bottom: 14px; }
.criteria-table th, .criteria-table td { border: 1px solid #555; padding: 4px 6px; vertical-align: middle; }
.criteria-table thead tr { background: #1e3a5f; color: #fff; text-align: center; }
.criteria-table thead th { font-size: 9pt; font-weight: bold; }
.criteria-table .th-criteria { text-align: left; min-width: 200px; }

.row-category { background: #dce6f1; }
.row-category td { font-weight: bold; font-size: 9.5pt; padding: 5px 6px; }
.row-criterion td { }
.row-criterion .td-num { text-align: center; width: 24px; color: #444; }
.row-criterion .td-criteria { font-size: 9pt; }
.row-criterion .td-score { text-align: center; font-weight: bold; width: 52px; }
.row-subtotal { background: #e8f4e8; }
.row-subtotal td { font-weight: bold; font-size: 9pt; }
.row-subtotal .td-pct { text-align: center; }

.row-total-score { background: #1e3a5f; color: #fff; }
.row-total-score td { font-weight: bold; font-size: 10.5pt; text-align: center; padding: 6px; }
.row-total-score .td-label { text-align: left; font-size: 10pt; }

.row-psb { background: #092d6b; color: #fff; }
.row-psb td { font-weight: bold; font-size: 11pt; text-align: center; padding: 7px 6px; }
.row-psb .td-label { text-align: left; font-size: 10.5pt; }

.col-avg-h { background: #c6a400 !important; color: #000 !important; }
.col-avg { background: #fffbe6; font-weight: bold; }

/* Remarks */
.remarks-block { margin: 12px 0; font-size: 10pt; }
.remarks-block table { width: 100%; border-collapse: collapse; }
.remarks-block td { border: 1px solid #999; padding: 6px 8px; vertical-align: top; font-size: 9.5pt; }
.remarks-block .rm-name { font-weight: bold; width: 160px; background: #f5f5f5; }

/* Signatories */
.sig-section { margin-top: 24px; }
.sig-section .sig-title { font-weight: bold; font-size: 10pt; margin-bottom: 10px; text-transform: uppercase; border-bottom: 1px solid #999; padding-bottom: 4px; }
.sig-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px 24px; }
.sig-item { text-align: center; }
.sig-name { font-weight: bold; font-size: 10pt; text-transform: uppercase; border-bottom: 1.5px solid #000; padding-bottom: 2px; display: inline-block; min-width: 160px; }
.sig-role { font-size: 9pt; margin-top: 3px; }

.prepared-block { margin-top: 20px; display: flex; justify-content: space-between; font-size: 9.5pt; }
.prepared-block .pb-item { text-align: center; }
.prepared-block .pb-name { font-weight: bold; text-transform: uppercase; border-bottom: 1.5px solid #000; display: inline-block; min-width: 180px; padding-bottom: 2px; }
.prepared-block .pb-role { font-size: 9pt; margin-top: 3px; }

/* No evaluations notice */
.no-eval { text-align: center; padding: 40px; font-style: italic; color: #666; font-size: 11pt; }

@media print {
    .screen-controls { display: none !important; }
    .page-wrap { margin: 0; padding: 0; max-width: 100%; }
    .report-doc { border: none; box-shadow: none; padding: 20px 28px; }
    body { font-size: 10pt; }
    .criteria-table { font-size: 8.5pt; }
    .criteria-table th, .criteria-table td { padding: 3px 5px; }
    @page { size: legal landscape; margin: 12mm 14mm; }
}
</style>
</head>
<body>

{{-- Screen controls --}}
<div class="screen-controls no-print">
    <a href="{{ route('recruitment.hrmpsb.interview.matrix', $applicant->id) }}" class="btn-back">
        &#8592; Back to Matrix
    </a>
    <span class="title-text">
        Interview Evaluation Report &mdash; {{ $applicant->full_name }}
    </span>
    <button onclick="window.print()" class="btn-print">&#128438; Print / Save PDF</button>
</div>

<div class="page-wrap">
<div class="report-doc">

{{-- Letterhead --}}
<div class="report-header">
    <div class="gov-label">Republic of the Philippines</div>
    <div class="province">PROVINCE OF BUKIDNON</div>
    <div class="city">Malaybalay City</div>
    <div class="board-name">Human Resource Merit Promotion and Selection Board</div>
    <div class="report-title">HRMPSB Interview Evaluation Report</div>
</div>

{{-- Applicant / Position Info --}}
<div class="info-grid">
    <div class="info-row">
        <span class="info-label">Position Applied For:</span>
        <span class="info-val">{{ strtoupper($applicant->position_applied ?? '—') }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Office/Department:</span>
        <span class="info-val">{{ strtoupper($applicant->office ?? '—') }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Applicant's Name:</span>
        <span class="info-val">{{ strtoupper($applicant->full_name) }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Date of Evaluation:</span>
        <span class="info-val">
            @if($evaluations->isNotEmpty())
                {{ $evaluations->max('created_at')->format('F d, Y') }}
            @else
                —
            @endif
        </span>
    </div>
    <div class="info-row">
        <span class="info-label">Item No.:</span>
        <span class="info-val">{{ $applicant->item_no ?? '—' }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">No. of Panel Members:</span>
        <span class="info-val">{{ $evaluations->count() }}</span>
    </div>
</div>

{{-- Rating scale legend --}}
<div class="scale-legend">
    <strong>Rating Scale:</strong>
    <span><strong>4</strong> – Outstanding</span>
    <span><strong>3</strong> – Very Satisfactory</span>
    <span><strong>2</strong> – Satisfactory</span>
    <span><strong>1</strong> – Poor</span>
</div>

@if($evaluations->isEmpty())
    <div class="no-eval">No panel members have submitted evaluations for this applicant yet.</div>
@else

@php
$members = $evaluations->map(function($eval) {
    return [
        'name'     => $eval->rater?->name ?? $eval->panelMember?->name ?? $eval->rater_name ?? 'Unknown',
        'position' => $eval->rater?->roles->first()?->name ?? ($eval->panelMember?->position ?: 'Panel Member'),
        'ratings'  => $eval->ratings ?? [],
        'total'    => (float)$eval->total_score,
        'remarks'  => $eval->remarks,
    ];
})->values();

$n = $members->count();

$catScore = function($ratings, $catKey) use ($categories) {
    $cfg     = $categories[$catKey];
    $sum     = collect($cfg['items'])->sum(fn($k) => (int)($ratings[$k] ?? 0));
    $maxPoss = count($cfg['items']) * 4;
    return $maxPoss > 0 ? ($sum / $maxPoss) * $cfg['weight'] : 0;
};

$criterionNum = 0;
@endphp

{{-- Criteria matrix table --}}
<table class="criteria-table">
    <thead>
        <tr>
            <th style="width:22px;">#</th>
            <th class="th-criteria">EVALUATION CRITERIA</th>
            @foreach($members as $idx => $m)
                <th style="min-width:54px;">
                    {{ $m['name'] }}<br>
                    <span style="font-size:8pt;font-weight:400;opacity:.8;">{{ $m['position'] }}</span>
                </th>
            @endforeach
            <th class="col-avg-h" style="min-width:54px;">AVERAGE</th>
        </tr>
    </thead>
    <tbody>
        @foreach($categories as $catKey => $cat)
            {{-- Category header --}}
            <tr class="row-category">
                <td colspan="{{ $n + 2 }}">
                    {{ $cat['label'] }}
                    <span style="font-size:8.5pt;font-weight:400;margin-left:8px;">({{ $cat['weight'] }}% weight)</span>
                </td>
            </tr>

            {{-- Individual criteria rows --}}
            @foreach($cat['items'] as $criterionKey)
                @php
                    $criterionNum++;
                    $label    = $criteriaLabels[$catKey][$criterionKey] ?? $criterionKey;
                    $avgRating = $n > 0
                        ? round($members->avg(fn($m) => (int)($m['ratings'][$criterionKey] ?? 0)), 2)
                        : 0;
                @endphp
                <tr class="row-criterion">
                    <td class="td-num">{{ $criterionNum }}</td>
                    <td class="td-criteria">{{ $label }}</td>
                    @foreach($members as $m)
                        @php $v = (int)($m['ratings'][$criterionKey] ?? 0); @endphp
                        <td class="td-score">{{ $v ?: '—' }}</td>
                    @endforeach
                    <td class="td-score col-avg">{{ $avgRating > 0 ? number_format($avgRating, 2) : '—' }}</td>
                </tr>
            @endforeach

            {{-- Category weighted subtotal --}}
            @php $avgCatScore = $n > 0 ? $members->avg(fn($m) => $catScore($m['ratings'], $catKey)) : 0; @endphp
            <tr class="row-subtotal">
                <td colspan="2" style="text-align:right;padding-right:10px;">
                    Weighted Category Score ({{ $cat['weight'] }}%)
                </td>
                @foreach($members as $m)
                    <td class="td-pct">{{ number_format($catScore($m['ratings'], $catKey), 2) }}%</td>
                @endforeach
                <td class="td-pct col-avg">{{ number_format($avgCatScore, 2) }}%</td>
            </tr>
        @endforeach

        {{-- Total interview score row --}}
        <tr class="row-total-score">
            <td colspan="2" class="td-label">TOTAL INTERVIEW SCORE (out of 100%)</td>
            @foreach($members as $m)
                <td>{{ number_format($m['total'], 2) }}%</td>
            @endforeach
            <td class="col-avg" style="background:#ffd700;color:#000;font-size:11pt;">
                {{ number_format($averageScore, 2) }}%
            </td>
        </tr>

        {{-- PSB Interview Rating (50-pt equivalent) --}}
        <tr class="row-psb">
            <td colspan="2" class="td-label">
                PSB INTERVIEW RATING
                <span style="font-size:9pt;font-weight:400;opacity:.8;">(50-point equivalent = Total × 0.50)</span>
            </td>
            @foreach($members as $m)
                <td>{{ number_format($m['total'] * 0.50, 2) }}</td>
            @endforeach
            <td style="background:#ffd700;color:#000;font-size:13pt;font-weight:900;">
                {{ number_format($psbRating, 2) }}
            </td>
        </tr>
    </tbody>
</table>

{{-- Remarks --}}
@if($members->filter(fn($m) => !empty($m['remarks']))->isNotEmpty())
<div class="remarks-block">
    <strong style="font-size:9.5pt;">REMARKS FROM PANEL MEMBERS:</strong>
    <table style="margin-top:4px;">
        @foreach($members as $m)
            @if(!empty($m['remarks']))
                <tr>
                    <td class="rm-name">{{ $m['name'] }}</td>
                    <td>{{ $m['remarks'] }}</td>
                </tr>
            @endif
        @endforeach
    </table>
</div>
@endif

@endif {{-- end if evaluations not empty --}}

{{-- Signatories / Panel Members --}}
@if($evaluations->isNotEmpty())
<div class="sig-section">
    <div class="sig-title">HRMPSB Panel Members</div>
    @php
        $memberChunks = $members->chunk(3);
    @endphp
    @foreach($memberChunks as $chunk)
    <div class="sig-grid" style="margin-bottom:20px;">
        @foreach($chunk as $m)
        <div class="sig-item">
            <div><span class="sig-name">{{ strtoupper($m['name']) }}</span></div>
            <div class="sig-role">{{ $m['position'] }}</div>
            <div class="sig-role" style="margin-top:2px;color:#555;">Score: <strong>{{ number_format($m['total'], 2) }}%</strong> → {{ number_format($m['total'] * 0.50, 2) }} pts</div>
        </div>
        @endforeach
    </div>
    @endforeach
</div>
@endif

{{-- Prepared / Noted by --}}
<div class="prepared-block" style="margin-top:30px;">
    <div class="pb-item">
        <div><span class="pb-name">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></div>
        <div class="pb-role">Prepared by / HR Officer</div>
    </div>
    <div class="pb-item">
        @php $chair = $signatories->where('role', 'chairman')->first(); @endphp
        <div><span class="pb-name">{{ $chair ? strtoupper($chair->name) : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' }}</span></div>
        <div class="pb-role">{{ $chair ? $chair->title : 'HRMPSB Chairperson' }}</div>
        <div class="pb-role">HRMPSB Chairperson</div>
    </div>
</div>

<div style="margin-top:20px;font-size:8.5pt;color:#555;text-align:center;border-top:1px solid #ccc;padding-top:6px;">
    Generated by PHRMO HRMDS &mdash; {{ now()->format('F d, Y g:i A') }}
</div>

</div>{{-- .report-doc --}}
</div>{{-- .page-wrap --}}
</body>
</html>
