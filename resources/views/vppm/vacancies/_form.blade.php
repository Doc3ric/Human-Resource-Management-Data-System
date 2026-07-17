@php 
$v = $vacancy ?? null; 

// Extract unique offices from vacantPlantillaRecords
$offices = collect($vacantPlantillaRecords)->pluck('office_department')->unique()->filter()->sort()->values();
@endphp

<div class="row g-3">
    <!-- Manual Entry Toggle -->
    <div class="col-12 mb-2">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="manualEntryToggle" name="is_manual_entry" value="1" {{ old('is_manual_entry', !$v?->plantilla_record_id && $v ? true : false) ? 'checked' : '' }}>
            <label class="form-check-label fw-bold" for="manualEntryToggle">Enable Manual Entry (Toggle this on if the position is not in the Plantilla list)</label>
        </div>
        <small class="text-muted">By default, selecting an Office and a Plantilla Item will auto-populate the details.</small>
    </div>

    <!-- Office Selector (Only active if not manual entry) -->
    <div class="col-md-6" id="officeSelectorWrap">
        <label class="form-label">Filter by Office/Department</label>
        <select id="officeSelector" class="form-select form-select-sm">
            <option value="">— Select Office —</option>
            @foreach($offices as $office)
                <option value="{{ $office }}">{{ $office }}</option>
            @endforeach
        </select>
    </div>

    <!-- Vacant Item Selector -->
    <div class="col-md-6" id="plantillaSelectorWrap">
        <label class="form-label">Link to a vacant Plantilla item</label>
        <select name="plantilla_record_id" id="plantillaSelector" class="form-select form-select-sm">
            <option value="">— Select a Vacant Item —</option>
            @foreach($vacantPlantillaRecords as $r)
                <option value="{{ $r->id }}" data-office="{{ $r->office_department }}" @selected(old('plantilla_record_id', $v?->plantilla_record_id) == $r->id)>
                    {{ $r->item_no_new }} — {{ $r->position_title }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-12"><hr></div>

    <div class="col-md-6">
        <label class="form-label">Position Title *</label>
        <input name="position_title" id="position_title" required class="form-control form-control-sm" value="{{ old('position_title', $v?->position_title) }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Parenthetical Title <small class="text-muted">(Sec.26 — missing this is not a disapproval ground)</small></label>
        <input name="parenthetical_title" id="parenthetical_title" class="form-control form-control-sm" value="{{ old('parenthetical_title', $v?->parenthetical_title) }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Salary Grade *</label>
        <input name="salary_grade" id="salary_grade" required class="form-control form-control-sm" value="{{ old('salary_grade', $v?->salary_grade) }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Monthly Salary *</label>
        <input type="number" step="0.01" name="monthly_salary" id="monthly_salary" required class="form-control form-control-sm" value="{{ old('monthly_salary', $v?->monthly_salary) }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Appointment Status *</label>
        <select name="appointment_status" id="appointment_status" required class="form-select form-select-sm">
            @foreach(['Permanent','Temporary','Casual','Coterminous'] as $opt)
                <option value="{{ $opt }}" @selected(old('appointment_status', $v?->appointment_status) === $opt)>{{ $opt }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Vacancy Type *</label>
        <select name="vacancy_type" id="vacancy_type" required class="form-select form-select-sm">
            @foreach(['Original','Vice','Reclassified','Created'] as $opt)
                <option value="{{ $opt }}" @selected(old('vacancy_type', $v?->vacancy_type) === $opt)>{{ $opt }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Place of Assignment *</label>
        <input name="place_of_assignment" id="place_of_assignment" required class="form-control form-control-sm" value="{{ old('place_of_assignment', $v?->place_of_assignment) }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Office/Division</label>
        <input name="office_division" id="office_division" class="form-control form-control-sm" value="{{ old('office_division', $v?->office_division) }}">
    </div>

    <div class="col-md-4">
        <label class="form-label">Vice Whom</label>
        <input name="vice_whom" class="form-control form-control-sm" value="{{ old('vice_whom', $v?->vice_whom) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Vacated Date</label>
        <input type="date" name="vacated_date" class="form-control form-control-sm" value="{{ old('vacated_date', $v?->vacated_date?->toDateString()) }}">
    </div>
    <div class="col-md-4">
        <div class="form-check mt-4">
            <input type="checkbox" name="is_anticipated" value="1" class="form-check-input" id="isAnticipated" @checked(old('is_anticipated', $v?->is_anticipated)) onchange="document.getElementById('anticipatedDateWrap').style.display=this.checked?'block':'none'">
            <label class="form-check-label" for="isAnticipated">Anticipated vacancy <small class="text-muted">(Sec.31 — up to 180 days before separation)</small></label>
        </div>
    </div>
    <div class="col-md-4" id="anticipatedDateWrap" style="display:{{ old('is_anticipated', $v?->is_anticipated) ? 'block' : 'none' }}">
        <label class="form-label">Anticipated Incumbent Separation Date</label>
        <input type="date" name="anticipated_incumbent_separation_date" class="form-control form-control-sm" value="{{ old('anticipated_incumbent_separation_date', $v?->anticipated_incumbent_separation_date?->toDateString()) }}">
    </div>

    <div class="col-12"><hr><h6 class="fw-bold">Qualification Standards (Will automatically fetch latest CSC QS for this position if available)</h6></div>
    <div class="col-md-6">
        <label class="form-label">Education *</label>
        <textarea name="qs_education" id="qs_education" required class="form-control form-control-sm">{{ old('qs_education', $v?->qs_education) }}</textarea>
    </div>
    <div class="col-md-6">
        <label class="form-label">Training *</label>
        <textarea name="qs_training" id="qs_training" required class="form-control form-control-sm">{{ old('qs_training', $v?->qs_training) }}</textarea>
    </div>
    <div class="col-md-6">
        <label class="form-label">Experience *</label>
        <textarea name="qs_experience" id="qs_experience" required class="form-control form-control-sm">{{ old('qs_experience', $v?->qs_experience) }}</textarea>
    </div>
    <div class="col-md-6">
        <label class="form-label">Eligibility *</label>
        <textarea name="qs_eligibility" id="qs_eligibility" required class="form-control form-control-sm">{{ old('qs_eligibility', $v?->qs_eligibility) }}</textarea>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const manualEntryToggle = document.getElementById('manualEntryToggle');
    const officeSelectorWrap = document.getElementById('officeSelectorWrap');
    const plantillaSelectorWrap = document.getElementById('plantillaSelectorWrap');
    const officeSelector = document.getElementById('officeSelector');
    const plantillaSelector = document.getElementById('plantillaSelector');
    
    // Auto-filled fields
    const fieldsToLock = [
        'position_title', 'salary_grade', 'monthly_salary', 
        'appointment_status', 'office_division', 'place_of_assignment'
    ];

    function toggleManualEntry() {
        const isManual = manualEntryToggle.checked;
        if (isManual) {
            officeSelectorWrap.style.display = 'none';
            plantillaSelectorWrap.style.display = 'none';
            plantillaSelector.value = ''; // clear selection
            
            // Unlock fields
            fieldsToLock.forEach(id => {
                const el = document.getElementById(id);
                if(el) el.readOnly = false;
            });
        } else {
            officeSelectorWrap.style.display = 'block';
            plantillaSelectorWrap.style.display = 'block';
            
            // Lock fields
            fieldsToLock.forEach(id => {
                const el = document.getElementById(id);
                if(el) {
                    if (el.tagName === 'SELECT') {
                        // For select, we can't easily readonly, but since it's driven by plantilla selection, it's fine.
                        // We could use CSS pointer-events: none, but let's just make inputs readonly.
                    } else {
                        el.readOnly = true;
                    }
                }
            });
        }
    }

    // Filter plantilla dropdown by selected office
    function filterPlantillaByOffice() {
        const selectedOffice = officeSelector.value;
        const options = plantillaSelector.querySelectorAll('option');
        
        // Always show the default empty option
        options[0].style.display = 'block';
        
        let firstVisible = null;

        for (let i = 1; i < options.length; i++) {
            const opt = options[i];
            const optOffice = opt.getAttribute('data-office');
            
            if (!selectedOffice || optOffice === selectedOffice) {
                opt.style.display = 'block';
                if (!firstVisible) firstVisible = opt.value;
            } else {
                opt.style.display = 'none';
            }
        }
        
        plantillaSelector.value = ''; // Reset selection
        clearAutoFilledFields();
    }

    function clearAutoFilledFields() {
        if (manualEntryToggle.checked) return;
        document.getElementById('position_title').value = '';
        document.getElementById('salary_grade').value = '';
        document.getElementById('monthly_salary').value = '';
        document.getElementById('office_division').value = '';
        document.getElementById('place_of_assignment').value = '';
        document.getElementById('qs_education').value = '';
        document.getElementById('qs_training').value = '';
        document.getElementById('qs_experience').value = '';
        document.getElementById('qs_eligibility').value = '';
    }

    // Fetch and populate details from the server
    function populatePlantillaDetails() {
        const id = plantillaSelector.value;
        if (!id) {
            clearAutoFilledFields();
            return;
        }

        // Show a loading state if desired
        fetch(`{{ url('/vppm/vacancies/api/plantilla-details') }}/${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.error) return;
                
                document.getElementById('position_title').value = data.position_title || '';
                document.getElementById('salary_grade').value = data.salary_grade || '';
                document.getElementById('monthly_salary').value = data.monthly_salary || '';
                document.getElementById('office_division').value = data.office_division || '';
                document.getElementById('place_of_assignment').value = data.office_division || ''; // usually same as office
                
                if (data.appointment_status) {
                    document.getElementById('appointment_status').value = data.appointment_status;
                }

                // Populate QS fields (which the user can still edit)
                document.getElementById('qs_education').value = data.qs_education || '';
                document.getElementById('qs_training').value = data.qs_training || '';
                document.getElementById('qs_experience').value = data.qs_experience || '';
                document.getElementById('qs_eligibility').value = data.qs_eligibility || '';
            })
            .catch(err => console.error("Failed to fetch plantilla details", err));
    }

    // Event Listeners
    manualEntryToggle.addEventListener('change', toggleManualEntry);
    officeSelector.addEventListener('change', filterPlantillaByOffice);
    plantillaSelector.addEventListener('change', populatePlantillaDetails);

    // Initial setup
    toggleManualEntry();
    
    // If a plantilla_record_id is already selected (e.g. on validation error or edit)
    if (plantillaSelector.value) {
        const selectedOpt = plantillaSelector.options[plantillaSelector.selectedIndex];
        if (selectedOpt) {
            officeSelector.value = selectedOpt.getAttribute('data-office');
            filterPlantillaByOffice();
            plantillaSelector.value = selectedOpt.value; // restore after filter
            
            // Only auto-populate if we are not in edit mode (we have a $v) or if we want to overwrite.
            // Since it's old() value, maybe we don't overwrite if they are already filled.
            // But if it's purely a new load with a selected value, let's fetch it.
        }
    }
});
</script>
