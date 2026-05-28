<x-dashboard-app>
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Import Plantilla Data</h1>
                <p class="text-gray-500 text-sm">Upload your Excel (.xlsx) or CSV file to import plantilla records into the system</p>
            </div>
            <a href="{{ route('imports.history') }}"
               class="inline-flex items-center gap-2 text-sm font-medium text-indigo-600 hover:text-indigo-800 border border-indigo-200 bg-indigo-50 px-4 py-2 rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Import History
            </a>
        </div>

        @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">{{ session('error') }}</div>
        @endif

        {{-- Current Data Info --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <p class="text-blue-800 font-medium text-sm">Current Database</p>
                <p class="text-blue-700 text-sm">{{ number_format($totalRecords) }} plantilla records currently in the system.</p>
                <p class="text-blue-600 text-xs mt-1" id="modeHint">Importing will <strong>UPDATE</strong> existing records (matched by Item code) and <strong>CREATE</strong> new ones.</p>
            </div>
        </div>

        {{-- Upload Form (Preview Step) --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-800 mb-4">Step 1 of 3: Upload File</h2>

            <form method="POST" action="{{ route('imports.read-headers') }}" enctype="multipart/form-data" id="previewForm">
                @csrf
                <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-blue-400 transition cursor-pointer"
                     onclick="document.getElementById('fileInput').click()">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-gray-600 font-medium">Click to select your Excel file</p>
                    <p class="text-gray-400 text-sm mt-1">Supports .xlsx, .xls, .csv files up to 20MB</p>
                    <p class="text-blue-600 text-xs mt-2" id="fileName">No file selected</p>
                    <input type="file" name="file" id="fileInput" accept=".xlsx,.xls,.csv" class="hidden"
                           onchange="document.getElementById('fileName').textContent = this.files[0]?.name || 'No file selected'">
                </div>

                {{-- Template Download --}}
                <div class="mt-3 flex items-center gap-2 text-xs text-gray-500">
                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Don't have the right format?
                    <a href="{{ route('imports.template') }}"
                       class="inline-flex items-center gap-1 font-semibold text-green-600 hover:text-green-800 underline underline-offset-2">
                        Download Excel Template
                    </a>
                    — with all required headers and sample rows.
                </div>

                <div class="mt-4 bg-gray-50 rounded-lg p-4">
                    <p class="text-xs font-medium text-gray-600 mb-2">Expected Excel Column Headers (Row 1):</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach(['ORGANIZATIONAL UNIT','ITEM','POSITION TITLE','SALARY GRADE','AUTHORIZED ANNUAL SALARY','ACTUAL ANNUAL SALARY','STEP','AREA CODE','AREA TYPE','LEVEL','LAST NAME','FIRST NAME','MIDDLE NAME','SEX','RELIGION','DATE OF BIRTH','TIN','DATE OF ORIGINAL APPOINTMENT','DATE OF LAST PROMOTION-APPOINTMENT','STATUS','CIVIL SERVICE ELIGIBILITY','COMMENT/ ANNOTATION','PWD','INDIGENOUS PEOPLE (Y)','SOLO PARENT (ID_NUMBER)','ABOLISHED','DISSOLVED','GSIS BP NUMBER','POSITION CLASSIFICATION','UMID'] as $col)
                        <span class="bg-white border border-gray-200 text-gray-600 text-xs px-2 py-0.5 rounded font-mono">{{ $col }}</span>
                        @endforeach
                    </div>
                </div>

                {{-- Import Mode Toggle --}}
                <div class="mt-4 border border-gray-200 rounded-lg overflow-hidden">
                    <div class="bg-gray-50 px-4 py-3 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-700">Import Mode</p>
                            <p class="text-xs text-gray-500 mt-0.5" id="modeDesc">Upsert — update existing, add new (safe)</p>
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <span class="text-xs text-gray-500">Upsert</span>
                            <div class="relative">
                                <input type="checkbox" name="replace_all" id="replaceToggle" value="1" class="sr-only" onchange="toggleMode(this)">
                                <div id="toggleTrack" class="w-11 h-6 bg-gray-200 rounded-full transition-colors duration-200"></div>
                                <div id="toggleThumb" class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200"></div>
                            </div>
                            <span class="text-xs text-gray-500">Replace All</span>
                        </label>
                    </div>

                    {{-- Warning shown when Replace All is active --}}
                    <div id="replaceWarning" class="hidden bg-red-50 border-t border-red-200 px-4 py-3">
                        <div class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                            <div>
                                <p class="text-red-700 text-xs font-semibold">⚠ Replace All Mode — All {{ number_format($totalRecords) }} existing records will be DELETED before importing.</p>
                                <p class="text-red-600 text-xs mt-0.5">This action cannot be undone. Use only when you want a completely fresh import.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Dry Run Toggle --}}
                <div class="mt-3 border border-indigo-200 rounded-lg bg-indigo-50 px-4 py-3 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-indigo-700">Dry Run Mode</p>
                        <p class="text-xs text-indigo-500 mt-0.5" id="dryRunDesc">Off — changes will be saved to the database</p>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <span class="text-xs text-indigo-400">Off</span>
                        <div class="relative">
                            <input type="checkbox" name="dry_run" id="dryRunToggle" value="1" class="sr-only" onchange="toggleDryRun(this)">
                            <div id="dryRunTrack" class="w-11 h-6 bg-gray-200 rounded-full transition-colors duration-200"></div>
                            <div id="dryRunThumb" class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200"></div>
                        </div>
                        <span class="text-xs text-indigo-400">On</span>
                    </label>
                </div>

                <button type="submit"
                    class="mt-4 w-full text-white py-3 rounded-lg font-medium transition flex items-center justify-center gap-2"
                    style="background: #10327c;"
                    onmouseover="this.style.background='#0c2461'" onmouseout="this.style.background='#10327c'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                    Continue to Column Mapping →
                </button>
            </form>
        </div>

        {{-- Instructions --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-800 mb-3">How It Works (3 Steps)</h2>
            <ol class="list-decimal list-inside space-y-3 text-sm text-gray-600">
                <li>
                    <strong class="text-gray-800">Upload</strong> — Select any Excel (.xlsx/.xls) or CSV file.
                    You can use the <a href="{{ route('imports.template') }}" class="text-green-600 font-semibold underline">Excel Template</a> for guaranteed compatibility, or upload any file from another system.
                </li>
                <li>
                    <strong class="text-gray-800">Map Columns</strong> — The system reads your file's column headers and shows a mapping screen. Match each of your columns to the correct system field (e.g., map your "Worker Name" column → First Name). Required fields are highlighted.
                </li>
                <li>
                    <strong class="text-gray-800">Preview &amp; Execute</strong> — Review the first 20 rows before committing. Then run the full import.
                </li>
            </ol>
            <div class="mt-4 pt-4 border-t border-gray-100 space-y-1 text-sm text-gray-500">
                <p>• Vacant positions should have "Vacant" (or blank) in the Last Name column.</p>
                <p>• Dates: YYYY-MM-DD, DD/MM/YYYY, MM/DD/YYYY, or Excel serial numbers are all accepted.</p>
                <p>• <strong>Upsert mode:</strong> ITEM code is the unique key — existing records UPDATE, new ones are CREATED.</p>
                <p>• <strong>Replace All mode:</strong> All existing records are deleted first, then your file is imported fresh.</p>
                <p>• <strong>Dry Run mode:</strong> Simulates the import without saving anything to the database.</p>
            </div>
        </div>
    </div>

    <script>
        function toggleMode(cb) {
            const track   = document.getElementById('toggleTrack');
            const thumb   = document.getElementById('toggleThumb');
            const warning = document.getElementById('replaceWarning');
            const desc    = document.getElementById('modeDesc');
            const hint    = document.getElementById('modeHint');

            if (cb.checked) {
                track.style.backgroundColor = '#dc2626';
                thumb.style.transform = 'translateX(20px)';
                warning.classList.remove('hidden');
                desc.textContent  = 'Replace All — DELETE all records, then import fresh';
                desc.style.color  = '#b91c1c';
                hint.innerHTML    = 'Importing will <strong>DELETE all {{ $totalRecords }} existing records</strong> first, then insert everything from your file.';
                hint.style.color  = '#b91c1c';
            } else {
                track.style.backgroundColor = '';
                thumb.style.transform = '';
                warning.classList.add('hidden');
                desc.textContent  = 'Upsert — update existing, add new (safe)';
                desc.style.color  = '';
                hint.innerHTML    = 'Importing will <strong>UPDATE</strong> existing records (matched by Item code) and <strong>CREATE</strong> new ones.';
                hint.style.color  = '';
            }
        }

        function toggleDryRun(cb) {
            const track = document.getElementById('dryRunTrack');
            const thumb = document.getElementById('dryRunThumb');
            const desc  = document.getElementById('dryRunDesc');
            if (cb.checked) {
                track.style.backgroundColor = '#6366f1';
                thumb.style.transform = 'translateX(20px)';
                desc.textContent = 'On — simulates import, NO data will be saved';
                desc.style.color = '#4338ca';
            } else {
                track.style.backgroundColor = '';
                thumb.style.transform = '';
                desc.textContent = 'Off — changes will be saved to the database';
                desc.style.color = '';
            }
        }
    </script>
</x-dashboard-app>
