<x-dashboard-app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Recruitment') }}
        </h2>
    </x-slot>

    <div class="content-wrapper p-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {!! session('success') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0 text-primary"><i class="bi bi-people-fill me-2"></i>Applicant Records</h4>
            <div>
                @unless(auth()->user()->hasRole('Appointment Encoder'))
                <button type="button" class="btn btn-danger fw-bold shadow-sm me-2" data-bs-toggle="modal" data-bs-target="#reportModal">
                    <i class="bi bi-file-pdf-fill me-1"></i> Generate Report
                </button>
                <a href="{{ route('recruitment.deliberation.agenda.create') }}" class="btn btn-outline-dark fw-bold shadow-sm me-2">
                    <i class="bi bi-card-list me-1"></i> Generate Agenda
                </a>
                <a href="{{ route('recruitment.import-excel') }}" class="btn btn-success fw-bold shadow-sm me-2">
                    <i class="bi bi-file-earmark-spreadsheet-fill me-1"></i> Import Excel
                </a>
                <button type="button" class="btn btn-outline-success fw-bold shadow-sm me-2" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bi bi-file-earmark-arrow-up-fill me-1"></i> Import CSV
                </button>
                @endunless
                <a href="{{ route('recruitment.create') }}" class="btn btn-primary fw-bold shadow-sm">
                    <i class="bi bi-plus-circle me-1"></i> New Application
                </a>
            </div>
        </div>

        <!-- Premium Dynamic Statistics -->
        <style>
            .stat-card {
                position: relative;
                border-radius: 16px;
                padding: 20px;
                overflow: hidden;
                color: #fff;
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                display: flex;
                align-items: center;
                justify-content: space-between;
                height: 100%;
                z-index: 1;
            }
            .stat-card::before {
                content: '';
                position: absolute;
                inset: 0;
                background-image: linear-gradient(120deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 100%);
                z-index: -1;
                pointer-events: none;
            }
            .stat-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            }
            .stat-card-icon {
                font-size: 2.5rem;
                opacity: 0.25;
                transition: transform 0.3s ease;
            }
            .stat-card:hover .stat-card-icon {
                transform: scale(1.15) rotate(5deg);
                opacity: 0.4;
            }
            .stat-card-title {
                font-size: 0.75rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                opacity: 0.85;
                margin-bottom: 4px;
            }
            .stat-card-value {
                font-size: 2rem;
                font-weight: 800;
                line-height: 1.1;
                margin: 0;
            }
            .bg-gradient-total { background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); }
            .bg-gradient-male { background: linear-gradient(135deg, #0f766e 0%, #06b6d4 100%); }
            .bg-gradient-female { background: linear-gradient(135deg, #9d174d 0%, #ec4899 100%); }
            .bg-gradient-qualified { background: linear-gradient(135deg, #166534 0%, #22c55e 100%); }
            .bg-gradient-disqualified { background: linear-gradient(135deg, #7f1d1d 0%, #ef4444 100%); }
            .bg-gradient-pending { background: linear-gradient(135deg, #b45309 0%, #f59e0b 100%); }
        </style>

        @php
            $recTotal   = $stats['total']        ?? 0;
            $recMale    = $stats['male']          ?? 0;
            $recFemale  = $stats['female']        ?? 0;
            $recQual    = $stats['qualified']     ?? 0;
            $recDisq    = $stats['disqualified']  ?? 0;
            $recPend    = $stats['pending']       ?? 0;
            $recGadTotal= $recMale + $recFemale;
            $recFPct    = $recGadTotal > 0 ? round($recFemale/$recGadTotal*100,1) : 0;
            $recGadOk   = $recFPct >= 40 && $recFPct <= 60;
            $qualPct    = $recTotal > 0 ? round($recQual/$recTotal*100,1) : 0;
        @endphp
        <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:10px;margin-bottom:14px;">
            <x-stat-card icon="bi-person-plus-fill" color="indigo" label="Total Applicants" :value="$recTotal"
                compliance="CSC Rules"
                analysis="All applicant records subject to CSC Omnibus Rules on Appointments. Records must be secured per RA 10173 (Data Privacy Act)." />
            <x-stat-card icon="bi-gender-male" color="blue" label="Male" :value="$recMale"
                :pct="$recGadTotal>0?round($recMale/$recGadTotal*100,1):0"
                compliance="RA 9710 GAD"
                analysis="Male applicant pool. Recruitment must be free of gender bias per RA 9710 and GAD Focal Point System guidelines." />
            <x-stat-card icon="bi-gender-female" color="pink" label="Female" :value="$recFemale"
                :pct="$recFPct"
                compliance="RA 9710 GAD"
                :alert="!$recGadOk && $recGadTotal>0"
                analysis="{{ $recGadOk || $recGadTotal==0 ? 'Applicant pool is GAD-balanced ('.$recFPct.'% female) per RA 9710.' : 'Pool not GAD-balanced ('.$recFPct.'% female). Review sourcing to broaden talent base (RA 9710 §12).' }}" />
            <x-stat-card icon="bi-check-circle-fill" color="green" label="Qualified" :value="$recQual"
                :pct="$qualPct" sub="of total applicants"
                compliance="CSC Omnibus Rules"
                analysis="Met minimum QS per CSC Omnibus Rules. QS verification must be documented in the 201 file and HRMPSB minutes." />
            <x-stat-card icon="bi-x-circle-fill" color="red" label="Disqualified" :value="$recDisq"
                :alert="$recDisq > 0"
                compliance="CSC Rules"
                analysis="Disqualification must be communicated in writing with specific grounds per CSC MC 3, s.2001. Right to appeal applies." />
            <x-stat-card icon="bi-hourglass-split" color="orange" label="Pending Eval" :value="$recPend"
                compliance="HRMPSB"
                analysis="Pending HRMPSB screening. CSC requires completion within 30 days from deadline to avoid protest action." />
        </div>

        {{-- Legacy cards hidden but kept for backward compatibility --}}
        <div class="row g-3 mb-4" style="display:none">
            <div class="col-6 col-md-4 col-lg-2">
                <div class="stat-card bg-gradient-total">
                    <div>
                        <div class="stat-card-title">Total</div>
                        <div class="stat-card-value">{{ number_format($stats['total'] ?? 0) }}</div>
                    </div>
                    <i class="bi bi-people-fill stat-card-icon"></i>
                </div>
            </div>
            
            <div class="col-6 col-md-4 col-lg-2">
                <div class="stat-card bg-gradient-male">
                    <div>
                        <div class="stat-card-title">Male</div>
                        <div class="stat-card-value">{{ number_format($stats['male'] ?? 0) }}</div>
                    </div>
                    <i class="bi bi-gender-male stat-card-icon"></i>
                </div>
            </div>

            <div class="col-6 col-md-4 col-lg-2">
                <div class="stat-card bg-gradient-female">
                    <div>
                        <div class="stat-card-title">Female</div>
                        <div class="stat-card-value">{{ number_format($stats['female'] ?? 0) }}</div>
                    </div>
                    <i class="bi bi-gender-female stat-card-icon"></i>
                </div>
            </div>

            <div class="col-6 col-md-4 col-lg-2">
                <div class="stat-card bg-gradient-qualified">
                    <div>
                        <div class="stat-card-title">Qualified</div>
                        <div class="stat-card-value">{{ number_format($stats['qualified'] ?? 0) }}</div>
                    </div>
                    <i class="bi bi-check-circle-fill stat-card-icon"></i>
                </div>
            </div>

            <div class="col-6 col-md-4 col-lg-2">
                <div class="stat-card bg-gradient-disqualified">
                    <div>
                        <div class="stat-card-title">Disqualified</div>
                        <div class="stat-card-value">{{ number_format($stats['disqualified'] ?? 0) }}</div>
                    </div>
                    <i class="bi bi-x-circle-fill stat-card-icon"></i>
                </div>
            </div>

            <div class="col-6 col-md-4 col-lg-2">
                <div class="stat-card bg-gradient-pending">
                    <div>
                        <div class="stat-card-title">Pending</div>
                        <div class="stat-card-value">{{ number_format($stats['pending'] ?? 0) }}</div>
                    </div>
                    <i class="bi bi-hourglass-split stat-card-icon"></i>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
            <div class="card-body p-3 bg-light" style="border-radius: 12px;">
                <form action="{{ route('recruitment.index') }}" method="GET" class="row g-2 align-items-end">
                    
                    <div class="col-md-3">
                        <label class="form-label mb-1" style="font-size: 12px; font-weight: 600;">Search Name/Ref</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." value="{{ request('search') }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label mb-1" style="font-size: 12px; font-weight: 600;">Position Applied</label>
                        <select name="position_applied" class="form-select form-select-sm">
                            <option value="">All</option>
                            @foreach($positions as $pos)
                                <option value="{{ $pos }}" {{ request('position_applied') == $pos ? 'selected' : '' }}>{{ $pos }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label mb-1" style="font-size: 12px; font-weight: 600;">Office</label>
                        <select name="office" class="form-select form-select-sm">
                            <option value="">All</option>
                            @foreach($offices as $off)
                                <option value="{{ $off }}" {{ request('office') == $off ? 'selected' : '' }}>{{ $off }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label mb-1" style="font-size: 12px; font-weight: 600;">Highest Education</label>
                        <select name="highest_educational_attainment" class="form-select form-select-sm">
                            <option value="">All</option>
                            @foreach($educations as $edu)
                                <option value="{{ $edu }}" {{ request('highest_educational_attainment') == $edu ? 'selected' : '' }}>{{ $edu }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 d-flex gap-2">
                        <div class="flex-grow-1">
                            <label class="form-label mb-1" style="font-size: 12px; font-weight: 600;">Degree</label>
                            <input type="text" name="degree" class="form-control form-control-sm" placeholder="Any degree..." value="{{ request('degree') }}">
                        </div>
                        <div class="flex-grow-1">
                            <label class="form-label mb-1" style="font-size: 12px; font-weight: 600;">Eligibility</label>
                            <input type="text" name="eligibility" class="form-control form-control-sm" placeholder="Any..." value="{{ request('eligibility') }}">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label mb-1" style="font-size: 12px; font-weight: 600;">Sex</label>
                        <select name="sex" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="Male" {{ request('sex') == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ request('sex') == 'Female' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold"><i class="bi bi-funnel-fill"></i> Filter</button>
                        <a href="{{ route('recruitment.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-body p-0 table-responsive" style="border-radius: 12px;">
                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Reference No</th>
                            <th>AIN</th>
                            <th>Applicant Name</th>
                            <th>Sex</th>
                            <th>Position Applied</th>
                            <th>Item No.</th>
                            <th>Office</th>
                            <th>Contact Info</th>
                            <th>Date Applied</th>
                            <th class="text-center pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applicants as $applicant)
                            <tr>
                                <td class="ps-3 fw-bold text-primary">{{ $applicant->reference_no }}</td>
                                <td>{{ $applicant->ain }}</td>
                                <td class="fw-bold">{{ $applicant->full_name }}</td>
                                <td>{{ $applicant->sex }}</td>
                                <td>{{ $applicant->position_applied }}</td>
                                <td>
                                    @if($applicant->item_no)
                                        <span class="badge bg-secondary">{{ $applicant->item_no }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $applicant->office ?: '-' }}</td>
                                <td>
                                    <div><i class="bi bi-telephone-fill me-1 text-muted"></i>{{ $applicant->phone_number }}</div>
                                    <div class="text-muted"><i class="bi bi-envelope-fill me-1 text-muted"></i>{{ $applicant->email_address }}</div>
                                </td>
                                <td>{{ $applicant->created_at->format('M d, Y h:i A') }}</td>
                                <td class="text-center pe-3">
                                    <div class="d-flex gap-1 justify-content-center flex-wrap">
                                        <a href="{{ route('recruitment.show', $applicant->id) }}" class="btn btn-sm btn-outline-primary" title="View Full Profile">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                        <a href="{{ route('recruitment.receipt', $applicant->id) }}" target="_blank" class="btn btn-sm btn-outline-danger" title="Print Receipt">
                                            <i class="bi bi-file-earmark-pdf-fill"></i>
                                        </a>
                                        @unless(auth()->user()->hasRole('Appointment Encoder'))
                                        <a href="{{ route('recruitment.deliberation.show', $applicant->id) }}" class="btn btn-sm btn-outline-secondary" title="Open Deliberation Panel">
                                            <i class="bi bi-clipboard2-data-fill"></i>
                                        </a>
                                        @endunless
                                        @unless(auth()->user()->hasRole('Appointment Encoder'))
                                        @php $ev = $applicant->evaluation; @endphp
                                        <button type="button"
                                            class="btn btn-sm {{ $ev ? ($ev->final_rating === 'Qualified' ? 'btn-success' : ($ev->final_rating === 'Disqualified' ? 'btn-danger' : 'btn-warning')) : 'btn-outline-warning' }}"
                                            title="Pre-Evaluate"
                                            onclick="openPreEval(
                                                {{ $applicant->id }},
                                                '{{ addslashes($applicant->full_name) }}',
                                                '{{ $ev->qs_requirement ?? '' }}',
                                                '{{ $ev->exam_status ?? '' }}',
                                                '{{ $ev->docs_complete ?? '' }}',
                                                '{{ $ev->final_rating ?? '' }}',
                                                '{{ addslashes($ev->remarks ?? '') }}'
                                            )">
                                            <i class="bi bi-clipboard2-check-fill"></i>
                                        </button>
                                        @endunless

                                        <a href="{{ route('recruitment.edit', $applicant->id) }}" class="btn btn-sm btn-outline-info" title="Edit Application">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('recruitment.destroy', $applicant->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to archive/delete this applicant?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="Archive/Delete">
                                                <i class="bi bi-archive-fill"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">No applicants found matching the filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-0 py-3">
                {{ $applicants->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════
         PRE-EVALUATION MODAL
    ════════════════════════════════════════════════════════════ -->
    <div class="modal fade" id="preEvalModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border-radius:12px; overflow:hidden;">
                <div class="modal-header border-0 text-white" style="background:linear-gradient(135deg,#1f497d,#2e75b6);">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-clipboard2-check-fill me-2"></i>
                        Pre-Evaluation Assessment
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <form id="preEvalForm" method="POST" action="">
                    @csrf
                    <div class="modal-body p-4">

                        <div class="alert alert-info py-2 px-3 mb-3 small" id="preEvalApplicantName"></div>

                        <div class="row g-3">
                            {{-- QS Requirement --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-check2-circle me-1 text-primary"></i>
                                    QS Requirement
                                </label>
                                <div class="d-flex gap-3 mt-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="qs_requirement" id="qs_met" value="Met">
                                        <label class="form-check-label text-success fw-semibold" for="qs_met">Met</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="qs_requirement" id="qs_unmet" value="Unmet">
                                        <label class="form-check-label text-danger fw-semibold" for="qs_unmet">Unmet</label>
                                    </div>
                                </div>
                            </div>

                            {{-- Examination Status --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-pencil-square me-1 text-primary"></i>
                                    Examination Status
                                </label>
                                <div class="d-flex gap-3 mt-1 flex-wrap">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="exam_status" id="exam_passed" value="Passed">
                                        <label class="form-check-label text-success fw-semibold" for="exam_passed">Passed</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="exam_status" id="exam_failed" value="Failed">
                                        <label class="form-check-label text-danger fw-semibold" for="exam_failed">Failed</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="exam_status" id="exam_absent" value="Absent">
                                        <label class="form-check-label text-warning fw-semibold" for="exam_absent">Absent</label>
                                    </div>
                                </div>
                            </div>

                            {{-- Documents Complete --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-folder-check me-1 text-primary"></i>
                                    Complete Documents Submitted On-Time?
                                </label>
                                <div class="d-flex gap-3 mt-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="docs_complete" id="docs_yes" value="Yes">
                                        <label class="form-check-label text-success fw-semibold" for="docs_yes">Yes</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="docs_complete" id="docs_no" value="No">
                                        <label class="form-check-label text-danger fw-semibold" for="docs_no">No</label>
                                    </div>
                                </div>
                            </div>

                            {{-- Final Rating --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-award-fill me-1 text-primary"></i>
                                    Final Rating
                                </label>
                                <div class="d-flex gap-3 mt-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="final_rating" id="rating_qualified" value="Qualified" onchange="toggleRemarks()">
                                        <label class="form-check-label text-success fw-bold" for="rating_qualified">Qualified</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="final_rating" id="rating_disqualified" value="Disqualified" onchange="toggleRemarks()">
                                        <label class="form-check-label text-danger fw-bold" for="rating_disqualified">Disqualified</label>
                                    </div>
                                </div>
                            </div>

                            {{-- Remarks --}}
                            <div class="col-12" id="remarksSection">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-chat-left-text-fill me-1 text-primary"></i>
                                    Remarks
                                    <span class="text-danger small" id="remarksHint">(Required for Disqualified)</span>
                                </label>
                                <textarea name="remarks" id="preEvalRemarks" class="form-control" rows="3"
                                    placeholder="Specify reasons for disqualification or any additional notes..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-semibold px-4">
                            <i class="bi bi-save-fill me-1"></i> Save Evaluation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function openPreEval(id, name, qsReq, examStatus, docsComplete, finalRating, remarks) {
        document.getElementById('preEvalApplicantName').innerHTML =
            '<i class="bi bi-person-fill me-1"></i> Evaluating: <strong>' + name + '</strong>';
        document.getElementById('preEvalForm').action = '{{ url("recruitment") }}/' + id + '/evaluate';

        // Reset all radio buttons
        document.querySelectorAll('#preEvalModal input[type=radio]').forEach(r => r.checked = false);

        // Set saved values
        if (qsReq)      { var el = document.querySelector('input[name=qs_requirement][value="'+qsReq+'"]');   if(el) el.checked = true; }
        if (examStatus) { var el = document.querySelector('input[name=exam_status][value="'+examStatus+'"]'); if(el) el.checked = true; }
        if (docsComplete){ var el = document.querySelector('input[name=docs_complete][value="'+docsComplete+'"]'); if(el) el.checked = true; }
        if (finalRating){ var el = document.querySelector('input[name=final_rating][value="'+finalRating+'"]'); if(el) el.checked = true; }

        document.getElementById('preEvalRemarks').value = remarks || '';
        toggleRemarks();

        new bootstrap.Modal(document.getElementById('preEvalModal')).show();
    }

    function toggleRemarks() {
        var isDisq = document.getElementById('rating_disqualified').checked;
        var hint   = document.getElementById('remarksHint');
        hint.style.display = isDisq ? 'inline' : 'none';
    }
    </script>

    <!-- Import CSV Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header bg-success text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-arrow-up-fill me-2"></i>Import Applicants from CSV</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('recruitment.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4 bg-light">
                        <div class="alert alert-info py-2" style="font-size: 13px;">
                            <i class="bi bi-info-circle-fill me-1"></i>
                            Upload the <strong>Google Form responses CSV</strong> (NF2026 format) downloaded from Google Drive.
                            All 46 columns are mapped automatically — personal info, PGB employment history, education, eligibility, training, work experience, performance, awards, and competencies.
                            Duplicate entries (same email + position) are skipped automatically.
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">CSV File</label>
                            <input type="file" name="csv_file" accept=".csv,.txt" class="form-control" required>
                            <div class="form-text">Maximum file size: 20 MB</div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success fw-bold">
                            <i class="bi bi-upload me-1"></i> Import Now
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Report Modal -->
    <div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-file-pdf-fill me-2"></i>Generate Report</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('recruitment.report') }}" method="POST" target="_blank">
                    @csrf
                    <div class="modal-body p-4 bg-light">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-danger">Report Type</label>
                            <select name="report_type" id="reportType" class="form-select border-danger" onchange="toggleReportOptions(this.value)">
                                <option value="list">List of Applicants</option>
                                <option value="demographics">Demographics &amp; Qualifications (Resume-Style)</option>
                                <option value="preeval">Pre-Evaluation Assessment Matrix</option>
                            </select>
                            <div id="demographicsNote" style="display:none;" class="alert alert-info py-1 px-2 mt-2 mb-0" style="font-size:12px;">
                                <i class="bi bi-info-circle-fill me-1"></i>
                                Generates one card per applicant showing demographics, education, eligibility, training, performance, and awards — grouped by Office and Position / Item No.
                            </div>
                        </div>

                        {{-- Filters --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Office</label>
                            <select name="office" class="form-select">
                                <option value="">All Offices</option>
                                @foreach($offices as $off)
                                    <option value="{{ $off }}">{{ $off }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-7">
                                <label class="form-label fw-bold">Vacant Position</label>
                                <select name="position_applied" class="form-select">
                                    <option value="">All Positions</option>
                                    @foreach($positions as $pos)
                                        <option value="{{ $pos }}">{{ $pos }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-bold">Item No.</label>
                                <input type="text" name="item_no" class="form-control" placeholder="e.g. BPH-KAL-34">
                            </div>
                        </div>
                        <div class="row g-2 mb-3" id="degreeEligRow">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Course / Degree</label>
                                <input type="text" name="degree" class="form-control" placeholder="Any course...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Eligibility</label>
                                <input type="text" name="eligibility" class="form-control" placeholder="Any eligibility...">
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Date Range Start</label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Date Range End</label>
                                <input type="date" name="end_date" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0" id="reportFooter">
                        <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">Cancel</button>
                        <span id="excelBtn">
                            <button type="submit" name="format" value="excel" class="btn btn-success fw-bold"><i class="bi bi-file-excel-fill me-1"></i> Export Excel</button>
                        </span>
                        <button type="submit" name="format" value="pdf" class="btn btn-danger fw-bold"><i class="bi bi-file-pdf-fill me-1"></i> Generate PDF</button>
                    </div>

                    <script>
                    function toggleReportOptions(type) {
                        var note    = document.getElementById('demographicsNote');
                        var excelBtn = document.getElementById('excelBtn');
                        note.style.display    = (type === 'demographics') ? 'block' : 'none';
                        // Demographics is PDF-only; hide Excel for it
                        excelBtn.style.display = (type === 'demographics') ? 'none' : 'inline';
                    }
                    </script>
                </form>
            </div>
        </div>
    </div>
</x-dashboard-app>
