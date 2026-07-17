<x-dashboard-app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            HRMPSB Deliberation
        </h2>
    </x-slot>

    <div class="content-wrapper p-4">
        @if($errors->has('access'))
            <div class="alert alert-danger fw-bold shadow-sm mb-4">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ $errors->first('access') }}
            </div>
        @endif

        <!-- Filter Bar -->
        <div class="card shadow-sm border-0 mb-4" style="background-color: #f8f9fa;">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('recruitment.deliberation.list') }}" class="row g-2 align-items-center">
                    <div class="col-md-5">
                        <div class="d-flex align-items-center">
                            <label class="form-label mb-0 fw-semibold me-2 text-nowrap">Office:</label>
                            <select name="office" id="officeSelect" class="form-select form-select-sm" required>
                                <option value="">-- Select Office --</option>
                                @php
                                    $offices = $officePositions->pluck('office')->unique()->sort();
                                @endphp
                                @foreach($offices as $optOffice)
                                    <option value="{{ $optOffice }}" {{ $office === $optOffice ? 'selected' : '' }}>{{ $optOffice }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="d-flex align-items-center">
                            <label class="form-label mb-0 fw-semibold me-2 text-nowrap">Position:</label>
                            <select name="position_applied" id="positionSelect" class="form-select form-select-sm" required {{ !$office ? 'disabled' : '' }}>
                                <option value="">-- Select Position --</option>
                                @if($office)
                                    @php
                                        $positions = $officePositions->where('office', $office)->pluck('position_applied')->unique()->sort();
                                    @endphp
                                    @foreach($positions as $optPosition)
                                        <option value="{{ $optPosition }}" {{ $position_applied === $optPosition ? 'selected' : '' }}>{{ $optPosition }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold"><i class="bi bi-filter"></i> Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Store mapping data as JSON for the JS to consume -->
        <script id="officePositionsData" type="application/json">
            {!! $officePositions->toJson() !!}
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const dataStr = document.getElementById('officePositionsData').textContent;
                const officePositions = JSON.parse(dataStr);
                
                const officeSelect = document.getElementById('officeSelect');
                const positionSelect = document.getElementById('positionSelect');

                officeSelect.addEventListener('change', () => {
                    const selectedOffice = officeSelect.value;
                    
                    // Clear positions
                    positionSelect.innerHTML = '<option value="">-- Select Position --</option>';
                    
                    if (!selectedOffice) {
                        positionSelect.disabled = true;
                        return;
                    }

                    // Filter positions for this office
                    const positions = officePositions
                        .filter(op => op.office === selectedOffice)
                        .map(op => op.position_applied)
                        .filter((value, index, self) => self.indexOf(value) === index) // unique
                        .sort();

                    positions.forEach(pos => {
                        const option = document.createElement('option');
                        option.value = pos;
                        option.textContent = pos;
                        positionSelect.appendChild(option);
                    });

                    positionSelect.disabled = false;
                });
            });
        </script>

        @if($office && $position_applied)
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="mb-0 fw-bold">{{ $position_applied }}</h4>
                    <p class="text-muted small mb-0"><i class="bi bi-building"></i> {{ $office }}</p>
                </div>
                <div>
                    @php
                        // Since $position_applied might be a raw string instead of a key in the list view,
                        // let's derive the key safely if needed, or rely on $position_applied if it's already the key.
                        $listPositionKey = \App\Support\Recruitment\VacancyIdentifier::key(null, $position_applied);
                    @endphp
                    <a href="{{ route('recruitment.report', ['report_type' => 'preeval', 'office' => $office, 'item_no' => $listPositionKey]) }}" target="_blank" class="btn btn-warning text-dark fw-bold shadow-sm">
                        <i class="bi bi-file-earmark-pdf-fill me-1"></i> Print Pre-Eval Matrix
                    </a>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Applicant</th>
                                    <th>Item No.</th>
                                    <th>Status / Phase</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($applicants as $app)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($app->photo_url)
                                                    <img src="{{ Storage::url($app->photo_url) }}" alt="Photo" class="rounded-circle me-3" style="width: 40px; height: 40px; object-fit: cover; border: 1px solid #ccc;">
                                                @else
                                                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">
                                                        {{ substr($app->first_name, 0, 1) }}{{ substr($app->last_name, 0, 1) }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $app->last_name }}, {{ $app->first_name }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $app->item_no ?: 'None' }}</span>
                                        </td>
                                        <td>
                                            @if($app->deliberation_phase === 'completed')
                                                <span class="badge bg-success">Completed</span>
                                            @elseif($app->deliberation_phase === 'hrmpsb_deliberation')
                                                <span class="badge bg-primary">HRMPSB Deliberation</span>
                                            @elseif($app->deliberation_phase === 'twg_evaluation')
                                                <span class="badge bg-warning text-dark">TWG Evaluation</span>
                                            @else
                                                <span class="badge bg-secondary">Screening</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('recruitment.deliberation.show', $app->id) }}" class="btn btn-sm btn-primary fw-bold">
                                                Open Workspace <i class="bi bi-box-arrow-up-right"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                            No applicants found for this position in the selected office.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-5 text-muted">
                <i class="bi bi-funnel fs-1 d-block mb-3" style="opacity: 0.5;"></i>
                <h5 class="fw-bold">Select Office & Position</h5>
                <p class="small">Use the filter bar above to select the office and position to view the applicants for deliberation.</p>
            </div>
        @endif
    </div>
</x-dashboard-app>
