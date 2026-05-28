<x-dashboard-app>
    <div class="max-w-7xl mx-auto space-y-6">
        
        {{-- Header --}}
        <div class="d-flex align-items-center justify-content-between border-bottom pb-4 mb-4">
            <div>
                <h1 class="h3 fw-bold text-dark mb-1">Salary Grade Table Management</h1>
                <p class="text-muted small mb-0">Configure the monthly base salary for each grade and step. This is used system-wide for automatically calculating employee salaries.</p>
            </div>
            <div>
                <button type="submit" form="salary-grades-form" class="btn btn-primary d-inline-flex align-items-center gap-2 shadow-sm px-4 py-2 text-sm fw-medium rounded-3 transition">
                    <i class="bi bi-save"></i>
                    Save All Changes
                </button>
            </div>
        </div>

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="alert alert-success d-flex align-items-center mb-4 border-0 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill fs-5 me-3"></i>
                <div class="fw-medium">
                    {{ session('success') }}
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger d-flex align-items-center mb-4 border-0 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-3"></i>
                <div class="fw-medium">
                    {{ session('error') }}
                </div>
            </div>
        @endif
        
        <div class="alert alert-primary d-flex align-items-start mb-4 border-0 shadow-sm" style="background-color: #eff6ff; border-left: 4px solid #3b82f6 !important;" role="alert">
            <i class="bi bi-info-circle-fill text-primary fs-5 me-3 mt-1"></i>
            <div>
                <strong>Important Note:</strong><br>
                Changing the values here will apply to any newly created/edited plantilla records and step increments. However, to recalculate all <em>existing</em> employee salaries in the inventory, you must click the <strong>"Sync Salaries"</strong> button in the All Data section after saving your changes here.
            </div>
        </div>

        {{-- Active Schedule Banner --}}
        @if($activeSchedule)
            <div class="alert d-flex align-items-center justify-content-between mb-4 border-0 shadow-sm" style="background:#f0fdf4; border-left:4px solid #16a34a !important;" role="alert">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill text-success fs-5 me-2"></i>
                    <div>
                        <span class="fw-bold text-success">Editing Active Schedule:</span>
                        <span class="fw-semibold text-dark ms-1">{{ $activeSchedule->name }}</span>
                        @if($activeSchedule->law_name)
                            <span class="badge bg-light text-secondary border ms-2" style="font-size:.7rem;">{{ $activeSchedule->law_name }}</span>
                        @endif
                        @if($activeSchedule->effective_date)
                            <span class="text-muted ms-2" style="font-size:.8rem;">Effective: {{ $activeSchedule->effective_date->format('M d, Y') }}</span>
                        @endif
                    </div>
                </div>
                <a href="{{ route('salary-schedules.index') }}" class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1">
                    <i class="bi bi-layers"></i> Manage Schedules
                </a>
            </div>
        @else
            <div class="alert d-flex align-items-center justify-content-between mb-4 border-0 shadow-sm" style="background:#fffbeb; border-left:4px solid #f59e0b !important;" role="alert">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill text-warning fs-5 me-2"></i>
                    <div>
                        <span class="fw-bold" style="color:#92400e;">No Active SSL Schedule.</span>
                        <span class="text-muted ms-1" style="font-size:.85rem;">Editing the legacy baseline. Create and activate an SSL Schedule for full tranche management.</span>
                    </div>
                </div>
                <a href="{{ route('salary-schedules.create') }}" class="btn btn-sm btn-outline-warning d-inline-flex align-items-center gap-1">
                    <i class="bi bi-plus-circle"></i> Create Schedule
                </a>
            </div>
        @endif

        <form id="salary-grades-form" action="{{ route('salary-grades.update-all') }}" method="POST">
            @csrf
            
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                <div class="table-responsive" style="max-height: 65vh; overflow-y: auto;">
                    <table class="table table-hover table-borderless mb-0 align-middle text-center" style="min-width: 1200px;">
                        <thead class="bg-light border-bottom">
                            <tr>
                                <th class="py-3 px-4 text-secondary text-uppercase fw-bold" style="font-size: 0.75rem; width: 100px; position: sticky; left: 0; top: 0; background: #f8f9fa; z-index: 10; border-right: 1px solid #dee2e6; box-shadow: 1px 0 0 #dee2e6, 0 1px 0 #dee2e6;">
                                    Grade
                                </th>
                                @for($step = 1; $step <= 8; $step++)
                                    <th class="py-3 text-secondary text-uppercase fw-bold" style="font-size: 0.75rem; width: 140px; position: sticky; top: 0; background: #f8f9fa; z-index: 5; border-bottom: 1px solid #dee2e6; box-shadow: 0 1px 0 #dee2e6;">
                                        Step {{ $step }}
                                    </th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @for($grade = 1; $grade <= 33; $grade++)
                                <tr>
                                    <td class="py-2 px-4 fw-bold text-dark" style="position: sticky; left: 0; background: #fff; z-index: 5; border-right: 1px solid #dee2e6; box-shadow: 1px 0 0 #dee2e6;">
                                        SG {{ $grade }}
                                    </td>
                                    @for($step = 1; $step <= 8; $step++)
                                        @php
                                            $val = $matrix[$grade][$step] ?? '';
                                        @endphp
                                        <td class="py-2 px-2">
                                            <div class="input-group input-group-sm rounded-3 shadow-sm border" style="background: #fff; overflow: hidden; display: flex;">
                                                <span class="input-group-text bg-transparent text-secondary border-0 px-2" style="font-size: 0.8rem; display: flex; align-items: center; justify-content: center; width: 30px;">₱</span>
                                                <input type="number" 
                                                       step="0.01" 
                                                       name="grade_{{ $grade }}_step_{{ $step }}" 
                                                       value="{{ $val }}" 
                                                       class="form-control border-0 ps-0 text-dark"
                                                       placeholder="0.00"
                                                       style="box-shadow: none; font-family: monospace; font-size: 0.85rem; height: 32px;"
                                                       required>
                                            </div>
                                        </td>
                                    @endfor
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="mt-4 mb-5 d-flex justify-content-end">
                <button type="button" onclick="confirmSave()" class="btn btn-primary d-inline-flex align-items-center gap-2 shadow-sm px-4 py-2 text-sm fw-medium rounded-3 transition">
                    <i class="bi bi-save"></i>
                    Save All Changes
                </button>
            </div>
        </form>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmSave() {
            Swal.fire({
                title: 'Save Salary Grade Updates?',
                text: "Are you sure you want to update the salary grades? This will be applied system-wide for new entries.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, save changes!',
                customClass: {
                    popup: 'rounded-xl',
                    confirmButton: 'rounded-lg',
                    cancelButton: 'rounded-lg'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('salary-grades-form').submit();
                }
            });
        }
    </script>
</x-dashboard-app>
