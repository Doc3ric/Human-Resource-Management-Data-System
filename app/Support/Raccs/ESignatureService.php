<?php

namespace App\Support\Raccs;

use App\Models\ESignature;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Module 10-M4 — e-signature capture per CS Form No. 11 s.2025. The PNPKI
 * (DICT) verification hook is intentionally non-blocking: pnpki_status
 * stays 'not_submitted' unless/until a real PNPKI integration exists, and
 * nothing here (or anywhere else) requires it to change before the
 * signature itself is considered valid and recorded.
 */
class ESignatureService
{
    public function capture(
        Model $signable,
        string $signatoryName,
        ?string $signatoryPosition,
        string $signatureImageBase64,
        User $signedBy,
        ?string $ipAddress = null,
    ): ESignature {
        return ESignature::create([
            'signable_type' => $signable->getMorphClass(),
            'signable_id' => $signable->getKey(),
            'signatory_name' => $signatoryName,
            'signatory_position' => $signatoryPosition,
            'signature_image' => $signatureImageBase64,
            'signed_by' => $signedBy->id,
            'signed_at' => now(),
            'ip_address' => $ipAddress,
            'pnpki_status' => 'not_submitted',
        ]);
    }
}
