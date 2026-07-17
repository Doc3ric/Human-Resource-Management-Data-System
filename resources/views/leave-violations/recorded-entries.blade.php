<x-dashboard-app>
<style>
    .premium-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    .table-header-group { border-bottom: 2px solid #e5e7eb; background: #f9fafb; }
    .nested-th { font-size: 10px; color: #6b7280; text-transform: uppercase; font-weight: 600; text-align: center; border-bottom: 1px solid #e5e7eb; border-left: 1px solid #e5e7eb; padding: 8px; }
    .nested-th-top { font-size: 10px; color: #6b7280; text-transform: uppercase; font-weight: 600; text-align: center; border-bottom: 1px solid #e5e7eb; border-left: 1px solid #e5e7eb; padding: 12px 8px; }
    .nested-th-leftmost { border-left: none; }
    .nested-td { border-left: 1px solid #e5e7eb; text-align: center; padding: 12px 8px; font-size: 12px; color: #4b5563; }
    .nested-td-leftmost { border-left: none; text-align: left; }
    .avatar-circle { width: 32px; height: 32px; border-radius: 50%; background: #2563eb; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; margin: 0 auto; }
    .table-btn { font-size: 11px; padding: 4px 8px; border-radius: 4px; border: 1px solid transparent; background: transparent; display: inline-flex; align-items: center; gap: 4px; cursor: pointer; }
    .btn-edit { color: #2563eb; } .btn-edit:hover { background: #eff6ff; border-color: #bfdbfe; }
    .btn-pdf { color: #ef4444; } .btn-pdf:hover { background: #fef2f2; border-color: #fecaca; }
    .btn-delete { color: #4b5563; } .btn-delete:hover { background: #f3f4f6; border-color: #e5e7eb; }

    /* Scoped to the Add Leave Record modal only — mirrors the old create-record page's compact look */
    #addRecordModal .form-label { font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; margin-bottom: 6px; display: block; }
    #addRecordModal .form-control, #addRecordModal .form-select { border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 12px; font-size: 13px; color: #1f2937; }
    #addRecordModal .form-control:focus, #addRecordModal .form-select:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
    #addRecordModal .readonly-field { background: #f9fafb; cursor: not-allowed; }
    #addRecordModal .section-title { font-size: 12px; font-weight: 700; color: #1f2937; text-transform: uppercase; margin-bottom: 16px; }
    #addRecordModal .grid-container { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 24px; }
    #addRecordModal .grid-card { border: 1px solid #e5e7eb; border-radius: 12px; padding: 18px; }
    @media (max-width: 991px) { #addRecordModal .grid-container { grid-template-columns: 1fr; } }
</style>

<div class="mb-4">
    <h2 style="font-size: 24px; font-weight: 700; color: #111827; margin: 0;">Recorded Entries</h2>
    <p style="font-size: 14px; color: #6b7280; margin: 4px 0 0;">Manage leave without pay (LWOP) and tardiness records.</p>
</div>

<style>
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
</style>

<div class="scoreboard-container">
    <div class="kpi-card theme-primary">
        <div class="kpi-header">
            <span class="kpi-pill">2017 RACCS</span>
            <div class="kpi-icon-box"><i class="bi bi-calendar-x"></i></div>
        </div>
        <div class="kpi-label">Total Absences Logged</div>
        <div class="kpi-value">{{ number_format($metrics['total']) }}</div>
        <div class="kpi-sub">100%</div>
        <div class="kpi-divider"></div>
        <div class="kpi-footer">
            <i class="bi bi-info-circle"></i>
            <span>All LWOP and tardiness must be accurately logged to determine proper deductions.</span>
        </div>
    </div>
    
    <div class="kpi-card theme-warning">
        <div class="kpi-header">
            <span class="kpi-pill">DATA PRIVACY</span>
            <div class="kpi-icon-box"><i class="bi bi-people"></i></div>
        </div>
        <div class="kpi-label">Employees w/ Absences</div>
        <div class="kpi-value">{{ number_format($metrics['employees']) }}</div>
        <div class="kpi-sub">{{ $metrics['employees_pct'] }}% of Workforce</div>
        <div class="kpi-divider"></div>
        <div class="kpi-footer">
            <i class="bi bi-info-circle"></i>
            <span>Access to specific leave violation records is restricted per RA 10173.</span>
        </div>
    </div>

    <div class="kpi-card theme-danger">
        <div class="kpi-header">
            <span class="kpi-pill">MONITORING</span>
            <div class="kpi-icon-box"><i class="bi bi-calendar2-month"></i></div>
        </div>
        <div class="kpi-label">Logged This Month</div>
        <div class="kpi-value">{{ number_format($metrics['this_month']) }}</div>
        <div class="kpi-sub">{{ $metrics['this_month_pct'] }}% of Total</div>
        <div class="kpi-divider"></div>
        <div class="kpi-footer">
            <i class="bi bi-info-circle"></i>
            <span>Frequent absences may warrant a written reprimand per CSC rules on Habitual Absenteeism.</span>
        </div>
    </div>
</div>

<div class="premium-card">
    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 24px; border-bottom: 1px solid #e5e7eb; gap: 16px;">
        <div style="display: flex; gap: 12px; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">All Leave Records</h3>
            <div style="font-size: 12px; color: #9ca3af; padding: 6px 12px; background: #f3f4f6; border-radius: 16px;">{{ $entries->total() }} entries</div>
        </div>
        <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap; justify-content: center;">
            <div style="position:relative;">
                <input type="text" placeholder="Search employee" style="padding:10px 14px 10px 36px; border-radius:8px; border:1px solid #d1d5db; font-size:13px; width:260px;">
                <i class="bi bi-search" style="position:absolute; left:14px; top:12px; color:#9ca3af; font-size:13px;"></i>
            </div>
            <button class="btn btn-outline-secondary btn-sm" style="background:#fff; padding: 8px 16px;"><i class="bi bi-funnel"></i> Filter</button>
            <button type="button" class="btn btn-sm" style="color: #fff; background: #2563eb; border: 1px solid #2563eb; padding: 8px 16px;" data-bs-toggle="modal" data-bs-target="#addRecordModal"><i class="bi bi-plus-lg"></i> Entry Record</button>
            <button type="button" class="btn btn-sm" style="color: #d97706; background: #fef3c7; border: 1px solid #fde68a; padding: 8px 16px;" data-bs-toggle="modal" data-bs-target="#generateModal"><i class="bi bi-file-earmark-check"></i> Generate Certificate</button>
        </div>
    </div>
    
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead class="table-header-group">
                <tr>
                    <th rowspan="2" class="nested-th-top nested-th-leftmost" style="text-align: left; padding-left: 24px;"><input type="checkbox" id="selectAll"> NAME</th>
                    <th rowspan="2" class="nested-th-top nested-th-leftmost" style="text-align: left;">POSITION</th>
                    <th rowspan="2" class="nested-th-top nested-th-leftmost" style="text-align: left;">OFFICE</th>
                    <th colspan="2" class="nested-th-top">EARNED LEAVE CREDITS BALANCE</th>
                    <th colspan="4" class="nested-th-top">NO. OF DAYS W/OUT PAY</th>
                    <th colspan="3" class="nested-th-top">NO. OF DAYS OF UNDERTIME/TARDY W/OUT PAY</th>
                    <th rowspan="2" class="nested-th-top">CREATED BY</th>
                    <th rowspan="2" class="nested-th-top">ACTIONS</th>
                </tr>
                <tr>
                    <th class="nested-th">VL</th>
                    <th class="nested-th">SL</th>
                    <th class="nested-th">VL</th>
                    <th class="nested-th">SL</th>
                    <th class="nested-th">TOTAL</th>
                    <th class="nested-th">INCLUSIVE DATES</th>
                    <th class="nested-th">HRS</th>
                    <th class="nested-th">MINS</th>
                    <th class="nested-th">INCLUSIVE DATES</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entries as $entry)
                <tr style="border-bottom: 1px solid #e5e7eb;">
                    <td class="nested-td nested-td-leftmost" style="padding-left: 24px;">
                        <div style="display:flex; align-items:flex-start; gap:8px;">
                            <input type="checkbox" class="record-checkbox" value="{{ $entry->id }}" style="margin-top:4px;">
                            <div>
                                <div style="font-weight: 600; color: #1f2937;">{{ $entry->plantillaRecord->last_name }}, {{ $entry->plantillaRecord->first_name }}</div>
                                <div style="font-size: 10px; color: #9ca3af;">As of {{ date('M d, Y', strtotime($entry->created_at)) }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="nested-td nested-td-leftmost">{{ $entry->plantillaRecord->position_title }}</td>
                    <td class="nested-td nested-td-leftmost">{{ $entry->plantillaRecord->office_department }}</td>
                    
                    <td class="nested-td">{{ number_format($entry->details['earned_vl'] ?? 0, 3) }}</td>
                    <td class="nested-td">{{ number_format($entry->details['earned_sl'] ?? 0, 3) }}</td>
                    
                    <td class="nested-td">—</td>
                    <td class="nested-td">—</td>
                    <td class="nested-td"><b>{{ $entry->details['days_without_pay'] ?? 0 }}</b></td>
                    <td class="nested-td" style="font-size: 11px; max-width: 200px; white-space: normal;">{{ $entry->details['inclusive_dates'] ?? '—' }}</td>
                    
                    <td class="nested-td">—</td>
                    <td class="nested-td">—</td>
                    <td class="nested-td">—</td>
                    
                    <td class="nested-td">
                        @php
                            $creatorName = $entry->details['creator_name'] ?? ($entry->issuedBy->first_name ?? ($entry->issuedBy->name ?? 'System'));
                            $creatorInitial = substr($creatorName, 0, 1);
                        @endphp
                        <div style="display:flex; align-items:center; gap:8px;">
                            <div class="avatar-circle" style="background:#f3f4f6; color:#4b5563;">{{ strtoupper($creatorInitial) }}</div>
                            <div style="font-size:10px; color:#9ca3af; line-height:1.2;">
                                <div style="font-weight:bold; color:#4b5563;">{{ $creatorName }}</div>
                                <div>{{ $entry->details['reference_no'] ?? 'LW-'.$entry->id }}</div>
                            </div>
                        </div>
                    </td>
                    
                    <td class="nested-td">
                        <div style="display:flex; gap:4px; justify-content:center;">
                            <button type="button" class="table-btn btn-edit" data-bs-toggle="modal" data-bs-target="#editRecordModal{{ $entry->id }}"><i class="bi bi-pencil-square"></i> Edit</button>
                            <a href="{{ route('leave-violations.download-pdf', $entry->id) }}" target="_blank" class="table-btn btn-pdf" style="text-decoration:none;"><i class="bi bi-file-pdf"></i> PDF</a>
                            <form action="{{ route('leave-violations.recorded-entries.destroy', $entry->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this leave record? It will be removed from the list and can be recovered by an administrator.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="table-btn btn-delete"><i class="bi bi-trash"></i> Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach

                @if($entries->isEmpty())
                <tr><td colspan="14" style="padding: 30px; text-align: center; color: #9ca3af;">No recorded entries found.</td></tr>
                @endif
            </tbody>
        </table>
    </div>
    <div style="padding: 16px 24px;">
        {{ $entries->links() }}
    </div>
</div>

{{-- Edit Leave Record modals (one per row, rendered outside the table so Bootstrap can portal-position them) --}}
@foreach($entries as $entry)
                <div class="modal fade" id="editRecordModal{{ $entry->id }}" tabindex="-1" aria-labelledby="editRecordModalLabel{{ $entry->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;">
                            <div class="modal-header" style="background: linear-gradient(135deg,#1e3a5f 0%,#1a5276 55%,#0f4c75 100%); color:#fff; border-top-left-radius:16px; border-top-right-radius:16px; border-bottom:none;">
                                <h5 class="modal-title fw-bold" id="editRecordModalLabel{{ $entry->id }}"><i class="bi bi-pencil-square me-2"></i>Edit Leave / Undertime / Tardy Record</h5>
                                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('leave-violations.recorded-entries.update', $entry->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-body p-4">
                                    <input type="hidden" name="plantilla_record_id" value="{{ $entry->plantilla_record_id }}">
                                    <div class="form-label" style="font-size: 10px;">EMPLOYEE INFORMATION</div>
                                    <div style="display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 16px;">
                                        <div>
                                            <label class="form-label" style="font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; margin-bottom: 6px; display: block;">EMPLOYEE NAME</label>
                                            <input type="text" class="form-control" style="border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 12px; font-size: 13px; background:#f9fafb; cursor:not-allowed;" value="{{ $entry->plantillaRecord->last_name }}, {{ $entry->plantillaRecord->first_name }}" readonly>
                                        </div>
                                        <div>
                                            <label class="form-label" style="font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; margin-bottom: 6px; display: block;">POSITION</label>
                                            <input type="text" class="form-control" style="border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 12px; font-size: 13px; background:#f9fafb; cursor:not-allowed;" value="{{ $entry->plantillaRecord->position_title }}" readonly>
                                        </div>
                                        <div>
                                            <label class="form-label" style="font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; margin-bottom: 6px; display: block;">OFFICE</label>
                                            <input type="text" class="form-control" style="border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 12px; font-size: 13px; background:#f9fafb; cursor:not-allowed;" value="{{ $entry->plantillaRecord->office_department }}" readonly>
                                        </div>
                                    </div>

                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 16px;">
                                        <div>
                                            <label class="form-label" style="font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; margin-bottom: 6px; display: block;">REFERENCE NO.</label>
                                            <input type="text" name="reference_no" class="form-control" style="border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 12px; font-size: 13px;" value="{{ $entry->details['reference_no'] ?? ('LW-'.$entry->id) }}">
                                        </div>
                                        <div>
                                            <label class="form-label" style="font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; margin-bottom: 6px; display: block;">CREATED BY (CREATOR)</label>
                                            <input type="text" name="creator_name" class="form-control" style="border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 12px; font-size: 13px;" value="{{ $entry->details['creator_name'] ?? ($entry->issuedBy->first_name ?? ($entry->issuedBy->name ?? 'System')) }}">
                                        </div>
                                    </div>

                                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 24px;">
                                        <div style="border: 1px solid #e5e7eb; border-radius: 12px; padding: 18px;">
                                            <div style="font-size: 12px; font-weight: 700; color: #1f2937; text-transform: uppercase; margin-bottom: 16px;">EARNED LEAVE CREDITS BALANCE</div>
                                            <div style="margin-bottom: 16px;">
                                                <label class="form-label" style="font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; margin-bottom: 6px; display: block;">As of Date <span style="color:#ef4444;">*</span></label>
                                                <input type="date" name="earned_as_of_date" class="form-control" style="border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 12px; font-size: 13px;" value="{{ $entry->details['earned_as_of_date'] ?? '' }}" required>
                                            </div>
                                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                                <div>
                                                    <label class="form-label" style="font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; margin-bottom: 6px; display: block;">VL</label>
                                                    <input type="number" step="0.001" name="earned_vl" class="form-control" style="border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 12px; font-size: 13px;" value="{{ $entry->details['earned_vl'] ?? '' }}" placeholder="0.00">
                                                </div>
                                                <div>
                                                    <label class="form-label" style="font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; margin-bottom: 6px; display: block;">SL</label>
                                                    <input type="number" step="0.001" name="earned_sl" class="form-control" style="border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 12px; font-size: 13px;" value="{{ $entry->details['earned_sl'] ?? '' }}" placeholder="0.00">
                                                </div>
                                            </div>
                                        </div>

                                        <div style="border: 1px solid #e5e7eb; border-radius: 12px; padding: 18px;">
                                            <div style="font-size: 12px; font-weight: 700; color: #1f2937; text-transform: uppercase; margin-bottom: 16px;">NO. OF DAYS W/OUT PAY</div>
                                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                                                <div>
                                                    <label class="form-label" style="font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; margin-bottom: 6px; display: block;">VL</label>
                                                    <input type="number" step="0.5" name="days_without_pay_vl" class="form-control edit-dwp-vl" style="border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 12px; font-size: 13px;" value="{{ $entry->details['days_without_pay_vl'] ?? '' }}" placeholder="0">
                                                </div>
                                                <div>
                                                    <label class="form-label" style="font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; margin-bottom: 6px; display: block;">SL</label>
                                                    <input type="number" step="0.5" name="days_without_pay_sl" class="form-control edit-dwp-sl" style="border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 12px; font-size: 13px;" value="{{ $entry->details['days_without_pay_sl'] ?? '' }}" placeholder="0">
                                                </div>
                                                <div>
                                                    <label class="form-label" style="font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; margin-bottom: 6px; display: block;">Total</label>
                                                    <input type="number" step="0.5" name="days_without_pay_total" class="form-control edit-dwp-total" style="border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 12px; font-size: 13px; color: #2563eb; font-weight: bold; background: #eff6ff; cursor:not-allowed;" value="{{ $entry->details['days_without_pay_total'] ?? '' }}" placeholder="0" readonly>
                                                </div>
                                            </div>
                                            <div style="margin-bottom: 16px;">
                                                <label class="form-label" style="font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; margin-bottom: 6px; display: block;">Inclusive Dates</label>
                                                <input type="text" name="days_without_pay_inclusive_dates" class="form-control" style="border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 12px; font-size: 13px;" value="{{ $entry->details['days_without_pay_inclusive_dates'] ?? '' }}" placeholder="e.g. January 5-10, 2026">
                                            </div>
                                            <div>
                                                <label class="form-label" style="font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; margin-bottom: 6px; display: block;">Remarks (Optional)</label>
                                                <textarea name="days_without_pay_remarks" class="form-control" style="border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 12px; font-size: 13px;" rows="2" placeholder="Add any notes here...">{{ $entry->details['days_without_pay_remarks'] ?? '' }}</textarea>
                                            </div>
                                        </div>

                                        <div style="border: 1px solid #e5e7eb; border-radius: 12px; padding: 18px;">
                                            <div style="font-size: 12px; font-weight: 700; color: #1f2937; text-transform: uppercase; margin-bottom: 16px;">NO. OF DAYS OF UNDERTIME/TARDY W/OUT PAY</div>
                                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                                                <div>
                                                    <label class="form-label" style="font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; margin-bottom: 6px; display: block;">Hours</label>
                                                    <input type="number" name="undertime_tardy_hours" class="form-control" style="border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 12px; font-size: 13px;" value="{{ $entry->details['undertime_tardy_hours'] ?? '' }}" placeholder="0">
                                                </div>
                                                <div>
                                                    <label class="form-label" style="font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; margin-bottom: 6px; display: block;">Minutes</label>
                                                    <input type="number" name="undertime_tardy_mins" class="form-control" style="border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 12px; font-size: 13px;" value="{{ $entry->details['undertime_tardy_mins'] ?? '' }}" placeholder="0">
                                                </div>
                                            </div>
                                            <div>
                                                <label class="form-label" style="font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; margin-bottom: 6px; display: block;">Inclusive Dates</label>
                                                <input type="text" name="undertime_tardy_inclusive_dates" class="form-control" style="border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 12px; font-size: 13px;" value="{{ $entry->details['undertime_tardy_inclusive_dates'] ?? '' }}" placeholder="e.g. January 5-10, 2026">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light border-top-0" style="border-bottom-left-radius:16px; border-bottom-right-radius:16px;">
                                    <button type="button" class="btn btn-light fw-medium px-4" data-bs-dismiss="modal" style="border-radius:8px; border:1px solid #e5e7eb;">Cancel</button>
                                    <button type="submit" class="btn btn-primary fw-bold px-4" style="border-radius:8px;"><i class="bi bi-check-lg me-1"></i> Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
@endforeach

<!-- Generate Leave Record PDF Modal -->
<div class="modal fade" id="generateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; padding: 12px;">
            <form action="{{ route('leave-violations.recorded-entries.generate-report') }}" method="POST" target="_blank">
                @csrf
                <input type="hidden" name="record_ids" id="selectedRecordIds">
                <div class="modal-header" style="border-bottom: none; padding-bottom: 0;">
                    <h5 class="modal-title" style="font-weight: 700; font-size: 16px;">Generate Leave Record PDF</h5>
                </div>
                <div class="modal-body">
                    <p style="font-size: 12px; color: #ef4444; font-weight: 600; margin-bottom: 4px;">Leave Type(s) *</p>
                    <p style="font-size: 11px; color: #6b7280; margin-bottom: 12px;">Select one or more types that will appear in the generated document.</p>
                    
                    <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 24px;">
                        <label style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 13px; cursor: pointer;">
                            <input type="checkbox" name="leave_types[]" value="Leave of Absence"> Leave of Absence
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 13px; cursor: pointer;">
                            <input type="checkbox" name="leave_types[]" value="Undertime"> Undertime
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 13px; cursor: pointer;">
                            <input type="checkbox" name="leave_types[]" value="Tardy"> Tardy
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 13px; cursor: pointer;">
                            <input type="checkbox" name="leave_types[]" value="Unauthorized Absence"> Unauthorized Absence
                        </label>
                    </div>

                    <p style="font-size: 11px; color: #6b7280; margin-bottom: 12px;">Select the certifying officer. The name and position will appear in the <i>Noted by</i> section of the document.</p>
                    
                    <div style="margin-bottom: 12px;">
                        <label style="font-size: 11px; font-weight: 700; color: #374151; display: block; margin-bottom: 4px;">Name</label>
                        <select name="noted_by_name" id="notedByNameSelect" class="form-select" style="font-size: 13px; border-radius: 8px;">
                            <option value="">Select a user...</option>
                            @foreach($moduleUsers as $user)
                                @php
                                    $position = \App\Models\PlantillaRecord::whereRaw("CONCAT(first_name, ' ', last_name) = ?", [$user->name])
                                        ->orWhereRaw("CONCAT(first_name, ' ', middle_name, ' ', last_name) = ?", [$user->name])
                                        ->value('position_title') ?? 'HR Management Officer';
                                @endphp
                                <option value="{{ $user->name }}" data-position="{{ $position }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label style="font-size: 11px; font-weight: 700; color: #374151; display: block; margin-bottom: 4px;">Position</label>
                        <input type="text" name="noted_by_position" id="notedByPositionInput" class="form-control" style="font-size: 13px; border-radius: 8px;" placeholder="Position title...">
                    </div>
                </div>
                <div class="modal-footer" style="border-top: none; padding-top: 16px; gap: 8px;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="font-size: 13px; font-weight: 600; padding: 8px 16px; border-radius: 8px;">Cancel</button>
                    <button type="submit" class="btn btn-danger" style="font-size: 13px; font-weight: 600; padding: 8px 16px; border-radius: 8px; background: #e11d48; border: none;">Print Preview</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add Leave Record modal --}}
<div class="modal fade" id="addRecordModal" tabindex="-1" aria-labelledby="addRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;">
            <div class="modal-header" style="background: linear-gradient(135deg,#1e3a5f 0%,#1a5276 55%,#0f4c75 100%); color:#fff; border-top-left-radius:16px; border-top-right-radius:16px; border-bottom:none;">
                <h5 class="modal-title fw-bold" id="addRecordModalLabel"><i class="bi bi-journal-plus me-2"></i>Record Leave / Undertime / Tardy Without Pay</h5>
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('leave-violations.recorded-entries.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-label" style="font-size: 10px;">EMPLOYEE INFORMATION</div>
                    <div style="display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 16px;">
                        <div>
                            <label class="form-label">EMPLOYEE NAME <span style="color:#ef4444;">*</span></label>
                            <select name="plantilla_record_id" id="addRecordEmployeeSelect" class="form-select" required>
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
                            <input type="text" id="addRecordPositionInput" class="form-control readonly-field" readonly>
                        </div>
                        <div>
                            <label class="form-label">OFFICE</label>
                            <input type="text" id="addRecordOfficeInput" class="form-control readonly-field" readonly>
                        </div>
                    </div>

                    <div class="grid-container">
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

                        <div class="grid-card">
                            <div class="section-title">NO. OF DAYS W/OUT PAY</div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                                <div>
                                    <label class="form-label">VL</label>
                                    <input type="number" step="0.5" name="days_without_pay_vl" id="addRecordDwpVl" class="form-control" placeholder="0">
                                </div>
                                <div>
                                    <label class="form-label">SL</label>
                                    <input type="number" step="0.5" name="days_without_pay_sl" id="addRecordDwpSl" class="form-control" placeholder="0">
                                </div>
                                <div>
                                    <label class="form-label">Total</label>
                                    <input type="number" step="0.5" name="days_without_pay_total" id="addRecordDwpTotal" class="form-control readonly-field" style="color: #2563eb; font-weight: bold; background: #eff6ff;" placeholder="0" readonly>
                                </div>
                            </div>
                            <div style="margin-bottom: 16px;">
                                <label class="form-label">Inclusive Dates</label>
                                <input type="text" name="days_without_pay_inclusive_dates" class="form-control add-record-flatpickr" placeholder="Select dates...">
                            </div>
                            <div>
                                <label class="form-label">Remarks (Optional)</label>
                                <textarea name="days_without_pay_remarks" class="form-control" rows="2" placeholder="Add any notes here..."></textarea>
                            </div>
                        </div>

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
                                <input type="text" name="undertime_tardy_inclusive_dates" class="form-control add-record-flatpickr" placeholder="Select dates...">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0" style="border-bottom-left-radius:16px; border-bottom-right-radius:16px;">
                    <button type="button" class="btn btn-light fw-medium px-4" data-bs-dismiss="modal" style="border-radius:8px; border:1px solid #e5e7eb;">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4" style="border-radius:8px;"><i class="bi bi-plus-lg me-1"></i> Save Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const select = document.getElementById('addRecordEmployeeSelect');
        const posInput = document.getElementById('addRecordPositionInput');
        const offInput = document.getElementById('addRecordOfficeInput');

        if (select) {
            select.addEventListener('change', function () {
                const opt = this.options[this.selectedIndex];
                if (opt && opt.value) {
                    posInput.value = opt.getAttribute('data-position') || '';
                    offInput.value = opt.getAttribute('data-office') || '';
                } else {
                    posInput.value = '';
                    offInput.value = '';
                }
            });
        }

        document.getElementById('addRecordDwpVl')?.addEventListener('input', calcAddRecordDwpTotal);
        document.getElementById('addRecordDwpSl')?.addEventListener('input', calcAddRecordDwpTotal);
        function calcAddRecordDwpTotal() {
            const vl = parseFloat(document.getElementById('addRecordDwpVl').value) || 0;
            const sl = parseFloat(document.getElementById('addRecordDwpSl').value) || 0;
            document.getElementById('addRecordDwpTotal').value = vl + sl;
        }

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

        flatpickr(".add-record-flatpickr", {
            mode: "multiple",
            onChange: function (selectedDates, dateStr, instance) {
                setTimeout(() => { instance.input.value = formatMultipleDates(selectedDates); }, 0);
            },
            onClose: function (selectedDates, dateStr, instance) {
                setTimeout(() => { instance.input.value = formatMultipleDates(selectedDates); }, 0);
            }
        });
    });
</script>

<script>
    // Edit-record modals: one handler (via delegation) covers every row's modal.
    function calcEditDwpTotal(input) {
        const modal = input.closest('.modal-content');
        const vl = parseFloat(modal.querySelector('.edit-dwp-vl')?.value) || 0;
        const sl = parseFloat(modal.querySelector('.edit-dwp-sl')?.value) || 0;
        const totalInput = modal.querySelector('.edit-dwp-total');
        if (totalInput) totalInput.value = vl + sl;
    }
    document.addEventListener('input', function (e) {
        if (e.target.matches('.edit-dwp-vl, .edit-dwp-sl')) {
            calcEditDwpTotal(e.target);
        }
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.record-checkbox');
        const idsInput = document.getElementById('selectedRecordIds');
        const form = document.querySelector('#generateModal form');
        
        const nameSelect = document.getElementById('notedByNameSelect');
        const positionInput = document.getElementById('notedByPositionInput');
        
        if (nameSelect) {
            nameSelect.addEventListener('change', function() {
                const selectedOpt = this.options[this.selectedIndex];
                if (selectedOpt && selectedOpt.value) {
                    positionInput.value = selectedOpt.getAttribute('data-position') || '';
                } else {
                    positionInput.value = '';
                }
            });
        }

        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
        });

        form.addEventListener('submit', function(e) {
            const selectedIds = Array.from(checkboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.value);
            
            if (selectedIds.length === 0) {
                e.preventDefault();
                alert('Please select at least one record.');
                return false;
            }
            
            idsInput.value = JSON.stringify(selectedIds);
            return true;
        });
    });
</script>
</x-dashboard-app>
