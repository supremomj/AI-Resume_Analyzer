# AI Integration Setup Guide

## Overview
Your Laravel application is now integrated with your Flask AI Resume Analyzer located at:
`C:\Users\azi\Desktop\UR\AI-Resume-Analyzer`

## Setup Instructions

### 1. Start Your Flask API

⚠️ **IMPORTANT**: You must run `flask_api_new.py` (NOT `flask_api.py`) to get salary data!

Navigate to your AI folder and start the Flask server:

```bash
cd C:\Users\azi\Desktop\UR\AI-Resume-Analyzer\App
python flask_api_new.py
```

**OR use the batch file:**
```bash
RUN_FLASK_API.bat
```

The Flask API will run on `http://localhost:8502` (default port).

**Note**: If you're running the old `flask_api.py`, you won't get Philippine salary data. Always use `flask_api_new.py`.

### 2. Configure Laravel Environment

Add this line to your `.env` file:

```env
AI_FLASK_API_URL=http://localhost:8502
```

If your Flask API runs on a different port or host, update this URL accordingly.

### 3. How It Works

1. **User uploads resume** → Laravel saves it to `storage/app/public/resumes/`
2. **Laravel calls Flask API** → Sends the resume file to `/predict` endpoint
3. **Flask AI analyzes** → Uses pyresparser and SentenceTransformer to analyze
4. **Results stored** → AI analysis saved to user's `ai_analysis` field in database
5. **Display results** → Shows on upload page and can be used throughout the app

### 4. Database Fields Added

- `ai_analysis` (JSON) - Full AI analysis results
- `resume_score` (integer) - Calculated resume score
- `recommended_field` (string) - Best matching job field

### 5. Using AI Results in Your Code

```php
// Get user's AI analysis
$user = Auth::user();
$analysis = $user->ai_analysis;

// Access specific data
$skills = $analysis['skills'] ?? [];
$recommendedField = $user->recommended_field;
$resumeScore = $user->resume_score;
$recommendedSkills = $analysis['recommended_skills'] ?? [];
$recommendedCourses = $analysis['recommended_courses'] ?? [];
```

### 6. Troubleshooting

- **Flask API not responding**: Make sure it's running on port 8502
- **Connection refused**: Check firewall settings and Flask API is accessible
- **Analysis fails**: Check Laravel logs at `storage/logs/laravel.log`
- **File not found**: Ensure resume is saved before calling AI service

### 7. Testing

1. Upload a resume through the upload page
2. Check Laravel logs for AI analysis results
3. View the results on the upload page after successful analysis
4. Check database `users` table for `ai_analysis` JSON data

## Next Steps

You can now use the AI analysis results to:
- Display on home page dashboard
- Show in jobs page for better matching
- Use in profile page to show user's skills
- Generate personalized job recommendations

