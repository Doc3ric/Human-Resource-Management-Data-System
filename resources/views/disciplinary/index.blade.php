<x-dashboard-app>
<div style="background: linear-gradient(135deg,#450a0a 0%,#7f1d1d 100%); border-radius:14px; padding:24px 28px; margin-bottom:20px; color:#fff;">
    <h1 style="font-size:20px;font-weight:800;margin:0 0 4px;"><i class="bi bi-shield-lock-fill"></i> RACCS Disciplinary Cases</h1>
    <p style="font-size:12.5px;opacity:.85;margin:0;">RACCS-Confidential. Access restricted to Discipline Committee. All access is logged.</p>
</div>

@if(session('success'))
    <div style="background:#f0fdf4;color:#166534;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:13px;">{{ session('success') }}</div>
@endif

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;font-size:12.5px;">
        <thead><tr style="text-align:left;color:#9ca3af;font-size:10.5px;text-transform:uppercase;">
            <th style="padding:8px;">Case No.</th><th>Classification</th><th>Status</th><th>Filed</th>
        </tr></thead>
        <tbody>
            @forelse($cases as $c)
                <tr style="border-top:1px solid #f3f4f6;">
                    <td style="padding:8px;"><a href="{{ route('disciplinary.show', $c) }}">{{ $c->case_no }}</a></td>
                    <td>{{ str_replace('_', ' ', ucfirst($c->offense_classification)) }}</td>
                    <td>{{ str_replace('_', ' ', ucfirst($c->status)) }}</td>
                    <td>{{ $c->created_at->format('M d, Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align:center;color:#9ca3af;padding:12px;">No cases on file.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $cases->links() }}
</div>

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;margin-top:16px;">
    <h3 style="font-size:14px;font-weight:700;margin-bottom:10px;">Register New Case</h3>
    <form method="POST" action="{{ route('disciplinary.store') }}" style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
        @csrf
        <input type="number" name="personnel_id" placeholder="Personnel ID" required class="form-control form-control-sm">
        <select name="offense_classification" required class="form-control form-control-sm">
            <option value="light">Light</option>
            <option value="less_grave">Less Grave</option>
            <option value="grave">Grave</option>
        </select>
        <textarea name="formal_charge" placeholder="Formal charge" required class="form-control form-control-sm" style="grid-column:span 2;" rows="3"></textarea>
        <button class="btn btn-sm btn-danger" style="grid-column:span 2;">Register Case</button>
    </form>
</div>
</x-dashboard-app>
