<x-dashboard-app>
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;">
    <h2 style="font-size:16px;font-weight:800;">Training History — {{ $plantilla->last_name }}, {{ $plantilla->first_name }}</h2>
    <p style="font-size:12.5px;color:#6b7280;">Total training hours: <b>{{ $totalHours }}</b></p>

    <table style="width:100%;border-collapse:collapse;font-size:12.5px;margin-top:12px;">
        <thead><tr style="text-align:left;color:#9ca3af;font-size:10.5px;text-transform:uppercase;">
            <th style="padding:8px;">Training</th><th>Competency Area</th><th>Dates</th><th>Hours</th><th>Certificate</th>
        </tr></thead>
        <tbody>
            @forelse($records as $r)
                <tr style="border-top:1px solid #f3f4f6;">
                    <td style="padding:8px;">{{ $r->training->title }}</td>
                    <td>{{ $r->training->competency_area }}</td>
                    <td>{{ $r->training->date_from->format('M d, Y') }}</td>
                    <td>{{ $r->hours_attended ?? $r->training->total_hours }}</td>
                    <td>{{ $r->certificates->first()?->reference_no ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;color:#9ca3af;padding:12px;">No training history yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
</x-dashboard-app>
