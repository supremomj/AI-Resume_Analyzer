# HanapBuhai Backend

A Laravel 12 backend application for job matching and employment services with AI integration and real-time job fetching.

## Features

- **Job Matching**: AI-powered job matching system
- **Real-time Job Fetching**: Integrated with multiple job APIs
- **OTP Email Verification**: Secure email verification with one-time passwords
- **Social Authentication**: Google and other social provider integrations (via Socialite)
- **Image Processing**: Advanced image handling with Intervention Image
- **API Management**: Comprehensive job API integrations
- **Email Configuration**: Multiple email provider support

## Tech Stack

- **Framework**: Laravel 12
- **PHP Version**: 8.2 or higher
- **Database**: MySQL/PostgreSQL (configurable)
- **Testing**: Pest PHP
- **Package Manager**: Composer

## Prerequisites

- PHP 8.2+
- Composer
- MySQL or PostgreSQL
- Node.js & npm (for frontend assets)

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd hanapbuhai-backend
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Build assets**
   ```bash
   npm run build
   ```

## Configuration

### Email Setup
Comprehensive email configuration guides are available:
- [Quick Email Setup](docs/QUICK_EMAIL_SETUP.md)
- [Email Configuration Guide](docs/EMAIL_CONFIGURATION_GUIDE.md)
- [OTP Email Verification](docs/EMAIL_VERIFICATION_OTP_SETUP.md)

### Job API Integration
- [Job API Setup](docs/JOB_API_SETUP.md)
- [Job Fetching Setup](docs/JOB_FETCHING_SETUP.md)
- [Real-time Job Fetching](docs/REALTIME_JOB_FETCHING.md)
- [Philippine Job API Setup](docs/PHILIPPINE_JOB_API_SETUP.md)

### AI Integration
- [AI Integration Setup](docs/AI_INTEGRATION_SETUP.md)
- [AI Job Matching Connection](docs/AI_JOB_MATCHING_CONNECTION.md)
- [AI Quick Cheat Sheet](docs/AI_QUICK_CHEAT_SHEET.md)

### Other Important Guides
- [Flask API Setup](docs/FLASK_API_SETUP.md)
- [Google Workspace Setup](docs/GOOGLE_WORKSPACE_SETUP.md)
- [Security Improvements](docs/SECURITY_IMPROVEMENTS.md)
- [Backend Improvements](docs/BACKEND_IMPROVEMENTS.md)

## Running the Application

### Development
```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

### Build Frontend Assets
```bash
# Development
npm run dev

# Production
npm run build
```

### Run Tests
```bash
php artisan test
```

## Project Structure

```
app/
├── Console/          # Artisan commands
├── Http/             # Controllers, middleware, requests
├── Models/           # Eloquent models
├── Notifications/    # Email and notification classes
├── Providers/        # Service providers
├── Services/         # Business logic services
└── View/             # View-related classes

config/              # Configuration files
database/            # Migrations and seeders
docs/                # Documentation
public/              # Public assets
resources/           # Views, CSS, JavaScript
routes/              # Route definitions
storage/             # Logs and file storage
tests/               # Test files
```

## Helper Scripts

Several utility scripts are available in the `scripts/` directory:
- `setup_email.bat` - Email configuration setup
- `start_flask_api.bat` - Start Flask API server
- `test_api_job_fetching.php` - Test job API integration
- `test_job_fetching.php` - Test job fetching functionality
- `test_ai_connection.php` - Test AI connection
- `check_api_config.php` - Verify API configuration

## API Endpoints

The application provides RESTful API endpoints. Refer to the API setup checklist for detailed endpoint documentation:
- [API Setup Checklist](docs/API_SETUP_CHECKLIST.md)

## Database

### Migrations
Run migrations to set up the database:
```bash
php artisan migrate
```

### Seeders
Populate sample data:
```bash
php artisan db:seed
```

## Environment Variables

Key environment variables to configure:

```
APP_NAME=HanapBuhai
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hanapbuhai
DB_USERNAME=root
DB_PASSWORD=

MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password

FLASK_API_URL=http://localhost:5000
```

## Troubleshooting

### Email Configuration Issues
See [FIX_EMAIL_NOW.md](docs/FIX_EMAIL_NOW.md) for quick fixes

### Email Verification & Security
Review [EMAIL_VERIFICATION_SECURITY.md](docs/EMAIL_VERIFICATION_SECURITY.md)

### Job Fetching Troubleshooting
Check [JOB_FETCHING_TROUBLESHOOTING.md](docs/JOB_FETCHING_TROUBLESHOOTING.md)

### System Analysis
For comprehensive system overview: [SYSTEM_ANALYSIS.md](docs/SYSTEM_ANALYSIS.md)

## Contributing

Please follow the Laravel coding standards and ensure all tests pass before submitting pull requests.

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For detailed setup and integration instructions, refer to the comprehensive documentation in the [docs/](docs/) folder.
