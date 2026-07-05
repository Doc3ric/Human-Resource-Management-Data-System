<?php

namespace App\Support\Renewal;

use App\Models\DisposalAuthorization;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Module 1B.4 — the single shared gate every personnel-record hard-delete
 * path must check before proceeding. Never bypassed by a role check alone;
 * a super_admin still cannot force-delete a record with no disposal
 * authorization on file, per Module 4A.4's "granting a checkbox never
 * bypasses a statutory control" rule.
 */
class DisposalAuthorizationService
{
    public function hasAuthorization(Model $disposable): bool
    {
        return DisposalAuthorization::where('disposable_type', $disposable->getMorphClass())
            ->where('disposable_id', $disposable->getKey())
            ->exists();
    }

    public function authorize(
        Model $disposable,
        string $napFormReference,
        ?string $filePath,
        User $actor,
        ?string $notes = null,
    ): DisposalAuthorization {
        return DisposalAuthorization::create([
            'disposable_type' => $disposable->getMorphClass(),
            'disposable_id' => $disposable->getKey(),
            'nap_form_reference' => $napFormReference,
            'file_path' => $filePath,
            'authorized_by' => $actor->id,
            'authorized_at' => now(),
            'notes' => $notes,
        ]);
    }
}
