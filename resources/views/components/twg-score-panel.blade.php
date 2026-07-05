@props(['applicant', 'score', 'actionUrl', 'psbFromPanel' => null, 'panelCount' => 0])

@php
use App\Models\HrmpsbRatingScale;
use App\Models\Setting;
use App\Support\BlindScoringId;

$sc = HrmpsbRatingScale::orderBy('points', 'desc')->get();

$ipcr_all    = $sc->where('criterion','ipcr')->where('position_category','all')->values();
$ipcr_noelig = $sc->where('criterion','ipcr')->where('position_category','no_eligibility')->values();
$awards      = $sc->where('criterion','awards')->sortByDesc('points')->values();
$edu_first   = $sc->where('criterion','education')->where('level','first_level')->values();
$edu_second  = $sc->where('criterion','education')->where('level','second_level')->values();
$edu_noelig  = $sc->where('criterion','education')->where('position_category','no_eligibility')->values();
$experience  = $sc->where('criterion','experience')->values();
$training    = $sc->where('criterion','training')->values();
$los         = $sc->where('criterion','length_of_service')->values();
$susp        = $sc->where('criterion','demerits_suspension')->sortBy('points')->values();
$dRepr       = $sc->where('criterion','demerits_reprimand')->first();
$dSW         = $sc->where('criterion','demerits_stern_warning')->first();
$dWmElig     = $sc->where('criterion','demerits_warning_memo')->where('position_category','eligibility')->first();
$dWmNoelig   = $sc->where('criterion','demerits_warning_memo')->where('position_category','no_eligibility')->first();

$sv = $score ?? null;
$exCat  = old('position_category', $sv->position_category ?? 'eligibility');
$exEdu  = old('education_level',   $sv->education_level   ?? 'first_level');

$twgMax = [
    'psb'    => (float) Setting::getVal('twg_max_psb_interview', 50),
    'ipcr'   => (float) Setting::getVal('twg_max_ipcr', 10),
    'awards' => (float) Setting::getVal('twg_max_awards', 5),
    'edu_e'  => (float) Setting::getVal('twg_max_education_eligibility', 15),
    'edu_n'  => (float) Setting::getVal('twg_max_education_no_eligibility', 20),
    'exp'    => (float) Setting::getVal('twg_max_experience', 10),
    'trn'    => (float) Setting::getVal('twg_max_training', 10),
    'los'    => (float) Setting::getVal('twg_max_length_of_service', 15),
];

// Helper: check if a scale option matches the saved score
$matchScore = fn($saved, $rowPts) => $saved !== null && $saved !== '' && abs((float)$saved - (float)$rowPts) < 0.001;
@endphp

{{-- ============================================================
     FLOATING PANEL
     ============================================================ --}}
