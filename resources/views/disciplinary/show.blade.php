<x-dashboard-app>
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;max-width:700px;">
    <h2 style="font-size:16px;font-weight:800;">{{ $case->case_no }}</h2>
    <p style="font-size:12.5px;color:#6b7280;">{{ str_replace('_', ' ', ucfirst($case->offense_classification)) }} — Status: {{ str_replace('_', ' ', ucfirst($case->status)) }}</p>

    @if(session('success'))
        <div style="background:#f0fdf4;color:#166534;padding:10px 14px;border-radius:8px;margin:14px 0;font-size:13px;">{{ session('success') }}</div>
    @endif

    <h4 style="font-size:13px;font-weight:700;margin-top:16px;">Formal Charge</h4>
    <p style="font-size:13px;">{{ $case->formal_charge }}</p>

    @if($case->decision)
        <h4 style="font-size:13px;font-weight:700;margin-top:16px;">Decision</h4>
        <p style="font-size:13px;">{{ $case->decision }}</p>
        <p style="font-size:13px;"><b>Penalty:</b> {{ $case->penalty }}</p>
    @endif

    <form method="POST" action="{{ route('disciplinary.update-status', $case) }}" style="margin-top:16px;display:grid;gap:8px;">
        @csrf
        <select name="status" required class="form-control form-control-sm">
            <option value="registered">Registered</option>
            <option value="under_investigation">Under Investigation</option>
            <option value="hearing">Hearing</option>
            <option value="decided">Decided</option>
            <option value="appealed">Appealed</option>
            <option value="closed">Closed</option>
        </select>
        <textarea name="decision" placeholder="Decision (if applicable)" class="form-control form-control-sm"></textarea>
        <textarea name="penalty" placeholder="Penalty (if applicable)" class="form-control form-control-sm"></textarea>

        <div>
            <p style="font-size:11.5px;color:#6b7280;margin-bottom:4px;">If marking as "Decided", an e-signature (CS Form No. 11 s.2025) is required:</p>
            <input name="signatory_name" placeholder="Signatory full name" class="form-control form-control-sm mb-2">
            <input name="signatory_position" placeholder="Signatory position" class="form-control form-control-sm mb-2">
            <x-signature-pad input-name="signature_image" />
        </div>

        <button class="btn btn-sm btn-primary">Update Case</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</x-dashboard-app>
