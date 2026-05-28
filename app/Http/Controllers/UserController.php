<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /** Paginated list of all users */
    public function index()
    {
        $users = User::orderBy('name')->paginate(20);
        return view('users.index', compact('users'));
    }

    /** Show create form */
    public function create()
    {
        return view('users.create');
    }

    /** Store a new user */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'required|in:super_admin,salary_admin,inventory_admin',
        ]);

        $isApproved = true;
        $currentUser = auth()->user();
        if ($currentUser && !$currentUser->isSuperAdmin()) {
            $isApproved = false;
        }

        $newUser = User::create([
            'name'        => $data['name'],
            'email'       => $data['email'],
            'password'    => Hash::make($data['password']),
            'role'        => $data['role'],
            'is_approved' => $isApproved,
        ]);

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
        return view('users.edit', compact('user'));
    }

    /** Update an existing user */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role'     => 'required|in:super_admin,salary_admin,inventory_admin',
        ]);

        $user->name  = $data['name'];
        $user->email = $data['email'];
        $user->role  = $data['role'];

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

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

