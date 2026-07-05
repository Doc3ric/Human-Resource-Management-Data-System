<x-dashboard-app>
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;max-width:800px;margin:0 auto;">
    <h2 style="font-size:16px;font-weight:800;">{{ $incident->reference_no }}</h2>
    <p style="font-size:12.5px;color:#6b7280;">{{ ucfirst($incident->category) }} — {{ $incident->incident_datetime->format('M d, Y g:i A') }} @ {{ $incident->location }}</p>

    @if(session('success'))
        <div style="background:#f0fdf4;color:#166534;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:13px;">{{ session('success') }}</div>
    @endif

    <h4 style="font-size:13px;font-weight:700;margin-top:16px;">Narrative</h4>
    <p style="font-size:13px;">{{ $incident->narrative }}</p>

    @if($incident->advisory_generated_at)
        <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:14px;margin-top:16px;">
            <p style="font-size:10.5px;font-weight:800;color:#92400e;text-transform:uppercase;">{{ \App\Support\Incident\RulesAdvisoryEngine::ADVISORY_LABEL }}</p>
            <ul style="font-size:12.5px;margin-top:8px;">
                @foreach($incident->advisory_citations as $c)
                    <li><b>{{ $c['provision'] }}</b> ({{ $c['classification'] }}) — {{ $c['penalty_range'] }}</li>
                @endforeach
            </ul>
            <p style="font-size:12.5px;margin-top:8px;"><b>Suggested Recommendation:</b> {{ $incident->advisory_recommendation }}</p>
        </div>
    @endif

    @if($incident->status === 'finalized')
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:14px;margin-top:16px;">
            <p style="font-size:12.5px;"><b>Finalized</b> — Track: {{ ucfirst($incident->final_track) }} by {{ optional($incident->finalizer)->name }} on {{ $incident->finalized_at->format('M d, Y') }}</p>
        </div>
    @else
        <div style="margin-top:16px;display:flex;gap:8px;">
            @if($incident->status === 'draft')
                <form method="POST" action="{{ route('incidents.advisory', $incident) }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-primary">Generate Advisory Draft</button>
                </form>
            @endif
            @if($incident->status === 'advisory_ready')
                <form method="POST" action="{{ route('incidents.finalize', $incident) }}">
                    @csrf
                    <select name="final_track" required class="form-control form-control-sm d-inline-block" style="width:auto;">
                        <option value="counseling">Counseling / Developmental</option>
                        <option value="escalated">Escalate to Formal RACCS Process</option>
                    </select>
                    <textarea name="counseling_recommendation" placeholder="Counseling recommendation (required for counseling track)" class="form-control form-control-sm mt-2"></textarea>
                    <button class="btn btn-sm btn-success mt-2">Finalize</button>
                </form>
            @endif
        </div>
    @endif
</div>
</x-dashboard-app>
