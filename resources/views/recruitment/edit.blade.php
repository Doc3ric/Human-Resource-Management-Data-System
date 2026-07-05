<x-dashboard-app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Application') }}
        </h2>
    </x-slot>

    <div class="content-wrapper p-4">
        <div class="card border-0 shadow-sm" style="border-radius: 12px; max-width: 960px; margin: 0 auto;">
            <div class="card-header bg-white border-0 pt-4 pb-0">
                <h4 class="fw-bold text-primary mb-0"><i class="bi bi-person-vcard-fill me-2"></i>Applicant Information Form</h4>
                <p class="text-muted small mt-1">Fill in all applicable fields. Fields marked <span class="text-danger">*</span> are required.</p>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('recruitment.update', $applicant->id) }}" method="POST" id="recruitmentForm">
                    @method('PUT')
                    @csrf

                    {{-- DATALISTS FOR AUTOCOMPLETE --}}
                    <datalist id="addresses_list">
                        @foreach($addresses as $addr)
                            <option value="{{ $addr }}"></option>
                        @endforeach
                    </datalist>
                    <datalist id="positions_list">
                        @foreach($vacantPositions as $pos)
                            <option value="{{ $pos }}"></option>
                        @endforeach
                    </datalist>
                    <datalist id="offices_list">
                        @foreach($vacantOffices as $off)
                            <option value="{{ $off }}"></option>
                        @endforeach
                    </datalist>
                    <datalist id="degrees_list">
                        @foreach($degrees as $deg)
                            <option value="{{ $deg }}"></option>
                        @endforeach
                    </datalist>
                    <datalist id="eligibilities_list">
                        @foreach($eligibilities as $elig)
                            <option value="{{ $elig }}"></option>
                        @endforeach
                    </datalist>

                    {{-- ── PRIMARY INFORMATION ─────────────────────────────── --}}
                    <h6 class="fw-bold mb-3 text-secondary border-bottom pb-2">
                        <i class="bi bi-star-fill me-1"></i> Required Applicant Details
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold" style="font-size:13px;">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" required value="{{ old('first_name', $applicant->first_name) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold" style="font-size:13px;">Middle Name</label>
                            <input type="text" name="middle_name" class="form-control" value="{{ old('middle_name', $applicant->middle_name) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold" style="font-size:13px;">Lastname <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control" required value="{{ old('last_name', $applicant->last_name) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold" style="font-size:13px;">Suffix</label>
                            <input type="text" name="name_extension" class="form-control" placeholder="Jr, Sr, III…" value="{{ old('name_extension', $applicant->name_extension) }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold" style="font-size:13px;">Gender <span class="text-danger">*</span></label>
                            <select name="sex" class="form-select" required>
                                <option value="" disabled selected>Select…</option>
                                <option value="Male"   {{ old('sex', $applicant->sex) == 'Male'   ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('sex', $applicant->sex) == 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold" style="font-size:13px;">Birthday <span class="text-danger">*</span></label>
                            <input type="date" name="date_of_birth" class="form-control" required value="{{ old('date_of_birth', $applicant->date_of_birth) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold" style="font-size:13px;">Religion</label>
                            <input type="text" name="religion" class="form-control" value="{{ old('religion', $applicant->religion) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:13px;">Indigenous People (IP) Affiliation</label>
                            <input type="text" name="indigenous_people" class="form-control" placeholder="Tribe/group or N/A" value="{{ old('indigenous_people', $applicant->indigenous_people) }}">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold" style="font-size:13px;">Address <span class="text-danger">*</span></label>
                            <input type="text" name="address" class="form-control" required value="{{ old('address', $applicant->address) }}" list="addresses_list">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:13px;">Phone No. <span class="text-danger">*</span></label>
                            <input type="text" name="phone_number" class="form-control" required value="{{ old('phone_number', $applicant->phone_number) }}">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold" style="font-size:13px;">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email_address" class="form-control" required value="{{ old('email_address', $applicant->email_address) }}">
                        </div>
                        
                        <div class="col-md-3 d-flex align-items-end pb-1 gap-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="is_pgb_employee" id="pgbEmployee"
                                    {{ old('is_pgb_employee', $applicant->is_pgb_employee) ? 'checked' : '' }}
                                    onchange="document.getElementById('pgbStatusWrapper').style.display=this.checked?'block':'none'; document.getElementById('pgbDetails').style.display=this.checked?'block':'none';">
                                <label class="form-check-label fw-semibold" for="pgbEmployee" style="font-size:13px;">PGB Employee?</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="is_pwd" id="pwd" {{ old('is_pwd', $applicant->is_pwd) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="pwd" style="font-size:13px;">PWD?</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px;">Position Applied For <span class="text-danger">*</span></label>
                            <input type="text" id="position_applied_input" name="position_applied" class="form-control" required placeholder="Specify position…" value="{{ old('position_applied', $applicant->position_applied) }}" list="positions_list">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px;">Office / Unit <span class="text-danger">*</span></label>
                            <input type="text" id="office_input" name="office" class="form-control" required placeholder="Specify office…" value="{{ old('office', $applicant->office) }}" list="offices_list">
                        </div>

                        <div class="col-md-4" id="pgbStatusWrapper" style="display:{{ old('is_pgb_employee', $applicant->is_pgb_employee) ? 'block' : 'none' }};">
                            <label class="form-label fw-semibold" style="font-size:13px;">Status of Appointment</label>
                            <select name="pgb_status" class="form-select">
                                <option value="">Select…</option>
                                @foreach(['Permanent','Coterminous','Casual','Job Order'] as $s)
                                    <option value="{{ $s }}" {{ old('pgb_status', $applicant->pgb_status) == $s ? 'selected' : '' }}>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:13px;">Highest Educational Attainment <span class="text-danger">*</span></label>
                            <select name="highest_educational_attainment" class="form-select" required>
                                <option value="" disabled selected>Select…</option>
                                @foreach([
                                    'Elementary Graduate','High School Graduate','Senior High School',
                                    'Associate Degree','Vocational/Technical','College Undergraduate',
                                    "Bachelor's Degree","Master's Degree",'Doctorate','Others'
                                ] as $opt)
                                    <option value="{{ $opt }}" {{ old('highest_educational_attainment', $applicant->highest_educational_attainment) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:13px;">Degree / Course</label>
                            <input type="text" name="degree" class="form-control" placeholder="e.g. BS Information Technology" value="{{ old('degree', $applicant->degree) }}" list="degrees_list">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold" style="font-size:13px;">Eligibility Title</label>
                            <input type="text" name="eligibility" class="form-control" placeholder="e.g. Career Service Professional" value="{{ old('eligibility', $applicant->eligibility) }}" list="eligibilities_list">
                        </div>
                    </div>

                    <hr class="my-4">
                    
                    {{-- ── ADDITIONAL INFORMATION ─────────────────────────────── --}}
                    <details class="mb-4">
                        <summary class="fw-bold text-secondary p-3 bg-light rounded" style="cursor: pointer; list-style: none;">
                            <i class="bi bi-chevron-down me-2"></i> Additional Information (Optional)
                        </summary>
                        <div class="p-3 border rounded mt-2">
                            
                            <h6 class="fw-bold mb-3 text-secondary border-bottom pb-2 mt-2">Additional Documents</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:13px;">Photo URL <span class="text-muted fw-normal">(Google Drive link)</span></label>
                                    <input type="url" name="photo_url" class="form-control" placeholder="https://drive.google.com/…" value="{{ old('photo_url', $applicant->photo_url) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:13px;">JAF URL <span class="text-muted fw-normal">(Job Application Form)</span></label>
                                    <input type="url" name="jaf_url" class="form-control" placeholder="https://drive.google.com/…" value="{{ old('jaf_url', $applicant->jaf_url) }}">
                                </div>
                            </div>

                            <h6 class="fw-bold mb-3 text-secondary border-bottom pb-2">Application Specifics</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:13px;">Item No.</label>
                                    <input type="text" name="item_no" class="form-control" placeholder="Optional" value="{{ old('item_no', $applicant->item_no) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:13px;">Date / Time Applied</label>
                                    <input type="datetime-local" name="applied_at" class="form-control" value="{{ old('applied_at', $applicant->applied_at) }}">
                                </div>
                            </div>

                            <div id="pgbDetails" style="display:{{ old('is_pgb_employee', $applicant->is_pgb_employee) ? 'block' : 'none' }};">
                                <h6 class="fw-bold mb-3 text-secondary border-bottom pb-2">
                                    Current PGB Employment Details
                                </h6>
                                <div class="row g-3 mb-4 ms-1 ps-3 border-start border-3 border-primary">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold" style="font-size:13px;">Length of Service</label>
                                        <input type="text" name="length_of_service" class="form-control" placeholder="e.g. 3 years 2 months" value="{{ old('length_of_service', $applicant->length_of_service) }}">
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label fw-semibold" style="font-size:13px;">Current Position / Designation</label>
                                        <input type="text" name="current_position" class="form-control" value="{{ old('current_position', $applicant->current_position) }}">
                                    </div>
        
                                    <div class="col-12">
                                        <p class="mb-1 text-muted" style="font-size:11px; text-transform:uppercase; letter-spacing:.5px; font-weight:700;">Years per Appointment Type</p>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label" style="font-size:12px;">In Present Position</label>
                                        <input type="text" name="years_in_present_position" class="form-control form-control-sm" value="{{ old('years_in_present_position', $applicant->years_in_present_position) }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label" style="font-size:12px;">Permanent</label>
                                        <input type="text" name="years_permanent" class="form-control form-control-sm" value="{{ old('years_permanent', $applicant->years_permanent) }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label" style="font-size:12px;">Coterminous</label>
                                        <input type="text" name="years_coterminous" class="form-control form-control-sm" value="{{ old('years_coterminous', $applicant->years_coterminous) }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label" style="font-size:12px;">Casual</label>
                                        <input type="text" name="years_casual" class="form-control form-control-sm" value="{{ old('years_casual', $applicant->years_casual) }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label" style="font-size:12px;">Job Order</label>
                                        <input type="text" name="years_job_order" class="form-control form-control-sm" value="{{ old('years_job_order', $applicant->years_job_order) }}">
                                    </div>
                                </div>
                            </div>

                            <h6 class="fw-bold mb-3 text-secondary border-bottom pb-2">
                                Non-PGB Work Experience
                            </h6>
                            <div class="row g-3 mb-3">
                                <div class="col-md-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" name="has_non_pgb_employment" id="nonPgb"
                                            {{ old('has_non_pgb_employment', $applicant->has_non_pgb_employment) ? 'checked' : '' }}
                                            onchange="document.getElementById('nonPgbDetails').style.display=this.checked?'block':'none'">
                                        <label class="form-check-label fw-semibold" for="nonPgb" style="font-size:13px;">Has previous or concurrent non-PGB employment?</label>
                                    </div>
                                </div>
                            </div>
                            <div id="nonPgbDetails" style="display:{{ old('has_non_pgb_employment', $applicant->has_non_pgb_employment) ? 'block' : 'none' }};">
                                <div class="row g-3 mb-4 ms-1 ps-3 border-start border-3 border-warning">
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold" style="font-size:13px;">Employment Status</label>
                                        <input type="text" name="np_employment_status" class="form-control" placeholder="e.g. Full-time" value="{{ old('np_employment_status', $applicant->np_employment_status) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold" style="font-size:13px;">Employer / Agency</label>
                                        <input type="text" name="np_employer" class="form-control" value="{{ old('np_employer', $applicant->np_employer) }}">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label fw-semibold" style="font-size:13px;">Designation / Position</label>
                                        <input type="text" name="np_designation" class="form-control" value="{{ old('np_designation', $applicant->np_designation) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold" style="font-size:13px;">Period (from–to)</label>
                                        <input type="text" name="np_period" class="form-control" placeholder="e.g. Jan 2020 – Dec 2022" value="{{ old('np_period', $applicant->np_period) }}">
                                    </div>
                                </div>
                            </div>

                            <h6 class="fw-bold mb-3 text-secondary border-bottom pb-2">Further Background Documents</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:13px;">TOR URL <span class="text-muted fw-normal">(Transcript)</span></label>
                                    <input type="url" name="tor_url" class="form-control" placeholder="https://drive.google.com/…" value="{{ old('tor_url', $applicant->tor_url) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:13px;">Eligibility Document URL <span class="text-muted fw-normal">(Google Drive)</span></label>
                                    <input type="url" name="eligibility_url" class="form-control" placeholder="https://drive.google.com/…" value="{{ old('eligibility_url', $applicant->eligibility_url) }}">
                                </div>
                                
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold" style="font-size:13px;">Training Document URL <span class="text-muted fw-normal">(Google Drive)</span></label>
                                    <input type="url" name="training_url" class="form-control" placeholder="https://drive.google.com/…" value="{{ old('training_url', $applicant->training_url) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold" style="font-size:13px;">Total Training Hours</label>
                                    <input type="text" name="training_hours" class="form-control" placeholder="e.g. 40 Hours" value="{{ old('training_hours', $applicant->training_hours) }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:13px;">Work Experience Document URL <span class="text-muted fw-normal">(Google Drive)</span></label>
                                    <input type="url" name="experience_url" class="form-control" placeholder="https://drive.google.com/…" value="{{ old('experience_url', $applicant->experience_url) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:13px;">Key Responsibilities / Duties</label>
                                    <textarea name="responsibilities" class="form-control" rows="2" placeholder="Brief description of duties…">{{ old('responsibilities', $applicant->responsibilities) }}</textarea>
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold" style="font-size:13px;">Latest Performance Rating</label>
                                    <input type="text" name="performance_rating" class="form-control" placeholder="e.g. 4.5 / Outstanding" value="{{ old('performance_rating', $applicant->performance_rating) }}">
                                </div>
                                <div class="col-md-9">
                                    <label class="form-label fw-semibold" style="font-size:13px;">Performance Form URL <span class="text-muted fw-normal">(Google Drive)</span></label>
                                    <input type="url" name="performance_form_url" class="form-control" placeholder="https://drive.google.com/…" value="{{ old('performance_form_url', $applicant->performance_form_url) }}">
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:13px;">Awards / Recognitions</label>
                                    <textarea name="award" class="form-control" rows="2" placeholder="List of awards or recognitions…">{{ old('award', $applicant->award) }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:13px;">Award Certificate URL <span class="text-muted fw-normal">(Google Drive)</span></label>
                                    <input type="url" name="award_certificate_url" class="form-control" placeholder="https://drive.google.com/…" value="{{ old('award_certificate_url', $applicant->award_certificate_url) }}">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold" style="font-size:13px;">Core Competencies</label>
                                    <textarea name="competencies" class="form-control" rows="2" placeholder="List relevant core competencies…">{{ old('competencies', $applicant->competencies) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </details>

                        document.addEventListener('DOMContentLoaded', function() {
                            // Convert all text inputs to uppercase dynamically on input
                            const inputs = document.querySelectorAll('#recruitmentForm input[type="text"], #recruitmentForm textarea');
                            inputs.forEach(input => {
                                input.addEventListener('input', function() {
                                    const start = this.selectionStart;
                                    const end = this.selectionEnd;
                                    this.value = this.value.toUpperCase();
                                    this.setSelectionRange(start, end);
                                });
                                // trigger it once for existing values
                                if(input.value) {
                                    input.value = input.value.toUpperCase();
                                }
                            });
                        });
                    </script>

                    {{-- ── 11. DECLARATION ──────────────────────────────────────── --}}
                    <div class="alert alert-light border mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="acknowledged" id="acknowledged" {{ old('acknowledged', $applicant->acknowledged) ? 'checked' : '' }}>
                            <label class="form-check-label" for="acknowledged" style="font-size:13px;">
                                I hereby certify that all information provided in this application is true and correct to the best of my knowledge and belief.
                                I understand that any false statement may be sufficient cause for rejection of my application or dismissal from service.
                            </label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-2">
                        <a href="{{ route('recruitment.index') }}" class="btn btn-light fw-bold px-4">Cancel</a>
                        <button type="submit" class="btn btn-success fw-bold px-4"><i class="bi bi-save me-1"></i> Update Application</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-dashboard-app>