<div id="floatingScorePanel"
     style="display:none; position:fixed; z-index:9999; top:5%; right:2%; width:580px; max-width:96vw;
            background:#fff; border-radius:12px; box-shadow:0 10px 35px rgba(0,0,0,.30);
            overflow:hidden; resize:both; min-width:340px; min-height:300px;
            border:1px solid #cbd5e1; flex-direction:column;">

    {{-- Header --}}
    <div id="floatingScoreHeader" class="bg-warning"
         style="padding:11px 16px; cursor:move; display:flex; justify-content:space-between; align-items:center; flex-shrink:0;">
        <h5 class="m-0 fw-bold text-dark" style="font-size:15px;">
            <i class="bi bi-star-fill me-1"></i>TWG Rating — {{ BlindScoringId::forApplicant($applicant) }}
        </h5>
        <button type="button" class="btn-close"
                onclick="document.getElementById('floatingScorePanel').style.display='none'"></button>
    </div>

    {{-- Scrollable body --}}
    <div style="padding:14px 18px; flex-grow:1; overflow-y:auto; max-height:calc(90vh - 120px);">
        <form action="{{ $actionUrl }}" method="POST" id="hrmpsbForm">
            @csrf
            <input type="hidden" name="applicant_id" value="{{ $applicant->id }}">

            {{-- ── Position Category ── --}}
            <div class="mb-3">
                <label class="form-label fw-bold mb-1" style="font-size:12px;text-transform:uppercase;letter-spacing:.5px;">Position Category</label>
                <div class="d-flex gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="position_category" id="cat_elig"
                               value="eligibility" {{ $exCat === 'eligibility' ? 'checked' : '' }} onchange="toggleCategory()">
                        <label class="form-check-label fw-semibold" for="cat_elig">With Eligibility</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="position_category" id="cat_noelig"
                               value="no_eligibility" {{ $exCat === 'no_eligibility' ? 'checked' : '' }} onchange="toggleCategory()">
                        <label class="form-check-label fw-semibold" for="cat_noelig">No Eligibility</label>
                    </div>
                </div>
            </div>

            <hr class="my-2">

            {{-- ── PSB Interview Score (auto-fetched from panel evaluations) ── --}}
            <div class="mb-3">
                <label class="form-label mb-1 d-flex justify-content-between" style="font-size:12px;">
                    <span class="fw-bold">PSB Interview Score</span>
                    <span class="text-muted">max {{ $twgMax['psb'] }} pts</span>
                </label>
                @if($psbFromPanel !== null)
                    <div class="input-group input-group-sm">
                        <input type="number" step="0.01" name="psb_interview_score" id="psb_score"
                               class="form-control score-num bg-light fw-bold"
                               value="{{ $psbFromPanel }}" readonly
                               oninput="calculateTotal()">
                        <span class="input-group-text text-success bg-success bg-opacity-10">
                            <i class="bi bi-check-circle-fill me-1"></i>Auto
                        </span>
                    </div>
                    <div class="form-text text-success" style="font-size:10px;">
                        <i class="bi bi-shield-check me-1"></i>Computed from {{ $panelCount }} panel evaluation(s) — panel avg × 0.50 = {{ $psbFromPanel }}
                    </div>
                @else
                    <input type="number" step="0.01" name="psb_interview_score" id="psb_score"
                           class="form-control form-control-sm score-num"
                           value="" disabled placeholder="No panel evaluations yet"
                           oninput="calculateTotal()">
                    <div class="form-text text-warning" style="font-size:10px;">
                        <i class="bi bi-exclamation-triangle me-1"></i>Panel has not submitted evaluations yet. This field will be auto-filled once available.
                    </div>
                @endif
            </div>

            {{-- ── IPCR Rating ── --}}
            <div class="mb-3">
                <label class="form-label mb-1 d-flex justify-content-between" style="font-size:12px;">
                    <span class="fw-bold">IPCR Rating</span>
                    <span class="text-muted">max {{ $twgMax['ipcr'] }} pts</span>
                </label>
                <select name="ipcr_score" class="form-select form-select-sm score-dropdown" onchange="calculateTotal()">
                    <option value="">— Select IPCR Rating —</option>
                    @foreach($ipcr_all as $row)
                        <option value="{{ $row->points }}"
                                {{ $matchScore($sv->ipcr_score ?? null, $row->points) ? 'selected' : '' }}>
                            {{ $row->condition_name }} — {{ number_format($row->points, 2) }} pts
                        </option>
                    @endforeach
                    @foreach($ipcr_noelig as $row)
                        <option value="{{ $row->points }}" class="no-elig-ipcr-opt"
                                {{ $matchScore($sv->ipcr_score ?? null, $row->points) ? 'selected' : '' }}>
                            {{ $row->condition_name }} — {{ number_format($row->points, 2) }} pts (No Elig. only)
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- ── Awards & Recognition ── --}}
            <div class="mb-3">
                <label class="form-label mb-1 d-flex justify-content-between" style="font-size:12px;">
                    <span class="fw-bold">Awards &amp; Recognition</span>
                    <span class="text-muted">max {{ $twgMax['awards'] }} pts</span>
                </label>
                <select name="awards_score" class="form-select form-select-sm score-dropdown" onchange="calculateTotal()">
                    <option value="">— Select Highest Award —</option>
                    @foreach($awards as $row)
                        <option value="{{ $row->points }}"
                                {{ $matchScore($sv->awards_score ?? null, $row->points) ? 'selected' : '' }}>
                            {{ $row->condition_name }} — {{ number_format($row->points, 2) }} pts
                        </option>
                    @endforeach
                </select>
                <div class="form-text" style="font-size:10px;">Highest applicable award prevails.</div>
            </div>

            {{-- ── Education ── --}}
            <div class="mb-3">
                <label class="form-label mb-1 d-flex justify-content-between" style="font-size:12px;">
                    <span class="fw-bold">Education</span>
                    <span class="text-muted edu-max-label">max
                        {{ $exCat === 'no_eligibility' ? $twgMax['edu_n'] : $twgMax['edu_e'] }} pts</span>
                </label>

                {{-- Level toggle — eligibility only --}}
                <div id="edu_level_toggle" class="mb-2 {{ $exCat !== 'eligibility' ? 'd-none' : '' }}">
                    <div class="d-flex gap-3" style="font-size:12px;">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="education_level" id="edu_first"
                                   value="first_level" {{ $exEdu === 'first_level' ? 'checked' : '' }}
                                   onchange="toggleEduLevel()">
                            <label class="form-check-label" for="edu_first">First Level <small class="text-muted">(Admin/Clerical)</small></label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="education_level" id="edu_second"
                                   value="second_level" {{ $exEdu === 'second_level' ? 'checked' : '' }}
                                   onchange="toggleEduLevel()">
                            <label class="form-check-label" for="edu_second">Second Level <small class="text-muted">(SG 11+)</small></label>
                        </div>
                    </div>
                </div>

                {{-- Education: First Level --}}
                <select id="edu_select_first" onchange="syncEduScore(this); calculateTotal();"
                        class="form-select form-select-sm edu-select"
                        {{ ($exCat !== 'eligibility' || $exEdu !== 'first_level') ? 'disabled' : '' }}
                        style="{{ ($exCat !== 'eligibility' || $exEdu !== 'first_level') ? 'display:none;' : '' }}">
                    <option value="">— Select Education Attainment —</option>
                    @foreach($edu_first as $row)
                        <option value="{{ $row->points }}"
                                {{ ($exCat==='eligibility' && $exEdu==='first_level' && $matchScore($sv->education_score ?? null, $row->points)) ? 'selected' : '' }}>
                            {{ $row->condition_name }} — {{ number_format($row->points, 2) }} pts
                        </option>
                    @endforeach
                </select>

                {{-- Education: Second Level --}}
                <select id="edu_select_second" onchange="syncEduScore(this); calculateTotal();"
                        class="form-select form-select-sm edu-select"
                        {{ ($exCat !== 'eligibility' || $exEdu !== 'second_level') ? 'disabled' : '' }}
                        style="{{ ($exCat !== 'eligibility' || $exEdu !== 'second_level') ? 'display:none;' : '' }}">
                    <option value="">— Select Education Attainment —</option>
                    @foreach($edu_second as $row)
                        <option value="{{ $row->points }}"
                                {{ ($exCat==='eligibility' && $exEdu==='second_level' && $matchScore($sv->education_score ?? null, $row->points)) ? 'selected' : '' }}>
                            {{ $row->condition_name }} — {{ number_format($row->points, 2) }} pts
                        </option>
                    @endforeach
                </select>

                {{-- Education: No Eligibility --}}
                <select id="edu_select_noelig" onchange="syncEduScore(this); calculateTotal();"
                        class="form-select form-select-sm edu-select"
                        {{ $exCat !== 'no_eligibility' ? 'disabled' : '' }}
                        style="{{ $exCat !== 'no_eligibility' ? 'display:none;' : '' }}">
                    <option value="">— Select Education Attainment —</option>
                    @foreach($edu_noelig as $row)
                        <option value="{{ $row->points }}"
                                {{ ($exCat==='no_eligibility' && $matchScore($sv->education_score ?? null, $row->points)) ? 'selected' : '' }}>
                            {{ $row->condition_name }} — {{ number_format($row->points, 2) }} pts
                        </option>
                    @endforeach
                </select>

                {{-- Hidden submitted field --}}
                <input type="hidden" name="education_score" id="education_score_hidden"
                       value="{{ old('education_score', $sv->education_score ?? '') }}">
            </div>

            {{-- ── Relevant Experience — eligibility only ── --}}
            <div class="mb-3 eligibility-only" {{ $exCat !== 'eligibility' ? 'style="display:none;"' : '' }}>
                <label class="form-label mb-1 d-flex justify-content-between" style="font-size:12px;">
                    <span class="fw-bold">Relevant Experience</span>
                    <span class="text-muted">max {{ $twgMax['exp'] }} pts</span>
                </label>
                <select name="experience_score" id="experience_score"
                        class="form-select form-select-sm score-dropdown" onchange="calculateTotal()"
                        {{ $exCat !== 'eligibility' ? 'disabled' : '' }}>
                    <option value="">— Select Experience Range —</option>
                    @foreach($experience as $row)
                        <option value="{{ $row->points }}"
                                {{ $matchScore($sv->experience_score ?? null, $row->points) ? 'selected' : '' }}>
                            {{ $row->condition_name }} — {{ number_format($row->points, 2) }} pts
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- ── Relevant Training — eligibility only ── --}}
            <div class="mb-3 eligibility-only" {{ $exCat !== 'eligibility' ? 'style="display:none;"' : '' }}>
                <label class="form-label mb-1 d-flex justify-content-between" style="font-size:12px;">
                    <span class="fw-bold">Relevant Training (Hours, last 5 yrs)</span>
                    <span class="text-muted">max {{ $twgMax['trn'] }} pts</span>
                </label>
                <select name="training_score" id="training_score"
                        class="form-select form-select-sm score-dropdown" onchange="calculateTotal()"
                        {{ $exCat !== 'eligibility' ? 'disabled' : '' }}>
                    <option value="">— Select Training Hours —</option>
                    @foreach($training as $row)
                        <option value="{{ $row->points }}"
                                {{ $matchScore($sv->training_score ?? null, $row->points) ? 'selected' : '' }}>
                            {{ $row->condition_name }} — {{ number_format($row->points, 2) }} pts
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- ── Length of Service — no eligibility only ── --}}
            <div class="mb-3 no-eligibility-only" {{ $exCat !== 'no_eligibility' ? 'style="display:none;"' : '' }}>
                <label class="form-label mb-1 d-flex justify-content-between" style="font-size:12px;">
                    <span class="fw-bold">Length of Service in PGB <small class="text-muted">(as JO/Casual)</small></span>
                    <span class="text-muted">max {{ $twgMax['los'] }} pts</span>
                </label>
                <select name="length_of_service_score" id="los_score"
                        class="form-select form-select-sm score-dropdown" onchange="calculateTotal()"
                        {{ $exCat !== 'no_eligibility' ? 'disabled' : '' }}>
                    <option value="">— Select Length of Service —</option>
                    @foreach($los as $row)
                        <option value="{{ $row->points }}"
                                {{ $matchScore($sv->length_of_service_score ?? null, $row->points) ? 'selected' : '' }}>
                            {{ $row->condition_name }} — {{ number_format($row->points, 2) }} pts
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- ── DEMERITS ── --}}
            <div class="mb-3 p-2 rounded" style="background:#fff5f5;border:1px solid #fecaca;">
                <div class="fw-bold text-danger mb-2" style="font-size:12px;text-transform:uppercase;letter-spacing:.5px;">
                    <i class="bi bi-dash-circle-fill me-1"></i>Demerits (Deductions)
                </div>

                {{-- Suspension --}}
                <div class="mb-2">
                    <label style="font-size:11px;font-weight:600;">Suspension <span class="text-muted fw-normal">(within 5 years)</span></label>
                    <select id="demerit_suspension" class="form-select form-select-sm" onchange="updateDemerit()">
                        @foreach($susp as $row)
                            <option value="{{ $row->points }}">{{ $row->condition_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Reprimand --}}
                <div class="mb-2 row g-1 align-items-center">
                    <div class="col-auto">
                        <label style="font-size:11px;font-weight:600;">Reprimands <span class="text-muted fw-normal">(within 3 yrs, × 0.75, max 2.25)</span></label>
                    </div>
                    <div class="col-3">
                        <input type="number" min="0" max="3" value="0" id="repr_count"
                               class="form-control form-control-sm" onchange="updateDemerit()" oninput="updateDemerit()">
                    </div>
                    <div class="col-auto text-danger fw-bold" id="repr_pts" style="font-size:11px;">= 0.00</div>
                </div>

                {{-- Stern Warning --}}
                <div class="mb-2 row g-1 align-items-center">
                    <div class="col-auto">
                        <label style="font-size:11px;font-weight:600;">Stern Warnings <span class="text-muted fw-normal">(within 2 yrs, × 0.50, max 1.50)</span></label>
                    </div>
                    <div class="col-3">
                        <input type="number" min="0" max="3" value="0" id="sw_count"
                               class="form-control form-control-sm" onchange="updateDemerit()" oninput="updateDemerit()">
                    </div>
                    <div class="col-auto text-danger fw-bold" id="sw_pts" style="font-size:11px;">= 0.00</div>
                </div>

                {{-- Warning/Memo --}}
                <div class="mb-2 row g-1 align-items-center">
                    <div class="col-auto">
                        <label id="wm_label" style="font-size:11px;font-weight:600;">Warnings/Memos
                            <span class="text-muted fw-normal">(within {{ $exCat === 'eligibility' ? '2' : '3' }} yrs, × 0.25, max 1.00)</span>
                        </label>
                    </div>
                    <div class="col-3">
                        <input type="number" min="0" max="4" value="0" id="wm_count"
                               class="form-control form-control-sm" onchange="updateDemerit()" oninput="updateDemerit()">
                    </div>
                    <div class="col-auto text-danger fw-bold" id="wm_pts" style="font-size:11px;">= 0.00</div>
                </div>

                {{-- Total Deduction display --}}
                <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top border-danger-subtle">
                    <span style="font-size:12px;font-weight:700;color:#dc2626;">Total Deduction:</span>
                    <span id="demerits_display" style="font-size:14px;font-weight:900;color:#dc2626;">–0.00</span>
                </div>

                {{-- Hidden input with computed total --}}
                <input type="hidden" name="demerits_deduction" id="demerits_hidden" value="{{ old('demerits_deduction', $sv->demerits_deduction ?? 0) }}">
            </div>

            {{-- Previously saved demerit note (if editing) --}}
            @if(isset($sv) && $sv->demerits_deduction && $sv->demerits_deduction > 0)
            <div class="alert alert-warning py-1 px-2 mb-2" style="font-size:11px;">
                <i class="bi bi-info-circle me-1"></i>
                Previously saved demerit deduction: <strong>{{ number_format($sv->demerits_deduction, 2) }}</strong>.
                Use the fields above to re-enter if updating.
            </div>
            @endif

            {{-- ── Remarks ── --}}
            <div class="mb-2">
                <label class="form-label fw-bold mb-1" style="font-size:12px;">Remarks / Notes</label>
                <textarea name="remarks" class="form-control form-control-sm" rows="2">{{ old('remarks', $sv->remarks ?? '') }}</textarea>
            </div>
        </form>
    </div>

    {{-- Footer --}}
    <div style="padding:10px 16px; border-top:1px solid #e2e8f0; background:#f8fafc; display:flex; justify-content:space-between; align-items:center; flex-shrink:0;">
        <div>
            <span style="font-size:12px;font-weight:700;">Total: </span>
            <span id="totalScore" class="text-primary fw-bold fs-5">{{ $sv->total_score ?? '0.00' }}</span>
            <span style="font-size:12px;color:#64748b;"> / 100</span>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-secondary"
                    onclick="document.getElementById('floatingScorePanel').style.display='none'">Close</button>
            <button type="submit" form="hrmpsbForm" class="btn btn-sm btn-primary fw-bold">
                <i class="bi bi-save me-1"></i>Save Score
            </button>
        </div>
    </div>
