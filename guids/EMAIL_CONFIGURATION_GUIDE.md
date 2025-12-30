# Email Configuration Guide for OTP Verification

## Problem: No OTP Email Received

If you're not receiving OTP emails, it's likely because email is not properly configured. By default, Laravel uses the `log` driver which writes emails to log files instead of sending them.

## Quick Fix: Check Current Configuration

1. **Check your `.env` file** - Look for these settings:
```env
MAIL_MAILER=log
```

If `MAIL_MAILER=log`, emails are being logged, not sent!

## Solution Options

### Option 1: Use Log Driver (Development Only) ✅ EASIEST

For development/testing, you can view the OTP in the browser. The system will automatically show the OTP on screen if email is not configured.

**No configuration needed!** Just register and the OTP will be displayed on the verification page.

### Option 2: Configure Gmail SMTP ✅ RECOMMENDED

1. **Enable 2-Factor Authentication** on your Gmail account
2. **Generate App Password**:
   - Go to: https://myaccount.google.com/apppasswords
   - Select "Mail" and "Other (Custom name)"
   - Enter "HanapBuh.AI" as the name
   - Copy the 16-character password

3. **Update `.env` file**:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-16-character-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="HanapBuh.AI"
```

4. **Clear config cache**:
```bash
php artisan config:clear
```

### Option 3: Use Mailtrap (Testing) ✅ FREE

1. **Sign up** at https://mailtrap.io (free account)
2. **Get SMTP credentials** from your inbox
3. **Update `.env` file**:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@hanapbuhai.com
MAIL_FROM_NAME="HanapBuh.AI"
```

### Option 4: Use SendGrid (Production) ✅ RELIABLE

1. **Sign up** at https://sendgrid.com (free tier available)
2. **Create API Key** in SendGrid dashboard
3. **Update `.env` file**:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="HanapBuh.AI"
```

## Verify Email Configuration

### Check Logs
If using `log` driver, check:
```
storage/logs/laravel.log
```

Look for entries like:
```
[2024-01-01 12:00:00] local.INFO: OTP email sent successfully
```

### Test Email Sending

Run this in `php artisan tinker`:
```php
Mail::raw('Test email', function($message) {
    $message->to('your-email@example.com')
            ->subject('Test Email');
});
```

## Troubleshooting

### Gmail Issues:
- ✅ Use App Password, not regular password
- ✅ Enable "Less secure app access" (if not using App Password)
- ✅ Check spam folder

### Port Issues:
- Port 587 (TLS) - Recommended
- Port 465 (SSL) - Alternative
- Port 25 - Usually blocked by ISPs

### Firewall Issues:
- Make sure port 587 is not blocked
- Check if your hosting provider allows SMTP

## Development Mode (Current Behavior)

**Good news!** The system now shows the OTP on screen if email fails or is not configured. You'll see a yellow box with the OTP code when you register.

This allows you to:
- ✅ Test the registration flow without email setup
- ✅ See the OTP immediately
- ✅ Verify the system works

## Production Setup

For production, you MUST configure a real email service:
1. Use SendGrid, Mailgun, or AWS SES
2. Never use Gmail for production (rate limits)
3. Set up proper SPF/DKIM records
4. Monitor email delivery

## Quick Test

1. Register a new account
2. Check the verification page - you should see the OTP displayed
3. Enter the OTP to verify
4. If you want real emails, configure SMTP as shown above

