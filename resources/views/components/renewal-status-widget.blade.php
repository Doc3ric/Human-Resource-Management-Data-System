@php
    // Module 1A.5 — ONE shared widget, rendered off the same is_renewed value
    // and the same renewal_signals rows everywhere it appears, so the
    // notification is inherently synchronized (resolving status in one
    // place updates it everywhere, since there is only one source).
    $period = $period ?? \App\Http\Controllers\BatchRenewalController::currentRatingPeriod();
    $isRenewed = (bool) $record->is_renewed;
    $signals = $isRenewed ? collect() : app(\App\Support\Renewal\RenewalSignalService::class)->forRecord($record, $period);
    $signalTypes = $signals->pluck('signal_type')->unique()->implode(', ');
@endphp

@unless($isRenewed)
<div style="background:var(--color-warning-bg,#fffbeb);border:1px solid #fde68a;border-radius:8px;padding:10px 14px;font-size:12.5px;color:#92400e;display:flex;align-items:center;gap:8px;">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>
        Not renewed for {{ $period }}
        @if($signals->count() > 0)
            · {{ $signals->count() }} activity signal(s) detected ({{ $signalTypes }})
        @endif
        · No authoritative renewal on file.
    </span>
    @if($showRequestButton ?? true)
        <form method="POST" action="{{ route('plantilla.request-renewal', $record) }}" style="margin-left:auto;">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-warning" style="font-size:11px;padding:2px 10px;">
                Request Renewal
            </button>
        </form>
    @endif
</div>
@endunless
