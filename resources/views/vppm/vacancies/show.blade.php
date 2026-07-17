<x-dashboard-app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ $vacancy->position_title }}</h2>
    </x-slot>

    <div class="content-wrapper p-4">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <span class="badge bg-secondary">{{ $vacancy->status }}</span>
                @if($vacancy->is_anticipated)
                    <span class="badge bg-info text-dark">Anticipated (Sec.31)</span>
                @endif
            </div>
            <a href="{{ route('vppm.vacancies.edit', $vacancy) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i> Edit Vacancy</a>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row" style="font-size:13px;">
                    <div class="col-md-4"><b>Salary Grade:</b> {{ $vacancy->salary_grade }}</div>
                    <div class="col-md-4"><b>Monthly Salary:</b> {{ number_format($vacancy->monthly_salary, 2) }}</div>
                    <div class="col-md-4"><b>Appointment Status:</b> {{ $vacancy->appointment_status }}</div>
                    <div class="col-md-4"><b>Vacancy Type:</b> {{ $vacancy->vacancy_type }}</div>
                    <div class="col-md-4"><b>Place of Assignment:</b> {{ $vacancy->place_of_assignment }}</div>
                    <div class="col-md-4"><b>Office/Division:</b> {{ $vacancy->office_division }}</div>
                    @if($vacancy->vice_whom)<div class="col-md-4"><b>Vice:</b> {{ $vacancy->vice_whom }}</div>@endif
                    @if($vacancy->plantillaRecord)<div class="col-md-4"><b>Plantilla Item:</b> {{ $vacancy->plantillaRecord->item_no_new }}</div>@endif
                </div>
            </div>
        </div>

        <h5 class="fw-bold mb-2">CS Form No. 9 Publication Requests</h5>
        <table class="table table-sm table-bordered">
            <thead><tr><th>Ver.</th><th>Status</th><th>Submission Mode</th><th>Posting Start</th><th>Validity End</th><th></th></tr></thead>
            <tbody>
                @forelse($vacancy->publicationRequests->sortByDesc('version_no') as $r)
                    <tr>
                        <td>{{ $r->version_no }}</td>
                        <td><span class="badge bg-dark">{{ $r->status }}</span></td>
                        <td>{{ $r->submission_mode }}</td>
                        <td>{{ $r->posting_start_date?->format('M d, Y') ?? '—' }}</td>
                        <td>{{ $r->validity_end_date?->format('M d, Y') ?? '—' }}</td>
                        <td><a href="{{ route('vppm.requests.show', $r) }}" class="btn btn-sm btn-primary">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted text-center">No publication request yet.</td></tr>
                @endforelse
            </tbody>
        </table>

        @if($vacancy->publicationRequests->isEmpty())
            <div class="card shadow-sm mt-3">
                <div class="card-body">
                    <h6 class="fw-bold">Prepare CS Form No. 9</h6>
                    <form method="POST" action="{{ route('vppm.requests.store', $vacancy) }}" class="row g-2">
                        @csrf
                        <div class="col-md-4"><input name="agency_contact_person" required placeholder="Agency Contact Person *" class="form-control form-control-sm"></div>
                        <div class="col-md-4"><input name="agency_contact_number" required placeholder="Contact Number *" class="form-control form-control-sm"></div>
                        <div class="col-md-4"><input type="email" name="agency_contact_email" required placeholder="Contact Email *" class="form-control form-control-sm"></div>
                        <div class="col-md-4">
                            <select name="submission_mode" required class="form-select form-select-sm">
                                <option value="CSC_FO">CSC Field Office</option>
                                <option value="Agency_Website">Agency Website</option>
                                <option value="Newspaper">Newspaper</option>
                                <option value="Job_Site">Job Search Site</option>
                                <option value="Multiple">Multiple</option>
                            </select>
                        </div>
                        <div class="col-md-4"><input type="number" name="posting_min_required_days" placeholder="Min. Posting Days (default 15)" class="form-control form-control-sm"></div>
                        <div class="col-md-4"><input type="number" name="validity_months" placeholder="Validity Months (default 9)" class="form-control form-control-sm"></div>
                        <div class="col-12"><button class="btn btn-sm btn-primary mt-2">Draft Request</button></div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-dashboard-app>
