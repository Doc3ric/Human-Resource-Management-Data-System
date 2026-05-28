{{--
    Shared form partial for Casual create/edit.
    Variables expected:
      $casual (CasualEmployee|null)  — null when creating
      $offices (collection)
      $formAction (string)
      $formMethod ('POST' | 'PUT')
--}}

<style>
    .cas-form-card {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
        padding: 28px; box-shadow: 0 1px 6px rgba(0,0,0,.05);
        margin-bottom: 20px;
    }
    .cas-section-title {
        font-size: 11px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase;
        color: #94a3b8; margin-bottom: 14px; padding-bottom: 8px;
        border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; gap: 7px;
    }
    .cas-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 14px; margin-bottom: 22px;
    }
    .cas-grid.cols-2 { grid-template-columns: repeat(2, 1fr); }
    .cas-grid.cols-3 { grid-template-columns: repeat(3, 1fr); }
    .cas-group { display: flex; flex-direction: column; gap: 5px; }
    .cas-group label {
        font-size: 11px; font-weight: 700; color: #475569;
        text-transform: uppercase; letter-spacing: .5px;
    }
    .cas-group input[type="text"],
    .cas-group input[type="number"],
    .cas-group input[type="date"],
    .cas-group select,
    .cas-group textarea {
        height: 38px; border: 1.5px solid #e2e8f0; border-radius: 9px;
        padding: 0 12px; font-size: 13px; color: #0f172a; background: #f8fafc;
        outline: none; transition: border .15s, box-shadow .15s; width: 100%;
        font-family: 'Inter', sans-serif;
    }
    .cas-group textarea { height: 72px; padding: 10px 12px; resize: vertical; }
    .cas-group input:focus, .cas-group select:focus, .cas-group textarea:focus {
        border-color: #e879a0; background: #fff;
        box-shadow: 0 0 0 3px rgba(232, 121, 160, .12);
    }
    .cas-group .input-error { border-color: #ef4444 !important; }
    .cas-group .field-error { font-size: 11px; color: #dc2626; margin-top: 2px; }

    .cas-checkbox-row {
        display: flex; flex-wrap: wrap; gap: 18px; margin-bottom: 22px; align-items: center;
    }
    .cas-checkbox-item {
        display: flex; align-items: center; gap: 7px;
        font-size: 13px; font-weight: 600; color: #334155; cursor: pointer;
    }
    .cas-checkbox-item input[type="checkbox"] {
        width: 17px; height: 17px; accent-color: #e879a0; cursor: pointer;
    }

    .cas-form-actions {
        display: flex; gap: 10px; justify-content: flex-end;
        padding-top: 16px; border-top: 1px solid #f1f5f9;
    }
    .btn-save-cas {
        padding: 10px 28px;
        background: linear-gradient(135deg, #e879a0, #be185d);
        color: #fff; border: none; border-radius: 10px; font-size: 14px; font-weight: 700;
        cursor: pointer; transition: all .15s; display: flex; align-items: center; gap: 7px;
    }
    .btn-save-cas:hover { opacity: .9; transform: translateY(-1px); }
    .btn-cancel-cas {
        padding: 10px 20px; background: #f1f5f9; color: #475569; border: 1.5px solid #e2e8f0;
        border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer;
        transition: all .15s; text-decoration: none; display: inline-flex; align-items: center; gap: 7px;
    }
    .btn-cancel-cas:hover { background: #e2e8f0; color: #0f172a; }

    /* Vacant toggle */
    .vacant-notice {
        background: #fff7ed; border: 1px solid #fed7aa; border-radius: 10px;
        padding: 10px 16px; font-size: 12px; color: #c2410c;
        font-weight: 600; display: flex; align-items: center; gap: 8px;
        margin-bottom: 14px;
    }
</style>

<form method="POST" action="{{ $formAction }}" id="casual-record-form">
    @csrf
    @if($formMethod === 'PUT') @method('PUT') @endif

    {{-- ── SECTION 1: Identification ── --}}
    <div class="cas-form-card">
        <div class="cas-section-title"><i class="bi bi-person-badge"></i> Identification</div>

        {{-- Vacant toggle --}}
        <div class="cas-checkbox-row" style="margin-bottom:14px;">
            <label class="cas-checkbox-item">
                <input type="hidden" name="is_vacant" value="0">
                <input type="checkbox" name="is_vacant" id="f-is-vacant" value="1"
                       {{ old('is_vacant', $casual->is_vacant ?? false) ? 'checked' : '' }}
                       onchange="toggleVacant(this.checked)">
                Position is <strong>VACANT</strong>
            </label>
        </div>

        <div id="vacant-notice" class="vacant-notice" style="display:none;">
            <i class="bi bi-info-circle-fill"></i> Name fields are hidden because this position is vacant.
        </div>

        <div id="name-fields">
            <div class="cas-grid">
                <div class="cas-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" id="f-last-name"
                           value="{{ old('last_name', $casual->last_name ?? '') }}"
                           placeholder="DELA CRUZ"
                           class="{{ $errors->has('last_name') ? 'input-error' : '' }}">
                    @error('last_name')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="cas-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" id="f-first-name"
                           value="{{ old('first_name', $casual->first_name ?? '') }}"
                           placeholder="Juan">
                    @error('first_name')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="cas-group">
                    <label>Middle Initial</label>
                    <input type="text" name="middle_initial" id="f-mi"
                           value="{{ old('middle_initial', $casual->middle_initial ?? '') }}"
                           placeholder="A" maxlength="10">
                </div>
                <div class="cas-group">
                    <label>Name Extension</label>
                    <input type="text" name="name_extension" id="f-ext"
                           value="{{ old('name_extension', $casual->name_extension ?? '') }}"
                           placeholder="Jr. / Sr. / III">
                </div>
                <div class="cas-group">
                    <label>Gender</label>
                    <select name="gender" id="f-gender">
                        <option value="">— Select —</option>
                        <option value="M" {{ old('gender', $casual->gender ?? '') === 'M' ? 'selected' : '' }}>Male (M)</option>
                        <option value="F" {{ old('gender', $casual->gender ?? '') === 'F' ? 'selected' : '' }}>Female (F)</option>
                    </select>
                </div>
                <div class="cas-group">
                    <label>Birthdate</label>
                    <input type="date" name="birthdate" id="f-birthdate"
                           value="{{ old('birthdate', isset($casual->birthdate) ? $casual->birthdate->format('Y-m-d') : '') }}">
                </div>
                <div class="cas-group" style="grid-column:span 2;">
                    <label style="display:flex;align-items:center;gap:5px;">
                        Employee Code
                        <span style="font-size:10px;color:#e879a0;font-weight:600;text-transform:none;">(auto-generated)</span>
                    </label>
                    <div style="display:flex;gap:6px;">
                        <input type="text" name="employee_code" id="f-employee-code"
                               value="{{ old('employee_code', $casual->employee_code ?? '') }}"
                               placeholder="e.g. 23042004A" maxlength="20"
                               style="font-family:monospace;text-transform:uppercase;letter-spacing:1px;">
                        <button type="button" id="btn-autofill-casual"
                                style="padding:0 12px;background:#fce7f3;border:1.5px solid #f9a8d4;color:#be185d;border-radius:9px;font-size:11px;font-weight:700;cursor:pointer;white-space:nowrap;display:flex;align-items:center;gap:4px;"
                                title="Auto-generate from Last Name + Birthdate">
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
    <div class="cas-form-card">
        <div class="cas-section-title"><i class="bi bi-briefcase"></i> Position & Office</div>
        <div class="cas-grid">
            <div class="cas-group" style="grid-column:span 3;">
                <label>Office / Department</label>
                <div style="display:flex; gap:5px;">
                    <select id="f-office-select" style="flex:1;" onchange="
                        if(this.value==='Other'){
                            document.getElementById('f-office').style.display='block';
                            document.getElementById('f-office').value='';
                            document.getElementById('f-office').focus();
                        } else {
                            document.getElementById('f-office').style.display='none';
                            document.getElementById('f-office').value=this.value;
                        }
                    ">
                        <option value="">— Select —</option>
                        @php $oldOff = old('office', $casual->office ?? ''); @endphp
                        @foreach($offices as $o)
                            <option value="{{ $o }}" {{ $oldOff === $o ? 'selected' : '' }}>{{ $o }}</option>
                        @endforeach
                        <option value="Other" {{ $oldOff && !$offices->contains($oldOff) ? 'selected' : '' }}>Other (Please Specify)</option>
                    </select>
                    <input type="text" name="office" id="f-office"
                           value="{{ $oldOff }}"
                           placeholder="Specify office..."
                           style="flex:1; {{ $oldOff && !$offices->contains($oldOff) ? '' : 'display:none;' }}">
                </div>
            </div>
            <div class="cas-group" style="grid-column:span 2;">
                <label>Position Title <span style="color:#ef4444;">*</span></label>
                <div style="display:flex; gap:5px;">
                    <select id="f-position-select" style="flex:1;" onchange="
                        if(this.value==='Other'){
                            document.getElementById('f-position').style.display='block';
                            document.getElementById('f-position').value='';
                            document.getElementById('f-position').focus();
                        } else {
                            document.getElementById('f-position').style.display='none';
                            document.getElementById('f-position').value=this.value;
                        }
                    ">
                        <option value="">— Select —</option>
                        @php $oldPos = old('position_title', $casual->position_title ?? ''); @endphp
                        @foreach($positions as $p)
                            <option value="{{ $p }}" {{ $oldPos === $p ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                        <option value="Other" {{ $oldPos && !$positions->contains($oldPos) ? 'selected' : '' }}>Other (Please Specify)</option>
                    </select>
                    <input type="text" name="position_title" id="f-position"
                           value="{{ $oldPos }}"
                           placeholder="Specify position..." required
                           class="{{ $errors->has('position_title') ? 'input-error' : '' }}"
                           style="flex:1; {{ $oldPos && !$positions->contains($oldPos) ? '' : 'display:none;' }}">
                </div>
                @error('position_title')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="cas-group" style="grid-column:span 2;">
                <label>Legislative District</label>
                <div style="display:flex; gap:5px;">
                    <select id="f-district-select" style="flex:1;" onchange="
                        if(this.value==='Other'){
                            document.getElementById('f-district').style.display='block';
                            document.getElementById('f-district').value='';
                            document.getElementById('f-district').focus();
                        } else {
                            document.getElementById('f-district').style.display='none';
                            document.getElementById('f-district').value=this.value;
                        }
                    ">
                        <option value="">— Select —</option>
                        @php $oldDist = old('legislative_district', $casual->legislative_district ?? ''); @endphp
                        @foreach($districts as $d)
                            <option value="{{ $d }}" {{ $oldDist === $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                        <option value="Other" {{ $oldDist && !$districts->contains($oldDist) ? 'selected' : '' }}>Other (Please Specify)</option>
                    </select>
                    <input type="text" name="legislative_district" id="f-district"
                           value="{{ $oldDist }}"
                           placeholder="Specify district..."
                           style="flex:1; {{ $oldDist && !$districts->contains($oldDist) ? '' : 'display:none;' }}">
                </div>
            </div>
            <div class="cas-group">
                <label>Item No. (Old)</label>
                <input type="text" name="item_no_old" id="f-item-old"
                       value="{{ old('item_no_old', $casual->item_no_old ?? '') }}"
                       placeholder="1">
            </div>
            <div class="cas-group">
                <label>Item No. (New)</label>
                <input type="text" name="item_no_new" id="f-item-new"
                       value="{{ old('item_no_new', $casual->item_no_new ?? '') }}"
                       placeholder="1">
            </div>
            <div class="cas-group">
                <label>First Day of Service</label>
                <input type="date" name="first_day_of_service" id="f-fds"
                       value="{{ old('first_day_of_service', isset($casual->first_day_of_service) ? $casual->first_day_of_service->format('Y-m-d') : '') }}">
            </div>
        </div>
    </div>

    {{-- ── SECTION 3: Salary ── --}}
    <div class="cas-form-card">
        <div class="cas-section-title"><i class="bi bi-cash-coin"></i> Salary Information</div>

        <p style="font-size:12px;color:#64748b;margin-bottom:12px;">
            <i class="bi bi-info-circle"></i>
            <strong>Current Year</strong> = Rate/Annum for this year &nbsp;|&nbsp;
            <strong>Proposed</strong> = Budget Year proposed rate
        </p>

        <div class="cas-grid cols-3">
            <div class="cas-group">
                <label>SG – Current Year</label>
                <input type="number" name="sg_current" id="f-sg-cur" min="1" max="33"
                       value="{{ old('sg_current', $casual->sg_current ?? '') }}" placeholder="e.g. 1">
            </div>
            <div class="cas-group">
                <label>Step – Current Year</label>
                <input type="number" name="step_current" id="f-step-cur" min="1" max="8"
                       value="{{ old('step_current', $casual->step_current ?? '') }}" placeholder="e.g. 1">
            </div>
            <div class="cas-group">
                <label>Annual Salary – Current (₱)</label>
                <input type="number" name="salary_current" id="f-sal-cur" step="0.01" min="0"
                       value="{{ old('salary_current', $casual->salary_current ?? '') }}" placeholder="0.00">
            </div>

            <div class="cas-group">
                <label>SG – Proposed</label>
                <input type="number" name="sg_proposed" id="f-sg-prop" min="1" max="33"
                       value="{{ old('sg_proposed', $casual->sg_proposed ?? '') }}" placeholder="e.g. 1">
            </div>
            <div class="cas-group">
                <label>Step – Proposed</label>
                <input type="number" name="step_proposed" id="f-step-prop" min="1" max="8"
                       value="{{ old('step_proposed', $casual->step_proposed ?? '') }}" placeholder="e.g. 1">
            </div>
            <div class="cas-group">
                <label>Annual Salary – Proposed (₱)</label>
                <input type="number" name="salary_proposed" id="f-sal-prop" step="0.01" min="0"
                       value="{{ old('salary_proposed', $casual->salary_proposed ?? '') }}" placeholder="0.00">
            </div>

            <div class="cas-group">
                <label>Increase / Decrease (₱)</label>
                <input type="number" name="increase_decrease" id="f-inc-dec" step="0.01"
                       value="{{ old('increase_decrease', $casual->increase_decrease ?? '') }}" placeholder="0.00">
            </div>
            <div class="cas-group">
                <label>Previous Monthly Rate (₱)</label>
                <input type="number" name="previous_rate" id="f-prev-rate" step="0.01" min="0"
                       value="{{ old('previous_rate', $casual->previous_rate ?? '') }}" placeholder="0.00">
            </div>
            <div class="cas-group">
                <label>Current Monthly Rate (₱)</label>
                <input type="number" name="current_rate" id="f-cur-rate" step="0.01" min="0"
                       value="{{ old('current_rate', $casual->current_rate ?? '') }}" placeholder="0.00">
            </div>
        </div>
    </div>

    {{-- ── SECTION 4: Eligibility & Other ── --}}
    <div class="cas-form-card">
        <div class="cas-section-title"><i class="bi bi-award"></i> Eligibility & Other Information</div>
        <div class="cas-grid">
            <div class="cas-group" style="grid-column:span 2;">
                <label>Civil Service Eligibility</label>
                <input type="text" name="eligibility" id="f-elig"
                       value="{{ old('eligibility', $casual->eligibility ?? '') }}"
                       placeholder="e.g. No Eligibility / CS Professional">
            </div>
            <div class="cas-group" style="grid-column:span 2;">
                <label>IP Community Membership</label>
                <input type="text" name="ip_community_membership" id="f-ip"
                       value="{{ old('ip_community_membership', $casual->ip_community_membership ?? '') }}"
                       placeholder="Name of IP Community (if applicable)">
            </div>
            <div class="cas-group" style="grid-column:span 2;">
                <label>Address</label>
                <input type="text" name="address" id="f-address"
                       value="{{ old('address', $casual->address ?? '') }}"
                       placeholder="Complete address">
            </div>
        </div>

        <div class="cas-checkbox-row">
            <label class="cas-checkbox-item">
                <input type="hidden" name="solo_parent" value="0">
                <input type="checkbox" name="solo_parent" id="f-sp" value="1"
                       {{ old('solo_parent', $casual->solo_parent ?? false) ? 'checked' : '' }}>
                Solo Parent
            </label>
        </div>

        <div class="cas-grid">
            <div class="cas-group" style="grid-column:span 4;">
                <label>Annotation</label>
                <textarea name="annotation" id="f-annotation" placeholder="Add annotation (visible in Casual UI only)...">{{ old('annotation', $casual->annotation ?? '') }}</textarea>
            </div>
        </div>

        {{-- ── ACTIONS ── --}}
        <div class="cas-form-actions">
            <a href="{{ route('casual.index') }}" class="btn-cancel-cas">
                <i class="bi bi-arrow-left"></i> Cancel
            </a>
            <button type="submit" class="btn-save-cas" id="btn-cas-save">
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
// Init on page load
document.addEventListener('DOMContentLoaded', function () {
    toggleVacant(document.getElementById('f-is-vacant').checked);

    // ── Employee Code Auto-fill ────────────────────────────────────────
    const lastNameEl = document.getElementById('f-last-name');
    const dobEl      = document.getElementById('f-birthdate');
    const codeEl     = document.getElementById('f-employee-code');
    const btnEl      = document.getElementById('btn-autofill-casual');

    function buildCasualCode() {
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

    function autofillCasualCode() {
        const code = buildCasualCode();
        if (code && codeEl) codeEl.value = code;
    }

    if (lastNameEl) lastNameEl.addEventListener('change', function() { if (!codeEl.value) autofillCasualCode(); });
    if (dobEl)      dobEl.addEventListener('change',      function() { if (!codeEl.value) autofillCasualCode(); });
    if (btnEl)      btnEl.addEventListener('click', autofillCasualCode);
});
</script>
