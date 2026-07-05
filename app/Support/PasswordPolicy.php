<?php

namespace App\Support;

use Illuminate\Validation\Rules\Password;

/**
 * Module 0.1: 8-char alphanumeric+special complexity gate (NPC Circular 16-01).
 * A short-password path is allowed outside production for local debugging
 * only; every account's actual compliance is still tracked via
 * users.meets_complexity_gate regardless of which rule path created it, so a
 * debug-created account is correctly quarantined the moment it's used
 * anywhere that isn't local.
 */
class PasswordPolicy
{
    /** Validation rule set for the current environment. */
    public static function rules(): array
    {
        if (app()->environment('local')) {
            return ['required', 'string', 'confirmed'];
        }

        return ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()];
    }

    /**
     * The objective complexity check used to set meets_complexity_gate,
     * independent of which environment created the password.
     */
    public static function meetsGate(string $password): bool
    {
        return strlen($password) >= 8
            && preg_match('/[a-z]/', $password) === 1
            && preg_match('/[A-Z]/', $password) === 1
            && preg_match('/[0-9]/', $password) === 1
            && preg_match('/[^a-zA-Z0-9]/', $password) === 1;
    }
}
