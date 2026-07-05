@extends('layouts.app')

@section('content')
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
    .btn-submit { background: #dc2626; color: #fff; padding: 10px 24px; border-radius: 8px; border: none; font-weight: 600; font-size: 14px; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .btn-submit:hover { background: #b91c1c; }
    .prior-warning-info { background: #fef3c7; border: 1px solid #fde68a; border-radius: 8px; padding: 14px 18px; margin-top: 16px; font-size: 13px; color: #92400e; display: none; }
    .prior-warning-info.visible { display: block; }
    .prior-warning-info i { color: #d97706; margin-right: 6px; }
</style>

<div>
    <h2 class="page-title">Generate Reprimand Letter</h2>
    <p class="page-subtitle">Issue a formal letter of reprimand for a repeat offense. This letter references the prior warning and informs the Provincial Discipline Committee.</p>

    <div x-data="{
        employees: {{ Js::from($employees) }},
        priorWarnings: {{ Js::from($priorWarnings) }},
        employeeIdsWithWarnings: {{ Js::from($employeeIdsWithWarnings) }},
        selectedId: '',
        selectedWarningId: '',
        employee: null,
        filteredWarnings: [],
        selectedWarning: null
    }" x-init="
        $watch('selectedId', id => {
            employee = employees.find(e => e.id == id);
            filteredWarnings = priorWarnings.filter(w => w.plantilla_record_id == id);
            selectedWarningId = '';
            selectedWarning = null;
        });
        $watch('selectedWarningId', id => {
            selectedWarning = filteredWarnings.find(w => w.id == id) || null;
        });
    ">
    
        <form method="POST" action="{{ route('leave-violations.store-reprimand') }}">
            @csrf
            
            <!-- Employee Info Card -->
            <div class="form-card">
                <div class="form-card-header">
                    <div class="form-card-icon" style="background:#fee2e2; color:#dc2626;"><i class="bi bi-person-exclamation"></i></div>
                    Employee Information
                </div>
                <div class="form-card-body">
                    <div style="margin-bottom: 20px;">
                        <label class="form-label">Employee Name <span>*</span></label>
                        <select name="plantilla_record_id" x-model="selectedId" class="form-control" required>
                            <option value="">Select employee with prior warning...</option>
                            <template x-for="emp in employees" :key="emp.id">
                                <option :value="emp.id" x-text="emp.last_name + ', ' + emp.first_name"
                                    :style="employeeIdsWithWarnings.includes(emp.id) ? 'font-weight:600;' : 'color:#9ca3af;'"></option>
                            </template>
                        </select>
                        <div class="help-text">Employees with prior warnings are shown in bold.</div>
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <label class="form-label">Prefix <span>*</span></label>
                        <select name="prefix" class="form-control" required style="width: 250px;">
                            <option value="">Select Prefix</option>
                            <option value="Mr.">Mr.</option>
                            <option value="Ms.">Ms.</option>
                            <option value="Mrs.">Mrs.</option>
                            <option value="Dr.">Dr.</option>
                            <option value="Engr.">Engr.</option>
                        </select>
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <label class="form-label">Position</label>
                        <input type="text" class="form-control" :value="employee ? employee.position_title : 'Auto-filled from employee'" readonly>
                    </div>
                    
                    <div>
                        <label class="form-label">Office / Department</label>
                        <input type="text" class="form-control" :value="employee ? employee.office_department : 'Auto-filled from employee'" readonly>
                    </div>
                </div>
            </div>
            
            <!-- Prior Warning Reference Card -->
            <div class="form-card">
                <div class="form-card-header">
                    <div class="form-card-icon" style="background:#fef3c7; color:#d97706;"><i class="bi bi-exclamation-triangle"></i></div>
                    Prior Warning Reference
                </div>
                <div class="form-card-body">
                    <div style="margin-bottom: 16px;">
                        <label class="form-label">Select the Prior Warning <span>*</span></label>
                        <select name="prior_warning_id" x-model="selectedWarningId" class="form-control" required>
                            <option value="">Select the prior warning to reference...</option>
                            <template x-for="w in filteredWarnings" :key="w.id">
                                <option :value="w.id" x-text="w.type_label + ' — ' + w.month + ' ' + w.year + ' (Issued: ' + w.issued_at + ')'"></option>
                            </template>
                        </select>
                        <div class="help-text">This links the reprimand to the specific prior warning. Only warnings for the selected employee are shown.</div>
                    </div>

                    <div class="prior-warning-info" :class="{ 'visible': selectedWarning }">
                        <i class="bi bi-info-circle-fill"></i>
                        <span x-text="selectedWarning ? 'Prior warning for ' + selectedWarning.type_label + ' (' + selectedWarning.month + ' ' + selectedWarning.year + ') was issued on ' + selectedWarning.issued_at + '. This date will appear in the reprimand letter.' : ''"></span>
                    </div>

                    <input type="hidden" name="base_violation_type" :value="selectedWarning ? (selectedWarning.violation_type) : ''">
                </div>
            </div>
            
            <!-- Current Violation Details Card -->
            <div class="form-card">
                <div class="form-card-header">
                    <div class="form-card-icon" style="background:#fee2e2; color:#dc2626;"><i class="bi bi-file-earmark-text"></i></div>
                    Current Violation Details
                </div>
                <div class="form-card-body" style="display:flex; gap:24px;">
                    <div style="flex:1;">
                        <label class="form-label">Month <span>*</span></label>
                        <select name="month" class="form-control" required>
                            <option value="">Select Month</option>
                            @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $m)
                                <option value="{{ $m }}">{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div style="flex:1;">
                        <label class="form-label">Year <span>*</span></label>
                        <select name="year" class="form-control" required>
                            <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                            <option value="{{ date('Y')-1 }}">{{ date('Y')-1 }}</option>
                        </select>
                    </div>
                    
                    <div style="flex:1;">
                        <label class="form-label">No. of Occurrences <span>*</span></label>
                        <input type="text" name="occurrences" class="form-control" placeholder='e.g. "three (3)" or "5"' required>
                        <div class="help-text">Number of tardiness/undertime occurrences for the current month.</div>
                    </div>
                </div>
            </div>
            
            <!-- Signatory Card -->
            <div class="form-card">
                <div class="form-card-header">
                    <div class="form-card-icon" style="background:#ecfdf5; color:#059669;"><i class="bi bi-shield-check"></i></div>
                    Signatory
                </div>
                <div class="form-card-body">
                    <div style="margin-bottom: 20px;">
                        <label class="form-label">Signatory Name</label>
                        <input type="text" name="signatory_name" class="form-control" value="AIDA B. LOVERES" required>
                    </div>
                    <div>
                        <label class="form-label">Signatory Position</label>
                        <input type="text" name="signatory_position" class="form-control" value="PG Department Head/PHRM Officer" required>
                    </div>
                </div>
            </div>
            
            <div class="submit-bar">
                <span>⚠ This letter will formally reprimand the employee and notify the Provincial Discipline Committee.</span>
                <button type="submit" class="btn-submit"><i class="bi bi-exclamation-triangle-fill"></i> Generate Reprimand Letter</button>
            </div>
        </form>
    </div>
</div>
@endsection
