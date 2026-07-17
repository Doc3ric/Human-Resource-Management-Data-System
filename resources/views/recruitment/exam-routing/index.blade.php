<x-dashboard-app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Examination Routing (Module 5.4)
        </h2>
    </x-slot>

    <div class="content-wrapper p-4">
        @if(session('success'))
            <div style="background:#f0fdf4;color:#166534;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:13px;">{{ session('success') }}</div>
        @endif

        <!-- TOP FILTER & REPORTS BAR -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('recruitment.exam-routing.index') }}" class="row g-2 align-items-end">
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label" style="font-size:12px;">From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom->format('Y-m-d') }}" max="{{ $dateTo->format('Y-m-d') }}">
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label" style="font-size:12px;">To</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo->format('Y-m-d') }}" min="{{ $dateFrom->format('Y-m-d') }}">
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label" style="font-size:12px;">Classification</label>
                        <select name="classification" class="form-select form-select-sm">
                            <option value="">Both (PGB JO & Outsider)</option>
                            <option value="pgb_jo" {{ ($filters['classification'] ?? '') == 'pgb_jo' ? 'selected' : '' }}>PGB Job Order</option>
                            <option value="external" {{ ($filters['classification'] ?? '') == 'external' ? 'selected' : '' }}>Outsider (External)</option>
                            <option value="exempt" {{ ($filters['classification'] ?? '') == 'exempt' ? 'selected' : '' }}>Exempted</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label" style="font-size:12px;">Salary Grade</label>
                        <input type="number" name="salary_grade" class="form-control form-control-sm" placeholder="Any" min="1" max="33" value="{{ $filters['salary_grade'] ?? '' }}">
                    </div>
                    <div class="col-lg-1 col-md-4">
                        <label class="form-label" style="font-size:12px;">Table Size</label>
                        <input type="number" name="table_size" class="form-control form-control-sm" value="{{ $tableSize }}" min="1">
                    </div>
                    
                    <div class="col-lg-3 col-md-12 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary flex-grow-1"><i class="bi bi-funnel"></i> Filter</button>
                        
                        <div class="dropdown flex-grow-1">
                            <button class="btn btn-sm btn-outline-dark w-100 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-journal-bookmark-fill text-primary"></i> Generated Reports
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size: 13px; max-height: 400px; overflow-y: auto;">
                                @if($savedSchedules->isEmpty())
                                    <li><span class="dropdown-item text-muted">No schedules generated</span></li>
                                @else
                                    @foreach($savedSchedules as $schedule)
                                        <li><h6 class="dropdown-header text-primary fw-bold">{{ $schedule->classification === 'pgb_jo' ? 'PGB Job Order' : 'Outsider' }} — {{ $schedule->exam_date ? $schedule->exam_date->format('M d, Y') : 'N/A' }}</h6></li>
                                        <li><a class="dropdown-item" href="{{ route('recruitment.exam-routing.export.layout-a', ['classification' => $schedule->classification, 'date' => $schedule->exam_date?->format('Y-m-d') ?? 'none', 'room' => $schedule->room ?: 'none', 'time' => $schedule->exam_time ?: 'none']) }}" target="_blank"><i class="bi bi-file-earmark-person-fill text-muted me-1"></i> Layout A (Secretariat)</a></li>
                                        <li><a class="dropdown-item" href="{{ route('recruitment.exam-routing.export.layout-b', ['classification' => $schedule->classification, 'date' => $schedule->exam_date?->format('Y-m-d') ?? 'none', 'room' => $schedule->room ?: 'none', 'time' => $schedule->exam_time ?: 'none']) }}" target="_blank"><i class="bi bi-file-earmark-lock-fill text-muted me-1"></i> Layout B (Public Copy)</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @php
            $groupsToShow = [];
            $selectedClass = $filters['classification'] ?? '';
            if ($selectedClass === '' || $selectedClass === 'pgb_jo') {
                $groupsToShow['pgb_jo'] = ['label' => 'PGB Job Order', 'tables' => $pgbJo];
            }
            if ($selectedClass === '' || $selectedClass === 'external') {
                $groupsToShow['external'] = ['label' => 'Outsider (External)', 'tables' => $external];
            }
        @endphp

        @foreach($groupsToShow as $classification => $group)
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">{{ $group['label'] }} — {{ $group['tables']->flatten(1)->count() }} applicant(s), {{ $group['tables']->count() }} table(s)</h5>
                    </div>

                    <form method="POST" action="{{ route('recruitment.exam-routing.generate') }}" class="row g-2 align-items-end mb-3 bg-light p-3 rounded border">
                        @csrf
                        <input type="hidden" name="classification" value="{{ $classification }}">
                        <input type="hidden" name="date_from" value="{{ $dateFrom->format('Y-m-d') }}">
                        <input type="hidden" name="date_to" value="{{ $dateTo->format('Y-m-d') }}">
                        <input type="hidden" name="table_size" value="{{ $tableSize }}">
                        <input type="hidden" name="salary_grade" value="{{ $filters['salary_grade'] ?? '' }}">
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:12px;">Exam Date</label>
                            <input type="date" name="exam_date" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:12px;">Time</label>
                            <input type="time" name="exam_time" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" style="font-size:12px;">Room</label>
                            <input type="text" name="room" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-calendar-plus"></i> Generate Schedule</button>
                        </div>
                    </form>

                    <div style="max-height: 400px; overflow-y: auto; padding-right: 10px;">
                        @foreach($group['tables'] as $index => $table)
                            <div class="mb-3 p-3 bg-white rounded border">
                                <strong class="text-primary mb-2 d-block">Table {{ $index + 1 }}</strong>
                                <ul class="list-unstyled mb-0" style="font-size:12.5px;">
                                    @foreach($table as $app)
                                        <li class="mb-1"><i class="bi bi-person-fill text-secondary"></i> <strong>{{ $app->last_name }}, {{ $app->first_name }}</strong> <br><span class="text-muted ms-3">— {{ $app->position_applied }} (SG-{{ $app->salary_grade_snapshot }})</span></li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                        @if($group['tables']->isEmpty())
                            <p class="text-muted mb-0 py-3 text-center border rounded bg-light">No applicants in this category match the filters.</p>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

        @if($selectedClass === 'exempt' || $selectedClass === '')
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h5 class="fw-bold text-success"><i class="bi bi-shield-check"></i> Exempt from Examination</h5>
                <p class="text-muted" style="font-size:12px;">Exempted Special Positions under 2025 ORAOHRA (Advances to Technical/Board Assessment).</p>
                
                <div style="max-height: 400px; overflow-y: auto; padding-right: 10px;">
                    <ul class="list-unstyled mb-0" style="font-size:13px;">
                        @foreach($exempt as $app)
                            <li class="d-flex justify-content-between align-items-center mb-2 p-2 border-bottom">
                                <div>
                                    <strong>{{ $app->last_name }}, {{ $app->first_name }}</strong><br>
                                    <span class="text-muted">{{ $app->position_applied }} (SG-{{ $app->salary_grade_snapshot }})</span>
                                </div>
                                <form method="POST" action="{{ route('recruitment.exam-routing.toggle-exempt', $app) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove Exemption"><i class="bi bi-x-circle"></i></button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                    @if($exempt->isEmpty())
                        <p class="text-muted mb-0 py-3 text-center border rounded bg-light">No exempted applicants in this date range.</p>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
</x-dashboard-app>
