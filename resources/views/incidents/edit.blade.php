<x-dashboard-app>
<div class="d-flex justify-content-between align-items-center mb-4" style="background: linear-gradient(135deg,#1e3a5f 0%,#1a5276 55%,#0f4c75 100%); border-radius:14px; padding:24px 28px; color:#fff; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
    <div>
        <h1 style="font-size:24px;font-weight:800;margin:0 0 8px;letter-spacing:-0.5px;">
            <i class="bi bi-pencil-square text-warning me-2"></i>Edit Incident Report
        </h1>
        <p style="font-size:13.5px;opacity:.85;margin:0;font-weight:500;">
            Editing report {{ $incident->reference_no }}
        </p>
    </div>
    <div>
        <a href="{{ route('incidents.show', $incident) }}" class="btn btn-light fw-bold px-4 py-2" style="border-radius:10px;box-shadow:0 4px 12px rgba(255, 255, 255, 0.3);">
            <i class="bi bi-arrow-left me-1"></i> Back to Report
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm" style="border-radius:16px;">
    <form method="POST" action="{{ route('incidents.update', $incident) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card-body p-4">
            @if ($errors->any())
                <div class="alert alert-danger" style="border-radius:12px; font-weight:500;" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-medium small text-muted mb-1">Incident Date & Time <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-clock-history text-muted"></i></span>
                        <input type="datetime-local" name="incident_datetime" value="{{ old('incident_datetime', $incident->incident_datetime->format('Y-m-d\TH:i')) }}" required class="form-control border-start-0" style="border-radius:0 8px 8px 0;">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium small text-muted mb-1">Location</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-geo-alt text-muted"></i></span>
                        <input name="location" value="{{ old('location', $incident->location) }}" placeholder="e.g. Main Office, Room 302" class="form-control border-start-0" style="border-radius:0 8px 8px 0;">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label fw-medium small text-muted mb-1">Category <span class="text-danger">*</span></label>
                    <select name="category" required class="form-select form-control" style="border-radius:8px;">
                        <option value="" disabled>Select category...</option>
                        <option value="attendance" {{ old('category', $incident->category) === 'attendance' ? 'selected' : '' }}>Attendance</option>
                        <option value="conduct" {{ old('category', $incident->category) === 'conduct' ? 'selected' : '' }}>Conduct</option>
                        <option value="performance" {{ old('category', $incident->category) === 'performance' ? 'selected' : '' }}>Performance</option>
                        <option value="safety" {{ old('category', $incident->category) === 'safety' ? 'selected' : '' }}>Safety</option>
                        <option value="property" {{ old('category', $incident->category) === 'property' ? 'selected' : '' }}>Property</option>
                        <option value="interpersonal" {{ old('category', $incident->category) === 'interpersonal' ? 'selected' : '' }}>Interpersonal</option>
                        <option value="other" {{ old('category', $incident->category) === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium small text-muted mb-1">Attachment (Evidence/Doc)</label>
                    <input type="file" name="attachment" class="form-control" style="border-radius:8px;">
                    @if($incident->document_id)
                        <div class="form-text mt-1 text-primary">
                            <i class="bi bi-paperclip"></i> Current attachment will be kept unless you upload a new one.
                        </div>
                    @endif
                </div>
            </div>

            <hr class="text-muted opacity-25 mb-4">
            <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-person-badge me-2"></i>Involved Person Details</h6>

            <div class="row g-3">
                <div class="col-12 position-relative">
                    <label class="form-label fw-medium small text-muted mb-1">PGB Employee (Auto-fill)</label>
                    <input type="text" id="involvedPersonSearch" class="form-control" autocomplete="off"
                           value="{{ old('involved_person_name', $incident->personnel_id ? $incident->involved_person_name : '') }}"
                           placeholder="Not a PGB employee / type a name to search and auto-fill..." style="border-radius:8px;">
                    <div id="involvedPersonResults" class="list-group position-absolute w-100 shadow-sm"
                         style="z-index:1055; max-height:260px; overflow-y:auto; display:none; top:100%;"></div>
                    <input type="hidden" name="personnel_id" id="personnelIdField" value="{{ old('personnel_id', $incident->personnel_id) }}">
                </div>
                
                <div class="col-md-4">
                    <input type="text" name="involved_person_name" id="involvedPersonName" value="{{ old('involved_person_name', $incident->involved_person_name) }}" placeholder="Full Name" class="form-control bg-light" style="border-radius:8px;">
                </div>
                <div class="col-md-4">
                    <input type="text" name="involved_position" id="involvedPosition" value="{{ old('involved_position', $incident->involved_position) }}" placeholder="Position" class="form-control bg-light" style="border-radius:8px;">
                </div>
                <div class="col-md-4">
                    <input type="text" name="involved_office" id="involvedOffice" value="{{ old('involved_office', $incident->involved_office) }}" placeholder="Office / Department" class="form-control bg-light" style="border-radius:8px;">
                </div>
            </div>
            
            <hr class="text-muted opacity-25 my-4">
            <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-card-text me-2"></i>Narrative & Actions</h6>

            <div class="mb-3">
                <label class="form-label fw-medium small text-muted mb-1">Factual Narrative <span class="text-danger">*</span></label>
                <textarea name="narrative" placeholder="Describe the incident based on observable facts only. Avoid conclusions." required class="form-control" style="border-radius:8px; resize:vertical;" rows="5">{{ old('narrative', $incident->narrative) }}</textarea>
                <div class="form-text text-muted" style="font-size:11.5px;">Include specific details (who, what, when, where).</div>
            </div>
            
            <div>
                <label class="form-label fw-medium small text-muted mb-1">Immediate Action Taken (Optional)</label>
                <textarea name="immediate_action_taken" placeholder="Any actions taken immediately after the incident" class="form-control" style="border-radius:8px; resize:vertical;" rows="3">{{ old('immediate_action_taken', $incident->immediate_action_taken) }}</textarea>
            </div>

        </div>
        <div class="card-footer bg-light p-3 text-end" style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
            <a href="{{ route('incidents.show', $incident) }}" class="btn btn-light fw-medium px-4 me-2" style="border-radius:8px; border:1px solid #e5e7eb;">Cancel</a>
            <button type="submit" class="btn btn-primary fw-bold px-4" style="border-radius:8px; box-shadow:0 4px 12px rgba(37,99,235,0.2);">
                <i class="bi bi-save me-1"></i> Update Report
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        initEmployeeAutocomplete({
            searchInputId: 'involvedPersonSearch',
            resultsId: 'involvedPersonResults',
            personnelIdId: 'personnelIdField',
            nameId: 'involvedPersonName',
            positionId: 'involvedPosition',
            officeId: 'involvedOffice',
        });
    });

    function initEmployeeAutocomplete(ids) {
        const searchInput = document.getElementById(ids.searchInputId);
        const results = document.getElementById(ids.resultsId);
        const personnelId = document.getElementById(ids.personnelIdId);
        const name = document.getElementById(ids.nameId);
        const position = document.getElementById(ids.positionId);
        const office = document.getElementById(ids.officeId);
        if (!searchInput || !results) return;

        let debounceTimer = null;
        let controller = null;

        function hideResults() {
            results.style.display = 'none';
            results.innerHTML = '';
        }

        function selectEmployee(emp) {
            personnelId.value = emp.id;
            name.value = emp.name || '';
            position.value = emp.position_title || '';
            office.value = emp.office_department || '';
            searchInput.value = emp.name || '';
            hideResults();
        }

        searchInput.addEventListener('input', function () {
            // Typing after a selection means they're changing/clearing it.
            personnelId.value = '';

            const q = searchInput.value.trim();
            clearTimeout(debounceTimer);
            if (q.length < 2) {
                hideResults();
                return;
            }
            debounceTimer = setTimeout(function () {
                if (controller) controller.abort();
                controller = new AbortController();
                fetch('{{ route('incidents.employees.search') }}?q=' + encodeURIComponent(q), {
                    signal: controller.signal,
                    headers: { 'Accept': 'application/json' },
                })
                    .then(r => r.json())
                    .then(function (employees) {
                        if (!employees.length) {
                            results.innerHTML = '<div class="list-group-item text-muted small">No matching employee found.</div>';
                            results.style.display = 'block';
                            return;
                        }
                        results.innerHTML = employees.map(function (emp, i) {
                            return '<button type="button" class="list-group-item list-group-item-action py-2" data-idx="' + i + '">'
                                + '<div class="fw-medium">' + emp.name + '</div>'
                                + '<div class="small text-muted">' + (emp.position_title || '') + (emp.office_department ? ' &middot; ' + emp.office_department : '') + '</div>'
                                + '</button>';
                        }).join('');
                        results.querySelectorAll('[data-idx]').forEach(function (btn) {
                            btn.addEventListener('click', function () {
                                selectEmployee(employees[parseInt(btn.getAttribute('data-idx'), 10)]);
                            });
                        });
                        results.style.display = 'block';
                    })
                    .catch(function (err) {
                        if (err.name !== 'AbortError') hideResults();
                    });
            }, 300);
        });

        document.addEventListener('click', function (e) {
            if (!results.contains(e.target) && e.target !== searchInput) hideResults();
        });
    }
</script>
</x-dashboard-app>
