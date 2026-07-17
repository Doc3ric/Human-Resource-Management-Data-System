<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use App\Support\PasswordPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /** Paginated list of all users */
    public function index()
    {
        $users = User::with('roles')->orderBy('name')->paginate(20);
        return view('users.index', compact('users'));
    }

    /** Show create form */
    public function create()
    {
        $roles = \Spatie\Permission\Models\Role::orderBy('name')->get();
        return view('users.create', compact('roles'));
    }

    /** Store a new user */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => PasswordPolicy::rules(),
            'roles'      => 'required|array|min:1',
            'roles.*'    => 'exists:roles,name',
        ]);

        $isApproved = true;
        $currentUser = auth()->user();
        if ($currentUser && !$currentUser->isSuperAdmin()) {
            $isApproved = false;
        }

        $newUser = User::create([
            'name'                  => $data['name'],
            'email'                 => $data['email'],
            'password'              => Hash::make($data['password']),
            'role'                  => $data['roles'][0],
            'is_approved'           => $isApproved,
            // Admin-created accounts always get a forced change on first
            // login (temp password), regardless of complexity gate status.
            'must_change_password'  => true,
            'meets_complexity_gate' => PasswordPolicy::meetsGate($data['password']),
        ]);

        $newUser->assignRole($data['roles']);

        if ($isApproved) {
            ActivityLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'Created User',
                'description' => "Created a new account for {$newUser->name} ({$newUser->role_label})."
            ]);
        } else {
            ActivityLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'Pending User Approval',
                'description' => "A new {$newUser->role_label} account for {$newUser->name} is pending Super Admin approval."
            ]);
        }

        $msg = $isApproved ? 'User created successfully.' : 'User created but requires Super Admin approval before they can log in.';
        return redirect()->route('users.index')
            ->with('success', $msg);
    }

    /** Show edit form */
    public function edit(User $user)
    {
        $roles = \Spatie\Permission\Models\Role::orderBy('name')->get();
        return view('users.edit', compact('user', 'roles'));
    }

    /** Update an existing user */
    public function update(Request $request, User $user)
    {
        $passwordRules = $request->filled('password') ? PasswordPolicy::rules() : ['nullable'];

        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => $passwordRules,
            'roles'      => 'required|array|min:1',
            'roles.*'    => 'exists:roles,name',
        ]);

        $user->name  = $data['name'];
        $user->email = $data['email'];
        $user->role  = $data['roles'][0];

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
            // An admin resetting someone's password quarantines them the
            // same way a self-registered non-compliant password would.
            $meetsGate = PasswordPolicy::meetsGate($data['password']);
            $user->meets_complexity_gate = $meetsGate;
            $user->must_change_password = !$meetsGate;
        }

        $user->save();
        $user->syncRoles($data['roles']);

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    /** Delete a user (cannot delete yourself) */
    public function destroy(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    /** Approve a pending user */
    public function approve(User $user)
    {
        if (!auth()->user()->isSuperAdmin()) {
            return back()->with('error', 'Only Super Admins can approve users.');
        }

        $user->update(['is_approved' => true]);

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'Approved User',
            'description' => "Approved the pending account for {$user->name} ({$user->role_label})."
        ]);

        return back()->with('success', "User {$user->name} has been approved.");
    }

    /** Reject (delete) a pending user */
    public function reject(User $user)
    {
        if (!auth()->user()->isSuperAdmin()) {
            return back()->with('error', 'Only Super Admins can reject users.');
        }

        $name = $user->name;
        $user->forceDelete();

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'Rejected User',
            'description' => "Rejected and removed the pending account for {$name}."
        ]);

        return back()->with('success', "Pending user {$user->name} has been rejected and removed.");
    }
}

