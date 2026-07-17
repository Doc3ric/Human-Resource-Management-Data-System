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
        selectedId: '',
        selectedWarningIds: [],
        employee: null,
        filteredWarnings: [],
        selectedWarnings: []
    }" x-init="
        $nextTick(() => {
            lvWireEmployeeCombobox($refs.employeeSearch, $refs.employeeDatalist, $refs.employeeIdInput, employees, id => selectedId = id);
        });
        $watch('selectedId', id => {
            employee = employees.find(e => e.id == id);
            filteredWarnings = priorWarnings.filter(w => w.plantilla_record_id == id);
            selectedWarningIds = [];
            selectedWarnings = [];
            lvPopulateWarningCheckboxes($refs.warningsContainer, filteredWarnings, () => {
                selectedWarningIds = lvCheckedWarningIds($refs.warningsContainer);
            });
        });
        $watch('selectedWarningIds', ids => {
            selectedWarnings = ids.map(id => filteredWarnings.find(w => w.id == id)).filter(w => w);
        });
    ">

        <form method="POST" action="{{ route('leave-violations.store-reprimand') }}"
            @submit="if (selectedWarningIds.length === 0) { $event.preventDefault(); alert('Please check at least one prior warning to reference in this reprimand.'); }">
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
                        <input type="text" x-ref="employeeSearch" list="reprimandEmployeeList" class="form-control" placeholder="Type or select employee name..." autocomplete="off" required>
                        <datalist x-ref="employeeDatalist" id="reprimandEmployeeList"></datalist>
                        <input type="hidden" name="plantilla_record_id" x-ref="employeeIdInput">
                        <div class="help-text">Only employees with a recorded prior warning are listed.</div>
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
                        <label class="form-label">Select Prior Warning(s) <span>*</span></label>
                        <div x-ref="warningsContainer" class="form-control" style="height:auto; min-height:80px; max-height:180px; overflow-y:auto;">
                            <div class="text-muted" style="font-size:13px;">Select an employee above first.</div>
                        </div>
                        <div class="help-text">Check every prior warning this reprimand should reference — all checked warnings will be listed in the generated letter with their reference number and date.</div>
                    </div>

                    <div class="prior-warning-info" :class="{ 'visible': selectedWarningIds.length > 0 }">
                        <i class="bi bi-info-circle-fill"></i>
                        <span x-text="selectedWarningIds.length > 0 ? selectedWarningIds.length + ' prior warning(s) selected.' : ''"></span>
                    </div>

                    <input type="hidden" name="base_violation_type" :value="selectedWarnings.length > 0 ? selectedWarnings[0].violation_type.replace('REPRIMAND_', '') : ''">
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
<script>
    // A single type-to-filter control for Employee Name: a text input backed by a
    // <datalist> (native browser suggestion list) plus a hidden field carrying the
    // resolved plantilla_record_id. Replaces a separate search box + <select> pair —
    // one control the user can both type into and pick from.
    function lvWireEmployeeCombobox(textInputEl, datalistEl, hiddenIdInputEl, employees, onChange) {
        if (!textInputEl || !datalistEl || !hiddenIdInputEl) return;
        var byName = {};
        employees.forEach(function (emp) {
            var label = emp.last_name + ', ' + emp.first_name;
            var opt = document.createElement('option');
            opt.value = label;
            datalistEl.appendChild(opt);
            byName[label] = emp.id;
        });
        textInputEl.addEventListener('input', function () {
            var id = byName[this.value] || '';
            hiddenIdInputEl.value = id;
            if (onChange) onChange(id);
        });
    }

    // Renders one checkbox per prior warning — obvious multi-select, unlike a native
    // <select multiple> which silently requires holding Ctrl/Cmd to pick more than one.
    function lvPopulateWarningCheckboxes(containerEl, warnings, onChange) {
        if (!containerEl) return;
        containerEl.innerHTML = '';
        if (warnings.length === 0) {
            containerEl.innerHTML = '<div class="text-muted" style="font-size:13px;">No prior warnings found for this employee.</div>';
            return;
        }
        warnings.forEach(function (w) {
            var wrapper = document.createElement('div');
            wrapper.style.marginBottom = '8px';

            var checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.name = 'prior_warning_ids[]';
            checkbox.value = w.id;
            checkbox.id = 'pw-' + w.id;
            checkbox.style.marginRight = '8px';
            checkbox.addEventListener('change', onChange);

            var label = document.createElement('label');
            label.htmlFor = checkbox.id;
            label.style.fontSize = '13px';
            label.style.cursor = 'pointer';
            label.textContent = w.type_label + ' — ' + w.month + ' ' + w.year + ' (Issued: ' + w.issued_at + ')';

            wrapper.appendChild(checkbox);
            wrapper.appendChild(label);
            containerEl.appendChild(wrapper);
        });
    }

    function lvCheckedWarningIds(containerEl) {
        if (!containerEl) return [];
        return Array.from(containerEl.querySelectorAll('input[type=checkbox]:checked')).map(function (cb) {
            return cb.value;
        });
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</x-dashboard-app>
