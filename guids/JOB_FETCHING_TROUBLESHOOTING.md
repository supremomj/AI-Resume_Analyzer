# Job Fetching Troubleshooting Guide

## Problem: "No jobs found from external sources"

If you're seeing this message, it means the system is trying to fetch jobs from online job sites but getting no results. Here's how to diagnose and fix it.

## Quick Checks

### 1. Check if Flask API is Running
The Flask API is needed for job matching. Make sure it's running:
```bash
# Check if Flask API is accessible
curl http://localhost:8502/
```

If it's not running, start it:
```bash
cd C:\Users\azi\Desktop\UR\AI-Resume-Analyzer\App
..\venvapp\Scripts\activate.bat
python flask_api_new.py
```

### 2. Check User Has AI Analysis
The system requires users to have uploaded a resume and have AI analysis:
- User must have `ai_analysis` field populated
- User must have `recommended_field` set
- User must have skills extracted

### 3. Check Logs
View the latest logs to see what's happening:
```bash
# Windows PowerShell
Get-Content storage\logs\laravel.log -Tail 100 | Select-String -Pattern "job|Job|fetch|Fetch"
```

Look for:
- ✅ `Fetched {Source} jobs` - Success
- ⚠️ `No jobs found from {Source}` - No results (but request succeeded)
- ❌ `Failed to fetch from {Source}` - Request failed

## Common Issues

### Issue 1: SSL Certificate Errors (FIXED ✅)
**Symptoms:**
- Error: `cURL error 60: SSL certificate problem: unable to get local issuer certificate`
- All job sources fail with SSL errors
- Common on Windows/WAMP environments

**Solution:**
✅ **FIXED** - The system now automatically disables SSL verification in development mode (`APP_ENV=local`). SSL verification is only enabled in production.

**For Production:**
If you're in production and still getting SSL errors, you need to:
1. Download CA certificate bundle: https://curl.se/ca/cacert.pem
2. Update `php.ini`:
   ```ini
   curl.cainfo = "C:\path\to\cacert.pem"
   openssl.cafile = "C:\path\to\cacert.pem"
   ```
3. Or set in `.env`:
   ```env
   APP_ENV=production
   ```

### Issue 2: Web Scraping Blocked
**Symptoms:**
- All sources return empty results
- Logs show "No jobs found" for all sources
- HTTP status codes are 200 but no data

**Causes:**
- Job sites have anti-bot protection
- IP address is rate-limited
- User-Agent is blocked

**Solutions:**
1. **Clear cache and retry:**
   - Add `?refresh=true` to the API call
   - Or clear Laravel cache: `php artisan cache:clear`

2. **Check if sites are accessible:**
   - Try accessing job sites manually in a browser
   - Check if your network/firewall is blocking requests

3. **Wait and retry:**
   - Some sites rate-limit requests
   - Wait 5-10 minutes and try again

### Issue 2: HTML Structure Changed
**Symptoms:**
- Specific source always returns empty
- Logs show "No jobs found" for one source only

**Causes:**
- Job site updated their HTML structure
- CSS selectors/patterns no longer match

**Solutions:**
1. Check the logs for that specific source
2. The parsing logic may need to be updated
3. Contact support to update the scraper

### Issue 3: Network/Firewall Issues
**Symptoms:**
- All sources fail with timeout or connection errors
- HTTP status codes are not 200

**Solutions:**
1. Check internet connection
2. Check firewall settings
3. Check if proxy is needed
4. Try from a different network

### Issue 4: Cache Showing Old Empty Results
**Symptoms:**
- First fetch returned empty, now always empty
- Even after waiting, still no jobs

**Solutions:**
1. **Force refresh:**
   ```javascript
   // In browser console or API call
   fetch('/api/jobs/home?limit=6&refresh=true')
   ```

2. **Clear cache manually:**
   ```bash
   php artisan cache:clear
   ```

## Testing Job Fetching

### Test Individual Source
You can test if a specific source is working by checking the logs after a fetch attempt.

### Test with Force Refresh
Add `?refresh=true` parameter to bypass cache:
```
GET /api/jobs/home?limit=6&refresh=true
```

### Check What's Being Fetched
Look at the logs for:
- Search query being used
- URLs being accessed
- Response status codes
- Number of jobs found per source

## Expected Behavior

### Normal Flow:
1. User uploads resume → AI analyzes it
2. System builds search query from recommended field + skills
3. System fetches from 8 job sources:
   - Indeed (RSS feed)
   - Kalibrr (HTML parsing)
   - OnlineJobs.ph (HTML parsing)
   - Bossjob (HTML parsing)
   - WorkAbroad.ph (HTML parsing)
   - JobsDB (JSON-LD)
   - JobStreet (HTML parsing)
   - LinkedIn (placeholder)
4. Jobs are matched using AI semantic similarity
5. Jobs are sorted by match score
6. Top jobs are returned

### If No Jobs Found:
- System falls back to sample jobs based on user's field
- User sees a yellow notice: "Showing sample job recommendations"

## Debugging Steps

1. **Check user has AI analysis:**
   ```php
   $user = auth()->user();
   dd($user->ai_analysis, $user->recommended_field);
   ```

2. **Check search query:**
   ```php
   $service = new JobFetchingService();
   $query = $service->buildSearchQuery('Software Engineer', ['PHP', 'MySQL']);
   dd($query);
   ```

3. **Test Indeed RSS directly:**
   ```bash
   curl "https://ph.indeed.com/rss?q=Software+Engineer&l=Philippines"
   ```

4. **Check logs for detailed errors:**
   ```bash
   Get-Content storage\logs\laravel.log -Tail 200
   ```

## Solutions Implemented

### ✅ Enhanced Logging
- Added detailed logging for each source
- Shows success/failure with emojis (✅/⚠️/❌)
- Logs duration, query, and error details

### ✅ Better Error Handling
- Improved HTTP headers (User-Agent, Accept, Accept-Language)
- Increased timeout to 15 seconds
- Better error messages in logs

### ✅ Cache Bypass
- Added `forceRefresh` parameter
- Can bypass cache with `?refresh=true`
- Clears cache before fetching

### ✅ Source Status Tracking
- Logs which sources succeeded/failed
- Shows count of jobs per source
- Summary at the end of fetch

## Next Steps

If jobs still aren't fetching:

1. **Check the logs** - Look for specific error messages
2. **Test individual sources** - See which ones are failing
3. **Check network** - Ensure sites are accessible
4. **Update scrapers** - If HTML structure changed
5. **Consider API alternatives** - Some sites offer official APIs

## Contact

If you continue to have issues, check:
- Laravel logs: `storage/logs/laravel.log`
- Browser console for JavaScript errors
- Network tab for failed API requests

