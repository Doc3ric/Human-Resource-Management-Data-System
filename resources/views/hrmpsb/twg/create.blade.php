<x-dashboard-app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Score HRMPSB (TWG)
        </h2>
    </x-slot>
    <style>
        .ai-section { margin-bottom: 28px; }
        .ai-section-title {
            font-size: 11px; font-weight: 800; text-transform: uppercase;
            letter-spacing: .7px; color: #6b7280;
            border-bottom: 1px solid #e5e7eb; padding-bottom: 6px; margin-bottom: 14px;
        }
        .ai-field { margin-bottom: 14px; }
        .ai-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #9ca3af; margin-bottom: 3px; }
        .ai-value { font-size: 13px; color: #1f2937; font-weight: 500; }
        .ai-value.empty { color: #d1d5db; font-style: italic; font-weight: 400; }
        .doc-link { font-size: 12px; display: inline-flex; align-items: center; gap: 4px; }
        .badge-yes { background: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .badge-no  { background: #f3f4f6; color: #9ca3af; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 700; }
    </style>

    <div class="content-wrapper p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1 text-primary"><i class="fas fa-star me-2"></i>Score HRMPSB (TWG)</h2>
                <p class="text-muted">Select an applicant to view their profile and evaluate using TWG criteria.</p>
            </div>
            @if($applicant)
                <button type="button" onclick="document.getElementById('floatingScorePanel').style.display='flex'" class="btn btn-warning fw-bold text-dark shadow-sm">
                    <i class="bi bi-star-fill me-1"></i> Open Scoring Panel
                </button>
            @endif
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #0dcaf0 !important;">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="bg-info bg-opacity-10 rounded p-3 me-3 text-info">
                            <i class="bi bi-people-fill fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-0" style="font-size: 12px; font-weight: 700; text-transform: uppercase;">Total Applicants</p>
                            <h4 class="mb-0 fw-bold">{{ $stats['total'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #198754 !important;">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 rounded p-3 me-3 text-success">
                            <i class="bi bi-check-circle-fill fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-0" style="font-size: 12px; font-weight: 700; text-transform: uppercase;">Evaluated</p>
                            <h4 class="mb-0 fw-bold">{{ $stats['evaluated'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #ffc107 !important;">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 rounded p-3 me-3 text-warning">
                            <i class="bi bi-hourglass-split fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-0" style="font-size: 12px; font-weight: 700; text-transform: uppercase;">Pending Evaluation</p>
                            <h4 class="mb-0 fw-bold">{{ $stats['pending'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-1"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-1"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('recruitment.hrmpsb.twg.create') }}" method="GET" class="mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom pb-0 pt-4">
                    <h5 class="mb-3">Applicant Selection</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Search Applicant Name</label>
                            <input type="text" class="form-control" id="applicant_search" placeholder="Last or first name...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Office where vacancy exists</label>
                            <select class="form-select" id="office_select">
                                <option value="">-- Select Office --</option>
                                @foreach($offices as $office)
                                    <option value="{{ $office }}">{{ $office }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Position</label>
                            <select class="form-select" id="position_select">
                                <option value="">-- Select Position --</option>
                                @foreach($positions as $posKey => $posLabel)
                                    <option value="{{ $posKey }}">{{ $posLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Applicant Name</label>
                            <select name="applicant_id" class="form-select" id="applicant_select" required>
                                <option value="">-- Select Applicant --</option>
                                @foreach($applicants as $app)
                                    <option value="{{ $app->id }}" data-office="{{ $app->office }}" data-position="{{ $app->position_key }}" data-name="{{ strtoupper($app->last_name . ', ' . $app->first_name) }}" {{ ($applicant && $applicant->id == $app->id) ? 'selected' : '' }}>
                                        {{ $app->last_name }}, {{ $app->first_name }} @if($app->item_no) ({{ $app->item_no }}) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100 fw-bold"><i class="bi bi-search me-1"></i> Load Profile</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        @if($applicant)
            {{-- Applicant header card --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px; overflow: hidden;">
                <div style="background: linear-gradient(135deg, #082977 0%, #0f172a 100%); padding: 24px 28px;">
                    <div class="d-flex align-items-center gap-4">
                        @if($applicant->photo_url)
                            <img src="{{ $applicant->photo_url }}" alt="Photo"
                                 style="width:72px;height:72px;border-radius:50%;object-fit:cover;border:3px solid rgba(255,255,255,.3);">
                        @else
                            <div style="width:72px;height:72px;border-radius:50%;background:rgba(255,255,255,.15);
                                        display:flex;align-items:center;justify-content:center;
                                        font-size:28px;font-weight:800;color:#fff;border:3px solid rgba(255,255,255,.2);">
                                {{ strtoupper(substr($applicant->first_name, 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <div style="color:#fff;font-size:22px;font-weight:800;">{{ $applicant->full_name }}</div>
                            <div style="color:rgba(255,255,255,.65);font-size:13px;margin-top:4px;">
                                AIN: <strong style="color:#fff;">{{ $applicant->ain }}</strong>
                                &nbsp;·&nbsp; Ref: <strong style="color:#fff;">{{ $applicant->reference_no }}</strong>
                                @if($autoCategory)
                                    &nbsp;·&nbsp;
                                    <span class="badge {{ $autoCategory === 'eligibility' ? 'bg-success' : 'bg-secondary' }} ms-1">
                                        {{ $autoCategory === 'eligibility' ? 'With Eligibility' : 'No Eligibility' }}
                                        <span style="opacity:.7;font-size:9px;">(auto-detected)</span>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                @include('recruitment.partials.profile-details')
            </div>

            {{-- TWG Dropdown Scoring Panel (uses the full component with all dropdowns) --}}
            <x-twg-score-panel
                :applicant="$applicant"
                :score="$score"
                :psb-from-panel="$psbFromPanel"
                :panel-count="$panelCount"
                actionUrl="{{ route('recruitment.hrmpsb.twg.store') }}"
            />

            {{-- Auto-open the panel and apply auto-detected category --}}
            <script>
            document.addEventListener('DOMContentLoaded', function () {
                const panel = document.getElementById('floatingScorePanel');
                if (panel) panel.style.display = 'flex';

                @if(!$score->position_category && $autoCategory)
                // Apply auto-detected category
                const eligRadio   = document.getElementById('cat_elig');
                const noeligRadio = document.getElementById('cat_noelig');
                if ('{{ $autoCategory }}' === 'eligibility' && eligRadio)   { eligRadio.checked = true; }
                if ('{{ $autoCategory }}' === 'no_eligibility' && noeligRadio) { noeligRadio.checked = true; }
                if (typeof toggleCategory === 'function') toggleCategory();

                @if($autoEduLevel === 'first_level')
                    const firstRadio = document.getElementById('edu_first');
                    if (firstRadio) { firstRadio.checked = true; if (typeof toggleEduLevel === 'function') toggleEduLevel(); }
                @else
                    const secondRadio = document.getElementById('edu_second');
                    if (secondRadio) { secondRadio.checked = true; if (typeof toggleEduLevel === 'function') toggleEduLevel(); }
                @endif
                @endif
            });
            </script>

            {{-- Copy Score to Other Applications --}}
            @if($copyTargets->count() > 0)
            <div class="card border-0 shadow-sm mt-4" style="border-left:4px solid #f59e0b !important; border-radius:12px;">
                <div class="card-header bg-warning bg-opacity-10 border-bottom d-flex align-items-center gap-2 py-3">
                    <i class="bi bi-copy text-warning fs-5"></i>
                    <div>
                        <h6 class="mb-0 fw-bold">Copy Credential Scores to Other Applications</h6>
                        <p class="mb-0 text-muted" style="font-size:12px;">
                            <strong>{{ $applicant->full_name }}</strong> is applying for {{ $copyTargets->count() + 1 }} position(s) in total.
                            Credential scores (IPCR, Awards, Education, Experience, Training, LOS, Demerits) can be copied — PSB Interview Score is always fetched individually from each position's panel evaluations.
                        </p>
                    </div>
                </div>
                <div class="card-body">
                    @if(!$score->id)
                        <div class="alert alert-secondary py-2" style="font-size:12px;">
                            <i class="bi bi-info-circle me-1"></i>Save the score first before copying to other applications.
                        </div>
                    @else
                        <form action="{{ route('recruitment.hrmpsb.twg.copy_score') }}" method="POST" id="copyScoreForm">
                            @csrf
                            <input type="hidden" name="source_applicant_id" value="{{ $applicant->id }}">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle mb-3" style="font-size:13px;">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:36px;"><input type="checkbox" id="copyAll" class="form-check-input" onchange="document.querySelectorAll('.copy-chk:not(:disabled)').forEach(c=>c.checked=this.checked)"></th>
                                            <th>Position Applied</th>
                                            <th>Item No.</th>
                                            <th>Category</th>
                                            <th>Has Score?</th>
                                            <th>Panel PSB</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($copyTargets as $ct)
                                        @php
                                            $sameCat = ($score->position_category ?? $autoCategory) === ($ct->hrmpsbScore->position_category ?? $autoCategory);
                                            $ctPanelAvg = \App\Models\InterviewEvaluation::where('applicant_id', $ct->id)->avg('total_score');
                                            $ctPsb = $ctPanelAvg !== null ? round($ctPanelAvg * 0.50, 2) : null;
                                        @endphp
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="target_applicant_ids[]" value="{{ $ct->id }}"
                                                       class="form-check-input copy-chk"
                                                       {{ !$sameCat ? 'disabled' : '' }}
                                                       title="{{ !$sameCat ? 'Different position category — cannot copy' : '' }}">
                                            </td>
                                            <td class="fw-semibold">{{ $ct->position_applied }}</td>
                                            <td>{{ $ct->item_no ?? '—' }}</td>
                                            <td>
                                                @if($sameCat)
                                                    <span class="badge bg-success">Same</span>
                                                @else
                                                    <span class="badge bg-secondary">Different</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($ct->hrmpsbScore)
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="bi bi-pencil-fill me-1"></i>Overwrite {{ $ct->hrmpsbScore->total_score }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-light text-muted border">New</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($ctPsb !== null)
                                                    <span class="text-success fw-bold">{{ $ctPsb }}</span>
                                                @else
                                                    <span class="text-muted fst-italic">No panel evals</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="confirmCopy" required>
                                <label class="form-check-label fw-semibold" for="confirmCopy" style="font-size:12px;">
                                    I confirm that the selected applicant has the same qualifications for the checked positions and approve the score carry-over.
                                </label>
                            </div>
                            <button type="button" class="btn btn-warning fw-bold" onclick="validateCopyForm()">
                                <i class="bi bi-copy me-1"></i> Copy Credential Scores to Checked Applications
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            @endif

        @else
            <div class="text-center py-5">
                <i class="bi bi-person-lines-fill text-muted" style="font-size: 4rem;"></i>
                <p class="mt-3 text-muted">Please select an applicant and load their profile to begin evaluating.</p>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const officeSelect    = document.getElementById('office_select');
            const positionSelect  = document.getElementById('position_select');
            const applicantSelect = document.getElementById('applicant_select');
            const applicantSearch = document.getElementById('applicant_search');

            const allApplicantsHtml = applicantSelect.innerHTML;
            const allPositionsHtml  = positionSelect.innerHTML;

            function filterPositions() {
                const office = officeSelect.value;
                positionSelect.innerHTML = allPositionsHtml;

                if (office === '') { filterApplicants(); return; }

                const validPositions = new Set();
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = allApplicantsHtml;
                tempDiv.querySelectorAll('option').forEach(opt => {
                    if (opt.value !== '' && opt.getAttribute('data-office') === office) {
                        validPositions.add(opt.getAttribute('data-position'));
                    }
                });

                positionSelect.querySelectorAll('option').forEach(opt => {
                    if (opt.value === '') return;
                    if (!validPositions.has(opt.value)) opt.remove();
                });

                if (positionSelect.value !== '' && !validPositions.has(positionSelect.value)) {
                    positionSelect.value = '';
                }
                filterApplicants();
            }

            function filterApplicants() {
                const office   = officeSelect.value;
                const position = positionSelect.value;
                const search   = (applicantSearch.value || '').trim().toUpperCase();
                applicantSelect.innerHTML = allApplicantsHtml;

                applicantSelect.querySelectorAll('option').forEach(opt => {
                    if (opt.value === '') return;
                    const matchOffice = office   === '' || opt.getAttribute('data-office')    === office;
                    const matchPos    = position === '' || opt.getAttribute('data-position')  === position;
                    const matchSearch = search   === '' || (opt.getAttribute('data-name') || '').includes(search);
                    if (!(matchOffice && matchPos && matchSearch)) opt.remove();
                });
            }

            officeSelect.addEventListener('change', filterPositions);
            positionSelect.addEventListener('change', filterApplicants);
            applicantSearch.addEventListener('input', filterApplicants);
        });

        function validateCopyForm() {
            const checked = document.querySelectorAll('.copy-chk:checked');
            if (checked.length === 0) {
                alert('Please select at least one application to copy scores to.');
                return;
            }
            const confirmed = document.getElementById('confirmCopy');
            if (!confirmed || !confirmed.checked) {
                alert('Please check the confirmation checkbox to approve the score carry-over.');
                return;
            }
            if (confirm('Copy credential scores to ' + checked.length + ' selected application(s)? This will overwrite any existing credential scores for those records.')) {
                document.getElementById('copyScoreForm').submit();
            }
        }
    </script>
</x-dashboard-app>
