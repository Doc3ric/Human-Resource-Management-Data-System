<x-dashboard-app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Interview Evaluation Form
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
                <h2 class="mb-1 text-primary"><i class="fas fa-clipboard-list me-2"></i>Interview Evaluation Form</h2>
                <p class="text-muted">Evaluate the candidate using the given criteria (1 = Poor, 4 = Excellent).</p>
            </div>
            @if($applicant)
                <button type="button" onclick="document.getElementById('floatingScorePanel').style.display='flex'" class="btn btn-info fw-bold text-dark shadow-sm">
                    <i class="bi bi-person-video3 me-1"></i> Open Evaluation Panel
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

        <form action="{{ route('recruitment.hrmpsb.interview.create') }}" method="GET" class="mb-4">
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
            {{-- Header card --}}
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                @include('recruitment.partials.profile-details')
            </div>

            <!-- Floating Interview Evaluation Panel -->
            <div id="floatingScorePanel" style="display:none; position:fixed; z-index:9999; top:5%; right:5%; width:600px; background:#fff; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.3); overflow:hidden; resize:both; min-width:400px; max-height: 90vh; border:1px solid #cbd5e1; flex-direction:column;">
                <div id="floatingScoreHeader" class="bg-info" style="padding:12px 16px; cursor:move; display:flex; justify-content:space-between; align-items:center;">
                    <h5 class="m-0 fw-bold text-dark" style="font-size:16px;">Interview Evaluation for {{ \App\Support\BlindScoringId::forApplicant($applicant) }}</h5>
                    <button type="button" class="btn-close" onclick="document.getElementById('floatingScorePanel').style.display='none'"></button>
                </div>
                <div style="padding:16px; flex-grow:1; overflow-y:auto; background:#f8f9fa;">
                    <form action="{{ route('recruitment.hrmpsb.interview.store') }}" method="POST" id="interviewForm">
                        @csrf
                        <input type="hidden" name="applicant_id" value="{{ $applicant->id }}">
                        
                        <!-- Personal Appearance -->
                        <div class="card shadow-sm border-0 mb-4 border-start border-primary border-4">
                            <div class="card-header bg-white">
                                <h6 class="mb-0 text-primary fw-bold">I. Personal Appearance ({{ $weights['appearance'] }}%)</h6>
                            </div>
                            <div class="card-body py-2">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <p class="mb-0" style="font-size: 13px;">1. The candidate presents himself/herself in a good grooming and tidy appearance.</p>
                                    </div>
                                    <div class="col-md-4">
                                        <select name="ratings[appearance_1]" class="form-select form-select-sm" required>
                                            <option value="">Select Rating</option>
                                            <option value="4">4 - Excellent</option>
                                            <option value="3">3 - Good</option>
                                            <option value="2">2 - Fair</option>
                                            <option value="1">1 - Poor</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Knowledge on the Job -->
                        <div class="card shadow-sm border-0 mb-4 border-start border-success border-4">
                            <div class="card-header bg-white">
                                <h6 class="mb-0 text-success fw-bold">II. Knowledge on the Job & Org ({{ $weights['knowledge'] }}%)</h6>
                            </div>
                            <div class="card-body py-2">
                                @php
                                    $knowledges = [
                                        '1' => 'The candidate is knowledgeable of the functions of the vacant position.',
                                        '2' => 'The candidate is knowledgeable of the organizational structure of the department/division where the vacancy exists.',
                                        '3' => 'The candidate is knowledgeable of the mission and vision of the department/office where the vacancy exists.',
                                        '4' => 'The candidate can explain how the functions of the vacant position translate to the achievement of the mission and vision.'
                                    ];
                                @endphp
                                @foreach($knowledges as $idx => $desc)
                                    <div class="row align-items-center mb-2">
                                        <div class="col-md-8">
                                            <p class="mb-0" style="font-size: 13px;">{{ $idx }}. {{ $desc }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <select name="ratings[knowledge_{{ $idx }}]" class="form-select form-select-sm" required>
                                                <option value="">Select Rating</option>
                                                <option value="4">4 - Excellent</option>
                                                <option value="3">3 - Good</option>
                                                <option value="2">2 - Fair</option>
                                                <option value="1">1 - Poor</option>
                                            </select>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Communication / Interpersonal -->
                        <div class="card shadow-sm border-0 mb-4 border-start border-info border-4">
                            <div class="card-header bg-white">
                                <h6 class="mb-0 text-info fw-bold">III. Communication / Interpersonal ({{ $weights['communication'] }}%)</h6>
                            </div>
                            <div class="card-body py-2">
                                @php
                                    $comms = [
                                        '1' => 'Listens to questions attentively and actively.',
                                        '2' => 'Answers the questions or expresses ideas clearly, concisely and logically.',
                                        '3' => 'Demonstrates confidence by displaying positive body language and maintaining eye contact.'
                                    ];
                                @endphp
                                @foreach($comms as $idx => $desc)
                                    <div class="row align-items-center mb-2">
                                        <div class="col-md-8">
                                            <p class="mb-0" style="font-size: 13px;">{{ $idx }}. {{ $desc }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <select name="ratings[comm_{{ $idx }}]" class="form-select form-select-sm" required>
                                                <option value="">Select Rating</option>
                                                <option value="4">4 - Excellent</option>
                                                <option value="3">3 - Good</option>
                                                <option value="2">2 - Fair</option>
                                                <option value="1">1 - Poor</option>
                                            </select>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Other Evaluation Criteria -->
                        <div class="card shadow-sm border-0 mb-4 border-start border-warning border-4">
                            <div class="card-header bg-white">
                                <h6 class="mb-0 text-warning fw-bold">IV. Other Evaluation Criteria ({{ $weights['other'] }}%)</h6>
                            </div>
                            <div class="card-body py-2">
                                @php
                                    $others = [
                                        '1' => '<strong>JOB COMMITMENT</strong>: Responsibility towards the mission and goals of an organization.',
                                        '2' => '<strong>COMMITMENT</strong>: Psychological attachment to the organization.',
                                        '3' => '<strong>POTENTIAL</strong>: Capability to perform duties of the position and higher ones.',
                                        '4' => '<strong>SINCERITY</strong>: Assessment of honesty, expression of valid/useful opinion backed by evidence.',
                                        '5' => '<strong>PROFESSIONALISM</strong>: Demonstrates consideration, respect, loyalty, and exceeds expectations.',
                                        '6' => '<strong>INITIATIVE</strong>: Eagerness to start actions without being told to start them.',
                                        '7' => '<strong>TEAMWORK</strong>: Active involvement to a team resulting in goal achievement.',
                                        '8' => '<strong>TIME MANAGEMENT</strong>: Act of planning time spent on activities to increase productivity.',
                                        '9' => '<strong>CUSTOMER SERVICE</strong>: Act of taking care of customers needs by providing professional assistance.',
                                        '10' => '<strong>JOB SATISFACTION</strong>: Contentment of current job and the sense of accomplishment.'
                                    ];
                                @endphp
                                @foreach($others as $idx => $desc)
                                    <div class="row align-items-center mb-2">
                                        <div class="col-md-8">
                                            <p class="mb-0" style="font-size: 13px;">{{ $idx }}. {!! $desc !!}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <select name="ratings[other_{{ $idx }}]" class="form-select form-select-sm" required>
                                                <option value="">Select Rating</option>
                                                <option value="4">4 - Excellent</option>
                                                <option value="3">3 - Good</option>
                                                <option value="2">2 - Fair</option>
                                                <option value="1">1 - Poor</option>
                                            </select>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Remarks -->
                        <div class="card shadow-sm border-0 mb-2 border-start border-secondary border-4">
                            <div class="card-body py-3">
                                <label class="form-label fw-bold" style="font-size: 13px;">Remarks (Optional)</label>
                                <textarea name="remarks" class="form-control form-control-sm" rows="3" placeholder="Additional observations..."></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div style="padding:12px 16px; border-top:1px solid #e2e8f0; background:#f8fafc; display:flex; justify-content:flex-end; align-items:center;">
                    <div>
                        <button type="button" class="btn btn-sm btn-secondary me-2" onclick="document.getElementById('floatingScorePanel').style.display='none'">Close</button>
                        <button type="submit" form="interviewForm" class="btn btn-sm btn-primary fw-bold"><i class="fas fa-save me-1"></i> Save Evaluation</button>
                    </div>
                </div>
            </div>
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

            // Build office→positions mapping from applicant data attributes
            const appOpts   = Array.from(applicantSelect.options).filter(o => o.value);
            const posOpts   = Array.from(positionSelect.options).filter(o => o.value);
            const offToPos  = {};
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
                const search   = (applicantSearch.value || '').trim().toUpperCase();
                appOpts.forEach(o => {
                    const mO = !office   || o.dataset.office    === office;
                    const mP = !position || o.dataset.position  === position;
                    const mS = !search   || (o.dataset.name || '').includes(search);
                    o.style.display = (mO && mP && mS) ? '' : 'none';
                });
                const selApp = applicantSelect.options[applicantSelect.selectedIndex];
                if (selApp && selApp.value && selApp.style.display === 'none') applicantSelect.value = '';
            }

            officeSelect.addEventListener('change', function() { updatePositions(); updateApplicants(); });
            positionSelect.addEventListener('change', updateApplicants);
            applicantSearch.addEventListener('input', updateApplicants);
            updatePositions();
            updateApplicants();

            @if($applicant)
            const panel = document.getElementById('floatingScorePanel');
            const header = document.getElementById('floatingScoreHeader');
            let isDragging = false, startX, startY, initialX, initialY;

            if(header && panel) {
                header.addEventListener('mousedown', function(e) {
                    if(e.target.tagName.toLowerCase() === 'button') return;
                    isDragging = true;
                    startX = e.clientX;
                    startY = e.clientY;
                    const rect = panel.getBoundingClientRect();
                    initialX = rect.left;
                    initialY = rect.top;
                    panel.style.right = 'auto';
                    panel.style.bottom = 'auto';
                    panel.style.left = initialX + 'px';
                    panel.style.top = initialY + 'px';
                    document.body.style.userSelect = 'none';
                });

                document.addEventListener('mousemove', function(e) {
                    if (!isDragging) return;
                    const dx = e.clientX - startX;
                    const dy = e.clientY - startY;
                    panel.style.left = (initialX + dx) + 'px';
                    panel.style.top = (initialY + dy) + 'px';
                });

                document.addEventListener('mouseup', function() {
                    isDragging = false;
                    document.body.style.userSelect = '';
                });
            }
            
            // Auto open the panel
            panel.style.display = 'flex';
            @endif
        });
    </script>
</x-dashboard-app>
