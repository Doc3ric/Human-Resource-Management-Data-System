<x-dashboard-app>
<style>
.rs-hero { background: linear-gradient(135deg,#1e3a5f 0%,#1a5276 55%,#0f4c75 100%); border-radius:14px; padding:24px 28px; margin-bottom:20px; color:#fff; }
.rs-hero h1 { font-size:20px; font-weight:800; margin:0 0 4px; }
.rs-hero p { font-size:12.5px; opacity:.8; margin:0; }
.rs-table { width:100%; border-collapse:collapse; font-size:12.5px; }
.rs-table th { text-align:left; padding:8px 10px; font-size:10.5px; text-transform:uppercase; color:#9ca3af; border-bottom:2px solid #e5e7eb; }
.rs-table td { padding:8px 10px; border-bottom:1px solid #f3f4f6; vertical-align:top; }
.rs-tag { font-size:10px; font-weight:700; padding:2px 8px; border-radius:99px; }
.tag-perm { background:#eff6ff; color:#1e40af; }
.tag-temp { background:#fefce8; color:#854d0e; }
</style>

<div class="rs-hero">
    <h1><i class="bi bi-archive-fill"></i> Records Retention &amp; Disposal Matrix</h1>
    <p>NAP General Records Disposition Schedule reference — R.A. 9470 Sec. 18 Art. III: no disposal without NAP Executive Director authority.</p>
</div>

@if(session('success'))
    <div style="background:#f0fdf4;color:#166534;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:13px;">{{ session('success') }}</div>
@endif

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;margin-bottom:16px;">
    <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search series, description, NAP ref..."
            style="border:1px solid #d1d5db;border-radius:8px;padding:7px 12px;font-size:12.5px;min-width:240px;">
        <select name="category" style="border:1px solid #d1d5db;border-radius:8px;padding:7px 12px;font-size:12.5px;">
            <option value="">All Categories</option>
            @foreach(['201','Leave','Appointment','Disciplinary','Financial','LGU'] as $cat)
                <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
        </select>
        <button class="btn btn-sm btn-primary">Search</button>
        <a href="{{ route('retention-schedule.export-csv') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-download"></i> Export CSV
        </a>
    </form>
</div>

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;overflow-x:auto;">
    <table class="rs-table">
        <thead>
            <tr>
                <th>Series</th><th>Category</th><th>Time Value</th><th>Active Period</th>
                <th>Storage Period</th><th>Disposition</th><th>Legal Basis</th><th>NAP Ref</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $r)
                <tr>
                    <td><b>{{ $r->record_series_title }}</b><div style="color:#9ca3af;">{{ $r->description }}</div></td>
                    <td>{{ $r->record_category }}</td>
                    <td><span class="rs-tag {{ $r->time_value === 'PERMANENT' ? 'tag-perm' : 'tag-temp' }}">{{ $r->time_value }}</span></td>
                    <td>{{ $r->active_period }}</td>
                    <td>{{ $r->storage_period }}</td>
                    <td>{{ $r->disposition_action }}</td>
                    <td>{{ $r->legal_basis }}</td>
                    <td>{{ $r->nap_grds_item_ref }}</td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center;color:#9ca3af;">No entries yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $rows->links() }}
</div>

@if($canEdit)
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;margin-top:16px;">
    <h3 style="font-size:14px;font-weight:700;margin-bottom:10px;">Add Retention Series</h3>
    <form method="POST" action="{{ route('retention-schedule.store') }}" style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;">
        @csrf
        <input name="record_series_title" placeholder="Series title" required class="form-control form-control-sm">
        <input name="record_category" placeholder="Category (201/Leave/...)" required class="form-control form-control-sm">
        <select name="time_value" required class="form-control form-control-sm">
            <option value="PERMANENT">Permanent</option>
            <option value="TEMPORARY">Temporary</option>
        </select>
        <input name="active_period" placeholder="Active period" class="form-control form-control-sm">
        <input name="storage_period" placeholder="Storage period" class="form-control form-control-sm">
        <input name="total_retention" placeholder="Total retention" class="form-control form-control-sm">
        <select name="disposition_action" required class="form-control form-control-sm">
            <option value="PERMANENT_PRESERVATION">Permanent Preservation</option>
            <option value="DESTRUCTION">Destruction</option>
            <option value="REVIEW">Review</option>
        </select>
        <input name="legal_basis" placeholder="Legal basis" class="form-control form-control-sm">
        <input name="nap_grds_item_ref" placeholder="NAP GRDS ref" class="form-control form-control-sm">
        <button class="btn btn-sm btn-primary" style="grid-column:span 3;">Add Entry</button>
    </form>
</div>
@endif
</x-dashboard-app>
