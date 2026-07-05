<x-dashboard-app>
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;">
    <h2 style="font-size:16px;font-weight:800;">{{ $training->title }}</h2>
    <p style="font-size:12.5px;color:#6b7280;">{{ $training->type }} — {{ $training->date_from->format('M d') }}–{{ $training->date_to->format('M d, Y') }} — {{ $training->total_hours }} hrs — {{ $training->venue }}</p>

    @if(session('success'))
        <div style="background:#f0fdf4;color:#166534;padding:10px 14px;border-radius:8px;margin:14px 0;font-size:13px;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div style="background:#fef2f2;color:#991b1b;padding:10px 14px;border-radius:8px;margin:14px 0;font-size:13px;">{{ session('error') }}</div>
    @endif

    <table style="width:100%;border-collapse:collapse;font-size:12.5px;margin-top:16px;">
        <thead><tr style="text-align:left;color:#9ca3af;font-size:10.5px;text-transform:uppercase;">
            <th style="padding:8px;">Participant</th><th>Status</th><th>Hours</th><th>Speaker?</th><th>Certificate</th><th></th>
        </tr></thead>
        <tbody>
            @forelse($participants as $p)
                <tr style="border-top:1px solid #f3f4f6;">
                    <td style="padding:8px;">{{ $p->displayName() }}</td>
                    <td>{{ ucfirst($p->attendance_status) }}</td>
                    <td>{{ $p->hours_attended ?? '—' }}</td>
                    <td>{{ $p->is_resource_speaker ? 'Yes' : 'No' }}</td>
                    <td>{{ $p->certificates->first()?->reference_no ?? '—' }}</td>
                    <td>
                        @if(!$p->certificates->count())
                            <form method="POST" action="{{ route('training.participants.certificate', $p) }}">
                                @csrf
                                <button class="btn btn-sm btn-outline-primary">Issue Certificate</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;color:#9ca3af;padding:12px;">No participants registered yet.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:14px;display:flex;gap:8px;">
        <form method="POST" action="{{ route('training.certificates.batch', $training) }}">
            @csrf
            <button class="btn btn-sm btn-success">Issue Certificates for Qualifying Roster (Batch)</button>
        </form>
    </div>

    <h4 style="font-size:13px;font-weight:700;margin-top:20px;">Register Participant</h4>
    <form method="POST" action="{{ route('training.participants.store', $training) }}" style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
        @csrf
        <input type="number" name="personnel_id" placeholder="Personnel ID (PGB only, optional)" class="form-control form-control-sm">
        <input name="participant_name" placeholder="Name (required if not PGB)" class="form-control form-control-sm">
        <input name="participant_office" placeholder="Office/Agency" class="form-control form-control-sm">
        <select name="attendance_status" required class="form-control form-control-sm">
            <option value="present">Present</option>
            <option value="partial">Partial</option>
            <option value="absent">Absent</option>
            <option value="excused">Excused</option>
        </select>
        <input type="number" step="0.01" name="hours_attended" placeholder="Hours attended" class="form-control form-control-sm">
        <label class="form-check-label"><input type="checkbox" name="is_resource_speaker" value="1"> Resource Speaker</label>
        <input name="topic" placeholder="Topic (if resource speaker)" class="form-control form-control-sm" style="grid-column:span 2;">
        <button class="btn btn-sm btn-primary" style="grid-column:span 2;">Register</button>
    </form>
</div>
</x-dashboard-app>
