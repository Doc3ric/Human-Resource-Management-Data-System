<?php

namespace App\Support\Idcc;

/**
 * Module 9 Stage 4 — R.A. 10173 / NPC Circular 16-01 SPI detection.
 * Regex/keyword scan of extracted OCR text. Conservative by design: any
 * single match is enough to trigger the privacy tier, since false negatives
 * (missed SPI) are the higher-harm failure mode than false positives.
 */
class SpiDetector
{
    private const PATTERNS = [
        'GSIS_BP'    => '/\bGSIS[\s\-]?(?:BP|B\.P\.)?\s*[:#]?\s*\d{2}-\d{7}\b/i',
        'PHILHEALTH' => '/\b\d{2}-\d{9}-\d{1}\b/',
        'TIN'        => '/\b\d{3}-\d{3}-\d{3}(?:-\d{3,4})?\b/',
        'SSS'        => '/\b\d{2}-\d{7}-\d{1}\b/',
        'BLOOD_TYPE' => '/\bblood\s*type\s*[:\-]?\s*(A|B|AB|O)[+-]?\b/i',
    ];

    private const KEYWORDS = [
        'saln', 'statement of assets', 'net worth',
        'medical certificate', 'medical history', 'diagnosis', 'psychiatric',
        'home address', 'residential address',
    ];

    /** @return string[] which SPI categories matched (empty = none found) */
    public function scan(?string $text): array
    {
        if ($text === null || trim($text) === '') {
            return [];
        }

        $matches = [];

        foreach (self::PATTERNS as $label => $pattern) {
            if (preg_match($pattern, $text) === 1) {
                $matches[] = $label;
            }
        }

        $lower = strtolower($text);
        foreach (self::KEYWORDS as $keyword) {
            if (str_contains($lower, $keyword)) {
                $matches[] = 'KEYWORD:' . $keyword;
            }
        }

        return array_unique($matches);
    }

    public function containsSpi(?string $text): bool
    {
        return $this->scan($text) !== [];
    }
}
