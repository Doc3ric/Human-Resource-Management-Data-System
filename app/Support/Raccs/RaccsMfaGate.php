<?php

namespace App\Support\Raccs;

use App\Models\User;
use Illuminate\Support\Facades\Session;

/**
 * Module 9 Stage 5 / 10-M3 — RACCS access requires MFA in addition to role
 * membership. TOTP is fully computed on-premise (pragmarx/google2fa), no
 * external service calls. A verified challenge is valid for a short window
 * (session-based) so the user isn't re-prompted on every click within the
 * same RACCS working session.
 */
class RaccsMfaGate
{
    public const SESSION_KEY = 'raccs_mfa_verified_at';
    public const VALID_MINUTES = 15;

    public static function isVerified(): bool
    {
        $verifiedAt = Session::get(self::SESSION_KEY);
        if (!$verifiedAt) {
            return false;
        }

        return now()->diffInMinutes(\Carbon\Carbon::parse($verifiedAt)) <= self::VALID_MINUTES;
    }

    public static function markVerified(): void
    {
        Session::put(self::SESSION_KEY, now()->toDateTimeString());
    }

    public static function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public static function requiresSetup(User $user): bool
    {
        return !$user->google2fa_enabled;
    }
}
