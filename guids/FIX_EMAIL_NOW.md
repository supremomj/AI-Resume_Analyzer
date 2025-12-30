# Fix Email Configuration - Get Real-Time OTP Now! 🚀

## Current Issue
Your email is configured (`smtp.gmail.com`) but **credentials are missing or incorrect**, so emails aren't being sent.

## Quick Fix - Run This Command:

```bash
php artisan email:setup gmail
```

This will:
1. ✅ Ask for your Gmail address
2. ✅ Ask for your Gmail App Password
3. ✅ Automatically update `.env` file
4. ✅ Clear config cache
5. ✅ Configure everything for real-time OTP delivery

## Before Running the Command:

### Get Gmail App Password:

1. **Go to:** https://myaccount.google.com/apppasswords
2. **Sign in** to your Gmail account
3. **Select:**
   - App: **Mail**
   - Device: **Other (Custom name)**
   - Name: **HanapBuh.AI**
4. **Click "Generate"**
5. **Copy the 16-character password** (looks like: `abcd efgh ijkl mnop`)

## Then Run:

```bash
php artisan email:setup gmail
```

Enter:
- Your Gmail address (e.g., `yourname@gmail.com`)
- The 16-character app password you just copied

## After Setup:

✅ OTP emails will be sent **instantly** (real-time)  
✅ Users receive OTP in their email within 1-5 seconds  
✅ No more OTP displayed on screen  
✅ Fully automated - works on every registration  

## Test It:

1. Run the setup command
2. Register a new account
3. Check your email - OTP should arrive within seconds!

---

**That's it!** Once configured, OTP emails are sent automatically in real-time! 🎉

