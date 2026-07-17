<x-dashboard-app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Pre-Evaluate Applicants
        </h2>
    </x-slot>

    <div class="content-wrapper p-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {!! session('success') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0 mb-4" style="border-radius:12px;">
            <div class="card-body bg-light" style="border-radius:12px;">
                <form method="GET" action="{{ route('recruitment.pre-evaluate') }}" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Search Applicant Name</label>
                        <input type="text" name="search" class="form-control" placeholder="Last or first name..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Select Offices</label>
                        <select name="offices[]" id="officesSelect" class="form-select select2-multiple" multiple>
                            @foreach($offices as $office)
                                <option value="{{ $office }}" {{ in_array($office, request('offices', [])) ? 'selected' : '' }}>{{ $office }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Select Vacancies (Items)</label>
                        <select name="positions[]" id="positionsSelect" class="form-select select2-multiple" multiple>
                            @foreach($positions as $value => $label)
                                <option value="{{ $value }}" data-office="{{ $positionOffices[$value] ?? '' }}" {{ in_array($value, request('positions', [])) ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="form-text" style="font-size:11px;">Narrows automatically to match the office(s) selected on the left.</div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-secondary w-100 fw-bold"><i class="bi bi-funnel"></i> Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <form method="POST" action="{{ route('recruitment.bulk-evaluate') }}">
            @csrf
            
            <div class="card shadow-sm border-0 mb-4" style="border-radius:12px;">
                <div class="card-header border-0 text-white" style="background:linear-gradient(135deg,#1f497d,#2e75b6); border-radius:12px 12px 0 0;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0"><i class="bi bi-clipboard2-check-fill me-2"></i>Bulk Pre-Evaluation Assessment</h5>
                        <button type="submit" class="btn btn-light btn-sm fw-bold text-primary"><i class="bi bi-save-fill"></i> Save All Evaluations</button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width:200px;">Applicant</th>
                                    <th style="width:130px;">QS Req.</th>
                                    <th style="width:130px;">Exam Status</th>
                                    <th style="width:130px;">Docs Complete</th>
                                    <th style="width:150px;">Final Rating</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($matrix as $office => $positionsList)
                                    <tr class="table-secondary">
                                        <td colspan="6" class="fw-bold"><i class="bi bi-building me-1"></i> Office: {{ $office }}</td>
                                    </tr>
                                    @foreach($positionsList as $vacancyKey => $details)
                                        <tr class="table-info">
                                            <td colspan="6" class="fw-bold ps-4">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <i class="bi bi-briefcase-fill me-1"></i> Vacancy: {{ $details['label'] }}
                                                        <span class="ms-3 text-muted" style="font-size:13px; font-weight:normal;">(Item No: {{ $details['item_no'] ?: 'N/A' }})</span>
                                                    </div>
                                                    <div>
                                                        <span class="badge bg-primary me-2" style="font-size:12px;">SG-{{ $details['sg'] ?? 'N/A' }}</span>
                                                        <span class="badge bg-success" style="font-size:12px;">₱ {{ number_format($details['rate'], 2) }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @foreach($details['applicants'] as $app)
                                            @php
                                                $eval = $app->evaluation;
                                            @endphp
                                            <tr>
                                                <td class="ps-5">
                                                    <strong>{{ $app->last_name }}, {{ $app->first_name }}</strong><br>
                                                    <span class="text-muted" style="font-size:11px;">Ref: {{ $app->reference_no }}</span>
                                                </td>
                                                <td>
                                                    <select name="evaluations[{{ $app->id }}][qs_requirement]" class="form-select form-select-sm {{ ($eval->qs_requirement ?? '') === 'Met' ? 'border-success bg-light-success text-success fw-bold' : (($eval->qs_requirement ?? '') === 'Unmet' ? 'border-danger bg-light-danger text-danger fw-bold' : '') }}">
                                                        <option value=""></option>
                                                        <option value="Met" {{ ($eval->qs_requirement ?? '') === 'Met' ? 'selected' : '' }}>Met</option>
                                                        <option value="Unmet" {{ ($eval->qs_requirement ?? '') === 'Unmet' ? 'selected' : '' }}>Unmet</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select name="evaluations[{{ $app->id }}][exam_status]" class="form-select form-select-sm {{ ($eval->exam_status ?? '') === 'Passed' ? 'border-success text-success fw-bold' : (($eval->exam_status ?? '') === 'Failed' ? 'border-danger text-danger fw-bold' : '') }}">
                                                        <option value=""></option>
                                                        <option value="Passed" {{ ($eval->exam_status ?? '') === 'Passed' ? 'selected' : '' }}>Passed</option>
                                                        <option value="Failed" {{ ($eval->exam_status ?? '') === 'Failed' ? 'selected' : '' }}>Failed</option>
                                                        <option value="Absent" {{ ($eval->exam_status ?? '') === 'Absent' ? 'selected' : '' }}>Absent</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select name="evaluations[{{ $app->id }}][docs_complete]" class="form-select form-select-sm {{ ($eval->docs_complete ?? '') === 'Yes' ? 'border-success text-success fw-bold' : (($eval->docs_complete ?? '') === 'No' ? 'border-danger text-danger fw-bold' : '') }}">
                                                        <option value=""></option>
                                                        <option value="Yes" {{ ($eval->docs_complete ?? '') === 'Yes' ? 'selected' : '' }}>Yes</option>
                                                        <option value="No" {{ ($eval->docs_complete ?? '') === 'No' ? 'selected' : '' }}>No</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select name="evaluations[{{ $app->id }}][final_rating]" class="form-select form-select-sm shadow-sm border-secondary {{ ($eval->final_rating ?? '') === 'Qualified' ? 'bg-success text-white fw-bold' : (($eval->final_rating ?? '') === 'Disqualified' ? 'bg-danger text-white fw-bold' : '') }}">
                                                        <option value=""></option>
                                                        <option value="Qualified" {{ ($eval->final_rating ?? '') === 'Qualified' ? 'selected' : '' }}>Qualified</option>
                                                        <option value="Disqualified" {{ ($eval->final_rating ?? '') === 'Disqualified' ? 'selected' : '' }}>Disqualified</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text" name="evaluations[{{ $app->id }}][remarks]" class="form-control form-control-sm" placeholder="Remarks..." value="{{ $eval->remarks ?? '' }}">
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-5">No applicants found for the selected criteria.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            @if(!empty($matrix))
                <div class="d-flex justify-content-end mb-5">
                    <button type="submit" class="btn btn-primary fw-bold px-5 py-2" style="font-size:16px;">
                        <i class="bi bi-save-fill me-2"></i> Save All Evaluations
                    </button>
                </div>
            @endif
        </form>

    </div>

    <!-- Include Select2 for multi-select -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#officesSelect').select2({
                placeholder: "Select options",
                allowClear: true
            });

            // Vacancy list narrows to whichever office(s) are selected, so users can't
            // pick a mismatched office/item combo that silently returns zero applicants.
            function positionMatcher(params, data) {
                if (!data.id) {
                    return data;
                }
                const selectedOffices = $('#officesSelect').val() || [];
                const optOffice = $(data.element).data('office');
                if (selectedOffices.length && optOffice && !selectedOffices.includes(String(optOffice))) {
                    return null;
                }
                if ($.trim(params.term) === '') {
                    return data;
                }
                if (data.text.toUpperCase().indexOf(params.term.toUpperCase()) > -1) {
                    return data;
                }
                return null;
            }

            $('#positionsSelect').select2({
                placeholder: "Select options",
                allowClear: true,
                matcher: positionMatcher
            });

            // Drop any already-selected vacancy items that no longer match once the office changes.
            $('#officesSelect').on('change', function() {
                const selectedOffices = $(this).val() || [];
                let changed = false;
                $('#positionsSelect option:selected').each(function() {
                    const optOffice = $(this).data('office');
                    if (selectedOffices.length && optOffice && !selectedOffices.includes(String(optOffice))) {
                        $(this).prop('selected', false);
                        changed = true;
                    }
                });
                if (changed) {
                    $('#positionsSelect').trigger('change');
                }
            });

            // Make the selects change colors dynamically on selection
            $('select[name^="evaluations"]').on('change', function() {
                let val = $(this).val();
                $(this).removeClass('border-success bg-light-success text-success bg-success text-white border-danger bg-light-danger text-danger bg-danger fw-bold');
                
                if (val === 'Met' || val === 'Passed' || val === 'Yes') {
                    $(this).addClass('border-success text-success fw-bold');
                } else if (val === 'Unmet' || val === 'Failed' || val === 'No') {
                    $(this).addClass('border-danger text-danger fw-bold');
                } else if (val === 'Qualified') {
                    $(this).addClass('bg-success text-white fw-bold');
                } else if (val === 'Disqualified') {
                    $(this).addClass('bg-danger text-white fw-bold');
                }
            });
        });
    </script>
</x-dashboard-app>p>
