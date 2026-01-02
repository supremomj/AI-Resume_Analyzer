# Quick Email Setup - Automatic OTP Sending

## 🚀 Fastest Way to Enable Email OTP

### Option 1: Automated Setup (Recommended) ⚡

Run this command and follow the prompts:

```bash
php artisan email:setup gmail
```

Or for Mailtrap (free testing):
```bash
php artisan email:setup mailtrap
```

The command will:
- ✅ Ask for your email credentials
- ✅ Automatically update your `.env` file
- ✅ Configure everything for you

**That's it!** After running this, OTP emails will be sent automatically when users register.

---

### Option 2: Manual Setup (If you prefer)

#### For Gmail:

1. **Get Gmail App Password:**
   - Go to: https://myaccount.google.com/apppasswords
   - Generate password for "Mail" → "Other (Custom name)" → "HanapBuh.AI"
   - Copy the 16-character password

2. **Update `.env` file:**
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=your-email@gmail.com
   MAIL_PASSWORD=your-app-password
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=your-email@gmail.com
   MAIL_FROM_NAME="HanapBuh.AI"
   ```

3. **Clear cache:**
   ```bash
   php artisan config:clear
   ```

#### For Mailtrap (Free Testing):

1. **Sign up:** https://mailtrap.io (free)
2. **Create inbox** and get credentials
3. **Update `.env` file:**
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

4. **Clear cache:**
   ```bash
   php artisan config:clear
   ```

---

## ✅ Verify It's Working

After setup, test it:

1. Register a new account
2. Check your email (or Mailtrap inbox)
3. You should receive the OTP email automatically!

---

## 🔧 Troubleshooting

**Email not sending?**
```bash
# Check current configuration
php artisan tinker
>>> config('mail.default')
>>> config('mail.mailers.smtp.host')
```

**Test email manually:**
```bash
php artisan tinker
>>> Mail::raw('Test', function($m) { $m->to('your@email.com')->subject('Test'); });
```

**Clear all caches:**
```bash
php artisan config:clear
php artisan cache:clear
```

---

## 📝 Notes

- **Gmail:** Requires 2FA and App Password
- **Mailtrap:** Perfect for testing, emails go to Mailtrap inbox
- **Production:** Use SendGrid, Mailgun, or AWS SES

Once configured, OTP emails are sent **automatically** on every registration! 🎉

