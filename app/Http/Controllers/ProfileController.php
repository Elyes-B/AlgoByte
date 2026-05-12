<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's public-facing profile dashboard.
     */
    public function show(Request $request): Response
    {
        return Inertia::render('Profile/Show', [
            'member' => $request->user(),
        ]);
    }

    /**
     * Display the user's settings/edit form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'member' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
{
    $user = $request->user();

    // 1. Only grab username and email from validated data initially
    $user->fill($request->safe()->only(['username', 'email']));

    if ($user->isDirty('email')) {
        $user->email_verified_at = null;
    }

    // 2. Handle Profile Image: ONLY update if a file is actually present
    if ($request->hasFile('profile_image')) {
        if ($user->profile_image) {
            $oldPath = str_replace(Storage::disk('supabase')->url(''), '', $user->profile_image);
            Storage::disk('supabase')->delete($oldPath);
        }

        $path = $request->file('profile_image')->store('profiles', 'supabase');
        $user->profile_image = Storage::disk('supabase')->url($path);
    }

    // 3. Handle Background Image: ONLY update if a file is actually present
    if ($request->hasFile('background_image')) {
        if ($user->background_image) {
            $oldPath = str_replace(Storage::disk('supabase')->url(''), '', $user->background_image);
            Storage::disk('supabase')->delete($oldPath);
        }

        $path = $request->file('background_image')->store('backgrounds', 'supabase');
        $user->background_image = Storage::disk('supabase')->url($path);
    }

    $user->save();

    return Redirect::route('profile.edit');
}

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
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
