# Setup Email for OTP Verification

## Quick Setup Guide

Your system is currently using `log` driver which only logs emails. Follow these steps to receive real OTP emails.

## Option 1: Gmail Setup (Recommended for Testing) ✅

### Step 1: Enable 2-Factor Authentication
1. Go to: https://myaccount.google.com/security
2. Enable "2-Step Verification" if not already enabled

### Step 2: Generate App Password
1. Go to: https://myaccount.google.com/apppasswords
2. Select "Mail" as the app
3. Select "Other (Custom name)" as device
4. Enter "HanapBuh.AI" as the name
5. Click "Generate"
6. **Copy the 16-character password** (it looks like: `abcd efgh ijkl mnop`)

### Step 3: Update .env File
Open your `.env` file in the project root and add/update these lines:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=abcd efgh ijkl mnop
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="HanapBuh.AI"
```

**Important:** 
- Replace `your-email@gmail.com` with your actual Gmail address
- Replace `abcd efgh ijkl mnop` with the app password you generated (remove spaces or keep them, both work)
- Keep the quotes around "HanapBuh.AI" in MAIL_FROM_NAME

### Step 4: Clear Config Cache
Run this command in your terminal:
```bash
php artisan config:clear
```

### Step 5: Test
1. Register a new account
2. Check your email inbox (and spam folder)
3. You should receive the OTP email!

---

## Option 2: Mailtrap (Free Testing Service) ✅

Perfect for testing without using your personal email.

### Step 1: Sign Up
1. Go to: https://mailtrap.io
2. Sign up for a free account
3. Create a new inbox

### Step 2: Get SMTP Credentials
1. In Mailtrap dashboard, go to "SMTP Settings"
2. Select "PHP" tab
3. Copy the credentials

### Step 3: Update .env File
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

### Step 4: Clear Config Cache
```bash
php artisan config:clear
```

### Step 5: Test
1. Register a new account
2. Check your Mailtrap inbox
3. You'll see the email there!

---

## Option 3: SendGrid (Production Ready) ✅

### Step 1: Sign Up
1. Go to: https://sendgrid.com
2. Sign up (free tier: 100 emails/day)

### Step 2: Create API Key
1. Go to Settings > API Keys
2. Create a new API Key with "Mail Send" permissions
3. Copy the API key

### Step 3: Update .env File
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

### Step 4: Clear Config Cache
```bash
php artisan config:clear
```

---

## Troubleshooting

### Email Still Not Sending?

1. **Check Laravel Logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```
   Look for email-related errors

2. **Test Email Manually:**
   Run this in `php artisan tinker`:
   ```php
   Mail::raw('Test email', function($message) {
       $message->to('your-email@example.com')
               ->subject('Test Email');
   });
   ```

3. **Verify .env File:**
   - Make sure there are no spaces around `=`
   - Make sure values don't have extra quotes (except MAIL_FROM_NAME)
   - Make sure you saved the file

4. **Clear All Caches:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   ```

5. **Check Gmail App Password:**
   - Make sure you're using App Password, not your regular password
   - Make sure 2FA is enabled
   - Try generating a new app password

### Common Errors:

**"Connection could not be established"**
- Check if port 587 is not blocked by firewall
- Try port 465 with `MAIL_ENCRYPTION=ssl`

**"Authentication failed"**
- Double-check your username and password
- For Gmail, make sure you're using App Password

**"Email sent but not received"**
- Check spam/junk folder
- Wait a few minutes (some providers delay emails)
- Verify the email address is correct

---

## Verify Configuration

After updating `.env`, verify it's working:

```bash
php artisan tinker
```

Then run:
```php
config('mail.default')  // Should show 'smtp'
config('mail.mailers.smtp.host')  // Should show your SMTP host
```

---

## Next Steps

Once email is configured:
1. ✅ OTP will be sent to user's email
2. ✅ User receives email with 6-digit code
3. ✅ User enters code on verification page
4. ✅ Email is verified and user is logged in

The system will automatically detect if email is configured and send real emails instead of showing OTP on screen.

