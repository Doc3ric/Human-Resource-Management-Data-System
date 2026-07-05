<?php

namespace App\Http\Controllers;

use App\Models\PanelMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PanelMemberController extends Controller
{
    public function index()
    {
        $members = PanelMember::orderBy('type')->orderBy('name')->get();
        return view('panel-members.index', compact('members'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:panel_members,email',
            'password' => ['required', Password::min(8)],
            'type'     => 'required|in:hrmpsb,twg',
            'position' => 'nullable|string|max:255',
        ]);

        PanelMember::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'type'      => $data['type'],
            'position'  => $data['position'] ?? null,
            'is_active' => true,
        ]);

        return back()->with('success', "Panel member account created for {$data['name']}.");
    }

    public function toggleActive(PanelMember $panelMember)
    {
        $panelMember->update(['is_active' => !$panelMember->is_active]);
        $status = $panelMember->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "{$panelMember->name} has been {$status}.");
    }

    public function resetPassword(Request $request, PanelMember $panelMember)
    {
        $data = $request->validate([
            'password' => ['required', Password::min(8)],
        ]);

        $panelMember->update(['password' => Hash::make($data['password'])]);
        return back()->with('success', "Password reset for {$panelMember->name}.");
    }

    public function destroy(PanelMember $panelMember)
    {
        $panelMember->delete();
        return back()->with('success', 'Panel member account deleted.');
    }
}
