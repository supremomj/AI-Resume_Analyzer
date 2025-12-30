<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    // Show the profile (read-only)
    public function show(Request $request): View
    {
        $user = $request->user();
        return view('profile.show', compact('user'));
    }

    // Show the edit form
    public function edit(Request $request): View
    {
        // Get fresh user data from database
        $user = $request->user()->fresh();
        return view('profile.edit', [
            'user' => $user,
        ]);
    }

    // Handle profile update POST
    public function update(Request $request): RedirectResponse
    {
        // Get fresh user from database to avoid stale data
        $user = $request->user()->fresh();

        $validated = $request->validate([
            'first_name'      => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\-\'\.]+$/'],
            'last_name'       => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\-\'\.]+$/'],
            'contact_number'  => ['nullable', 'string', 'max:20', 'regex:/^(09|\+639)\d{9}$/'],
            'address'         => ['nullable', 'string', 'max:500'],
            'profile_photo'   => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'],
            'current_password'=> ['nullable', 'string'],
            'new_password'    => ['nullable', 'string', 'confirmed', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/'],
        ], [
            'first_name.regex' => 'First name can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'last_name.regex' => 'Last name can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'new_password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number.',
        ]);

        // Update info
        $user->first_name     = $validated['first_name'];
        $user->last_name      = $validated['last_name'];
        $user->contact_number = $validated['contact_number'] ?? null;
        $user->address        = $validated['address'] ?? null;

        // Image upload/removal logic
        if ($request->has('remove_photo') && $user->profile_photo) {
            Storage::disk('public')->delete('profiles/'.$user->profile_photo);
            $user->profile_photo = null;
        } elseif ($request->hasFile('profile_photo')) {
            $photo = $request->file('profile_photo');
            
            // Validate file
            if (!$photo->isValid()) {
                return redirect()->back()->withErrors(['profile_photo' => 'Invalid file uploaded.']);
            }
            
            // Ensure profiles directory exists
            if (!Storage::disk('public')->exists('profiles')) {
                Storage::disk('public')->makeDirectory('profiles');
            }
            
            // Validate and sanitize file extension
            $extension = strtolower($photo->getClientOriginalExtension());
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (!in_array($extension, $allowedExtensions)) {
                return redirect()->back()->withErrors(['profile_photo' => 'Invalid file type. Only JPG, PNG, GIF, and WEBP images are allowed.']);
            }
            
            // Validate MIME type
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $mimeType = $photo->getMimeType();
            if (!in_array($mimeType, $allowedMimeTypes)) {
                return redirect()->back()->withErrors(['profile_photo' => 'Invalid file type. Only image files are allowed.']);
            }
            
            // Generate secure unique filename (no user input)
            $filename = uniqid('profile_', true) . '_' . time() . '.' . $extension;
            
            // Store the file
            try {
                $storedPath = $photo->storeAs('profiles', $filename, 'public');
                
                // Verify the file was stored
                $fullPath = storage_path('app/public/profiles/' . $filename);
                if (!file_exists($fullPath)) {
                    return redirect()->back()->withErrors(['profile_photo' => 'File was uploaded but not found in storage.']);
                }
            } catch (\Exception $e) {
                return redirect()->back()->withErrors(['profile_photo' => 'Upload error: ' . $e->getMessage()]);
            }

            // Remove old image if exists (with security check)
            if ($user->profile_photo) {
                $oldFilename = basename($user->profile_photo);
                // Prevent path traversal
                if (preg_match('/^[a-zA-Z0-9._-]+$/', $oldFilename)) {
                    $oldPath = storage_path('app/public/profiles/' . $oldFilename);
                    $realOldPath = realpath($oldPath);
                    $basePath = realpath(storage_path('app/public/profiles'));
                    
                    // Ensure file is within profiles directory
                    if ($realOldPath && strpos($realOldPath, $basePath) === 0 && is_file($realOldPath)) {
                        @unlink($realOldPath);
                    }
                }
            }
            
            // Update the profile_photo field
            $user->profile_photo = $filename;
        }

        // Password update logic (optional fields)
        if ($request->filled('new_password')) {
            $request->validate([
                'current_password' => 'required|current_password',
                'new_password'     => 'required|confirmed|min:8',
            ]);
            $user->password = Hash::make($request->new_password);
        }

        // Save the user model
        try {
            if (!$user->save()) {
                return redirect()->back()->withErrors(['error' => 'Failed to save profile.']);
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to save profile: ' . $e->getMessage()]);
        }

        // Reload the user from database to ensure fresh data
        $user = $user->fresh();

        return redirect()->route('profile.edit')->with('success', 'Profile saved successfully!');
    }

    // Delete account
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

        return redirect('/');
    }
}
