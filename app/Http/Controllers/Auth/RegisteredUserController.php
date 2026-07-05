<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Support\PasswordPolicy;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', 'unique:'.User::class],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => PasswordPolicy::rules(),
        ]);

        // Module 0.1: quarantine — a password that fails the complexity gate
        // (only reachable at all outside local via the debug-only rule path)
        // forces the account through the change-password screen before it
        // can touch anything else; the global ForcePasswordChange middleware
        // enforces this.
        $meetsGate = PasswordPolicy::meetsGate($request->password);

        // Self-registered accounts are never auto-approved or auto-logged-in
        // — they wait in the same is_approved=false queue as accounts a
        // non-Super-Admin creates via User Management (see UserController),
        // reviewed on the Users page and surfaced in the admin notification
        // widget (dashboard-app.blade.php).
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_approved' => false,
            'meets_complexity_gate' => $meetsGate,
            'must_change_password' => !$meetsGate,
        ]);

        event(new Registered($user));

        // user_id is a required, cascade-deleting FK (activity_logs migration)
        // and there's no authenticated actor for a public registration, so
        // the entry is attributed to the newly created (still-pending) user.
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'Pending User Approval',
            'description' => "New account request submitted by {$user->name} ({$user->email}) — awaiting Super Admin approval.",
        ]);

        return redirect()->route('registration.pending')
            ->with('registered_name', $user->name);
    }
}
