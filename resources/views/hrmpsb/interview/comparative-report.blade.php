<x-dashboard-app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Comparative Assessment Report
        </h2>
    </x-slot>

    <div class="content-wrapper p-4">
    <style>
        @media print {
            body * { visibility: hidden; }
            #printableArea, #printableArea * { visibility: visible; }
            #printableArea { position: absolute; left: 0; top: 0; width: 100%; }
            .no-print { display: none !important; }
            @page { size: portrait; margin: 10mm; }
        }
        .table-bordered th, .table-bordered td { border-color: #000 !important; }
        .report-title { font-family: 'Times New Roman', Times, serif; }
    </style>
    
    <!-- Controls (No Print) -->
    <div class="card shadow-sm border-0 mb-4 no-print">
        <div class="card-body">
            <form action="{{ route('recruitment.hrmpsb.comparative_report') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Filter by Position</label>
                    <select name="position" id="reportPositionFilter" class="form-select">
                        <option value="">-- All Positions --</option>
                        @foreach($positions as $posKey => $posLabel)
                            <option value="{{ $posKey }}" data-office="{{ $positionOffices[$posKey] ?? '' }}" {{ $position == $posKey ? 'selected' : '' }}>{{ $posLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Filter by Office</label>
                    <select name="office" id="reportOfficeFilter" class="form-select">
                        <option value="">-- All Offices --</option>
                        @foreach($offices as $off)
                            <option value="{{ $off }}" {{ $office == $off ? 'selected' : '' }}>{{ $off }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Signatories Team</label>
                    <select name="team" class="form-select">
                        <option value="management" {{ $teamType == 'management' ? 'selected' : '' }}>Management</option>
                        <option value="legislative" {{ $teamType == 'legislative' ? 'selected' : '' }}>Legislative</option>
                    </select>
                </div>
                <div class="col-md-4 text-end">
                    <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i>Generate</button>
                    <button type="button" onclick="window.print()" class="btn btn-dark"><i class="fas fa-print me-1"></i>Print Report</button>
                    <a href="{{ route('recruitment.hrmpsb.signatories') }}" class="btn btn-outline-secondary ms-2"><i class="fas fa-cog"></i> Settings</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Printable Area -->
    <div id="printableArea" class="bg-white p-4 shadow-sm" style="min-height: 297mm; max-width: 210mm; margin: 0 auto; color: #000;">
        
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div style="width: 100px;">
                <p class="mb-0 fw-bold small report-title">HRMPSB Report No. 01</p>
                <p class="mb-0 small report-title">(Revised November 2017)</p>
            </div>
            <div class="text-center report-title">
                <p class="mb-0">Republic of the Philippines</p>
                <p class="mb-0">PROVINCE OF BUKIDNON</p>
                <p class="mb-0">Malaybalay City</p>
                <p class="mb-0 fw-bold fs-5">OFFICE OF THE PROVINCIAL GOVERNOR</p>
                <p class="mb-0 fs-6">HUMAN RESOURCE MERIT PROMOTION AND SELECTION REPORT</p>
                <p class="mb-0 fw-bold fs-5 text-decoration-underline mt-2">COMPARATIVE ASSESSMENT REPORT</p>
            </div>
            <div style="width: 100px;" class="text-end">
                <!-- Logos could go here -->
            </div>
        </div>

        @if($position || $office)
        <div class="text-center mb-3 fw-bold bg-light border border-dark p-1 text-uppercase">
            {{ $office ?? 'ALL OFFICES' }} <br>
            {{ $position ?? 'ALL POSITIONS' }}
        </div>
        @endif

        <!-- Data Table -->
        <table class="table table-bordered table-sm align-middle text-center" style="font-size: 11px;">
            <thead class="align-middle">
                <tr>
                    <th rowspan="2" class="text-start ps-2" style="width: 25%;">NAME OF CANDIDATE/NOMINEES</th>
                    <th rowspan="2">RANK</th>
                    <th colspan="2">INTERVIEW</th>
                    <th rowspan="2">IPCR<br>Rating<br>(10%)</th>
                    <th rowspan="2">AWARDS<br>(5%)</th>
                    <th rowspan="2">EDUCA<br>TION<br>(15%)</th>
                    <th rowspan="2">EXPERIENCE<br>(10%)</th>
                    <th rowspan="2">TRAINING<br>(10%)</th>
                    <th rowspan="2">TOTAL</th>
                    <th rowspan="2">DEMERIT</th>
                    <th rowspan="2">GRAND<br>TOTAL</th>
                </tr>
                <tr>
                    <th>(100%)</th>
                    <th>(50%)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($applicants as $index => $app)
                <tr>
                    <td class="text-start ps-2 fw-bold text-uppercase">
                        {{ $app->last_name }}, {{ $app->first_name }}
                        @if($app->contact_number)
                        <div class="fw-normal text-muted" style="font-size: 9px;">Contact: {{ $app->contact_number }}</div>
                        @endif
                    </td>
                    <td class="fw-bold fs-6">{{ $index + 1 }}</td>
                    <td>{{ number_format($app->raw_interview_avg, 2) }}</td>
                    <td>{{ number_format($app->weighted_interview, 2) }}</td>
                    <td>{{ number_format($app->twg_ipcr, 2) }}</td>
                    <td>{{ number_format($app->twg_awards, 2) }}</td>
                    <td>{{ number_format($app->twg_education, 2) }}</td>
                    <td>{{ number_format($app->twg_experience, 2) }}</td>
                    <td>{{ number_format($app->twg_training, 2) }}</td>
                    <td>{{ number_format($app->grand_total + $app->demerits, 3) }}</td>
                    <td>{{ number_format($app->demerits, 0) }}</td>
                    <td class="fw-bold">{{ number_format($app->grand_total, 3) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="12" class="text-center py-3">No applicants found for the selected criteria.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <p class="small mt-1 mb-4">I HEREBY CERTIFY to the correctness of the foregoing</p>

        <!-- Signatories -->
        @php
            $secretary = $signatories->where('role', 'secretary')->first();
            $chairman = $signatories->where('role', 'chairman')->first();
            $appointing = $signatories->where('role', 'appointing_authority')->first();
            $members = $signatories->whereNotIn('role', ['secretary', 'chairman', 'appointing_authority']);
        @endphp

        <!-- Secretary -->
        <div class="row mb-4">
            <div class="col-12 text-center">
                <p class="mb-0 fw-bold text-uppercase">{{ $secretary->name ?? '_________________________' }}</p>
                <p class="mb-0 small">{{ $secretary->title ?? 'HRMPSB Secretary' }}</p>
            </div>
        </div>

        <h5 class="text-center fw-bold text-decoration-underline mb-3" style="letter-spacing: 5px;">CERTIFICATION</h5>
        <p class="text-center small px-4 mb-5" style="text-align: justify !important;">
            WE HEREBY CERTIFY that the Personnel Selection / Promotion Board organized pursuant to Local Government Code of 1991 and Sangguniang Panlalawigan Resolution Ordinance conducted a screening and evaluation on the applicants of the position on the specified date and determined that after due deliberation and consideration, the nominee(s) for the aforemention position meet(s) the minimum requirements set by the law and is evaluated in accordance with the approved HRMPSB criteria.
        </p>

        <!-- Members -->
        <div class="row text-center mb-5 gx-2">
            @foreach($members as $member)
                <div class="col">
                    <p class="mb-0 fw-bold text-uppercase">{{ $member->name ?? '_________________________' }}</p>
                    <p class="mb-0 small">{{ $member->role == 'member' ? 'Member' : $member->role }}</p>
                    <p class="mb-0 small">{{ $member->title ?? '' }}</p>
                </div>
            @endforeach
        </div>

        <!-- Chairman & Appointing Authority -->
        <div class="row text-center mt-4">
            <div class="col-12 mb-4">
                <p class="mb-0 fw-bold text-uppercase">{{ $chairman->name ?? '_________________________' }}</p>
                <p class="mb-0 small">HRMPSB Chairman</p>
                <p class="mb-0 small">{{ $chairman->title ?? '' }}</p>
            </div>
            <div class="col-12 mt-3">
                <p class="mb-0 fw-bold text-uppercase">{{ $appointing->name ?? '_________________________' }}</p>
                <p class="mb-0 small">Appointing Authority</p>
                <p class="mb-0 small">{{ $appointing->title ?? 'Governor' }}</p>
            </div>
        </div>

    </div>
</div>
<script>
    // Narrows the Position dropdown to whichever Office is selected, so users can't pick
    // a mismatched office/position combo that silently returns zero results.
    document.addEventListener('DOMContentLoaded', function () {
        var officeSelect = document.getElementById('reportOfficeFilter');
        var positionSelect = document.getElementById('reportPositionFilter');
        if (!officeSelect || !positionSelect) return;

        var allOptions = Array.prototype.slice.call(positionSelect.options);

        function applyFilter() {
            var selectedOffice = officeSelect.value;
            allOptions.forEach(function (opt) {
                if (!opt.value) return; // keep the "All Positions" placeholder always visible
                var optOffice = opt.getAttribute('data-office');
                opt.style.display = (selectedOffice && optOffice && optOffice !== selectedOffice) ? 'none' : '';
            });
            var selected = positionSelect.options[positionSelect.selectedIndex];
            if (selected && selected.style.display === 'none') {
                positionSelect.value = '';
            }
        }

        officeSelect.addEventListener('change', applyFilter);
        applyFilter();
    });
</script>
</x-dashboard-app>