</div>

{{-- ============================================================
     SCRIPTS
     ============================================================ --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Category toggle ──────────────────────────────────────────
    window.toggleCategory = function () {
        const isElig = document.getElementById('cat_elig').checked;

        // Eligibility-only rows
        document.querySelectorAll('.eligibility-only').forEach(el => {
            el.style.display = isElig ? '' : 'none';
            el.querySelectorAll('select,input').forEach(f => { f.disabled = !isElig; });
        });

        // No-eligibility-only rows
        document.querySelectorAll('.no-eligibility-only').forEach(el => {
            el.style.display = isElig ? 'none' : '';
            el.querySelectorAll('select,input').forEach(f => { f.disabled = isElig; });
        });

        // IPCR 3.60-3.99 option visibility
        document.querySelectorAll('.no-elig-ipcr-opt').forEach(opt => {
            opt.style.display = isElig ? 'none' : '';
            if (isElig && opt.selected) opt.parentElement.value = '';
        });

        // Education toggle
        document.getElementById('edu_level_toggle').classList.toggle('d-none', !isElig);
        updateEduSelects(isElig);

        // Warning/memo label
        const wmLabel = document.getElementById('wm_label');
        if (wmLabel) {
            wmLabel.innerHTML = 'Warnings/Memos <span class="text-muted fw-normal">(within '
                + (isElig ? '2' : '3') + ' yrs, × 0.25, max 1.00)</span>';
        }

        calculateTotal();
    };

    // ── Education level toggle ────────────────────────────────────
    window.toggleEduLevel = function () {
        updateEduSelects(true);
        calculateTotal();
    };

    function updateEduSelects(isElig) {
        const isFirst  = document.getElementById('edu_first')  && document.getElementById('edu_first').checked;
        const isSecond = document.getElementById('edu_second') && document.getElementById('edu_second').checked;

        const sFirst  = document.getElementById('edu_select_first');
        const sSecond = document.getElementById('edu_select_second');
        const sNoElig = document.getElementById('edu_select_noelig');

        const showFirst  = isElig && isFirst;
        const showSecond = isElig && isSecond;
        const showNoElig = !isElig;

        if (sFirst)  { sFirst.style.display  = showFirst  ? '' : 'none'; sFirst.disabled  = !showFirst; }
        if (sSecond) { sSecond.style.display = showSecond ? '' : 'none'; sSecond.disabled = !showSecond; }
        if (sNoElig) { sNoElig.style.display = showNoElig ? '' : 'none'; sNoElig.disabled = !showNoElig; }

        // Sync hidden input from currently visible select
        const visible = showFirst ? sFirst : (showSecond ? sSecond : sNoElig);
        if (visible) syncEduScore(visible);

        // Update max label
        const maxLabel = document.querySelector('.edu-max-label');
        if (maxLabel) {
            const max = isElig ? {{ $twgMax['edu_e'] }} : {{ $twgMax['edu_n'] }};
            maxLabel.textContent = 'max ' + max + ' pts';
        }
    }

    window.syncEduScore = function (selectEl) {
        const hidden = document.getElementById('education_score_hidden');
        if (hidden) hidden.value = selectEl.value || '';
    };

    // ── Demerit calculator ────────────────────────────────────────
    window.updateDemerit = function () {
        const susp = parseFloat(document.getElementById('demerit_suspension').value) || 0;

        const reprN = Math.min(Math.max(parseInt(document.getElementById('repr_count').value) || 0, 0), 100);
        const reprPts = Math.min(reprN * 0.75, 2.25);
        document.getElementById('repr_pts').textContent = '= ' + reprPts.toFixed(2);

        const swN = Math.min(Math.max(parseInt(document.getElementById('sw_count').value) || 0, 0), 100);
        const swPts = Math.min(swN * 0.50, 1.50);
        document.getElementById('sw_pts').textContent = '= ' + swPts.toFixed(2);

        const wmN = Math.min(Math.max(parseInt(document.getElementById('wm_count').value) || 0, 0), 100);
        const wmPts = Math.min(wmN * 0.25, 1.00);
        document.getElementById('wm_pts').textContent = '= ' + wmPts.toFixed(2);

        const total = susp + reprPts + swPts + wmPts;
        document.getElementById('demerits_display').textContent = '–' + total.toFixed(2);
        document.getElementById('demerits_hidden').value = total.toFixed(2);

        calculateTotal();
    };

    // ── Grand total calculator ───────────────────────────────────
    window.calculateTotal = function () {
        let sum = 0;

        // Numeric inputs (PSB score)
        document.querySelectorAll('.score-num').forEach(inp => {
            if (!inp.disabled && inp.value) sum += parseFloat(inp.value) || 0;
        });

        // Dropdowns (IPCR, awards, experience, training, LOS)
        document.querySelectorAll('.score-dropdown').forEach(sel => {
            if (!sel.disabled && sel.value) sum += parseFloat(sel.value) || 0;
        });

        // Education hidden input
        const eduHidden = document.getElementById('education_score_hidden');
        if (eduHidden && eduHidden.value) sum += parseFloat(eduHidden.value) || 0;

        // Demerits
        const demerits = parseFloat(document.getElementById('demerits_hidden').value) || 0;
        const total = Math.max(0, sum - demerits);

        document.getElementById('totalScore').textContent = total.toFixed(2);
    };

    // ── Draggable panel ──────────────────────────────────────────
    const panel  = document.getElementById('floatingScorePanel');
    const header = document.getElementById('floatingScoreHeader');
    let isDragging = false, startX, startY, initX, initY;

    if (header && panel) {
        header.addEventListener('mousedown', function (e) {
            if (e.target.tagName.toLowerCase() === 'button') return;
            isDragging = true;
            startX = e.clientX; startY = e.clientY;
            const r = panel.getBoundingClientRect();
            initX = r.left; initY = r.top;
            panel.style.right = 'auto'; panel.style.bottom = 'auto';
            panel.style.left = initX + 'px'; panel.style.top = initY + 'px';
            document.body.style.userSelect = 'none';
        });
        document.addEventListener('mousemove', function (e) {
            if (!isDragging) return;
            panel.style.left = (initX + e.clientX - startX) + 'px';
            panel.style.top  = (initY + e.clientY - startY) + 'px';
        });
        document.addEventListener('mouseup', function () {
            isDragging = false;
            document.body.style.userSelect = '';
        });
    }

    // ── Init ─────────────────────────────────────────────────────
    toggleCategory();
    updateDemerit();
    calculateTotal();
});
</script>
