<?php

namespace App\Support\Idcc;

use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetIOFactory;

/**
 * Text-native formats don't need image OCR at all — extract directly via
 * the libraries already installed in this app (PhpWord, PhpSpreadsheet).
 * Always available; not tied to Tesseract.
 */
class NativeTextExtractionDriver implements OcrDriverInterface
{
    private const SUPPORTED = [
        'text/plain',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    public function isAvailable(): bool
    {
        return true;
    }

    public function supports(string $mimeType): bool
    {
        return in_array($mimeType, self::SUPPORTED, true);
    }

    public function extract(string $absoluteFilePath, string $mimeType): OcrResult
    {
        if (!$this->supports($mimeType)) {
            return OcrResult::unavailable('TIER_1');
        }

        try {
            $text = match ($mimeType) {
                'text/plain' => file_get_contents($absoluteFilePath) ?: '',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => $this->extractDocx($absoluteFilePath),
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => $this->extractXlsx($absoluteFilePath),
                default => '',
            };
        } catch (\Throwable) {
            return new OcrResult(null, null, 'failed', 'TIER_1');
        }

        if (trim($text) === '') {
            return new OcrResult(null, null, 'failed', 'TIER_1');
        }

        // Native text extraction is exact, not probabilistic — full confidence.
        return new OcrResult($text, 100.00, 'completed', 'TIER_1');
    }

    private function extractDocx(string $path): string
    {
        $phpWord = WordIOFactory::load($path);
        $text = [];
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $text[] = $element->getText();
                } elseif (method_exists($element, 'getElements')) {
                    foreach ($element->getElements() as $inner) {
                        if (method_exists($inner, 'getText')) {
                            $text[] = $inner->getText();
                        }
                    }
                }
            }
        }
        return implode("\n", array_filter($text));
    }

    private function extractXlsx(string $path): string
    {
        $spreadsheet = SpreadsheetIOFactory::load($path);
        $text = [];
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            foreach ($sheet->toArray() as $row) {
                $text[] = implode(' ', array_filter($row, fn ($v) => $v !== null && $v !== ''));
            }
        }
        return implode("\n", array_filter($text));
    }
}
