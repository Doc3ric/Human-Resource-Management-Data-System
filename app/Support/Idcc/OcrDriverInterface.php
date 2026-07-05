<?php

namespace App\Support\Idcc;

interface OcrDriverInterface
{
    /** Whether this driver can actually run on this machine right now. */
    public function isAvailable(): bool;

    public function extract(string $absoluteFilePath, string $mimeType): OcrResult;
}
