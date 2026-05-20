<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Member;
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
     * Display the user's profile form.
     */
    public function edit(): Response
    {
        $member = Auth::user(); // Get the authenticated member
        return Inertia::render('Profile/Edit', [
            'member' => $member, // Pass the authenticated member to the view
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->fill([
            'username' => $validated['username'] ?? $user->username,
            'email' => $validated['email'] ?? $user->email,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // --- Handle Profile Image Upload (Using Supabase S3/Disk) ---
        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('supabase')->delete('profiles/' . $user->profile_image);
            }

            // Stores in your 'images' bucket inside a 'profiles' folder
            $path = $request->file('profile_image')->store('profiles', 'supabase');

            // Extract just the filename or store the relative bucket path 'profiles/filename.jpg'
            $user->profile_image = basename($path);
        }

        // --- Handle Background Image Upload (Using Supabase S3/Disk) ---
        if ($request->hasFile('background_image')) {
            if ($user->background_image) {
                Storage::disk('supabase')->delete('banners/' . $user->background_image);
            }

            $path = $request->file('background_image')->store('banners', 'supabase');
            $user->background_image = basename($path);
        }

        $user->save();

        return Redirect::route('profile.edit')->with('success', 'Profile updated successfully.');
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

    /**
     * Display the user's profile page.
     */
    public function show($username): Response
    {
        $member = Member::where('username', $username)->firstOrFail();
        $isOwner = $member->userId === Auth::id();
        return Inertia::render('Profile/Show', [
            'member' => $member,
            'isOwner' => $isOwner,
        ]);
    }
}
