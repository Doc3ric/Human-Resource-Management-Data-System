@php
    $isPayroll = $violation->status === 'escalated';
    $isShowCause = $violation->violation_type === 'SHOW_CAUSE';
    $isReprimand = str_starts_with($violation->violation_type, 'REPRIMAND_');
    $titleLabel = match(true) {
        $isPayroll => 'Payroll Coordination',
        $isShowCause => 'Show-Cause Order',
        $violation->violation_type == 'HABITUAL_TARDINESS' => 'Tardy',
        $violation->violation_type == 'UNDERTIME' => 'Undertime',
        $violation->violation_type == 'REPRIMAND_HABITUAL_TARDINESS' => 'Reprimand (Tardy)',
        $violation->violation_type == 'REPRIMAND_UNDERTIME' => 'Reprimand (Undertime)',
        default => $violation->violation_type,
    };
@endphp

<div class="modal-header" style="background: linear-gradient(135deg,#1e3a5f 0%,#1a5276 55%,#0f4c75 100%); color:#fff; border-top-left-radius:16px; border-top-right-radius:16px; border-bottom:none;">
    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Letter ({{ $titleLabel }})</h5>
    <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<form method="POST" action="{{ route('leave-violations.update-letter', $violation->id) }}">
    @csrf
    @method('PUT')
    <div class="modal-body p-4">
        @if($isPayroll)
            <div class="form-card">
                <div class="form-card-header"><div class="form-card-icon" style="background:#ccfbf1; color:#0f766e;"><i class="bi bi-person-badge"></i></div>Employee &amp; Violation Reference</div>
                <div class="form-card-body">
                    <div style="margin-bottom: 16px;">
                        <label class="form-label">Employee</label>
                        <input type="text" class="form-control" value="{{ $violation->plantillaRecord->last_name }}, {{ $violation->plantillaRecord->first_name }}" readonly>
                    </div>
                    <div>
                        <label class="form-label">Referenced Violation</label>
                        <input type="text" class="form-control" value="{{ ucwords(strtolower(str_replace('_', ' ', $violation->violation_type))) }} — recorded {{ $violation->created_at->format('F d, Y') }}" readonly>
                    </div>
                </div>
            </div>

            <div class="form-card mb-0">
                <div class="form-card-header"><div class="form-card-icon" style="background:#e0e7ff; color:#4338ca;"><i class="bi bi-cash-stack"></i></div>Payroll Action</div>
                <div class="form-card-body">
                    <label class="form-label">Requested Action <span>*</span></label>
                    <select name="action_type" class="form-control" required style="width: 300px;">
                        <option value="DROP" {{ ($violation->details['payroll_action_type'] ?? '') == 'DROP' ? 'selected' : '' }}>Drop from Payroll</option>
                        <option value="DEDUCT" {{ ($violation->details['payroll_action_type'] ?? '') == 'DEDUCT' ? 'selected' : '' }}>Salary Deduction</option>
                    </select>
                    <div class="help-text">Changing this regenerates the letter and re-transmits it to Payroll.</div>
                </div>
            </div>
        @elseif($isShowCause)
            <div class="form-card">
                <div class="form-card-header"><div class="form-card-icon"><i class="bi bi-person"></i></div>Employee Information</div>
                <div class="form-card-body">
                    <label class="form-label">Employee Name <span>*</span></label>
                    <select name="plantilla_record_id" class="form-control" required>
                        <option value="">Select employee...</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ $emp->id == $violation->plantilla_record_id ? 'selected' : '' }}>
                                {{ $emp->last_name }}, {{ $emp->first_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-card">
                <div class="form-card-header"><div class="form-card-icon" style="background:#ede9fe; color:#6d28d9;"><i class="bi bi-file-earmark-text"></i></div>Grounds</div>
                <div class="form-card-body">
                    <label class="form-label">Grounds for the Show-Cause Order <span>*</span></label>
                    <textarea name="grounds" class="form-control" rows="4" required>{{ $violation->details['grounds'] ?? '' }}</textarea>
                    <div class="help-text">Describe the facts the employee is being directed to explain.</div>
                </div>
            </div>

            <div class="form-card mb-0">
                <div class="form-card-header"><div class="form-card-icon" style="background:#ecfdf5; color:#059669;"><i class="bi bi-shield-check"></i></div>Signatory</div>
                <div class="form-card-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Signatory Name</label>
                        <input type="text" name="signatory_name" class="form-control" value="{{ $violation->details['signatory_name'] ?? 'AIDA B. LOVERES' }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Signatory Position</label>
                        <input type="text" name="signatory_position" class="form-control" value="{{ $violation->details['signatory_position'] ?? 'PG Department Head/PHRM Officer' }}" required>
                    </div>
                </div>
            </div>
        @else
            <input type="hidden" name="letter_type" value="{{ $violation->violation_type }}">

            <div class="form-card">
                <div class="form-card-header"><div class="form-card-icon"><i class="bi bi-person"></i></div>Employee Information</div>
                <div class="form-card-body">
                    <div style="margin-bottom: 16px;">
                        <label class="form-label">Employee Name <span>*</span></label>
                        <select name="plantilla_record_id" id="editLetterEmployeeSelect" class="form-control" required>
                            <option value="">Type to search employee...</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}"
                                    data-pos="{{ $emp->position_title }}"
                                    data-off="{{ $emp->office_department }}"
                                    {{ $emp->id == $violation->plantilla_record_id ? 'selected' : '' }}>
                                    {{ $emp->last_name }}, {{ $emp->first_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label class="form-label">Prefix <span>*</span></label>
                        <select name="prefix" class="form-control" required style="width: 250px;">
                            <option value="">Select Prefix</option>
                            <option value="Mr." {{ ($violation->details['prefix'] ?? '') == 'Mr.' ? 'selected' : '' }}>Mr.</option>
                            <option value="Ms." {{ ($violation->details['prefix'] ?? '') == 'Ms.' ? 'selected' : '' }}>Ms.</option>
                            <option value="Mrs." {{ ($violation->details['prefix'] ?? '') == 'Mrs.' ? 'selected' : '' }}>Mrs.</option>
                            <option value="Dr." {{ ($violation->details['prefix'] ?? '') == 'Dr.' ? 'selected' : '' }}>Dr.</option>
                            <option value="Engr." {{ ($violation->details['prefix'] ?? '') == 'Engr.' ? 'selected' : '' }}>Engr.</option>
                        </select>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Position</label>
                            <input type="text" name="position_title" id="editLetterEmpPosition" class="form-control" value="{{ $violation->details['position_title'] ?? $violation->plantillaRecord->position_title }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Office / Department</label>
                            <input type="text" name="office_department" id="editLetterEmpOffice" class="form-control" value="{{ $violation->details['office_department'] ?? $violation->plantillaRecord->office_department }}">
                        </div>
                    </div>
                    <div class="row g-3" style="margin-top: 8px;">
                        <div class="col-md-6">
                            <label class="form-label">Reference No.</label>
                            <input type="text" name="reference_no" class="form-control" value="{{ $violation->details['reference_no'] ?? ('LV-'.$violation->id) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Created By (Creator)</label>
                            <input type="text" name="creator_name" class="form-control" value="{{ $violation->details['creator_name'] ?? ($violation->issuedBy->first_name ?? ($violation->issuedBy->name ?? 'System')) }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-card">
                <div class="form-card-header"><div class="form-card-icon" style="background:#fef3c7; color:#d97706;"><i class="bi bi-file-text"></i></div>Letter Details</div>
                <div class="form-card-body row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Month <span>*</span></label>
                        <select name="month" class="form-control" required>
                            <option value="">Select Month</option>
                            @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $m)
                                <option value="{{ $m }}" {{ ($violation->details['month'] ?? '') == $m ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Year <span>*</span></label>
                        <select name="year" class="form-control" required>
                            <option value="{{ date('Y') }}" {{ ($violation->details['year'] ?? '') == date('Y') ? 'selected' : '' }}>{{ date('Y') }}</option>
                            <option value="{{ date('Y')-1 }}" {{ ($violation->details['year'] ?? '') == date('Y')-1 ? 'selected' : '' }}>{{ date('Y')-1 }}</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">No. of {{ $violation->violation_type == 'HABITUAL_TARDINESS' ? 'Tardiness' : 'Undertime' }} Occurrences <span>*</span></label>
                        <input type="text" name="occurrences" class="form-control" value="{{ $violation->details['occurrences'] ?? '' }}" placeholder="e.g. &quot;three (3)&quot; or &quot;5&quot;" required>
                    </div>
                </div>
            </div>

            @if($isReprimand)
            @php
                $priorDefaultDate = $violation->details['prior_warning_date_override']
                    ?? ($priorWarning ? ($priorWarning->issued_at ?? $priorWarning->created_at)->format('Y-m-d') : null);
                $priorDefaultRef = $violation->details['prior_warning_ref_override']
                    ?? ($priorWarning ? 'LV-' . $priorWarning->created_at->format('Ymd') . '-' . strtoupper(substr(md5($priorWarning->id), -6)) : '');
                $priorDefaultMonth = $violation->details['prior_warning_month_override'] ?? $priorWarning?->details['month'] ?? '';
                $priorDefaultYear = $violation->details['prior_warning_year_override'] ?? $priorWarning?->details['year'] ?? '';
            @endphp
            <div class="form-card">
                <div class="form-card-header"><div class="form-card-icon" style="background:#fee2e2; color:#dc2626;"><i class="bi bi-exclamation-triangle"></i></div>Reference Warning Details</div>
                <div class="form-card-body">
                    <div class="help-text" style="margin-bottom:14px;">Select the prior warnings to reference in this reprimand.</div>
                    
                    <div class="mb-4">
                        <label class="form-label">Prior Warning(s) <span>*</span></label>
                        <select name="prior_warning_ids[]" class="form-control" multiple style="height: 120px;" required>
                            @foreach($availablePriorWarnings as $w)
                                @php
                                    $isSelected = $priorWarnings->pluck('id')->contains($w->id);
                                @endphp
                                <option value="{{ $w->id }}" {{ $isSelected ? 'selected' : '' }}>
                                    {{ $w->violation_type === 'HABITUAL_TARDINESS' ? 'Habitual Tardiness' : 'Undertime' }} &mdash; {{ $w->details['month'] ?? '' }} {{ $w->details['year'] ?? '' }} (Issued: {{ $w->issued_at ? $w->issued_at->format('Y-m-d') : '' }})
                                </option>
                            @endforeach
                            @foreach($priorWarnings as $w)
                                @if(!$availablePriorWarnings->pluck('id')->contains($w->id))
                                    <option value="{{ $w->id }}" selected>
                                        {{ $w->violation_type === 'HABITUAL_TARDINESS' ? 'Habitual Tardiness' : 'Undertime' }} &mdash; {{ $w->details['month'] ?? '' }} {{ $w->details['year'] ?? '' }} (Issued: {{ $w->issued_at ? $w->issued_at->format('Y-m-d') : '' }})
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        <div class="help-text mt-1">Hold Ctrl/Cmd to select multiple warnings.</div>
                    </div>

                    <hr class="my-4">
                    <div class="help-text" style="margin-bottom:14px;">If you need to manually correct the text for the <strong>first</strong> referenced warning without altering its original record, you may override it below:</div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Reference Warning Date</label>
                            <input type="date" name="prior_warning_date_override" class="form-control" value="{{ old('prior_warning_date_override', $priorDefaultDate) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reference No.</label>
                            <input type="text" name="prior_warning_ref_override" class="form-control" value="{{ old('prior_warning_ref_override', $priorDefaultRef) }}" placeholder="e.g. LV-20260615-ABC123">
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Prior Violation Month</label>
                            <select name="prior_warning_month_override" class="form-control">
                                <option value="">Select Month</option>
                                @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $m)
                                    <option value="{{ $m }}" {{ old('prior_warning_month_override', $priorDefaultMonth) == $m ? 'selected' : '' }}>{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Prior Violation Year</label>
                            <input type="number" name="prior_warning_year_override" class="form-control" value="{{ old('prior_warning_year_override', $priorDefaultYear) }}">
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="form-card mb-0">
                <div class="form-card-header"><div class="form-card-icon" style="background:#ecfdf5; color:#059669;"><i class="bi bi-shield-check"></i></div>Signatory</div>
                <div class="form-card-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Signatory Name</label>
                        <input type="text" name="signatory_name" class="form-control" value="{{ $violation->details['signatory_name'] ?? 'AIDA B. LOVERES' }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Signatory Position</label>
                        <input type="text" name="signatory_position" class="form-control" value="{{ $violation->details['signatory_position'] ?? 'PG Department Head/PHRM Officer' }}" required>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <div class="modal-footer bg-light border-top-0" style="border-bottom-left-radius:16px; border-bottom-right-radius:16px;">
        <button type="button" class="btn btn-light fw-medium px-4" data-bs-dismiss="modal" style="border-radius:8px; border:1px solid #e5e7eb;">Cancel</button>
        <button type="submit" class="btn btn-primary fw-bold px-4" style="border-radius:8px;"><i class="bi bi-check-lg me-1"></i> Update Letter</button>
    </div>
</form>

<script>
    (function () {
        const empSelect = document.getElementById('editLetterEmployeeSelect');
        if (!empSelect) return;
        const empPosition = document.getElementById('editLetterEmpPosition');
        const empOffice = document.getElementById('editLetterEmpOffice');

        function updateEmployeeDetails() {
            const opt = empSelect.options[empSelect.selectedIndex];
            if (opt && opt.value) {
                empPosition.value = opt.getAttribute('data-pos') || '';
                empOffice.value = opt.getAttribute('data-off') || '';
            } else {
                empPosition.value = '';
                empOffice.value = '';
            }
        }

        empSelect.addEventListener('change', updateEmployeeDetails);
    })();
</script>
