<x-dashboard-app>
<style>
.lgu-hero { background: linear-gradient(135deg,#1e3a5f 0%,#1a5276 55%,#0f4c75 100%); border-radius:14px; padding:24px 28px; margin-bottom:20px; color:#fff; }
.lgu-hero h1 { font-size:20px; font-weight:800; margin:0 0 4px; }
.lgu-hero p { font-size:12.5px; opacity:.8; margin:0; }
.lgu-table { width:100%; border-collapse:collapse; font-size:12.5px; }
.lgu-table th { text-align:left; padding:8px 10px; font-size:10.5px; text-transform:uppercase; color:#9ca3af; border-bottom:2px solid #e5e7eb; }
.lgu-table td { padding:8px 10px; border-bottom:1px solid #f3f4f6; }
.cc-ok { color:#166534; font-weight:700; }
.cc-warn { color:#b45309; font-weight:700; }
.cc-overdue { color:#dc2626; font-weight:700; }
</style>

<div class="lgu-hero">
    <h1><i class="bi bi-bank"></i> LGU Documents &amp; Service Requests</h1>
    <p>Executive Orders, Memoranda, Ordinances; service requests tracked against the R.A. 11032 Citizen's Charter window.</p>
</div>

@if(session('success'))
    <div style="background:#f0fdf4;color:#166534;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:13px;">{{ session('success') }}</div>
@endif

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;">
        <h3 style="font-size:14px;font-weight:700;margin-bottom:10px;">LGU Documents</h3>
        <table class="lgu-table">
            <thead><tr><th>Type</th><th>No.</th><th>Title</th><th>Date Issued</th></tr></thead>
            <tbody>
                @forelse($documents as $d)
                    <tr><td>{{ $d->type }}</td><td>{{ $d->number }}</td><td>{{ $d->title }}</td><td>{{ $d->date_issued->format('M d, Y') }}</td></tr>
                @empty
                    <tr><td colspan="4" style="text-align:center;color:#9ca3af;">None yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $documents->links() }}
        <form method="POST" action="{{ route('lgu.documents.store') }}" style="margin-top:12px;display:grid;gap:6px;">
            @csrf
            <select name="type" required class="form-control form-control-sm">
                <option value="EO">Executive Order</option>
                <option value="MEMO">Memorandum</option>
                <option value="ORDINANCE">Ordinance</option>
            </select>
            <input name="number" placeholder="Number" class="form-control form-control-sm">
            <input name="title" placeholder="Title" required class="form-control form-control-sm">
            <input type="date" name="date_issued" required class="form-control form-control-sm">
            <button class="btn btn-sm btn-primary">Register</button>
        </form>
    </div>

    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;">
        <h3 style="font-size:14px;font-weight:700;margin-bottom:10px;">Service Requests</h3>
        <table class="lgu-table">
            <thead><tr><th>Type</th><th>Requester</th><th>Due</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($requests as $r)
                    @php $days = $r->daysRemaining(); @endphp
                    <tr>
                        <td>{{ $r->request_type }}</td>
                        <td>{{ $r->requester_name }}</td>
                        <td>{{ $r->due_at->format('M d, Y') }}
                            <span class="{{ $days < 0 ? 'cc-overdue' : ($days <= 1 ? 'cc-warn' : 'cc-ok') }}">
                                ({{ $days < 0 ? abs($days).' day(s) overdue' : $days.' day(s) left' }})
                            </span>
                        </td>
                        <td>{{ ucfirst($r->status) }}</td>
                        <td>
                            @if($r->status !== 'completed')
                                <form method="POST" action="{{ route('lgu.requests.complete', $r) }}" style="display:inline;">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success">Complete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center;color:#9ca3af;">None yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $requests->links() }}
        <form method="POST" action="{{ route('lgu.requests.store') }}" style="margin-top:12px;display:grid;gap:6px;">
            @csrf
            <select name="request_type" required class="form-control form-control-sm">
                <option value="COE">Certificate of Employment</option>
                <option value="SERVICE_RECORD">Service Record</option>
                <option value="CERTIFICATION">Certification</option>
            </select>
            <input name="requester_name" placeholder="Requester name" required class="form-control form-control-sm">
            <input type="date" name="date_requested" required class="form-control form-control-sm">
            <button class="btn btn-sm btn-primary">Log Request</button>
        </form>
    </div>
</div>
</x-dashboard-app>
