<?php

namespace App\Support\Idcc;

use Symfony\Component\Process\Process;

/**
 * Tier 1 — on-premise Tesseract for clean printed images. No data leaves
 * the LAN. Gracefully reports unavailable if the binary isn't installed on
 * this host, rather than failing the whole ingestion pipeline.
 */
class TesseractOcrDriver implements OcrDriverInterface
{
    private const IMAGE_MIMES = ['image/jpeg', 'image/png', 'image/tiff'];

    // Common Windows install locations, checked if the bare `tesseract`
    // command isn't resolvable on PATH — e.g. a long-running Apache/service
    // process started before the binary was installed won't see a PATH
    // update until it's restarted, so we don't want to depend on that.
    private const WINDOWS_FALLBACK_PATHS = [
        'C:\\Program Files\\Tesseract-OCR\\tesseract.exe',
        'C:\\Program Files (x86)\\Tesseract-OCR\\tesseract.exe',
    ];

    public function isAvailable(): bool
    {
        return $this->resolveBinary() !== null;
    }

    private function resolveBinary(): ?string
    {
        static $resolved = false;
        static $binary = null;
        if ($resolved) {
            return $binary;
        }
        $resolved = true;

        $command = PHP_OS_FAMILY === 'Windows' ? ['where', 'tesseract'] : ['which', 'tesseract'];
        $process = new Process($command);
        $process->run();
        if ($process->isSuccessful()) {
            return $binary = 'tesseract';
        }

        if (PHP_OS_FAMILY === 'Windows') {
            foreach (self::WINDOWS_FALLBACK_PATHS as $path) {
                if (file_exists($path)) {
                    return $binary = $path;
                }
            }
        }

        return $binary = null;
    }

    public function supports(string $mimeType): bool
    {
        return in_array($mimeType, self::IMAGE_MIMES, true);
    }

    public function extract(string $absoluteFilePath, string $mimeType): OcrResult
    {
        $binary = $this->resolveBinary();
        if ($binary === null || !$this->supports($mimeType)) {
            return OcrResult::unavailable('TIER_1');
        }

        $outputBase = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ocr_' . uniqid();

        $process = new Process([$binary, $absoluteFilePath, $outputBase, 'tsv']);
        $process->setTimeout(120);
        $process->run();

        $tsvPath = $outputBase . '.tsv';
        if (!$process->isSuccessful() || !file_exists($tsvPath)) {
            @unlink($tsvPath);
            return new OcrResult(null, null, 'failed', 'TIER_1');
        }

        [$text, $confidence] = $this->parseTsv($tsvPath);
        @unlink($tsvPath);

        if ($text === '') {
            return new OcrResult(null, null, 'failed', 'TIER_1');
        }

        return new OcrResult($text, $confidence, 'completed', 'TIER_1');
    }

    /** @return array{0: string, 1: ?float} */
    private function parseTsv(string $tsvPath): array
    {
        $lines = file($tsvPath, FILE_IGNORE_NEW_LINES);
        if (!$lines) {
            return ['', null];
        }

        $header = str_getcsv(array_shift($lines), "\t");
        $textIdx = array_search('text', $header, true);
        $confIdx = array_search('conf', $header, true);

        $words = [];
        $confidences = [];

        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }
            $cols = str_getcsv($line, "\t");
            $word = $cols[$textIdx] ?? '';
            $conf = (float) ($cols[$confIdx] ?? -1);

            if ($word !== '') {
                $words[] = $word;
            }
            if ($conf >= 0) {
                $confidences[] = $conf;
            }
        }

        $confidence = $confidences ? round(array_sum($confidences) / count($confidences), 2) : null;

        return [implode(' ', $words), $confidence];
    }
}
