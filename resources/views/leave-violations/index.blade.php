<x-dashboard-app>
<div style="background: linear-gradient(135deg,#1e3a5f 0%,#1a5276 55%,#0f4c75 100%); border-radius:14px; padding:24px 28px; margin-bottom:20px; color:#fff;">
    <h1 style="font-size:20px;font-weight:800;margin:0 0 4px;"><i class="bi bi-flag-fill"></i> Leave Violation Watchlist</h1>
    <p style="font-size:12.5px;opacity:.8;margin:0;">LWOP, AWOL &amp; Habitual Absenteeism detected from leave records (Omnibus Rules on Leave; 2025 RACCS Rule 10 §63). JO/COS personnel are excluded.</p>
</div>

@if(session('success'))
    <div style="background:#f0fdf4;color:#166534;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:13px;">{{ session('success') }}</div>
@endif

<div x-data="{ 
    showModal: false,
    employees: {{ Js::from($employees) }},
    selectedEmployeeId: '',
    selectedEmployee: null
}" x-init="$watch('selectedEmployeeId', id => selectedEmployee = employees.find(e => e.id == id))" style="margin-bottom: 14px;">
    <button @click="showModal = true" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Log Manual LWOP/Tardiness</button>
    
    <div x-show="showModal" style="position:fixed;inset:0;z-index:100;display:none;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;overflow-y:auto;padding:20px;">
            <div style="background:#fff;padding:24px;border-radius:8px;width:550px;max-width:100%;" @click.away="showModal = false">
                <h4 style="font-size:16px;font-weight:700;margin-bottom:16px;">Log Leave Without Pay / Tardiness</h4>
                <form method="POST" action="{{ route('leave-violations.manual-tardiness') }}">
                    @csrf
                    
                    <div style="margin-bottom:12px;">
                        <label style="font-size:12px;font-weight:600;">Employee Name</label>
                        <select name="plantilla_record_id" x-model="selectedEmployeeId" required style="width:100%;padding:6px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;">
                            <option value="">Select Employee...</option>
                            <template x-for="emp in employees" :key="emp.id">
                                <option :value="emp.id" x-text="emp.last_name + ', ' + emp.first_name"></option>
                            </template>
                        </select>
                    </div>

                    <div style="display:flex; gap:12px; margin-bottom:16px;">
                        <div style="flex:1;">
                            <label style="font-size:12px;font-weight:600;">Office/Department</label>
                            <input type="text" readonly :value="selectedEmployee ? selectedEmployee.office_department : ''" style="width:100%;padding:6px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;background:#f3f4f6;color:#6b7280;">
                        </div>
                        <div style="flex:1;">
                            <label style="font-size:12px;font-weight:600;">Position Title</label>
                            <input type="text" readonly :value="selectedEmployee ? selectedEmployee.position_title : ''" style="width:100%;padding:6px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;background:#f3f4f6;color:#6b7280;">
                        </div>
                    </div>

                    <div style="display:flex; gap:12px; margin-bottom:12px;">
                        <div style="flex:1;">
                            <label style="font-size:12px;font-weight:600;">Earned VL (as of given date)</label>
                            <input type="number" step="0.001" name="earned_vl" required style="width:100%;padding:6px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;">
                        </div>
                        <div style="flex:1;">
                            <label style="font-size:12px;font-weight:600;">Earned SL (as of given date)</label>
                            <input type="number" step="0.001" name="earned_sl" required style="width:100%;padding:6px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;">
                        </div>
                    </div>

                    <div style="margin-bottom:12px;">
                        <label style="font-size:12px;font-weight:600;">No. of days without pay</label>
                        <input type="number" step="0.001" name="lwop_days" required style="width:100%;padding:6px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;">
                    </div>

                    <div style="margin-bottom:12px;">
                        <label style="font-size:12px;font-weight:600;">Inclusive Date(s) (e.g. Jan 1-5, Feb 2)</label>
                        <input type="text" name="inclusive_dates" required style="width:100%;padding:6px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;">
                    </div>

                    <div style="margin-bottom:20px;">
                        <label style="font-size:12px;font-weight:600;">No. of days of undertime/tardy without pay</label>
                        <input type="number" step="0.001" name="undertime_tardy_lwop_days" required style="width:100%;padding:6px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;">
                    </div>

                    <div style="display:flex;gap:8px;justify-content:flex-end;border-top:1px solid #e5e7eb;padding-top:16px;">
                        <button type="button" @click="showModal = false" class="btn btn-sm btn-outline-secondary">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">Submit Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;font-size:12.5px;">
        <thead><tr style="text-align:left;color:#9ca3af;font-size:10.5px;text-transform:uppercase;">
            <th style="padding:8px;">Employee</th><th>Violation</th><th>Offense Tier</th><th>Period</th><th>Status</th><th>Actions</th>
        </tr></thead>
        <tbody>
            @forelse($violations as $v)
                <tr style="border-top:1px solid #f3f4f6;">
                    <td style="padding:8px;">{{ $v->plantillaRecord->last_name }}, {{ $v->plantillaRecord->first_name }}</td>
                    <td>{{ str_replace('_', ' ', $v->violation_type) }}</td>
                    <td>{{ $v->offense_tier }}</td>
                    <td>{{ $v->rating_period }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $v->status)) }}</td>
                    <td>
                        <div style="display:flex; gap:6px;">
                            @if($v->status === 'detected')
                                <form method="POST" action="{{ route('leave-violations.issue-notice', $v) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-warning">Issue Notice</button>
                                </form>
                                @if(in_array($v->violation_type, ['AWOL', 'LWOP', 'HABITUAL_ABSENTEEISM', 'HABITUAL_TARDINESS']))
                                    <form method="POST" action="{{ route('leave-violations.issue-payroll-coordination', $v) }}">
                                        @csrf
                                        <input type="hidden" name="action_type" value="{{ $v->violation_type === 'AWOL' ? 'DROP' : 'DEDUCT' }}">
                                        <button class="btn btn-sm btn-outline-danger">Payroll: {{ $v->violation_type === 'AWOL' ? 'Drop' : 'Deduct' }}</button>
                                    </form>
                                @endif
                            @elseif(in_array($v->status, ['notice_pending','issued','escalated']))
                                <form method="POST" action="{{ route('leave-violations.resolve', $v) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success">Mark Resolved</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;color:#9ca3af;padding:12px;">No violations detected.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $violations->links() }}
</div>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</x-dashboard-app>
