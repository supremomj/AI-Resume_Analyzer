# Job Fetching System - Setup Guide

## Overview
Your application now fetches real jobs from Philippine job sites and matches them to users' resumes based on their AI analysis.

## How It Works

1. **User uploads resume** → AI analyzes it and extracts skills, experience, and recommends a field
2. **Job fetching** → System searches job sites using the user's recommended field and skills
3. **Matching algorithm** → Calculates match score based on:
   - Field match (30 points)
   - Skill matches (10 points per skill)
   - Location (10 points for Philippines)
   - Multiple skill matches bonus (20 points)
4. **Display** → Jobs are sorted by match score and shown to the user

## Current Job Sources

### ✅ Active Sources (8 total)

1. **Indeed Philippines**
   - **Status**: ✅ Active
   - **Method**: RSS Feed
   - **URL**: `https://ph.indeed.com/rss`
   - **Reliability**: High

2. **Kalibrr**
   - **Status**: ✅ Active
   - **Method**: HTML Parsing (JSON embedded)
   - **URL**: `https://www.kalibrr.com/ph/jobs`
   - **Reliability**: Medium-High

3. **OnlineJobs.ph**
   - **Status**: ✅ Active
   - **Method**: HTML Parsing
   - **URL**: `https://www.onlinejobs.ph/jobseekers/jobsearch`
   - **Reliability**: Medium

4. **Bossjob**
   - **Status**: ✅ Active
   - **Method**: HTML Parsing (JSON embedded)
   - **URL**: `https://www.bossjob.ph/jobs`
   - **Reliability**: Medium

5. **WorkAbroad.ph**
   - **Status**: ✅ Active
   - **Method**: HTML Parsing
   - **URL**: `https://www.workabroad.ph/search`
   - **Reliability**: Medium
   - **Note**: Includes overseas jobs for Filipinos

6. **JobsDB**
   - **Status**: ✅ Active
   - **Method**: JSON-LD Structured Data
   - **URL**: `https://www.jobsdb.com.ph/en/jobs`
   - **Reliability**: Medium-High

7. **JobStreet**
   - **Status**: ✅ Active
   - **Method**: HTML Parsing (JSON embedded)
   - **URL**: `https://www.jobstreet.com.ph/en/job-search`
   - **Reliability**: Medium

8. **LinkedIn**
   - **Status**: ⚠️ Requires API Access
   - **Method**: API (requires authentication)
   - **Note**: Currently disabled - needs LinkedIn API credentials

## Files Created

1. **`app/Services/JobFetchingService.php`**
   - Main service for fetching and matching jobs
   - Handles caching (1 hour cache)
   - Calculates match scores

2. **`app/Http/Controllers/JobController.php`**
   - Handles job requests
   - Provides API endpoint for home page
   - Filters jobs based on user preferences

3. **Updated Views**:
   - `resources/views/home.blade.php` - Fetches jobs via AJAX
   - `resources/views/jobs.blade.php` - Displays full job listings

## Usage

### For Users
1. Upload your resume
2. Wait for AI analysis
3. View personalized job recommendations on:
   - Home page (6 jobs)
   - Jobs page (all matching jobs)

### For Developers

#### Fetching Jobs Programmatically
```php
use App\Services\JobFetchingService;

$jobService = new JobFetchingService($user);
$jobs = $jobService->fetchJobsForUser(20); // Get 20 jobs
```

#### With Filters
```php
$filters = [
    'location' => 'Manila',
    'min_score' => 70,
];
$jobs = $jobService->getJobsWithFilters($filters, 20);
```

## Caching

Jobs are cached for 1 hour per user to:
- Reduce API calls
- Improve performance
- Avoid rate limiting

Cache key format: `jobs_user_{user_id}_{hash}`

To clear cache:
```php
Cache::forget("jobs_user_{$userId}_" . md5(json_encode($user->ai_analysis)));
```

## Match Score Calculation

The match score (0-100) is calculated as:
- **Field Match**: 30 points if job title contains recommended field
- **Skill Matches**: 10 points per matching skill
- **Multiple Skills Bonus**: 20 points if 3+ skills match
- **Location Bonus**: 10 points for Philippines locations

## Extending the System

### Adding New Job Sources

1. Add a new method in `JobFetchingService.php`:
```php
protected function fetchFromNewSource(string $query, int $limit): array
{
    // Fetch jobs from new source
    // Return array of jobs with: title, company, location, description, url, source
}
```

2. Call it in `fetchJobsForUser()`:
```php
$jobs = array_merge(
    $jobs,
    $this->fetchFromNewSource($searchQuery, $limit),
);
```

### Job Data Structure

Each job should have:
```php
[
    'title' => 'Job Title',
    'company' => 'Company Name',
    'location' => 'Manila, Philippines',
    'description' => 'Job description...',
    'url' => 'https://job-url.com',
    'source' => 'Indeed',
    'published_at' => '2025-11-18 10:00:00',
]
```

## Troubleshooting

### No Jobs Showing
1. Check if user has AI analysis: `auth()->user()->ai_analysis`
2. Check Laravel logs: `storage/logs/laravel.log`
3. Verify Indeed RSS feed is accessible
4. Check cache: `php artisan cache:clear`

### Jobs Not Matching Well
- Adjust match score calculation in `calculateMatchScore()`
- Improve skill extraction in AI analysis
- Add more job sources

### Rate Limiting
- Increase cache time in `JobFetchingService.php`
- Add delays between requests
- Use multiple job sources to distribute load

## Future Improvements

1. **Web Scraping for JobStreet**
   - Use Goutte or similar library
   - Handle rate limiting and CAPTCHAs
   - Respect robots.txt

2. **Job APIs**
   - Apply for official APIs from job sites
   - Use API keys for better access

3. **Advanced Matching**
   - Use AI/ML for better matching
   - Consider salary ranges
   - Match experience levels

4. **User Preferences**
   - Save favorite jobs
   - Job application tracking
   - Email notifications for new matches

## Notes

- Jobs are fetched in real-time but cached for performance
- Match scores are calculated on-the-fly
- Jobs open in new tabs (external links)
- All jobs are from Philippine job sites

