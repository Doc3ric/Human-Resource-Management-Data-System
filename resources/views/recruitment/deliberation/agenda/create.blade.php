<x-dashboard-app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Agenda Preparation Workspace (Module 6.5)
        </h2>
    </x-slot>

    <div class="content-wrapper p-4">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm border-0 mb-4" style="border-radius:12px;">
            <div class="card-body bg-light" style="border-radius:12px;">
                <form method="GET" action="{{ route('recruitment.deliberation.agenda.create') }}" class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Select Offices</label>
                        <select name="offices[]" class="form-select select2-multiple" multiple>
                            @foreach($offices as $office)
                                <option value="{{ $office }}" {{ in_array($office, request('offices', [])) ? 'selected' : '' }}>{{ $office }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Select Vacancies (Items)</label>
                        <select name="positions[]" class="form-select select2-multiple" multiple>
                            @foreach($positions as $value => $label)
                                <option value="{{ $value }}" {{ in_array($value, request('positions', [])) ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-secondary w-100 fw-bold"><i class="bi bi-funnel"></i> Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <form method="POST" action="{{ route('recruitment.deliberation.agenda.store') }}">
            @csrf
            
            <div class="mb-4">
                <label class="form-label fw-bold" style="font-size:18px;">Agenda Title</label>
                <input type="text" name="title" class="form-control form-control-lg" required placeholder="e.g. HRMPSB Deliberation Agenda for July 2026" value="HRMPSB Deliberation Agenda - {{ date('M d, Y') }}">
            </div>

            <!-- Part I: Preliminary Editor -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius:12px;">
                <div class="card-header bg-dark text-white fw-bold" style="border-radius:12px 12px 0 0;">
                    Part I: Preliminary Editor
                </div>
                <div class="card-body p-0">
                    <textarea name="part_1_notes" class="form-control border-0 p-3" rows="8" placeholder="Enter preliminary notes, Markdown supported...
e.g. Call to Order, Roll Call, Overview of Positions..." style="resize:vertical;"></textarea>
                </div>
            </div>

            <!-- Part II: Deliberation Matrix -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius:12px;">
                <div class="card-header bg-primary text-white fw-bold" style="border-radius:12px 12px 0 0;">
                    Part II: Deliberation Matrix
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Applicant (Qualified Only)</th>
                                    <th>Category Tag</th>
                                    <th>Action Taken / Board Resolution</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($matrix as $office => $positions)
                                    <tr class="table-secondary">
                                        <td colspan="3" class="fw-bold"><i class="bi bi-building me-1"></i> Office: {{ $office }}</td>
                                    </tr>
                                    @foreach($positions as $pos => $details)
                                        <tr class="table-info">
                                            <td colspan="3" class="fw-bold ps-4">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <i class="bi bi-briefcase-fill me-1"></i> Vacancy: {{ $pos }}
                                                        <span class="ms-3 text-muted" style="font-size:13px; font-weight:normal;">(Item No: {{ $details['item_no'] ?? 'N/A' }})</span>
                                                    </div>
                                                    <div>
                                                        <span class="badge bg-primary me-2" style="font-size:12px;">SG-{{ $details['sg'] ?? 'N/A' }}</span>
                                                        <span class="badge bg-success" style="font-size:12px;">₱ {{ number_format($details['rate'], 2) }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <input type="hidden" name="matrix_data[{{ $office }}][{{ $pos }}][item_no]" value="{{ $details['item_no'] }}">
                                        <input type="hidden" name="matrix_data[{{ $office }}][{{ $pos }}][sg]" value="{{ $details['sg'] }}">
                                        <input type="hidden" name="matrix_data[{{ $office }}][{{ $pos }}][rate]" value="{{ $details['rate'] }}">
                                        @foreach($details['applicants'] as $idx => $app)
                                            <tr>
                                                <td class="ps-5">
                                                    {{ $app['name'] }}
                                                    <input type="hidden" name="matrix_data[{{ $office }}][{{ $pos }}][applicants][{{ $idx }}][id]" value="{{ $app['id'] }}">
                                                    <input type="hidden" name="matrix_data[{{ $office }}][{{ $pos }}][applicants][{{ $idx }}][name]" value="{{ $app['name'] }}">
                                                    <input type="hidden" name="matrix_data[{{ $office }}][{{ $pos }}][applicants][{{ $idx }}][tag]" value="{{ $app['tag'] }}">
                                                </td>
                                                <td>
                                                    @if($app['tag'] === 'Job Order')
                                                        <span class="badge bg-warning text-dark">Job Order</span>
                                                    @elseif($app['tag'] === 'Exempted')
                                                        <span class="badge bg-success">Exempted</span>
                                                    @else
                                                        <span class="badge bg-secondary">External</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <input type="text" name="matrix_data[{{ $office }}][{{ $pos }}][applicants][{{ $idx }}][resolution]" class="form-control form-control-sm" placeholder="Enter board resolution...">
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">No qualified applicants found for the selected criteria.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <button type="submit" name="action" value="save" class="btn btn-outline-primary fw-bold"><i class="bi bi-save"></i> Save Agenda</button>
                <button type="submit" name="action" value="print" class="btn btn-primary fw-bold"><i class="bi bi-printer"></i> Save & Print PDF</button>
            </div>
        </form>
    </div>

    <!-- Include Select2 for multi-select -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2-multiple').select2({
                placeholder: "Select options",
                allowClear: true
            });
        });
    </script>
</x-dashboard-app>
