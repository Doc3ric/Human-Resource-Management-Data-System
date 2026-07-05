<x-dashboard-app>
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between border-bottom pb-4 mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">
                <i class="bi bi-layers me-2 text-primary"></i>SSL Salary Schedules (Tranches)
            </h1>
            <p class="text-muted small mb-0">Manage Salary Standardization Law tranches. Set the active schedule to automatically drive all salary calculations system-wide.</p>
        </div>
        <a href="{{ route('salary-schedules.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2 shadow-sm px-4 py-2 fw-medium rounded-3">
            <i class="bi bi-plus-circle"></i> Create New Schedule
        </a>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center mb-4 border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill fs-5 me-3"></i>
            <div class="fw-medium">{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center mb-4 border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-3"></i>
            <div class="fw-medium">{{ session('error') }}</div>
        </div>
    @endif

    {{-- Enhanced Stats with compliance analysis --}}
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px;">
        <x-stat-card icon="bi-check-circle-fill" color="blue"
            label="Active Schedule"
            :value="1"
            sub="{{ $activeSchedule ? $activeSchedule->name.' ('.$activeSchedule->effective_date?->format('M Y').')' : 'No active schedule set' }}"
            compliance="DBM / EO 201"
            analysis="{{ $activeSchedule ? 'Active SSL: '.($activeSchedule->law_name ?? 'N/A').'. All salary calculations system-wide use this schedule. Switching requires PHRMO and DBM coordination.' : 'No active schedule. Set an active SSL tranche to drive salary computations across all modules.' }}" />

        <x-stat-card icon="bi-cash-stack" color="green"
            label="Annual Budget Requirement"
            :value="(int) $budgetTotal"
            sub="Total PS cost — filled positions (₱)"
            compliance="DBM / GAA"
            analysis="Total annual Personal Services (PS) cost for all filled plantilla positions. Must align with the approved GAA/budget ceiling for the province. Excess requires DBM approval." />

        <x-stat-card icon="bi-layers-fill" color="purple"
            label="SSL Tranches on Record"
            :value="$schedules->count()"
            sub="Historical SSL schedules"
            compliance="SSL Law Series"
            analysis="Total SSL tranches configured. Only ONE schedule may be active at a time. Historical schedules are retained for audit, retroactive computation, and back-pay verification." />
    </div>

    {{-- Summary Cards --}}
    <div class="grid sm:grid-cols-3 gap-4 mb-2" style="display:none!important">
        {{-- Active Schedule Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background:#eff6ff;">
                    <i class="bi bi-check-circle-fill text-blue-600" style="font-size:1.4rem;"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-medium uppercase" style="letter-spacing:.05em;">Active Schedule</p>
                    @if($activeSchedule)
                        <p class="font-bold text-gray-800 text-sm mt-0.5">{{ $activeSchedule->name }}</p>
                        <p class="text-xs text-gray-500">{{ $activeSchedule->law_name ?? '—' }}
                            @if($activeSchedule->effective_date)
                                &mdash; {{ $activeSchedule->effective_date->format('M d, Y') }}
                            @endif
                        </p>
                    @else
                        <p class="text-gray-500 text-sm mt-0.5 italic">No active schedule</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Budget Requirement Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background:#f0fdf4;">
                    <i class="bi bi-cash-stack text-green-600" style="font-size:1.4rem;"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-medium uppercase" style="letter-spacing:.05em;">Budget Requirement</p>
                    <p class="font-bold text-gray-800" style="font-size:1.1rem;">
                        ₱{{ number_format($budgetTotal, 2) }}
                    </p>
                    <p class="text-xs text-gray-500">Total annual (filled positions)</p>
                </div>
            </div>
        </div>

        {{-- Total Schedules Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background:#f3e8ff;">
                    <i class="bi bi-layers text-purple-600" style="font-size:1.4rem;"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-medium uppercase" style="letter-spacing:.05em;">Total Schedules</p>
                    <p class="font-bold text-gray-800" style="font-size:1.5rem;">{{ $schedules->count() }}</p>
                    <p class="text-xs text-gray-500">SSL tranches on record</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Schedules Table --}}
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        @if($schedules->isEmpty())
            <div class="text-center py-12">
                <i class="bi bi-layers" style="font-size:3rem;color:#d1d5db;"></i>
                <p class="text-gray-500 mt-3 font-medium">No salary schedules yet.</p>
                <p class="text-gray-400 text-sm mb-4">Create your first SSL tranche to get started.</p>
                <a href="{{ route('salary-schedules.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Create New Schedule
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light border-bottom">
                        <tr>
                            <th class="py-3 px-4 text-uppercase fw-bold text-secondary" style="font-size:.75rem;">Schedule Name</th>
                            <th class="py-3 text-uppercase fw-bold text-secondary" style="font-size:.75rem;">Law / EO</th>
                            <th class="py-3 text-uppercase fw-bold text-secondary" style="font-size:.75rem;">LBC #</th>
                            <th class="py-3 text-uppercase fw-bold text-secondary" style="font-size:.75rem;">Effective Date</th>
                            <th class="py-3 text-uppercase fw-bold text-secondary" style="font-size:.75rem;">Entries</th>
                            <th class="py-3 text-uppercase fw-bold text-secondary" style="font-size:.75rem;">Status</th>
                            <th class="py-3 text-uppercase fw-bold text-secondary text-center" style="font-size:.75rem;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schedules as $schedule)
                        <tr class="{{ $schedule->is_active ? 'table-primary' : '' }}">
                            <td class="px-4 py-3">
                                <div class="fw-semibold text-dark">{{ $schedule->name }}</div>
                                @if($schedule->description)
                                    <div class="text-muted" style="font-size:.8rem;">{{ \Illuminate\Support\Str::limit($schedule->description, 80) }}</div>
                                @endif
                            </td>
                            <td class="py-3">
                                <span class="badge bg-light text-dark border fw-medium" style="font-size:.75rem;">
                                    {{ $schedule->law_name ?? '—' }}
                                </span>
                            </td>
                            <td class="py-3">
                                @if($schedule->lbc_number)
                                    <span class="badge border fw-medium" style="font-size:.75rem;background:#fff1f2;color:#be123c;border-color:#fecdd3;">
                                        {{ $schedule->lbc_number }}
                                    </span>
                                @else
                                    <span class="text-muted" style="font-size:.8rem;">—</span>
                                @endif
                            </td>
                            <td class="py-3">
                                {{ $schedule->effective_date ? $schedule->effective_date->format('M d, Y') : '—' }}
                            </td>
                            <td class="py-3">
                                <span class="fw-semibold">{{ $schedule->salaryGrades()->count() }}</span>
                                <span class="text-muted" style="font-size:.8rem;"> rows</span>
                            </td>
                            <td class="py-3">
                                @if($schedule->is_active)
                                    <span class="badge" style="background:#16a34a;color:white;padding:5px 10px;border-radius:99px;font-size:.75rem;">
                                        <i class="bi bi-check-circle-fill me-1"></i>Active
                                    </span>
                                @else
                                    <span class="badge bg-light text-secondary border" style="padding:5px 10px;border-radius:99px;font-size:.75rem;">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 text-center">
                                <div class="d-inline-flex gap-2 flex-wrap justify-content-center">
                                    {{-- Edit --}}
                                    <a href="{{ route('salary-schedules.edit', $schedule) }}"
                                       class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1"
                                       title="Edit this schedule">
                                        <i class="bi bi-pencil-fill"></i> Edit
                                    </a>

                                    {{-- Set Active --}}
                                    @if(!$schedule->is_active)
                                        <form method="POST" action="{{ route('salary-schedules.set-active', $schedule) }}" class="d-inline" id="set-active-form-{{ $schedule->id }}">
                                            @csrf
                                            <button type="button"
                                                class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1"
                                                title="Set as Active Schedule"
                                                onclick="confirmSetActive({{ $schedule->id }}, '{{ addslashes($schedule->name) }}')">
                                                <i class="bi bi-check2-circle"></i> Set Active
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Apply to All Plantilla --}}
                                    <form method="POST" action="{{ route('salary-schedules.apply-to-plantilla', $schedule) }}" class="d-inline" id="apply-form-{{ $schedule->id }}">
                                        @csrf
                                        <button type="button"
                                            class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1"
                                            title="Apply this schedule's rates to all plantilla records"
                                            onclick="confirmApply({{ $schedule->id }}, '{{ addslashes($schedule->name) }}')">
                                            <i class="bi bi-lightning-charge-fill"></i> Apply to Plantilla
                                        </button>
                                    </form>

                                    {{-- Archive --}}
                                    @if(!$schedule->is_active)
                                        <form method="POST" action="{{ route('salary-schedules.destroy', $schedule) }}" class="d-inline" id="archive-form-{{ $schedule->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                class="btn btn-sm btn-outline-warning d-inline-flex align-items-center gap-1"
                                                onclick="confirmArchive({{ $schedule->id }}, '{{ addslashes($schedule->name) }}')">
                                                <i class="bi bi-archive"></i> Archive
                                            </button>
                                        </form>
                                    @else
                                        <button class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1" disabled title="Cannot archive the active schedule">
                                            <i class="bi bi-lock"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Tip --}}
    <div class="alert d-flex align-items-start mb-2 border-0 shadow-sm" style="background:#eff6ff; border-left:4px solid #3b82f6 !important;" role="alert">
        <i class="bi bi-info-circle-fill text-primary fs-5 me-3 mt-1"></i>
        <div class="text-sm">
            <strong>How SSL Tranches Work:</strong><br>
            Create a new schedule for each SSL tranche (e.g. SSL VI Tranche 1, Tranche 2). Click <strong>Set Active</strong> to make a schedule the system-wide default. Then click <strong>Apply to Plantilla</strong> to instantly update every employee's <em>actual</em> and <em>authorized</em> annual salary based on that schedule's matrix.
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmApply(id, name) {
    Swal.fire({
        title: 'Apply Schedule to All Plantilla?',
        html: `This will update <strong>actual</strong> and <strong>authorized annual salary</strong> for every non-vacant plantilla record using the rates in <strong>"${name}"</strong>.<br><br>This is a bulk operation and cannot be undone individually.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#16a34a',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="bi bi-lightning-charge-fill me-1"></i> Yes, Apply Now!',
        cancelButtonText: 'Cancel',
        customClass: { popup: 'rounded-xl' }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('apply-form-' + id).submit();
        }
    });
}

function confirmSetActive(id, name) {
    Swal.fire({
        title: 'Set as Active Schedule?',
        html: `Make <strong>"${name}"</strong> the active salary schedule?<br><br>This will become the master source for all future salary lookups and matrix synchronizations.`,
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="bi bi-check2-circle me-1"></i> Set Active',
        cancelButtonText: 'Cancel',
        customClass: { popup: 'rounded-xl' }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('set-active-form-' + id).submit();
        }
    });
}

function confirmArchive(id, name) {
    Swal.fire({
        title: 'Archive Schedule?',
        html: `Archive <strong>"${name}"</strong>?<br><br>This will safely move the schedule and all its underlying matrix records into the Archives. You can restore it later.`,
        icon: 'warning',
        iconColor: '#f59e0b',
        showCancelButton: true,
        confirmButtonColor: '#f59e0b',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="bi bi-archive me-1"></i> Archive Schedule',
        cancelButtonText: 'Cancel',
        customClass: { popup: 'rounded-xl' }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('archive-form-' + id).submit();
        }
    });
}
</script>
</x-dashboard-app>
