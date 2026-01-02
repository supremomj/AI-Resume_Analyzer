# Job API Setup Guide

This guide explains how to configure API-based job fetching for more accurate and reliable job postings.

## Why Use APIs?

✅ **More Accurate**: APIs provide structured, validated data  
✅ **More Reliable**: No HTML parsing issues or site structure changes  
✅ **Better Performance**: Faster than web scraping  
✅ **More Data**: Access to additional fields (salary, benefits, etc.)  
✅ **Rate Limits**: Proper rate limiting and quotas  

## Available APIs

### 1. Adzuna API (Recommended)
- **Aggregates**: Multiple job sources including Indeed, Monster, etc.
- **Coverage**: Global, including Philippines
- **Free Tier**: 1,000 requests/day
- **Sign Up**: https://developer.adzuna.com/
- **Documentation**: https://developer.adzuna.com/overview

### 2. Jooble API
- **Aggregates**: Multiple job boards and company websites
- **Coverage**: Global, including Philippines
- **Free Tier**: 500 requests/day
- **Sign Up**: https://jooble.org/api/about
- **Documentation**: https://jooble.org/api/about

### 3. Jobdata API
- **Aggregates**: Multiple job sources
- **Coverage**: Global, including Philippines
- **Pricing**: Contact for pricing
- **Sign Up**: https://jobdataapi.com/
- **Documentation**: https://jobdataapi.com/docs

### 4. ScraperAPI
- **Purpose**: Web scraping service (for sites without APIs)
- **Use For**: LinkedIn, Glassdoor, Indeed (when API unavailable)
- **Free Tier**: 5,000 requests/month
- **Sign Up**: https://www.scraperapi.com/
- **Documentation**: https://docs.scraperapi.com/

### 5. LinkedIn API
- **Purpose**: Official LinkedIn job listings
- **Requirements**: LinkedIn Partner Program access
- **Documentation**: https://docs.microsoft.com/en-us/linkedin/

## Setup Instructions

### Step 1: Get API Keys

1. **Adzuna**:
   - Visit https://developer.adzuna.com/
   - Sign up for free account
   - Get your `APP_ID` and `APP_KEY`

2. **Jooble**:
   - Visit https://jooble.org/api/about
   - Request API key
   - Get your `API_KEY`

3. **Jobdata**:
   - Visit https://jobdataapi.com/
   - Sign up and get your `API_KEY`

4. **ScraperAPI**:
   - Visit https://www.scraperapi.com/
   - Sign up for free tier
   - Get your `API_KEY`

### Step 2: Configure Environment Variables

Add these to your `.env` file:

```env
# Adzuna API
ADZUNA_API_ENABLED=true
ADZUNA_APP_ID=your_app_id_here
ADZUNA_APP_KEY=your_app_key_here

# Jooble API
JOOBLE_API_ENABLED=true
JOOBLE_API_KEY=your_api_key_here

# Jobdata API
JOBDATA_API_ENABLED=true
JOBDATA_API_KEY=your_api_key_here

# ScraperAPI (for LinkedIn, etc.)
SCRAPERAPI_ENABLED=true
SCRAPERAPI_KEY=your_api_key_here

# LinkedIn API (optional, requires partner access)
LINKEDIN_API_ENABLED=false
LINKEDIN_CLIENT_ID=your_client_id
LINKEDIN_CLIENT_SECRET=your_client_secret
LINKEDIN_ACCESS_TOKEN=your_access_token

# Fallback to web scraping if API fails
JOB_API_FALLBACK_TO_SCRAPING=true
```

### Step 3: Clear Cache

After configuring APIs, clear the cache:

```bash
php artisan cache:clear
php artisan config:clear
```

## How It Works

1. **API Priority**: The system tries APIs first (Adzuna, Jooble, Jobdata)
2. **Fallback**: If API fails or is not configured, falls back to web scraping
3. **Hybrid Approach**: Uses APIs for aggregators, scraping for direct sources
4. **Rate Limiting**: Built-in rate limiting to respect API quotas

## Testing APIs

Test if your APIs are working:

```bash
php artisan tinker
```

```php
$service = new \App\Services\JobFetchingService(auth()->user());
$jobs = $service->fetchJobsForUser(10, true);
dd($jobs);
```

Check the logs:

```bash
tail -f storage/logs/laravel.log | grep -i "api fetched"
```

## Cost Comparison

### Free Tier Options:
- **Adzuna**: 1,000 requests/day (free)
- **Jooble**: 500 requests/day (free)
- **ScraperAPI**: 5,000 requests/month (free)

### Recommended Setup:
1. **Primary**: Adzuna API (best coverage)
2. **Secondary**: Jooble API (backup)
3. **ScraperAPI**: For LinkedIn/Glassdoor (if needed)

## Troubleshooting

### API Not Working?
1. Check API keys in `.env`
2. Verify API is enabled in `config/job_apis.php`
3. Check API quotas/limits
4. Review logs: `storage/logs/laravel.log`

### No Jobs Returned?
1. Check API response in logs
2. Verify search query is valid
3. Try different keywords
4. Check if API supports Philippines location

### Rate Limit Errors?
1. Reduce `perSourceLimit` in `JobFetchingService.php`
2. Increase cache time
3. Upgrade API plan

## Benefits of API vs Scraping

| Feature | API | Web Scraping |
|---------|-----|--------------|
| Accuracy | ✅ High | ⚠️ Medium |
| Reliability | ✅ High | ⚠️ Breaks on site changes |
| Performance | ✅ Fast | ⚠️ Slower |
| Data Quality | ✅ Structured | ⚠️ Needs parsing |
| Rate Limits | ✅ Official | ❌ Risk of blocking |
| Cost | ⚠️ May have cost | ✅ Free |

## Next Steps

1. Sign up for at least one API (Adzuna recommended)
2. Add API keys to `.env`
3. Test the integration
4. Monitor logs for any issues
5. Adjust rate limits if needed

For questions or issues, check the logs or review the API documentation.

