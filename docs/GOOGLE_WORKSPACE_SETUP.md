# Google Workspace (Business Gmail) Setup for OTP

## Quick Setup for Business Gmail Account

Since you have a **Google Workspace (business Gmail) account**, follow these steps:

### Step 1: Generate App Password

1. **Go to:** https://myaccount.google.com/apppasswords
   - (Or: Google Account → Security → 2-Step Verification → App passwords)

2. **Sign in** with your business Gmail account

3. **If App Passwords option is missing:**
   - Your admin may have disabled it
   - Contact your Google Workspace admin to enable it
   - Or use the alternative method below

4. **Generate App Password:**
   - Select "Mail"
   - Select "Other (Custom name)"
   - Enter: **HanapBuh.AI**
   - Click "Generate"
   - **Copy the 16-character password**

### Step 2: Run Setup Command

```bash
php artisan email:setup gmail
```

When prompted:
- **Email:** Enter your business Gmail (e.g., `yourname@yourcompany.com`)
- **Password:** Enter the 16-character app password you just generated

### Step 3: Test

1. Register a new account
2. Check your business email
3. OTP should arrive within seconds!

---

## Alternative: If App Passwords Are Disabled

If your Google Workspace admin has disabled App Passwords, you have these options:

### Option A: Ask Admin to Enable App Passwords

Your admin needs to:
1. Go to Google Admin Console
2. Security → API Controls
3. Enable "App Passwords" for your organization or your account

### Option B: Use OAuth 2.0 (Advanced)

Requires setting up OAuth credentials. Contact your admin or use a service like SendGrid instead.

### Option C: Use SendGrid or Mailtrap

If Google Workspace is too restrictive, use a dedicated email service:

```bash
php artisan email:setup mailtrap
```

Or configure SendGrid manually in `.env`.

---

## Google Workspace SMTP Settings

If setting up manually in `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=yourname@yourcompany.com
MAIL_PASSWORD=your-16-character-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=yourname@yourcompany.com
MAIL_FROM_NAME="HanapBuh.AI"
```

**Note:** 
- Workspace accounts use the same SMTP server (`smtp.gmail.com`)
- Port 587 with TLS encryption
- Requires App Password (not your regular password)

---

## Troubleshooting

### "App Passwords not available"
- Your admin has disabled it
- Contact admin to enable App Passwords
- Or use alternative email service (Mailtrap, SendGrid)

### "Authentication failed"
- Make sure you're using App Password, not regular password
- Verify 2FA is enabled
- Check if admin has restricted SMTP access

### "Connection timeout"
- Check if your network/firewall blocks port 587
- Try port 465 with SSL instead:
  ```env
  MAIL_PORT=465
  MAIL_ENCRYPTION=ssl
  ```

---

## Quick Command

Just run:
```bash
php artisan email:setup gmail
```

Enter your business email and app password when prompted. The system will configure everything automatically for real-time OTP delivery! 🚀

