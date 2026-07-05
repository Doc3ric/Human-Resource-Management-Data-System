        <div class="row g-4">

            {{-- LEFT COLUMN --}}
            <div class="col-md-5">

                {{-- Personal --}}
                <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
                    <div class="card-body p-4">
                        <div class="ai-section-title"><i class="bi bi-person-fill me-1"></i> Personal Information</div>
                        <div class="row g-0">
                            <div class="col-6 ai-field">
                                <div class="ai-label">Sex</div>
                                <div class="ai-value">{{ $applicant->sex ?: '—' }}</div>
                            </div>
                            <div class="col-6 ai-field">
                                <div class="ai-label">Date of Birth</div>
                                <div class="ai-value">{{ $applicant->date_of_birth?->format('M d, Y') ?? '—' }}</div>
                            </div>
                            <div class="col-6 ai-field">
                                <div class="ai-label">Religion</div>
                                <div class="ai-value {{ !$applicant->religion ? 'empty' : '' }}">{{ $applicant->religion ?: '—' }}</div>
                            </div>
                            <div class="col-6 ai-field">
                                <div class="ai-label">IP Affiliation</div>
                                <div class="ai-value {{ !$applicant->indigenous_people ? 'empty' : '' }}">{{ $applicant->indigenous_people ?: '—' }}</div>
                            </div>
                            <div class="col-12 ai-field">
                                <div class="ai-label">Address</div>
                                <div class="ai-value">{{ $applicant->address }}</div>
                            </div>
                            <div class="col-6 ai-field">
                                <div class="ai-label">Phone</div>
                                <div class="ai-value">{{ $applicant->phone_number }}</div>
                            </div>
                            <div class="col-6 ai-field">
                                <div class="ai-label">Email</div>
                                <div class="ai-value" style="word-break:break-all;">{{ $applicant->email_address }}</div>
                            </div>
                            <div class="col-6 ai-field mb-0">
                                <div class="ai-label">PWD</div>
                                <div class="ai-value">
                                    @if($applicant->is_pwd) <span class="badge-yes">Yes</span> @else <span class="badge-no">No</span> @endif
                                </div>
                            </div>
                        </div>
                        @if($applicant->photo_url || $applicant->jaf_url)
                        <div class="mt-3 pt-3 border-top d-flex gap-3 flex-wrap">
                            @if($applicant->photo_url)
                                <a href="{{ $applicant->photo_url }}" target="_blank" class="doc-link text-primary">
                                    <i class="bi bi-image-fill"></i> View Photo
                                </a>
                            @endif
                            @if($applicant->jaf_url)
                                <a href="{{ $applicant->jaf_url }}" target="_blank" class="doc-link text-success">
                                    <i class="bi bi-file-earmark-text-fill"></i> View JAF
                                </a>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Vacancy --}}
                <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
                    <div class="card-body p-4">
                        <div class="ai-section-title"><i class="bi bi-briefcase-fill me-1"></i> Application Details</div>
                        <div class="ai-field">
                            <div class="ai-label">Position Applied For</div>
                            <div class="ai-value fw-bold text-primary">{{ $applicant->position_applied }}</div>
                        </div>
                        <div class="row g-0">
                            <div class="col-8 ai-field">
                                <div class="ai-label">Office / Unit</div>
                                <div class="ai-value">{{ $applicant->office ?: '—' }}</div>
                            </div>
                            <div class="col-4 ai-field">
                                <div class="ai-label">Item No.</div>
                                <div class="ai-value {{ !$applicant->item_no ? 'empty' : '' }}">{{ $applicant->item_no ?: '—' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Education --}}
                <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
                    <div class="card-body p-4">
                        <div class="ai-section-title"><i class="bi bi-mortarboard-fill me-1"></i> Education</div>
                        <div class="ai-field">
                            <div class="ai-label">Highest Educational Attainment</div>
                            <div class="ai-value">{{ $applicant->highest_educational_attainment }}</div>
                        </div>
                        <div class="ai-field">
                            <div class="ai-label">Degree / Course</div>
                            <div class="ai-value {{ !$applicant->degree ? 'empty' : '' }}">{{ $applicant->degree ?: '—' }}</div>
                        </div>
                        @if($applicant->tor_url)
                        <a href="{{ $applicant->tor_url }}" target="_blank" class="doc-link text-primary">
                            <i class="bi bi-file-earmark-text-fill"></i> View Transcript (TOR)
                        </a>
                        @endif
                    </div>
                </div>

                {{-- Eligibility --}}
                <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
                    <div class="card-body p-4">
                        <div class="ai-section-title"><i class="bi bi-patch-check-fill me-1"></i> Eligibility</div>
                        <div class="ai-field">
                            <div class="ai-label">Eligibility Title</div>
                            <div class="ai-value {{ !$applicant->eligibility ? 'empty' : '' }}">{{ $applicant->eligibility ?: '—' }}</div>
                        </div>
                        @if($applicant->eligibility_url)
                        <a href="{{ $applicant->eligibility_url }}" target="_blank" class="doc-link text-success">
                            <i class="bi bi-file-earmark-check-fill"></i> View Eligibility Document
                        </a>
                        @endif
                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN --}}
            <div class="col-md-7">

                {{-- PGB Employment --}}
                <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
                    <div class="card-body p-4">
                        <div class="ai-section-title"><i class="bi bi-building me-1"></i> Current PGB Employment</div>
                        <div class="mb-2">
                            @if($applicant->is_pgb_employee) <span class="badge-yes">Yes — PGB Employee</span>
                            @else <span class="badge-no">Not a PGB Employee</span>
                            @endif
                        </div>
                        @if($applicant->is_pgb_employee)
                        <div class="row g-0 mt-3">
                            <div class="col-6 ai-field">
                                <div class="ai-label">Employment Status</div>
                                <div class="ai-value">{{ $applicant->pgb_status ?: '—' }}</div>
                            </div>
                            <div class="col-6 ai-field">
                                <div class="ai-label">Length of Service</div>
                                <div class="ai-value">{{ $applicant->length_of_service ?: '—' }}</div>
                            </div>
                            <div class="col-12 ai-field">
                                <div class="ai-label">Current Position</div>
                                <div class="ai-value">{{ $applicant->current_position ?: '—' }}</div>
                            </div>
                        </div>
                        <p class="mb-1" style="font-size:11px;font-weight:700;text-transform:uppercase;color:#9ca3af;letter-spacing:.5px;">Years per Appointment Type</p>
                        <div class="row g-2 text-center">
                            @foreach([
                                'In Present Position' => $applicant->years_in_present_position,
                                'Permanent'           => $applicant->years_permanent,
                                'Coterminous'         => $applicant->years_coterminous,
                                'Casual'              => $applicant->years_casual,
                                'Job Order'           => $applicant->years_job_order,
                            ] as $label => $value)
                            <div class="col">
                                <div style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;padding:8px 4px;">
                                    <div style="font-size:16px;font-weight:800;color:#1e3a5f;">{{ $value ?: '—' }}</div>
                                    <div style="font-size:10px;color:#9ca3af;">{{ $label }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Non-PGB --}}
                <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
                    <div class="card-body p-4">
                        <div class="ai-section-title"><i class="bi bi-buildings me-1"></i> Non-PGB Work Experience</div>
                        @if($applicant->has_non_pgb_employment)
                        <div class="row g-0">
                            <div class="col-4 ai-field">
                                <div class="ai-label">Status</div>
                                <div class="ai-value">{{ $applicant->np_employment_status ?: '—' }}</div>
                            </div>
                            <div class="col-8 ai-field">
                                <div class="ai-label">Employer / Agency</div>
                                <div class="ai-value">{{ $applicant->np_employer ?: '—' }}</div>
                            </div>
                            <div class="col-6 ai-field mb-0">
                                <div class="ai-label">Designation</div>
                                <div class="ai-value">{{ $applicant->np_designation ?: '—' }}</div>
                            </div>
                            <div class="col-6 ai-field mb-0">
                                <div class="ai-label">Period</div>
                                <div class="ai-value">{{ $applicant->np_period ?: '—' }}</div>
                            </div>
                        </div>
                        @else
                        <span class="badge-no">No non-PGB employment declared</span>
                        @endif
                    </div>
                </div>

                {{-- Training --}}
                <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
                    <div class="card-body p-4">
                        <div class="ai-section-title"><i class="bi bi-journal-bookmark-fill me-1"></i> Training &amp; Development</div>
                        <div class="row g-0">
                            <div class="col-4 ai-field mb-0">
                                <div class="ai-label">Training Hours</div>
                                <div class="ai-value {{ !$applicant->training_hours ? 'empty' : '' }}">{{ $applicant->training_hours ?: '—' }}</div>
                            </div>
                            <div class="col-8 ai-field mb-0">
                                @if($applicant->training_url)
                                <div class="ai-label">Training Documents</div>
                                <a href="{{ $applicant->training_url }}" target="_blank" class="doc-link text-primary">
                                    <i class="bi bi-file-earmark-text-fill"></i> View Training Records
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Work Experience --}}
                <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
                    <div class="card-body p-4">
                        <div class="ai-section-title"><i class="bi bi-file-earmark-person-fill me-1"></i> Work Experience</div>
                        @if($applicant->responsibilities)
                        <div class="ai-field">
                            <div class="ai-label">Key Responsibilities</div>
                            <div class="ai-value" style="white-space:pre-wrap;">{{ $applicant->responsibilities }}</div>
                        </div>
                        @endif
                        @if($applicant->experience_url)
                        <a href="{{ $applicant->experience_url }}" target="_blank" class="doc-link text-primary">
                            <i class="bi bi-file-earmark-text-fill"></i> View Experience Records
                        </a>
                        @endif
                        @if(!$applicant->responsibilities && !$applicant->experience_url)
                        <span class="ai-value empty">No work experience records submitted.</span>
                        @endif
                    </div>
                </div>

                {{-- Performance --}}
                <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="ai-section-title mb-0 border-0 pb-0"><i class="bi bi-star-fill me-1"></i> Performance Rating</div>
                            <button type="button" class="btn btn-sm btn-outline-primary fw-bold" onclick="syncPgbIpcr({{ $applicant->id }}, this)">
                                <i class="bi bi-arrow-repeat me-1"></i> Fetch from PGB
                            </button>
                        </div>
                        <div class="row g-0 align-items-center">
                            <div class="col-4">
                                <div id="ipcr_rating_display" style="font-size:24px;font-weight:800;color:#1e3a5f;">{{ $applicant->performance_rating ?: '—' }}</div>
                                <div style="font-size:10px;color:#9ca3af;">Latest Rating</div>
                            </div>
                            <div class="col-8">
                                @if($applicant->performance_form_url)
                                <a href="{{ $applicant->performance_form_url }}" target="_blank" class="doc-link text-primary">
                                    <i class="bi bi-file-earmark-text-fill"></i> View Performance Form
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Awards & Competencies --}}
                <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
                    <div class="card-body p-4">
                        <div class="ai-section-title"><i class="bi bi-trophy-fill me-1"></i> Awards &amp; Competencies</div>
                        @if($applicant->award)
                        <div class="ai-field">
                            <div class="ai-label">Awards / Recognitions</div>
                            <div class="ai-value" style="white-space:pre-wrap;">{{ $applicant->award }}</div>
                        </div>
                        @endif
                        @if($applicant->award_certificate_url)
                        <a href="{{ $applicant->award_certificate_url }}" target="_blank" class="doc-link text-warning d-block mb-2">
                            <i class="bi bi-award-fill"></i> View Award Certificate
                        </a>
                        @endif
                        @if($applicant->competencies)
                        <div class="ai-field mb-0">
                            <div class="ai-label">Core Competencies</div>
                            <div class="ai-value" style="white-space:pre-wrap;">{{ $applicant->competencies }}</div>
                        </div>
                        @endif
                        @if(!$applicant->award && !$applicant->award_certificate_url && !$applicant->competencies)
                        <span class="ai-value empty">No awards or competencies declared.</span>
                        @endif
                    </div>
                </div>

                {{-- Declaration --}}
                <div class="card border-0 shadow-sm" style="border-radius:12px;">
                    <div class="card-body p-4">
                        <div class="ai-section-title"><i class="bi bi-shield-check me-1"></i> Declaration</div>
                        @if($applicant->acknowledged)
                        <span class="badge-yes"><i class="bi bi-check-circle-fill me-1"></i> Applicant has acknowledged and certified the information provided.</span>
                        @else
                        <span class="badge-no">Acknowledgement not yet recorded.</span>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

<script>
function syncPgbIpcr(applicantId, btn) {
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Fetching...';
    btn.disabled = true;

    fetch('/recruitment/' + applicantId + '/sync-ipcr', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('ipcr_rating_display').innerText = data.rating;
            
            // If TWG form IPCR input exists, auto-fill it
            const twgInput = document.querySelector('input[name="ipcr_score"]');
            if (twgInput) {
                twgInput.value = data.rating; 
            }
            
            alert(data.message);
        } else {
            alert(data.message || 'Failed to fetch IPCR rating.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('An error occurred while fetching the IPCR rating.');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}
</script>

