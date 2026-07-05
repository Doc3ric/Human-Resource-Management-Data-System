<?php

namespace App\Support\Idcc;

class VisionLlmDriver implements OcrDriverInterface
{
    public function isAvailable(): bool
    {
        // Admin Opt-In flag check (using config for now)
        return config('idcc.vision_llm_enabled', false);
    }

    public function supports(string $mimeType): bool
    {
        return str_starts_with($mimeType, 'image/');
    }

    public function extract(string $absoluteFilePath, string $mimeType): OcrResult
    {
        // Stub implementation for Tier 3 external LLM
        return new OcrResult(
            text: "[TIER 3 VISION LLM EXTRACTION SIMULATED]\n" . file_get_contents($absoluteFilePath),
            confidence: 0.95,
            tier: 'TIER_3',
            status: 'completed'
        );
    }
}
