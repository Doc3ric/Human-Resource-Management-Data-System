<x-dashboard-app>
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('salary-schedules.index') }}" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Create New SSL Schedule</h1>
            <p class="text-gray-500 text-sm">Define a new Salary Standardization Law tranche with its full salary grade matrix.</p>
        </div>
    </div>

    {{-- Errors --}}
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <p class="text-red-700 font-medium text-sm mb-1">Please fix the following errors:</p>
        <ul class="list-disc list-inside text-red-600 text-sm space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form id="create-schedule-form" method="POST" action="{{ route('salary-schedules.store') }}" class="space-y-6">
        @csrf

        {{-- Schedule Details --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">
                <i class="bi bi-info-circle me-2 text-blue-600"></i>Schedule Information
            </h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Schedule Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        placeholder="e.g. SSL VI – Tranche 1"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Law / EO Reference</label>
                    <input type="text" name="law_name" value="{{ old('law_name') }}"
                        placeholder="e.g. R.A. 11466"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">LBC # <span class="text-gray-400">(Local Budget Circular)</span></label>
                    <input type="text" name="lbc_number" value="{{ old('lbc_number') }}"
                        placeholder="e.g. LBC 165"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Effective Date</label>
                    <input type="date" name="effective_date" value="{{ old('effective_date') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                    <input type="text" name="description" value="{{ old('description') }}"
                        placeholder="Optional notes..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
            </div>

            <div class="mt-4 flex items-center gap-3 p-3 rounded-lg" style="background:#fffbeb;border:1px solid #fde68a;">
                <input type="checkbox" name="set_active" id="set_active" value="1"
                    class="rounded w-4 h-4" {{ old('set_active') ? 'checked' : '' }}>
                <label for="set_active" class="text-sm font-medium cursor-pointer" style="color:#92400e;">
                    <i class="bi bi-star-fill me-1" style="color:#d97706;"></i>
                    Set this as the <strong>Active Schedule</strong> immediately after creating it
                </label>
            </div>
        </div>

        {{-- Salary Matrix --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-100">
                <h2 class="text-base font-semibold text-gray-800">
                    <i class="bi bi-table me-2 text-blue-600"></i>Salary Grade Matrix (Grades 1–33 × Steps 1–8)
                </h2>
                @if($activeSchedule)
                    <span class="text-xs text-gray-500 bg-gray-50 px-3 py-1.5 rounded-md border border-gray-200">
                        Pre-filled from: <strong>{{ $activeSchedule->name }}</strong>
                    </span>
                @endif
            </div>

            <div class="alert alert-info d-flex align-items-start border-0 shadow-sm mb-4" style="background:#eff6ff;" role="alert">
                <i class="bi bi-info-circle-fill text-primary fs-6 me-2 mt-1"></i>
                <div class="text-sm">
                    Enter the <strong>monthly salary</strong> for each Grade/Step combination.
                    @if($activeSchedule)
                        Values are pre-filled from the current active schedule (<strong>{{ $activeSchedule->name }}</strong>) — adjust as needed.
                    @endif
                </div>
            </div>

            <div class="table-responsive" style="max-height: 65vh; overflow-y: auto;">
                <table class="table table-hover table-borderless mb-0 align-middle text-center" style="min-width: 1200px;">
                    <thead class="bg-light border-bottom">
                        <tr>
                            <th class="py-3 px-4 text-secondary text-uppercase fw-bold" style="font-size:.75rem; width:90px; position:sticky; left:0; top:0; background:#f8f9fa; z-index:10; border-right:1px solid #dee2e6; box-shadow:1px 0 0 #dee2e6, 0 1px 0 #dee2e6;">
                                Grade
                            </th>
                            @for($step = 1; $step <= 8; $step++)
                                <th class="py-3 text-secondary text-uppercase fw-bold" style="font-size:.75rem; width:140px; position:sticky; top:0; background:#f8f9fa; z-index:5; border-bottom:1px solid #dee2e6; box-shadow:0 1px 0 #dee2e6;">
                                    Step {{ $step }}
                                </th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @for($grade = 1; $grade <= 33; $grade++)
                            <tr>
                                <td class="py-2 px-4 fw-bold text-dark" style="position:sticky; left:0; background:#fff; z-index:5; border-right:1px solid #dee2e6; box-shadow:1px 0 0 #dee2e6;">
                                    SG {{ $grade }}
                                </td>
                                @for($step = 1; $step <= 8; $step++)
                                    @php $val = old("grade_{$grade}_step_{$step}", $matrix[$grade][$step] ?? ''); @endphp
                                    <td class="py-2 px-2">
                                        <div class="input-group input-group-sm rounded-3 shadow-sm border" style="background:#fff; overflow:hidden; display:flex;">
                                            <span class="input-group-text bg-transparent text-secondary border-0 px-2" style="font-size:.8rem; display:flex; align-items:center; justify-content:center; width:30px;">₱</span>
                                            <input type="number"
                                                   step="0.01"
                                                   min="0"
                                                   name="grade_{{ $grade }}_step_{{ $step }}"
                                                   value="{{ $val }}"
                                                   class="form-control border-0 ps-0 text-dark"
                                                   placeholder="0.00"
                                                   style="box-shadow:none; font-family:monospace; font-size:.85rem; height:32px;">
                                        </div>
                                    </td>
                                @endfor
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex gap-3 justify-end">
            <a href="{{ route('salary-schedules.index') }}"
               class="px-5 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                Cancel
            </a>
            <button type="button" onclick="confirmCreate()"
               class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition">
                <i class="bi bi-save me-1"></i> Save Schedule
            </button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmCreate() {
    const name    = document.querySelector('input[name="name"]').value.trim();
    const active  = document.getElementById('set_active').checked;

    if (!name) {
        Swal.fire({ title: 'Missing Name', text: 'Please enter a schedule name.', icon: 'warning' });
        return;
    }

    Swal.fire({
        title: 'Save Salary Schedule?',
        html: `Create schedule <strong>"${name}"</strong>?` + (active ? '<br><span style="color:#16a34a;font-weight:600;"><i class="bi bi-star-fill"></i> Will be set as Active immediately.</span>' : ''),
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Save it!',
        cancelButtonText: 'Cancel',
        customClass: { popup: 'rounded-xl' }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('create-schedule-form').submit();
        }
    });
}
</script>
</x-dashboard-app>
