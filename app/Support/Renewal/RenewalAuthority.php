<?php

namespace App\Support\Renewal;

use App\Models\User;

/**
 * Module 1A.7 — renewal_commit authority. Reuses the existing "add Batch
 * Renewal" permission bit already in the RBAC matrix (Module 4A) as the
 * commit gate, rather than inventing a second parallel bit-naming scheme.
 * By default only System & Administration (super admin, via the Gate::before
 * bypass) and Appointment role holders with that bit may commit; delegable
 * via the Role Matrix / per-user overrides exactly like any other bit.
 */
class RenewalAuthority
{
    public const COMMIT_PERMISSION = 'add Batch Renewal';

    public static function canCommit(User $user): bool
    {
        return $user->isSuperAdmin() || $user->can(self::COMMIT_PERMISSION);
    }

    public static function assertCanCommit(User $user): void
    {
        abort_unless(self::canCommit($user), 403, 'You do not have renewal commit authority. Use "Request Renewal" instead.');
    }
}
