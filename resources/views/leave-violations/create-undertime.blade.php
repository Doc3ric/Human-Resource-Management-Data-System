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

<div>
    <h2 class="page-title">Generate Undertime Letter</h2>
    <p class="page-subtitle">Generate a formal notice letter for employee undertime records.</p>

    <div x-data="{
        employees: {{ Js::from($employees) }},
        selectedId: '',
        employee: null
    }" x-init="$watch('selectedId', id => employee = employees.find(e => e.id == id))">
    
        <form method="POST" action="{{ route('leave-violations.store-letter') }}">
            @csrf
            <input type="hidden" name="letter_type" value="UNDERTIME">
            
            <!-- Employee Info Card -->
            <div class="form-card">
                <div class="form-card-header">
                    <div class="form-card-icon"><i class="bi bi-person"></i></div>
                    Employee Information
                </div>
                <div class="form-card-body">
                    <div style="margin-bottom: 20px;">
                        <label class="form-label">Employee Name <span>*</span></label>
                        <select name="plantilla_record_id" x-model="selectedId" class="form-control" required>
                            <option value="">Type to search employee...</option>
                            <template x-for="emp in employees" :key="emp.id">
                                <option :value="emp.id" x-text="emp.last_name + ', ' + emp.first_name"></option>
                            </template>
                        </select>
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
                            <option value="January">January</option><option value="February">February</option><option value="March">March</option>
                            <option value="April">April</option><option value="May">May</option><option value="June">June</option>
                            <option value="July">July</option><option value="August">August</option><option value="September">September</option>
                            <option value="October">October</option><option value="November">November</option><option value="December">December</option>
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
                        <label class="form-label">No. of Undertime Occurrences <span>*</span></label>
                        <input type="text" name="occurrences" class="form-control" placeholder="e.g. &quot;three (3)&quot; or &quot;5&quot;" required>
                        <div class="help-text">Appears as: "You have incurred ___ times of undertime."</div>
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
                <span>✦ Letter will be saved to <b>Generated Letters</b> — then you can print from there.</span>
                <button type="submit" class="btn-submit"><i class="bi bi-check-lg"></i> Save to Generated Letters</button>
            </div>
        </form>
    </div>
</div>
</x-dashboard-app>
