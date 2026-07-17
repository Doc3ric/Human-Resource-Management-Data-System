<x-dashboard-app>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">CS Form No. 9 Batch Report</h2></x-slot>
    <div class="content-wrapper p-4">
        <p class="text-muted small">All currently PUBLICATION_ACTIVE / VALID requests, for CSC Field Office submission.</p>
        <table class="table table-sm table-bordered">
            <thead><tr><th>Position</th><th>SG</th><th>Office</th><th>Status</th><th>Submission Mode</th><th>Posting Start</th><th>Validity End</th></tr></thead>
            <tbody>
                @foreach($requests as $r)
                    <tr>
                        <td>{{ $r->vacantPosition->position_title }}</td>
                        <td>{{ $r->vacantPosition->salary_grade }}</td>
                        <td>{{ $r->vacantPosition->office_division }}</td>
                        <td>{{ $r->status }}</td>
                        <td>{{ $r->submission_mode }}</td>
                        <td>{{ $r->posting_start_date?->format('M d, Y') ?? '—' }}</td>
                        <td>{{ $r->validity_end_date?->format('M d, Y') ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-dashboard-app>
