<x-dashboard-app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Import Applicants — Map Fields</h2>
    </x-slot>

    <div class="content-wrapper p-4">

        <div class="card shadow-sm border-0 mb-4" style="border-radius:14px; overflow:hidden;">
            <div class="card-header border-0 text-white py-3 px-4"
                 style="background:linear-gradient(135deg,#1f497d,#2e75b6);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-arrow-left-right me-2"></i>
                            Step 2 of 2 — Match Columns to Database Fields
                        </h5>
                        <div class="small opacity-75 mt-1">
                            File has <strong>{{ $totalRows }}</strong> data rows and
                            <strong>{{ count($headers) }}</strong> columns.
                        </div>
                    </div>
                    <a href="{{ route('recruitment.import-excel') }}" class="btn btn-sm btn-light">
                        <i class="bi bi-arrow-left me-1"></i> Re-upload
                    </a>
                </div>
            </div>

            <div class="card-body p-4">
                <form method="POST" action="{{ route('recruitment.import-excel.process') }}" id="mappingForm">
                    @csrf

                    <div class="alert alert-info py-2 px-3 small mb-4">
                        <i class="bi bi-info-circle-fill me-1"></i>
                        For each <strong>Excel column</strong>, choose the matching <strong>database field</strong>.
                        Select <em>"— Skip this column —"</em> to ignore columns you don't need.
                        Columns marked with <span class="badge bg-success">auto</span> were matched automatically.
                    </div>

                    {{-- ── MAPPING TABLE ──────────────────────────────────────── --}}
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle" style="font-size:14px;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:5%;">#</th>
                                    <th style="width:22%;">Excel Column</th>
                                    <th style="width:35%;">Map to Database Field</th>
                                    <th>Sample Values (first 5 rows)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($headers as $colIdx => $colName)
                                @php
                                    $suggested = $suggestions[$colName] ?? '__skip__';
                                    $isAuto    = $suggested !== '__skip__';
                                @endphp
                                <tr class="{{ $isAuto ? '' : 'table-warning' }}">
                                    <td class="text-center text-muted small">{{ $colIdx + 1 }}</td>
                                    <td>
                                        <span class="fw-semibold">{{ $colName }}</span>
                                        @if($isAuto)
                                            <span class="badge bg-success ms-1" style="font-size:10px;">auto</span>
                                        @else
                                            <span class="badge bg-warning text-dark ms-1" style="font-size:10px;">review</span>
                                        @endif
                                    </td>
                                    <td>
                                        <select name="mapping[{{ $colName }}]"
                                                class="form-select form-select-sm mapping-select"
                                                data-col="{{ $colName }}">
                                            <option value="__skip__" {{ $suggested === '__skip__' ? 'selected' : '' }}>
                                                — Skip this column —
                                            </option>
                                            @foreach($dbFields as $field => $label)
                                                <option value="{{ $field }}" {{ $suggested === $field ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($previewRows as $pRow)
                                                @php $val = $pRow[$colIdx] ?? ''; @endphp
                                                @if($val !== '' && $val !== null)
                                                    <span class="badge bg-light text-dark border"
                                                          style="font-weight:normal; font-size:11px; max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"
                                                          title="{{ $val }}">{{ $val }}</span>
                                                @endif
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- ── DUPLICATE HANDLING ─────────────────────────────────── --}}
                    <div class="card border-0 bg-light p-3 mb-4" style="border-radius:10px;">
                        <div class="fw-semibold mb-2"><i class="bi bi-copy me-1 text-primary"></i>Duplicate Handling</div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="duplicate_rule"
                                           id="dup_update" value="update" checked>
                                    <label class="form-check-label" for="dup_update">
                                        <strong>Update</strong> existing record if same Email + Item No.
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="duplicate_rule"
                                           id="dup_skip" value="skip">
                                    <label class="form-check-label" for="dup_skip">
                                        <strong>Skip</strong> duplicates — keep original record unchanged
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── DUPLICATE FIELD WARNING ────────────────────────────── --}}
                    <div id="dupWarning" class="alert alert-danger py-2 small d-none">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        <span id="dupWarningText"></span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('recruitment.import-excel') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Back
                        </a>
                        <button type="submit" class="btn btn-primary fw-semibold px-5" id="importBtn">
                            <i class="bi bi-cloud-upload-fill me-1"></i>
                            Import {{ number_format($totalRows) }} Records
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── PREVIEW TABLE ───────────────────────────────────────────────── --}}
        <div class="card shadow-sm border-0" style="border-radius:14px; overflow:hidden;">
            <div class="card-header bg-white py-2 px-4 border-bottom">
                <span class="fw-semibold small"><i class="bi bi-eye me-1 text-primary"></i>Data Preview (first 5 rows)</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0" style="font-size:12px;">
                        <thead class="table-dark">
                            <tr>
                                @foreach($headers as $h)
                                    <th class="px-2 py-1">{{ $h }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($previewRows as $pRow)
                                <tr>
                                    @foreach($headers as $colIdx => $h)
                                        <td class="px-2 py-1" style="max-width:160px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"
                                            title="{{ $pRow[$colIdx] ?? '' }}">
                                            {{ $pRow[$colIdx] ?? '' }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <script>
    // Warn if the same DB field is mapped to more than one Excel column
    document.querySelectorAll('.mapping-select').forEach(function(sel) {
        sel.addEventListener('change', checkDuplicates);
    });

    function checkDuplicates() {
        var seen = {};
        var dups = [];
        document.querySelectorAll('.mapping-select').forEach(function(s) {
            var v = s.value;
            if (v === '__skip__') return;
            if (seen[v]) dups.push(v);
            seen[v] = true;
        });

        var warn = document.getElementById('dupWarning');
        if (dups.length) {
            document.getElementById('dupWarningText').textContent =
                'The following database fields are mapped more than once: ' + dups.join(', ') + '. Only the last mapped column will be used.';
            warn.classList.remove('d-none');
        } else {
            warn.classList.add('d-none');
        }
    }

    // Show loading state on submit
    document.getElementById('mappingForm').addEventListener('submit', function() {
        var btn = document.getElementById('importBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Importing, please wait…';
    });
    </script>
</x-dashboard-app>
