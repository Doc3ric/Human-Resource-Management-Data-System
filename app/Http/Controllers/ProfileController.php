<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * The only theme keys the CSS actually defines (see public/css/theme-variables.css).
     * 'default' means "no body class" (Navy).
     */
    private const VALID_THEMES = [
        'default', 'emerald-night', 'theme-financial', 'theme-corona',
        'theme-light', 'theme-eyecare', 'theme-nature', 'theme-civic-blue',
    ];
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->hasFile('profile_picture')) {
            // Delete old picture if exists
            if ($user->profile_picture) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->profile_picture);
            }
            
            $path = $request->file('profile_picture')->store('avatars', 'public');
            $user->profile_picture = $path;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Persist the user's chosen UI theme (see setTheme() in the dashboard layouts).
     * Rejects anything outside VALID_THEMES with a plain 400 - never trust client input.
     */
    public function theme(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'theme' => ['required', 'string', 'in:' . implode(',', self::VALID_THEMES)],
        ]);

        if ($validator->fails()) {
            abort(400, 'Invalid theme selection.');
        }

        $request->user()->update(['theme_preference' => $request->string('theme')->toString()]);

        return response()->json(['status' => 'ok']);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
