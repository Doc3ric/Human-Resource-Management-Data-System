{{-- Module 5.3 — left panel read-only applicant profile: photo block, identity,
     current employment, and 8 default-open accordions. Expects $applicant. --}}
@php
    $hasPhoto = app(\App\Support\PhotoEnforcementService::class)->hasValidPhoto($applicant);
    $age = $applicant->date_of_birth ? $applicant->date_of_birth->age : null;
    $mirrorRecord = app(\App\Support\PhotoEnforcementService::class)->findMirrorCandidate($applicant);
    $demeritCases = $mirrorRecord
        ? \App\Models\DisciplinaryCase::where('personnel_id', $mirrorRecord->id)->orderByDesc('created_at')->get()
        : collect();
@endphp

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body p-4 text-center">
        @if($applicant->photo_url)
            <img src="{{ $applicant->photo_url }}" alt="Photo"
                 style="width:120px;height:120px;border-radius:50%;object-fit:cover;border:3px solid var(--color-accent,#2563eb);">
        @else
            <div style="width:120px;height:120px;border-radius:50%;margin:0 auto;background:#f3f4f6;
                        display:flex;align-items:center;justify-content:center;border:3px solid var(--color-danger,#dc2626);">
                <i class="bi bi-person-fill" style="font-size:48px;color:#dc2626;"></i>
            </div>
        @endif

        @unless($hasPhoto)
            <div class="mt-2" style="color:#dc2626;font-size:12.5px;font-weight:700;">
                <span class="text-danger">*</span> A 2x2 ID photo is required for TWG deliberation. Upload now or import from the 201-file.
            </div>
            <div class="d-flex gap-2 justify-content-center mt-2 flex-wrap">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="document.getElementById('photoInput').click()">Upload Photo Now</button>
                @if($mirrorRecord && $mirrorRecord->profile_picture)
                    <form method="POST" action="{{ route('recruitment.deliberation.photo.import', $applicant) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary">Import from 201-File</button>
                    </form>
                @endif
            </div>
            <input type="file" id="photoInput" accept="image/jpeg,image/png,image/webp" style="display:none;" onchange="handlePhotoSelect(event)">
        @endunless

        @if($applicant->photo_source === '201_import' && !$applicant->photo_confirmed)
            <div class="alert alert-warning mt-3 p-2 text-start" style="font-size: 12px;">
                <strong>Verify Imported Photo</strong><br>
                Photo imported from 201-file. Verify that this photo matches the applicant before proceeding.
                <form method="POST" action="{{ route('recruitment.deliberation.photo.confirm', $applicant) }}" class="mt-2">
                    @csrf
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="confirmation" id="confirmPhotoCheck" value="1" required>
                        <label class="form-check-label" for="confirmPhotoCheck">
                            I confirm this photo correctly identifies the applicant
                        </label>
                    </div>
                    <button type="submit" class="btn btn-sm btn-warning mt-2 w-100 fw-bold">Confirm & Unlock Scoring</button>
                </form>
            </div>
        @endif

        <div class="mt-3" style="font-size:18px;font-weight:800;color:var(--color-primary,#1e3a5f);">{{ $applicant->full_name }}</div>
        <div style="font-size:13px;color:#6b7280;">Age: {{ $age !== null ? $age . ' years old' : '—' }}</div>
        <div style="font-size:12px;color:#9ca3af;">{{ $applicant->position_applied }} @if($applicant->item_no) &middot; Item {{ $applicant->item_no }} @endif</div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body p-4">
        <div class="ai-section-title"><i class="bi bi-building me-1"></i> Current Employment</div>
        @if($applicant->is_pgb_employee)
            <span class="badge bg-success">Government — PGB</span>
            <div class="mt-2" style="font-size:12.5px;">
                <div><strong>Status:</strong> {{ $applicant->pgb_status ?: '—' }}</div>
                <div><strong>Designation:</strong> {{ $applicant->current_position ?: '—' }}</div>
            </div>
        @elseif($applicant->has_non_pgb_employment)
            <span class="badge bg-secondary">{{ $applicant->np_employment_status ?: 'Employed' }}</span>
            <div class="mt-2" style="font-size:12.5px;">
                <div><strong>Employer:</strong> {{ $applicant->np_employer ?: '—' }}</div>
                <div><strong>Designation:</strong> {{ $applicant->np_designation ?: '—' }}</div>
            </div>
        @else
            <span class="badge bg-light text-dark border">Unemployed / Not Declared</span>
        @endif
    </div>
</div>

<div style="font-size:10.5px;color:#9ca3af;margin-bottom:8px;text-align:right;">
    <strong>EVALUATION BASIS:</strong> 2025 ORAOHRA Rule VIII &amp; DBM-CSC JC No. 1 s.2017
</div>
<div class="accordion" id="qualificationsAccordion">

    {{-- 1. Education --}}
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accEducation">
                <i class="bi bi-mortarboard-fill me-2"></i> Education
            </button>
        </h2>
        <div id="accEducation" class="accordion-collapse collapse show" data-bs-parent="#qualificationsAccordion">
            <div class="accordion-body" style="font-size:12.5px;">
                <div><strong>Attainment:</strong> {{ $applicant->highest_educational_attainment ?: '—' }}</div>
                <div><strong>Course:</strong> {{ $applicant->degree ?: '—' }}</div>
                @if($applicant->tor_url)
                    <a href="{{ $applicant->tor_url }}" target="_blank" class="doc-link text-primary">View Transcript</a>
                @endif
            </div>
        </div>
    </div>

    {{-- 2. Civil Service Eligibility --}}
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accEligibility">
                <i class="bi bi-patch-check-fill me-2"></i> Civil Service Eligibility
            </button>
        </h2>
        <div id="accEligibility" class="accordion-collapse collapse show" data-bs-parent="#qualificationsAccordion">
            <div class="accordion-body" style="font-size:12.5px;">
                @if($applicant->eligibility && !in_array(strtolower(trim($applicant->eligibility)), ['n/a', 'none', 'not applicable', 'no', 'na']))
                    <span class="badge bg-success">On File</span> {{ $applicant->eligibility }}
                @else
                    <span class="badge bg-light text-dark border">Not Required</span>
                @endif
                @if($applicant->eligibility_url)
                    <a href="{{ $applicant->eligibility_url }}" target="_blank" class="doc-link text-success d-block mt-1">View Eligibility Document</a>
                @endif
            </div>
        </div>
    </div>

    {{-- 3. Relevant Training --}}
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accTraining">
                <i class="bi bi-journal-bookmark-fill me-2"></i> Relevant Training
            </button>
        </h2>
        <div id="accTraining" class="accordion-collapse collapse show" data-bs-parent="#qualificationsAccordion">
            <div class="accordion-body" style="font-size:12.5px;">
                <div style="font-size:20px;font-weight:800;color:var(--color-primary,#1e3a5f);">{{ $applicant->training_hours ?: '0' }} hrs</div>
                @if($applicant->training_url)
                    <a href="{{ $applicant->training_url }}" target="_blank" class="doc-link text-primary">View Training Records</a>
                @endif
            </div>
        </div>
    </div>

    {{-- 4. Awards --}}
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accAwards">
                <i class="bi bi-trophy-fill me-2"></i> Awards
            </button>
        </h2>
        <div id="accAwards" class="accordion-collapse collapse show" data-bs-parent="#qualificationsAccordion">
            <div class="accordion-body" style="font-size:12.5px;">
                @if($applicant->award)
                    <div style="white-space:pre-wrap;">{{ $applicant->award }}</div>
                @else
                    <span class="text-muted">No awards declared.</span>
                @endif
                @if($applicant->award_certificate_url)
                    <a href="{{ $applicant->award_certificate_url }}" target="_blank" class="doc-link text-warning d-block mt-1">View Award Certificate</a>
                @endif
            </div>
        </div>
    </div>

    {{-- 5. IPCR --}}
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accIpcr">
                <i class="bi bi-star-fill me-2"></i> IPCR
            </button>
        </h2>
        <div id="accIpcr" class="accordion-collapse collapse show" data-bs-parent="#qualificationsAccordion">
            <div class="accordion-body" style="font-size:12.5px;">
                <div style="font-size:20px;font-weight:800;color:var(--color-primary,#1e3a5f);">{{ $applicant->performance_rating ?: '—' }}</div>
                @if($applicant->performance_form_url)
                    <a href="{{ $applicant->performance_form_url }}" target="_blank" class="doc-link text-primary">View Performance Form</a>
                @endif
            </div>
        </div>
    </div>

    {{-- 6. Length of Service --}}
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accLos">
                <i class="bi bi-clock-history me-2"></i> Length of Service
            </button>
        </h2>
        <div id="accLos" class="accordion-collapse collapse show" data-bs-parent="#qualificationsAccordion">
            <div class="accordion-body" style="font-size:12.5px;">
                <div>{{ $applicant->length_of_service ?: '—' }}</div>
                @if($applicant->is_pgb_employee)
                    <p class="mb-1 mt-2" style="font-size:11px;font-weight:700;text-transform:uppercase;color:#9ca3af;">Years per Appointment Type</p>
                    <div class="row g-2 text-center">
                        @foreach([
                            'Present Position' => $applicant->years_in_present_position,
                            'Permanent'        => $applicant->years_permanent,
                            'Coterminous'      => $applicant->years_coterminous,
                            'Casual'           => $applicant->years_casual,
                            'Job Order'        => $applicant->years_job_order,
                        ] as $label => $value)
                            <div class="col">
                                <div style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;padding:6px 4px;">
                                    <div style="font-size:14px;font-weight:800;">{{ $value ?: '—' }}</div>
                                    <div style="font-size:9px;color:#9ca3af;">{{ $label }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- 7. Demerit Record --}}
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accDemerit">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> Demerit Record
            </button>
        </h2>
        <div id="accDemerit" class="accordion-collapse collapse show" data-bs-parent="#qualificationsAccordion">
            <div class="accordion-body" style="font-size:12.5px;">
                @if($demeritCases->isEmpty())
                    <span class="badge bg-success">No Demerit on Record</span>
                @else
                    <span class="badge bg-danger">Demerit on Record — {{ $demeritCases->count() }} Item(s)</span>
                    <ul class="list-unstyled mt-2 mb-0">
                        @foreach($demeritCases as $case)
                            <li class="mb-1">
                                {{ $case->offense_classification }} &middot; {{ $case->penalty ?: 'Pending' }}
                                &middot; <span class="text-muted">{{ $case->status }}</span>
                                @if($case->decided_at)
                                    &middot; {{ $case->decided_at->format('M d, Y') }}
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
                <p class="mt-2 mb-0" style="font-size:10.5px;color:#9ca3af;">
                    SECURITY NOTICE: Demerit information is governed by R.A. 10173. Access is restricted to authorized HRMPSB members only. All views are read-only and logged dynamically to the system audit trail.
                </p>
            </div>
        </div>
    </div>

    {{-- 8. Relevant Experience (EETE) --}}
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accExperience">
                <i class="bi bi-file-earmark-person-fill me-2"></i> Relevant Experience (EETE)
            </button>
        </h2>
        <div id="accExperience" class="accordion-collapse collapse show" data-bs-parent="#qualificationsAccordion">
            <div class="accordion-body" style="font-size:12.5px;">
                @if($applicant->responsibilities)
                    <div style="white-space:pre-wrap;">{{ $applicant->responsibilities }}</div>
                @endif
                @if($applicant->experience_url)
                    <a href="{{ $applicant->experience_url }}" target="_blank" class="doc-link text-primary d-block mt-1">View Experience Records</a>
                @endif
                @if($applicant->has_non_pgb_employment)
                    <div class="mt-2 pt-2 border-top">
                        <div class="fw-bold" style="font-size:11px;">Non-PGB (private sector)</div>
                        <div>{{ $applicant->np_designation ?: '—' }} at {{ $applicant->np_employer ?: '—' }} ({{ $applicant->np_period ?: '—' }})</div>
                        <div style="font-size:11px;color:#9ca3af;">For reference only. Does not count toward government service length for scoring purposes unless the position's rating criteria explicitly includes it.</div>
                    </div>
                @endif
                @if(!$applicant->responsibilities && !$applicant->experience_url && !$applicant->has_non_pgb_employment)
                    <span class="text-muted">No work experience records submitted.</span>
                @endif
            </div>
        </div>
    </div>

</div>

<p class="mt-3" style="font-size:10.5px;color:#9ca3af;">
    Profile data sourced from applicant's submitted documents and 201-file. Last updated: {{ $applicant->updated_at->format('M d, Y') }}. For corrections, contact HR Records Section.
</p>
