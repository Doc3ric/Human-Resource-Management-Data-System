@extends('layouts.app')

@section('content')
<style>
    .premium-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 24px; }
    .form-label { font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; margin-bottom: 6px; display: block; }
    .form-control, .form-select { border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 12px; font-size: 13px; color: #1f2937; width: 100%; box-sizing: border-box; }
    .form-control:focus, .form-select:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
    .readonly-field { background: #f9fafb; cursor: not-allowed; }
    .btn-primary { background: #2563eb; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; }
    .btn-primary:hover { background: #1d4ed8; }
</style>

<div class="mb-4">
    <h2 style="font-size: 24px; font-weight: 700; color: #111827; margin: 0;">Record Detail Order</h2>
    <p style="font-size: 14px; color: #6b7280; margin: 4px 0 0;">Tracks the CSC 1-year detail limit and auto-drafts a recall letter once it lapses.</p>
</div>

@if ($errors->any())
    <div class="premium-card" style="border-color:#fecaca; background:#fef2f2; margin-bottom:16px;">
        <ul style="margin:0; padding-left:18px; color:#991b1b; font-size:13px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('detail-orders.store') }}" method="POST">
    @csrf
    <div class="premium-card">
        <div style="display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 16px;">
            <div>
                <label class="form-label">Employee <span style="color:#ef4444;">*</span></label>
                <select name="plantilla_record_id" id="employeeSelect" class="form-select" required>
                    <option value="">Type to search employee...</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" data-office="{{ $emp->office_department }}">
                            {{ $emp->last_name }}, {{ $emp->first_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Home Unit (Permanent Station)</label>
                <input type="text" name="home_unit" id="homeUnitInput" class="form-control" placeholder="Defaults to employee's office">
            </div>
            <div>
                <label class="form-label">Detailed Unit <span style="color:#ef4444;">*</span></label>
                <input type="text" name="detailed_unit" class="form-control" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-top: 20px;">
            <div>
                <label class="form-label">Detail Order No. <span style="color:#ef4444;">*</span></label>
                <input type="text" name="detail_order_no" class="form-control" required>
            </div>
            <div>
                <label class="form-label">Date Issued <span style="color:#ef4444;">*</span></label>
                <input type="date" name="date_issued" class="form-control" required>
            </div>
            <div>
                <label class="form-label">Effective Start <span style="color:#ef4444;">*</span></label>
                <input type="date" name="date_effective_start" class="form-control" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr; gap: 16px; margin-top: 20px;">
            <div>
                <label class="form-label">Effective End (optional — defaults to Start + 1 year)</label>
                <input type="date" name="date_effective_end" class="form-control">
            </div>
            <div>
                <label class="form-label">Remarks</label>
                <textarea name="remarks" class="form-control" rows="2"></textarea>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; margin-top: 32px;">
            <button type="submit" class="btn-primary">Save Detail Order</button>
        </div>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const select = document.getElementById('employeeSelect');
        const homeUnitInput = document.getElementById('homeUnitInput');

        select.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            homeUnitInput.value = (opt && opt.value) ? (opt.getAttribute('data-office') || '') : '';
        });
    });
</script>
@endsection
