<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\PanelMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PanelAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('panel')->check()) {
            return redirect()->route('panel.home');
        }
        return view('panel.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $member = PanelMember::where('email', $credentials['email'])->first();

        if (!$member || !$member->is_active) {
            return back()->withErrors(['email' => 'Account not found or is inactive.'])->withInput();
        }

        if (!Auth::guard('panel')->attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid email or password.'])->withInput();
        }

        $member->update(['last_login_at' => now()]);

        $request->session()->regenerate();
        return redirect()->route('panel.home');
    }

    public function logout(Request $request)
    {
        Auth::guard('panel')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('panel.login');
    }
}
