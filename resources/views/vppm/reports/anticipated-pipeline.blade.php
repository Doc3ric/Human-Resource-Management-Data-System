<x-dashboard-app>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Anticipated Vacancy Pipeline Report</h2></x-slot>
    <div class="content-wrapper p-4">
        <p class="text-muted small">Sec.31 items still within their 180-day publication eligibility window.</p>
        <table class="table table-sm table-bordered">
            <thead><tr><th>Position</th><th>Separation Date</th><th>Eligible Now</th><th>Vacancy Status</th></tr></thead>
            <tbody>
                @foreach($vacancies as $vp)
                    <tr>
                        <td><a href="{{ route('vppm.vacancies.show', $vp) }}">{{ $vp->position_title }}</a></td>
                        <td>{{ $vp->anticipated_incumbent_separation_date->format('M d, Y') }}</td>
                        <td>{{ $vp->anticipated_publication_eligible ? 'Yes' : 'No' }}</td>
                        <td>{{ $vp->status }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-dashboard-app>
