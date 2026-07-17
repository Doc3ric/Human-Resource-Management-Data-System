<x-dashboard-app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Vacant Position Publication & Monitoring') }}</h2>
    </x-slot>

    <div class="content-wrapper p-4">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <span class="badge bg-danger me-1">Deficient: {{ $flagged['deficient'] }}</span>
                <span class="badge bg-warning text-dark me-1">Near Expiry: {{ $flagged['near_expiry'] }}</span>
                <span class="badge bg-secondary">Expired: {{ $flagged['expired'] }}</span>
            </div>
            <div>
                <div class="btn-group me-2">
                    <a href="{{ route('vppm.reports.batch') }}" class="btn btn-sm btn-outline-dark">CS Form 9 Batch</a>
                    <a href="{{ route('vppm.reports.compliance') }}" class="btn btn-sm btn-outline-dark">Compliance</a>
                    <a href="{{ route('vppm.reports.expiry-forecast') }}" class="btn btn-sm btn-outline-dark">Expiry Forecast</a>
                    <a href="{{ route('vppm.reports.anticipated-pipeline') }}" class="btn btn-sm btn-outline-dark">Anticipated Pipeline</a>
                </div>
                <a href="{{ route('vppm.vacancies.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-circle me-1"></i> New Vacancy</a>
            </div>
        </div>

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    @foreach(['DRAFT','PENDING_SIGNATURE','SUBMITTED_TO_CSC_FO','POSTED','PUBLICATION_ACTIVE','PUBLICATION_DEFICIENT','VALID','NEAR_EXPIRY','EXPIRED','FILLED','CANCELLED'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <input name="office" value="{{ request('office') }}" placeholder="Filter by office/division" class="form-control form-control-sm">
            </div>
            <div class="col-md-2"><button class="btn btn-sm btn-outline-secondary">Filter</button></div>
        </form>

        <table class="table table-sm table-bordered table-hover">
            <thead>
                <tr>
                    <th>Position</th><th>SG</th><th>Status</th><th>Posting Start</th><th>Days Posted/Required</th>
                    <th>Validity End</th><th>Days to Expiry</th><th>CSC FO Copy</th><th>3-Site</th><th>Flag</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $r)
                    @php
                        $daysPosted = $r->posting_start_date ? now()->diffInDays($r->posting_start_date) : null;
                        $sitesConfirmed = $r->postingSiteLogs->count();
                        $flag = in_array($r->status, ['PUBLICATION_DEFICIENT','NEAR_EXPIRY','EXPIRED']);
                    @endphp
                    <tr class="{{ $flag ? 'table-danger' : '' }}">
                        <td><a href="{{ route('vppm.requests.show', $r) }}">{{ $r->vacantPosition->position_title }}</a></td>
                        <td>{{ $r->vacantPosition->salary_grade }}</td>
                        <td><span class="badge bg-dark">{{ $r->status }}</span></td>
                        <td>{{ $r->posting_start_date?->format('M d, Y') ?? '—' }}</td>
                        <td>{{ $daysPosted !== null ? $daysPosted . '/' . $r->posting_min_required_days : '—' }}</td>
                        <td>{{ $r->validity_end_date?->format('M d, Y') ?? '—' }}</td>
                        <td>{{ $r->days_to_expiry ?? '—' }}</td>
                        <td>{{ $r->csc_fo_receiving_copy_path ? 'Y' : 'N' }}</td>
                        <td>{{ $sitesConfirmed }}/3</td>
                        <td>{!! $flag ? '<span class="badge bg-danger">Flagged</span>' : '' !!}</td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center text-muted">No publication requests yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $requests->links() }}

        @if($anticipated->isNotEmpty())
            <h6 class="fw-bold mt-4">Anticipated Vacancy Pipeline (Sec.31 — within 180-day window)</h6>
            <table class="table table-sm table-bordered">
                <thead><tr><th>Position</th><th>Separation Date</th><th>Days Until Eligible/Elapsed</th><th>Eligible Now</th></tr></thead>
                <tbody>
                    @foreach($anticipated as $vp)
                        <tr>
                            <td><a href="{{ route('vppm.vacancies.show', $vp) }}">{{ $vp->position_title }}</a></td>
                            <td>{{ $vp->anticipated_incumbent_separation_date->format('M d, Y') }}</td>
                            <td>{{ now()->diffInDays($vp->anticipated_incumbent_separation_date, false) }}</td>
                            <td>{{ $vp->anticipated_publication_eligible ? 'Yes' : 'No' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-dashboard-app>
