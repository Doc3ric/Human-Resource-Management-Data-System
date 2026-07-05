<x-dashboard-app>
<div style="background: linear-gradient(135deg,#1e3a5f 0%,#1a5276 55%,#0f4c75 100%); border-radius:14px; padding:24px 28px; margin-bottom:20px; color:#fff;">
    <h1 style="font-size:20px;font-weight:800;margin:0 0 4px;"><i class="bi bi-exclamation-diamond-fill"></i> Incident Reports</h1>
    <p style="font-size:12.5px;opacity:.8;margin:0;">Facts-only intake; advisory drafts are AI/rules-assisted suggestions only, never findings — 2025 RACCS due process governs.</p>
</div>

@if(session('success'))
    <div style="background:#f0fdf4;color:#166534;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:13px;">{{ session('success') }}</div>
@endif

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;font-size:12.5px;">
        <thead><tr style="text-align:left;color:#9ca3af;font-size:10.5px;text-transform:uppercase;">
            <th style="padding:8px;">Reference</th><th>Category</th><th>Reported</th><th>Status</th><th>Track</th><th></th>
        </tr></thead>
        <tbody>
            @forelse($reports as $r)
                <tr style="border-top:1px solid #f3f4f6;">
                    <td style="padding:8px;"><a href="{{ route('incidents.show', $r) }}">{{ $r->reference_no }}</a></td>
                    <td>{{ ucfirst($r->category) }}</td>
                    <td>{{ $r->reported_at->format('M d, Y') }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $r->status)) }}</td>
                    <td>{{ $r->final_track ? ucfirst($r->final_track) : '—' }}</td>
                    <td></td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;color:#9ca3af;padding:12px;">No incident reports yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $reports->links() }}
</div>

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;margin-top:16px;">
    <h3 style="font-size:14px;font-weight:700;margin-bottom:10px;">File New Incident Report</h3>
    <form method="POST" action="{{ route('incidents.store') }}" enctype="multipart/form-data" style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
        @csrf
        <input type="datetime-local" name="incident_datetime" required class="form-control form-control-sm">
        <input name="location" placeholder="Location" class="form-control form-control-sm">
        <select name="category" required class="form-control form-control-sm">
            <option value="attendance">Attendance</option>
            <option value="conduct">Conduct</option>
            <option value="performance">Performance</option>
            <option value="safety">Safety</option>
            <option value="property">Property</option>
            <option value="interpersonal">Interpersonal</option>
            <option value="other">Other</option>
        </select>
        <input type="file" name="attachment" class="form-control form-control-sm">
        <textarea name="narrative" placeholder="Factual narrative — observable facts only, no conclusions" required class="form-control form-control-sm" style="grid-column:span 2;" rows="3"></textarea>
        <textarea name="immediate_action_taken" placeholder="Immediate action taken (optional)" class="form-control form-control-sm" style="grid-column:span 2;" rows="2"></textarea>
        <button class="btn btn-sm btn-primary" style="grid-column:span 2;">File Report</button>
    </form>
</div>
</x-dashboard-app>
