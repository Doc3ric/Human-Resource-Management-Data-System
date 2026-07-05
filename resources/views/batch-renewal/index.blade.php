<x-dashboard-app>
<style>
.br-hero {
    background: linear-gradient(135deg, #064e3b 0%, #065f46 55%, #047857 100%);
    border-radius: 14px;
    padding: 24px 28px;
    position: relative;
    overflow: hidden;
    margin-bottom: 20px;
}
.br-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: radial-gradient(circle, rgba(255,255,255,.07) 1px, transparent 1px);
    background-size: 22px 22px;
}
.br-hero-title {
    font-size: 1.55rem;
    font-weight: 700;
    color: #fff;
    position: relative;
    z-index: 1;
}
.br-hero-sub {
    font-size: .85rem;
    color: rgba(255,255,255,.75);
    position: relative;
    z-index: 1;
    margin-top: 2px;
}
.br-filter-card {
    background: #fff;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    padding: 18px 20px;
    margin-bottom: 16px;
}
.br-filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: flex-end;
}
.br-filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 160px;
    flex: 1;
}
.br-filter-group label {
    font-size: .7rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: .05em;
}
.br-filter-group select,
.br-filter-group input {
    border: 1px solid #d1d5db;
    border-radius: 7px;
    padding: 7px 10px;
    font-size: .85rem;
    background: #f9fafb;
    outline: none;
    transition: border-color .15s;
}
.br-filter-group select:focus,
.br-filter-group input:focus {
    border-color: #059669;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(5,150,105,.12);
}
.br-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: .85rem;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all .15s;
}
.br-btn-green  { background: #059669; color: #fff; }
.br-btn-green:hover  { background: #047857; }
.br-btn-slate  { background: #475569; color: #fff; }
.br-btn-slate:hover  { background: #334155; }
.br-btn-outline { background: #fff; color: #374151; border: 1px solid #d1d5db; }
.br-btn-outline:hover { background: #f3f4f6; }

.br-table-wrap {
    background: #fff;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
    margin-bottom: 16px;
}
.br-table {
    width: 100%;
    border-collapse: collapse;
    font-size: .82rem;
}
.br-table thead th {
    background: #f8fafc;
    padding: 10px 12px;
    text-align: left;
    font-size: .7rem;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: .06em;
    border-bottom: 1px solid #e5e7eb;
    white-space: nowrap;
}
.br-table tbody td {
    padding: 9px 12px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
    color: #1e293b;
}
.br-table tbody tr:last-child td { border-bottom: none; }
.br-table tbody tr:hover { background: #f8fafc; }
.br-table tbody tr.row-invalid { background: #fff5f5; }
.br-table tbody tr.row-invalid:hover { background: #fee2e2; }
.br-table tbody tr.row-selected { background: #ecfdf5; }
.br-table tbody tr.row-selected:hover { background: #d1fae5; }

.badge-jo     { background: #dbeafe; color: #1d4ed8; padding: 2px 8px; border-radius: 999px; font-size: .7rem; font-weight: 600; }
.badge-cas    { background: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 999px; font-size: .7rem; font-weight: 600; }
.badge-warn   { background: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 999px; font-size: .7rem; font-weight: 600; }
.badge-error  { background: #fee2e2; color: #b91c1c; padding: 2px 8px; border-radius: 999px; font-size: .7rem; font-weight: 600; }
.badge-ok     { background: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 999px; font-size: .7rem; font-weight: 600; }

.br-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    border-bottom: 1px solid #e5e7eb;
    flex-wrap: wrap;
    gap: 8px;
}
.br-selection-count {
    font-size: .82rem;
    color: #64748b;
    font-weight: 500;
}
.br-empty {
    text-align: center;
    padding: 60px 20px;
    color: #94a3b8;
}
.br-empty i { font-size: 2.5rem; display: block; margin-bottom: 10px; }

/* Modal */
.br-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.45);
    z-index: 1050;
    align-items: center;
    justify-content: center;
}
.br-modal-overlay.show { display: flex; }
.br-modal {
    background: #fff;
    border-radius: 14px;
    width: min(560px, 95vw);
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 25px 60px rgba(0,0,0,.3);
}
.br-modal-header {
    padding: 20px 24px 16px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.br-modal-title { font-size: 1.05rem; font-weight: 700; color: #0f172a; }
.br-modal-close { background: none; border: none; font-size: 1.3rem; color: #94a3b8; cursor: pointer; line-height: 1; }
.br-modal-close:hover { color: #475569; }
.br-modal-body { padding: 20px 24px; }
.br-modal-footer {
    padding: 14px 24px 20px;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    border-top: 1px solid #e5e7eb;
}
.br-field { margin-bottom: 16px; }
.br-field label { display: block; font-size: .75rem; font-weight: 600; color: #64748b; margin-bottom: 5px; text-transform: uppercase; letter-spacing: .04em; }
.br-field input, .br-field select, .br-field textarea {
    width: 100%; border: 1px solid #d1d5db; border-radius: 8px;
    padding: 9px 12px; font-size: .88rem; background: #f9fafb; outline: none;
    transition: border-color .15s;
}
.br-field input:focus, .br-field select:focus, .br-field textarea:focus {
    border-color: #059669; background: #fff; box-shadow: 0 0 0 3px rgba(5,150,105,.12);
}
.br-field-row { display: flex; gap: 12px; }
.br-field-row .br-field { flex: 1; }

/* Validation preview panel */
.br-validation-panel {
    display: none;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 16px;
    max-height: 240px;
    overflow-y: auto;
}
.br-validation-panel .vp-header {
    padding: 8px 14px;
    font-size: .75rem;
    font-weight: 700;
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: .05em;
}
.br-validation-panel .vp-row {
    padding: 7px 14px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: .8rem;
}
.br-validation-panel .vp-row:last-child { border-bottom: none; }
.vp-row .vp-name { flex: 1; color: #1e293b; font-weight: 500; }
.vp-row .vp-err { color: #dc2626; font-size: .75rem; }

/* Inline edit fields */
.edit-field {
    width: 100%;
    border: 1px solid transparent;
    border-radius: 5px;
    padding: 3px 6px;
    font-size: .8rem;
    background: transparent;
    outline: none;
    transition: border-color .15s, background .15s;
    font-family: inherit;
}
.edit-field:hover {
    border-color: #d1d5db;
    background: #f9fafb;
}
.edit-field:focus {
    border-color: #059669;
    background: #fff;
    box-shadow: 0 0 0 2px rgba(5,150,105,.12);
}
.edit-field.dirty {
    border-color: #f59e0b;
    background: #fffbeb;
}

/* Result modal */
.br-result-card { border-radius: 8px; padding: 16px; margin-bottom: 14px; }
.br-result-success { background: #ecfdf5; border: 1px solid #a7f3d0; }
.br-result-fail    { background: #fff5f5; border: 1px solid #fecaca; }
.br-result-num { font-size: 2rem; font-weight: 800; }
.br-result-label { font-size: .8rem; color: #64748b; margin-top: 2px; }
</style>

<div style="padding: 16px;">

    {{-- Hero --}}
    <div class="br-hero">
        <div class="br-hero-title"><i class="bi bi-arrow-repeat me-2"></i>Batch Contract Renewal</div>
        <div class="br-hero-sub">Filter, select, and renew multiple Casual/Job Order contracts in one atomic operation.</div>
    </div>

    {{-- Stats with compliance analysis --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px;">
        <x-stat-card icon="bi-person-lines-fill" color="indigo" label="Total Casual" :value="$brStats['total_casual']"
            compliance="CSC / RA 6656"
            analysis="Active casual employees subject to monthly contract renewal. RA 6656 prohibits casual employment of more than 12 consecutive months for the same position." />

        <x-stat-card icon="bi-briefcase-fill" color="blue" label="Total Job Order" :value="$brStats['total_jo']"
            compliance="COA Circular 2012-001"
            analysis="JO workers must have a specific Work Program per engagement. Annual aggregate must not exceed 12 months. Renewal requires office request and funding certification." />

        <x-stat-card icon="bi-calendar-event" color="orange" label="Expiring in 30 Days" :value="$brStats['expiring_30']"
            :alert="$brStats['expiring_30'] > 0"
            compliance="CSC / DBM"
            analysis="{{ $brStats['expiring_30'] > 0 ? $brStats['expiring_30'].' contract(s) expire within 30 days. Process renewal endorsements immediately to avoid service gaps and unauthorized continuation.' : 'No contracts expiring within 30 days.' }}" />

        <x-stat-card icon="bi-exclamation-triangle-fill" color="red" label="Expired Contracts" :value="$brStats['expired']"
            :alert="$brStats['expired'] > 0"
            compliance="COA / CSC"
            analysis="{{ $brStats['expired'] > 0 ? $brStats['expired'].' contract(s) are past expiry. Continued service without a valid contract may constitute unauthorized hiring per COA rules and Civil Service regulations.' : 'No expired contracts on record.' }}" />
    </div>

    {{-- Filter Card --}}
    <div class="br-filter-card">
        <form method="GET" action="{{ route('batch-renewal.index') }}" id="filterForm">
            <div class="br-filter-row" style="margin-bottom:10px;">
                <div class="br-filter-group" style="flex:2; min-width:220px;">
                    <label><i class="bi bi-search" style="margin-right:4px;"></i>Search Employee</label>
                    <input type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Last name, first name, position, TIN, item no…"
                        autocomplete="off">
                </div>
            </div>
            <div class="br-filter-row">
                <div class="br-filter-group">
                    <label>Office / Department</label>
                    <select name="office">
                        <option value="">— All Offices —</option>
                        @foreach($offices as $o)
                            <option value="{{ $o }}" {{ request('office') === $o ? 'selected' : '' }}>{{ $o }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="br-filter-group" style="max-width:160px;">
                    <label>Status</label>
                    <select name="status">
                        <option value="">— All —</option>
                        <option value="Casual" {{ request('status') === 'Casual' ? 'selected' : '' }}>Casual</option>
                        <option value="JO"     {{ request('status') === 'JO' ? 'selected' : '' }}>Job Order</option>
                    </select>
                </div>

                <div class="br-filter-group" style="max-width:220px;">
                    <label>Expiration Window</label>
                    <select name="expiry_window" id="expiryWindowSel" onchange="toggleCustomDates(this.value)">
                        <option value="">— Any —</option>
                        <option value="7d"         {{ request('expiry_window') === '7d' ? 'selected' : '' }}>Expiring in 7 days</option>
                        <option value="15d"        {{ request('expiry_window') === '15d' ? 'selected' : '' }}>Expiring in 15 days</option>
                        <option value="30d"        {{ request('expiry_window') === '30d' ? 'selected' : '' }}>Expiring in 30 days</option>
                        <option value="60d"        {{ request('expiry_window') === '60d' ? 'selected' : '' }}>Expiring in 60 days</option>
                        <option value="this_month" {{ request('expiry_window') === 'this_month' ? 'selected' : '' }}>This month</option>
                        <option value="next_month" {{ request('expiry_window') === 'next_month' ? 'selected' : '' }}>Next month</option>
                        <option value="custom"     {{ request('expiry_window') === 'custom' ? 'selected' : '' }}>Custom range…</option>
                    </select>
                </div>

                <div class="br-filter-group" id="customDateGroup" style="display:none; max-width:160px;">
                    <label>From</label>
                    <input type="date" name="expiry_from" value="{{ request('expiry_from') }}">
                </div>
                <div class="br-filter-group" id="customDateGroupTo" style="display:none; max-width:160px;">
                    <label>To</label>
                    <input type="date" name="expiry_to" value="{{ request('expiry_to') }}">
                </div>

                <div style="display:flex; gap:8px; align-items:flex-end;">
                    <button type="submit" class="br-btn br-btn-green"><i class="bi bi-funnel-fill"></i> Filter</button>
                    <a href="{{ route('batch-renewal.index') }}" class="br-btn br-btn-outline"><i class="bi bi-x"></i> Clear</a>
                </div>
            </div>
        </form>
    </div>

    @if($filtered)
    {{-- Toolbar --}}
    <div class="br-table-wrap">
        <div class="br-toolbar">
            <span class="br-selection-count">
                <span id="selCount">0</span> of {{ $records->count() }} records selected
                @if($records->count() > 0)
                    &nbsp;·&nbsp;
                    <span id="invalidCount" style="color:#dc2626;"></span>
                @endif
            </span>
            <div style="display:flex; gap:8px;">
                @if($records->count() > 0)
                <button class="br-btn br-btn-outline" onclick="selectAll(true)"><i class="bi bi-check-all"></i> Select All</button>
                <button class="br-btn br-btn-outline" onclick="selectAll(false)"><i class="bi bi-x"></i> Deselect All</button>
                <button class="br-btn br-btn-green" id="renewBtn" onclick="openRenewalModal()" disabled>
                    <i class="bi bi-arrow-repeat"></i> Batch Renew Selected
                </button>
                @endif
            </div>
        </div>

        @if($records->count() === 0)
        <div class="br-empty">
            <i class="bi bi-inbox"></i>
            No records match the selected filters.
        </div>
        @else
        <table class="br-table" id="recordsTable">
            <thead>
                <tr>
                    <th style="width:36px;"><input type="checkbox" id="checkAll" onchange="selectAll(this.checked)" style="cursor:pointer;"></th>
                    <th>Last Name / Middle Name</th>
                    <th>Office / Detailed Unit <span style="color:#059669; font-size:.65rem; font-weight:500;">(editable)</span></th>
                    <th>Position</th>
                    <th>Status</th>
                    <th>Sex</th>
                    <th>Birthday</th>
                    <th>Current Start</th>
                    <th>Current End</th>
                    <th>Rate</th>
                    <th>Ready?</th>
                    <th style="text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $r)
                @php
                    $joStatus = in_array(strtoupper($r->employment_status), ['JO','J.O.','JOB ORDER']);
                @endphp
                <tr data-id="{{ $r->id }}" @if(!$r->is_renewed) style="background:var(--color-warning-bg,#fffbeb);" @endif>
                    <td @if(!$r->is_renewed) onclick="attemptBlockedSelection('{{ addslashes(strtoupper($r->last_name) . ', ' . $r->first_name) }}')" @endif>
                        <input type="checkbox"
                            class="row-cb"
                            value="{{ $r->id }}"
                            onchange="updateSelection()"
                            @if(!$r->is_renewed)
                                disabled
                                title="Action Blocked: {{ strtoupper($r->last_name) }}, {{ $r->first_name }} is currently unrenewed or excluded for this rating period. Resolve status under individual Personnel Inventory before executing batch renewal."
                                data-blocked="1"
                                data-name="{{ strtoupper($r->last_name) }}, {{ $r->first_name }}"
                            @endif
                            style="cursor:pointer;">
                        @if(!$r->is_renewed)
                            <div style="font-size:9px;color:#b45309;font-weight:700;margin-top:2px;">NOT RENEWED</div>
                        @endif
                    </td>
                    <td style="min-width:180px;">
                        <input type="text"
                            class="edit-field"
                            data-id="{{ $r->id }}"
                            data-field="last_name"
                            value="{{ strtoupper($r->last_name) }}"
                            placeholder="Last Name"
                            style="font-weight:600; text-transform:uppercase; background:#f1f5f9; color:#64748b; cursor:not-allowed;"
                            readonly>
                        <div style="display:flex; gap:4px; margin-top:3px;">
                            <span style="font-size:.72rem; color:#94a3b8; align-self:center;">M:</span>
                            <input type="text"
                                class="edit-field"
                                data-id="{{ $r->id }}"
                                data-field="middle_name"
                                value="{{ $r->middle_name }}"
                                placeholder="Middle Name"
                                style="font-size:.75rem; color:#64748b; background:#f1f5f9; cursor:not-allowed;"
                                readonly>
                        </div>
                        @if($r->first_name)
                            <div style="font-size:.72rem; color:#94a3b8; margin-top:2px;">{{ $r->first_name }} {{ $r->name_extension }}</div>
                        @endif
                    </td>
                    <td style="min-width:180px;">
                        <input type="text"
                            class="edit-field"
                            data-id="{{ $r->id }}"
                            data-field="office_department"
                            value="{{ $r->office }}"
                            placeholder="Office / Department">
                        <div style="display:flex; gap:4px; margin-top:3px;">
                            <span style="font-size:.7rem; color:#94a3b8; align-self:center; white-space:nowrap;">Unit:</span>
                            <input type="text"
                                class="edit-field"
                                data-id="{{ $r->id }}"
                                data-field="detailed_unit"
                                value="{{ $r->detailed_unit ?? '' }}"
                                placeholder="Detailed Unit (if any)"
                                style="font-size:.75rem; color:#64748b; background:#f1f5f9; cursor:not-allowed;"
                                readonly>
                        </div>
                    </td>
                    <td style="min-width:160px;">
                        <input type="text"
                            class="edit-field"
                            data-id="{{ $r->id }}"
                            data-field="position_title"
                            value="{{ $r->position_title }}"
                            placeholder="Position Title"
                            style="background:#f1f5f9; color:#64748b; cursor:not-allowed; {{ empty($r->position_title) ? 'border-color:#fca5a5;' : '' }}"
                            readonly>
                        @if(empty($r->position_title))
                            <div style="font-size:.7rem; color:#dc2626; margin-top:2px;"><i class="bi bi-exclamation-triangle-fill"></i> Missing position — edit profile to fix</div>
                        @endif
                    </td>
                    <td>
                        <span class="{{ $joStatus ? 'badge-jo' : 'badge-cas' }}">
                            {{ $joStatus ? 'JO' : 'Casual' }}
                        </span>
                    </td>
                    <td style="font-size:.78rem; text-align:center;">
                        @if($r->sex === 'M')
                            <span style="color:#1d4ed8; font-weight:600;">M</span>
                        @elseif($r->sex === 'F')
                            <span style="color:#be185d; font-weight:600;">F</span>
                        @else
                            <span style="color:#94a3b8;">—</span>
                        @endif
                    </td>
                    <td style="font-size:.78rem;">
                        {{ $r->date_of_birth ? \Carbon\Carbon::parse($r->date_of_birth)->format('M d, Y') : '—' }}
                    </td>
                    <td style="font-size:.78rem;">
                        {{ $r->latest_start ? $r->latest_start->format('M d, Y') : '—' }}
                    </td>
                    <td style="font-size:.78rem;">
                        @if($r->latest_end)
                            @php $daysLeft = now()->diffInDays($r->latest_end, false); @endphp
                            <span style="font-weight:600; color:{{ $daysLeft < 0 ? '#dc2626' : ($daysLeft <= 14 ? '#d97706' : '#059669') }};">
                                {{ $r->latest_end->format('M d, Y') }}
                            </span>
                            @if($daysLeft < 0)
                                <div style="font-size:.7rem; color:#dc2626;">Expired {{ abs($daysLeft) }}d ago</div>
                            @elseif($daysLeft <= 30)
                                <div style="font-size:.7rem; color:#d97706;">{{ $daysLeft }}d remaining</div>
                            @endif
                        @else
                            <span style="color:#94a3b8;">No contract on file</span>
                        @endif
                    </td>
                    <td style="font-size:.78rem;">
                        @if($r->latest_rate)
                            ₱{{ number_format($r->latest_rate, 2) }}
                            <span style="color:#94a3b8;">/ {{ $r->latest_rate_type ?? '—' }}</span>
                        @else
                            <span style="color:#94a3b8;">—</span>
                        @endif
                    </td>
                    <td>
                        @if(empty($r->position_title))
                            <span class="badge-warn"><i class="bi bi-exclamation-triangle-fill"></i> Fix Needed</span>
                        @else
                            <span class="badge-ok"><i class="bi bi-check-circle-fill"></i> Ready</span>
                        @endif
                    </td>
                    <td style="text-align:center; white-space:nowrap;">
                        @php
                            $empStatus = strtoupper(trim($r->employment_status ?? ''));
                            $isJO  = in_array($empStatus, ['JO', 'J.O.', 'JOB ORDER']);
                            $isCas = in_array($empStatus, ['CASUAL', 'CAS', 'C']);
                            $editUrl = $isJO
                                ? route('job-orders.edit', $r->id)
                                : ($isCas
                                    ? route('casual.edit', $r->id)
                                    : route('plantilla.edit', $r->id));
                            $editLabel = $isJO ? 'Edit JO' : ($isCas ? 'Edit Casual' : 'Edit Record');
                        @endphp
                        <a href="{{ $editUrl }}"
                           target="_blank"
                           class="br-btn br-btn-outline"
                           style="font-size:.75rem; padding:5px 10px; text-decoration:none; display:inline-flex; align-items:center; gap:5px;"
                           title="Open edit form in new tab">
                            <i class="bi bi-pencil-square"></i> {{ $editLabel }}
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>


    @else
    {{-- Not yet filtered --}}
    <div class="br-table-wrap">
        <div class="br-empty">
            <i class="bi bi-funnel"></i>
            <div style="font-weight:600; color:#475569; margin-bottom:4px;">Set your filters above</div>
            <div style="font-size:.82rem;">Select an office, status, and/or expiration window, then click <strong>Filter</strong> to load records.</div>
        </div>
    </div>
    @endif

</div>

{{-- ══════ RENEWAL MODAL ══════ --}}
<div class="br-modal-overlay" id="renewalModal">
    <div class="br-modal">
        <div class="br-modal-header">
            <div class="br-modal-title"><i class="bi bi-arrow-repeat me-2 text-success"></i>Batch Contract Renewal</div>
            <button class="br-modal-close" onclick="closeModal('renewalModal')">×</button>
        </div>
        <div class="br-modal-body">
            <div id="selSummary" style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:10px 14px; font-size:.82rem; color:#064e3b; margin-bottom:16px;">
                <i class="bi bi-info-circle-fill me-1"></i>
                <span id="selSummaryText"></span>
            </div>

            {{-- Validation Preview --}}
            <div id="validationPanel" class="br-validation-panel">
                <div class="vp-header">Pre-Renewal Check Results</div>
                <div id="validationRows"></div>
            </div>

            <div class="br-field-row">
                <div class="br-field">
                    <label>New Contract Start Date <span style="color:#dc2626;">*</span></label>
                    <input type="date" id="m-start" required>
                </div>
                <div class="br-field">
                    <label>New Contract End Date <span style="color:#dc2626;">*</span></label>
                    <input type="date" id="m-end" required>
                </div>
            </div>

            <div class="br-field-row">
                <div class="br-field">
                    <label>Rate / Compensation <span style="color:#94a3b8;">(optional)</span></label>
                    <input type="number" id="m-rate" placeholder="0.00" min="0" step="0.01">
                </div>
                <div class="br-field" style="max-width:140px;">
                    <label>Rate Type</label>
                    <select id="m-rate-type">
                        <option value="">—</option>
                        <option value="Daily">Daily</option>
                        <option value="Monthly">Monthly</option>
                        <option value="Annual">Annual</option>
                    </select>
                </div>
            </div>

            <div class="br-field">
                <label>Notes <span style="color:#94a3b8;">(optional)</span></label>
                <textarea id="m-notes" rows="2" placeholder="e.g. Q3 2026 renewal batch" maxlength="500"
                    style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:9px 12px; font-size:.88rem; resize:vertical; background:#f9fafb; outline:none;"></textarea>
            </div>

            <div id="validateMsg" style="display:none; font-size:.82rem; padding:8px 12px; border-radius:7px; margin-top:4px;"></div>
        </div>
        <div class="br-modal-footer">
            <button class="br-btn br-btn-outline" onclick="closeModal('renewalModal')">Cancel</button>
            <button class="br-btn br-btn-slate" id="checkReadyBtn" onclick="runPreValidation()">
                <i class="bi bi-shield-check"></i> Check Readiness
            </button>
            <button class="br-btn br-btn-green" id="processBtn" onclick="processRenewals()" disabled>
                <i class="bi bi-arrow-repeat"></i> Process Renewals
            </button>
        </div>
    </div>
</div>

{{-- ══════ RESULT MODAL ══════ --}}
<div class="br-modal-overlay" id="resultModal">
    <div class="br-modal">
        <div class="br-modal-header">
            <div class="br-modal-title"><i class="bi bi-clipboard-check me-2"></i>Renewal Summary</div>
            <button class="br-modal-close" onclick="closeModal('resultModal')">×</button>
        </div>
        <div class="br-modal-body" id="resultModalBody"></div>
        <div class="br-modal-footer">
            <button class="br-btn br-btn-green" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Reload Table
            </button>
            <button class="br-btn br-btn-outline" onclick="closeModal('resultModal')">Close</button>
        </div>
    </div>
</div>

<script>
const CSRF = '{{ csrf_token() }}';
const VALIDATE_URL = '{{ route('batch-renewal.validate') }}';
const PROCESS_URL  = '{{ route('batch-renewal.process') }}';

// ── Inline edit tracking ─────────────────────────────────────────────────────

const fieldEdits = {}; // { recordId: { field: value, ... } }

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.edit-field').forEach(input => {
        if (input.readOnly) return; // skip locked fields
        const original = input.value;
        input.addEventListener('input', () => {
            const id    = input.dataset.id;
            const field = input.dataset.field;
            if (!fieldEdits[id]) fieldEdits[id] = {};
            fieldEdits[id][field] = input.value;
            input.classList.toggle('dirty', input.value !== original);
        });
    });
});

function getFieldUpdates(ids) {
    const updates = {};
    ids.forEach(id => {
        if (fieldEdits[id] && Object.keys(fieldEdits[id]).length > 0) {
            updates[id] = fieldEdits[id];
        }
    });
    return updates;
}

// ── Selection helpers ────────────────────────────────────────────────────────

function getSelectedIds() {
    return [...document.querySelectorAll('.row-cb:checked')].map(cb => cb.value);
}

function selectAll(checked) {
    // Module 1.2 — unrenewed rows never get swept up by "select all".
    document.querySelectorAll('.row-cb:not([data-blocked])').forEach(cb => cb.checked = checked);
    const blockedChecked = [...document.querySelectorAll('.row-cb[data-blocked]')].filter(cb => cb.checked);
    if (checked && document.querySelectorAll('.row-cb[data-blocked]').length > 0 && typeof Swal !== 'undefined') {
        // Informational only — nothing was actually selected for these rows.
    }
    const allCb = document.getElementById('checkAll');
    if (allCb) allCb.checked = checked;
    updateSelection();
}

function attemptBlockedSelection(name) {
    const message = `Action Blocked: ${name} is currently unrenewed or excluded for this rating period. Resolve status under individual Personnel Inventory before executing batch renewal.`;
    if (typeof Swal !== 'undefined') {
        Swal.fire({ icon: 'warning', title: 'Action Blocked', text: message });
    } else {
        alert(message);
    }
}

function updateSelection() {
    const ids = getSelectedIds();
    const selCount = document.getElementById('selCount');
    const renewBtn = document.getElementById('renewBtn');
    const invalidSpan = document.getElementById('invalidCount');
    if (selCount) selCount.textContent = ids.length;
    if (renewBtn) renewBtn.disabled = ids.length === 0;

    // Sync header checkbox
    const allCbs = document.querySelectorAll('.row-cb');
    const checked = document.querySelectorAll('.row-cb:checked');
    const checkAll = document.getElementById('checkAll');
    if (checkAll) {
        checkAll.indeterminate = checked.length > 0 && checked.length < allCbs.length;
        checkAll.checked = checked.length === allCbs.length && allCbs.length > 0;
    }

    // Highlight rows
    document.querySelectorAll('.row-cb').forEach(cb => {
        const row = cb.closest('tr');
        if (cb.checked) row.classList.add('row-selected');
        else row.classList.remove('row-selected');
    });
}

// ── Filter: custom date range toggle ────────────────────────────────────────

function toggleCustomDates(val) {
    const show = val === 'custom';
    document.getElementById('customDateGroup').style.display   = show ? 'flex' : 'none';
    document.getElementById('customDateGroupTo').style.display = show ? 'flex' : 'none';
}
// Init on page load
toggleCustomDates('{{ request('expiry_window') }}');

// ── Modal helpers ────────────────────────────────────────────────────────────

function openRenewalModal() {
    const ids = getSelectedIds();
    if (ids.length === 0) return;
    document.getElementById('selSummaryText').textContent =
        ids.length + ' record(s) selected for renewal.';
    document.getElementById('validationPanel').style.display = 'none';
    document.getElementById('validateMsg').style.display = 'none';
    document.getElementById('processBtn').disabled = true;
    document.getElementById('renewalModal').classList.add('show');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('show');
}

// Close on overlay click
document.querySelectorAll('.br-modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
        if (e.target === overlay) overlay.classList.remove('show');
    });
});

// ── Pre-validation ───────────────────────────────────────────────────────────

function runPreValidation() {
    const ids   = getSelectedIds();
    const start = document.getElementById('m-start').value;
    const end   = document.getElementById('m-end').value;

    if (!start || !end) {
        showMsg('danger', 'Please enter both Contract Start Date and End Date before checking readiness.');
        return;
    }
    if (start >= end) {
        showMsg('danger', 'End date must be after start date.');
        return;
    }

    const btn = document.getElementById('checkReadyBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Checking…';

    fetch(VALIDATE_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ ids, contract_start_date: start, contract_end_date: end })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-shield-check"></i> Check Readiness';

        const panel = document.getElementById('validationPanel');
        const rowsEl = document.getElementById('validationRows');
        rowsEl.innerHTML = '';

        data.results.forEach(r => {
            const div = document.createElement('div');
            div.className = 'vp-row';
            div.innerHTML = `
                <span class="vp-name">${r.name}</span>
                ${r.valid
                    ? '<span class="badge-ok"><i class="bi bi-check-circle-fill"></i> Ready</span>'
                    : `<span class="vp-err"><i class="bi bi-x-circle-fill"></i> ${r.errors.join('; ')}</span>`
                }`;
            rowsEl.appendChild(div);
        });
        panel.style.display = 'block';

        if (data.invalid_count === 0) {
            showMsg('success', `All ${data.valid_count} record(s) passed validation. You may proceed.`);
            document.getElementById('processBtn').disabled = false;
        } else {
            showMsg('warning',
                `${data.valid_count} record(s) ready · ${data.invalid_count} will be skipped (see above).
                 ${data.valid_count > 0 ? 'You may still proceed — only valid records will be renewed.' : 'No valid records to renew.'}`
            );
            document.getElementById('processBtn').disabled = data.valid_count === 0;
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-shield-check"></i> Check Readiness';
        showMsg('danger', 'Network error. Please try again.');
    });
}

function showMsg(type, text) {
    const el = document.getElementById('validateMsg');
    const colors = {
        success: { bg:'#f0fdf4', border:'#bbf7d0', color:'#064e3b' },
        warning: { bg:'#fffbeb', border:'#fde68a', color:'#92400e' },
        danger:  { bg:'#fff5f5', border:'#fecaca', color:'#b91c1c' },
    };
    const c = colors[type] || colors.danger;
    el.style.cssText = `display:block; background:${c.bg}; border:1px solid ${c.border}; color:${c.color}; font-size:.82rem; padding:10px 14px; border-radius:7px; margin-top:8px;`;
    el.textContent = text;
}

// ── Process renewals ─────────────────────────────────────────────────────────

function processRenewals() {
    const ids   = getSelectedIds();
    const start = document.getElementById('m-start').value;
    const end   = document.getElementById('m-end').value;
    const rate  = document.getElementById('m-rate').value;
    const rType = document.getElementById('m-rate-type').value;
    const notes = document.getElementById('m-notes').value;

    if (!start || !end) {
        showMsg('danger', 'Contract dates are required.');
        return;
    }
    if (ids.length === 0) {
        showMsg('danger', 'No records selected.');
        return;
    }

    const btn = document.getElementById('processBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing…';

    fetch(PROCESS_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({
            ids, contract_start_date: start, contract_end_date: end,
            rate: rate || null, rate_type: rType || null, notes: notes || null,
            updates: getFieldUpdates(ids),
        })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Process Renewals';
        closeModal('renewalModal');
        showResultModal(data, start, end);
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Process Renewals';
        showMsg('danger', 'Network error. Please try again.');
    });
}

function showResultModal(data, start, end) {
    const body = document.getElementById('resultModalBody');
    let html = `<div style="display:flex; gap:12px; margin-bottom:16px;">`;

    if (data.succeeded > 0) {
        html += `<div class="br-result-card br-result-success" style="flex:1;">
            <div class="br-result-num" style="color:#059669;">${data.succeeded}</div>
            <div class="br-result-label">Records Successfully Renewed</div>
            <div style="font-size:.75rem; color:#047857; margin-top:4px;">
                ${start} → ${end}
            </div>
        </div>`;
    }

    if (data.failed && data.failed.length > 0) {
        html += `<div class="br-result-card br-result-fail" style="flex:1;">
            <div class="br-result-num" style="color:#dc2626;">${data.failed.length}</div>
            <div class="br-result-label">Records Failed / Skipped</div>
        </div>`;
    }

    html += `</div>`;

    if (data.failed && data.failed.length > 0) {
        html += `<div style="font-size:.8rem; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.05em; margin-bottom:8px;">
            Exception Report
        </div>
        <div style="border:1px solid #e5e7eb; border-radius:8px; overflow:hidden;">`;
        data.failed.forEach(f => {
            html += `<div style="padding:8px 14px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:10px; font-size:.8rem;">
                <i class="bi bi-x-circle-fill" style="color:#dc2626;"></i>
                <span style="flex:1; font-weight:600;">${f.name}</span>
                <span style="color:#dc2626;">${f.reason}</span>
            </div>`;
        });
        html += `</div>`;
    }

    if (data.status === 'error') {
        html = `<div style="background:#fff5f5; border:1px solid #fecaca; border-radius:8px; padding:14px; color:#b91c1c; font-size:.88rem;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>${data.message}
        </div>`;
        if (data.failed && data.failed.length > 0) {
            html += `<div style="margin-top:12px; font-size:.8rem; font-weight:700; color:#64748b;">Failures:</div>
            <div style="border:1px solid #e5e7eb; border-radius:8px; overflow:hidden; margin-top:6px;">`;
            data.failed.forEach(f => {
                html += `<div style="padding:8px 14px; border-bottom:1px solid #f1f5f9; display:flex; gap:10px; font-size:.8rem;">
                    <i class="bi bi-x-circle-fill" style="color:#dc2626;"></i>
                    <span style="flex:1; font-weight:600;">${f.name}</span>
                    <span style="color:#dc2626;">${f.reason}</span>
                </div>`;
            });
            html += `</div>`;
        }
    }

    body.innerHTML = html;
    document.getElementById('resultModal').classList.add('show');
}
</script>
</x-dashboard-app>
