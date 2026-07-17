<?php

namespace App\Support\Idcc;

/**
 * Module 9 Stage 3 — classification. Keyword/filename-driven (no external
 * LLM call — everything here runs on-premise, consistent with the rest of
 * the pipeline). abstract_description is a short heuristic summary; an
 * Administrator can always edit it — this is a starting point, not a final
 * word.
 */
class DocumentClassifier
{
    /** @var array<string, string[]> doc_type_code => keywords to match against filename+text */
    private const DOC_TYPE_KEYWORDS = [
        'APPT-33A'   => ['appointment form 33', 'csc form 33', 'notice of appointment'],
        'PDS-212'    => ['personal data sheet', 'csc form 212', 'pds'],
        'LEAVE-CSC6' => ['application for leave', 'csc form 6', 'leave application'],
        'DISC-RACCS' => ['disciplinary', 'formal charge', 'raccs', 'administrative case', 'complaint affidavit'],
        'LGU-EO'     => ['executive order', 'memorandum circular', 'ordinance'],
        'COE-REQ'    => ['certificate of employment', 'coe request'],
    ];

    private const PERMANENT_TYPES = ['APPT-33A', 'PDS-212'];
    private const VITAL_TYPES = ['APPT-33A', 'DISC-RACCS'];

    public function __construct(private readonly SpiDetector $spiDetector)
    {
    }

    /**
     * $raccsEligible lets a caller who already knows a document's true
     * nature (e.g. a system-generated leave-violation notice, which quotes
     * CSC rules and so trips the 'disciplinary' keyword below) opt out of
     * the DISC-RACCS match. Genuine RACCS case files (formal charges,
     * complaint affidavits) are tracked separately via the Discipline Case
     * registry and are never ingested with that flag set false.
     */
    public function classify(string $filename, ?string $ocrText, bool $raccsEligible = true): array
    {
        $haystack = strtolower($filename . ' ' . ($ocrText ?? ''));
        $docTypeCode = 'OTHER';

        foreach (self::DOC_TYPE_KEYWORDS as $code => $keywords) {
            if ($code === 'DISC-RACCS' && !$raccsEligible) {
                continue;
            }
            foreach ($keywords as $keyword) {
                if (str_contains($haystack, $keyword)) {
                    $docTypeCode = $code;
                    break 2;
                }
            }
        }

        $isRaccs = $docTypeCode === 'DISC-RACCS';
        $spiMatches = $this->spiDetector->scan($ocrText);
        $isSpi = $spiMatches !== [] || $isRaccs;

        $importanceClass = match (true) {
            in_array($docTypeCode, self::VITAL_TYPES, true) => 'Vital',
            $docTypeCode === 'OTHER' => 'Routine',
            default => 'Important',
        };

        // Privacy tier: 1 Restricted (SPI/RACCS), 2 Protected (personnel-linked
        // but no detected SPI), 3 Internal (general/administrative).
        $privacyTier = match (true) {
            $isSpi => 1,
            $docTypeCode !== 'OTHER' => 2,
            default => 3,
        };

        return [
            'doc_type_code' => $docTypeCode,
            'importance_class' => $importanceClass,
            'privacy_tier' => $privacyTier,
            'is_spi' => $isSpi,
            'is_raccs' => $isRaccs,
            'spi_matches' => $spiMatches,
            'search_keyword' => $this->extractKeyword($filename, $ocrText, $docTypeCode),
            'abstract_description' => $this->summarize($docTypeCode, $ocrText),
        ];
    }

    private function extractKeyword(string $filename, ?string $ocrText, string $docTypeCode): string
    {
        $text = $filename . ' ' . ($ocrText ?? '');

        // 1. Check for specific Case or Resolution Numbers
        if (preg_match('/(?:Case|Resolution)\s*No\.?\s*([A-Za-z0-9\-]+)/i', $text, $matches)) {
            return strtoupper(trim($matches[0]));
        }

        // 2. Fall back to the category name if it's unknown
        if ($docTypeCode === 'OTHER') {
            return 'Uncategorized';
        }

        // 3. Extract the first capitalized phrase of 2-4 words from OCR (often a name or subject)
        // Only look at the first 1000 characters of OCR to find the subject
        $head = substr(preg_replace('/\s+/', ' ', $ocrText ?? ''), 0, 1000);
        if (preg_match('/(?:[A-Z][a-z]+\s+){1,3}[A-Z][a-z]+/', $head, $matches)) {
            return trim($matches[0]);
        }

        // 4. Default to the filename base
        return pathinfo($filename, PATHINFO_FILENAME);
    }

    private function summarize(string $docTypeCode, ?string $ocrText): string
    {
        if ($ocrText === null || trim($ocrText) === '') {
            return "A {$docTypeCode} document. No text could be extracted for a detailed summary; manual review recommended.";
        }

        $snippet = trim(preg_replace('/\s+/', ' ', $ocrText));
        $snippet = mb_substr($snippet, 0, 220);

        return "A {$docTypeCode} document. Extracted content begins: \"{$snippet}...\"";
    }
}
