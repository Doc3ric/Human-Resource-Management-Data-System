<x-dashboard-app>
<style>
.rl-hero { background: linear-gradient(135deg,#3730a3 0%,#4338ca 55%,#4f46e5 100%); border-radius:14px; padding:24px 28px; margin-bottom:20px; color:#fff; }
.rl-hero h1 { font-size:20px; font-weight:800; margin:0 0 4px; }
.rl-hero p { font-size:12.5px; opacity:.85; margin:0; }
.rl-tabs { display:flex; gap:8px; margin-bottom:16px; }
.rl-tab { padding:8px 16px; border-radius:8px; font-size:12.5px; font-weight:700; text-decoration:none; color:#4b5563; background:#fff; border:1px solid #e5e7eb; }
.rl-tab.active { background:#4338ca; color:#fff; border-color:#4338ca; }
.rl-table { width:100%; border-collapse:collapse; font-size:12.5px; }
.rl-table th { text-align:left; padding:8px 10px; font-size:10.5px; text-transform:uppercase; color:#9ca3af; border-bottom:2px solid #e5e7eb; }
.rl-table td { padding:8px 10px; border-bottom:1px solid #f3f4f6; vertical-align:top; }
</style>

<div class="rl-hero">
    <h1><i class="bi bi-clock-history"></i> Recruitment View & NAP Retention</h1>
    <p>Module 2 — three-stage recruitment-record lifecycle (Active / Archived / Valueless Holding Queue), R.A. 10173 + NAP Records Disposition Schedule.</p>
</div>

@if(session('success'))
    <div style="background:#f0fdf4;color:#166534;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:13px;">{{ session('success') }}</div>
@endif

@if($stage === 'ARCHIVED')
    <div style="background:#fffbeb;color:#92400e;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:13px;">
        Notice: These records are past the standard 1-year operational retention limit. They will be scheduled for permanent disposal review upon fulfilling civil service requirements.
    </div>
@endif

<div class="rl-tabs">
    <a href="{{ route('recruitment-view.index', ['stage' => 'ACTIVE']) }}" class="rl-tab {{ $stage === 'ACTIVE' ? 'active' : '' }}">Active ({{ $counts['ACTIVE'] }})</a>
    <a href="{{ route('recruitment-view.index', ['stage' => 'ARCHIVED']) }}" class="rl-tab {{ $stage === 'ARCHIVED' ? 'active' : '' }}">Archived ({{ $counts['ARCHIVED'] }})</a>
    <a href="{{ route('recruitment-view.index', ['stage' => 'VALUELESS_QUEUE']) }}" class="rl-tab {{ $stage === 'VALUELESS_QUEUE' ? 'active' : '' }}">Valueless Holding Queue ({{ $counts['VALUELESS_QUEUE'] }})</a>
</div>

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;margin-bottom:16px;">
    <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;">
        <input type="hidden" name="stage" value="{{ $stage }}">
        <input type="text" name="search" value="{{ $search }}" placeholder="Search name, item no., reference no..."
            style="border:1px solid #d1d5db;border-radius:8px;padding:7px 12px;font-size:12.5px;min-width:260px;">
        <button class="btn btn-sm btn-primary">Search</button>
    </form>
</div>

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;overflow-x:auto;">
    <table class="rl-table">
        <thead>
            <tr>
                <th>Reference No.</th><th>Applicant</th><th>Item No.</th><th>Position Applied</th>
                <th>Applied</th><th>Filled</th>
                @if($stage === 'VALUELESS_QUEUE')<th>Disposal Authorization</th>@endif
            </tr>
        </thead>
        <tbody>
            @forelse($applicants as $a)
                <tr>
                    <td>{{ $a->reference_no }}</td>
                    <td>{{ $a->full_name }}</td>
                    <td>{{ $a->item_no ?: '—' }}</td>
                    <td>{{ $a->position_applied }}</td>
                    <td>{{ $a->applied_at?->format('M d, Y') ?: '—' }}</td>
                    <td>{{ $a->is_filled ? ($a->date_filled?->format('M d, Y') ?: 'Yes') : '—' }}</td>
                    @if($stage === 'VALUELESS_QUEUE')
                    <td>
                        <form method="POST" action="{{ route('recruitment-view.authorize-disposal', $a) }}" enctype="multipart/form-data" style="display:flex;gap:4px;align-items:center;">
                            @csrf
                            <input type="text" name="nap_form_reference" placeholder="NAP Form No. 3 ref." required
                                style="border:1px solid #d1d5db;border-radius:6px;padding:4px 8px;font-size:11.5px;width:140px;">
                            <input type="file" name="nap_form_file" style="font-size:11px;width:110px;">
                            <button type="submit" class="btn btn-sm btn-outline-danger" style="font-size:11px;">Authorize Disposal</button>
                        </form>
                    </td>
                    @endif
                </tr>
            @empty
                <tr><td colspan="7"><div style="text-align:center;padding:24px;color:#9ca3af;">No records in this stage.</div></td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($applicants->hasPages())
    <div style="margin-top:12px;">{{ $applicants->links() }}</div>
@endif
</x-dashboard-app>
