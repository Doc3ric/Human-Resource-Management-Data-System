<x-dashboard-app>
<style>
    .page-title { font-size: 24px; font-weight: 700; color: #111827; margin: 0; }
    .page-subtitle { font-size: 14px; color: #6b7280; margin: 4px 0 24px; }
    .form-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; margin-bottom: 24px; }
    .form-card-header { padding: 16px 24px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; gap: 12px; font-size: 14px; font-weight: 600; color: #374151; }
    .form-card-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
    .form-card-body { padding: 24px; }
    .form-label { display: block; font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px; }
    .form-label span { color: #ef4444; }
    .form-control { width: 100%; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; color: #111827; background: #fff; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .form-control:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
    .form-control[readonly] { background: #f9fafb; color: #9ca3af; }
    .help-text { font-size: 11px; color: #9ca3af; margin-top: 6px; }
    .submit-bar { display: flex; justify-content: flex-end; align-items: center; gap: 16px; margin-top: 16px; }
    .submit-bar span { font-size: 12px; color: #6b7280; }
    .btn-submit { background: #0f766e; color: #fff; padding: 10px 24px; border-radius: 8px; border: none; font-weight: 600; font-size: 14px; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .btn-submit:hover { background: #115e59; }
    .violation-info { background: #ecfeff; border: 1px solid #a5f3fc; border-radius: 8px; padding: 14px 18px; margin-top: 16px; font-size: 13px; color: #0e7490; display: none; }
    .violation-info.visible { display: block; }
    .violation-info i { color: #0891b2; margin-right: 6px; }
    .action-choice { display: flex; gap: 16px; }
    .action-option { flex: 1; border: 1px solid #d1d5db; border-radius: 8px; padding: 14px 16px; cursor: pointer; }
    .action-option:has(input:checked) { border-color: #0f766e; background: #f0fdfa; }
    .action-option input { margin-right: 8px; }
    .action-option-title { font-weight: 600; font-size: 13px; color: #111827; }
    .action-option-desc { font-size: 12px; color: #6b7280; margin-top: 4px; }
</style>

<div>
    <h2 class="page-title">Generate Payroll Coordination Letter</h2>
    <p class="page-subtitle">Request that Payroll drop an employee from the rolls or apply a salary deduction, based on an already-recorded AWOL / LWOP / Habitual Absenteeism entry.</p>

    <div x-data="{
        employees: {{ Js::from($employees) }},
        eligibleViolations: {{ Js::from($eligibleViolations) }},
        employeeIdsEligible: {{ Js::from($employeeIdsEligible) }},
        selectedId: '',
        selectedViolationId: '',
        employee: null,
        filteredViolations: [],
        selectedViolation: null
    }" x-init="
        $watch('selectedId', id => {
            employee = employees.find(e => e.id == id);
            filteredViolations = eligibleViolations.filter(v => v.plantilla_record_id == id);
            selectedViolationId = '';
            selectedViolation = null;
        });
        $watch('selectedViolationId', id => {
            selectedViolation = filteredViolations.find(v => v.id == id) || null;
        });
    ">

        <form method="POST" action="{{ route('leave-violations.store-payroll-coordination') }}">
            @csrf

            <!-- Employee Info Card -->
            <div class="form-card">
                <div class="form-card-header">
                    <div class="form-card-icon" style="background:#ccfbf1; color:#0f766e;"><i class="bi bi-person-badge"></i></div>
                    Employee Information
                </div>
                <div class="form-card-body">
                    <div>
                        <label class="form-label">Employee Name <span>*</span></label>
                        <select x-model="selectedId" class="form-control" required>
                            <option value="">Select employee with a recorded AWOL/LWOP entry...</option>
                            <template x-for="emp in employees" :key="emp.id">
                                <option :value="emp.id" x-text="emp.last_name + ', ' + emp.first_name"
                                    :style="employeeIdsEligible.includes(emp.id) ? 'font-weight:600;' : 'color:#9ca3af;'"></option>
                            </template>
                        </select>
                        <div class="help-text">Employees with an eligible violation are shown in bold.</div>
                    </div>
                </div>
            </div>

            <!-- Violation Reference Card -->
            <div class="form-card">
                <div class="form-card-header">
                    <div class="form-card-icon" style="background:#fef3c7; color:#d97706;"><i class="bi bi-exclamation-triangle"></i></div>
                    Violation Reference
                </div>
                <div class="form-card-body">
                    <div>
                        <label class="form-label">Select the Violation <span>*</span></label>
                        <select name="violation_id" x-model="selectedViolationId" class="form-control" required>
                            <option value="">Select the recorded violation to coordinate...</option>
                            <template x-for="v in filteredViolations" :key="v.id">
                                <option :value="v.id" x-text="v.type_label + ' — recorded ' + v.created_at"></option>
                            </template>
                        </select>
                        <div class="help-text">Only AWOL, LWOP, and Habitual Absenteeism entries not yet coordinated with payroll are shown. Only entries for the selected employee are listed.</div>
                    </div>

                    <div class="violation-info" :class="{ 'visible': selectedViolation }">
                        <i class="bi bi-info-circle-fill"></i>
                        <span x-text="selectedViolation ? selectedViolation.type_label + ' recorded on ' + selectedViolation.created_at + ' will be referenced in the coordination letter.' : ''"></span>
                    </div>
                </div>
            </div>

            <!-- Action Card -->
            <div class="form-card">
                <div class="form-card-header">
                    <div class="form-card-icon" style="background:#e0e7ff; color:#4338ca;"><i class="bi bi-cash-stack"></i></div>
                    Payroll Action
                </div>
                <div class="form-card-body">
                    <label class="form-label">Requested Action <span>*</span></label>
                    <div class="action-choice">
                        <label class="action-option">
                            <input type="radio" name="action_type" value="DROP" required>
                            <span class="action-option-title">Drop from Payroll</span>
                            <div class="action-option-desc">Request that the employee be dropped from the payroll effective immediately.</div>
                        </label>
                        <label class="action-option">
                            <input type="radio" name="action_type" value="DEDUCT" required>
                            <span class="action-option-title">Salary Deduction</span>
                            <div class="action-option-desc">Request a salary deduction corresponding to the recorded LWOP/undertime days.</div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="submit-bar">
                <span>⚠ This letter will be transmitted to Payroll and saved to the employee's 201 file.</span>
                <button type="submit" class="btn-submit"><i class="bi bi-send-fill"></i> Generate Payroll Coordination Letter</button>
            </div>
        </form>
    </div>
</div>
</x-dashboard-app>
