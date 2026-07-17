<x-dashboard-app>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Expiry Forecast Report</h2></x-slot>
    <div class="content-wrapper p-4">
        <table class="table table-sm table-bordered">
            <thead><tr><th>Position</th><th>Status</th><th>Validity End</th><th>Days to Expiry</th></tr></thead>
            <tbody>
                @foreach($requests as $r)
                    <tr class="{{ $r->status === 'NEAR_EXPIRY' ? 'table-warning' : '' }}">
                        <td>{{ $r->vacantPosition->position_title }}</td>
                        <td>{{ $r->status }}</td>
                        <td>{{ $r->validity_end_date?->format('M d, Y') }}</td>
                        <td>{{ $r->days_to_expiry }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-dashboard-app>
