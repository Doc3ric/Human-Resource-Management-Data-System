<x-dashboard-app>
<div class="d-flex justify-content-between align-items-center mb-4" style="background: linear-gradient(135deg,#1e3a5f 0%,#1a5276 55%,#0f4c75 100%); border-radius:14px; padding:24px 28px; color:#fff; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
    <div>
        <h1 style="font-size:24px;font-weight:800;margin:0 0 8px;letter-spacing:-0.5px;">
            <i class="bi bi-exclamation-diamond-fill text-warning me-2"></i>Incident Reports
        </h1>
        <p style="font-size:13.5px;opacity:.85;margin:0;font-weight:500;">
            Facts-only intake; advisory drafts are AI/rules-assisted suggestions only, never findings — 2025 RACCS due process governs.
        </p>
    </div>
    <div>
        <button type="button" class="btn btn-warning fw-bold px-4 py-2" style="border-radius:10px;box-shadow:0 4px 12px rgba(251, 191, 36, 0.3);" data-bs-toggle="modal" data-bs-target="#newIncidentModal">
            <i class="bi bi-plus-lg me-1"></i> File New Report
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm d-flex align-items-center" style="border-radius:12px; font-weight:500;" role="alert">
        <i class="bi bi-check-circle-fill fs-5 me-3"></i>
        <div>{{ session('success') }}</div>
    </div>
@endif

<div class="card border-0 shadow-sm" style="border-radius:14px;overflow:hidden;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:14px;">
                <thead style="background-color:#f8fafc; border-bottom:2px solid #e2e8f0;">
                    <tr style="color:#64748b; font-size:12px; text-transform:uppercase; letter-spacing:0.5px;">
                        <th class="ps-4 py-3 border-0">Reference</th>
                        <th class="py-3 border-0">Category</th>
                        <th class="py-3 border-0">Reported</th>
                        <th class="py-3 border-0">Status</th>
                        <th class="py-3 border-0">Track</th>
                        <th class="pe-4 py-3 border-0 text-end">Action</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($reports as $r)
                        <tr>
                            <td class="ps-4 py-3">
                                <a href="{{ route('incidents.show', $r) }}" class="fw-bold text-primary text-decoration-none">{{ $r->reference_no }}</a>
                            </td>
                            <td class="py-3">
                                <span class="badge bg-light text-dark border">{{ ucfirst($r->category) }}</span>
                            </td>
                            <td class="py-3 text-secondary fw-medium">
                                <i class="bi bi-calendar3 me-1 opacity-50"></i> {{ $r->reported_at->format('M d, Y') }}
                            </td>
                            <td class="py-3">
                                @php
                                    $statusColor = match($r->status) {
                                        'draft' => 'bg-secondary',
                                        'advisory_ready' => 'bg-info text-dark',
                                        'finalized' => 'bg-success',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $statusColor }} rounded-pill px-3">{{ ucfirst(str_replace('_', ' ', $r->status)) }}</span>
                            </td>
                            <td class="py-3 fw-medium">
                                @if($r->final_track === 'counseling')
                                    <span class="text-primary"><i class="bi bi-person-heart me-1"></i> Counseling</span>
                                @elseif($r->final_track === 'escalated')
                                    <span class="text-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i> Escalated</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="pe-4 py-3 text-end">
                                <a href="{{ route('incidents.show', $r) }}" class="btn btn-sm btn-light border shadow-sm" style="border-radius:8px;">
                                    View <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                                <span class="fw-medium">No incident reports yet.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($reports->hasPages())
    <div class="card-footer bg-white border-top p-3">
        {{ $reports->links() }}
    </div>
    @endif
</div>

