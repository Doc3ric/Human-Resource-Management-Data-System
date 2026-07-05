<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\PasswordPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ForcePasswordChangeController extends Controller
{
    public function show(): View
    {
        return view('auth.force-password-change');
    }

    public function update(Request $request): RedirectResponse
    {
        // This IS the compliance-upgrade screen (Module 0.1) — always enforce
        // the full complexity gate here, regardless of environment, since a
        // debug-only short password should never be re-issued as the "fix."
        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $user = $request->user();
        $user->password = Hash::make($request->password);
        $user->must_change_password = false;
        $user->meets_complexity_gate = PasswordPolicy::meetsGate($request->password);
        $user->save();

        return redirect()->route('dashboard')
            ->with('status', 'password-changed');
    }
}
