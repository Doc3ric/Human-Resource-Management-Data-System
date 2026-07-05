<x-dashboard-app>
<div style="background: linear-gradient(135deg,#1e3a5f 0%,#1a5276 55%,#0f4c75 100%); border-radius:14px; padding:24px 28px; margin-bottom:20px; color:#fff;">
    <h1 style="font-size:20px;font-weight:800;margin:0 0 4px;"><i class="bi bi-mortarboard-fill"></i> Learning &amp; Development</h1>
    <p style="font-size:12.5px;opacity:.8;margin:0;">Training records, attendance, and certificate issuance — feeds relevant-training-hours in recruitment scoring.</p>
</div>

@if(session('success'))
    <div style="background:#f0fdf4;color:#166534;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:13px;">{{ session('success') }}</div>
@endif

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;font-size:12.5px;">
        <thead><tr style="text-align:left;color:#9ca3af;font-size:10.5px;text-transform:uppercase;">
            <th style="padding:8px;">Title</th><th>Type</th><th>Dates</th><th>Hours</th><th>Participants</th>
        </tr></thead>
        <tbody>
            @forelse($trainings as $t)
                <tr style="border-top:1px solid #f3f4f6;">
                    <td style="padding:8px;"><a href="{{ route('training.show', $t) }}">{{ $t->title }}</a></td>
                    <td>{{ $t->type }}</td>
                    <td>{{ $t->date_from->format('M d') }}–{{ $t->date_to->format('M d, Y') }}</td>
                    <td>{{ $t->total_hours }}</td>
                    <td>{{ $t->participants_count }}</td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;color:#9ca3af;padding:12px;">No trainings recorded yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $trainings->links() }}
</div>

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;margin-top:16px;">
    <h3 style="font-size:14px;font-weight:700;margin-bottom:10px;">Record New Training</h3>
    <form method="POST" action="{{ route('training.store') }}" enctype="multipart/form-data" style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
        @csrf
        <input name="title" placeholder="Training title" required class="form-control form-control-sm" style="grid-column:span 2;">
        <select name="type" required class="form-control form-control-sm">
            <option value="in_house">In-House</option>
            <option value="external">External</option>
            <option value="online">Online</option>
            <option value="seminar">Seminar</option>
            <option value="workshop">Workshop</option>
            <option value="conference">Conference</option>
            <option value="scholarship">Scholarship</option>
            <option value="other">Other</option>
        </select>
        <input name="competency_area" placeholder="Competency area" class="form-control form-control-sm">
        <input name="institution" placeholder="Conducting institution" class="form-control form-control-sm">
        <input name="venue" placeholder="Venue" class="form-control form-control-sm">
        <input type="date" name="date_from" required class="form-control form-control-sm">
        <input type="date" name="date_to" required class="form-control form-control-sm">
        <input type="number" step="0.01" name="total_hours" placeholder="Total hours" required class="form-control form-control-sm">
        <input type="file" name="attachment" class="form-control form-control-sm">
        <button class="btn btn-sm btn-primary" style="grid-column:span 2;">Record Training</button>
    </form>
</div>
</x-dashboard-app>
