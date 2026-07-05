<?php

namespace App\Support\Idcc;

use App\Models\Document;
use App\Models\User;
use App\Support\Raccs\RaccsMfaGate;

/**
 * Module 9 Stage 4/5 — who may view/unmask a document, shared by the
 * document viewer and the 9A.10 duplicate-disclosure logic so both enforce
 * the same rule.
 *
 * RACCS roles (Discipline Committee) are seeded by IdccRolesSeeder.
 *
 * Module 9 Stage 5 requires MFA in addition to role membership for RACCS
 * documents. This mirrors DisciplinaryCaseController's Module 10-M3
 * precedent (RaccsMfaGate) — the two RACCS-gated surfaces (case registry
 * vs. document wall) previously enforced this inconsistently; this closes
 * that gap. Per that same precedent, RACCS access does NOT let super admin
 * skip MFA — it is the one surface in this app where the usual super-admin
 * bypass does not extend past role membership.
 */
class DocumentAccessPolicy
{
    private const RACCS_ROLES = ['Discipline Committee'];

    public function canView(User $user, Document $document): bool
    {
        if ($document->is_raccs) {
            $hasRole = $user->isSuperAdmin() || $user->hasAnyRole(self::RACCS_ROLES);

            return $hasRole && RaccsMfaGate::isVerified();
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($document->privacy_tier === 1) {
            // SPI, non-RACCS: records custodians only (mirrors the existing
            // 201-file access pattern already used for employee profiles).
            return $user->isInventoryAdmin();
        }

        return true;
    }

    public function canUnmask(User $user, Document $document): bool
    {
        return $this->canView($user, $document);
    }

    /** Distinguishes a role denial from an MFA denial, for accurate audit logging/messaging. */
    public function denialReason(User $user, Document $document): ?string
    {
        if ($this->canView($user, $document)) {
            return null;
        }

        if ($document->is_raccs) {
            $hasRole = $user->isSuperAdmin() || $user->hasAnyRole(self::RACCS_ROLES);

            return $hasRole ? 'MFA not verified or expired' : 'not a RACCS-authorized role';
        }

        return 'not authorized';
    }

    /** Generic disclosure message when a viewer cannot see the real location. */
    public function restrictedLocationMessage(Document $document): string
    {
        if ($document->is_raccs) {
            return 'An identical file exists on a record you are not authorized to view; contact HR Records.';
        }

        return 'An identical file exists on a record you are not authorized to view; contact HR Records.';
    }
}
