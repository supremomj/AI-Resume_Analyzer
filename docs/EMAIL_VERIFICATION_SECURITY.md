# Email Verification Security Flow

## Overview
This document explains how the email verification system ensures users cannot access the system without verifying their email address.

## Security Flow

### 1. Registration (User Created, But Unverified)
- When a user registers, their account is **created in the database** with:
  - `email_verified_at = NULL` (unverified)
  - `email_verification_otp = [6-digit code]`
  - `email_verification_otp_expires_at = [15 minutes from now]`
- **This is normal behavior** - the account exists but is inactive.

### 2. Login Blocked for Unverified Users
- **Location**: `app/Http/Requests/Auth/LoginRequest.php` (line 54)
- If a user tries to log in without verifying their email:
  - Authentication succeeds (correct email/password)
  - **BUT** the system immediately logs them out
  - Redirects to the OTP verification page
  - Shows error: "Please verify your email address before logging in"

### 3. Protected Routes Require Verification
- **Location**: `routes/web.php` (line 21)
- All protected routes use `middleware(['auth', 'verified'])`:
  - `/home` - Main dashboard
  - `/profile` - User profile
  - `/jobs` - Job listings
  - `/resume/upload` - Resume upload
  - `/api/jobs/home` - Job API
  - `/api/bookmarks` - Bookmark API
- **Unverified users cannot access these routes** even if they somehow bypass login.

### 4. Email Verification (Auto-Login)
- **Location**: `app/Http/Controllers/Auth/EmailVerificationController.php` (line 60-62)
- When user enters correct OTP:
  1. `email_verified_at` is set to current timestamp
  2. OTP fields are cleared
  3. **User is automatically logged in** (`Auth::login($user)`)
  4. Session is regenerated for security
  5. Redirects to `/home` with success message

## Security Layers

### Layer 1: Login Block
- Prevents unverified users from authenticating
- **File**: `app/Http/Requests/Auth/LoginRequest.php`

### Layer 2: Route Protection
- `verified` middleware checks `hasVerifiedEmail()`
- Blocks access to all protected routes
- **File**: `routes/web.php`

### Layer 3: Database State
- `email_verified_at` must be set for user to be considered verified
- OTP expires after 15 minutes
- OTP is cleared after successful verification

## Testing the Flow

### Test 1: Unverified User Cannot Login
1. Register a new account
2. Try to log in immediately (before verifying)
3. **Expected**: Redirected to verification page with error message

### Test 2: Unverified User Cannot Access Protected Routes
1. Register a new account
2. Try to access `/home` directly (if somehow authenticated)
3. **Expected**: Redirected to verification page

### Test 3: Verified User Auto-Login
1. Register a new account
2. Check email for OTP
3. Enter OTP on verification page
4. **Expected**: Automatically logged in and redirected to `/home`

## Important Notes

### Why Users Are Created Before Verification?
- **Standard Practice**: This is the normal flow in most applications
- **Benefits**:
  - User can resend OTP if needed
  - OTP expiration can be tracked
  - User data is preserved if they verify later
- **Security**: Multiple layers prevent unverified users from accessing the system

### What Happens to Unverified Accounts?
- They remain in the database but are **completely inactive**
- Cannot log in
- Cannot access any protected routes
- OTP expires after 15 minutes (can request new one)
- Can be cleaned up later with a scheduled job (optional)

## Files Modified

1. **`app/Http/Controllers/Auth/EmailVerificationController.php`**
   - Added `Log` import
   - Added session regeneration after login
   - Auto-login after verification

2. **`routes/web.php`**
   - Added `verified` middleware to all protected routes

3. **`app/Http/Requests/Auth/LoginRequest.php`**
   - Already blocks unverified users (existing code)

## Summary

✅ **Users are created in database** (normal behavior)
✅ **Login is blocked** for unverified users
✅ **Protected routes require verification** (`verified` middleware)
✅ **Auto-login after verification** (user doesn't need to log in separately)
✅ **Session regeneration** for security after login

The system is secure - unverified users cannot access any protected functionality, even though their account exists in the database.

