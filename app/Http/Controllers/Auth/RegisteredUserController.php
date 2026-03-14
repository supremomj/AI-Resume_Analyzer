<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\EmailVerificationOTP;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
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
            'first_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\-\'\.]+$/'],
            'last_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\-\'\.]+$/'],
            'email' => ['required', 'string', 'lowercase', 'email:rfc', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults(), 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/'],
            'contact_number' => ['nullable', 'string', 'max:20', 'regex:/^(09|\+639)\d{9}$/'],
            'address' => ['nullable', 'string', 'max:500'],
            'birth_date' => ['required', 'date', 'before:-18 years'],
            'terms' => ['required', 'accepted'],
        ], [
            'first_name.regex' => 'First name can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'last_name.regex' => 'Last name can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number.',
            'email.email' => 'Please provide a valid email address. All email domains are accepted, including .edu.ph, .com, .org, and others.',
            'birth_date.before' => 'You must be at least 18 years old to create an account.',
            'terms.required' => 'You must accept the Terms and Conditions to create an account.',
            'terms.accepted' => 'You must accept the Terms and Conditions to create an account.',
        ]);

        // Generate 6-digit OTP
        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        $user = User::create([
            'name' => $request->first_name . ' ' . $request->last_name,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'contact_number' => $request->contact_number,
            'address' => $request->address,
            'birth_date' => $request->birth_date,
            'email_verification_otp' => $otp,
            'email_verification_otp_expires_at' => now()->addMinutes(15),
        ]);

        // Send OTP email immediately (real-time, synchronous)
        $emailSent = false;
        $emailError = null;

        try {
            // Send immediately without queuing for real-time delivery
            // Using notifyNow() ensures instant synchronous sending
            $user->notifyNow(new EmailVerificationOTP($otp));
            $emailSent = true;
            Log::info('OTP email sent successfully (real-time)', [
                'user_id' => $user->id,
                'email' => $user->email,
                'sent_at' => now()->toDateTimeString(),
            ]);
        } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
            // SMTP connection error - likely missing credentials
            $emailError = 'SMTP connection failed. Please check your email configuration in .env file.';
            Log::error('SMTP connection failed for OTP email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
                'smtp_host' => config('mail.mailers.smtp.host'),
                'smtp_port' => config('mail.mailers.smtp.port'),
                'has_username' => !empty(config('mail.mailers.smtp.username')),
                'has_password' => !empty(config('mail.mailers.smtp.password')),
            ]);
        } catch (\Exception $e) {
            $emailError = $e->getMessage();
            Log::error('Failed to send OTP email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        // Check if mail is configured (not using 'log' driver)
        $mailDriver = config('mail.default');
        $isLogDriver = $mailDriver === 'log';

        // Redirect to OTP verification page
        // DEVELOPMENT MODE: Always show OTP on screen
        $redirect = redirect()->route('verify.email.show')
            ->with('email', $user->email)
            ->with('otp_display', $otp)
            ->with('status', 'Registration successful! Your OTP code is shown below.');

        return $redirect;
    }
}
