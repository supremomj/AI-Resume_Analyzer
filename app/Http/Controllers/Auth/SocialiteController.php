<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     *
     * @return RedirectResponse
     */
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')
            ->scopes(['email', 'profile'])
            ->stateless()
            ->redirect();
    }

    /**
     * Obtain the user information from Google.
     *
     * @return RedirectResponse
     */
    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            /** @var \Laravel\Socialite\Two\User $googleUser */
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google Auth Error: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('login')->withErrors(['email' => 'Google authentication failed: ' . $e->getMessage()]);
        }

        // Find or create user
        $user = User::where('google_id', $googleUser->id)
            ->orWhere('email', $googleUser->email)
            ->first();

        if ($user) {
            // Update existing user with Google ID and token if not set
            $user->update([
                'google_id' => $googleUser->id,
                'google_token' => $googleUser->token,
            ]);
        } else {
            // Create a new user
            $user = User::create([
                'name' => $googleUser->name,
                'first_name' => $this->getFirstName($googleUser->name),
                'last_name' => $this->getLastName($googleUser->name),
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'google_token' => $googleUser->token,
                'password' => Hash::make(Str::random(24)),
                'email_verified_at' => now(), // Google emails are already verified
            ]);
        }

        Auth::login($user);

        return redirect()->intended(route('home', absolute: false));
    }

    /**
     * Helper to extract first name.
     */
    private function getFirstName(string $fullName): string
    {
        $parts = explode(' ', $fullName);
        return $parts[0] ?? '';
    }

    /**
     * Helper to extract last name.
     */
    private function getLastName(string $fullName): string
    {
        $parts = explode(' ', $fullName);
        return count($parts) > 1 ? end($parts) : '';
    }
}