<!-- New Incident Report Modal -->
<div class="modal fade" id="newIncidentModal" tabindex="-1" aria-labelledby="newIncidentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3a8a, #2563eb); color: white; border-top-left-radius: 16px; border-top-right-radius: 16px; border-bottom: none;">
                <h5 class="modal-title fw-bold" id="newIncidentModalLabel">
                    <i class="bi bi-file-earmark-plus-fill me-2"></i>File New Incident Report
                </h5>
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form method="POST" action="{{ route('incidents.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-medium small text-muted mb-1">Incident Date & Time <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-clock-history text-muted"></i></span>
                                <input type="datetime-local" name="incident_datetime" required class="form-control border-start-0" style="border-radius:0 8px 8px 0;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium small text-muted mb-1">Location</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-geo-alt text-muted"></i></span>
                                <input name="location" placeholder="e.g. Main Office, Room 302" class="form-control border-start-0" style="border-radius:0 8px 8px 0;">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-medium small text-muted mb-1">Category <span class="text-danger">*</span></label>
                            <select name="category" required class="form-select form-control" style="border-radius:8px;">
                                <option value="" disabled selected>Select category...</option>
                                <option value="attendance">Attendance</option>
                                <option value="conduct">Conduct</option>
                                <option value="performance">Performance</option>
                                <option value="safety">Safety</option>
                                <option value="property">Property</option>
                                <option value="interpersonal">Interpersonal</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium small text-muted mb-1">Attachment (Evidence/Doc)</label>
                            <input type="file" name="attachment" class="form-control" style="border-radius:8px;">
                        </div>
                    </div>

                    <hr class="text-muted opacity-25 mb-4">
                    <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-person-badge me-2"></i>Involved Person Details</h6>

                    <div class="row g-3">
                        <div class="col-12 position-relative">
                            <label class="form-label fw-medium small text-muted mb-1">PGB Employee (Auto-fill)</label>
                            <input type="text" id="involvedPersonSearch" class="form-control" autocomplete="off"
                                   placeholder="Not a PGB employee / type a name to search and auto-fill..." style="border-radius:8px;">
                            <div id="involvedPersonResults" class="list-group position-absolute w-100 shadow-sm"
                                 style="z-index:1055; max-height:260px; overflow-y:auto; display:none; top:100%;"></div>
                            <input type="hidden" name="personnel_id" id="personnelIdField">
                        </div>
                        
                        <div class="col-md-4">
                            <input type="text" name="involved_person_name" id="involvedPersonName" placeholder="Full Name" class="form-control bg-light" style="border-radius:8px;">
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="involved_position" id="involvedPosition" placeholder="Position" class="form-control bg-light" style="border-radius:8px;">
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="involved_office" id="involvedOffice" placeholder="Office / Department" class="form-control bg-light" style="border-radius:8px;">
                        </div>
                    </div>
                    
                    <hr class="text-muted opacity-25 my-4">
                    <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-card-text me-2"></i>Narrative & Actions</h6>

                    <div class="mb-3">
                        <label class="form-label fw-medium small text-muted mb-1">Factual Narrative <span class="text-danger">*</span></label>
                        <textarea name="narrative" placeholder="Describe the incident based on observable facts only. Avoid conclusions." required class="form-control" style="border-radius:8px; resize:none;" rows="4"></textarea>
                        <div class="form-text text-muted" style="font-size:11.5px;">Include specific details (who, what, when, where).</div>
                    </div>
                    
                    <div>
                        <label class="form-label fw-medium small text-muted mb-1">Immediate Action Taken (Optional)</label>
                        <textarea name="immediate_action_taken" placeholder="Any actions taken immediately after the incident" class="form-control" style="border-radius:8px; resize:none;" rows="2"></textarea>
                    </div>

                </div>
                <div class="modal-footer bg-light border-top-0" style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                    <button type="button" class="btn btn-light fw-medium px-4" data-bs-dismiss="modal" style="border-radius:8px; border:1px solid #e5e7eb;">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4" style="border-radius:8px; box-shadow:0 4px 12px rgba(37,99,235,0.2);">
                        <i class="bi bi-send-fill me-1"></i> Submit Report
                    </button>
                </div>
            </form>
        </div>
    </div>
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
