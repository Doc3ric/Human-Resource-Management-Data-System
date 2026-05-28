<x-dashboard-app>
    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('imports.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <div style="display:inline-flex;align-items:center;gap:6px;background:#10327c;color:#fff;font-size:12px;font-weight:700;padding:3px 10px;border-radius:99px;margin-bottom:6px;"><i class="bi bi-eye"></i> Step 3 of 3</div>
                    <h1 class="text-2xl font-bold text-gray-800">Import Preview</h1>
                    <p class="text-gray-500 text-sm">Showing first 20 valid rows — full import will process all <strong>{{ number_format($totalRows) }}</strong> records</p>
                </div>
            </div>
        </div>

        {{-- Dry Run Banner --}}
        @if($dryRun)
        <div class="bg-indigo-600 text-white rounded-lg p-4 flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <p class="font-semibold text-sm">🧪 DRY RUN MODE IS ACTIVE</p>
                <p class="text-indigo-200 text-xs mt-0.5">This import will be fully simulated. <strong>No changes will be saved</strong> to the database.</p>
            </div>
        </div>
        @endif

        {{-- Replace All Warning --}}
        @if($replaceAll)
        <div class="bg-red-50 border border-red-300 rounded-lg p-4 flex items-start gap-3">
            <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            <div>
                <p class="text-red-700 font-semibold text-sm">⚠ Replace All Mode is ON</p>
                <p class="text-red-600 text-xs mt-1">All existing plantilla records will be <strong>permanently deleted</strong> before the import runs. This cannot be undone.</p>
            </div>
        </div>
        @endif

        {{-- Duplicate Detection Warning --}}
        @if(count($duplicates) > 0)
        <div class="bg-amber-50 border border-amber-300 rounded-lg p-4">
            <div class="flex items-center gap-2 mb-2">
                <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                <p class="text-amber-800 font-semibold text-sm">{{ count($duplicates) }} item(s) already exist and will be <span class="underline">overwritten</span> (upsert mode):</p>
            </div>
            <div class="flex flex-wrap gap-1.5 max-h-28 overflow-y-auto">
                @foreach(array_slice($duplicates, 0, 60) as $dup)
                <span class="bg-amber-100 text-amber-700 text-xs font-mono px-2 py-0.5 rounded border border-amber-200">{{ $dup }}</span>
                @endforeach
                @if(count($duplicates) > 60)
                <span class="text-amber-600 text-xs self-center">…and {{ count($duplicates) - 60 }} more</span>
                @endif
            </div>
        </div>
        @endif

        {{-- Row Errors (all rows scanned) --}}
        @if(count($allErrors) > 0)
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center justify-between mb-2 cursor-pointer" onclick="this.nextElementSibling.classList.toggle('hidden')">
                <p class="text-red-700 font-semibold text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    {{ count($allErrors) }} row(s) with errors found across the entire file (will be skipped)
                </p>
                <span class="text-xs text-red-500 underline">Toggle list</span>
            </div>
            <ul class="list-disc list-inside text-red-600 text-xs space-y-0.5 max-h-40 overflow-y-auto hidden">
                @foreach($allErrors as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Preview Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 bg-gray-50 text-xs text-gray-500">
                Showing up to 20 valid rows. Rows with errors are excluded from this preview.
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="text-left px-3 py-2 font-semibold text-gray-600">#</th>
                            <th class="text-left px-3 py-2 font-semibold text-gray-600">ITEM</th>
                            <th class="text-left px-3 py-2 font-semibold text-gray-600">POSITION TITLE</th>
                            <th class="text-left px-3 py-2 font-semibold text-gray-600">EMPLOYEE</th>
                            <th class="text-center px-3 py-2 font-semibold text-gray-600">SG</th>
                            <th class="text-center px-3 py-2 font-semibold text-gray-600">STEP</th>
                            <th class="text-left px-3 py-2 font-semibold text-gray-600">STATUS</th>
                            <th class="text-center px-3 py-2 font-semibold text-gray-600">ACTION</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @php $existingItems = \App\Models\PlantillaRecord::pluck('item')->filter(fn($v) => is_string($v) || is_int($v))->mapWithKeys(fn($item) => [$item => true])->toArray(); @endphp
                        @foreach($mapped as $row)
                        @php $isDupe = isset($existingItems[$row['item']]); @endphp
                        <tr class="{{ $row['is_vacant'] ? 'bg-amber-50' : ($isDupe ? 'bg-orange-50' : 'hover:bg-gray-50') }}">
                            <td class="px-3 py-2 text-gray-400">{{ $row['row_number'] }}</td>
                            <td class="px-3 py-2 font-mono text-gray-600">
                                {{ $row['item'] }}
                                @if($isDupe)
                                <span class="ml-1 text-orange-500 font-bold" title="Will be overwritten">↻</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-gray-800">{{ $row['position_title'] }}</td>
                            <td class="px-3 py-2">
                                @if($row['is_vacant'])
                                    <span class="text-amber-600 font-medium">VACANT</span>
                                @else
                                    {{ strtoupper($row['last_name'] ?? '') }}, {{ $row['first_name'] ?? '' }}
                                @endif
                            </td>
                            <td class="px-3 py-2 text-center font-bold text-gray-700">{{ $row['salary_grade'] }}</td>
                            <td class="px-3 py-2 text-center text-gray-700">{{ $row['step'] }}</td>
                            <td class="px-3 py-2 text-gray-600">{{ $row['employment_status'] ?: 'N/A' }}</td>
                            <td class="px-3 py-2 text-center">
                                @if($isDupe)
                                <span class="text-orange-500 font-semibold text-xs">UPDATE</span>
                                @else
                                <span class="text-green-600 font-semibold text-xs">NEW</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Execute Form --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-800 mb-2">Step 3: Execute {{ $dryRun ? 'Dry Run' : 'Full Import' }}</h2>
            <p class="text-gray-500 text-sm mb-4">
                The full {{ $dryRun ? 'simulation' : 'import' }} will process all
                <strong>{{ number_format($totalRows) }}</strong> rows.
                @if($dryRun) <span class="text-indigo-600 font-semibold">No data will be saved.</span> @endif
            </p>
            <form method="POST" action="{{ route('imports.execute') }}" enctype="multipart/form-data" id="import-form">
                @csrf
                <input type="hidden" name="replace_all"       value="{{ $replaceAll ? '1' : '0' }}">
                <input type="hidden" name="dry_run"           value="{{ $dryRun     ? '1' : '0' }}">
                <input type="hidden" name="original_filename" value="{{ $originalFileName ?? '' }}">

                @if(isset($mappingJson) && $mappingJson)
                    <input type="hidden" name="tmp_path"     value="{{ $tmpPath }}">
                    <input type="hidden" name="mapping_json" value="{{ $mappingJson }}">
                    <div class="mb-4 bg-green-50 border border-green-200 rounded-lg p-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-green-700 text-sm font-medium">File is ready — no re-upload needed. Your column mapping will be applied automatically.</p>
                    </div>
                @else
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-600 mb-1">Re-upload your file to execute:</label>
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                @endif

                <div class="flex gap-3">
                    <a href="{{ route('imports.index') }}" class="px-5 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                        Cancel
                    </a>
                    <button type="button"
                        class="px-5 py-2 {{ $dryRun ? 'bg-indigo-600 hover:bg-indigo-700' : ($replaceAll ? 'bg-red-600 hover:bg-red-700' : '') }} text-white rounded-lg text-sm font-medium transition"
                        style="{{ (!$dryRun && !$replaceAll) ? 'background:#10327c;' : '' }}"
                        onclick="confirmImport({{ $replaceAll ? 'true' : 'false' }}, {{ $dryRun ? 'true' : 'false' }}, {{ $totalRows }})"
                    >
                        @if($dryRun)
                            🧪 Run Dry Run Simulation ({{ number_format($totalRows) }} rows)
                        @elseif($replaceAll)
                            ⚠ Delete All &amp; Import ({{ number_format($totalRows) }} rows)
                        @else
                            ✔ Execute Import ({{ number_format($totalRows) }} rows)
                        @endif
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmImport(replaceAll, dryRun, count) {
            if (dryRun) {
                Swal.fire({
                    title: 'Run Dry Run Simulation?',
                    html: `<p style="font-size:14px;color:#4b5563;">This will simulate a full import of <strong>${count}</strong> rows.<br><br><span style="color:#4338ca;font-weight:700;">No data will be saved to the database.</span></p>`,
                    icon: 'info',
                    iconColor: '#6366f1',
                    showCancelButton: true,
                    confirmButtonColor: '#6366f1',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '🧪 Run Simulation'
                }).then((result) => {
                    if (result.isConfirmed) document.getElementById('import-form').submit();
                });
                return;
            }

            if (!replaceAll) {
                document.getElementById('import-form').submit();
                return;
            }

            Swal.fire({
                title: 'WARNING: Delete All Records?',
                text: "You are about to delete ALL existing Plantilla records before importing " + count + " rows. This action cannot be undone!",
                icon: 'warning',
                iconColor: '#dc3545',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#0f2942',
                confirmButtonText: 'Yes, Delete All & Import'
            }).then((result) => {
                if (result.isConfirmed) document.getElementById('import-form').submit();
            });
        }
    </script>
</x-dashboard-app>
