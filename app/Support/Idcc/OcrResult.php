<?php

namespace App\Support\Idcc;

/**
 * Module 9 Stage 2 — result of an extraction attempt at any tier.
 */
class OcrResult
{
    public function __construct(
        public readonly ?string $text,
        public readonly ?float $confidence,
        public readonly string $status, // completed|failed|unavailable
        public readonly string $tier,   // TIER_1|TIER_2|TIER_3
    ) {
    }

    public static function unavailable(string $tier = 'TIER_1'): self
    {
        return new self(null, null, 'unavailable', $tier);
    }
}
