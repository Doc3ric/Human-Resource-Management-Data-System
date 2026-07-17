@extends('layouts.app')

@section('content')
<style>
    .premium-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 24px; }
    .metric-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 20px; }
    .metric-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px 20px; }
    .metric-value { font-size: 22px; font-weight: 700; color: #111827; }
    .metric-label { font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; margin-top: 4px; }
    .btn-primary { background: #2563eb; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; text-decoration: none; display: inline-block; }
    .btn-primary:hover { background: #1d4ed8; }
    .btn-sm { padding: 5px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; border: none; cursor: pointer; text-decoration: none; display: inline-block; }
    table th { font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; text-align: left; padding: 10px 12px; border-bottom: 1px solid #e5e7eb; }
    table td { font-size: 13px; color: #1f2937; padding: 10px 12px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
    .badge { padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; }
    .badge-active { background: #dbeafe; color: #1d4ed8; }
    .badge-nearing { background: #fef3c7; color: #92400e; }
    .badge-overdue { background: #fee2e2; color: #991b1b; }
    .badge-recalled { background: #e5e7eb; color: #374151; }
    .badge-extended { background: #dcfce7; color: #166534; }

    .modal-backdrop { display:none; position:fixed; inset:0; background:rgba(17,24,39,0.5); z-index:50; align-items:center; justify-content:center; }
    .modal-backdrop.open { display:flex; }
    .modal-box { background:#fff; border-radius:12px; padding:24px; width:420px; max-width:90vw; box-shadow:0 10px 25px rgba(0,0,0,0.15); }
    .modal-title { font-size:16px; font-weight:700; color:#111827; margin:0 0 4px; }
    .modal-subtitle { font-size:13px; color:#6b7280; margin:0 0 16px; }
    .form-label { font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; margin-bottom: 6px; display: block; }
    .form-control { border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 12px; font-size: 13px; color: #1f2937; width: 100%; box-sizing: border-box; }
    .modal-actions { display:flex; justify-content:flex-end; gap:8px; margin-top:20px; }
    .btn-secondary { background:#fff; color:#374151; border:1px solid #d1d5db; padding:9px 16px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; }
</style>

<div class="mb-4" style="display:flex; justify-content:space-between; align-items:flex-end;">
    <div>
        <h2 style="font-size: 24px; font-weight: 700; color: #111827; margin: 0;">Detail Orders</h2>
        <p style="font-size: 14px; color: #6b7280; margin: 4px 0 0;">CSC 1-year detail limit tracking &mdash; a daily job flags orders nearing/past the limit and auto-drafts a recall letter.</p>
    </div>
    <a href="{{ route('detail-orders.create') }}" class="btn-primary">+ Record Detail Order</a>
</div>

@if (session('success'))
    <div class="premium-card" style="border-color:#bbf7d0; background:#f0fdf4; margin-bottom:16px; color:#166534; font-size:13px;">
        {{ session('success') }}
    </div>
@endif

<div class="metric-row">
    <div class="metric-card"><div class="metric-value">{{ $metrics['total'] }}</div><div class="metric-label">Total</div></div>
    <div class="metric-card"><div class="metric-value">{{ $metrics['active'] }}</div><div class="metric-label">Active</div></div>
    <div class="metric-card"><div class="metric-value">{{ $metrics['nearing_expiry'] }}</div><div class="metric-label">Nearing Expiry</div></div>
    <div class="metric-card"><div class="metric-value">{{ $metrics['overdue'] }}</div><div class="metric-label">Overdue</div></div>
</div>

<div class="premium-card">
    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr>
                <th>Employee</th>
                <th>Order No.</th>
                <th>Detailed Unit</th>
                <th>Effective Start</th>
                <th>1-Year Mark</th>
                <th>Status</th>
                <th>Recall Letter</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                <tr>
                    <td>{{ $order->plantillaRecord?->last_name }}, {{ $order->plantillaRecord?->first_name }}</td>
                    <td>{{ $order->detail_order_no }}</td>
                    <td>{{ $order->detailed_unit }}</td>
                    <td>{{ $order->date_effective_start->format('M d, Y') }}</td>
                    <td>{{ $order->one_year_mark->format('M d, Y') }}</td>
                    <td>
                        <span class="badge badge-{{ strtolower(str_replace(' ', '', $order->status)) === 'nearingexpiry' ? 'nearing' : strtolower($order->status) }}">
                            {{ $order->status }}
                        </span>
                    </td>
                    <td>
                        @if($order->recall_letter_id)
                            <a href="{{ route('detail-orders.recall-letter', $order->detail_id) }}" style="color:#2563eb; font-size:12px;">View Draft</a>
                        @else
                            &mdash;
                        @endif
                    </td>
                    <td>
                        @if(!in_array($order->status, ['Recalled', 'Extended']))
                            <div style="display:flex; gap:6px;">
                                <form action="{{ route('detail-orders.recall', $order->detail_id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn-sm" style="background:#e5e7eb; color:#374151;">Mark Recalled</button>
                                </form>
                                <button type="button" class="btn-sm" style="background:#dbeafe; color:#1d4ed8;"
                                    onclick="openExtendModal({{ $order->detail_id }}, '{{ $order->detail_order_no }}', '{{ $order->date_effective_end?->toDateString() }}')">
                                    Extend
                                </button>
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center; color:#6b7280; padding: 24px;">No detail orders recorded yet.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 16px;">
        {{ $orders->links() }}
    </div>
</div>

<!-- Extend Detail Order modal -->
<div class="modal-backdrop" id="extendModalBackdrop">
    <div class="modal-box">
        <p class="modal-title">Extend Detail Order</p>
        <p class="modal-subtitle" id="extendModalSubtitle">Record a properly-authorized extension beyond the CSC 1-year limit.</p>

        <form id="extendForm" method="POST">
            @csrf
            <div style="margin-bottom:14px;">
                <label class="form-label">New Effective End Date <span style="color:#ef4444;">*</span></label>
                <input type="date" name="date_effective_end" id="extendEndDateInput" class="form-control" required>
            </div>
            <div>
                <label class="form-label">Extension Authority / Basis</label>
                <textarea name="remarks" class="form-control" rows="3" placeholder="e.g. Office Order No. ___ authorizing extension, signed by ___"></textarea>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeExtendModal()">Cancel</button>
                <button type="submit" class="btn-primary">Save Extension</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openExtendModal(detailId, orderNo, currentEndDate) {
        document.getElementById('extendModalSubtitle').textContent =
            'Detail Order ' + orderNo + ' — record a properly-authorized extension beyond the CSC 1-year limit.';
        document.getElementById('extendForm').action = '{{ url("detail-orders") }}/' + detailId + '/extend';

        const endDateInput = document.getElementById('extendEndDateInput');
        // Suggest a date after the current end date (or today) rather than leaving it blank.
        const base = currentEndDate ? new Date(currentEndDate + 'T00:00:00') : new Date();
        base.setDate(base.getDate() + 1);
        endDateInput.min = new Date(new Date().setDate(new Date().getDate() + 1)).toISOString().slice(0, 10);
        endDateInput.value = base.toISOString().slice(0, 10);

        document.getElementById('extendModalBackdrop').classList.add('open');
    }

    function closeExtendModal() {
        document.getElementById('extendModalBackdrop').classList.remove('open');
    }
</script>
@endsection
