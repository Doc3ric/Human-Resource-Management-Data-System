<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Print Documents</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .idcc-print-notice { background: #fef3c7; color: #92400e; padding: 10px 16px; font-size: 13px; }
        @media print { .idcc-print-notice { display: none; } }
        .idcc-print-page { page-break-after: always; padding: 20px; }
        .idcc-print-page:last-child { page-break-after: auto; }
        .idcc-print-page h3 { font-size: 14px; margin: 0 0 10px; }
        .idcc-print-page img { max-width: 100%; }
        .idcc-print-page iframe { width: 100%; height: 90vh; border: none; }
        .idcc-print-page pre { white-space: pre-wrap; font-size: 12.5px; }
    </style>
</head>
<body>
    @if($skipped)
        <div class="idcc-print-notice">
            Not included (no permission to view): {{ implode(', ', $skipped) }}
        </div>
    @endif

    @forelse($printable as $item)
        <div class="idcc-print-page">
            <h3>{{ $item['document']->original_filename }}</h3>
            @if($item['is_image'])
                <img src="{{ $item['data_uri'] }}" alt="{{ $item['document']->original_filename }}">
            @elseif($item['is_pdf'])
                <iframe src="{{ $item['preview_url'] }}"></iframe>
            @else
                <pre>{{ $item['document']->ocr_text ?: '(No extracted text available.)' }}</pre>
            @endif
        </div>
    @empty
        <div class="idcc-print-notice">No documents available to print.</div>
    @endforelse

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 400);
        });
    </script>
</body>
</html>
