@extends('panel.layout')
@section('title', 'Interview Evaluation')

@section('content')
<div class="panel-card">
    <div class="panel-card-header">
        <i class="bi bi-clipboard-check-fill text-primary"></i>
        Interview Evaluation Form
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0" style="border-radius:10px;border-left:4px solid #0dcaf0!important;box-shadow:0 1px 4px rgba(0,0,0,.07);">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="bg-info bg-opacity-10 rounded p-2 text-info"><i class="bi bi-people-fill fs-5"></i></div>
                    <div>
                        <div class="text-muted" style="font-size:11px;font-weight:700;text-transform:uppercase;">Total Applicants</div>
                        <div class="fw-bold fs-5">{{ $stats['total'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0" style="border-radius:10px;border-left:4px solid #198754!important;box-shadow:0 1px 4px rgba(0,0,0,.07);">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="bg-success bg-opacity-10 rounded p-2 text-success"><i class="bi bi-check-circle-fill fs-5"></i></div>
                    <div>
                        <div class="text-muted" style="font-size:11px;font-weight:700;text-transform:uppercase;">Evaluated by You</div>
                        <div class="fw-bold fs-5">{{ $stats['evaluated'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0" style="border-radius:10px;border-left:4px solid #ffc107!important;box-shadow:0 1px 4px rgba(0,0,0,.07);">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="bg-warning bg-opacity-10 rounded p-2 text-warning"><i class="bi bi-hourglass-split fs-5"></i></div>
                    <div>
                        <div class="text-muted" style="font-size:11px;font-weight:700;text-transform:uppercase;">Pending</div>
                        <div class="fw-bold fs-5">{{ $stats['pending'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Applicant Selection --}}
    <form action="{{ route('panel.interview.form') }}" method="GET" class="mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Filter by Office</label>
                <select class="form-select form-select-sm" id="office_select">
                    <option value="">— All Offices —</option>
                    @foreach($offices as $office)
                        <option value="{{ $office }}">{{ $office }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Filter by Position</label>
                <select class="form-select form-select-sm" id="position_select">
                    <option value="">— All Positions —</option>
                    @foreach($positions as $pos)
                        <option value="{{ $pos }}">{{ $pos }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Applicant <span class="text-danger">*</span></label>
                <select name="applicant_id" id="applicant_select" class="form-select form-select-sm" required>
                    <option value="">— Select Applicant —</option>
                    @foreach($applicants as $app)
                        <option value="{{ $app->id }}"
                                data-office="{{ $app->office }}"
                                data-position="{{ $app->position_applied }}"
                                {{ $applicant && $applicant->id == $app->id ? 'selected' : '' }}>
                            {{ $app->last_name }}, {{ $app->first_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-save w-100">
                    <i class="bi bi-search me-1"></i> Load
                </button>
            </div>
        </div>
    </form>

    @if($applicant)
        @php $er = $existingEvaluation?->ratings ?? []; @endphp

        {{-- Applicant header --}}
        <div class="mb-3 p-3 rounded-3" style="background:linear-gradient(135deg,#1e3a5f,#2d6a4f);color:#fff;">
            <div class="d-flex align-items-center gap-3">
                <div style="width:50px;height:50px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:800;">
                    {{ strtoupper(substr($applicant->first_name, 0, 1)) }}
                </div>
                <div>
                    <div style="font-size:18px;font-weight:800;">{{ $applicant->full_name }}</div>
                    <div style="font-size:12px;opacity:.75;">
                        {{ $applicant->position_applied }} &nbsp;·&nbsp; {{ $applicant->office }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Already-scored banner --}}
        @if($existingEvaluation)
            <div class="alert alert-info d-flex align-items-center gap-2 mb-3" style="font-size:13px;">
                <i class="bi bi-info-circle-fill"></i>
                <div>
                    You have already evaluated this applicant
                    (saved {{ $existingEvaluation->updated_at->diffForHumans() }},
                    score: <strong>{{ number_format($existingEvaluation->total_score, 2) }}%</strong>).
                    Submitting again will <strong>update</strong> your evaluation.
                </div>
            </div>
        @endif

        {{-- Evaluation Form --}}
        <form action="{{ route('panel.interview.store') }}" method="POST" id="evalForm">
            @csrf
            <input type="hidden" name="applicant_id" value="{{ $applicant->id }}">

            {{-- I. Personal Appearance --}}
            <div class="card border-0 shadow-sm mb-3 border-start border-primary border-3">
                <div class="card-header bg-white py-2">
                    <strong class="text-primary" style="font-size:13px;">I. Personal Appearance ({{ $weights['appearance'] }}%)</strong>
                </div>
                <div class="card-body py-2">
                    <div class="row align-items-center">
                        <div class="col-md-8 small">1. The candidate presents himself/herself in a good grooming and tidy appearance.</div>
                        <div class="col-md-4">
                            <select name="ratings[appearance_1]" class="form-select form-select-sm" required>
                                <option value="">Select Rating</option>
                                @foreach([4=>'4 - Outstanding',3=>'3 - Very Satisfactory',2=>'2 - Satisfactory',1=>'1 - Poor'] as $val=>$lbl)
                                    <option value="{{ $val }}" {{ (int)($er['appearance_1'] ?? 0) == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- II. Knowledge --}}
            <div class="card border-0 shadow-sm mb-3 border-start border-success border-3">
                <div class="card-header bg-white py-2">
                    <strong class="text-success" style="font-size:13px;">II. Knowledge on the Job &amp; Org ({{ $weights['knowledge'] }}%)</strong>
                </div>
                <div class="card-body py-2">
                    @php $knowledges = [
                        '1' => 'The candidate is knowledgeable of the functions of the vacant position.',
                        '2' => 'The candidate is knowledgeable of the organizational structure of the department/division where the vacancy exists.',
                        '3' => 'The candidate is knowledgeable of the mission and vision of the department/office where the vacancy exists.',
                        '4' => 'The candidate can explain how the functions of the vacant position translate to the achievement of the mission and vision.',
                    ]; @endphp
                    @foreach($knowledges as $i => $desc)
                        <div class="row align-items-center mb-2">
                            <div class="col-md-8 small">{{ $i }}. {{ $desc }}</div>
                            <div class="col-md-4">
                                <select name="ratings[knowledge_{{ $i }}]" class="form-select form-select-sm" required>
                                    <option value="">Select</option>
                                    @foreach([4=>'4 - Outstanding',3=>'3 - Very Satisfactory',2=>'2 - Satisfactory',1=>'1 - Poor'] as $val=>$lbl)
                                        <option value="{{ $val }}" {{ (int)($er['knowledge_'.$i] ?? 0) == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- III. Communication --}}
            <div class="card border-0 shadow-sm mb-3 border-start border-info border-3">
                <div class="card-header bg-white py-2">
                    <strong class="text-info" style="font-size:13px;">III. Communication / Interpersonal ({{ $weights['communication'] }}%)</strong>
                </div>
                <div class="card-body py-2">
                    @php $comms = [
                        '1' => 'Listens to questions attentively and actively.',
                        '2' => 'Answers the questions or expresses ideas clearly, concisely and logically.',
                        '3' => 'Demonstrates confidence by displaying positive body language and maintaining eye contact.',
                    ]; @endphp
                    @foreach($comms as $i => $desc)
                        <div class="row align-items-center mb-2">
                            <div class="col-md-8 small">{{ $i }}. {{ $desc }}</div>
                            <div class="col-md-4">
                                <select name="ratings[comm_{{ $i }}]" class="form-select form-select-sm" required>
                                    <option value="">Select</option>
                                    @foreach([4=>'4 - Outstanding',3=>'3 - Very Satisfactory',2=>'2 - Satisfactory',1=>'1 - Poor'] as $val=>$lbl)
                                        <option value="{{ $val }}" {{ (int)($er['comm_'.$i] ?? 0) == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- IV. Other Criteria --}}
            <div class="card border-0 shadow-sm mb-3 border-start border-warning border-3">
                <div class="card-header bg-white py-2">
                    <strong class="text-warning" style="font-size:13px;">IV. Other Evaluation Criteria ({{ $weights['other'] }}%)</strong>
                </div>
                <div class="card-body py-2">
                    @php $others = [
                        '1'  => '<strong>JOB COMMITMENT</strong>: Responsibility towards the mission and goals of an organization.',
                        '2'  => '<strong>COMMITMENT</strong>: Psychological attachment to the organization.',
                        '3'  => '<strong>POTENTIAL</strong>: Capability to perform duties of the position and higher ones.',
                        '4'  => '<strong>SINCERITY</strong>: Assessment of honesty, expression of valid/useful opinion backed by evidence.',
                        '5'  => '<strong>PROFESSIONALISM</strong>: Demonstrates consideration, respect, loyalty, and exceeds expectations.',
                        '6'  => '<strong>INITIATIVE</strong>: Eagerness to start actions without being told to start them.',
                        '7'  => '<strong>TEAMWORK</strong>: Active involvement to a team resulting in goal achievement.',
                        '8'  => '<strong>TIME MANAGEMENT</strong>: Act of planning time spent on activities to increase productivity.',
                        '9'  => '<strong>CUSTOMER SERVICE</strong>: Act of taking care of customers needs by providing professional assistance.',
                        '10' => '<strong>JOB SATISFACTION</strong>: Contentment of current job and the sense of accomplishment.',
                    ]; @endphp
                    @foreach($others as $i => $desc)
                        <div class="row align-items-center mb-2">
                            <div class="col-md-8 small">{{ $i }}. {!! $desc !!}</div>
                            <div class="col-md-4">
                                <select name="ratings[other_{{ $i }}]" class="form-select form-select-sm" required>
                                    <option value="">Select</option>
                                    @foreach([4=>'4 - Outstanding',3=>'3 - Very Satisfactory',2=>'2 - Satisfactory',1=>'1 - Poor'] as $val=>$lbl)
                                        <option value="{{ $val }}" {{ (int)($er['other_'.$i] ?? 0) == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Remarks --}}
            <div class="mb-3">
                <label class="form-label">Remarks <span class="text-muted">(optional)</span></label>
                <textarea name="remarks" class="form-control form-control-sm" rows="3"
                          placeholder="Additional observations...">{{ $existingEvaluation?->remarks ?? '' }}</textarea>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('panel.interview.form') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
                <button type="submit" class="btn btn-save btn-sm px-4">
                    <i class="bi bi-save me-1"></i> Save Evaluation
                </button>
            </div>
        </form>
    @else
        <div class="text-center py-5 text-muted">
            <i class="bi bi-person-lines-fill" style="font-size:3rem;"></i>
            <p class="mt-3">Select an applicant above to begin the interview evaluation.</p>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const officeSelect    = document.getElementById('office_select');
    const positionSelect  = document.getElementById('position_select');
    const applicantSelect = document.getElementById('applicant_select');

    const appOpts  = Array.from(applicantSelect.options).filter(o => o.value);
    const posOpts  = Array.from(positionSelect.options).filter(o => o.value);
    const offToPos = {};
    appOpts.forEach(o => {
        const off = o.dataset.office || '', pos = o.dataset.position || '';
        if (off) { offToPos[off] = offToPos[off] || new Set(); offToPos[off].add(pos); }
    });

    function updatePositions() {
        const office = officeSelect.value;
        const valid  = office ? (offToPos[office] || new Set()) : null;
        posOpts.forEach(o => { o.style.display = (!valid || valid.has(o.value)) ? '' : 'none'; });
        const selPos = positionSelect.options[positionSelect.selectedIndex];
        if (selPos && selPos.value && selPos.style.display === 'none') positionSelect.value = '';
    }

    function updateApplicants() {
        const office   = officeSelect.value;
        const position = positionSelect.value;
        appOpts.forEach(o => {
            const mO = !office   || o.dataset.office    === office;
            const mP = !position || o.dataset.position  === position;
            o.style.display = (mO && mP) ? '' : 'none';
        });
        const sel = applicantSelect.options[applicantSelect.selectedIndex];
        if (sel && sel.value && sel.style.display === 'none') applicantSelect.value = '';
    }

    officeSelect.addEventListener('change', function() { updatePositions(); updateApplicants(); });
    positionSelect.addEventListener('change', updateApplicants);
    updatePositions();
    updateApplicants();
});
</script>
@endsection
