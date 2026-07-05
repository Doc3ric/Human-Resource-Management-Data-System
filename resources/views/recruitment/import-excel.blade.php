<x-dashboard-app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Import Applicants — Excel</h2>
    </x-slot>

    <div class="content-wrapper p-4" style="max-width:700px; margin:0 auto;">

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card shadow-sm border-0" style="border-radius:14px; overflow:hidden;">
            <div class="card-header border-0 text-white py-3 px-4"
                 style="background:linear-gradient(135deg,#1f497d,#2e75b6);">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-file-earmark-spreadsheet-fill me-2"></i>
                    Import Applicants from Excel (.xlsx / .xls)
                </h5>
                <div class="small opacity-75 mt-1">Step 1 of 2 — Upload your file</div>
            </div>

            <div class="card-body p-4">
                <p class="text-muted mb-4">
                    Upload an Excel file containing applicant records. On the next step you can
                    match each column in your file to the correct field in the system database.
                </p>

                <form method="POST" action="{{ route('recruitment.import-excel.map') }}"
                      enctype="multipart/form-data" id="uploadForm">
                    @csrf

                    {{-- Drop zone --}}
                    <div id="dropZone"
                         class="border border-2 border-dashed rounded-3 text-center py-5 px-3 mb-3"
                         style="border-color:#2e75b6!important; background:#f4f8ff; cursor:pointer;"
                         onclick="document.getElementById('excel_file').click()"
                         ondragover="event.preventDefault(); this.style.background='#dce9ff';"
                         ondragleave="this.style.background='#f4f8ff';"
                         ondrop="handleDrop(event)">
                        <i class="bi bi-cloud-arrow-up-fill fs-1 text-primary mb-2 d-block"></i>
                        <div class="fw-semibold text-primary mb-1">Click or drag & drop your Excel file here</div>
                        <div class="text-muted small">Supports .xlsx and .xls — max 20 MB</div>
                        <div id="fileLabel" class="mt-2 small fw-bold text-success d-none"></div>
                    </div>

                    <input type="file" name="excel_file" id="excel_file" accept=".xlsx,.xls"
                           class="d-none" onchange="showFileName(this)">

                    @error('excel_file')
                        <div class="text-danger small mb-2">{{ $message }}</div>
                    @enderror

                    {{-- Date format hint --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Date Format in the File</label>
                        <select name="date_format" class="form-select form-select-sm w-auto">
                            <option value="excel_serial" selected>Excel Serial Numbers (most common)</option>
                            <option value="Y-m-d">YYYY-MM-DD (e.g. 2024-01-15)</option>
                            <option value="m/d/Y">MM/DD/YYYY (e.g. 01/15/2024)</option>
                            <option value="d/m/Y">DD/MM/YYYY (e.g. 15/01/2024)</option>
                        </select>
                        <div class="text-muted small mt-1">This applies to Date Received and Date of Birth columns.</div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('recruitment.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Back
                        </a>
                        <button type="submit" class="btn btn-primary fw-semibold px-4" id="uploadBtn" disabled>
                            <i class="bi bi-arrow-right-circle-fill me-1"></i> Next: Map Fields
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function showFileName(input) {
        if (input.files.length) {
            document.getElementById('fileLabel').textContent = '✓ ' + input.files[0].name;
            document.getElementById('fileLabel').classList.remove('d-none');
            document.getElementById('uploadBtn').disabled = false;
        }
    }
    function handleDrop(e) {
        e.preventDefault();
        document.getElementById('dropZone').style.background = '#f4f8ff';
        const dt = e.dataTransfer;
        if (dt.files.length) {
            document.getElementById('excel_file').files = dt.files;
            showFileName(document.getElementById('excel_file'));
        }
    }
    </script>
</x-dashboard-app>
