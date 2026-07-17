<x-dashboard-app>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Publication Compliance Report</h2></x-slot>
    <div class="content-wrapper p-4">
        <p class="text-muted small">COA/CSC audit trail — posting dates, all 3 site proofs, receiving copy, and signature chain per vacancy.</p>
        <table class="table table-sm table-bordered">
            <thead><tr><th>Position</th><th>Status</th><th>Posting Start</th><th>Sites Logged</th><th>CSC FO Copy</th><th>Signed</th></tr></thead>
            <tbody>
                @foreach($requests as $r)
                    <tr>
                        <td>{{ $r->vacantPosition->position_title }}</td>
                        <td>{{ $r->status }}</td>
                        <td>{{ $r->posting_start_date?->format('M d, Y') ?? '—' }}</td>
                        <td>{{ $r->postingSiteLogs->count() }}/3</td>
                        <td>{{ $r->csc_fo_receiving_copy_path ? 'Y' : 'N' }}</td>
                        <td>{{ $r->signatures->isNotEmpty() ? 'Y (' . $r->signatures->last()->signatory_name . ')' : 'N' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-dashboard-app>
