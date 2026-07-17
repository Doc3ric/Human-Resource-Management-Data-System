<x-dashboard-app>
<style>
    .premium-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    .table-header { border-bottom: 1px solid #e5e7eb; background: #f9fafb; font-size: 10px; color: #6b7280; text-transform: uppercase; font-weight: 600; text-align: left; padding: 12px 24px; }
    .table-td { border-bottom: 1px solid #e5e7eb; padding: 16px 24px; font-size: 13px; color: #374151; vertical-align: middle; }
    .pill { display: inline-block; padding: 4px 10px; border-radius: 9999px; font-size: 11px; font-weight: 600; }
    .pill-tardy { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
    .pill-undertime { background: #dbeafe; color: #2563eb; border: 1px solid #bfdbfe; }
    .avatar-circle { width: 32px; height: 32px; border-radius: 50%; background: #2563eb; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; }
    .table-btn { font-size: 11px; padding: 6px 10px; border-radius: 4px; border: 1px solid transparent; background: transparent; display: inline-flex; align-items: center; gap: 4px; cursor: pointer; font-weight: 500; }
    .btn-edit { color: #2563eb; } .btn-edit:hover { background: #eff6ff; border-color: #bfdbfe; }
    .btn-generate { color: #059669; } .btn-generate:hover { background: #ecfdf5; border-color: #a7f3d0; }
    .btn-delete { color: #4b5563; } .btn-delete:hover { background: #f3f4f6; border-color: #e5e7eb; }
    .pill-reprimand { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
    .pill-show-cause { background: #ede9fe; color: #6d28d9; border: 1px solid #ddd6fe; }
    .pill-payroll { background: #ccfbf1; color: #0f766e; border: 1px solid #99f6e4; }

    .btn-dynamic { padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); text-decoration: none; border: none; color: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .btn-dynamic:hover { transform: translateY(-2px); box-shadow: 0 6px 12px rgba(0,0,0,0.15); color: #fff; }
    .btn-dynamic:active { transform: translateY(0); }
    .btn-tardy { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .btn-undertime { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    .btn-reprimand { background: linear-gradient(135deg, #ef4444, #dc2626); }
    .btn-show-cause { background: linear-gradient(135deg, #8b5cf6, #6d28d9); }
    .btn-payroll { background: linear-gradient(135deg, #14b8a6, #0f766e); }

    .scoreboard-container { display: flex; gap: 16px; margin-bottom: 24px; flex-wrap: wrap; }
    .kpi-card { flex: 1; min-width: 280px; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; display: flex; flex-direction: column; position: relative; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    .kpi-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
    .kpi-pill { font-size: 10px; font-weight: 800; padding: 4px 10px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.5px; }
    .kpi-icon-box { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 16px; }
    .kpi-label { font-size: 11px; font-weight: 700; color: #4b5563; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
    .kpi-value { font-size: 32px; font-weight: 900; color: #111827; line-height: 1; margin-bottom: 6px; }
    .kpi-sub { font-size: 13px; font-weight: 600; margin-bottom: 16px; }
    .kpi-divider { height: 2px; width: 100%; margin-bottom: 12px; border-radius: 2px; }
    .kpi-footer { font-size: 11.5px; color: #6b7280; line-height: 1.4; display: flex; gap: 6px; }
    .kpi-footer i { font-size: 12px; margin-top: 1px; }

    .theme-primary .kpi-pill { background: #e0e7ff; color: #4338ca; }
    .theme-primary .kpi-icon-box { background: #e0e7ff; color: #4338ca; }
    .theme-primary .kpi-sub { color: #4338ca; }
    .theme-primary .kpi-divider { background: #c7d2fe; }
    .theme-primary .kpi-footer i { color: #818cf8; }

    .theme-warning .kpi-pill { background: #fef3c7; color: #b45309; }
    .theme-warning .kpi-icon-box { background: #fef3c7; color: #b45309; }
    .theme-warning .kpi-sub { color: #b45309; }
    .theme-warning .kpi-divider { background: #fde68a; }
    .theme-warning .kpi-footer i { color: #fbbf24; }

    .theme-danger .kpi-pill { background: #fee2e2; color: #be123c; }
    .theme-danger .kpi-icon-box { background: #fee2e2; color: #be123c; }
    .theme-danger .kpi-sub { color: #be123c; }
    .theme-danger .kpi-divider { background: #fecaca; }
    .theme-danger .kpi-footer i { color: #fb7185; }

    .theme-purple .kpi-pill { background: #ede9fe; color: #6d28d9; }
    .theme-purple .kpi-icon-box { background: #ede9fe; color: #6d28d9; }
    .theme-purple .kpi-sub { color: #6d28d9; }
    .theme-purple .kpi-divider { background: #ddd6fe; }
    .theme-purple .kpi-footer i { color: #a78bfa; }

    .theme-teal .kpi-pill { background: #ccfbf1; color: #0f766e; }
    .theme-teal .kpi-icon-box { background: #ccfbf1; color: #0f766e; }
    .theme-teal .kpi-sub { color: #0f766e; }
    .theme-teal .kpi-divider { background: #99f6e4; }
    .theme-teal .kpi-footer i { color: #2dd4bf; }

    /* Scoped to the "New Letter" popup forms only */
    .lv-letter-form .form-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; margin-bottom: 20px; }
    .lv-letter-form .form-card-header { padding: 14px 20px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; gap: 12px; font-size: 13px; font-weight: 600; color: #374151; }
    .lv-letter-form .form-card-icon { width: 30px; height: 30px; border-radius: 8px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .lv-letter-form .form-card-body { padding: 20px; }
    .lv-letter-form .form-label { display: block; font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px; }
    .lv-letter-form .form-label span { color: #ef4444; }
    .lv-letter-form .form-control { width: 100%; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; color: #111827; background: #fff; }
    .lv-letter-form .form-control:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
    .lv-letter-form .form-control[readonly] { background: #f9fafb; color: #9ca3af; }
    .lv-letter-form .help-text { font-size: 11px; color: #9ca3af; margin-top: 6px; }
    .lv-letter-form .prior-warning-info, .lv-letter-form .violation-info { background: #fef3c7; border: 1px solid #fde68a; border-radius: 8px; padding: 12px 16px; margin-top: 14px; font-size: 12.5px; color: #92400e; display: none; }
    .lv-letter-form .prior-warning-info.visible, .lv-letter-form .violation-info.visible { display: block; }
    .lv-letter-form .action-choice { display: flex; gap: 14px; flex-wrap: wrap; }
    .lv-letter-form .action-option { flex: 1; min-width: 200px; border: 1px solid #d1d5db; border-radius: 8px; padding: 12px 14px; cursor: pointer; }
    .lv-letter-form .action-option:has(input:checked) { border-color: #0f766e; background: #f0fdfa; }
    .lv-letter-form .action-option input { margin-right: 8px; }
    .lv-letter-form .action-option-title { font-weight: 600; font-size: 13px; color: #111827; }
    .lv-letter-form .action-option-desc { font-size: 11.5px; color: #6b7280; margin-top: 4px; }
</style>

<div style="margin-bottom: 14px;">
    <h2 style="font-size: 24px; font-weight: 700; color: #111827; margin: 0;">Generated Letters</h2>
    <p style="font-size: 14px; color: #6b7280; margin: 4px 0 0;">Storage for all created Tardy and Undertime letters.</p>
</div>

<div class="scoreboard-container">
    <div class="kpi-card theme-primary">
        <div class="kpi-header">
            <span class="kpi-pill">DUE PROCESS</span>
            <div class="kpi-icon-box"><i class="bi bi-envelope-paper"></i></div>
        </div>
        <div class="kpi-label">Total Letters</div>
        <div class="kpi-value">{{ number_format($metrics['total']) }}</div>
        <div class="kpi-sub">100%</div>
        <div class="kpi-divider"></div>
        <div class="kpi-footer">
            <i class="bi bi-info-circle"></i>
            <span>Official letters issued serve as formal notices for administrative proceedings.</span>
        </div>
    </div>
    
    <div class="kpi-card theme-warning">
        <div class="kpi-header">
            <span class="kpi-pill">CSC MC 1</span>
            <div class="kpi-icon-box"><i class="bi bi-clock-history"></i></div>
        </div>
        <div class="kpi-label">Tardy / Undertime</div>
        <div class="kpi-value">{{ number_format($metrics['tardy_undertime']) }}</div>
        <div class="kpi-sub">{{ $metrics['tardy_undertime_pct'] }}% of Letters</div>
        <div class="kpi-divider"></div>
        <div class="kpi-footer">
            <i class="bi bi-info-circle"></i>
            <span>Tardiness of 10 times in a month for 2 consecutive months is habitual.</span>
        </div>
    </div>

    <div class="kpi-card theme-danger">
        <div class="kpi-header">
            <span class="kpi-pill">DISCIPLINARY</span>
            <div class="kpi-icon-box"><i class="bi bi-exclamation-triangle"></i></div>
        </div>
        <div class="kpi-label">Reprimand Letters</div>
        <div class="kpi-value">{{ number_format($metrics['reprimand']) }}</div>
        <div class="kpi-sub">{{ $metrics['reprimand_pct'] }}% of Letters</div>
        <div class="kpi-divider"></div>
        <div class="kpi-footer">
            <i class="bi bi-info-circle"></i>
            <span>Reprimands are the first tier of administrative penalties prior to suspension.</span>
        </div>
    </div>

    <div class="kpi-card theme-purple">
        <div class="kpi-header">
            <span class="kpi-pill">RACCS</span>
            <div class="kpi-icon-box"><i class="bi bi-file-earmark-text"></i></div>
        </div>
        <div class="kpi-label">Show-Cause Orders</div>
        <div class="kpi-value">{{ number_format($metrics['show_cause']) }}</div>
        <div class="kpi-sub">{{ $metrics['show_cause_pct'] }}% of Letters</div>
        <div class="kpi-divider"></div>
        <div class="kpi-footer">
            <i class="bi bi-info-circle"></i>
            <span>Notices to explain, issued before any penalty is recommended.</span>
        </div>
    </div>

    <div class="kpi-card theme-teal">
        <div class="kpi-header">
            <span class="kpi-pill">PAYROLL</span>
            <div class="kpi-icon-box"><i class="bi bi-cash-stack"></i></div>
        </div>
        <div class="kpi-label">Payroll Coordination</div>
        <div class="kpi-value">{{ number_format($metrics['payroll_coordination']) }}</div>
        <div class="kpi-sub">{{ $metrics['payroll_coordination_pct'] }}% of Letters</div>
        <div class="kpi-divider"></div>
        <div class="kpi-footer">
            <i class="bi bi-info-circle"></i>
            <span>Drop-from-payroll and salary deduction letters transmitted to Payroll.</span>
        </div>
    </div>
</div>

<div style="display:flex; justify-content: center; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 14px;">
    <div style="position:relative;">
        <input type="text" placeholder="Search reference, employee" style="padding:8px 12px 8px 32px; border-radius:8px; border:1px solid #d1d5db; font-size:13px; width:220px;">
        <i class="bi bi-search" style="position:absolute; left:12px; top:10px; color:#9ca3af; font-size:12px;"></i>
    </div>
    <button class="btn btn-outline-secondary btn-sm" style="background:#fff;"><i class="bi bi-funnel"></i> Filter</button>
    <button type="button" class="btn-dynamic btn-tardy" data-bs-toggle="modal" data-bs-target="#tardyUndertimeModal" data-letter-type="HABITUAL_TARDINESS"><i class="bi bi-plus-lg"></i> New Tardy Letter</button>
    <button type="button" class="btn-dynamic btn-undertime" data-bs-toggle="modal" data-bs-target="#tardyUndertimeModal" data-letter-type="UNDERTIME"><i class="bi bi-plus-lg"></i> New Undertime Letter</button>
    <button type="button" class="btn-dynamic btn-reprimand" data-bs-toggle="modal" data-bs-target="#reprimandModal"><i class="bi bi-exclamation-triangle-fill"></i> New Reprimand Letter</button>
    <button type="button" class="btn-dynamic btn-show-cause" data-bs-toggle="modal" data-bs-target="#showCauseModal"><i class="bi bi-file-earmark-text"></i> New Show-Cause Order</button>
    <button type="button" class="btn-dynamic btn-payroll" data-bs-toggle="modal" data-bs-target="#payrollCoordinationModal"><i class="bi bi-cash-stack"></i> New Payroll Coordination</button>
</div>

<div class="premium-card">
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 24px; border-bottom: 1px solid #e5e7eb;">
        <h3 style="font-size: 14px; font-weight: 600; margin: 0;">All Generated Letters</h3>
        <div style="font-size: 12px; color: #9ca3af;">{{ $letters->total() }} letter(s) total</div>
    </div>
    
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th class="table-header">REFERENCE NO.</th>
                    <th class="table-header">TYPE</th>
                    <th class="table-header">EMPLOYEE</th>
                    <th class="table-header">POSITION / OFFICE</th>
                    <th class="table-header">MONTH / YEAR</th>
                    <th class="table-header">OCCURRENCES</th>
                    <th class="table-header">CREATED BY</th>
                    <th class="table-header">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($letters as $letter)
                <tr>
                    <td class="table-td" style="color: #6b7280; font-weight: 500;">{{ $letter->details['reference_no'] ?? ('LV-'.$letter->id) }}</td>
                    <td class="table-td">
                        @if($letter->status == 'escalated')
                            <span class="pill pill-payroll"><i class="bi bi-cash-stack"></i> Payroll ({{ $letter->details['payroll_action_type'] ?? '—' }})</span>
                        @elseif($letter->violation_type == 'HABITUAL_TARDINESS')
                            <span class="pill pill-tardy"><i class="bi bi-clock-history"></i> Tardy</span>
                        @elseif($letter->violation_type == 'UNDERTIME')
                            <span class="pill pill-undertime"><i class="bi bi-hourglass-split"></i> Undertime</span>
                        @elseif($letter->violation_type == 'REPRIMAND_HABITUAL_TARDINESS')
                            <span class="pill pill-reprimand"><i class="bi bi-exclamation-triangle"></i> Reprimand (Tardy)</span>
                        @elseif($letter->violation_type == 'REPRIMAND_UNDERTIME')
                            <span class="pill pill-reprimand"><i class="bi bi-exclamation-triangle"></i> Reprimand (Undertime)</span>
                        @elseif($letter->violation_type == 'SHOW_CAUSE')
                            <span class="pill pill-show-cause"><i class="bi bi-file-earmark-text"></i> Show-Cause</span>
                        @else
                            <span class="pill pill-undertime"><i class="bi bi-hourglass-split"></i> {{ $letter->violation_type }}</span>
                        @endif
                    </td>
                    <td class="table-td" style="font-weight: 500;">
                        {{ $letter->details['prefix'] ?? '' }} {{ $letter->plantillaRecord->last_name }}, {{ $letter->plantillaRecord->first_name }}
                    </td>
                    <td class="table-td">
                        <div>{{ $letter->plantillaRecord->position_title }}</div>
                        <div style="font-size: 11px; color: #9ca3af; margin-top:2px;">{{ $letter->plantillaRecord->office_department }}</div>
                    </td>
                    <td class="table-td">{{ $letter->details['month'] ?? '—' }} {{ $letter->details['year'] ?? '' }}</td>
                    <td class="table-td"><b>{{ $letter->details['occurrences'] ?? '0' }}</b> time(s)</td>
                    <td class="table-td">
                        @php
                            $creatorName = $letter->details['creator_name'] ?? ($letter->issuedBy->first_name ?? ($letter->issuedBy->name ?? 'System'));
                            $creatorInitial = substr($creatorName, 0, 1);
                        @endphp
                        <div style="display:flex; align-items:center; gap:8px;">
                            <div class="avatar-circle" style="background:#f3f4f6; color:#4b5563;">{{ strtoupper($creatorInitial) }}</div>
                            <div style="font-size:10px; color:#9ca3af; line-height:1.2;">
                                <div style="font-weight:bold; color:#4b5563;">{{ $creatorName }}</div>
                                <div>{{ date('M d, Y', strtotime($letter->created_at)) }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="table-td">
                        <div style="display:flex; gap:4px;">
                            <button type="button" class="table-btn btn-edit" data-bs-toggle="modal" data-bs-target="#editLetterModal" data-edit-url="{{ route('leave-violations.edit-letter', $letter->id) }}"><i class="bi bi-pencil-square"></i> Edit</button>
                            <a href="{{ route('leave-violations.download-pdf', $letter->id) }}" target="_blank" class="table-btn btn-generate" style="text-decoration:none;"><i class="bi bi-file-pdf"></i> Generate</a>
                            <form action="{{ route('leave-violations.destroy-letter', $letter->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this letter?');" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="table-btn btn-delete"><i class="bi bi-trash"></i> Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
                
                @if($letters->isEmpty())
                <tr><td colspan="8" class="table-td" style="text-align: center; padding: 40px; color: #9ca3af;">No generated letters found.</td></tr>
                @endif
            </tbody>
        </table>
    </div>
    
    <div style="padding: 16px 24px; font-size:12px; color:#9ca3af; border-top:1px solid #e5e7eb;">
        {{ $letters->total() }} letter(s) total
    </div>
</div>

<script>
    // Shared once across all "New Letter" modals below — avoids re-embedding the
    // full employee/warning/violation lists 4x on a roster this size (5,000+).
    window.lvEmployees = {{ Js::from($employees) }};
    window.lvEmployeesWithPriorWarnings = {{ Js::from($employeesWithPriorWarnings) }};
    window.lvPriorWarnings = {{ Js::from($priorWarnings) }};
    window.lvEligibleViolations = {{ Js::from($eligibleViolations) }};
    window.lvEmployeeIdsEligible = {{ Js::from($employeeIdsEligible) }};

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

    // Removes previously-populated dynamic options while preserving a static
    // placeholder (value="") if the <select> has one.
    function lvClearDynamicOptions(selectEl) {
        Array.from(selectEl.options).forEach(function (opt) {
            if (opt.value !== '') opt.remove();
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

    function lvPopulateViolationOptions(selectEl, violations) {
        if (!selectEl) return;
        lvClearDynamicOptions(selectEl);
        violations.forEach(function (v) {
            var opt = document.createElement('option');
            opt.value = v.id;
            opt.textContent = v.type_label + ' — recorded ' + v.created_at;
            selectEl.appendChild(opt);
        });
    }
</script>

{{-- New Tardy / Undertime Letter modal --}}
<div class="modal fade" id="tardyUndertimeModal" tabindex="-1" aria-labelledby="tardyUndertimeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg lv-letter-form" style="border-radius:16px;"
            x-data="{
                employees: window.lvEmployees,
                selectedId: '',
                employee: null,
                letterType: 'HABITUAL_TARDINESS'
            }" x-init="
                $nextTick(() => {
                    lvWireEmployeeCombobox($refs.employeeSearch, $refs.employeeDatalist, $refs.employeeIdInput, employees, id => selectedId = id);
                });
                $watch('selectedId', id => employee = employees.find(e => e.id == id));
            ">
            <div class="modal-header" style="background: linear-gradient(135deg, #f59e0b, #d97706); color:#fff; border-top-left-radius:16px; border-top-right-radius:16px; border-bottom:none;">
                <h5 class="modal-title fw-bold" id="tardyUndertimeModalLabel">
                    <i class="bi bi-clock-history me-2"></i>
                    <span x-text="letterType === 'UNDERTIME' ? 'Generate Undertime Letter' : 'Generate Tardy Letter'"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('leave-violations.store-letter') }}">
                @csrf
                <input type="hidden" name="letter_type" :value="letterType">
                <div class="modal-body p-4">
                    <div class="form-card">
                        <div class="form-card-header"><div class="form-card-icon"><i class="bi bi-person"></i></div>Employee Information</div>
                        <div class="form-card-body">
                            <div style="margin-bottom: 16px;">
                                <label class="form-label">Employee Name <span>*</span></label>
                                <input type="text" x-ref="employeeSearch" list="tardyEmployeeList" class="form-control" placeholder="Type or select employee name..." autocomplete="off" required>
                                <datalist x-ref="employeeDatalist" id="tardyEmployeeList"></datalist>
                                <input type="hidden" name="plantilla_record_id" x-ref="employeeIdInput">
                            </div>
                            <div style="margin-bottom: 16px;">
                                <label class="form-label">Prefix <span>*</span></label>
                                <select name="prefix" class="form-control" required style="width: 250px;">
                                    <option value="">Select Prefix</option>
                                    <option value="Mr.">Mr.</option><option value="Ms.">Ms.</option><option value="Mrs.">Mrs.</option><option value="Dr.">Dr.</option><option value="Engr.">Engr.</option>
                                </select>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Position</label>
                                    <input type="text" class="form-control" :value="employee ? employee.position_title : 'Auto-filled from employee'" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Office / Department</label>
                                    <input type="text" class="form-control" :value="employee ? employee.office_department : 'Auto-filled from employee'" readonly>
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
                                        <option value="{{ $m }}">{{ $m }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Year <span>*</span></label>
                                <select name="year" class="form-control" required>
                                    <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                                    <option value="{{ date('Y')-1 }}">{{ date('Y')-1 }}</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">No. of Occurrences <span>*</span></label>
                                <input type="text" name="occurrences" class="form-control" placeholder='e.g. "three (3)" or "5"' required>
                                <div class="help-text" x-text="'Appears as: You have incurred ___ times of ' + (letterType === 'UNDERTIME' ? 'undertime.' : 'tardiness.')"></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-card mb-0">
                        <div class="form-card-header"><div class="form-card-icon" style="background:#ecfdf5; color:#059669;"><i class="bi bi-shield-check"></i></div>Signatory</div>
                        <div class="form-card-body row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Signatory Name</label>
                                <input type="text" name="signatory_name" class="form-control" value="AIDA B. LOVERES" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Signatory Position</label>
                                <input type="text" name="signatory_position" class="form-control" value="PG Department Head/PHRM Officer" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0" style="border-bottom-left-radius:16px; border-bottom-right-radius:16px;">
                    <button type="button" class="btn btn-light fw-medium px-4" data-bs-dismiss="modal" style="border-radius:8px; border:1px solid #e5e7eb;">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4" style="border-radius:8px;"><i class="bi bi-check-lg me-1"></i> Save to Generated Letters</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- New Reprimand Letter modal --}}
<div class="modal fade" id="reprimandModal" tabindex="-1" aria-labelledby="reprimandModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg lv-letter-form" style="border-radius:16px;"
            x-data="{
                employees: window.lvEmployeesWithPriorWarnings,
                priorWarnings: window.lvPriorWarnings,
                selectedId: '',
                selectedWarningIds: [],
                employee: null,
                filteredWarnings: [],
                selectedWarnings: [],
                positionTitle: '',
                officeDepartment: ''
            }" x-init="
                $nextTick(() => {
                    lvWireEmployeeCombobox($refs.employeeSearch, $refs.employeeDatalist, $refs.employeeIdInput, employees, id => selectedId = id);
                });
                $watch('selectedId', id => {
                    employee = employees.find(e => e.id == id);
                    if (employee) {
                        positionTitle = employee.position_title;
                        officeDepartment = employee.office_department;
                    } else {
                        positionTitle = '';
                        officeDepartment = '';
                    }
                    filteredWarnings = priorWarnings.filter(w => w.plantilla_record_id == id);
                    selectedWarningIds = []; selectedWarnings = [];
                    lvPopulateWarningCheckboxes($refs.warningsContainer, filteredWarnings, () => {
                        selectedWarningIds = lvCheckedWarningIds($refs.warningsContainer);
                    });
                });
                $watch('selectedWarningIds', ids => {
                    selectedWarnings = ids.map(id => filteredWarnings.find(w => w.id == id)).filter(w => w);
                });
            ">
            <div class="modal-header" style="background: linear-gradient(135deg, #ef4444, #dc2626); color:#fff; border-top-left-radius:16px; border-top-right-radius:16px; border-bottom:none;">
                <h5 class="modal-title fw-bold" id="reprimandModalLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i>Generate Reprimand Letter</h5>
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('leave-violations.store-reprimand') }}"
                @submit="if (selectedWarningIds.length === 0) { $event.preventDefault(); alert('Please check at least one prior warning to reference in this reprimand.'); }">
                @csrf
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Issues a formal letter of reprimand for a repeat offense, referencing the prior warning and notifying the Provincial Discipline Committee.</p>

                    <div class="form-card">
                        <div class="form-card-header"><div class="form-card-icon" style="background:#fee2e2; color:#dc2626;"><i class="bi bi-person-exclamation"></i></div>Employee Information</div>
                        <div class="form-card-body">
                            <div style="margin-bottom: 16px;">
                                <label class="form-label">Employee Name <span>*</span></label>
                                <input type="text" x-ref="employeeSearch" list="reprimandModalEmployeeList" class="form-control" placeholder="Type or select employee name..." autocomplete="off" required>
                                <datalist x-ref="employeeDatalist" id="reprimandModalEmployeeList"></datalist>
                                <input type="hidden" name="plantilla_record_id" x-ref="employeeIdInput">
                                <div class="help-text">Only employees with a recorded prior warning are listed.</div>
                            </div>
                            <div style="margin-bottom: 16px;">
                                <label class="form-label">Prefix <span>*</span></label>
                                <select name="prefix" class="form-control" required style="width: 250px;">
                                    <option value="">Select Prefix</option>
                                    <option value="Mr.">Mr.</option><option value="Ms.">Ms.</option><option value="Mrs.">Mrs.</option><option value="Dr.">Dr.</option><option value="Engr.">Engr.</option>
                                </select>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Position</label>
                                    <input type="text" name="position_title" class="form-control" x-model="positionTitle">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Office / Department</label>
                                    <input type="text" name="office_department" class="form-control" x-model="officeDepartment">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-card">
                        <div class="form-card-header"><div class="form-card-icon" style="background:#fef3c7; color:#d97706;"><i class="bi bi-exclamation-triangle"></i></div>Prior Warning Reference</div>
                        <div class="form-card-body">
                            <label class="form-label">Select Prior Warning(s) <span>*</span></label>
                            <div x-ref="warningsContainer" class="form-control" style="height:auto; min-height:80px; max-height:180px; overflow-y:auto;">
                                <div class="text-muted" style="font-size:13px;">Select an employee above first.</div>
                            </div>
                            <div class="help-text">Check every prior warning this reprimand should reference — all checked warnings will be listed in the generated letter with their reference number and date.</div>
                            <div class="prior-warning-info" :class="{ 'visible': selectedWarningIds.length > 0 }">
                                <i class="bi bi-info-circle-fill"></i>
                                <span x-text="selectedWarningIds.length > 0 ? selectedWarningIds.length + ' prior warning(s) selected.' : ''"></span>
                            </div>
                            <input type="hidden" name="base_violation_type" :value="filteredWarnings.length > 0 && selectedWarningIds.length > 0 ? filteredWarnings.find(w => w.id == selectedWarningIds[0]).violation_type.replace('REPRIMAND_', '') : ''">
                        </div>
                    </div>

                    <div class="form-card">
                        <div class="form-card-header"><div class="form-card-icon" style="background:#fee2e2; color:#dc2626;"><i class="bi bi-file-earmark-text"></i></div>Current Violation Details</div>
                        <div class="form-card-body row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Month <span>*</span></label>
                                <select name="month" class="form-control" required>
                                    <option value="">Select Month</option>
                                    @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $m)
                                        <option value="{{ $m }}">{{ $m }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Year <span>*</span></label>
                                <select name="year" class="form-control" required>
                                    <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                                    <option value="{{ date('Y')-1 }}">{{ date('Y')-1 }}</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">No. of Occurrences <span>*</span></label>
                                <input type="text" name="occurrences" class="form-control" placeholder='e.g. "three (3)" or "5"' required>
                            </div>
                        </div>
                    </div>

                    <div class="form-card mb-0">
                        <div class="form-card-header"><div class="form-card-icon" style="background:#ecfdf5; color:#059669;"><i class="bi bi-shield-check"></i></div>Signatory</div>
                        <div class="form-card-body row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Signatory Name</label>
                                <input type="text" name="signatory_name" class="form-control" value="AIDA B. LOVERES" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Signatory Position</label>
                                <input type="text" name="signatory_position" class="form-control" value="PG Department Head/PHRM Officer" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0" style="border-bottom-left-radius:16px; border-bottom-right-radius:16px;">
                    <button type="button" class="btn btn-light fw-medium px-4" data-bs-dismiss="modal" style="border-radius:8px; border:1px solid #e5e7eb;">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-bold px-4" style="border-radius:8px;"><i class="bi bi-exclamation-triangle-fill me-1"></i> Generate Reprimand Letter</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- New Show-Cause Order modal --}}
<div class="modal fade" id="showCauseModal" tabindex="-1" aria-labelledby="showCauseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg lv-letter-form" style="border-radius:16px;"
            x-data="{ employees: window.lvEmployees, selectedId: '', employee: null }"
            x-init="
                $nextTick(() => {
                    lvWireEmployeeCombobox($refs.employeeSearch, $refs.employeeDatalist, $refs.employeeIdInput, employees, id => selectedId = id);
                });
                $watch('selectedId', id => employee = employees.find(e => e.id == id));
            ">
            <div class="modal-header" style="background: linear-gradient(135deg, #8b5cf6, #6d28d9); color:#fff; border-top-left-radius:16px; border-top-right-radius:16px; border-bottom:none;">
                <h5 class="modal-title fw-bold" id="showCauseModalLabel"><i class="bi bi-file-earmark-text me-2"></i>Issue Show-Cause Order</h5>
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('leave-violations.store-show-cause') }}">
                @csrf
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Due-process notice giving the employee an opportunity to explain before any penalty is recommended (2025 RACCS).</p>

                    <div class="form-card">
                        <div class="form-card-header"><div class="form-card-icon"><i class="bi bi-person"></i></div>Employee Information</div>
                        <div class="form-card-body">
                            <div style="margin-bottom: 16px;">
                                <label class="form-label">Employee Name <span>*</span></label>
                                <input type="text" x-ref="employeeSearch" list="showCauseEmployeeList" class="form-control" placeholder="Type or select employee name..." autocomplete="off" required>
                                <datalist x-ref="employeeDatalist" id="showCauseEmployeeList"></datalist>
                                <input type="hidden" name="plantilla_record_id" x-ref="employeeIdInput">
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Position</label>
                                    <input type="text" class="form-control" :value="employee ? employee.position_title : 'Auto-filled from employee'" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Office / Department</label>
                                    <input type="text" class="form-control" :value="employee ? employee.office_department : 'Auto-filled from employee'" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-card">
                        <div class="form-card-header"><div class="form-card-icon" style="background:#fef3c7; color:#d97706;"><i class="bi bi-file-text"></i></div>Grounds</div>
                        <div class="form-card-body">
                            <label class="form-label">Grounds for the Show-Cause Order <span>*</span></label>
                            <textarea name="grounds" class="form-control" rows="4" required placeholder="State the specific facts and matter the employee must explain."></textarea>
                            <div class="help-text">This becomes part of the letter's stated facts. The employee has five (5) working days to respond before any penalty is recommended.</div>
                        </div>
                    </div>

                    <div class="form-card mb-0">
                        <div class="form-card-header"><div class="form-card-icon" style="background:#ecfdf5; color:#059669;"><i class="bi bi-shield-check"></i></div>Signatory</div>
                        <div class="form-card-body row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Signatory Name</label>
                                <input type="text" name="signatory_name" class="form-control" value="AIDA B. LOVERES" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Signatory Position</label>
                                <input type="text" name="signatory_position" class="form-control" value="PG Department Head/PHRM Officer" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0" style="border-bottom-left-radius:16px; border-bottom-right-radius:16px;">
                    <button type="button" class="btn btn-light fw-medium px-4" data-bs-dismiss="modal" style="border-radius:8px; border:1px solid #e5e7eb;">Cancel</button>
                    <button type="submit" class="btn fw-bold px-4" style="border-radius:8px; background:#6d28d9; color:#fff;"><i class="bi bi-check-lg me-1"></i> Issue Show-Cause Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- New Payroll Coordination modal --}}
<div class="modal fade" id="payrollCoordinationModal" tabindex="-1" aria-labelledby="payrollCoordinationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg lv-letter-form" style="border-radius:16px;"
            x-data="{
                employees: window.lvEmployees,
                eligibleViolations: window.lvEligibleViolations,
                employeeIdsEligible: window.lvEmployeeIdsEligible,
                selectedId: '',
                selectedViolationId: '',
                employee: null,
                filteredViolations: [],
                selectedViolation: null
            }" x-init="
                $nextTick(() => {
                    lvWireEmployeeCombobox($refs.employeeSearch, $refs.employeeDatalist, $refs.employeeIdInput, employees, id => selectedId = id);
                });
                $watch('selectedId', id => {
                    employee = employees.find(e => e.id == id);
                    filteredViolations = eligibleViolations.filter(v => v.plantilla_record_id == id);
                    selectedViolationId = ''; selectedViolation = null;
                    lvPopulateViolationOptions($refs.violationSelect, filteredViolations);
                });
                $watch('selectedViolationId', id => { selectedViolation = filteredViolations.find(v => v.id == id) || null; });
            ">
            <div class="modal-header" style="background: linear-gradient(135deg, #14b8a6, #0f766e); color:#fff; border-top-left-radius:16px; border-top-right-radius:16px; border-bottom:none;">
                <h5 class="modal-title fw-bold" id="payrollCoordinationModalLabel"><i class="bi bi-cash-stack me-2"></i>Generate Payroll Coordination Letter</h5>
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('leave-violations.store-payroll-coordination') }}">
                @csrf
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Request that Payroll drop an employee from the rolls or apply a salary deduction, based on an already-recorded AWOL / LWOP / Habitual Absenteeism entry.</p>

                    <div class="form-card">
                        <div class="form-card-header"><div class="form-card-icon" style="background:#ccfbf1; color:#0f766e;"><i class="bi bi-person-badge"></i></div>Employee Information</div>
                        <div class="form-card-body">
                            <label class="form-label">Employee Name <span>*</span></label>
                            <input type="text" x-ref="employeeSearch" list="payrollEmployeeList" class="form-control" placeholder="Type or select employee name..." autocomplete="off" required>
                            <datalist x-ref="employeeDatalist" id="payrollEmployeeList"></datalist>
                            <input type="hidden" name="plantilla_record_id" x-ref="employeeIdInput">
                        </div>
                    </div>

                    <div class="form-card">
                        <div class="form-card-header"><div class="form-card-icon" style="background:#fef3c7; color:#d97706;"><i class="bi bi-exclamation-triangle"></i></div>Violation Reference</div>
                        <div class="form-card-body">
                            <label class="form-label">Select the Violation <span>*</span></label>
                            <select name="violation_id" x-model="selectedViolationId" x-ref="violationSelect" class="form-control" required>
                                <option value="">Select the recorded violation to coordinate...</option>
                            </select>
                            <div class="help-text">Only AWOL, LWOP, and Habitual Absenteeism entries not yet coordinated with payroll are shown, for the selected employee only.</div>
                            <div class="violation-info" :class="{ 'visible': selectedViolation }">
                                <i class="bi bi-info-circle-fill"></i>
                                <span x-text="selectedViolation ? selectedViolation.type_label + ' recorded on ' + selectedViolation.created_at + ' will be referenced in the coordination letter.' : ''"></span>
                            </div>
                        </div>
                    </div>

                    <div class="form-card mb-0">
                        <div class="form-card-header"><div class="form-card-icon" style="background:#e0e7ff; color:#4338ca;"><i class="bi bi-cash-stack"></i></div>Payroll Action</div>
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
                </div>
                <div class="modal-footer bg-light border-top-0" style="border-bottom-left-radius:16px; border-bottom-right-radius:16px;">
                    <button type="button" class="btn btn-light fw-medium px-4" data-bs-dismiss="modal" style="border-radius:8px; border:1px solid #e5e7eb;">Cancel</button>
                    <button type="submit" class="btn fw-bold px-4" style="border-radius:8px; background:#0f766e; color:#fff;"><i class="bi bi-send-fill me-1"></i> Generate Payroll Coordination Letter</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Letter modal — content fetched per-row via AJAX (see JS below) since
     the form varies by violation type/status and a static copy per row would
     duplicate the full employee list on every row of a 5,000+ roster. --}}
<div class="modal fade" id="editLetterModal" tabindex="-1" aria-labelledby="editLetterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg lv-letter-form" style="border-radius:16px;" id="editLetterModalContent">
            <div class="modal-body p-5 text-center text-muted">
                <div class="spinner-border text-primary mb-2" role="status"></div>
                <div>Loading letter details...</div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tardyUndertimeModal = document.getElementById('tardyUndertimeModal');
        if (tardyUndertimeModal) {
            tardyUndertimeModal.addEventListener('show.bs.modal', function (event) {
                const btn = event.relatedTarget;
                const type = btn.getAttribute('data-letter-type') || 'HABITUAL_TARDINESS';
                // Alpine component instance lives on the modal-content element.
                Alpine.$data(tardyUndertimeModal.querySelector('.modal-content')).letterType = type;
            });
        }

        const editLetterModal = document.getElementById('editLetterModal');
        const editLetterModalContent = document.getElementById('editLetterModalContent');
        const editLetterLoadingHtml = editLetterModalContent.innerHTML;
        if (editLetterModal) {
            editLetterModal.addEventListener('show.bs.modal', function (event) {
                const btn = event.relatedTarget;
                const url = btn.getAttribute('data-edit-url');
                editLetterModalContent.innerHTML = editLetterLoadingHtml;
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.text())
                    .then(html => {
                        editLetterModalContent.innerHTML = html;
                        // Re-run any inline <script> tags in the fetched fragment — innerHTML doesn't execute them.
                        editLetterModalContent.querySelectorAll('script').forEach(old => {
                            const s = document.createElement('script');
                            s.textContent = old.textContent;
                            old.replaceWith(s);
                        });
                    })
                    .catch(() => {
                        editLetterModalContent.innerHTML = '<div class="modal-body p-4 text-danger">Failed to load letter details. Please close this dialog and try again.</div>';
                    });
            });
            editLetterModal.addEventListener('hidden.bs.modal', function () {
                editLetterModalContent.innerHTML = editLetterLoadingHtml;
            });
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</x-dashboard-app>
