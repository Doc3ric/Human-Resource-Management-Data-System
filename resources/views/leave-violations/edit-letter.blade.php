<x-dashboard-app>
<style>
    .page-title { font-size: 24px; font-weight: 700; color: #111827; margin: 0; }
    .page-subtitle { font-size: 14px; color: #6b7280; margin: 4px 0 24px; }
    .form-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; margin-bottom: 24px; }
    .form-card-header { padding: 16px 24px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; gap: 12px; font-size: 14px; font-weight: 600; color: #374151; }
    .form-card-icon { width: 32px; height: 32px; border-radius: 8px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; }
    .form-card-body { padding: 24px; }
    .form-label { display: block; font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px; }
    .form-label span { color: #ef4444; }
    .form-control { width: 100%; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; color: #111827; background: #fff; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .form-control:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
    .form-control[readonly] { background: #f9fafb; color: #9ca3af; }
    .help-text { font-size: 11px; color: #9ca3af; margin-top: 6px; }
    .submit-bar { display: flex; justify-content: flex-end; align-items: center; gap: 16px; margin-top: 16px; }
    .submit-bar span { font-size: 12px; color: #6b7280; }
    .btn-submit { background: #2563eb; color: #fff; padding: 10px 24px; border-radius: 8px; border: none; font-weight: 600; font-size: 14px; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .btn-submit:hover { background: #1d4ed8; }
</style>

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

<div>
    <h2 class="page-title">Edit Letter ({{ $titleLabel }})</h2>
    <p class="page-subtitle">Update the details of the generated formal notice letter.</p>

    <div>
        <form method="POST" action="{{ route('leave-violations.update-letter', $violation->id) }}">
            @csrf
            @method('PUT')

            @if($isPayroll)
                <!-- Payroll Coordination: employee and referenced violation are fixed; only the requested action can change. -->
                <div class="form-card">
                    <div class="form-card-header">
                        <div class="form-card-icon" style="background:#ccfbf1; color:#0f766e;"><i class="bi bi-person-badge"></i></div>
                        Employee &amp; Violation Reference
                    </div>
                    <div class="form-card-body">
                        <div style="margin-bottom: 20px;">
                            <label class="form-label">Employee</label>
                            <input type="text" class="form-control" value="{{ $violation->plantillaRecord->last_name }}, {{ $violation->plantillaRecord->first_name }}" readonly>
                        </div>
                        <div>
                            <label class="form-label">Referenced Violation</label>
                            <input type="text" class="form-control" value="{{ ucwords(strtolower(str_replace('_', ' ', $violation->violation_type))) }} — recorded {{ $violation->created_at->format('F d, Y') }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="form-card">
                    <div class="form-card-header">
                        <div class="form-card-icon" style="background:#e0e7ff; color:#4338ca;"><i class="bi bi-cash-stack"></i></div>
                        Payroll Action
                    </div>
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
                <!-- Show-Cause: free-text grounds instead of month/year/occurrences. -->
                <div class="form-card">
                    <div class="form-card-header">
                        <div class="form-card-icon"><i class="bi bi-person"></i></div>
                        Employee Information
                    </div>
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
                    <div class="form-card-header">
                        <div class="form-card-icon" style="background:#ede9fe; color:#6d28d9;"><i class="bi bi-file-earmark-text"></i></div>
                        Grounds
                    </div>
                    <div class="form-card-body">
                        <label class="form-label">Grounds for the Show-Cause Order <span>*</span></label>
                        <textarea name="grounds" class="form-control" rows="5" required>{{ $violation->details['grounds'] ?? '' }}</textarea>
                        <div class="help-text">Describe the facts the employee is being directed to explain.</div>
                    </div>
                </div>

                <div class="form-card">
                    <div class="form-card-header">
                        <div class="form-card-icon" style="background:#ecfdf5; color:#059669;"><i class="bi bi-shield-check"></i></div>
                        Signatory
                    </div>
                    <div class="form-card-body">
                        <div style="margin-bottom: 20px;">
                            <label class="form-label">Signatory Name</label>
                            <input type="text" name="signatory_name" class="form-control" value="{{ $violation->details['signatory_name'] ?? 'AIDA B. LOVERES' }}" required>
                        </div>
                        <div>
                            <label class="form-label">Signatory Position</label>
                            <input type="text" name="signatory_position" class="form-control" value="{{ $violation->details['signatory_position'] ?? 'PG Department Head/PHRM Officer' }}" required>
                        </div>
                    </div>
                </div>
            @else
                <input type="hidden" name="letter_type" value="{{ $violation->violation_type }}">

                <!-- Employee Info Card -->
                <div class="form-card">
                    <div class="form-card-header">
                        <div class="form-card-icon"><i class="bi bi-person"></i></div>
                        Employee Information
                    </div>
                    <div class="form-card-body">
                        <div style="margin-bottom: 20px;">
                            <label class="form-label">Employee Name <span>*</span></label>
                            <select name="plantilla_record_id" id="employeeSelect" class="form-control" required>
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

                        <div style="margin-bottom: 20px;">
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

                        <div style="margin-bottom: 20px;">
                            <label class="form-label">Position</label>
                            <input type="text" id="empPosition" class="form-control" value="Auto-filled from employee" readonly>
                        </div>

                        <div>
                            <label class="form-label">Office / Department</label>
                            <input type="text" id="empOffice" class="form-control" value="Auto-filled from employee" readonly>
                        </div>
                    </div>
                </div>

                <!-- Letter Details Card -->
                <div class="form-card">
                    <div class="form-card-header">
                        <div class="form-card-icon" style="background:#fef3c7; color:#d97706;"><i class="bi bi-file-text"></i></div>
                        Letter Details
                    </div>
                    <div class="form-card-body" style="display:flex; gap:24px;">
                        <div style="flex:1;">
                            <label class="form-label">Month <span>*</span></label>
                            <select name="month" class="form-control" required>
                                <option value="">Select Month</option>
                                @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $m)
                                    <option value="{{ $m }}" {{ ($violation->details['month'] ?? '') == $m ? 'selected' : '' }}>{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div style="flex:1;">
                            <label class="form-label">Year <span>*</span></label>
                            <select name="year" class="form-control" required>
                                <option value="{{ date('Y') }}" {{ ($violation->details['year'] ?? '') == date('Y') ? 'selected' : '' }}>{{ date('Y') }}</option>
                                <option value="{{ date('Y')-1 }}" {{ ($violation->details['year'] ?? '') == date('Y')-1 ? 'selected' : '' }}>{{ date('Y')-1 }}</option>
                            </select>
                        </div>

                        <div style="flex:1;">
                            <label class="form-label">No. of {{ $violation->violation_type == 'HABITUAL_TARDINESS' ? 'Tardiness' : 'Undertime' }} Occurrences <span>*</span></label>
                            <input type="text" name="occurrences" class="form-control" value="{{ $violation->details['occurrences'] ?? '' }}" placeholder="e.g. &quot;three (3)&quot; or &quot;5&quot;" required>
                            <div class="help-text">Appears as: "You have incurred ___ times of {{ strtolower($violation->violation_type == 'HABITUAL_TARDINESS' ? 'tardiness' : 'undertime') }}."</div>
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
                <!-- Reference Warning Details Card (Reprimand only) -->
                <div class="form-card">
                    <div class="form-card-header">
                        <div class="form-card-icon" style="background:#fee2e2; color:#dc2626;"><i class="bi bi-exclamation-triangle"></i></div>
                        Reference Warning Details
                    </div>
                    <div class="form-card-body">
                        <div class="help-text" style="margin-bottom:14px;">Select the prior warnings to reference in this reprimand.</div>

                        <div style="margin-bottom: 20px;">
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
                            <div class="help-text" style="margin-top:4px;">Hold Ctrl/Cmd to select multiple warnings.</div>
                        </div>

                        <hr style="margin:20px 0;">
                        <div class="help-text" style="margin-bottom:16px;">If you need to manually correct the text for the <strong>first</strong> referenced warning without altering its original record, you may override it below:</div>
                        <div style="display:flex; gap:24px; margin-bottom:20px;">
                            <div style="flex:1;">
                                <label class="form-label">Reference Warning Date</label>
                                <input type="date" name="prior_warning_date_override" class="form-control" value="{{ old('prior_warning_date_override', $priorDefaultDate) }}">
                            </div>
                            <div style="flex:1;">
                                <label class="form-label">Reference No.</label>
                                <input type="text" name="prior_warning_ref_override" class="form-control" value="{{ old('prior_warning_ref_override', $priorDefaultRef) }}" placeholder="e.g. LV-20260615-ABC123">
                            </div>
                        </div>
                        <div style="display:flex; gap:24px;">
                            <div style="flex:1;">
                                <label class="form-label">Prior Violation Month</label>
                                <select name="prior_warning_month_override" class="form-control">
                                    <option value="">Select Month</option>
                                    @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $m)
                                        <option value="{{ $m }}" {{ old('prior_warning_month_override', $priorDefaultMonth) == $m ? 'selected' : '' }}>{{ $m }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div style="flex:1;">
                                <label class="form-label">Prior Violation Year</label>
                                <input type="number" name="prior_warning_year_override" class="form-control" value="{{ old('prior_warning_year_override', $priorDefaultYear) }}">
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Signatory Card -->
                <div class="form-card">
                    <div class="form-card-header">
                        <div class="form-card-icon" style="background:#ecfdf5; color:#059669;"><i class="bi bi-shield-check"></i></div>
                        Signatory
                    </div>
                    <div class="form-card-body">
                        <div style="margin-bottom: 20px;">
                            <label class="form-label">Signatory Name</label>
                            <input type="text" name="signatory_name" class="form-control" value="{{ $violation->details['signatory_name'] ?? 'AIDA B. LOVERES' }}" required>
                        </div>
                        <div>
                            <label class="form-label">Signatory Position</label>
                            <input type="text" name="signatory_position" class="form-control" value="{{ $violation->details['signatory_position'] ?? 'PG Department Head/PHRM Officer' }}" required>
                        </div>
                    </div>
                </div>
            @endif

            <div class="submit-bar">
                <a href="{{ route('leave-violations.generated-letters') }}" class="btn btn-outline-secondary" style="border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 24px; color: #374151; background: #fff; text-decoration: none;">Cancel</a>
                <button type="submit" class="btn-submit"><i class="bi bi-check-lg"></i> Update Letter</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const empSelect = document.getElementById('employeeSelect');
        if (!empSelect) return;
        const empPosition = document.getElementById('empPosition');
        const empOffice = document.getElementById('empOffice');

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
        // Run once on load to populate current values
        updateEmployeeDetails();
    });
</script>
</x-dashboard-app>
