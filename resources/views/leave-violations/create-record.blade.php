@extends('layouts.app')

@section('content')
<style>
    .premium-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 24px; }
    .form-label { font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; margin-bottom: 6px; display: block; }
    .form-control, .form-select { border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 12px; font-size: 13px; color: #1f2937; width: 100%; box-sizing: border-box; }
    .form-control:focus, .form-select:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
    .readonly-field { background: #f9fafb; cursor: not-allowed; }
    
    .section-title { font-size: 12px; font-weight: 700; color: #1f2937; text-transform: uppercase; margin-bottom: 16px; }
    .grid-container { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-top: 24px; }
    .grid-card { border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; }
    
    .btn-primary { background: #2563eb; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 8px; }
    .btn-primary:hover { background: #1d4ed8; }
</style>

<div class="mb-4">
    <h2 style="font-size: 24px; font-weight: 700; color: #111827; margin: 0;">Leave Records</h2>
    <p style="font-size: 14px; color: #6b7280; margin: 4px 0 0;">Record employee leave of absence / undertime / tardy without pay.</p>
</div>

<form action="{{ route('leave-violations.recorded-entries.store') }}" method="POST">
    @csrf
    <div class="premium-card">
        
        <!-- Employee Information -->
        <div>
            <div class="form-label" style="font-size: 10px;">EMPLOYEE INFORMATION</div>
            <div style="display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 16px;">
                <div>
                    <label class="form-label">EMPLOYEE NAME <span style="color:#ef4444;">*</span></label>
                    <select name="plantilla_record_id" id="employeeSelect" class="form-select" required>
                        <option value="">Type to search employee...</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" data-position="{{ $emp->position_title }}" data-office="{{ $emp->office_department }}">
                                {{ $emp->last_name }}, {{ $emp->first_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">POSITION</label>
                    <input type="text" id="positionInput" class="form-control readonly-field" readonly>
                </div>
                <div>
                    <label class="form-label">OFFICE</label>
                    <input type="text" id="officeInput" class="form-control readonly-field" readonly>
                </div>
            </div>
        </div>
        
        <div class="grid-container">
            <!-- Earned Leave Credits Balance -->
            <div class="grid-card">
                <div class="section-title">EARNED LEAVE CREDITS BALANCE</div>
                <div style="margin-bottom: 16px;">
                    <label class="form-label">As of Date <span style="color:#ef4444;">*</span></label>
                    <input type="date" name="earned_as_of_date" class="form-control" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div>
                        <label class="form-label">VL</label>
                        <input type="number" step="0.001" name="earned_vl" class="form-control" placeholder="0.00">
                    </div>
                    <div>
                        <label class="form-label">SL</label>
                        <input type="number" step="0.001" name="earned_sl" class="form-control" placeholder="0.00">
                    </div>
                </div>
            </div>
            
            <!-- No. of Days w/out pay -->
            <div class="grid-card">
                <div class="section-title">NO. OF DAYS W/OUT PAY</div>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                    <div>
                        <label class="form-label">VL</label>
                        <input type="number" step="0.5" name="days_without_pay_vl" id="dwpVl" class="form-control" placeholder="0" oninput="calcTotalDwp()">
                    </div>
                    <div>
                        <label class="form-label">SL</label>
                        <input type="number" step="0.5" name="days_without_pay_sl" id="dwpSl" class="form-control" placeholder="0" oninput="calcTotalDwp()">
                    </div>
                    <div>
                        <label class="form-label">Total</label>
                        <input type="number" step="0.5" name="days_without_pay_total" id="dwpTotal" class="form-control readonly-field" style="color: #2563eb; font-weight: bold; background: #eff6ff;" placeholder="0" readonly>
                    </div>
                </div>
                <div style="margin-bottom: 16px;">
                    <label class="form-label">Inclusive Dates</label>
                    <input type="text" name="days_without_pay_inclusive_dates" class="form-control" placeholder="Select dates...">
                </div>
                <div>
                    <label class="form-label">Remarks (Optional)</label>
                    <textarea name="days_without_pay_remarks" class="form-control" rows="2" placeholder="Add any notes here..."></textarea>
                </div>
            </div>
            
            <!-- No. of days of undertime/tardy w/out pay -->
            <div class="grid-card">
                <div class="section-title">NO. OF DAYS OF UNDERTIME/TARDY W/OUT PAY</div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label class="form-label">Hours</label>
                        <input type="number" name="undertime_tardy_hours" class="form-control" placeholder="0">
                    </div>
                    <div>
                        <label class="form-label">Minutes</label>
                        <input type="number" name="undertime_tardy_mins" class="form-control" placeholder="0">
                    </div>
                </div>
                <div>
                    <label class="form-label">Inclusive Dates</label>
                    <input type="text" name="undertime_tardy_inclusive_dates" class="form-control" placeholder="Select dates...">
                </div>
            </div>
        </div>
        
        <div style="display: flex; justify-content: flex-end; margin-top: 32px;">
            <button type="submit" class="btn-primary">
                <i class="bi bi-plus-lg"></i> Save Record
            </button>
        </div>
    </div>
</form>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const select = document.getElementById('employeeSelect');
        const posInput = document.getElementById('positionInput');
        const offInput = document.getElementById('officeInput');
        
        select.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if (opt && opt.value) {
                posInput.value = opt.getAttribute('data-position') || '';
                offInput.value = opt.getAttribute('data-office') || '';
            } else {
                posInput.value = '';
                offInput.value = '';
            }
        });

        function formatMultipleDates(dates) {
            if (!dates || dates.length === 0) return "";
            dates.sort((a, b) => a - b);
            
            const groups = new Map();
            dates.forEach(d => {
                const y = d.getFullYear();
                const m = d.toLocaleString('default', { month: 'long' });
                const day = d.getDate();
                
                if (!groups.has(y)) groups.set(y, new Map());
                if (!groups.get(y).has(m)) groups.get(y).set(m, []);
                groups.get(y).get(m).push(day);
            });
            
            const formatDays = (days) => {
                days = [...new Set(days)].sort((a, b) => a - b);
                let ranges = [];
                let start = days[0];
                let prev = days[0];
                for (let i = 1; i < days.length; i++) {
                    if (days[i] === prev + 1) {
                        prev = days[i];
                    } else {
                        ranges.push(start === prev ? start : start + "-" + prev);
                        start = days[i];
                        prev = days[i];
                    }
                }
                ranges.push(start === prev ? start : start + "-" + prev);
                return ranges.join(", ");
            };
            
            const yearStrings = [];
            for (const [y, monthMap] of groups.entries()) {
                const monthStrings = [];
                for (const [m, days] of monthMap.entries()) {
                    monthStrings.push(m + " " + formatDays(days));
                }
                yearStrings.push(monthStrings.join("; ") + ", " + y);
            }
            
            return yearStrings.join(" & ");
        }

        const pickerOptions = {
            mode: "multiple",
            onChange: function(selectedDates, dateStr, instance) {
                setTimeout(() => { instance.input.value = formatMultipleDates(selectedDates); }, 0);
            },
            onClose: function(selectedDates, dateStr, instance) {
                setTimeout(() => { instance.input.value = formatMultipleDates(selectedDates); }, 0);
            }
        };

        flatpickr("input[name='days_without_pay_inclusive_dates']", pickerOptions);
        flatpickr("input[name='undertime_tardy_inclusive_dates']", pickerOptions);
    });

    function calcTotalDwp() {
        const vl = parseFloat(document.getElementById('dwpVl').value) || 0;
        const sl = parseFloat(document.getElementById('dwpSl').value) || 0;
        document.getElementById('dwpTotal').value = vl + sl;
    }
</script>
@endsection
