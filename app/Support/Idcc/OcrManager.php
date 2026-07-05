<?php

namespace App\Support\Idcc;

/**
 * Module 9 Stage 2 dispatcher — picks the right Tier 1 driver for the
 * payload's mime type. A *scanned* PDF (no text layer) still needs page
 * rasterization (Imagick/Ghostscript) to run through Tesseract, which isn't
 * installed on this host — `PdfTextExtractionDriver` correctly reports
 * `failed` for those and they fall through to Stage 2's existing "flag for
 * human validation" path, rather than silently dropping the file (Stage 1's
 * "never drop" rule).
 *
 * Tier 2 (handwriting/messy-photo vision model) and Tier 3 (external Vision
 * LLM) are not implemented in this pass — no on-premise vision model or
 * external API integration exists in this codebase yet. See
 * TieredExtractionService for how a future Tier 2/3 driver plugs in here
 * without changing the pipeline.
 */
class OcrManager
{
    public function __construct(
        private readonly TesseractOcrDriver $tesseract,
        private readonly NativeTextExtractionDriver $nativeText,
        private readonly PdfTextExtractionDriver $pdfText,
        private readonly SpiDetector $spiDetector,
        private readonly VisionLlmDriver $visionLlm
    ) {
    }

    public function extract(string $absoluteFilePath, string $mimeType): OcrResult
    {
        $result = OcrResult::unavailable('TIER_1');

        if ($this->nativeText->supports($mimeType)) {
            $result = $this->nativeText->extract($absoluteFilePath, $mimeType);
        } elseif ($this->pdfText->supports($mimeType)) {
            $result = $this->pdfText->extract($absoluteFilePath, $mimeType);
        } elseif ($this->tesseract->supports($mimeType)) {
            $result = $this->tesseract->extract($absoluteFilePath, $mimeType);
        }

        // If Tier 1 produced text, we run SPI detection.
        // If clean AND Vision LLM is enabled, we can enhance extraction with Tier 3.
        if ($result->status === 'completed' && $this->visionLlm->isAvailable() && $this->visionLlm->supports($mimeType)) {
            if (!$this->spiDetector->containsSpi($result->text)) {
                $tier3Result = $this->visionLlm->extract($absoluteFilePath, $mimeType);
                if ($tier3Result->status === 'completed') {
                    return $tier3Result;
                }
            }
        }

        return $result;
    }
}
