<x-dashboard-app>
<style>
.idcc-detail-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:22px; max-width:720px; margin:0 auto; }
.idcc-detail-row { display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f3f4f6; font-size:13px; }
.idcc-detail-row b { color:#6b7280; font-weight:600; }
</style>

<div class="idcc-detail-card">
    <h2 style="font-size:18px;font-weight:800;margin-bottom:4px;">{{ $document->original_filename }}</h2>
    <p style="color:#6b7280;font-size:13px;margin-bottom:16px;">{{ $document->abstract_description }}</p>

    <div class="idcc-detail-row"><b>Doc Type</b><span>{{ $document->doc_type_code }}</span></div>
    <div class="idcc-detail-row"><b>Importance</b><span>{{ $document->importance_class }}</span></div>
    <div class="idcc-detail-row"><b>Privacy Tier</b><span>{{ $document->privacy_tier }}</span></div>
    <div class="idcc-detail-row"><b>Status</b><span>{{ $document->status }}</span></div>
    @if($document->review_reason)
        <div class="idcc-detail-row"><b>Review Reason</b><span>{{ $document->review_reason }}</span></div>
    @endif
    <div class="idcc-detail-row"><b>Capture Source</b><span>{{ $document->capture_source }}</span></div>
    <div class="idcc-detail-row"><b>Extraction Tier</b><span>{{ $document->extraction_tier ?? '—' }}</span></div>
    <div class="idcc-detail-row"><b>OCR Confidence</b><span>{{ $document->ocr_confidence ?? '—' }}</span></div>
    <div class="idcc-detail-row"><b>SPI</b><span>{{ $document->is_spi ? 'Yes — encrypted at rest' : 'No' }}</span></div>
    <div class="idcc-detail-row"><b>RACCS</b><span>{{ $document->is_raccs ? 'Yes — confidentiality wall applies' : 'No' }}</span></div>
    <div class="idcc-detail-row"><b>Ingested By</b><span>{{ optional($document->ingestedBy)->name ?? '—' }}</span></div>
    <div class="idcc-detail-row"><b>Ingested At</b><span>{{ $document->created_at->toDayDateTimeString() }}</span></div>
    @if($retentionRule)
        <div class="idcc-detail-row"><b>Retention</b><span>{{ $retentionRule->is_permanent ? 'Permanent' : ($retentionRule->retention_years . ' years') }}</span></div>
    @endif

    <div style="margin-top:18px;">
        <a href="{{ route('idcc.download', $document) }}" class="btn btn-sm btn-primary">
            <i class="bi bi-download"></i> Download
        </a>
        <a href="{{ route('idcc.index') }}" class="btn btn-sm btn-outline-secondary">Back to Documents</a>
    </div>

    <hr style="margin:20px 0;">

    <h3 style="font-size:14px;font-weight:700;margin-bottom:10px;">Related Documents (Stage 6)</h3>
    @forelse($relations as $rel)
        <div class="idcc-detail-row">
            <b>{{ $rel->relation_type }}</b>
            <span>
                @if($rel->relatedDocument)
                    <a href="{{ route('idcc.show', $rel->relatedDocument) }}">{{ $rel->relatedDocument->original_filename }}</a>
                @else
                    (unavailable)
                @endif
            </span>
        </div>
    @empty
        <p style="font-size:12.5px;color:#9ca3af;">No linked documents yet.</p>
    @endforelse

    <form method="POST" action="{{ route('idcc.relate', $document) }}" style="margin-top:12px;display:flex;gap:8px;">
        @csrf
        <input type="number" name="related_document_id" placeholder="Related document ID" required
            style="border:1px solid #d1d5db;border-radius:8px;padding:6px 10px;font-size:12.5px;width:160px;">
        <input type="text" name="relation_type" placeholder="e.g. SUPPORTING_ELIGIBILITY" required
            style="border:1px solid #d1d5db;border-radius:8px;padding:6px 10px;font-size:12.5px;flex:1;">
        <button class="btn btn-sm btn-outline-primary">Link</button>
    </form>
</div>
</x-dashboard-app>
