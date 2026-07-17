{{--
    Form partial for Permanent/CT/Elected create.
    Variables expected:
      $offices (collection)
      $positions (collection)
--}}

<style>
    .perm-form-card {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
        padding: 28px; box-shadow: 0 1px 6px rgba(0,0,0,.05);
        margin-bottom: 20px;
    }
    .perm-section-title {
        font-size: 11px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase;
        color: #94a3b8; margin-bottom: 14px; padding-bottom: 8px;
        border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; gap: 7px;
    }
    .perm-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 14px; margin-bottom: 22px;
    }
    .perm-grid.cols-3 { grid-template-columns: repeat(3, 1fr); }
    .perm-group { display: flex; flex-direction: column; gap: 5px; }
    .perm-group label {
        font-size: 11px; font-weight: 700; color: #475569;
        text-transform: uppercase; letter-spacing: .5px;
    }
    .perm-group input[type="text"],
    .perm-group input[type="number"],
    .perm-group input[type="date"],
    .perm-group select,
    .perm-group textarea {
        height: 38px; border: 1.5px solid #e2e8f0; border-radius: 9px;
        padding: 0 12px; font-size: 13px; color: #0f172a; background: #f8fafc;
        outline: none; transition: border .15s, box-shadow .15s; width: 100%;
        font-family: 'Inter', sans-serif;
    }
    .perm-group textarea { height: 72px; padding: 10px 12px; resize: vertical; }
    .perm-group input:focus, .perm-group select:focus, .perm-group textarea:focus {
        border-color: #3b82f6; background: #fff;
        box-shadow: 0 0 0 3px rgba(59,130,246,.12);
    }
    .perm-group .input-error { border-color: #ef4444 !important; }
    .perm-group .field-error { font-size: 11px; color: #dc2626; margin-top: 2px; }

    .perm-checkbox-row {
        display: flex; flex-wrap: wrap; gap: 18px; margin-bottom: 22px; align-items: center;
    }
    .perm-checkbox-item {
        display: flex; align-items: center; gap: 7px;
        font-size: 13px; font-weight: 600; color: #334155; cursor: pointer;
    }
    .perm-checkbox-item input[type="checkbox"] {
        width: 17px; height: 17px; accent-color: #1e40af; cursor: pointer;
    }

    .perm-form-actions {
        display: flex; gap: 10px; justify-content: flex-end;
        padding-top: 16px; border-top: 1px solid #f1f5f9;
    }
    .btn-save-perm {
        padding: 10px 28px;
        background: linear-gradient(135deg, #1e40af, #1e3a8a);
        color: #fff; border: none; border-radius: 10px; font-size: 14px; font-weight: 700;
        cursor: pointer; transition: all .15s; display: flex; align-items: center; gap: 7px;
    }
    .btn-save-perm:hover { opacity: .9; transform: translateY(-1px); }
    .btn-cancel-perm {
        padding: 10px 20px; background: #f1f5f9; color: #475569; border: 1.5px solid #e2e8f0;
        border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer;
        transition: all .15s; text-decoration: none; display: inline-flex; align-items: center; gap: 7px;
    }
    .btn-cancel-perm:hover { background: #e2e8f0; color: #0f172a; }

    .vacant-notice {
        background: #fff7ed; border: 1px solid #fed7aa; border-radius: 10px;
        padding: 10px 16px; font-size: 12px; color: #c2410c;
        font-weight: 600; display: flex; align-items: center; gap: 8px;
        margin-bottom: 14px;
    }
</style>

<form method="POST" action="{{ route('permanent.store') }}" id="perm-record-form">
    @csrf

    {{-- ── SECTION 1: Identification ── --}}
    <div class="perm-form-card">
        <div class="perm-section-title"><i class="bi bi-person-badge"></i> Identification</div>

        <div class="perm-grid" style="margin-bottom:14px;">
            <div class="perm-group">
                <label>Employment Status <span style="color:#ef4444;">*</span></label>
                <select name="employment_status" id="f-status" required>
                    <option value="P" {{ old('employment_status', 'P') === 'P' ? 'selected' : '' }}>Permanent (P)</option>
                    <option value="CT" {{ old('employment_status') === 'CT' ? 'selected' : '' }}>Co-Terminous (CT)</option>
                    <option value="E" {{ old('employment_status') === 'E' ? 'selected' : '' }}>Elected (E)</option>
                </select>
            </div>
        </div>

        <div class="perm-checkbox-row" style="margin-bottom:14px;">
            <label class="perm-checkbox-item">
                <input type="hidden" name="is_vacant" value="0">
                <input type="checkbox" name="is_vacant" id="f-is-vacant" value="1"
                       {{ old('is_vacant') ? 'checked' : '' }}
                       onchange="toggleVacant(this.checked)">
                Position is <strong>VACANT</strong>
            </label>
        </div>

        <div id="vacant-notice" class="vacant-notice" style="display:none;">
            <i class="bi bi-info-circle-fill"></i> Name fields are hidden because this position is vacant.
        </div>

        <div id="name-fields">
            <div class="perm-grid">
                <div class="perm-group">
                    <label>LAST NAME</label>
                    <input type="text" name="last_name" id="f-last-name"
                           value="{{ old('last_name') }}"
                           placeholder="DELA CRUZ"
                           class="{{ $errors->has('last_name') ? 'input-error' : '' }}">
                    @error('last_name')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="perm-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" id="f-first-name"
                           value="{{ old('first_name') }}"
                           placeholder="Juan">
                    @error('first_name')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="perm-group">
                    <label>MIDDLE NAME</label>
                    <input type="text" name="middle_name" id="f-mi"
                           value="{{ old('middle_name') }}"
                           placeholder="Santos">
                </div>
                <div class="perm-group">
                    <label>SUFFIX</label>
                    <input type="text" name="name_extension" id="f-ext"
                           value="{{ old('name_extension') }}"
                           placeholder="Jr. / Sr. / III">
                </div>
                <div class="perm-group">
                    <label>SEX</label>
                    <select name="sex" id="f-sex">
                        <option value="">— Select —</option>
                        <option value="M" {{ old('sex') === 'M' ? 'selected' : '' }}>Male (M)</option>
                        <option value="F" {{ old('sex') === 'F' ? 'selected' : '' }}>Female (F)</option>
                    </select>
                </div>
                <div class="perm-group">
                    <label>CIVIL STATUS</label>
                    <select name="civil_status" id="f-civil-status">
                        <option value="">— Select —</option>
                        @foreach(['SINGLE','MARRIED','WIDOW','WIDOWER','SEPARATED','ANNULLED'] as $cs)
                            <option value="{{ $cs }}" {{ old('civil_status') === $cs ? 'selected' : '' }}>{{ $cs }}</option>
                        @endforeach
                    </select>
                    @error('civil_status')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="perm-group">
                    <label>DATE OF BIRTH</label>
                    <input type="date" name="date_of_birth" id="f-date_of_birth"
                           value="{{ old('date_of_birth') }}">
                </div>
                <div class="perm-group" style="grid-column:span 2;">
                    <label style="display:flex;align-items:center;gap:5px;">
                        Employee Code
                        <span style="font-size:10px;color:#3b82f6;font-weight:600;text-transform:none;">(auto-generated)</span>
                    </label>
                    <div style="display:flex;gap:6px;">
                        <input type="text" name="employee_code" id="f-employee-code"
                               value="{{ old('employee_code') }}"
                               placeholder="e.g. 23042004A" maxlength="20"
                               style="font-family:monospace;text-transform:uppercase;letter-spacing:1px;">
                        <button type="button" id="btn-autofill-perm"
                                style="padding:0 12px;background:#dbeafe;border:1.5px solid #93c5fd;color:#1e40af;border-radius:9px;font-size:11px;font-weight:700;cursor:pointer;white-space:nowrap;display:flex;align-items:center;gap:4px;"
                                title="Auto-generate from Last Name + DATE OF BIRTH">
                            <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Auto-fill
                        </button>
                    </div>
                    <span style="font-size:10px;color:#94a3b8;margin-top:2px;">Format: DDMMYYYY + first letter of last name</span>
                    @error('employee_code')<span class="field-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>
    </div>

    {{-- ── SECTION 2: Position & Office ── --}}
    <div class="perm-form-card">
        <div class="perm-section-title"><i class="bi bi-briefcase"></i> Position & Office</div>
        <div class="perm-grid">
            <div class="perm-group" style="grid-column:span 3;">
                <label>OFFICE</label>
                <select name="office_department" id="f-office" class="tom-select-tags">
                    <option value="">— Select or Type Office —</option>
                    @php $oldOff = old('office_department', ''); @endphp
                    @foreach($offices as $o)
                        <option value="{{ $o }}" {{ $oldOff === $o ? 'selected' : '' }}>{{ $o }}</option>
                    @endforeach
                    @if($oldOff && !$offices->contains($oldOff))
                        <option value="{{ $oldOff }}" selected>{{ $oldOff }}</option>
                    @endif
                </select>
            </div>
            <div class="perm-group" style="grid-column:span 3;">
                <label>Position Title <span style="color:#ef4444;">*</span></label>
                <select name="position_title" id="f-position" class="tom-select-tags" required>
                    <option value="">— Select or Type Position —</option>
                    @php $oldPos = old('position_title', ''); @endphp
                    @foreach($positions as $p)
                        <option value="{{ $p }}" {{ $oldPos === $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                    @if($oldPos && !$positions->contains($oldPos))
                        <option value="{{ $oldPos }}" selected>{{ $oldPos }}</option>
                    @endif
                </select>
                @error('position_title')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="perm-group">
                <label>Item No. (Old)</label>
                <input type="text" name="item_no_old" id="f-item-old" value="{{ old('item_no_old') }}" placeholder="1">
            </div>
            <div class="perm-group">
                <label>Item No. (New)</label>
                <input type="text" name="item_no_new" id="f-item-new" value="{{ old('item_no_new') }}" placeholder="1">
            </div>
            <div class="perm-group">
                <label>Legislative District</label>
                <input type="text" name="legislative_district" id="f-district" value="{{ old('legislative_district') }}" placeholder="e.g. 1st District">
            </div>
            <div class="perm-group">
                <label>First Day of Service</label>
                <input type="date" name="first_day_of_service" id="f-fds" value="{{ old('first_day_of_service') }}">
            </div>
        </div>
    </div>

    {{-- ── SECTION 3: Salary ── --}}
    <div class="perm-form-card">
        <div class="perm-section-title"><i class="bi bi-cash-coin"></i> Salary Information</div>

        <p style="font-size:12px;color:#64748b;margin-bottom:12px;">
            <i class="bi bi-info-circle"></i>
            <strong>Current Year</strong> = Rate/Annum for this year &nbsp;|&nbsp;
            <strong>Proposed</strong> = Budget Year proposed rate
        </p>

        <div class="perm-grid cols-3">
            <div class="perm-group">
                <label>SG – Current Year</label>
                <input type="number" name="salary_grade" id="f-sg-cur" min="1" max="33" value="{{ old('salary_grade') }}" placeholder="e.g. 1">
            </div>
            <div class="perm-group">
                <label>Step – Current Year</label>
                <input type="number" name="step" id="f-step-cur" min="1" max="8" value="{{ old('step') }}" placeholder="e.g. 1">
            </div>
            <div class="perm-group">
                <label>Annual Salary – Current (₱)</label>
                <input type="number" name="authorized_annual_salary" id="f-sal-cur" step="0.01" min="0" value="{{ old('authorized_annual_salary') }}" placeholder="0.00">
            </div>

            <div class="perm-group">
                <label>SG – Proposed</label>
                <input type="number" name="sg_proposed" id="f-sg-prop" min="1" max="33" value="{{ old('sg_proposed') }}" placeholder="e.g. 1">
            </div>
            <div class="perm-group">
                <label>Step – Proposed</label>
                <input type="number" name="step_proposed" id="f-step-prop" min="1" max="8" value="{{ old('step_proposed') }}" placeholder="e.g. 1">
            </div>
            <div class="perm-group">
                <label>Annual Salary – Proposed (₱)</label>
                <input type="text" inputmode="decimal" name="salary_proposed" id="f-sal-prop" value="{{ old('salary_proposed') }}" placeholder="0.00">
            </div>

            <div class="perm-group">
                <label>Increase / Decrease (₱)</label>
                <input type="text" inputmode="decimal" name="increase_decrease" id="f-inc-dec" value="{{ old('increase_decrease') }}" placeholder="0.00">
            </div>
            <div class="perm-group">
                <label>Previous Monthly Rate (₱)</label>
                <input type="text" inputmode="decimal" name="previous_rate" id="f-prev-rate" value="{{ old('previous_rate') }}" placeholder="0.00">
            </div>
            <div class="perm-group">
                <label>Current Monthly Rate (₱)</label>
                <input type="text" inputmode="decimal" name="base_salary_amount" id="f-cur-rate" value="{{ old('base_salary_amount') }}" placeholder="0.00">
            </div>
        </div>
    </div>

    {{-- ── SECTION 4: Eligibility & Other ── --}}
    <div class="perm-form-card">
        <div class="perm-section-title"><i class="bi bi-award"></i> Eligibility & Other Information</div>
        <div class="perm-grid">
            <div class="perm-group" style="grid-column:span 2;">
                <label>Civil Service Eligibility</label>
                <input type="text" name="civil_service_eligibility" id="f-elig" value="{{ old('civil_service_eligibility') }}" placeholder="e.g. CS Professional">
            </div>
            <div class="perm-group" style="grid-column:span 2;">
                <label>IP Community Membership</label>
                <input type="text" name="ip_community_membership" id="f-ip" value="{{ old('ip_community_membership') }}" placeholder="Name of IP Community (if applicable)">
            </div>
            <div class="perm-group" style="grid-column:span 2;">
                <label>Address</label>
                <input type="text" name="address" id="f-address" value="{{ old('address') }}" placeholder="Complete address">
            </div>
        </div>

        <div class="perm-checkbox-row">
            <label class="perm-checkbox-item">
                <input type="hidden" name="solo_parent" value="0">
                <input type="checkbox" name="solo_parent" id="f-sp" value="1" {{ old('solo_parent') ? 'checked' : '' }}>
                Solo Parent
            </label>
        </div>

        <div class="perm-grid">
            <div class="perm-group" style="grid-column:span 4;">
                <label>Annotation</label>
                <textarea name="remarks_annotation" id="f-annotation" placeholder="Add annotation...">{{ old('remarks_annotation') }}</textarea>
            </div>
        </div>

        {{-- ── ACTIONS ── --}}
        <div class="perm-form-actions">
            <a href="{{ route('permanent.index') }}" class="btn-cancel-perm">
                <i class="bi bi-arrow-left"></i> Cancel
            </a>
            <button type="submit" class="btn-save-perm" id="btn-perm-save">
                <i class="bi bi-check-lg"></i> Save Record
            </button>
        </div>
    </div>
</form>

<script>
function toggleVacant(isVacant) {
    document.getElementById('name-fields').style.display = isVacant ? 'none' : 'block';
    document.getElementById('vacant-notice').style.display = isVacant ? 'flex' : 'none';
}
document.addEventListener('DOMContentLoaded', function () {
    toggleVacant(document.getElementById('f-is-vacant').checked);

    const lastNameEl = document.getElementById('f-last-name');
    const dobEl      = document.getElementById('f-date_of_birth');
    const codeEl     = document.getElementById('f-employee-code');
    const btnEl      = document.getElementById('btn-autofill-perm');

    function buildCode() {
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
        } catch (e) { return ''; }
    }

    function autofillCode() {
        const code = buildCode();
        if (code && codeEl) codeEl.value = code;
    }

    if (lastNameEl) lastNameEl.addEventListener('change', function () { if (!codeEl.value) autofillCode(); });
    if (dobEl)      dobEl.addEventListener('change',      function () { if (!codeEl.value) autofillCode(); });
    if (btnEl)      btnEl.addEventListener('click', autofillCode);
});
</script>
