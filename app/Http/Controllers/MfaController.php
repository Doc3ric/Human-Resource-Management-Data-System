<?php

namespace App\Http\Controllers;

use App\Support\Raccs\RaccsMfaGate;
use Illuminate\Http\Request;
use PragmaRX\Google2FALaravel\Facade as Google2FA;

/**
 * Module 9 Stage 5 / 10-M3 — TOTP MFA setup and per-session challenge for
 * RACCS-Confidential access. Fully on-premise (no external API calls).
 */
class MfaController extends Controller
{
    public function setup(Request $request)
    {
        $user = $request->user();

        if (!$user->google2fa_secret) {
            $user->update(['google2fa_secret' => Google2FA::generateSecretKey()]);
        }

        $qrCodeUrl = Google2FA::getQRCodeInline(
            config('app.name'),
            $user->email,
            $user->google2fa_secret
        );

        return view('mfa.setup', compact('qrCodeUrl'));
    }

    public function enable(Request $request)
    {
        $data = $request->validate(['one_time_password' => 'required|string']);
        $user = $request->user();

        if (!Google2FA::verifyKey($user->google2fa_secret, $data['one_time_password'])) {
            return back()->withErrors(['one_time_password' => 'Invalid code. Please try again.']);
        }

        $user->update(['google2fa_enabled' => true]);
        RaccsMfaGate::markVerified();

        return redirect()->route('disciplinary.index')->with('success', 'MFA enabled for RACCS-Confidential access.');
    }

    public function challenge()
    {
        return view('mfa.challenge');
    }

    public function verify(Request $request)
    {
        $data = $request->validate(['one_time_password' => 'required|string']);
        $user = $request->user();

        if (!$user->google2fa_enabled || !Google2FA::verifyKey($user->google2fa_secret, $data['one_time_password'])) {
            return back()->withErrors(['one_time_password' => 'Invalid code. Please try again.']);
        }

        RaccsMfaGate::markVerified();

        return redirect()->intended(route('disciplinary.index'))->with('success', 'MFA verified — RACCS session unlocked for 15 minutes.');
    }
}
