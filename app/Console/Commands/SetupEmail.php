<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SetupEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:setup {driver=gmail}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup email configuration for OTP verification';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $driver = $this->argument('driver');
        
        $this->info('========================================');
        $this->info('Email Configuration Setup');
        $this->info('========================================');
        $this->newLine();

        if ($driver === 'gmail') {
            $this->setupGmail();
        } elseif ($driver === 'mailtrap') {
            $this->setupMailtrap();
        } else {
            $this->error('Invalid driver. Use: gmail or mailtrap');
            return 1;
        }

        $this->newLine();
        $this->info('✓ Configuration updated!');
        $this->info('Clearing config cache...');
        $this->call('config:clear');
        $this->newLine();
        $this->info('✅ Email is now configured! OTP emails will be sent automatically.');
        $this->info('Test it by registering a new account.');
        return 0;
    }

    private function setupGmail()
    {
        $this->info('Gmail / Google Workspace Email Setup');
        $this->newLine();
        $this->warn('Before proceeding, make sure you have:');
        $this->line('1. Enabled 2-Factor Authentication on your Google account');
        $this->line('2. Generated an App Password from: https://myaccount.google.com/apppasswords');
        $this->line('   (For Google Workspace: Admin may need to enable "Less secure app access" or use OAuth)');
        $this->newLine();

        $email = $this->ask('Enter your Gmail/Google Workspace email address');
        $password = $this->secret('Enter your Google App Password (16 characters)');

        if (!$email || !$password) {
            $this->error('Email and password are required!');
            return;
        }

        // Check if it's a Google Workspace account (has custom domain)
        $isWorkspace = !str_ends_with(strtolower($email), '@gmail.com');
        
        if ($isWorkspace) {
            $this->info('Detected Google Workspace account (business email)');
            $this->warn('Note: If App Passwords are disabled, contact your admin or use OAuth.');
        }

        $this->updateEnvFile([
            'MAIL_MAILER' => 'smtp',
            'MAIL_HOST' => 'smtp.gmail.com',
            'MAIL_PORT' => '587',
            'MAIL_USERNAME' => $email,
            'MAIL_PASSWORD' => $password,
            'MAIL_ENCRYPTION' => 'tls',
            'MAIL_FROM_ADDRESS' => $email,
            'MAIL_FROM_NAME' => '"HanapBuh.AI"',
        ]);

        $this->info('✓ Gmail/Google Workspace configuration saved!');
        
        if ($isWorkspace) {
            $this->newLine();
            $this->warn('Google Workspace Note:');
            $this->line('If you encounter issues, your admin may need to:');
            $this->line('1. Enable "Less secure app access" (legacy)');
            $this->line('2. Or allow App Passwords for your account');
            $this->line('3. Or configure OAuth 2.0 for SMTP');
        }
    }

    private function setupMailtrap()
    {
        $this->info('Mailtrap Email Setup (Free Testing Service)');
        $this->newLine();
        $this->warn('Sign up at: https://mailtrap.io (free account)');
        $this->line('Then create an inbox and get SMTP credentials');
        $this->newLine();

        $username = $this->ask('Enter Mailtrap Username');
        $password = $this->secret('Enter Mailtrap Password');

        if (!$username || !$password) {
            $this->error('Username and password are required!');
            return;
        }

        $this->updateEnvFile([
            'MAIL_MAILER' => 'smtp',
            'MAIL_HOST' => 'smtp.mailtrap.io',
            'MAIL_PORT' => '2525',
            'MAIL_USERNAME' => $username,
            'MAIL_PASSWORD' => $password,
            'MAIL_ENCRYPTION' => 'tls',
            'MAIL_FROM_ADDRESS' => 'noreply@hanapbuhai.com',
            'MAIL_FROM_NAME' => '"HanapBuh.AI"',
        ]);

        $this->info('Mailtrap configuration saved!');
    }

    private function updateEnvFile(array $config)
    {
        $envPath = base_path('.env');
        
        if (!File::exists($envPath)) {
            $this->error('.env file not found!');
            return;
        }

        $envContent = File::get($envPath);

        foreach ($config as $key => $value) {
            // Remove spaces from passwords and wrap values with spaces in quotes
            if ($key === 'MAIL_PASSWORD') {
                // Remove all spaces from app password
                $value = str_replace(' ', '', $value);
            } elseif (str_contains($value, ' ') && !str_starts_with($value, '"')) {
                // Wrap values with spaces in quotes (if not already quoted)
                $value = '"' . $value . '"';
            }
            
            $pattern = "/^{$key}=.*/m";
            
            if (preg_match($pattern, $envContent)) {
                // Update existing - escape special characters
                $escapedValue = preg_quote($value, '/');
                $envContent = preg_replace($pattern, "{$key}={$value}", $envContent);
            } else {
                // Add new
                $envContent .= "\n{$key}={$value}";
            }
        }

        File::put($envPath, $envContent);
        $this->info('✓ .env file updated');
    }
}

