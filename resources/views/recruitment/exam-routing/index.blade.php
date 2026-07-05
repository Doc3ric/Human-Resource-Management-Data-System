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

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('recruitment.exam-routing.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Window (months)</label>
                        <input type="number" name="window_months" class="form-control" value="{{ $windowMonths }}" min="1">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Table Size (max)</label>
                        <input type="number" name="table_size" class="form-control" value="{{ $tableSize }}" min="1">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary w-100">Apply</button>
                    </div>
                </form>
            </div>
        </div>

        @foreach(['pgb_jo' => ['label' => 'PGB Job Order', 'tables' => $pgbJo], 'external' => ['label' => 'External', 'tables' => $external]] as $classification => $group)
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">{{ $group['label'] }} — {{ $group['tables']->flatten(1)->count() }} applicant(s), {{ $group['tables']->count() }} table(s)</h5>
                    </div>

                    <form method="POST" action="{{ route('recruitment.exam-routing.generate') }}" class="row g-2 align-items-end mb-3">
                        @csrf
                        <input type="hidden" name="classification" value="{{ $classification }}">
                        <input type="hidden" name="window_months" value="{{ $windowMonths }}">
                        <input type="hidden" name="table_size" value="{{ $tableSize }}">
                        <div class="col-md-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="exam_date" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Time</label>
                            <input type="time" name="exam_time" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Room</label>
                            <input type="text" name="room" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-sm w-100">Generate</button>
                        </div>
                    </form>

                    @foreach($group['tables'] as $index => $table)
                        <div class="mb-2">
                            <strong>Table {{ $index + 1 }}</strong>
                            <ul class="list-unstyled mb-0" style="font-size:12.5px;">
                                @foreach($table as $app)
                                    <li>{{ $app->last_name }}, {{ $app->first_name }} — {{ $app->position_applied }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                    @if($group['tables']->isEmpty())
                        <p class="text-muted mb-0">No applicants in this window.</p>
                    @endif
                </div>
            </div>
        @endforeach

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h5 class="fw-bold">Exempt from Examination — Advances to Technical/Board Assessment</h5>
                <p class="text-muted" style="font-size:12px;">Exempted Special Positions under 2025 ORAOHRA (licensed Medical Doctors, Legal Officers, specialized IT, or board-exempted).</p>
                <ul class="list-unstyled mb-0" style="font-size:12.5px;">
                    @foreach($exempt as $app)
                        <li class="d-flex justify-content-between align-items-center mb-1">
                            <span>{{ $app->last_name }}, {{ $app->first_name }} — {{ $app->position_applied }}</span>
                            <form method="POST" action="{{ route('recruitment.exam-routing.toggle-exempt', $app) }}">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary">Remove Exemption</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
                @if($exempt->isEmpty())
                    <p class="text-muted mb-0">No exempted applicants in this window.</p>
                @endif
            </div>
        </div>
    </div>
</x-dashboard-app>
