<?php

namespace App\Support\Idcc;

use Smalot\PdfParser\Parser as PdfParser;

/**
 * Tier 1 — text-layer PDFs (digitally generated/typed, not scanned). Reads
 * the embedded text layer directly, no OCR needed. A scanned/photographed
 * PDF has no text layer, so this correctly reports `failed` for those and
 * lets them fall through to Stage 2's manual-review path — rasterizing a
 * scanned PDF page to an image for Tesseract (Imagick/Ghostscript) is not
 * implemented, same gap already flagged for Tier 2/3 in OcrManager.
 */
class PdfTextExtractionDriver implements OcrDriverInterface
{
    public function isAvailable(): bool
    {
        return true;
    }

    public function supports(string $mimeType): bool
    {
        return $mimeType === 'application/pdf';
    }

    public function extract(string $absoluteFilePath, string $mimeType): OcrResult
    {
        if (!$this->supports($mimeType)) {
            return OcrResult::unavailable('TIER_1');
        }

        try {
            $pdf = (new PdfParser())->parseFile($absoluteFilePath);
            $text = trim($pdf->getText());
        } catch (\Throwable) {
            return new OcrResult(null, null, 'failed', 'TIER_1');
        }

        if ($text === '') {
            return new OcrResult(null, null, 'failed', 'TIER_1');
        }

        // Native text extraction is exact, not probabilistic — full confidence.
        return new OcrResult($text, 100.00, 'completed', 'TIER_1');
    }
}
