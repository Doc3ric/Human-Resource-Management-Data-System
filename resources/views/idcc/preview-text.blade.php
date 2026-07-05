<x-dashboard-app>
<div class="idcc-detail-card" style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:22px;max-width:760px;margin:0 auto;">
    <h2 style="font-size:16px;font-weight:800;margin-bottom:4px;">{{ $document->original_filename }}</h2>
    <p style="color:#6b7280;font-size:12.5px;margin-bottom:16px;">
        No live in-browser renderer for {{ $document->mime_type }} — showing the extracted text instead.
        <a href="{{ route('idcc.download', $document) }}">Download the original file</a> to view it in its native application.
    </p>
    <pre style="white-space:pre-wrap;font-size:13px;background:#f9fafb;border:1px solid #f3f4f6;border-radius:8px;padding:14px;max-height:70vh;overflow:auto;">{{ $document->ocr_text ?: '(No extracted text available.)' }}</pre>
</div>
</x-dashboard-app>
