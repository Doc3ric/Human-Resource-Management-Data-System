<?php

namespace App\Support\Idcc;

use App\Models\Document;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Module 9 — orchestrates Stages 1-4 for a single captured payload.
 * Stage 5 (RACCS storage segregation) happens here too, since it's decided
 * by the same classification pass. Stages 6-8 (relationships, foldering
 * view, retention) operate on already-ingested documents — see
 * DocumentRelationService / retention rule lookups, not this class.
 */
class IdccPipeline
{
    public function __construct(
        private readonly OcrManager $ocr,
        private readonly DocumentClassifier $classifier,
        private readonly DocumentEncryptor $encryptor,
    ) {
    }

    /**
     * @param array{capture_source?: string, attachment_field?: string, personnel_id?: int, personnel_type?: string, ingested_by?: int} $context
     */
    public function ingest(UploadedFile $file, array $context): Document
    {
        $absolutePath = $file->getRealPath();
        $sha256 = hash_file('sha256', $absolutePath);
        $originalName = $file->getClientOriginalName();
        $mime = $file->getMimeType() ?: 'application/octet-stream';
        $size = $file->getSize() ?: 0;

        // Stage 2 — OCR / text extraction runs on the plain uploaded bytes.
        $ocrResult = $this->ocr->extract($absolutePath, $mime);

        // Stage 3 — classification.
        $classification = $this->classifier->classify($originalName, $ocrResult->text);

        // Stage 5 — RACCS docs are stored in a segregated subtree (logical
        // isolation; true physical air-gapping is an infrastructure decision
        // outside this codebase).
        $dir = $classification['is_raccs'] ? 'idcc/raccs' : 'idcc/documents';
        $extension = $file->getClientOriginalExtension() ?: 'bin';
        $relativePath = "{$dir}/" . bin2hex(random_bytes(16)) . ".{$extension}";

        // Stage 4 — SPI (and RACCS) documents are encrypted at rest.
        $needsEncryption = $classification['is_spi'] || $classification['is_raccs'];
        $contents = file_get_contents($absolutePath);

        Storage::disk('local')->put(
            $relativePath,
            $needsEncryption ? $this->encryptor->encrypt($contents) : $contents
        );
        unset($contents);

        $status = $ocrResult->status === 'completed' ? 'classified' : 'manual_review';
        $reviewReason = $ocrResult->status !== 'completed'
            ? "OCR {$ocrResult->status} for this format on this host — needs manual review"
            : null;

        return Document::create([
            'sha256_hash' => $sha256,
            'original_filename' => $originalName,
            'mime_type' => $mime,
            'size_bytes' => $size,
            'storage_path' => $relativePath,
            'capture_source' => $context['capture_source'] ?? 'UPLOAD',
            'attachment_field' => $context['attachment_field'] ?? null,
            'extraction_tier' => $ocrResult->tier,
            'ocr_text' => $ocrResult->text,
            'ocr_confidence' => $ocrResult->confidence,
            'ocr_status' => $ocrResult->status,
            'doc_type_code' => $classification['doc_type_code'],
            'importance_class' => $classification['importance_class'],
            'privacy_tier' => $classification['privacy_tier'],
            'abstract_description' => $classification['abstract_description'],
            'is_spi' => $classification['is_spi'],
            'is_raccs' => $classification['is_raccs'],
            'search_keyword' => $classification['search_keyword'],
            'encrypted' => $needsEncryption,
            'personnel_id' => $context['personnel_id'] ?? null,
            'personnel_type' => $context['personnel_type'] ?? null,
            'status' => $status,
            'review_reason' => $reviewReason,
            'ingested_by' => $context['ingested_by'] ?? null,
        ]);
    }

    /**
     * Re-run Stages 2-3 (OCR + classification) on an already-ingested
     * document, using whichever OCR drivers are available *now* — e.g. after
     * installing Tesseract/a PDF parser post-ingestion. A document that was
     * originally stuck in `manual_review`/doc_type `OTHER` for lack of
     * extractable text doesn't fix itself retroactively otherwise.
     *
     * Never downgrades encryption: if the file is already stored encrypted,
     * it stays encrypted even if reclassification would no longer require
     * it — re-encrypting plaintext-at-rest is safe to add, silently
     * decrypting previously-protected content is not.
     *
     * KNOWN GAP: the actual access-control check (DocumentAccessPolicy)
     * reads the `is_raccs` column, not the storage path, so a document that
     * newly turns out to be RACCS on reprocess is still correctly gated —
     * but the file itself is left in its original `idcc/documents` path
     * rather than moved into the segregated `idcc/raccs` subtree. Flagged,
     * not silently ignored, matching this module's existing gap-reporting
     * convention.
     */
    public function reprocess(Document $document): Document
    {
        $stored = Storage::disk('local')->get($document->storage_path);
        $plainContents = $document->encrypted ? $this->encryptor->decrypt($stored) : $stored;

        $tempPath = tempnam(sys_get_temp_dir(), 'idcc_reprocess_');
        file_put_contents($tempPath, $plainContents);

        try {
            $ocrResult = $this->ocr->extract($tempPath, $document->mime_type);
            $classification = $this->classifier->classify($document->original_filename, $ocrResult->text);
        } finally {
            @unlink($tempPath);
        }

        $needsEncryption = $document->encrypted || $classification['is_spi'] || $classification['is_raccs'];
        if ($needsEncryption && !$document->encrypted) {
            Storage::disk('local')->put($document->storage_path, $this->encryptor->encrypt($plainContents));
        }
        unset($plainContents, $stored);

        $status = $ocrResult->status === 'completed' ? 'classified' : 'manual_review';
        $reviewReason = $ocrResult->status !== 'completed'
            ? "OCR {$ocrResult->status} for this format on this host — needs manual review"
            : null;

        $document->update([
            'extraction_tier' => $ocrResult->tier,
            'ocr_text' => $ocrResult->text,
            'ocr_confidence' => $ocrResult->confidence,
            'ocr_status' => $ocrResult->status,
            'doc_type_code' => $classification['doc_type_code'],
            'importance_class' => $classification['importance_class'],
            'privacy_tier' => $classification['privacy_tier'],
            'abstract_description' => $classification['abstract_description'],
            'is_spi' => $classification['is_spi'],
            'is_raccs' => $document->is_raccs || $classification['is_raccs'],
            'search_keyword' => $classification['search_keyword'],
            'encrypted' => $needsEncryption,
            'status' => $status,
            'review_reason' => $reviewReason,
        ]);

        return $document->fresh();
    }
}
