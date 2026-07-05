<x-dashboard-app>
    <style>
        .perf-hero {
            background: linear-gradient(135deg, #0f766e 0%, #0d9488 55%, #14b8a6 100%);
            border-radius: 14px;
            padding: 28px 32px;
            position: relative;
            overflow: hidden;
            margin-bottom: 20px;
            color: white;
        }

        .perf-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255, 255, 255, .15) 1px, transparent 1px);
            background-size: 22px 22px;
        }

        .perf-title {
            font-size: 28px;
            font-weight: 800;
            margin: 0 0 8px;
            position: relative;
            z-index: 1;
            letter-spacing: -0.5px;
        }

        .perf-subtitle {
            font-size: 15px;
            opacity: 0.9;
            margin: 0;
            position: relative;
            z-index: 1;
        }

        .card-custom {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
        }

        .table-wrapper {
            max-height: 600px;
            overflow-y: auto;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            position: relative;
        }

        .table-custom {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 13.5px;
        }

        .table-custom th {
            position: sticky;
            top: 0;
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
            padding: 12px 16px;
            border-bottom: 2px solid #cbd5e1;
            z-index: 10;
            white-space: nowrap;
        }

        .table-custom td {
            padding: 12px 16px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
            color: #1e293b;
        }

        .table-custom tbody tr:hover {
            background-color: #f1f5f9;
        }

        .form-control-sm, .form-select-sm {
            font-size: 13px;
            border-radius: 6px;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
        }

        .status-plantilla { background: #dbeafe; color: #1e40af; }
        .status-casual { background: #fef3c7; color: #92400e; }
        .status-jo { background: #f3e8ff; color: #6b21a8; }
        
        .filters-container {
            display: flex;
            gap: 16px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }

        /* Fixed Sidebar & Layout constraints to keep table scrollable */
        .content-container {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 120px);
        }
        
        .flex-grow-wrapper {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }

        .table-wrapper {
            flex-grow: 1;
            overflow-y: auto;
        }
        
        .search-wrapper {
            position: relative;
            flex-grow: 1;
            max-width: 400px;
        }
        .search-wrapper i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }
        .search-wrapper input {
            padding-left: 36px;
        }

        .btn-icon {
            padding: 6px 10px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            background: #f1f5f9;
            color: #475569;
        }
        .btn-icon:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
        .btn-danger-icon {
            background: #fee2e2;
            color: #dc2626;
        }
        .btn-danger-icon:hover {
            background: #fecaca;
            color: #b91c1c;
        }
        .btn-save-icon {
            background: #dcfce7;
            color: #16a34a;
        }
        .btn-save-icon:hover {
            background: #bbf7d0;
            color: #15803d;
        }

        .rating-input {
            width: 70px;
            text-align: center;
        }
        .custom-period-input {
            display: none;
            width: 120px;
            margin-top: 4px;
        }
    </style>

    <div class="content-container">
        <div class="perf-hero">
            <h1 class="perf-title">Performance Management</h1>
            <p class="perf-subtitle">Track and manage Individual Performance Commitment and Review targets and ratings.</p>
        </div>

        {{-- IPCR Stats with compliance analysis --}}
        @php
            $ratedPct  = $ipcrStats['total_plantilla'] > 0 ? round($ipcrStats['rated']/$ipcrStats['total_plantilla']*100,1) : 0;
            $unratedAlert = $ipcrStats['unrated'] > 0;
        @endphp
        <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:10px;margin-bottom:16px;">
            <x-stat-card icon="bi-people-fill" color="slate" label="Total Plantilla" :value="$ipcrStats['total_plantilla']"
                compliance="CSC SPMS"
                analysis="Total filled plantilla positions. All must submit IPCR targets and ratings per CSC Res. No. 1200481 (SPMS Rules)." />

            <x-stat-card icon="bi-clipboard2-check" color="indigo" label="Rated ({{ $currentYear }})" :value="$ipcrStats['rated']"
                :pct="$ratedPct" sub="of total plantilla"
                compliance="CSC SPMS"
                analysis="Employees with IPCR ratings for {{ $currentYear }}. 100% submission rate is required per CSC Res. No. 1200481. Non-submission may result in disciplinary action." />

            <x-stat-card icon="bi-star-fill" color="yellow" label="Outstanding" :value="$ipcrStats['outstanding']"
                compliance="SPMS ≥4.90"
                analysis="Rating ≥4.90 (Outstanding). Employees in this category are eligible for PBB (Performance-Based Bonus) per DBM-CSC Joint Circular 1, s.2012." />

            <x-stat-card icon="bi-hand-thumbs-up-fill" color="green" label="Very Satisfactory" :value="$ipcrStats['very_sat']"
                compliance="SPMS 3.80–4.89"
                analysis="Rating 3.80–4.89. Eligible for PBB at a lower rate. Consistent VS employees may qualify for step increment under NOSI/NOLP rules." />

            <x-stat-card icon="bi-check-circle" color="blue" label="Satisfactory" :value="$ipcrStats['satisfactory']"
                compliance="SPMS 2.50–3.79"
                analysis="Rating 2.50–3.79. Employees must maintain this minimum to retain their positions. Below-satisfactory ratings trigger PIP under CSC rules." />

            <x-stat-card icon="bi-x-circle-fill" color="red" label="Unsatisfactory" :value="$ipcrStats['unsatisfactory']"
                :alert="$ipcrStats['unsatisfactory'] > 0"
                compliance="SPMS <2.50"
                analysis="{{ $ipcrStats['unsatisfactory'] > 0 ? $ipcrStats['unsatisfactory'].' employee(s) rated Unsatisfactory. Two consecutive US ratings is grounds for dismissal per CSC Omnibus Rules §52.' : 'No unsatisfactory ratings recorded for '.$currentYear.'.' }}" />

            <x-stat-card icon="bi-dash-circle-fill" color="orange" label="Unrated" :value="$ipcrStats['unrated']"
                :alert="$unratedAlert"
                compliance="CSC SPMS"
                analysis="{{ $unratedAlert ? $ipcrStats['unrated'].' employee(s) have no IPCR rating for '.$currentYear.'. Non-submission is a CSC violation and bars PBB eligibility (DBM-CSC JC 1, s.2012).' : 'All employees have IPCR ratings for '.$currentYear.'.' }}" />
        </div>

        <div class="card-custom flex-grow-wrapper">
            <div class="filters-container">
                <div class="search-wrapper" style="width: 240px; flex-grow: 0;">
                    <i class="bi bi-search"></i>
                    <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search..." onkeyup="filterTable()">
                </div>

                <div style="width: 250px;">
                    <select id="officeSelect" class="form-select form-select-sm" onchange="fetchEmployees()">
                        <option value="">-- Select Office --</option>
                        @foreach($offices as $office)
                            <option value="{{ $office }}">{{ $office }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="width: 100px;">
                    <select id="yearSelect" class="form-select form-select-sm" onchange="fetchEmployees()">
                        @for($i = date('Y') + 1; $i >= 2020; $i--)
                            <option value="{{ $i }}" {{ $i == $currentYear ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>

                <div style="width: 130px;">
                    <select id="periodSelect" class="form-select form-select-sm" onchange="handleTopPeriodChange()">
                        <option value="jan-jun">Jan - Jun</option>
                        <option value="jul-dec">Jul - Dec</option>
                        <option value="custom">Custom</option>
                    </select>
                </div>

                <div style="width: 120px; display: none;" id="customPeriodContainer">
                    <input type="text" class="form-control form-control-sm" id="customPeriodInput" placeholder="e.g. May-Oct" onchange="fetchEmployees()">
                </div>

                <div style="width: 150px;">
                    <select id="adjectivalSelect" class="form-select form-select-sm" onchange="filterTable()">
                        <option value="">All Ratings</option>
                        <option value="outstanding">Outstanding</option>
                        <option value="very satisfactory">Very Satisfactory</option>
                        <option value="satisfactory">Satisfactory</option>
                        <option value="unsatisfactory">Unsatisfactory</option>
                        <option value="poor">Poor</option>
                    </select>
                </div>

                <div class="ms-auto d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary fw-bold px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="bi bi-file-earmark-arrow-up-fill me-1"></i> Import
                    </button>
                    <button class="btn btn-sm btn-outline-success fw-bold px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#exportModal">
                        <i class="bi bi-file-earmark-excel-fill me-1"></i> Export
                    </button>
                    <button class="btn btn-sm btn-outline-secondary fw-bold px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#settingsModal">
                        <i class="bi bi-gear-fill me-1"></i> Settings
                    </button>
                    <button class="btn btn-sm btn-outline-info fw-bold px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#batchDatesModal">
                        <i class="bi bi-calendar-check-fill me-1"></i> Batch Dates
                    </button>
                    <button class="btn btn-sm btn-success fw-bold px-3 shadow-sm" onclick="batchSaveAll()">
                        <i class="bi bi-floppy me-1"></i> Save All
                    </button>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="text-muted" style="font-size: 13px;" id="employeeCount">0 employees found</div>
            </div>

            <!-- Missing Submissions Alert -->
            <div id="missingSubmissionsAlert" class="alert alert-warning mb-3 shadow-sm border-warning" style="display: none; border-left: 4px solid #f59e0b;">
                <div class="d-flex align-items-center mb-1">
                    <i class="bi bi-exclamation-triangle-fill text-warning fs-5 me-2"></i>
                    <strong class="text-dark">Missing Submissions Detected!</strong>
                </div>
                <div id="missingTargetsList" class="mt-1 small text-dark" style="display: none;">
                    <b>Missing Target IPCR:</b> <span id="missingTargetsNames"></span>
                </div>
                <div id="missingRatingsList" class="mt-1 small text-dark" style="display: none;">
                    <b>Missing Final Ratings:</b> <span id="missingRatingsNames"></span>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="table-custom" id="employeeTable">
                    <thead>
                        <tr>
                            <th>Last Name</th>
                            <th>First Name</th>
                            <th>Middle Name</th>
                            <th>Suffix</th>
                            <th>Position Title</th>
                            <th>Status</th>
                            <th style="width: 130px; text-align: center;">
                                <div class="d-flex flex-column align-items-center gap-1">
                                    <span>Target IPCR</span>
                                    <div class="form-check m-0">
                                        <input class="form-check-input" type="checkbox" id="selectAllTargets" onchange="toggleAllTargets(this)" title="Select/Unselect All Targets">
                                    </div>
                                </div>
                            </th>
                            <th style="width: 130px; text-align: center;">Initial Rating</th>
                            <th style="width: 140px; text-align: center;">Final Rating</th>
                            <th style="width: 150px; text-align: center;">Adjectival Rating</th>
                            <th style="width: 70px; text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="employeeTableBody">
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">Please select an office to view employees.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Export Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="exportForm" action="{{ route('performance.export') }}" method="POST">
                    @csrf
                    <!-- Hidden inputs for current filters -->
                    <input type="hidden" name="office" id="exportOffice">
                    <input type="hidden" name="year" id="exportYear">
                    <input type="hidden" name="period_type" id="exportPeriod">
                    <input type="hidden" name="custom_period" id="exportCustomPeriod">
                    <input type="hidden" name="adjectival_rating" id="exportAdjectival">

                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-excel-fill me-2 text-success"></i>Export to Excel</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-3 pb-4">
                        <p class="text-muted mb-3" style="font-size: 13px;">Select the fields you want to include in the exported report:</p>
                        
                        <div class="row g-2" style="font-size: 14px;">
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="fields[]" value="year" id="field_year" checked>
                                    <label class="form-check-label" for="field_year">Year</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="fields[]" value="period" id="field_period" checked>
                                    <label class="form-check-label" for="field_period">Period</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="fields[]" value="name" id="field_name" checked>
                                    <label class="form-check-label" for="field_name">Employee Name</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="fields[]" value="position" id="field_position" checked>
                                    <label class="form-check-label" for="field_position">Position Title</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="fields[]" value="status" id="field_status" checked>
                                    <label class="form-check-label" for="field_status">Employment Status</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="fields[]" value="target_submitted" id="field_target_sub" checked>
                                    <label class="form-check-label" for="field_target_sub">Target Submitted (Yes/No)</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="fields[]" value="target_date" id="field_target_date">
                                    <label class="form-check-label" for="field_target_date">Target Date</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="fields[]" value="initial_rating" id="field_initial_rating" checked>
                                    <label class="form-check-label" for="field_initial_rating">Initial Rating</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="fields[]" value="rating_date" id="field_rating_date">
                                    <label class="form-check-label" for="field_rating_date">Rating Date</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="fields[]" value="final_rating" id="field_final_rating" checked>
                                    <label class="form-check-label" for="field_final_rating">Final Rating</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="fields[]" value="adjectival_rating" id="field_adjectival_rating" checked>
                                    <label class="form-check-label" for="field_adjectival_rating">Adjectival Rating</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 d-flex gap-2">
                        <button type="button" class="btn btn-light flex-grow-1" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success flex-grow-1" onclick="submitExport()">Download Excel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-arrow-up-fill me-2 text-primary"></i>Import Ratings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3 pb-4">
                    <div class="alert alert-info py-2" style="font-size: 13px;">
                        <strong>Step 1:</strong> Download the template for the currently selected Office, Year, and Period.
                        <div class="mt-2 text-center">
                            <button type="button" class="btn btn-sm btn-primary" onclick="downloadImportTemplate()">
                                <i class="bi bi-download me-1"></i> Download Template
                            </button>
                        </div>
                    </div>
                    <div class="alert alert-secondary py-2 mb-0" style="font-size: 13px;">
                        <strong>Step 2:</strong> Fill the template and upload it below. <br/>
                        <em class="text-danger">Do not modify the Employee ID column.</em>
                        <div class="mt-3">
                            <input class="form-control form-control-sm" type="file" id="importFileInput" accept=".xlsx,.xls,.csv">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 d-flex gap-2">
                    <button type="button" class="btn btn-light flex-grow-1" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success flex-grow-1" id="uploadRatingsBtn" onclick="uploadRatings()">Upload & Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Modal -->
    <div class="modal fade" id="settingsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-gear-fill me-2"></i>Performance Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3 pb-4">
                    <h6 class="fw-bold mb-3 text-primary border-bottom pb-2">Penalty Deductions</h6>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label" style="font-size: 12px; font-weight: 600; color: #64748b;">Target Late Penalty</label>
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="targetPenaltyInput" value="{{ $targetPenalty }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label" style="font-size: 12px; font-weight: 600; color: #64748b;">Rating Late Penalty</label>
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="ratingPenaltyInput" value="{{ $ratingPenalty }}">
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3 mt-4 text-primary border-bottom pb-2">Adjectival Rating Thresholds</h6>
                    <div class="row">
                        <div class="col-6 mb-2">
                            <label class="form-label" style="font-size: 12px; font-weight: 600; color: #64748b;">Poor (<= x)</label>
                            <input type="number" step="0.01" class="form-control form-control-sm" id="threshPoorInput" value="{{ $threshPoor }}">
                        </div>
                        <div class="col-6 mb-2">
                            <label class="form-label" style="font-size: 12px; font-weight: 600; color: #64748b;">Unsatisfactory (< x)</label>
                            <input type="number" step="0.01" class="form-control form-control-sm" id="threshUnsatInput" value="{{ $threshUnsat }}">
                        </div>
                        <div class="col-6 mb-2">
                            <label class="form-label" style="font-size: 12px; font-weight: 600; color: #64748b;">Satisfactory (< x)</label>
                            <input type="number" step="0.01" class="form-control form-control-sm" id="threshSatInput" value="{{ $threshSat }}">
                        </div>
                        <div class="col-6 mb-2">
                            <label class="form-label" style="font-size: 12px; font-weight: 600; color: #64748b;">Very Satisfactory (< x)</label>
                            <input type="number" step="0.01" class="form-control form-control-sm" id="threshVsatInput" value="{{ $threshVsat }}">
                        </div>
                        <div class="col-12 mt-1">
                            <small class="text-muted" style="font-size: 11px;"><em>Outstanding is applied if rating is equal or above Very Satisfactory threshold.</em></small>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3 mt-4 text-primary border-bottom pb-2">Deadlines (MM-DD)</h6>
                    <div class="row">
                        <div class="col-6 mb-2">
                            <label class="form-label" style="font-size: 12px; font-weight: 600; color: #64748b;">Jan-Jun Target Deadline</label>
                            <input type="text" class="form-control form-control-sm" id="jjTargetInput" value="{{ $jjTarget }}" placeholder="12-15">
                        </div>
                        <div class="col-6 mb-2">
                            <label class="form-label" style="font-size: 12px; font-weight: 600; color: #64748b;">Jan-Jun Rating Deadline</label>
                            <input type="text" class="form-control form-control-sm" id="jjRatingInput" value="{{ $jjRating }}" placeholder="07-30">
                        </div>
                        <div class="col-6 mb-2">
                            <label class="form-label" style="font-size: 12px; font-weight: 600; color: #64748b;">Jul-Dec Target Deadline</label>
                            <input type="text" class="form-control form-control-sm" id="jdTargetInput" value="{{ $jdTarget }}" placeholder="06-15">
                        </div>
                        <div class="col-6 mb-2">
                            <label class="form-label" style="font-size: 12px; font-weight: 600; color: #64748b;">Jul-Dec Rating Deadline</label>
                            <input type="text" class="form-control form-control-sm" id="jdRatingInput" value="{{ $jdRating }}" placeholder="01-30">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 d-flex gap-2">
                    <button type="button" class="btn btn-light flex-grow-1" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary flex-grow-1" id="saveSettingsBtn" onclick="saveSettings()">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Dates Modal -->
    <div class="modal fade" id="batchDatesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-calendar-check-fill me-2 text-info"></i>Batch Set Dates</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3 pb-4">
                    <p class="text-muted" style="font-size: 13px;">Apply a submission date to all visible employees in the table.</p>
                    <div class="mb-3">
                        <label class="form-label" style="font-size: 12px; font-weight: 600;">Target Submission Date</label>
                        <input type="date" class="form-control form-control-sm" id="batchTargetDate">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size: 12px; font-weight: 600;">Rating Submission Date</label>
                        <input type="date" class="form-control form-control-sm" id="batchRatingDate">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 d-flex gap-2">
                    <button type="button" class="btn btn-light flex-grow-1" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-info text-white flex-grow-1 fw-bold" onclick="applyBatchDates()">Apply</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentEmployees = [];
        let deleteId = null;

        function fetchEmployees() {
            const office = document.getElementById('officeSelect').value;
            const year = document.getElementById('yearSelect').value;
            const tbody = document.getElementById('employeeTableBody');
            const countLabel = document.getElementById('employeeCount');
            const periodType = document.getElementById('periodSelect').value;
            const customPeriod = document.getElementById('customPeriodInput').value;

            if (!office) {
                tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-4">Please select an office to view employees.</td></tr>';
                countLabel.textContent = '0 employees found';
                currentEmployees = [];
                return;
            }

            tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></td></tr>';

            fetch(`{{ route('performance.employees') }}?office=${encodeURIComponent(office)}&year=${year}&period_type=${periodType}&custom_period=${encodeURIComponent(customPeriod)}`)
                .then(res => res.json())
                .then(data => {
                    currentEmployees = data;
                    renderTable(data);
                })
                .catch(err => {
                    console.error('Error fetching employees:', err);
                    tbody.innerHTML = '<tr><td colspan="10" class="text-center text-danger py-4">Error loading data.</td></tr>';
                });
        }

        function getStatusBadgeClass(status) {
            status = (status || '').toUpperCase();
            if (status === 'CASUAL') return 'status-casual';
            if (status === 'JO') return 'status-jo';
            return 'status-plantilla';
        }

        function renderTable(data) {
            const tbody = document.getElementById('employeeTableBody');
            const countLabel = document.getElementById('employeeCount');
            const selectAllTargets = document.getElementById('selectAllTargets');
            
            countLabel.textContent = `${data.length} employees found`;
            selectAllTargets.checked = false;

            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-4">No employees found for this office.</td></tr>';
                return;
            }

            let html = '';
            let allSubmitted = true;
            
            const yearVal = parseInt(document.getElementById('yearSelect').value);
            const pType = document.getElementById('periodSelect').value;
            const today = new Date();
            let tDeadline = null;
            let rDeadline = null;

            if (pType === 'jan-jun') {
                tDeadline = new Date(yearVal - 1, 11, 15);
                rDeadline = new Date(yearVal, 6, 30);
            } else if (pType === 'jul-dec') {
                tDeadline = new Date(yearVal, 5, 15);
                rDeadline = new Date(yearVal + 1, 0, 30);
            }

            let missingTargets = [];
            let missingRatings = [];

            data.forEach((emp, index) => {
                let ratingVal = '';
                let targetChecked = '';
                let finalRatingVal = '-';
                let adjRatingVal = '-';
                let targetDateVal = '';
                let ratingDateVal = '';
                let periodType = 'jan-jun';
                let customPeriod = '';

                const r = emp.ratings ? emp.ratings.find(x => x.year == yearVal && x.period_type == pType) : null;
                
                if (r) {
                    targetChecked = r.target_submitted ? 'checked' : '';
                    if (!r.target_submitted) allSubmitted = false;
                    targetDateVal = r.target_submission_date ? r.target_submission_date.split('T')[0] : '';
                    ratingVal = r.rating || '';
                    ratingDateVal = r.rating_submission_date ? r.rating_submission_date.split('T')[0] : '';
                    finalRatingVal = r.final_rating !== null ? parseFloat(r.final_rating).toFixed(2) : '-';
                    adjRatingVal = r.adjectival_rating || '-';
                    periodType = r.period_type;
                } else {
                    allSubmitted = false;
                }

                let rowFlagStyle = '';
                const fullName = `${emp.first_name || ''} ${emp.last_name || ''}`.trim();
                
                if (tDeadline && today > tDeadline && !targetChecked) {
                    missingTargets.push(fullName);
                    rowFlagStyle = 'background-color: #fffbeb; border-left: 3px solid #f59e0b;';
                }
                
                if (rDeadline && today > rDeadline && !ratingVal) {
                    missingRatings.push(fullName);
                    rowFlagStyle = 'background-color: #fffbeb; border-left: 3px solid #f59e0b;';
                }

                html += `
                    <tr data-emp-id="${emp.id}" id="row-${emp.id}" style="${rowFlagStyle}" data-adj="${(adjRatingVal||'').toLowerCase()}">
                        <td>${emp.last_name || ''}</td>
                        <td>${emp.first_name || ''}</td>
                        <td>${emp.middle_name || ''}</td>
                        <td>${emp.name_extension || ''}</td>
                        <td>${emp.position_title || ''}</td>
                        <td><span class="status-badge ${getStatusBadgeClass(emp.employment_status)}">${emp.employment_status || 'REGULAR'}</span></td>
                        <td style="text-align: center;">
                            <input class="form-check-input target-checkbox" type="checkbox" data-id="${emp.id}" ${targetChecked} onchange="updateRowState(${emp.id})">
                            <input type="date" class="form-control form-control-sm mt-1" style="font-size: 11px; padding: 2px 4px; width: 100px; margin: 0 auto;" id="target-date-${emp.id}" value="${targetDateVal}" onchange="updateRowState(${emp.id})" title="Target Submission Date">
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" max="5" class="form-control form-control-sm rating-input mb-1" value="${ratingVal}" placeholder="Score" onchange="updateRowState(${emp.id})" id="rating-${emp.id}" ${targetChecked ? '' : 'disabled'}>
                            <input type="date" class="form-control form-control-sm" style="font-size: 11px; padding: 2px 4px; width: 100px;" id="rating-date-${emp.id}" value="${ratingDateVal}" onchange="updateRowState(${emp.id})" title="Rating Submission Date">
                        </td>
                        <td style="text-align: center;">
                            <div style="font-weight: bold; font-size: 15px; color: ${finalRatingVal !== '-' && finalRatingVal < ratingVal ? '#dc2626' : '#1e293b'}">
                                ${finalRatingVal}
                            </div>
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            <span style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">
                                ${adjRatingVal}
                            </span>
                        </td>
                        <td style="text-align: center; white-space: nowrap;">
                            <button class="btn-icon btn-save-icon" onclick="saveRating(${emp.id})" title="Save Rating"><i class="bi bi-floppy"></i></button>
                            <a href="/plantilla/${emp.id}/edit" class="btn-icon btn-danger-icon" title="Terminate/Remove Employee"><i class="bi bi-person-dash"></i></a>
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
            
            if(data.length > 0 && allSubmitted) {
                selectAllTargets.checked = true;
            }

            const alertBox = document.getElementById('missingSubmissionsAlert');
            const targetList = document.getElementById('missingTargetsList');
            const ratingList = document.getElementById('missingRatingsList');
            
            if (missingTargets.length > 0 || missingRatings.length > 0) {
                alertBox.style.display = 'block';
                if (missingTargets.length > 0) {
                    targetList.style.display = 'block';
                    document.getElementById('missingTargetsNames').textContent = missingTargets.join(', ');
                } else { targetList.style.display = 'none'; }
                if (missingRatings.length > 0) {
                    ratingList.style.display = 'block';
                    document.getElementById('missingRatingsNames').textContent = missingRatings.join(', ');
                } else { ratingList.style.display = 'none'; }
            } else { alertBox.style.display = 'none'; }
        }

        function filterTable() {
            const term = document.getElementById('searchInput').value.toLowerCase();
            const adjFilter = document.getElementById('adjectivalSelect').value.toLowerCase();
            const rows = document.querySelectorAll('#employeeTableBody tr');
            rows.forEach(row => {
                if (row.cells.length < 5) return;
                const text = row.innerText.toLowerCase();
                const adjVal = row.getAttribute('data-adj') || '';
                
                let matchesSearch = text.includes(term);
                let matchesAdj = (adjFilter === '' || adjVal === adjFilter);
                
                row.style.display = (matchesSearch && matchesAdj) ? '' : 'none';
            });
        }

        function handleTopPeriodChange() {
            const select = document.getElementById('periodSelect');
            const customInputContainer = document.getElementById('customPeriodContainer');
            if (select.value === 'custom') {
                customInputContainer.style.display = 'block';
            } else {
                customInputContainer.style.display = 'none';
                fetchEmployees();
            }
        }

        function updateRowState(id) {
            const row = document.getElementById(`row-${id}`);
            if(row) {
                row.style.borderLeft = '3px solid #f59e0b';
                const targetCheckbox = row.querySelector('.target-checkbox');
                const ratingInput = document.getElementById(`rating-${id}`);
                if (targetCheckbox && ratingInput) {
                    ratingInput.disabled = !targetCheckbox.checked;
                    if (!targetCheckbox.checked) ratingInput.value = '';
                }
            }
        }

        function clearRowState(id) {
            const row = document.getElementById(`row-${id}`);
            if(row) row.style.borderLeft = 'none';
        }

        function saveRating(id) {
            const row = document.getElementById(`row-${id}`);
            const targetCheckbox = row.querySelector('.target-checkbox');
            const ratingInput = document.getElementById(`rating-${id}`);
            const periodType = document.getElementById('periodSelect').value;
            const customPeriod = document.getElementById('customPeriodInput').value;
            const year = document.getElementById('yearSelect').value;

            const payload = {
                _token: '{{ csrf_token() }}',
                plantilla_record_id: id,
                year: year,
                target_submitted: targetCheckbox.checked ? 1 : 0,
                target_submission_date: document.getElementById(`target-date-${id}`).value || null,
                rating: ratingInput.value || null,
                rating_submission_date: document.getElementById(`rating-date-${id}`).value || null,
                period_type: periodType,
                custom_period: periodType === 'custom' ? customPeriod : null
            };

            const btn = row.querySelector('.btn-save-icon');
            const origHtml = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
            btn.disabled = true;

            fetch('{{ route('performance.save') }}', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                btn.innerHTML = '<i class="bi bi-check-lg"></i>';
                clearRowState(id);
                fetchEmployees();
                setTimeout(() => { btn.innerHTML = origHtml; btn.disabled = false; }, 1500);
            })
            .catch(err => { console.error(err); alert('Error saving data.'); btn.innerHTML = origHtml; btn.disabled = false; });
        }

        function toggleAllTargets(checkbox) {
            const isChecked = checkbox.checked;
            const rows = document.querySelectorAll('#employeeTableBody tr');
            
            rows.forEach(row => {
                if (row.style.display !== 'none' && row.hasAttribute('data-emp-id')) {
                    const cb = row.querySelector('.target-checkbox');
                    if (cb) {
                        cb.checked = isChecked;
                        updateRowState(row.getAttribute('data-emp-id'));
                    }
                }
            });
        }

        function applyBatchDates() {
            const tDate = document.getElementById('batchTargetDate').value;
            const rDate = document.getElementById('batchRatingDate').value;

            if (!tDate && !rDate) {
                alert('Please enter at least one date before applying.');
                return;
            }

            const rows = document.querySelectorAll('#employeeTableBody tr');
            let applied = 0;

            rows.forEach(row => {
                if (row.style.display !== 'none' && row.hasAttribute('data-emp-id')) {
                    const id = row.getAttribute('data-emp-id');
                    if (tDate) {
                        const targetInput = document.getElementById(`target-date-${id}`);
                        if (targetInput) targetInput.value = tDate;
                    }
                    if (rDate) {
                        const ratingInput = document.getElementById(`rating-date-${id}`);
                        if (ratingInput) ratingInput.value = rDate;
                    }
                    applied++;
                }
            });

            if (applied === 0) {
                alert('No employees loaded. Please select an office first.');
                return;
            }

            // Close modal
            const modalEl = document.getElementById('batchDatesModal');
            (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl)).hide();

            // Persist immediately — read current row state without touching rating values
            const year = document.getElementById('yearSelect').value;
            const periodType = document.getElementById('periodSelect').value;
            const customPeriod = document.getElementById('customPeriodInput').value;
            const ratings = [];

            rows.forEach(row => {
                if (row.style.display !== 'none' && row.hasAttribute('data-emp-id')) {
                    const id = row.getAttribute('data-emp-id');
                    const cb = row.querySelector('.target-checkbox');
                    ratings.push({
                        employee_id: id,
                        target_submitted: cb && cb.checked ? 1 : 0,
                        target_submission_date: document.getElementById(`target-date-${id}`)?.value || null,
                        rating: document.getElementById(`rating-${id}`)?.value || null,
                        rating_submission_date: document.getElementById(`rating-date-${id}`)?.value || null,
                    });
                }
            });

            const applyBtn = document.querySelector('#batchDatesModal .btn-info');
            if (applyBtn) { applyBtn.disabled = true; applyBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>'; }

            fetch('{{ route('performance.batch-save') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ year, period_type: periodType, custom_period: customPeriod, ratings })
            })
            .then(res => res.json())
            .then(() => fetchEmployees())
            .catch(err => {
                console.error(err);
                alert('Error saving batch dates.');
            })
            .finally(() => {
                if (applyBtn) { applyBtn.disabled = false; applyBtn.innerHTML = 'Apply'; }
            });
        }

        function batchSaveAll() {
            const rows = document.querySelectorAll('#employeeTableBody tr');
            const ratings = [];
            const year = document.getElementById('yearSelect').value;
            const periodType = document.getElementById('periodSelect').value;
            const customPeriod = document.getElementById('customPeriodInput').value;

            rows.forEach(row => {
                if (row.hasAttribute('data-emp-id') && row.style.display !== 'none') {
                    const id = row.getAttribute('data-emp-id');
                    const targetChecked = row.querySelector('.target-checkbox').checked;
                    const ratingVal = document.getElementById(`rating-${id}`).value;
                    const targetDateVal = document.getElementById(`target-date-${id}`).value;
                    const ratingDateVal = document.getElementById(`rating-date-${id}`).value;
                    
                    ratings.push({
                        employee_id: id,
                        target_submitted: targetChecked ? 1 : 0,
                        target_submission_date: targetDateVal || null,
                        rating: ratingVal || null,
                        rating_submission_date: ratingDateVal || null
                    });
                }
            });

            if(ratings.length === 0) return;

            const btn = document.querySelector('.btn-success');
            const origHtml = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Saving...';
            btn.disabled = true;

            fetch('{{ route('performance.batch-save') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    _token: '{{ csrf_token() }}',
                    year: year,
                    period_type: periodType,
                    custom_period: customPeriod,
                    ratings: ratings
                })
            })
            .then(res => res.json())
            .then(data => {
                btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Saved!';
                ratings.forEach(r => clearRowState(r.employee_id));
                // Re-fetch to update all final ratings
                fetchEmployees();
                setTimeout(() => {
                    btn.innerHTML = origHtml;
                    btn.disabled = false;
                }, 1500);
            })
            .catch(err => {
                console.error(err);
                alert('Error batch saving targets/ratings.');
                btn.innerHTML = origHtml;
                btn.disabled = false;
            });
        }



        function saveSettings() {
            const btn = document.getElementById('saveSettingsBtn');
            const targetPenalty = document.getElementById('targetPenaltyInput').value;
            const ratingPenalty = document.getElementById('ratingPenaltyInput').value;
            const threshPoor = document.getElementById('threshPoorInput').value;
            const threshUnsat = document.getElementById('threshUnsatInput').value;
            const threshSat = document.getElementById('threshSatInput').value;
            const threshVsat = document.getElementById('threshVsatInput').value;

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

            fetch('{{ route('performance.settings.save') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    _token: '{{ csrf_token() }}',
                    target_penalty: targetPenalty,
                    rating_penalty: ratingPenalty,
                    thresh_poor: threshPoor,
                    thresh_unsat: threshUnsat,
                    thresh_sat: threshSat,
                    thresh_vsat: threshVsat,
                    jan_jun_target_deadline: document.getElementById('jjTargetInput').value,
                    jan_jun_rating_deadline: document.getElementById('jjRatingInput').value,
                    jul_dec_target_deadline: document.getElementById('jdTargetInput').value,
                    jul_dec_rating_deadline: document.getElementById('jdRatingInput').value
                })
            })
            .then(res => {
                if (!res.ok) throw new Error('Server returned ' + res.status);
                return res.json();
            })
            .then(() => {
                btn.disabled = false;
                btn.innerHTML = 'Save Changes';
                const modalEl = document.getElementById('settingsModal');
                (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl)).hide();
                fetchEmployees();
            })
            .catch(err => {
                console.error(err);
                alert('Error saving settings.');
                btn.disabled = false;
                btn.innerHTML = 'Save Changes';
            });
        }

        function submitExport() {
            const office = document.getElementById('officeSelect').value;
            if (!office) {
                alert("Please select an office first.");
                return;
            }

            document.getElementById('exportOffice').value = office;
            document.getElementById('exportYear').value = document.getElementById('yearSelect').value;
            document.getElementById('exportPeriod').value = document.getElementById('periodSelect').value;
            document.getElementById('exportCustomPeriod').value = document.getElementById('customPeriodInput').value;
            document.getElementById('exportAdjectival').value = document.getElementById('adjectivalSelect').value;

            // Submit the form normally so the browser downloads the file
            document.getElementById('exportForm').submit();
            bootstrap.Modal.getInstance(document.getElementById('exportModal')).hide();
        }

        function downloadImportTemplate() {
            const office = document.getElementById('officeSelect').value;
            const year = document.getElementById('yearSelect').value;
            const periodType = document.getElementById('periodSelect').value;
            const customPeriod = document.getElementById('customPeriodInput').value;

            if (!office) {
                alert('Please select an office first to download the template.');
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('performance.import-template') }}';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            const inputs = { office, year, period_type: periodType };
            if (periodType === 'custom') inputs.custom_period = customPeriod;

            for (const [key, value] of Object.entries(inputs)) {
                const i = document.createElement('input');
                i.type = 'hidden'; i.name = key; i.value = value;
                form.appendChild(i);
            }

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }

        function uploadRatings() {
            const year = document.getElementById('yearSelect').value;
            const periodType = document.getElementById('periodSelect').value;
            const customPeriod = document.getElementById('customPeriodInput').value;
            const fileInput = document.getElementById('importFileInput');

            if (!fileInput.files.length) {
                alert('Please select a file to upload.');
                return;
            }

            const btn = document.getElementById('uploadRatingsBtn');
            const origHtml = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Uploading...';
            btn.disabled = true;

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('year', year);
            formData.append('period_type', periodType);
            if (periodType === 'custom') formData.append('custom_period', customPeriod);
            formData.append('import_file', fileInput.files[0]);

            fetch('{{ route('performance.import') }}', {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Ratings imported successfully!');
                    bootstrap.Modal.getInstance(document.getElementById('importModal')).hide();
                    fetchEmployees(); 
                } else {
                    alert('Error: ' + (data.message || 'Failed to import ratings.'));
                }
            })
            .catch(err => {
                console.error(err);
                alert('An error occurred during upload.');
            })
            .finally(() => {
                btn.innerHTML = origHtml;
                btn.disabled = false;
                fileInput.value = '';
            });
        }
    </script>
</x-dashboard-app>
