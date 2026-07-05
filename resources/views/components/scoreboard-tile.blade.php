@props(['metricKey', 'compact' => false])

@php
    // Module 12.1 — the ONE shared scoreboard component. Every canonical
    // metric tile in the system renders through this, not a bespoke one-off.
    $metric = \App\Support\Metrics\MetricRegistry::get($metricKey, auth()->user());
    $tileId = 'tile-' . $metricKey . '-' . uniqid();
    $isNumeric = is_numeric($metric['value'] ?? null);
    $decimals = $isNumeric && floor($metric['value']) != $metric['value'] ? 2 : 0;
@endphp

@if($metric)
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:{{ $compact ? '12px 16px' : '18px 20px' }};display:inline-block;min-width:220px;"
     role="group" aria-label="{{ $metric['label'] }}: {{ $metric['value'] }}. {{ $metric['context'] }}">
    <div style="font-size:10.5px;text-transform:uppercase;letter-spacing:.05em;color:#9ca3af;font-weight:700;">{{ $metric['label'] }}</div>
    <div id="{{ $tileId }}" style="font-size:{{ $compact ? '22px' : '30px' }};font-weight:800;color:#111827;margin-top:4px;"
         data-scoreboard-value data-target-value="{{ $metric['value'] }}" data-decimals="{{ $decimals }}">
        {{ $isNumeric ? '0' : $metric['value'] }}
    </div>
    <div style="font-size:11.5px;color:#6b7280;margin-top:4px;">{{ $metric['context'] }}</div>
    @if(!empty($metric['insight']))
        <div style="font-size:11px;color:var(--color-accent,#2563eb);margin-top:4px;font-style:italic;">{{ $metric['insight'] }}</div>
    @endif
    <div style="font-size:10px;color:#9ca3af;margin-top:6px;">
        Owner: {{ $metric['owner'] }}
        @if(Route::has($metric['owner_route']))
            · <a href="{{ route($metric['owner_route']) }}">View full board</a>
        @endif
        · <span title="This tile reflects data as of page load">as of {{ now()->format('h:i A') }}</span>
    </div>
</div>
@once
<script>
    // Module 12.4 — friendly/alive display standard: a gentle count-up
    // animation on load, applied uniformly to every scoreboard-tile instance
    // (no per-page duplication of this logic).
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-scoreboard-value][data-target-value]').forEach(function (el) {
            const target = parseFloat(el.dataset.targetValue);
            if (isNaN(target)) return;
            const decimals = parseInt(el.dataset.decimals || '0', 10);
            const duration = 600;
            const start = performance.now();
            function tick(now) {
                const progress = Math.min((now - start) / duration, 1);
                const current = target * progress;
                el.textContent = current.toLocaleString(undefined, { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
                if (progress < 1) requestAnimationFrame(tick);
            }
            requestAnimationFrame(tick);
        });
    });
</script>
@endonce
@endif
