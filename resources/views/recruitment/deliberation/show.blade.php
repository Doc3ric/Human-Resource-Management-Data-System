<x-dashboard-app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Deliberation Workspace (Module 5)
        </h2>
    </x-slot>

    @php
        $phaseLabels = [
            'screening' => 'Screening',
            'twg_evaluation' => 'TWG Evaluation',
            'hrmpsb_deliberation' => 'HRMPSB Deliberation',
            'completed' => 'Completed',
        ];
        $currentPhase = request('phase', $applicant ? ($applicant->deliberation_phase ?: 'screening') : 'screening');
    @endphp

    <style>
        .deliberation-header {
            position: sticky; top: 0; z-index: 5;
            background: linear-gradient(135deg,#082977 0%,#0f172a 100%);
            color: #fff; padding: 14px 20px; border-radius: 10px;
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;
        }
        .applicant-chip-track {
            display: flex; gap: 8px; overflow-x: auto; padding: 12px 4px; margin: 10px 0;
        }
        .applicant-chip {
            display: flex; align-items: center; gap: 6px; padding: 6px 12px 6px 6px;
            border-radius: 20px; border: 1px solid #e5e7eb; background: #fff;
            text-decoration: none; color: #374151; font-size: 12.5px; white-space: nowrap; flex-shrink: 0;
        }
        .applicant-chip.active { border-color: var(--color-accent, #2563eb); background: #eff6ff; color: var(--color-accent, #2563eb); font-weight: 700; }
        .applicant-chip img, .applicant-chip .chip-silhouette {
            width: 24px; height: 24px; border-radius: 50%; object-fit: cover;
        }
        .applicant-chip .chip-silhouette { background: #f3f4f6; border: 1.5px solid var(--color-danger, #dc2626); display: flex; align-items: center; justify-content: center; }

        .deliberation-grid {
            display: grid; grid-template-columns: 45% 55%; gap: 16px;
            height: calc(100vh - 230px); min-height: 500px;
        }
        .deliberation-panel { overflow-y: auto; padding-right: 6px; }

        @media (max-width: 1024px) {
            .deliberation-grid { display: block; height: auto; }
            .deliberation-panel { display: none; height: auto; overflow-y: visible; }
            .deliberation-panel.active-tab { display: block; }
        }
    </style>

    <div class="content-wrapper p-4">
        @if($errors->has('access'))
            <div class="alert alert-danger fw-bold shadow-sm mb-4">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ $errors->first('access') }}
            </div>
        @endif

        @if(!empty($vppmDeficientRequest))
            <div class="alert alert-warning fw-bold shadow-sm mb-4">
                <i class="bi bi-exclamation-triangle-fill"></i>
                This vacancy's publication (CS Form No. 9 request #{{ $vppmDeficientRequest->id }}) is flagged
                <strong>PUBLICATION_DEFICIENT</strong> — the minimum posting period was reached without all
                required posting sites confirmed (R.A. 7041 / 2025 ORAOHRA Sec. 26). Deliberation may proceed,
                but resolve this before the appointment is finalized to avoid a disapproval ground.
                <a href="{{ route('vppm.requests.show', $vppmDeficientRequest) }}" class="alert-link">Review publication request</a>.
            </div>
        @endif

        <!-- Filter Bar -->
        <div class="card shadow-sm border-0 mb-4" style="background-color: #f8f9fa;">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('recruitment.deliberation.list') }}" class="row g-2 align-items-center">
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <label class="form-label mb-0 fw-semibold me-2 text-nowrap">Search:</label>
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Applicant name or Item No..." value="{{ $search ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <label class="form-label mb-0 fw-semibold me-2 text-nowrap">Office:</label>
                            <select name="office" id="officeSelect" class="form-select form-select-sm">
                                <option value="">-- Select Office --</option>
                                @php
                                    $offices = $officePositions->pluck('office')->unique()->sort();
                                @endphp
                                @foreach($offices as $optOffice)
                                    <option value="{{ $optOffice }}" {{ $office === $optOffice ? 'selected' : '' }}>{{ $optOffice }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <label class="form-label mb-0 fw-semibold me-2 text-nowrap">Position:</label>
                            <select name="position_applied" id="positionSelect" class="form-select form-select-sm" {{ !$office ? 'disabled' : '' }}>
                                <option value="">-- Select Position --</option>
                                @if($office)
                                    @php
                                        $positions = $officePositions->where('office', $office)->sortBy('position_label');
                                    @endphp
                                    @foreach($positions as $optPosition)
                                        <option value="{{ $optPosition->position_key }}" {{ $position_applied === $optPosition->position_key ? 'selected' : '' }}>{{ $optPosition->position_label }}@if($optPosition->item_no) ({{ $optPosition->item_no }})@endif</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold"><i class="bi bi-filter"></i> Filter</button>
                    </div>
                </form>
                <div class="form-text mt-1" style="font-size:11px;">
                    Search by applicant name or Item No, or select an Office and Position to browse that vacancy's candidate pool. Search takes priority if both are filled.
                </div>
            </div>
        </div>

        @if($applicants && $applicants->isNotEmpty())
        <div class="applicant-chip-track">
            @php $photoEnforcer = app(\App\Support\PhotoEnforcementService::class); @endphp
            @foreach($applicants as $app)
                @php $hasValidPhoto = $photoEnforcer->hasValidPhoto($app); @endphp
                <a href="{{ route('recruitment.deliberation.show', $app) }}" class="applicant-chip {{ $applicant && $app->id === $applicant->id ? 'active' : '' }}" {!! !$hasValidPhoto ? 'style="border-color:#dc2626;" title="Photo missing — scoring blocked."' : '' !!}>
                    @if($app->photo_url)
                        <img src="{{ $app->photo_url }}" alt="">
                    @else
                        <span class="chip-silhouette"><i class="bi bi-person-fill" style="font-size:11px;color:#dc2626;"></i></span>
                    @endif
                    {{ $app->last_name }}, {{ $app->first_name }}
                </a>
            @endforeach
        </div>
        @endif

        @if($applicant)
        <div class="deliberation-header">
            <div>
                <div style="font-size:18px;font-weight:800;">
                    {{ $applicant->position_applied ?: 'N/A' }} 
                    <span style="font-size:10px;font-weight:normal;opacity:0.8;background:rgba(255,255,255,0.2);padding:2px 6px;border-radius:4px;margin-left:6px;">Published via R.A. 7041</span>
                </div>
                <div style="font-size:12px;opacity:.8;">
                    Item No: {{ $applicant->item_no ?: '—' }}
                    &middot; SG: {{ $position?->salary_grade ?? '—' }}
                    &middot; Office: {{ $applicant->office ?: '—' }}
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('recruitment.deliberation.export.layout-a', $applicant) }}" target="_blank" class="btn btn-sm btn-outline-light">
                    <i class="bi bi-printer"></i> Layout A (Profile)
                </a>
                <a href="{{ route('recruitment.deliberation.export.cer', $applicant) }}" target="_blank" class="btn btn-sm btn-outline-info text-dark fw-bold" style="background:#e0f2fe;">
                    <i class="bi bi-file-earmark-pdf"></i> Print CER
                </a>
                <a href="{{ route('recruitment.deliberation.export.layout-c', $applicant) }}" target="_blank" class="btn btn-sm btn-outline-light">
                    <i class="bi bi-file-earmark-pdf"></i> Layout C (Scores)
                </a>
                <a href="{{ route('recruitment.deliberation.export.layout-d', ['position' => $applicant->position_applied]) }}" class="btn btn-sm btn-light text-dark fw-bold">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Layout D (Comparative)
                </a>
                <a href="{{ route('recruitment.report', ['report_type' => 'preeval', 'office' => $office, 'item_no' => \App\Support\Recruitment\VacancyIdentifier::key(null, $position_applied)]) }}" target="_blank" class="btn btn-sm btn-outline-warning text-dark fw-bold" style="background:#fffbeb; border-color:#fcd34d;">
                    <i class="bi bi-file-earmark-pdf-fill" style="color:#d97706;"></i> Pre-Eval Matrix
                </a>
                <div class="btn-group btn-group-sm ms-3 stage-switcher" role="group">
                    <button type="button" class="btn btn-outline-light {{ $currentPhase === 'screening' ? 'active fw-bold' : '' }}" data-phase="screening" onclick="switchPhaseUI('screening', event)">Screening</button>
                    <button type="button" class="btn btn-outline-light {{ $currentPhase === 'twg_evaluation' ? 'active fw-bold' : '' }}" data-phase="twg_evaluation" onclick="switchPhaseUI('twg_evaluation', event)">TWG Eval</button>
                    <button type="button" class="btn btn-outline-light {{ $currentPhase === 'hrmpsb_deliberation' ? 'active fw-bold' : '' }}" data-phase="hrmpsb_deliberation" onclick="switchPhaseUI('hrmpsb_deliberation', event)">HRMPSB</button>
                    <button type="button" class="btn btn-outline-light {{ $currentPhase === 'completed' ? 'active fw-bold' : '' }}" data-phase="completed" onclick="switchPhaseUI('completed', event)">Completed</button>
                </div>
                
                <div class="btn-group btn-group-sm ms-3 focus-toggles" role="group">
                    <button type="button" class="btn btn-dark" data-mode="profile" onclick="setFocusMode('profile')" title="Expand Profile"><i class="bi bi-arrow-bar-right"></i></button>
                    <button type="button" class="btn btn-dark active" data-mode="split" onclick="setFocusMode('split')" title="Split View"><i class="bi bi-layout-split"></i></button>
                    <button type="button" class="btn btn-dark" data-mode="scoring" onclick="setFocusMode('scoring')" title="Expand Scoring"><i class="bi bi-arrow-bar-left"></i></button>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-2 px-1">
            <div style="font-size:10.5px;color:#6b7280;font-weight:600;">
                <i class="bi bi-shield-lock-fill"></i> PRIVACY NOTICE: Selector tracks governed by R.A. 10173.
            </div>
            <div class="d-flex align-items-center gap-3" style="font-size:13px;" id="dashboardProgressStrip" style="display:none;">
                <div class="fw-bold text-primary me-2"><i class="bi bi-bar-chart-fill"></i> <span id="dashStageName">Phase</span> Progress:</div>
                <div><span class="badge bg-success" id="dashCompleted">0</span> Completed</div>
                <div><span class="badge bg-warning text-dark" id="dashInProgress">0</span> In Progress</div>
                <div><span class="badge bg-secondary" id="dashNotStarted">0</span> Not Started</div>
            </div>
        </div>

        <div class="d-md-none mb-2" style="display:none;" id="mobileTabSwitcher">
            <div class="btn-group w-100">
                <button type="button" class="btn btn-outline-primary btn-sm active" onclick="showDeliberationTab('profile')" id="tabBtnProfile">Profile</button>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="showDeliberationTab('scoring')" id="tabBtnScoring">Scoring Sheet</button>
            </div>
        </div>

        <div class="mb-3">
            <button type="button" class="btn btn-sm btn-dark" onclick="toggleMonitoringBoard()">
                <i class="bi bi-display"></i> HRMPSB Monitoring Board
            </button>
            <div id="monitoringBoard" style="display:none; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:15px; margin-top:10px;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-bold">Deliberation Completion Board</h6>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-secondary active" onclick="setBoardView('matrix')" id="btnViewMatrix">Matrix</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="setBoardView('member')" id="btnViewMember">By Member</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="setBoardView('applicant')" id="btnViewApplicant">By Applicant</button>
                    </div>
                    <small class="text-muted" id="monitoringLastUpdated">Updating...</small>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered" style="font-size: 13px; text-align: center;">
                        <thead id="monitoringTableHead">
                            <!-- Filled via JS -->
                        </thead>
                        <tbody id="monitoringTableBody">
                            <!-- Filled via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="deliberation-grid" id="delibGrid">
            <div class="deliberation-panel active-tab" id="profilePanel">
                @include('recruitment.partials.deliberation-profile')
            </div>
            <div class="deliberation-panel" id="scoringPanel">
                <div class="btn-group btn-group-sm mb-2 scoring-view-switcher" role="group">
                    <button type="button" class="btn btn-outline-primary active" onclick="showScoringView('form')" id="scoringViewFormBtn">
                        <i class="bi bi-pencil-square"></i> Scoring Form
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="showScoringView('matrix')" id="scoringViewMatrixBtn">
                        <i class="bi bi-grid-3x3-gap-fill"></i> Scoring Matrix
                    </button>
                </div>

                <div id="scoringFormWrapper">
                    <div id="panel-screening" style="display: {{ $currentPhase === 'screening' ? 'block' : 'none' }}">
                        @include('recruitment.partials.panel-screening')
                    </div>
                    <div id="panel-twg_evaluation" style="display: {{ $currentPhase === 'twg_evaluation' ? 'block' : 'none' }}">
                        @include('hrmpsb.twg-dynamic._sheet')
                    </div>
                    <div id="panel-hrmpsb_deliberation" style="display: {{ $currentPhase === 'hrmpsb_deliberation' ? 'block' : 'none' }}">
                        @include('recruitment.partials.panel-interview')
                    </div>
                    <div id="panel-completed" style="display: {{ $currentPhase === 'completed' ? 'block' : 'none' }}">
                        @include('recruitment.partials.panel-summary')
                    </div>
                </div>

                <div id="panel-scoring-matrix" style="display:none;">
                    @include('hrmpsb.interview._matrix-content')
                </div>
            </div>
            </div>
        </div>
        @else
        <div class="text-center py-5 text-muted" style="background:#fff; border-radius:10px; border:1px solid #e5e7eb; min-height: 400px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <i class="bi bi-person-bounding-box" style="font-size: 4rem; color: #cbd5e1; margin-bottom: 15px;"></i>
            <h4 class="fw-bold" style="color: #64748b;">No Applicant Selected</h4>
            <p style="color: #94a3b8; max-width: 400px;">
                @if($office && $position_applied)
                    Select an applicant from the list above to view their profile and begin scoring.
                @else
                    Please select an Office and Position using the filter bar above to load the list of applicants.
                @endif
            </p>
        </div>
        @endif
    </div>

    <!-- Filter logic (runs even without applicant) -->
    <script id="officePositionsData" type="application/json">
        {!! $officePositions->toJson() !!}
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dataStr = document.getElementById('officePositionsData').textContent;
            if(!dataStr) return;
            const officePositions = JSON.parse(dataStr);
            
            const officeSelect = document.getElementById('officeSelect');
            const positionSelect = document.getElementById('positionSelect');

            if(officeSelect && positionSelect) {
                officeSelect.addEventListener('change', () => {
                    const selectedOffice = officeSelect.value;
                    positionSelect.innerHTML = '<option value="">-- Select Position --</option>';
                    
                    if (!selectedOffice) {
                        positionSelect.disabled = true;
                        return;
                    }

                    const positions = officePositions
                        .filter(op => op.office === selectedOffice)
                        .sort((a, b) => a.position_label.localeCompare(b.position_label));

                    positions.forEach(op => {
                        const option = document.createElement('option');
                        option.value = op.position_key;
                        option.textContent = op.position_label + (op.item_no ? ` (${op.item_no})` : '');
                        positionSelect.appendChild(option);
                    });

                    positionSelect.disabled = false;
                });
            }
        });
    </script>

    @if($applicant)
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Restore Phase
            const savedPhase = localStorage.getItem('deliberation_phase');
            if (savedPhase && savedPhase !== '{{ $currentPhase }}') {
                switchPhaseUI(savedPhase);
            }

            // Restore Focus Mode
            const savedFocus = localStorage.getItem('deliberation_focus');
            if (savedFocus && savedFocus !== 'split') {
                setFocusMode(savedFocus);
            }

            // Restore Scoring View
            const savedScoringView = localStorage.getItem('deliberation_scoring_view');
            if (savedScoringView && savedScoringView !== 'form') {
                showScoringView(savedScoringView);
            }

            // Intercept applicant chip clicks to pass the phase in the URL, avoiding flash of content
            document.querySelectorAll('.applicant-chip').forEach(chip => {
                chip.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = new URL(this.href);
                    const phase = localStorage.getItem('deliberation_phase') || '{{ $currentPhase }}';
                    url.searchParams.set('phase', phase);
                    window.location.href = url.toString();
                });
            });
        });

        function switchPhaseUI(phase, event) {
            document.getElementById('panel-screening').style.display = 'none';
            document.getElementById('panel-twg_evaluation').style.display = 'none';
            document.getElementById('panel-hrmpsb_deliberation').style.display = 'none';
            document.getElementById('panel-completed').style.display = 'none';
            
            const targetPanel = document.getElementById('panel-' + phase);
            if (targetPanel) targetPanel.style.display = 'block';
            
            const buttons = document.querySelectorAll('.stage-switcher button');
            buttons.forEach(b => b.classList.remove('active', 'fw-bold'));
            
            if (event) {
                event.currentTarget.classList.add('active', 'fw-bold');
            } else {
                const targetBtn = document.querySelector(`.stage-switcher button[data-phase="${phase}"]`);
                if(targetBtn) targetBtn.classList.add('active', 'fw-bold');
            }
            
            localStorage.setItem('deliberation_phase', phase);
        }

        function setFocusMode(mode) {
            const grid = document.getElementById('delibGrid');
            if (!grid) return;
            if (mode === 'profile') {
                grid.style.gridTemplateColumns = '100%';
                document.getElementById('profilePanel').style.display = 'block';
                document.getElementById('scoringPanel').style.display = 'none';
            } else if (mode === 'scoring') {
                grid.style.gridTemplateColumns = '100%';
                document.getElementById('profilePanel').style.display = 'none';
                document.getElementById('scoringPanel').style.display = 'block';
            } else {
                grid.style.gridTemplateColumns = '45% 55%';
                document.getElementById('profilePanel').style.display = 'block';
                document.getElementById('scoringPanel').style.display = 'block';
            }
            
            const toggles = document.querySelectorAll('.focus-toggles button');
            toggles.forEach(b => b.classList.remove('active'));
            
            const targetBtn = document.querySelector(`.focus-toggles button[data-mode="${mode}"]`);
            if(targetBtn) targetBtn.classList.add('active');
            
            localStorage.setItem('deliberation_focus', mode);
        }

        function showScoringView(view) {
            document.getElementById('scoringFormWrapper').style.display = view === 'form' ? 'block' : 'none';
            document.getElementById('panel-scoring-matrix').style.display = view === 'matrix' ? 'block' : 'none';
            document.getElementById('scoringViewFormBtn').classList.toggle('active', view === 'form');
            document.getElementById('scoringViewMatrixBtn').classList.toggle('active', view === 'matrix');
            
            localStorage.setItem('deliberation_scoring_view', view);
        }

        function showDeliberationTab(which) {
            document.getElementById('profilePanel').classList.toggle('active-tab', which === 'profile');
            document.getElementById('scoringPanel').classList.toggle('active-tab', which === 'scoring');
            document.getElementById('tabBtnProfile').classList.toggle('active', which === 'profile');
            document.getElementById('tabBtnScoring').classList.toggle('active', which === 'scoring');
        }
        function applyResponsiveDeliberation() {
            const isMobile = window.innerWidth <= 1024;
            document.getElementById('mobileTabSwitcher').style.display = isMobile ? 'block' : 'none';
        }
        
        let monitoringInterval = null;
        let currentBoardView = 'matrix';
        let lastMonitoringData = null;

        function setBoardView(view) {
            currentBoardView = view;
            document.getElementById('btnViewMatrix').classList.toggle('active', view === 'matrix');
            document.getElementById('btnViewMember').classList.toggle('active', view === 'member');
            document.getElementById('btnViewApplicant').classList.toggle('active', view === 'applicant');
            if (lastMonitoringData) renderMonitoringTable(lastMonitoringData);
        }

        function toggleMonitoringBoard() {
            const board = document.getElementById('monitoringBoard');
            if (board.style.display === 'none') {
                board.style.display = 'block';
                fetchMonitoringData();
                monitoringInterval = setInterval(fetchMonitoringData, 10000); // 10s poll
            } else {
                board.style.display = 'none';
                if (monitoringInterval) clearInterval(monitoringInterval);
            }
        }

        async function fetchMonitoringData() {
            try {
                const res = await fetch('{{ route('recruitment.deliberation.monitoring', $applicant) }}');
                if (!res.ok) return;
                const data = await res.json();
                lastMonitoringData = data;
                renderMonitoringTable(data);
                updateDashboardStrip(data);
                document.getElementById('monitoringLastUpdated').innerText = 'Last updated: ' + new Date().toLocaleTimeString();
            } catch (err) {
                console.error("Failed to fetch monitoring data", err);
            }
        }

        function updateDashboardStrip(data) {
            const phase = '{{ $currentPhase }}';
            let prog = {completed:0, in_progress:0, not_started:0};
            if (phase === 'screening') prog = data.progress.screening;
            else if (phase === 'twg_evaluation') prog = data.progress.twg_evaluation;
            else if (phase === 'hrmpsb_deliberation') prog = data.progress.hrmpsb_deliberation;
            
            document.getElementById('dashCompleted').innerText = prog.completed || 0;
            document.getElementById('dashInProgress').innerText = prog.in_progress || 0;
            document.getElementById('dashNotStarted').innerText = prog.not_started || 0;
            
            const phaseNames = {screening: 'Screening', twg_evaluation: 'TWG', hrmpsb_deliberation: 'HRMPSB', completed: 'Completed'};
            document.getElementById('dashStageName').innerText = phaseNames[phase] || 'Phase';
            document.getElementById('dashboardProgressStrip').style.display = 'flex';
        }

        function renderMonitoringTable(data) {
            const thead = document.getElementById('monitoringTableHead');
            const tbody = document.getElementById('monitoringTableBody');
            
            if (currentBoardView === 'matrix') {
                let headHtml = '<tr><th style="width: 25%;">Panel Member</th>';
                data.applicants.forEach(app => { headHtml += `<th>${app.masked_id}</th>`; });
                headHtml += '</tr>';
                thead.innerHTML = headHtml;

                let bodyHtml = '';
                if (data.members.length === 0) {
                    bodyHtml = `<tr><td colspan="${data.applicants.length + 1}" class="text-muted text-center py-3">No active panel members assigned to this position.</td></tr>`;
                } else {
                    data.members.forEach(member => {
                        bodyHtml += `<tr><td class="text-start fw-bold">${member.name}<div class="text-muted fw-normal" style="font-size:11px;">${member.role}</div></td>`;
                        data.applicants.forEach(app => {
                            const state = data.matrix[member.id]?.[app.id] || 'Not Started';
                            let badgeClass = state === 'Scored' ? 'bg-success' : (state === 'In Progress' ? 'bg-warning text-dark' : 'bg-secondary');
                            bodyHtml += `<td><span class="badge ${badgeClass}">${state}</span></td>`;
                        });
                        bodyHtml += '</tr>';
                    });
                }
                tbody.innerHTML = bodyHtml;
            } else if (currentBoardView === 'member') {
                thead.innerHTML = '<tr><th class="text-start">Panel Member</th><th class="text-start">Progress by Applicant</th></tr>';
                let bodyHtml = '';
                data.members.forEach(member => {
                    let tags = '';
                    data.applicants.forEach(app => {
                        const state = data.matrix[member.id]?.[app.id] || 'Not Started';
                        let badgeClass = state === 'Scored' ? 'bg-success' : (state === 'In Progress' ? 'bg-warning text-dark' : 'bg-secondary');
                        tags += `<span class="badge ${badgeClass} me-1 mb-1">${app.masked_id}: ${state}</span>`;
                    });
                    bodyHtml += `<tr><td class="text-start fw-bold" style="width:30%">${member.name}<div class="text-muted fw-normal" style="font-size:11px;">${member.role}</div></td><td class="text-start">${tags}</td></tr>`;
                });
                tbody.innerHTML = bodyHtml;
            } else if (currentBoardView === 'applicant') {
                thead.innerHTML = '<tr><th class="text-start">Applicant</th><th class="text-start">Progress by Member</th></tr>';
                let bodyHtml = '';
                data.applicants.forEach(app => {
                    let tags = '';
                    data.members.forEach(member => {
                        const state = data.matrix[member.id]?.[app.id] || 'Not Started';
                        let badgeClass = state === 'Scored' ? 'bg-success' : (state === 'In Progress' ? 'bg-warning text-dark' : 'bg-secondary');
                        tags += `<span class="badge ${badgeClass} me-1 mb-1">${member.name}: ${state}</span>`;
                    });
                    bodyHtml += `<tr><td class="text-start fw-bold" style="width:30%">${app.masked_id}</td><td class="text-start">${tags}</td></tr>`;
                });
                tbody.innerHTML = bodyHtml;
            }
        }

        window.addEventListener('resize', applyResponsiveDeliberation);
        document.addEventListener('DOMContentLoaded', applyResponsiveDeliberation);
    </script>

    <!-- Cropper Modal -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <div class="modal fade" id="cropperModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Crop ID Photo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" style="background:#000;">
                    <div style="max-height: 400px; overflow: hidden;">
                        <img id="cropperImage" src="" style="max-width: 100%; display: block;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary fw-bold" id="btnSaveCrop">Save Photo</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let cropper = null;
        let cropperModalElement = null;
        let cropperModal = null;

        document.addEventListener('DOMContentLoaded', () => {
            cropperModalElement = document.getElementById('cropperModal');
            if (typeof bootstrap !== 'undefined') {
                cropperModal = new bootstrap.Modal(cropperModalElement);
            }
            
            cropperModalElement.addEventListener('hidden.bs.modal', () => {
                if (cropper) { cropper.destroy(); cropper = null; }
                document.getElementById('photoInput').value = '';
            });

            document.getElementById('btnSaveCrop').addEventListener('click', async () => {
                if (!cropper) return;
                
                const btn = document.getElementById('btnSaveCrop');
                btn.disabled = true;
                btn.innerText = 'Saving...';

                // Get cropped canvas
                const canvas = cropper.getCroppedCanvas({
                    width: 300,
                    height: 300,
                    minWidth: 300,
                    minHeight: 300,
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high',
                });

                if (!canvas) {
                    alert('Could not crop image. Please try again.');
                    btn.disabled = false;
                    btn.innerText = 'Save Photo';
                    return;
                }

                // Compress as JPEG
                const base64Data = canvas.toDataURL('image/jpeg', 0.85);

                try {
                    const res = await fetch('{{ route('recruitment.deliberation.photo.upload', $applicant) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ photo_data: base64Data })
                    });
                    const json = await res.json();
                    if (res.ok && json.success) {
                        window.location.reload();
                    } else {
                        alert(json.error || 'Failed to upload photo.');
                    }
                } catch (err) {
                    alert('An error occurred during upload.');
                    console.error(err);
                } finally {
                    btn.disabled = false;
                    btn.innerText = 'Save Photo';
                }
            });
        });

        function handlePhotoSelect(event) {
            const files = event.target.files;
            if (!files || files.length === 0) return;
            const file = files[0];
            
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be under 5MB.');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('cropperImage');
                img.src = e.target.result;
                cropperModal.show();
                
                cropperModalElement.addEventListener('shown.bs.modal', function onShown() {
                    cropperModalElement.removeEventListener('shown.bs.modal', onShown);
                    cropper = new Cropper(img, {
                        aspectRatio: 1,
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 1,
                        restore: false,
                        guides: true,
                        center: true,
                        highlight: false,
                        cropBoxMovable: true,
                        cropBoxResizable: true,
                        toggleDragModeOnDblclick: false,
                    });
                });
            };
            reader.readAsDataURL(file);
        }
    </script>
    @endif
</x-dashboard-app>
