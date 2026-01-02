@echo off
echo ========================================
echo Email Configuration Helper
echo ========================================
echo.
echo This script will help you configure email for OTP verification.
echo.
echo Current mail driver: 
php artisan tinker --execute="echo config('mail.default');"
echo.
echo.
echo ========================================
echo Email Configuration Options:
echo ========================================
echo.
echo 1. Gmail (Recommended for testing)
echo 2. Mailtrap (Free testing service)
echo 3. SendGrid (Production ready)
echo 4. View current configuration
echo 5. Test email sending
echo.
set /p choice="Enter your choice (1-5): "

if "%choice%"=="1" goto gmail
if "%choice%"=="2" goto mailtrap
if "%choice%"=="3" goto sendgrid
if "%choice%"=="4" goto viewconfig
if "%choice%"=="5" goto testemail
goto end

:gmail
echo.
echo ========================================
echo Gmail Setup Instructions:
echo ========================================
echo.
echo 1. Enable 2-Factor Authentication on your Gmail account
echo 2. Generate App Password: https://myaccount.google.com/apppasswords
echo 3. Select "Mail" and "Other (Custom name)"
echo 4. Enter "HanapBuh.AI" as name
echo 5. Copy the 16-character password
echo.
echo Then update your .env file with:
echo.
echo MAIL_MAILER=smtp
echo MAIL_HOST=smtp.gmail.com
echo MAIL_PORT=587
echo MAIL_USERNAME=your-email@gmail.com
echo MAIL_PASSWORD=your-app-password-here
echo MAIL_ENCRYPTION=tls
echo MAIL_FROM_ADDRESS=your-email@gmail.com
echo MAIL_FROM_NAME="HanapBuh.AI"
echo.
pause
goto end

:mailtrap
echo.
echo ========================================
echo Mailtrap Setup Instructions:
echo ========================================
echo.
echo 1. Sign up at: https://mailtrap.io
echo 2. Create a new inbox
echo 3. Go to SMTP Settings ^> PHP tab
echo 4. Copy the credentials
echo.
echo Then update your .env file with:
echo.
echo MAIL_MAILER=smtp
echo MAIL_HOST=smtp.mailtrap.io
echo MAIL_PORT=2525
echo MAIL_USERNAME=your-mailtrap-username
echo MAIL_PASSWORD=your-mailtrap-password
echo MAIL_ENCRYPTION=tls
echo MAIL_FROM_ADDRESS=noreply@hanapbuhai.com
echo MAIL_FROM_NAME="HanapBuh.AI"
echo.
pause
goto end

:sendgrid
echo.
echo ========================================
echo SendGrid Setup Instructions:
echo ========================================
echo.
echo 1. Sign up at: https://sendgrid.com
echo 2. Go to Settings ^> API Keys
echo 3. Create API Key with "Mail Send" permission
echo 4. Copy the API key
echo.
echo Then update your .env file with:
echo.
echo MAIL_MAILER=smtp
echo MAIL_HOST=smtp.sendgrid.net
echo MAIL_PORT=587
echo MAIL_USERNAME=apikey
echo MAIL_PASSWORD=your-sendgrid-api-key
echo MAIL_ENCRYPTION=tls
echo MAIL_FROM_ADDRESS=noreply@yourdomain.com
echo MAIL_FROM_NAME="HanapBuh.AI"
echo.
pause
goto end

:viewconfig
echo.
echo ========================================
echo Current Email Configuration:
echo ========================================
php artisan tinker --execute="echo 'Mail Driver: ' . config('mail.default') . PHP_EOL; echo 'SMTP Host: ' . config('mail.mailers.smtp.host') . PHP_EOL; echo 'SMTP Port: ' . config('mail.mailers.smtp.port') . PHP_EOL; echo 'From Address: ' . config('mail.from.address') . PHP_EOL;"
echo.
pause
goto end

:testemail
echo.
echo ========================================
echo Testing Email Configuration:
echo ========================================
echo.
set /p testemail="Enter your email address to test: "
php artisan tinker --execute="Mail::raw('Test email from HanapBuh.AI', function($message) { $message->to('%testemail%')->subject('Test Email'); }); echo 'Test email sent! Check your inbox.';"
echo.
pause
goto end

:end
echo.
echo After updating .env, run: php artisan config:clear
echo.
pause

