<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\EmailVerificationOTP;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    /**
     * Show the OTP verification form.
     */
    public function show(Request $request): View
    {
        $email = $request->session()->get('email') ?? $request->query('email');
        
        return view('auth.verify-email-otp', [
            'email' => $email,
        ]);
    }

    /**
     * Verify the OTP code.
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email address not found.'])->withInput();
        }

        // Check if OTP matches
        if ($user->email_verification_otp !== $request->otp) {
            return back()->withErrors(['otp' => 'Invalid OTP code. Please try again.'])->withInput();
        }

        // Check if OTP has expired
        if ($user->email_verification_otp_expires_at && $user->email_verification_otp_expires_at->isPast()) {
            return back()->withErrors(['otp' => 'OTP code has expired. Please request a new one.'])->withInput();
        }

        // Verify email and clear OTP
        $user->email_verified_at = now();
        $user->email_verification_otp = null;
        $user->email_verification_otp_expires_at = null;
        $user->save();

        // Refresh user model to ensure latest data is loaded
        $user->refresh();

        // Log the user in and regenerate session for security
        Auth::login($user);
        $request->session()->regenerate();

        // Log successful verification and login
        Log::info('Email verified and user logged in', [
            'user_id' => $user->id,
            'email' => $user->email,
            'verified_at' => $user->email_verified_at,
        ]);

        // Redirect directly to home page
        return redirect()->route('home')
            ->with('status', 'Email verified successfully! Welcome to HanapBuh.AI!');
    }

    /**
     * Resend OTP code.
     */
    public function resend(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email address not found.'])->withInput();
        }

        // Check if email is already verified
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('login')
                ->with('status', 'Your email is already verified. You can log in now.');
        }

        // Generate new OTP
        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        
        $user->email_verification_otp = $otp;
        $user->email_verification_otp_expires_at = now()->addMinutes(15);
        $user->save();

        // Send new OTP immediately (real-time)
        try {
            $user->notifyNow(new EmailVerificationOTP($otp));
            Log::info('OTP resent successfully (real-time)', [
                'user_id' => $user->id,
                'email' => $user->email,
                'sent_at' => now()->toDateTimeString(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to resend OTP email', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            
            return back()->withErrors(['email' => 'Failed to send OTP. Please try again later.'])->withInput();
        }

        return back()->with('status', 'A new OTP code has been sent to your email address.');
    }
}
