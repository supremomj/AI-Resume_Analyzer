# Email Verification with OTP - Setup Guide

## Overview
Your registration system now requires users to verify their email address using a 6-digit OTP (One-Time Password) code before they can log in.

## How It Works

### Registration Flow:
1. **User registers** → Account is created but email is NOT verified
2. **OTP is generated** → 6-digit code (e.g., 123456)
3. **OTP is sent** → Email notification sent to user's email address
4. **User redirected** → To OTP verification page
5. **User enters OTP** → Submits the 6-digit code
6. **Email verified** → User is logged in automatically and redirected to home

### Login Flow:
- If user tries to login with unverified email → Redirected to OTP verification page
- User must verify email before accessing the system

## Features

✅ **6-digit OTP code** - Secure random code  
✅ **15-minute expiration** - OTP expires after 15 minutes  
✅ **Resend OTP** - Users can request a new code  
✅ **Auto-submit** - Form submits automatically when 6 digits are entered  
✅ **Email validation** - Only valid emails can register  
✅ **Security** - OTP is cleared after verification  

## Files Created/Modified

### New Files:
1. **`database/migrations/2025_11_18_180945_add_email_verification_otp_to_users_table.php`**
   - Adds `email_verification_otp` and `email_verification_otp_expires_at` columns

2. **`app/Notifications/EmailVerificationOTP.php`**
   - Email notification for sending OTP codes

3. **`app/Http/Controllers/Auth/EmailVerificationController.php`**
   - Handles OTP verification and resend

4. **`resources/views/auth/verify-email-otp.blade.php`**
   - OTP verification form

### Modified Files:
1. **`app/Http/Controllers/Auth/RegisteredUserController.php`**
   - Generates OTP and sends email after registration
   - Redirects to verification page

2. **`app/Models/User.php`**
   - Added OTP fields to `$fillable` and `$casts`

3. **`app/Http/Requests/Auth/LoginRequest.php`**
   - Checks if email is verified before allowing login

4. **`routes/auth.php`**
   - Added routes for OTP verification

## Routes

### Guest Routes (No Authentication Required):
- `GET /verify-email` - Show OTP verification form
- `POST /verify-email` - Verify OTP code
- `POST /resend-otp` - Resend OTP code

## Email Configuration

Make sure your `.env` file has proper email settings:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@hanapbuhai.com
MAIL_FROM_NAME="${APP_NAME}"
```

### For Gmail:
1. Enable 2-factor authentication
2. Generate an App Password: https://myaccount.google.com/apppasswords
3. Use the app password in `MAIL_PASSWORD`

### For Other Providers:
- **SendGrid**: Use SMTP settings from SendGrid dashboard
- **Mailgun**: Use Mailgun SMTP settings
- **Amazon SES**: Use AWS SES SMTP credentials

## Testing

### Test Registration:
1. Go to `/register`
2. Fill in the registration form
3. Submit the form
4. Check your email for the OTP code
5. Enter the OTP on the verification page
6. You should be logged in and redirected to home

### Test Resend OTP:
1. On the verification page, click "Resend OTP"
2. Check your email for a new OTP code
3. The old OTP will be invalidated

### Test Login with Unverified Email:
1. Try to login with an unverified account
2. You should be redirected to the verification page
3. Enter the OTP to verify

## Security Features

✅ **OTP Expiration** - Codes expire after 15 minutes  
✅ **One-time Use** - OTP is cleared after successful verification  
✅ **Rate Limiting** - Already implemented on routes  
✅ **Email Validation** - RFC and DNS validation  
✅ **Secure Generation** - Uses `random_int()` for cryptographically secure random numbers  

## Customization

### Change OTP Expiration Time:
Edit `app/Http/Controllers/Auth/RegisteredUserController.php`:
```php
'email_verification_otp_expires_at' => now()->addMinutes(30), // Change to 30 minutes
```

### Change OTP Length:
Edit `app/Http/Controllers/Auth/RegisteredUserController.php`:
```php
// For 4-digit OTP:
$otp = str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);

// For 8-digit OTP:
$otp = str_pad((string) random_int(10000000, 99999999), 8, '0', STR_PAD_LEFT);
```

### Customize Email Template:
Edit `app/Notifications/EmailVerificationOTP.php`:
```php
public function toMail(object $notifiable): MailMessage
{
    return (new MailMessage)
        ->subject('Your Custom Subject')
        ->greeting('Hello!')
        ->line('Your custom message here')
        ->line('**' . $this->otp . '**')
        // ... more customization
}
```

## Troubleshooting

### OTP Email Not Received:
1. Check spam/junk folder
2. Verify email configuration in `.env`
3. Check Laravel logs: `storage/logs/laravel.log`
4. Test email sending: `php artisan tinker` → `Mail::raw('Test', function($msg) { $msg->to('your@email.com'); });`

### OTP Expired:
- User can click "Resend OTP" to get a new code

### Migration Error:
- Run: `php artisan migrate:fresh` (WARNING: This will delete all data)
- Or: `php artisan migrate:rollback` then `php artisan migrate`

## Next Steps

Consider adding:
- SMS OTP option (using Twilio or similar)
- OTP attempt limiting (max 3 attempts)
- Account lockout after multiple failed attempts
- Email change verification (when user changes email)

## Support

If you encounter any issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify email configuration
3. Test email sending manually
4. Check database migration was successful

