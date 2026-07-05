@extends('panel.layout')
@section('title', 'TWG Scoring')

@section('content')
<div class="panel-card">
    <div class="panel-card-header">
        <i class="bi bi-star-fill text-warning"></i>
        Score HRMPSB (TWG)
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
                        <div class="text-muted" style="font-size:11px;font-weight:700;text-transform:uppercase;">Scored</div>
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
    <form action="{{ route('panel.twg.form') }}" method="GET" class="mb-4">
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
        <x-applicant-profile :applicant="$applicant">
            <x-slot name="actions">
                <button type="button" onclick="document.getElementById('floatingScorePanel').style.display='flex'"
                   class="btn btn-sm btn-warning fw-bold text-dark">
                    <i class="bi bi-star-fill me-1"></i> Open Scoring Panel
                </button>
            </x-slot>
        </x-applicant-profile>

        <x-twg-score-panel :applicant="$applicant" :score="$score" actionUrl="{{ route('panel.twg.store') }}" />
        
        {{-- Auto open floating score panel since they loaded an applicant to score --}}
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const panel = document.getElementById('floatingScorePanel');
                if(panel) {
                    panel.style.display = 'flex';
                }
            });
        </script>
    @else
        <div class="text-center py-5 text-muted">
            <i class="bi bi-star" style="font-size:3rem;"></i>
            <p class="mt-3">Select an applicant above to begin the TWG scoring.</p>
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

    if (officeSelect && positionSelect && applicantSelect) {
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
    }
});
</script>
@endsection
