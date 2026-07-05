{{--
    Shared form partial for Job Order create/edit.
    Variables expected:
      $jo (JobOrder|null)  — null when creating
      $offices, $chargesList, $natures (collections)
      $formAction (string)
      $formMethod ('POST' | 'PUT')
--}}

<style>
    .jo-form-card {
        background:#fff; border:1px solid #e2e8f0; border-radius:14px;
        padding:28px 28px; box-shadow:0 1px 6px rgba(0,0,0,.05);
    }
    .jo-form-section-title {
        font-size:11px; font-weight:800; letter-spacing:1px; text-transform:uppercase;
        color:#94a3b8; margin-bottom:14px; padding-bottom:8px;
        border-bottom:1px solid #f1f5f9;
    }
    .jo-form-grid {
        display:grid;
        grid-template-columns:repeat(auto-fill, minmax(200px, 1fr));
        gap:14px;
        margin-bottom:22px;
    }
    .jo-form-grid.cols-2 { grid-template-columns:repeat(2, 1fr); }
    .jo-form-group { display:flex; flex-direction:column; gap:5px; }
    .jo-form-group label {
        font-size:11px; font-weight:700; color:#475569;
        text-transform:uppercase; letter-spacing:.5px;
    }
    .jo-form-group input[type="text"],
    .jo-form-group input[type="number"],
    .jo-form-group input[type="date"],
    .jo-form-group select,
    .jo-form-group textarea {
        height:38px; border:1.5px solid #e2e8f0; border-radius:9px;
        padding:0 12px; font-size:13px; color:#0f172a; background:#f8fafc;
        outline:none; transition:border .15s, box-shadow .15s; width:100%;
        font-family:'Inter',sans-serif;
    }
    .jo-form-group textarea { height:72px; padding:10px 12px; resize:vertical; }
    .jo-form-group input:focus,
    .jo-form-group select:focus,
    .jo-form-group textarea:focus {
        border-color:#3b82f6; background:#fff;
        box-shadow:0 0 0 3px rgba(59,130,246,.12);
    }
    .jo-form-group .input-error { border-color:#ef4444 !important; }
    .jo-form-group .field-error { font-size:11px; color:#dc2626; margin-top:2px; }

    /* Checkbox row */
    .jo-checkbox-row {
        display:flex; flex-wrap:wrap; gap:18px; margin-bottom:22px; align-items:center;
    }
    .jo-checkbox-item {
        display:flex; align-items:center; gap:7px;
        font-size:13px; font-weight:600; color:#334155; cursor:pointer;
    }
    .jo-checkbox-item input[type="checkbox"] {
        width:17px; height:17px; accent-color:#3b82f6; cursor:pointer;
    }

    .jo-form-actions {
        display:flex; gap:10px; justify-content:flex-end;
        padding-top:16px; border-top:1px solid #f1f5f9;
    }
    .btn-save {
        padding:10px 28px; background:linear-gradient(135deg,#3b82f6,#2563eb);
        color:#fff; border:none; border-radius:10px; font-size:14px; font-weight:700;
        cursor:pointer; transition:all .15s; display:flex; align-items:center; gap:7px;
    }
    .btn-save:hover { background:linear-gradient(135deg,#2563eb,#1d4ed8); transform:translateY(-1px); }
    .btn-cancel {
        padding:10px 20px; background:#f1f5f9; color:#475569; border:1.5px solid #e2e8f0;
        border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;
        transition:all .15s; text-decoration:none; display:inline-flex; align-items:center; gap:7px;
    }
    .btn-cancel:hover { background:#e2e8f0; color:#0f172a; }
</style>

<form method="POST" action="{{ $formAction }}" id="jo-record-form">
    @csrf
    @if($formMethod === 'PUT') @method('PUT') @endif

    <div class="jo-form-card">

        {{-- ── SECTION 1: Identification ── --}}
        <div class="jo-form-section-title"><i class="bi bi-person-badge"></i> Identification</div>
        <div class="jo-form-grid">
            <div class="jo-form-group">
                <label>OFFICE</label>
                <select name="office_department" id="f-office_department" class="tom-select-tags">
                    <option value="">— Select or Type Office —</option>
                    @php $oldCharge = old('office_department', $jo->office_department ?? ''); @endphp
                    @foreach($chargesList as $c)
                        <option value="{{ $c }}" {{ $oldCharge === $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                    @if($oldCharge && !$chargesList->contains($oldCharge))
                        <option value="{{ $oldCharge }}" selected>{{ $oldCharge }}</option>
                    @endif
                </select>
                @error('office_department')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="jo-form-group">
                <label>LAST NAME<span style="color:#ef4444;">*</span></label>
                <input type="text" name="last_name" id="f-last-name" value="{{ old('last_name', $jo->last_name ?? '') }}"
                       placeholder="DELA CRUZ" required class="{{ $errors->has('last_name') ? 'input-error' : '' }}">
                @error('last_name')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="jo-form-group">
                <label>First Name <span style="color:#ef4444;">*</span></label>
                <input type="text" name="first_name" id="f-first-name" value="{{ old('first_name', $jo->first_name ?? '') }}"
                       placeholder="Juan" required class="{{ $errors->has('first_name') ? 'input-error' : '' }}">
                @error('first_name')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="jo-form-group">
                <label>MIDDLE NAME</label>
                <input type="text" name="middle_name" id="f-mi" value="{{ old('middle_name', $jo->middle_name ?? '') }}"
                       placeholder="Santos">
                @error('middle_name')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="jo-form-group">
                <label>SUFFIX</label>
                <input type="text" name="name_extension" id="f-ext" value="{{ old('name_extension', $jo->name_extension ?? '') }}"
                       placeholder="Jr. / Sr. / III">
                @error('name_extension')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="jo-form-group">
                <label>SEX</label>
                <select name="sex" id="f-sex">
                    <option value="">— Select —</option>
                    <option value="M" {{ old('sex', $jo->sex ?? '') === 'M' ? 'selected' : '' }}>Male (M)</option>
                    <option value="F" {{ old('sex', $jo->sex ?? '') === 'F' ? 'selected' : '' }}>Female (F)</option>
                </select>
                @error('sex')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="jo-form-group">
                <label>Status (Civil Status)</label>
                <select name="civil_status" id="f-civil-status">
                    <option value="">— Select —</option>
                    @foreach(['SINGLE','MARRIED','WIDOW','WIDOWER','SEPARATED','ANNULLED'] as $cs)
                        <option value="{{ $cs }}" {{ old('civil_status', $jo->civil_status ?? '') === $cs ? 'selected' : '' }}>{{ $cs }}</option>
                    @endforeach
                </select>
                @error('civil_status')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="jo-form-group">
                <label>DATE OF BIRTH</label>
                <input type="date" name="date_of_birth" id="f-date_of_birth"
                       value="{{ old('date_of_birth', isset($jo->date_of_birth) ? $jo->date_of_birth->format('Y-m-d') : '') }}">
                @error('date_of_birth')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="jo-form-group" style="grid-column:span 2;">
                <label style="display:flex;align-items:center;gap:5px;">
                    Employee Code
                    <span style="font-size:10px;color:#3b82f6;font-weight:600;text-transform:none;">(auto-generated)</span>
                </label>
                <div style="display:flex;gap:6px;">
                    <input type="text" name="employee_code" id="f-employee-code"
                           value="{{ old('employee_code', $jo->employee_code ?? '') }}"
                           placeholder="e.g. 23042004A" maxlength="20"
                           style="font-family:monospace;text-transform:uppercase;letter-spacing:1px;">
                    <button type="button" id="btn-autofill-jo"
                            style="padding:0 12px;background:#eff6ff;border:1.5px solid #93c5fd;color:#1d4ed8;border-radius:9px;font-size:11px;font-weight:700;cursor:pointer;white-space:nowrap;display:flex;align-items:center;gap:4px;"
                            title="Auto-generate from Last Name + DATE OF BIRTH">
                        <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Auto-fill
                    </button>
                </div>
                <span style="font-size:10px;color:#94a3b8;margin-top:2px;">Format: DDMMYYYY + first letter of last name</span>
                @error('employee_code')<span class="field-error">{{ $message }}</span>@enderror
            </div>
        </div>

        {{-- ── SECTION 2: Position & Service ── --}}
        <div class="jo-form-section-title"><i class="bi bi-briefcase"></i> Position & Service</div>
        <div class="jo-form-grid">
            <div class="jo-form-group" style="grid-column:span 2;">
                <label>Position Title <span style="color:#ef4444;">*</span></label>
                <select name="position_title" id="f-position" class="tom-select-tags" required>
                    <option value="">— Select or Type Position —</option>
                    @php $oldPos = old('position_title', $jo->position_title ?? ''); @endphp
                    @foreach($positions as $p)
                        <option value="{{ $p }}" {{ $oldPos === $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                    @if($oldPos && !$positions->contains($oldPos))
                        <option value="{{ $oldPos }}" selected>{{ $oldPos }}</option>
                    @endif
                </select>
                @error('position_title')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div class="jo-form-group" style="grid-column:span 2;">
                <label>Nature of Work (Specific) <span style="font-size:10px;color:#64748b;font-weight:400;text-transform:none;">(e.g. CLERICAL SERVICES, JANITORIAL SERVICES, OTHERS…)</span></label>
                <select name="nature_of_work_detail" id="f-nature-detail" class="tom-select-tags">
                    <option value="">— Select or Type Nature of Work —</option>
                    @php $oldNat = old('nature_of_work_detail', $jo->nature_of_work_detail ?? ''); @endphp
                    @foreach($detailList as $nd)
                        <option value="{{ $nd }}" {{ $oldNat === $nd ? 'selected' : '' }}>{{ $nd }}</option>
                    @endforeach
                    @if($oldNat && !$detailList->contains($oldNat))
                        <option value="{{ $oldNat }}" selected>{{ $oldNat }}</option>
                    @endif
                </select>
                @error('nature_of_work_detail')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="jo-form-group">
                <label>Level</label>
                <select name="level" id="f-level">
                    <option value="">— Select —</option>
                    @foreach(['M1','F1','M2','F2'] as $lv)
                        <option value="{{ $lv }}" {{ old('level', $jo->level ?? '') === $lv ? 'selected' : '' }}>{{ $lv }}</option>
                    @endforeach
                </select>
                @error('level')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="jo-form-group">
                <label>DETAILED UNIT</label>
                <select name="detailed_unit" id="f-office" class="tom-select-tags">
                    <option value="">— Select or Type Unit —</option>
                    @php $oldOff = old('detailed_unit', $jo->detailed_unit ?? ''); @endphp
                    @foreach($offices as $o)
                        <option value="{{ $o }}" {{ $oldOff === $o ? 'selected' : '' }}>{{ $o }}</option>
                    @endforeach
                    @if($oldOff && !$offices->contains($oldOff))
                        <option value="{{ $oldOff }}" selected>{{ $oldOff }}</option>
                    @endif
                </select>
                @error('detailed_unit')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="jo-form-group">
                <label>Rate Per Day (₱)</label>
                <input type="text" inputmode="decimal" name="rate_per_day" id="f-rate"
                       value="{{ old('rate_per_day', $jo->rate_per_day ?? '') }}" placeholder="0.00" class="peso-input">
                @error('rate_per_day')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="jo-form-group">
                <label>First Day of Service</label>
                <input type="date" name="first_day_of_service" id="f-fds"
                       value="{{ old('first_day_of_service', isset($jo->first_day_of_service) ? $jo->first_day_of_service->format('Y-m-d') : '') }}">
                @error('first_day_of_service')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="jo-form-group" style="justify-content: center;">
                <label class="jo-checkbox-item" style="margin-top: 18px;">
                    <input type="hidden" name="reemployment" value="0">
                    <input type="checkbox" name="reemployment" id="f-reemp" value="1"
                           {{ old('reemployment', $jo->reemployment ?? false) ? 'checked' : '' }}>
                    Reemployment
                </label>
            </div>
        </div>

        {{-- ── SECTION 3: Eligibility ── --}}
        <div class="jo-form-section-title"><i class="bi bi-award"></i> Eligibility & Classification</div>
        <div class="jo-form-grid">
            <div class="jo-form-group" style="grid-column:span 2;">
                <label>Civil Service Eligibility</label>
                @php
                    $oldEligibility = old('eligibility', $jo->eligibility ?? '');
                    $cseOptions = ['CS Professional', 'CS Sub-Professional', 'No Eligibility', 'RA 1080 (Physician)', 'RA 1080 (Dentist)', 'RA 1080 (Pharmacist)', 'RA 1080 (Nurse)', 'PD 907 (Veteran)', 'Career Service Executive Eligibility (CSEE)'];
                    $isOtherElig = $oldEligibility && !in_array($oldEligibility, $cseOptions) && $oldEligibility !== '';
                @endphp
                <select id="f-eligibility-select" onchange="handleEligibilityChange(this)">
                    <option value="">— None / Not Applicable —</option>
                    @foreach($cseOptions as $opt)
                        <option value="{{ $opt }}" {{ $oldEligibility === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                    @endforeach
                    <option value="Others" {{ $isOtherElig ? 'selected' : '' }}>Others (Please specify)</option>
                </select>
                <input type="text" name="eligibility" id="f-eligibility"
                       value="{{ $oldEligibility }}"
                       placeholder="Specify eligibility..."
                       style="margin-top:6px; {{ $isOtherElig ? '' : 'display:none;' }}">
                @error('eligibility')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="jo-form-group" style="grid-column:span 2;">
                <label>IP Community Membership</label>
                <input type="text" name="ip_community_membership" id="f-ip" value="{{ old('ip_community_membership', $jo->ip_community_membership ?? '') }}"
                       placeholder="Name of IP Community (if applicable)">
                @error('ip_community_membership')<span class="field-error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="jo-checkbox-row">
            <label class="jo-checkbox-item">
                <input type="hidden" name="first_level_eligibility" value="0">
                <input type="checkbox" name="first_level_eligibility" id="f-lv1" value="1"
                       {{ old('first_level_eligibility', $jo->first_level_eligibility ?? false) ? 'checked' : '' }}>
                1st Level Eligibility
            </label>
            <label class="jo-checkbox-item">
                <input type="hidden" name="second_level_eligibility" value="0">
                <input type="checkbox" name="second_level_eligibility" id="f-lv2" value="1"
                       {{ old('second_level_eligibility', $jo->second_level_eligibility ?? false) ? 'checked' : '' }}>
                2nd Level Eligibility
            </label>
            <label class="jo-checkbox-item">
                <input type="hidden" name="solo_parent" value="0">
                <input type="checkbox" name="solo_parent" id="f-sp" value="1"
                       {{ old('solo_parent', $jo->solo_parent ?? false) ? 'checked' : '' }}>
                Solo Parent
            </label>
        </div>

        {{-- ── SECTION 4: Address & Remarks ── --}}
        <div class="jo-form-section-title"><i class="bi bi-geo-alt"></i> Address & Remarks</div>
        <div class="jo-form-grid cols-2">
            <div class="jo-form-group" style="grid-column:span 2;">
                <label>Address</label>
                <textarea name="address" id="f-address" placeholder="Complete address...">{{ old('address', $jo->address ?? '') }}</textarea>
                @error('address')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="jo-form-group" style="grid-column:span 2;">
                <label>Remarks</label>
                <textarea name="remarks" id="f-remarks" placeholder="Additional notes...">{{ old('remarks', $jo->remarks ?? '') }}</textarea>
                @error('remarks')<span class="field-error">{{ $message }}</span>@enderror
            </div>
        </div>

        {{-- ── SEPARATION ── --}}
        <div class="jo-form-section-title" style="color:#7c3aed;"><i class="bi bi-box-arrow-right"></i> Appointment History & Separation</div>
        <div class="jo-form-grid cols-2">
            <div class="jo-form-group">
                <label>Nature of Separation</label>
                <select name="nature_of_separation" id="f-nature-sep">
                    <option value="">— None / Still Active —</option>
                    @foreach(['End of Contract','Resigned','Transferred','Terminated','Dropped from Rolls','Dismissed','Death'] as $opt)
                        <option value="{{ $opt }}" {{ old('nature_of_separation', $jo->nature_of_separation ?? '') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                    @endforeach
                </select>
                @error('nature_of_separation')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="jo-form-group">
                <label>Date Separated / Effectivity</label>
                <input type="date" name="date_separated" id="f-date-sep"
                       value="{{ old('date_separated', isset($jo->date_separated) ? \Carbon\Carbon::parse($jo->date_separated)->format('Y-m-d') : '') }}">
                @error('date_separated')<span class="field-error">{{ $message }}</span>@enderror
            </div>
        </div>

        {{-- ── ACTIONS ── --}}
        <div class="jo-form-actions">
            <a href="{{ route('job-orders.index') }}" class="btn-cancel">
                <i class="bi bi-arrow-left"></i> Cancel
            </a>
            <button type="submit" class="btn-save" id="btn-jo-save">
                <i class="bi bi-check-lg"></i> Save Record
            </button>
        </div>

    </div>{{-- end .jo-form-card --}}
</form>

<script>
function handleEligibilityChange(sel) {
    const input = document.getElementById('f-eligibility');
    if (sel.value === 'Others') {
        input.style.display = '';
        input.value = '';
        input.focus();
    } else {
        input.style.display = 'none';
        input.value = sel.value;
    }
}
// On page load: if select is on Others, show input
document.addEventListener('DOMContentLoaded', function() {
    const sel = document.getElementById('f-eligibility-select');
    if (sel && sel.value === 'Others') {
        document.getElementById('f-eligibility').style.display = '';
    } else if (sel && sel.value !== '') {
        const inp = document.getElementById('f-eligibility');
        if (inp && !inp.value) inp.value = sel.value;
    }

    // ── Employee Code Auto-fill ────────────────────────────────────────
    const lastNameEl = document.getElementById('f-last-name');
    const dobEl      = document.getElementById('f-date_of_birth');
    const codeEl     = document.getElementById('f-employee-code');
    const btnEl      = document.getElementById('btn-autofill-jo');

    function buildJoCode() {
        const lastName = (lastNameEl?.value || '').trim();
        const dob = dobEl?.value || '';
        if (!lastName || !dob) return '';
        try {
            const d = new Date(dob);
            if (isNaN(d)) return '';
            const dd   = String(d.getDate()).padStart(2, '0');
            const mm   = String(d.getMonth() + 1).padStart(2, '0');
            const yyyy = d.getFullYear();
            return dd + mm + yyyy + lastName.charAt(0).toUpperCase();
        } catch(e) { return ''; }
    }

    function autofillJoCode() {
        const code = buildJoCode();
        if (code && codeEl) codeEl.value = code;
    }

    if (lastNameEl) lastNameEl.addEventListener('change', function() { if (!codeEl.value) autofillJoCode(); });
    if (dobEl)      dobEl.addEventListener('change',      function() { if (!codeEl.value) autofillJoCode(); });
    if (btnEl)      btnEl.addEventListener('click', autofillJoCode);
});
</script>
