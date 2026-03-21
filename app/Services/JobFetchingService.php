<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use Illuminate\Support\Facades\Config;

class JobFetchingService
{
    protected $user;
    protected $cacheTime = 600; // 10 minutes cache for better performance - jobs refresh every 10 minutes

    public function __construct(?User $user = null)
    {
        $this->user = $user ?? auth()->user();
    }

    /**
     * Fetch jobs from multiple Philippine job sites
     */
    public function fetchJobsForUser(int $limit = 20, bool $forceRefresh = false): array
    {
        if (!$this->user || !$this->user->ai_analysis) {
            Log::warning('Cannot fetch jobs: User missing or no AI analysis', [
                'user_id' => $this->user->id ?? null,
                'has_ai_analysis' => !empty($this->user->ai_analysis ?? null),
            ]);
            return [];
        }

        $cacheKey = "jobs_user_{$this->user->id}_" . md5(json_encode($this->user->ai_analysis));

        // For real-time fetching (cacheTime = 0), always bypass cache
        // Clear cache if force refresh is requested OR if cacheTime is 0 (real-time)
        if ($forceRefresh || $this->cacheTime === 0) {
            Cache::forget($cacheKey);
            if ($this->cacheTime === 0) {
                Log::info('Real-time job fetching - bypassing cache', ['user_id' => $this->user->id]);
            } else {
                Log::info('Job cache cleared for force refresh', ['user_id' => $this->user->id]);
            }
        }

        // If cacheTime is 0, don't use cache at all - always fetch fresh
        if ($this->cacheTime === 0 || $forceRefresh) {
            return $this->fetchJobsRealTime($limit);
        }

        return Cache::remember($cacheKey, $this->cacheTime, function () use ($limit) {
            return $this->fetchJobsRealTime($limit);
        });
    }

    /**
     * Fetch jobs in real-time from all sources
     * This method is called directly for real-time fetching or via cache wrapper
     */
    protected function fetchJobsRealTime(int $limit): array
    {
        $jobs = [];

        // Get user's skills and recommended field
        $skills = $this->user->ai_analysis['skills'] ?? [];
        $recommendedField = $this->user->recommended_field ?? 'Software Engineering';

        // Build search query based on AI analysis
        $searchQuery = $this->buildSearchQuery($recommendedField, $skills);

        // OPTIMIZED: Reduced job limits for faster fetching
        // Each source fetches only what's needed (reduced from 2x-3x to 1x-1.5x)
        $perSourceLimit = max(10, $limit); // Reduced to 1x limit (was 2x) for faster fetching

        // PRIORITY SOURCES: OnlineJobs.ph and Indeed only - fetch slightly more
        $prioritySourceLimit = max(15, (int) ($limit * 1.5)); // Reduced to 1.5x limit (was 3x) for speed

        // PRIORITY ORDER: API-based sources first (reliable), then scrapers as fallback
        $sources = [
            // PRIMARY API SOURCES (Highly Reliable / User-Requested)
            'JSearch' => fn() => (new \App\Services\Scrapers\JSearchApiScraper())->fetchJobs($searchQuery, $prioritySourceLimit),
            'Adzuna' => fn() => (new \App\Services\Scrapers\AdzunaApiScraper())->fetchJobs($searchQuery, $prioritySourceLimit),
            
            // SECONDARY API SOURCES (Fallbacks / Free Tiers)
            'Jooble' => fn() => (new \App\Services\Scrapers\JoobleApiScraper())->fetchJobs($searchQuery, $perSourceLimit),
            'Remotive' => fn() => (new \App\Services\Scrapers\RemotiveApiScraper())->fetchJobs($searchQuery, $perSourceLimit),
            'Arbeitnow' => fn() => (new \App\Services\Scrapers\ArbeitnowApiScraper())->fetchJobs($searchQuery, $perSourceLimit),
            'The Muse' => fn() => (new \App\Services\Scrapers\TheMuseApiScraper())->fetchJobs($searchQuery, $perSourceLimit),
            
            // TERTIARY SOURCES - Web scrapers as a last resort
            'OnlineJobs.ph' => fn() => (new \App\Services\Scrapers\OnlineJobsScraper())->fetchJobs($searchQuery, $perSourceLimit),
            'Indeed' => fn() => (new \App\Services\Scrapers\IndeedScraper())->fetchJobs($searchQuery, $perSourceLimit),
            'Kalibrr' => fn() => (new \App\Services\Scrapers\KalibrrScraper())->fetchJobs($searchQuery, $perSourceLimit),
        ];

        // Fetch from all sources in parallel (using array_map for better performance)
        $totalFetched = 0;
        $sourceResults = [];
        $allJobs = [];

        foreach ($sources as $sourceName => $fetchFunction) {
            try {
                $startTime = microtime(true);
                $sourceJobs = $fetchFunction();
                $duration = round((microtime(true) - $startTime) * 1000, 2);

                if (!empty($sourceJobs)) {
                    // Ensure all jobs have source field
                    foreach ($sourceJobs as &$job) {
                        if (!isset($job['source'])) {
                            $job['source'] = $sourceName;
                        }
                    }
                    unset($job);

                    Log::info("✅ Fetched {$sourceName} jobs", [
                        'count' => count($sourceJobs),
                        'duration_ms' => $duration,
                        'query' => $searchQuery,
                        'sample_titles' => array_slice(array_column($sourceJobs, 'title'), 0, 3)
                    ]);
                    $allJobs = array_merge($allJobs, $sourceJobs);
                    $totalFetched += count($sourceJobs);
                    $sourceResults[$sourceName] = ['success' => true, 'count' => count($sourceJobs)];
                } else {
                    Log::warning("⚠️ No jobs found from {$sourceName}", [
                        'query' => $searchQuery,
                        'duration_ms' => $duration
                    ]);
                    $sourceResults[$sourceName] = ['success' => false, 'reason' => 'No jobs returned'];
                }
            } catch (\Exception $e) {
                Log::error("❌ Failed to fetch from {$sourceName}", [
                    'error' => $e->getMessage(),
                    'trace' => substr($e->getTraceAsString(), 0, 500),
                    'query' => $searchQuery
                ]);
                $sourceResults[$sourceName] = ['success' => false, 'error' => $e->getMessage()];
            }
        }

        // Merge all jobs
        $jobs = array_merge($jobs, $allJobs);

        Log::info("Job fetching summary", [
            'total_fetched' => $totalFetched,
            'unique_sources' => array_unique(array_column($jobs, 'source')),
            'sources' => $sourceResults,
            'query' => $searchQuery
        ]);

        Log::info("Total jobs fetched from all sources", ['count' => count($jobs)]);

        // Store count before filtering for fallback
        $jobsBeforeFilter = count($jobs);

        // Remove duplicates based on title and company (but be less aggressive)
        $jobs = $this->removeDuplicates($jobs);
        Log::info("Jobs after removing duplicates", ['count' => count($jobs)]);

        // If we have very few jobs, try to get more by expanding search
        if (count($jobs) < $limit && count($jobs) > 0) {
            Log::info("Low job count, attempting to fetch more with expanded search", [
                'current_count' => count($jobs),
                'target_limit' => $limit
            ]);

            // Try fetching with just the recommended field (broader search)
            $broaderQuery = $recommendedField;
            foreach (['Indeed', 'Kalibrr', 'OnlineJobs.ph', 'Bossjob'] as $sourceName) {
                try {
                    $additionalJobs = match ($sourceName) {
                        'Indeed' => (new \App\Services\Scrapers\IndeedScraper())->fetchJobs($broaderQuery, 10),
                        'Kalibrr' => (new \App\Services\Scrapers\KalibrrScraper())->fetchJobs($broaderQuery, 10),
                        'OnlineJobs.ph' => (new \App\Services\Scrapers\OnlineJobsScraper())->fetchJobs($broaderQuery, 10),
                        'Bossjob' => $this->fetchFromBossjob($broaderQuery, 10),
                        default => [],
                    };

                    if (!empty($additionalJobs)) {
                        $jobs = array_merge($jobs, $additionalJobs);
                        Log::info("Added jobs from {$sourceName} with broader search", ['count' => count($additionalJobs)]);
                    }
                } catch (\Exception $e) {
                    // Continue with other sources
                }
            }

            // Remove duplicates again
            $jobs = $this->removeDuplicates($jobs);
            Log::info("Jobs after broader search and deduplication", ['count' => count($jobs)]);
        }

        // If no jobs found, check if we had jobs before filtering
        // If we had jobs but they were all filtered, try to be less strict
        if (empty($jobs)) {
            Log::warning("No jobs found from external sources after all processing", [
                'recommended_field' => $recommendedField,
                'skills_count' => count($skills),
                'total_fetched_before_filter' => $totalFetched ?? 0,
                'jobs_before_duplicate_removal' => $jobsBeforeFilter ?? 0
            ]);

            // No jobs fetched - return empty array (no sample jobs)
            Log::info("No jobs fetched from any source, returning empty array", [
                'recommended_field' => $recommendedField
            ]);

            // Return empty array so user sees blank state instead of sample jobs
            $jobs = [];
        }

        // OPTIMIZED: Use fast keyword matching only (skip slow AI matching for better performance)
        if (!empty($jobs)) {
            $matchedJobs = $this->matchJobsToResume($jobs, $skills, $recommendedField);

            Log::info("Job matching completed", [
                'total_jobs' => count($jobs),
                'matched_jobs' => count($matchedJobs),
                'recommended_field' => $recommendedField,
            ]);

            // Filter jobs: Prioritize jobs matching the recommended field
            $jobsBeforeFilter = count($matchedJobs);

            // Separate jobs into field-matched and non-field-matched
            $fieldMatchedJobs = [];
            $otherJobs = [];

            foreach ($matchedJobs as $job) {
                $score = $job['match_score'] ?? $job['match_percentage'] ?? 0;
                $jobText = strtolower(($job['title'] ?? '') . ' ' . ($job['description'] ?? ''));
                $fieldLower = strtolower($recommendedField);
                $fieldKeywords = $this->getFieldKeywords($recommendedField);

                // Check if job matches the recommended field
                $matchesField = false;
                if (stripos($jobText, $fieldLower) !== false) {
                    $matchesField = true;
                } else {
                    // Check field keywords
                    foreach ($fieldKeywords as $keyword) {
                        if (stripos($jobText, $keyword) !== false) {
                            $matchesField = true;
                            break;
                        }
                    }
                }

                // Only include jobs with minimum score
                if ($score >= 30) {
                    if ($matchesField) {
                        $fieldMatchedJobs[] = $job;
                    } else {
                        // Only include non-field jobs if they have high skill match (60%+)
                        if ($score >= 60) {
                            $otherJobs[] = $job;
                        }
                    }
                }
            }

            // Prioritize field-matched jobs, then add high-scoring others
            $matchedJobs = array_merge($fieldMatchedJobs, $otherJobs);

            Log::info("Jobs after filtering (field-focused)", [
                'remaining_jobs' => count($matchedJobs),
                'field_matched' => count($fieldMatchedJobs),
                'other_high_score' => count($otherJobs),
                'jobs_before_filter' => $jobsBeforeFilter,
                'filtered_out' => $jobsBeforeFilter - count($matchedJobs),
                'recommended_field' => $recommendedField
            ]);
        } else {
            $matchedJobs = [];
        }

        // Sort by match score (highest first) - most relevant to AI analysis first
        usort($matchedJobs, function ($a, $b) {
            $scoreA = $a['match_score'] ?? $a['match_percentage'] ?? 0;
            $scoreB = $b['match_score'] ?? $b['match_percentage'] ?? 0;
            return $scoreB <=> $scoreA;
        });

        // If no real jobs found after matching, check if we actually fetched jobs
        if (empty($matchedJobs)) {
            // If we actually fetched jobs but they were filtered, return them anyway with lower scores
            if (!empty($jobs) && count($jobs) > 0) {
                Log::warning("All jobs filtered out, but we have fetched jobs. Returning top jobs anyway.", [
                    'recommended_field' => $recommendedField,
                    'total_fetched' => count($jobs),
                    'skills' => array_slice($skills, 0, 5)
                ]);

                // Return the top fetched jobs even if they didn't match well
                // Add basic scores based on keyword matching
                $fallbackJobs = [];
                foreach (array_slice($jobs, 0, $limit * 2) as $job) {
                    $score = $this->calculateFallbackMatchScore($job, $skills, $recommendedField);
                    $fallbackJobs[] = array_merge($job, [
                        'match_score' => max($score, 50), // Minimum 50% for fetched jobs
                        'match_percentage' => max($score, 50),
                    ]);
                }

                // Sort by score
                usort($fallbackJobs, function ($a, $b) {
                    return ($b['match_score'] ?? 0) <=> ($a['match_score'] ?? 0);
                });

                return array_slice($fallbackJobs, 0, $limit * 2);
            }

            // No real jobs found - return empty array (no sample jobs)
            Log::info("No real jobs found after matching, returning empty array", [
                'recommended_field' => $recommendedField,
                'total_fetched' => count($jobs)
            ]);

            // Return empty array so user sees blank state instead of sample jobs
            return [];
        }

        // Return more jobs than requested to ensure variety
        $returnLimit = min($limit * 2, 100, count($matchedJobs));

        Log::info("Returning AI-matched jobs", [
            'requested' => $limit,
            'returning' => $returnLimit,
            'available' => count($matchedJobs),
            'recommended_field' => $recommendedField,
            'top_score' => !empty($matchedJobs) ? ($matchedJobs[0]['match_score'] ?? 0) : 0,
            'min_score' => !empty($matchedJobs) ? min(array_column($matchedJobs, 'match_score')) : 0
        ]);

        return array_slice($matchedJobs, 0, $returnLimit);
    }

    /**
     * Build search query from user's AI analysis
     * Keeps query SHORT and focused so all job sites can return results
     */
    protected function buildSearchQuery(string $field, array $skills): string
    {
        // Map recommended field to a clean, short job title search term
        $fieldSearchTerms = [
            'Software Engineering' => 'Software Engineer',
            'Web Development' => 'Web Developer',
            'Data Science' => 'Data Scientist',
            'Android Development' => 'Android Developer',
            'IOS Development' => 'iOS Developer',
            'UI-UX Development' => 'UI UX Designer',
            'Machine Learning' => 'Machine Learning Engineer',
            'Cyber Security' => 'Cybersecurity Analyst',
            'Database Administration' => 'Database Administrator',
            'Network Engineering' => 'Network Engineer',
            'DevOps' => 'DevOps Engineer',
            'Cloud Computing' => 'Cloud Engineer',
            'Game Development' => 'Game Developer',
            'Embedded Systems' => 'Embedded Systems Engineer',
            'Business Administration' => 'Business Operations',
            'Accounting and Finance' => 'Finance Accounting',
            'Marketing' => 'Marketing',
            'Engineering' => 'Engineer',
            'Education' => 'Education Teacher',
            'Healthcare / Nursing' => 'Healthcare Medical',
            'Hospitality and Tourism' => 'Hospitality',
            'Architecture' => 'Architecture',
            'Psychology' => 'Psychology HR',
            'Agriculture' => 'Agriculture',
            'Arts and Multimedia' => 'Arts Design',
            'Communications' => 'Communications',
            'Logistics and Supply Chain' => 'Logistics Supply Chain',
        ];

        // Use the mapped term or fall back to the raw field
        $searchTerm = $fieldSearchTerms[$field] ?? $field;

        // Optionally append the single most relevant skill if it's short
        if (!empty($skills)) {
            $topSkill = $skills[0];
            // Only append if the skill is a concise technology name (not a phrase)
            if (strlen($topSkill) <= 15 && str_word_count($topSkill) <= 2) {
                $searchTerm .= ' ' . $topSkill;
            }
        }

        // Keep the query under 60 chars so job sites don't choke
        if (strlen($searchTerm) > 60) {
            $searchTerm = substr($searchTerm, 0, 60);
            $searchTerm = substr($searchTerm, 0, strrpos($searchTerm, ' '));
        }

        Log::info("Built search query from AI analysis", [
            'field' => $field,
            'skills_count' => count($skills),
            'query' => $searchTerm,
        ]);

        return trim($searchTerm);
    }



    /**
     * Fetch jobs from Bossjob
     */
    protected function fetchFromBossjob(string $query, int $limit): array
    {
        try {
            $url = "https://www.bossjob.ph/jobs?q=" . urlencode($query);

            Log::info('Fetching jobs from Bossjob', ['url' => $url]);

            $httpOptions = [
                'verify' => config('app.env') === 'production', // Only verify SSL in production
            ];

            $response = Http::timeout(8) // Reduced timeout from 15s to 8s for faster response
                ->withOptions($httpOptions)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.9',
                ])
                ->get($url);

            if ($response->successful()) {
                $html = $response->body();
                $jobs = [];

                // Try multiple patterns to find job data
                // Pattern 1: JSON data in page
                if (preg_match('/"jobs":\s*(\[.+?\])/s', $html, $matches)) {
                    $jobData = json_decode($matches[1], true);
                    if (is_array($jobData)) {
                        foreach (array_slice($jobData, 0, $limit) as $job) {
                            $title = trim($job['title'] ?? $job['jobTitle'] ?? '');
                            if (empty($title))
                                continue;

                            $jobs[] = [
                                'title' => $title,
                                'company' => trim($job['company']['name'] ?? $job['companyName'] ?? 'Company Not Specified'),
                                'location' => $this->extractLocation($job['location'] ?? $job['city'] ?? 'Philippines'),
                                'description' => strip_tags($job['description'] ?? ''),
                                'url' => $job['url'] ?? $job['jobUrl'] ?? '#',
                                'source' => 'Bossjob',
                                'published_at' => isset($job['postedDate']) ? date('Y-m-d H:i:s', strtotime($job['postedDate'])) : now(),
                            ];
                        }
                    }
                }

                // Pattern 2: HTML parsing fallback
                if (empty($jobs) && preg_match_all('/<div[^>]*class="[^"]*job[^"]*card[^"]*"[^>]*>(.*?)<\/div>/is', $html, $cardMatches)) {
                    foreach (array_slice($cardMatches[0], 0, $limit) as $cardHtml) {
                        if (
                            preg_match('/<h[23][^>]*>([^<]+)<\/h[23]>/i', $cardHtml, $titleMatch) ||
                            preg_match('/<a[^>]*class="[^"]*title[^"]*"[^>]*>([^<]+)<\/a>/i', $cardHtml, $titleMatch)
                        ) {
                            $title = trim(strip_tags($titleMatch[1]));
                            if (strlen($title) < 5)
                                continue;

                            preg_match('/<div[^>]*class="[^"]*company[^"]*"[^>]*>([^<]+)<\/div>/i', $cardHtml, $companyMatch);
                            preg_match('/<a[^>]*href="([^"]+)"[^>]*>/i', $cardHtml, $urlMatch);

                            $jobs[] = [
                                'title' => $title,
                                'company' => isset($companyMatch[1]) ? trim(strip_tags($companyMatch[1])) : 'Company Not Specified',
                                'location' => 'Philippines',
                                'description' => substr(strip_tags($cardHtml), 0, 200),
                                'url' => isset($urlMatch[1]) ? (strpos($urlMatch[1], 'http') === 0 ? $urlMatch[1] : 'https://www.bossjob.ph' . $urlMatch[1]) : '#',
                                'source' => 'Bossjob',
                                'published_at' => now(),
                            ];

                            if (count($jobs) >= $limit)
                                break;
                        }
                    }
                }

                Log::info('Bossjob parsing result', ['jobs_found' => count($jobs)]);
                return $jobs;
            } else {
                Log::warning('Indeed RSS feed request failed', [
                    'url' => $url,
                    'status' => $response->status(),
                    'error' => $response->body()
                ]);
            }

            return [];

        } catch (\Exception $e) {
            Log::error('Error fetching from Bossjob', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Fetch jobs from WorkAbroad.ph
     */
    protected function fetchFromWorkAbroad(string $query, int $limit): array
    {
        try {
            $url = "https://www.workabroad.ph/search?q=" . urlencode($query);

            Log::info('Fetching jobs from WorkAbroad.ph', ['url' => $url]);

            $httpOptions = [
                'verify' => config('app.env') === 'production', // Only verify SSL in production
            ];

            $response = Http::timeout(8) // Reduced timeout from 15s to 8s for faster response
                ->withOptions($httpOptions)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.9',
                ])
                ->get($url);

            if ($response->successful()) {
                $html = $response->body();
                $jobs = [];

                // Try multiple patterns to parse job listings
                // Pattern 1: Job item divs
                if (preg_match_all('/<div[^>]*class="[^"]*job[^"]*item[^"]*"[^>]*>(.*?)<\/div>/is', $html, $matches)) {
                    foreach (array_slice($matches[0], 0, $limit) as $jobHtml) {
                        preg_match('/<a[^>]*href="([^"]*)"[^>]*>([^<]*)<\/a>/i', $jobHtml, $titleMatch);
                        preg_match('/<span[^>]*class="[^"]*company[^"]*"[^>]*>([^<]*)<\/span>/i', $jobHtml, $companyMatch);

                        if (isset($titleMatch[2])) {
                            $title = trim(strip_tags($titleMatch[2]));
                            if (strlen($title) < 5)
                                continue;

                            $jobs[] = [
                                'title' => $title,
                                'company' => isset($companyMatch[1]) ? trim(strip_tags($companyMatch[1])) : 'Company Not Specified',
                                'location' => 'Philippines / Overseas',
                                'description' => substr(strip_tags($jobHtml), 0, 200),
                                'url' => isset($titleMatch[1]) ? (strpos($titleMatch[1], 'http') === 0 ? $titleMatch[1] : 'https://www.workabroad.ph' . $titleMatch[1]) : '#',
                                'source' => 'WorkAbroad.ph',
                                'published_at' => now(),
                            ];
                        }
                    }
                }

                // Pattern 2: Article or list item fallback
                if (empty($jobs) && preg_match_all('/<(?:article|li)[^>]*class="[^"]*job[^"]*"[^>]*>(.*?)<\/(?:article|li)>/is', $html, $articleMatches)) {
                    foreach (array_slice($articleMatches[0], 0, $limit) as $articleHtml) {
                        if (
                            preg_match('/<h[23][^>]*>([^<]+)<\/h[23]>/i', $articleHtml, $titleMatch) ||
                            preg_match('/<a[^>]*class="[^"]*title[^"]*"[^>]*>([^<]+)<\/a>/i', $articleHtml, $titleMatch)
                        ) {
                            $title = trim(strip_tags($titleMatch[1]));
                            if (strlen($title) < 5)
                                continue;

                            preg_match('/<div[^>]*class="[^"]*company[^"]*"[^>]*>([^<]+)<\/div>/i', $articleHtml, $companyMatch);
                            preg_match('/<a[^>]*href="([^"]+)"[^>]*>/i', $articleHtml, $urlMatch);

                            $jobs[] = [
                                'title' => $title,
                                'company' => isset($companyMatch[1]) ? trim(strip_tags($companyMatch[1])) : 'Company Not Specified',
                                'location' => 'Philippines / Overseas',
                                'description' => substr(strip_tags($articleHtml), 0, 200),
                                'url' => isset($urlMatch[1]) ? (strpos($urlMatch[1], 'http') === 0 ? $urlMatch[1] : 'https://www.workabroad.ph' . $urlMatch[1]) : '#',
                                'source' => 'WorkAbroad.ph',
                                'published_at' => now(),
                            ];

                            if (count($jobs) >= $limit)
                                break;
                        }
                    }
                }

                Log::info('WorkAbroad.ph parsing result', ['jobs_found' => count($jobs)]);
                return $jobs;
            } else {
                Log::warning('Indeed RSS feed request failed', [
                    'url' => $url,
                    'status' => $response->status(),
                    'error' => $response->body()
                ]);
            }

            return [];

        } catch (\Exception $e) {
            Log::error('Error fetching from WorkAbroad.ph', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Fetch jobs from JobsDB Philippines
     */
    protected function fetchFromJobsDB(string $query, int $limit): array
    {
        try {
            $url = "https://www.jobsdb.com.ph/en/jobs?q=" . urlencode($query) . "&location=Philippines";

            Log::info('Fetching jobs from JobsDB', ['url' => $url]);

            $httpOptions = [
                'verify' => config('app.env') === 'production', // Only verify SSL in production
            ];

            $response = Http::timeout(8) // Reduced timeout from 15s to 8s for faster response
                ->withOptions($httpOptions)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.9',
                ])
                ->get($url);

            if ($response->successful()) {
                $html = $response->body();
                $jobs = [];

                // Try to extract JSON-LD structured data
                if (preg_match_all('/<script[^>]*type="application\/ld\+json"[^>]*>(.*?)<\/script>/is', $html, $jsonMatches)) {
                    foreach ($jsonMatches[1] as $jsonStr) {
                        $data = json_decode($jsonStr, true);
                        if (isset($data['@type']) && $data['@type'] === 'JobPosting') {
                            $title = $data['title'] ?? 'Job Title';
                            $location = $data['jobLocation']['address']['addressLocality'] ?? 'Philippines';

                            // Clean title
                            $title = trim(strip_tags($title));
                            $title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');

                            $jobs[] = [
                                'title' => $title,
                                'company' => $data['hiringOrganization']['name'] ?? 'Company Not Specified',
                                'location' => $this->extractLocation($location),
                                'description' => strip_tags($data['description'] ?? ''),
                                'url' => $data['url'] ?? '#',
                                'source' => 'JobsDB',
                                'published_at' => isset($data['datePosted']) ? date('Y-m-d H:i:s', strtotime($data['datePosted'])) : now(),
                            ];

                            if (count($jobs) >= $limit)
                                break;
                        }
                    }
                }

                return $jobs;
            } else {
                Log::warning('Indeed RSS feed request failed', [
                    'url' => $url,
                    'status' => $response->status(),
                    'error' => $response->body()
                ]);
            }

            return [];

        } catch (\Exception $e) {
            Log::error('Error fetching from JobsDB', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Fetch jobs from LinkedIn (Philippines filter)
     */
    protected function fetchFromLinkedIn(string $query, int $limit): array
    {
        try {
            $config = Config::get('job_apis.linkedin');

            // Try API first if configured
            if ($config['enabled'] && !empty($config['access_token'])) {
                return $this->fetchFromLinkedInAPI($query, $limit);
            }

            // Fallback to ScraperAPI if enabled
            $scraperConfig = Config::get('job_apis.scraperapi');
            if ($scraperConfig['enabled'] && in_array('linkedin', $scraperConfig['use_for'])) {
                return $this->fetchFromLinkedInViaScraperAPI($query, $limit);
            }

            // Last resort: return empty (LinkedIn requires authentication)
            Log::info('LinkedIn: No API configured, skipping');
            return [];

        } catch (\Exception $e) {
            Log::error('Error fetching from LinkedIn', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Fetch jobs from JobStreet Philippines (enhanced)
     */
    protected function fetchFromJobStreet(string $query, int $limit): array
    {
        try {
            // JobStreet search URL
            $url = "https://www.jobstreet.com.ph/en/job-search/job-vacancy.php?ojs=3&key=" . urlencode($query);

            Log::info('Fetching jobs from JobStreet', ['url' => $url]);

            $httpOptions = [
                'verify' => config('app.env') === 'production', // Only verify SSL in production
            ];

            $response = Http::timeout(8) // Reduced timeout from 15s to 8s for faster response
                ->withOptions($httpOptions)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.9',
                ])
                ->get($url);

            if ($response->successful()) {
                $html = $response->body();
                $jobs = [];

                // Try multiple patterns to extract job data
                // Pattern 1: JSON-LD structured data
                if (preg_match_all('/<script[^>]*type="application\/ld\+json"[^>]*>(.*?)<\/script>/is', $html, $jsonMatches)) {
                    foreach ($jsonMatches[1] as $jsonStr) {
                        $data = json_decode($jsonStr, true);
                        if (isset($data['@type']) && ($data['@type'] === 'JobPosting' || (is_array($data) && isset($data[0]['@type']) && $data[0]['@type'] === 'JobPosting'))) {
                            $jobData = is_array($data) && isset($data[0]) ? $data[0] : $data;
                            $title = trim(strip_tags($jobData['title'] ?? ''));
                            if (empty($title))
                                continue;

                            $jobs[] = [
                                'title' => $title,
                                'company' => $jobData['hiringOrganization']['name'] ?? 'Company Not Specified',
                                'location' => $this->extractLocation($jobData['jobLocation']['address']['addressLocality'] ?? 'Philippines'),
                                'description' => strip_tags($jobData['description'] ?? ''),
                                'url' => $jobData['url'] ?? '#',
                                'source' => 'JobStreet',
                                'published_at' => isset($jobData['datePosted']) ? date('Y-m-d H:i:s', strtotime($jobData['datePosted'])) : now(),
                            ];

                            if (count($jobs) >= $limit)
                                break;
                        }
                    }
                }

                // Pattern 2: Embedded JSON data
                if (empty($jobs) && preg_match('/"jobResults":\s*(\[.+?\])/s', $html, $matches)) {
                    $jobData = json_decode($matches[1], true);
                    if (is_array($jobData)) {
                        foreach (array_slice($jobData, 0, $limit) as $job) {
                            $title = trim($job['jobTitle'] ?? $job['title'] ?? '');
                            if (empty($title))
                                continue;

                            $jobs[] = [
                                'title' => $title,
                                'company' => trim($job['company']['name'] ?? $job['companyName'] ?? 'Company Not Specified'),
                                'location' => $this->extractLocation($job['location'] ?? $job['locationName'] ?? 'Philippines'),
                                'description' => strip_tags($job['description'] ?? ''),
                                'url' => $job['jobUrl'] ?? $job['url'] ?? '#',
                                'source' => 'JobStreet',
                                'published_at' => isset($job['postedDate']) ? date('Y-m-d H:i:s', strtotime($job['postedDate'])) : now(),
                            ];
                        }
                    }
                }

                // Pattern 3: HTML parsing fallback
                if (empty($jobs) && preg_match_all('/<article[^>]*class="[^"]*job[^"]*"[^>]*>(.*?)<\/article>/is', $html, $articleMatches)) {
                    foreach (array_slice($articleMatches[0], 0, $limit) as $articleHtml) {
                        if (preg_match('/<h[23][^>]*>([^<]+)<\/h[23]>/i', $articleHtml, $titleMatch)) {
                            $title = trim(strip_tags($titleMatch[1]));
                            if (strlen($title) < 5)
                                continue;

                            preg_match('/<span[^>]*class="[^"]*company[^"]*"[^>]*>([^<]+)<\/span>/i', $articleHtml, $companyMatch);
                            preg_match('/<a[^>]*href="([^"]+)"[^>]*>/i', $articleHtml, $urlMatch);

                            $jobs[] = [
                                'title' => $title,
                                'company' => isset($companyMatch[1]) ? trim(strip_tags($companyMatch[1])) : 'Company Not Specified',
                                'location' => 'Philippines',
                                'description' => substr(strip_tags($articleHtml), 0, 200),
                                'url' => isset($urlMatch[1]) ? (strpos($urlMatch[1], 'http') === 0 ? $urlMatch[1] : 'https://www.jobstreet.com.ph' . $urlMatch[1]) : '#',
                                'source' => 'JobStreet',
                                'published_at' => now(),
                            ];

                            if (count($jobs) >= $limit)
                                break;
                        }
                    }
                }

                Log::info('JobStreet parsing result', ['jobs_found' => count($jobs)]);
                return $jobs;
            } else {
                Log::warning('Indeed RSS feed request failed', [
                    'url' => $url,
                    'status' => $response->status(),
                    'error' => $response->body()
                ]);
            }

            return [];

        } catch (\Exception $e) {
            Log::error('Error fetching from JobStreet', ['error' => $e->getMessage()]);
            return [];
        }
    }



    /**
     * Extract location from text (Philippine cities)
     */
    protected function extractLocation(string $text): string
    {
        $philippineCities = [
            'Manila',
            'Makati',
            'Quezon City',
            'Pasig',
            'Taguig',
            'BGC',
            'Ortigas',
            'Mandaluyong',
            'San Juan',
            'Parañaque',
            'Las Piñas',
            'Muntinlupa',
            'Cebu City',
            'Davao City',
            'Iloilo City',
            'Bacolod',
            'Cagayan de Oro',
            'Zamboanga City',
            'Dagupan',
            'Baguio',
            'Angeles City',
            'Batangas City',
            'Lipa',
            'Calamba',
            'Santa Rosa',
            'Antipolo',
            'Marikina',
            'Valenzuela',
            'Caloocan',
            'Malabon',
            'Navotas',
            'Muntinlupa',
            'Las Piñas',
            'Parañaque',
            'Taguig',
            'Pateros',
            'Pasay',
            'Mandaluyong',
            'San Juan',
            'Makati',
            'Metro Manila',
            'NCR',
            'MM',
            'National Capital Region'
        ];

        $text = ' ' . $text . ' ';

        foreach ($philippineCities as $city) {
            if (stripos($text, $city) !== false) {
                // Return the most specific match
                if (stripos($city, 'City') !== false || stripos($city, 'Metro') !== false) {
                    return $city;
                }
            }
        }

        // Check for Metro Manila variations
        if (stripos($text, 'metro manila') !== false || stripos($text, 'ncr') !== false) {
            return 'Metro Manila';
        }

        // Check for Philippines
        if (stripos($text, 'philippines') !== false || stripos($text, 'ph') !== false) {
            return 'Philippines';
        }

        return 'Philippines'; // Default
    }

    /**
     * Match jobs to user's resume using AI semantic similarity (batch mode)
     * Falls back to keyword matching if Flask API is unavailable
     */
    protected function matchJobsToResume(array $jobs, array $userSkills, string $recommendedField): array
    {
        $totalJobs = count($jobs);

        if ($totalJobs === 0) {
            return [];
        }

        // Get user's full resume context from AI analysis
        $aiAnalysis = $this->user->ai_analysis ?? [];
        $experience = $aiAnalysis['experience'] ?? [];
        $education = $aiAnalysis['education'] ?? [];

        $resumeContext = [
            'skills' => $userSkills,
            'experience' => $experience,
            'education' => $education,
            'recommended_field' => $recommendedField,
        ];

        // --- TRY BATCH AI MATCHING FIRST ---
        $aiService = new \App\Services\ResumeAIService();
        $aiResults = $aiService->batchMatchJobs($resumeContext, $jobs);

        if ($aiResults !== null && count($aiResults) === $totalJobs) {
            Log::info('Using batch AI semantic matching', [
                'total_jobs' => $totalJobs,
                'matching_method' => 'AI semantic (batch)',
            ]);

            $matchedJobs = [];
            foreach ($jobs as $i => $job) {
                $aiScore = $aiResults[$i]['match_score'] ?? 0;

                // Ensure minimum 30% for any fetched job so they still show up
                $finalScore = max($aiScore, 30);

                // Location bonus for Philippines
                $location = strtolower($job['location'] ?? '');
                if (
                    stripos($location, 'philippines') !== false ||
                    stripos($location, 'manila') !== false ||
                    stripos($location, 'makati') !== false ||
                    stripos($location, 'quezon') !== false ||
                    stripos($location, 'pasig') !== false ||
                    stripos($location, 'taguig') !== false
                ) {
                    $finalScore = min(100, $finalScore + 5);
                }

                $matchedJobs[] = array_merge($job, [
                    'match_score' => $finalScore,
                    'match_percentage' => $finalScore,
                    'match_method' => 'ai_semantic',
                ]);
            }

            Log::info('Batch AI matching completed', [
                'total_jobs' => $totalJobs,
                'avg_score' => round(array_sum(array_column($matchedJobs, 'match_score')) / count($matchedJobs), 2),
            ]);

            return $matchedJobs;
        }

        // --- FALLBACK: Fast keyword matching ---
        Log::info('Falling back to keyword-based job matching', [
            'total_jobs' => $totalJobs,
            'reason' => $aiResults === null ? 'AI API unavailable' : 'result count mismatch',
            'user_skills_count' => count($userSkills),
            'recommended_field' => $recommendedField,
        ]);

        $matchedJobs = [];
        foreach ($jobs as $job) {
            $matchScore = $this->calculateFallbackMatchScore($job, $userSkills, $recommendedField);
            $finalScore = max($matchScore, 50);

            $location = strtolower($job['location'] ?? '');
            $locationBonus = 0;
            if (
                stripos($location, 'philippines') !== false ||
                stripos($location, 'manila') !== false ||
                stripos($location, 'makati') !== false ||
                stripos($location, 'quezon') !== false ||
                stripos($location, 'pasig') !== false ||
                stripos($location, 'taguig') !== false
            ) {
                $locationBonus = 5;
            }

            $finalScoreWithBonus = min(100, $finalScore + $locationBonus);

            $matchedJobs[] = array_merge($job, [
                'match_score' => $finalScoreWithBonus,
                'match_percentage' => $finalScoreWithBonus,
                'match_method' => 'keyword_fallback',
            ]);
        }

        Log::info('Keyword matching completed', [
            'total_jobs' => $totalJobs,
            'avg_score' => !empty($matchedJobs) ? round(array_sum(array_column($matchedJobs, 'match_score')) / count($matchedJobs), 2) : 0,
        ]);

        return $matchedJobs;
    }

    /**
     * Calculate AI match score using Flask API semantic similarity
     * Sends full resume context for accurate AI matching
     */
    protected function calculateAIMatchScore(
        string $apiUrl,
        array $skills,
        array $experience,
        array $education,
        string $recommendedField,
        string $jobTitle,
        string $jobDescription
    ): int {
        try {
            // Get full AI analysis context
            $aiAnalysis = $this->user->ai_analysis ?? [];

            // Prepare comprehensive resume context for AI
            // Include ALL AI analysis data for maximum accuracy
            $resumeContext = [
                'skills' => $skills,
                'experience' => $experience,
                'education' => $education,
                'recommended_field' => $recommendedField,
                'summary' => $aiAnalysis['summary'] ?? '',
                'resume_score' => $this->user->resume_score ?? 0,
                'recommended_skills' => $aiAnalysis['recommended_skills'] ?? [],
                // Include additional AI insights if available
                'certifications' => $aiAnalysis['certifications'] ?? [],
                'languages' => $aiAnalysis['languages'] ?? [],
                'projects' => $aiAnalysis['projects'] ?? [],
                'achievements' => $aiAnalysis['achievements'] ?? [],
            ];

            $response = Http::timeout(8) // Reduced timeout from 15s to 8s for faster response // Increased timeout for AI processing
                ->post("{$apiUrl}/match", [
                    'resume_context' => $resumeContext,
                    'job_title' => $jobTitle,
                    'job_description' => substr($jobDescription, 0, 2000),
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $matchScore = $data['match_score'] ?? 0;

                // Ensure score is 0-100
                $finalScore = max(0, min(100, (int) $matchScore));

                Log::debug("AI match calculated", [
                    'job_title' => substr($jobTitle, 0, 50),
                    'score' => $finalScore
                ]);

                return $finalScore;
            } else {
                Log::warning("AI match API error", [
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 200)
                ]);
                throw new \Exception("AI API returned status " . $response->status());
            }
        } catch (\Exception $e) {
            Log::error("AI match failed", [
                'error' => $e->getMessage(),
                'job_title' => substr($jobTitle, 0, 50)
            ]);
            throw $e;
        }
    }

    /**
     * Fallback keyword-based matching (used when AI fails)
     * Improved to better match AI analysis
     */
    protected function calculateFallbackMatchScore(array $job, array $userSkills, string $recommendedField): int
    {
        $score = 0;

        // Normalize for comparison
        $userSkillsLower = array_map('strtolower', $userSkills);
        $jobTitle = strtolower($job['title'] ?? '');
        $jobDesc = strtolower($job['description'] ?? '');
        $jobText = $jobTitle . ' ' . $jobDesc;

        // Field-specific keywords mapping for better matching
        $fieldKeywords = $this->getFieldKeywords($recommendedField);

        // Recommended field match (most important - 60 points)
        $fieldMatchScore = 0;
        if (!empty($recommendedField)) {
            $fieldLower = strtolower($recommendedField);

            // Check exact field name match
            if (stripos($jobTitle, $fieldLower) !== false) {
                $fieldMatchScore = 60; // Field in title - highest priority
            } elseif (stripos($jobText, $fieldLower) !== false) {
                $fieldMatchScore = 40; // Field in description
            }

            // Check field-specific keywords (more flexible matching)
            if ($fieldMatchScore === 0 && !empty($fieldKeywords)) {
                $keywordMatches = 0;
                foreach ($fieldKeywords as $keyword) {
                    if (stripos($jobTitle, $keyword) !== false) {
                        $keywordMatches += 2; // Keywords in title are more important
                    } elseif (stripos($jobText, $keyword) !== false) {
                        $keywordMatches += 1; // Keywords in description
                    }
                }

                if ($keywordMatches >= 3) {
                    $fieldMatchScore = 50; // Strong keyword match
                } elseif ($keywordMatches >= 2) {
                    $fieldMatchScore = 35; // Moderate keyword match
                } elseif ($keywordMatches >= 1) {
                    $fieldMatchScore = 20; // Weak keyword match
                }
            }
        }

        $score += $fieldMatchScore;

        // Skill matches (15 points per skill in title, 10 in description)
        $matchingSkills = 0;
        foreach ($userSkillsLower as $skill) {
            if (!empty($skill) && strlen($skill) > 2) {
                if (stripos($jobTitle, $skill) !== false) {
                    $matchingSkills++;
                    $score += 15;
                } elseif (stripos($jobText, $skill) !== false) {
                    $matchingSkills++;
                    $score += 10;
                }
            }
        }

        // Bonus for multiple skill matches
        if ($matchingSkills >= 3) {
            $score += 25;
        } elseif ($matchingSkills >= 2) {
            $score += 15;
        }

        // Penalty if field doesn't match at all (reduce score significantly)
        if ($fieldMatchScore === 0 && !empty($recommendedField)) {
            $score = max(0, $score - 30); // Reduce score by 30 if no field match
        }

        return min(100, $score);
    }

    /**
     * Get field-specific keywords for better job matching
     */
    protected function getFieldKeywords(string $field): array
    {
        $fieldLower = strtolower($field);

        $keywordsMap = [
            'software engineering' => ['software engineer', 'software developer', 'programmer', 'developer', 'coding', 'programming', 'software', 'application developer', 'systems developer'],
            'data science' => ['data scientist', 'data analyst', 'data engineer', 'machine learning', 'ml engineer', 'ai engineer', 'analytics', 'data mining', 'big data'],
            'web development' => ['web developer', 'frontend', 'backend', 'full stack', 'fullstack', 'web developer', 'php developer', '.net developer', 'node.js', 'react developer', 'vue developer'],
            'ui-ux development' => ['ui designer', 'ux designer', 'ui/ux', 'user interface', 'user experience', 'interface designer', 'ux/ui', 'designer'],
            'android development' => ['android developer', 'android', 'mobile developer', 'kotlin', 'android app', 'mobile app developer'],
            'ios development' => ['ios developer', 'swift', 'ios app', 'apple developer', 'iphone developer'],
            'business administration' => ['business manager', 'operations', 'business analyst', 'administrator', 'manager'],
            'accounting and finance' => ['accountant', 'finance', 'financial analyst', 'bookkeeper', 'audit'],
            'marketing' => ['marketing', 'seo', 'social media', 'digital marketing', 'content creator'],
            'engineering' => ['engineer', 'civil engineer', 'mechanical engineer', 'electrical engineer'],
            'education' => ['teacher', 'instructor', 'educator', 'tutor', 'professor', 'instructional designer'],
            'healthcare / nursing' => ['nurse', 'rn', 'healthcare', 'medical', 'clinic', 'hospital'],
            'hospitality and tourism' => ['hotel', 'hospitality', 'tourism', 'travel', 'resort', 'front desk'],
            'architecture' => ['architect', 'interior designer', 'cad', 'draftsman'],
            'psychology' => ['hr', 'human resources', 'psychologist', 'recruiter', 'counselor'],
            'agriculture' => ['agriculture', 'farm', 'agronomist', 'agriculturist'],
            'arts and multimedia' => ['graphic designer', 'video editor', 'multimedia', 'artist', 'creative'],
            'communications' => ['pr', 'public relations', 'communications', 'media', 'writer'],
            'logistics and supply chain' => ['logistics', 'supply chain', 'warehouse', 'delivery', 'procurement'],
        ];

        // Check for exact match
        if (isset($keywordsMap[$fieldLower])) {
            return $keywordsMap[$fieldLower];
        }

        // Check for partial match
        foreach ($keywordsMap as $key => $keywords) {
            if (stripos($fieldLower, $key) !== false || stripos($key, $fieldLower) !== false) {
                return $keywords;
            }
        }

        // Default: return field name variations
        return [str_replace(' ', '', $fieldLower), $fieldLower];
    }

    /**
     * Remove duplicate jobs - only remove EXACT duplicates (same URL)
     */
    protected function removeDuplicates(array $jobs): array
    {
        $seen = [];
        $unique = [];
        $filtered = [];

        // First, filter out invalid jobs (pagination links, "See More", etc.)
        $invalidPatterns = [
            'see more',
            'view more',
            'load more',
            'next page',
            'show more',
            'more jobs',
            'job opportunity #',  // Generic OnlineJobs.ph fallback titles
            '...',
            '…',
        ];

        foreach ($jobs as $job) {
            $title = trim($job['title'] ?? '');
            $url = trim($job['url'] ?? '');
            $titleLower = strtolower($title);

            // Skip invalid jobs
            $isInvalid = false;

            // Check for pagination/invalid patterns
            foreach ($invalidPatterns as $pattern) {
                if (stripos($titleLower, $pattern) !== false || stripos($url, $pattern) !== false) {
                    $isInvalid = true;
                    Log::debug("Job filtered: invalid pattern", [
                        'title' => substr($title, 0, 50),
                        'pattern' => $pattern
                    ]);
                    break;
                }
            }

            // Only filter if BOTH title is too short AND URL is invalid
            // This is much more lenient - keep almost everything
            if (!$isInvalid) {
                $hasValidTitle = strlen($title) >= 2; // Even more lenient - 2 chars minimum
                $hasValidUrl = !empty($url) && $url !== '#' && stripos($url, 'javascript:') === false;

                // Keep job if it has EITHER valid title OR valid URL
                // OR if title is not empty (even if short)
                if ($hasValidTitle || $hasValidUrl || !empty($title)) {
                    $filtered[] = $job;
                } else {
                    Log::debug("Job filtered: no valid title or URL", [
                        'title' => substr($title, 0, 50),
                        'url' => substr($url, 0, 50),
                        'title_len' => strlen($title)
                    ]);
                }
            }
        }

        // Log sample of filtered jobs for debugging
        $sampleFiltered = array_slice(array_map(function ($j) {
            return [
                'title' => substr($j['title'] ?? '', 0, 50),
                'url' => substr($j['url'] ?? '', 0, 50),
                'title_len' => strlen($j['title'] ?? ''),
                'has_url' => !empty($j['url']) && $j['url'] !== '#'
            ];
        }, $filtered), 0, 5);

        Log::info("Filtered invalid jobs", [
            'before' => count($jobs),
            'after' => count($filtered),
            'removed' => count($jobs) - count($filtered),
            'sample_filtered' => $sampleFiltered
        ]);

        // Now remove duplicates - ONLY by URL (exact same job posting)
        foreach ($filtered as $job) {
            $url = trim($job['url'] ?? '');

            // Only use URL for duplicate detection (most reliable)
            if (!empty($url) && $url !== '#') {
                $urlKey = md5(strtolower($url));

                if (!isset($seen[$urlKey])) {
                    $seen[$urlKey] = true;
                    $unique[] = $job;
                }
            } else {
                // If no URL, use title+company as fallback
                $title = strtolower(trim($job['title'] ?? ''));
                $company = strtolower(trim($job['company'] ?? ''));
                $fallbackKey = md5($title . '|' . $company);

                if (!isset($seen[$fallbackKey])) {
                    $seen[$fallbackKey] = true;
                    $unique[] = $job;
                }
            }
        }

        Log::info("Duplicate removal", [
            'before' => count($filtered),
            'after' => count($unique),
            'removed' => count($filtered) - count($unique)
        ]);

        return $unique;
    }

    /**
     * Get jobs with filters
     */
    public function getJobsWithFilters(array $filters = [], int $limit = 20, bool $forceRefresh = false): array
    {
        $jobs = $this->fetchJobsForUser($limit * 2, $forceRefresh); // Get more to filter

        // Apply search filter
        if (isset($filters['search']) && !empty($filters['search'])) {
            $searchTerm = strtolower(trim($filters['search']));
            $jobs = array_filter($jobs, function ($job) use ($searchTerm) {
                $title = strtolower($job['title'] ?? '');
                $company = strtolower($job['company'] ?? '');
                $description = strtolower($job['description'] ?? '');
                $location = strtolower($job['location'] ?? '');

                return stripos($title, $searchTerm) !== false ||
                    stripos($company, $searchTerm) !== false ||
                    stripos($description, $searchTerm) !== false ||
                    stripos($location, $searchTerm) !== false;
            });
        }

        // Apply location filter
        if (isset($filters['location']) && !empty($filters['location'])) {
            $location = strtolower(trim($filters['location']));
            $jobs = array_filter($jobs, function ($job) use ($location) {
                $jobLocation = strtolower($job['location'] ?? '');
                return stripos($jobLocation, $location) !== false;
            });
        }

        // Apply minimum score filter
        if (isset($filters['min_score']) && !empty($filters['min_score'])) {
            $minScore = (int) $filters['min_score'];
            $jobs = array_filter($jobs, function ($job) use ($minScore) {
                $score = $job['match_score'] ?? $job['match_percentage'] ?? 0;
                return $score >= $minScore;
            });
        }

        // Apply source filter
        if (isset($filters['source']) && !empty($filters['source'])) {
            $source = trim($filters['source']);
            $jobs = array_filter($jobs, function ($job) use ($source) {
                return ($job['source'] ?? '') === $source;
            });
        }

        return array_values($jobs); // Return all filtered jobs (limit applied in controller)
    }



    /**
     * Generate sample jobs based on user's recommended field and skills
     * This is a fallback when external job sources fail
     */
    protected function generateSampleJobs(string $field, array $skills, int $limit): array
    {
        $sampleJobs = [];

        // Job titles based on field
        $jobTitles = [
            'Software Engineer' => [
                'Senior Software Engineer',
                'Full Stack Developer',
                'Backend Developer',
                'Software Developer',
                'Junior Software Engineer',
                'PHP Developer',
                'Laravel Developer',
            ],
            'Data Scientist' => [
                'Data Scientist',
                'Data Analyst',
                'Machine Learning Engineer',
                'Business Intelligence Analyst',
                'Data Engineer',
            ],
            'Web Development' => [
                'Web Developer',
                'Frontend Developer',
                'Full Stack Developer',
                'React Developer',
                'Laravel Developer',
            ],
            'Android Development' => [
                'Android Developer',
                'Mobile App Developer',
                'Flutter Developer',
                'Kotlin Developer',
            ],
            'IOS Development' => [
                'iOS Developer',
                'Mobile App Developer',
                'Swift Developer',
            ],
            'UI-UX Development' => [
                'UI/UX Designer',
                'Product Designer',
                'UX Designer',
                'Graphic Designer',
            ],
        ];

        $titles = $jobTitles[$field] ?? ['Professional', 'Specialist', 'Consultant'];

        // Sample companies in Philippines
        $companies = [
            'Accenture',
            'IBM Philippines',
            'Cognizant',
            'DXC Technology',
            'Pointwest',
            'Trend Micro',
            'Globe Telecom',
            'PLDT',
            'Ayala Corporation',
            'SM Investments',
            'BDO Unibank',
            'Manulife',
            'Shell Philippines',
            'Nestle Philippines',
        ];

        // Sample locations
        $locations = [
            'Makati, Metro Manila',
            'BGC, Taguig',
            'Ortigas, Pasig',
            'Quezon City',
            'Manila',
            'Cebu City',
            'Davao City',
        ];

        // Generate sample jobs
        for ($i = 0; $i < $limit; $i++) {
            $title = $titles[array_rand($titles)];
            $company = $companies[array_rand($companies)];
            $location = $locations[array_rand($locations)];

            // Build description based on skills
            $skillText = !empty($skills) ? implode(', ', array_slice($skills, 0, 5)) : 'various technical skills';
            $description = "We are looking for a {$title} with experience in {$skillText}. This is an exciting opportunity to work with a leading company in the Philippines.";

            $sampleJobs[] = [
                'title' => $title,
                'company' => $company,
                'location' => $location,
                'description' => $description,
                'url' => 'https://www.jobstreet.com.ph',
                'source' => 'Sample Jobs',
                'published_at' => now()->subDays(rand(1, 30))->format('Y-m-d H:i:s'),
            ];
        }

        Log::info("Generated sample jobs", ['count' => count($sampleJobs), 'field' => $field]);

        return $sampleJobs;
    }

    /**
     * Fetch jobs from Adzuna API (aggregates multiple sources)
     */
    protected function fetchFromAdzunaAPI(string $query, int $limit): array
    {
        $config = Config::get('job_apis.adzuna');

        if (!$config['enabled'] || empty($config['app_id']) || empty($config['app_key'])) {
            return [];
        }

        try {
            // Adzuna API endpoint for Philippines
            $url = "https://api.adzuna.com/v1/api/jobs/{$config['country']}/search/1";

            Log::info('Fetching from Adzuna API', [
                'url' => $url,
                'query' => $query,
                'country' => $config['country'],
                'limit' => $limit
            ]);

            $response = Http::timeout(8) // Reduced timeout from 15s to 8s for faster response
                ->withHeaders([
                    'Accept' => 'application/json',
                ])
                ->get($url, [
                    'app_id' => $config['app_id'],
                    'app_key' => $config['app_key'],
                    'what' => $query,
                    'where' => 'Philippines', // Explicitly set Philippines location
                    'results_per_page' => min($limit, 50),
                    'sort_by' => 'date', // Get latest jobs first
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $jobs = [];

                if (isset($data['results']) && is_array($data['results'])) {
                    foreach ($data['results'] as $job) {
                        // Filter for Philippines jobs only
                        $location = $job['location']['display_name'] ?? '';
                        $locationLower = strtolower($location);

                        // Only include jobs in Philippines
                        if (
                            stripos($locationLower, 'philippines') !== false ||
                            stripos($locationLower, 'manila') !== false ||
                            stripos($locationLower, 'makati') !== false ||
                            stripos($locationLower, 'quezon') !== false ||
                            stripos($locationLower, 'cebu') !== false ||
                            stripos($locationLower, 'davao') !== false ||
                            empty($location)
                        ) { // Include if location not specified (likely PH)

                            $jobs[] = [
                                'title' => $job['title'] ?? 'Job Title',
                                'company' => $job['company']['display_name'] ?? 'Company Not Specified',
                                'location' => $this->extractLocation($location ?: 'Philippines'),
                                'description' => strip_tags($job['description'] ?? ''),
                                'url' => $job['redirect_url'] ?? '#',
                                'source' => 'Adzuna',
                                'published_at' => isset($job['created']) ? date('Y-m-d H:i:s', strtotime($job['created'])) : now(),
                            ];
                        }
                    }
                }

                Log::info('Adzuna API fetched jobs', [
                    'count' => count($jobs),
                    'total_results' => count($data['results'] ?? []),
                    'query' => $query
                ]);
                return $jobs;
            } else {
                Log::warning('Adzuna API request failed', [
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 500),
                    'query' => $query
                ]);
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Error fetching from Adzuna API', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Fetch jobs from Jooble API (aggregates multiple sources)
     */
    protected function fetchFromJoobleAPI(string $query, int $limit): array
    {
        $config = Config::get('job_apis.jooble');

        if (!$config['enabled'] || empty($config['api_key'])) {
            return [];
        }

        try {
            $url = "https://jooble.org/api/{$config['api_key']}";

            Log::info('Fetching from Jooble API', [
                'url' => $url,
                'query' => $query,
                'location' => 'Philippines',
                'limit' => $limit
            ]);

            $response = Http::timeout(8) // Reduced timeout from 15s to 8s for faster response
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'keywords' => $query,
                    'location' => 'Philippines', // Explicitly set Philippines
                    'page' => 1,
                    'searchMode' => 1,
                    'radius' => 25,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $jobs = [];

                if (isset($data['jobs']) && is_array($data['jobs'])) {
                    foreach (array_slice($data['jobs'], 0, $limit) as $job) {
                        $location = $job['location'] ?? '';
                        $locationLower = strtolower($location);

                        // Filter for Philippines jobs
                        if (
                            stripos($locationLower, 'philippines') !== false ||
                            stripos($locationLower, 'manila') !== false ||
                            stripos($locationLower, 'makati') !== false ||
                            stripos($locationLower, 'ph') !== false ||
                            empty($location)
                        ) {

                            $jobs[] = [
                                'title' => $job['title'] ?? 'Job Title',
                                'company' => $job['company'] ?? 'Company Not Specified',
                                'location' => $this->extractLocation($location ?: 'Philippines'),
                                'description' => strip_tags($job['snippet'] ?? ''),
                                'url' => $job['link'] ?? '#',
                                'source' => 'Jooble',
                                'published_at' => isset($job['updated']) ? date('Y-m-d H:i:s', strtotime($job['updated'])) : now(),
                            ];
                        }
                    }
                }

                Log::info('Jooble API fetched jobs', [
                    'count' => count($jobs),
                    'total_results' => count($data['jobs'] ?? []),
                    'query' => $query
                ]);
                return $jobs;
            } else {
                Log::warning('Jooble API request failed', [
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 500),
                    'query' => $query
                ]);
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Error fetching from Jooble API', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Fetch jobs from Jobdata API
     */
    protected function fetchFromJobdataAPI(string $query, int $limit): array
    {
        $config = Config::get('job_apis.jobdata');

        if (!$config['enabled'] || empty($config['api_key'])) {
            return [];
        }

        try {
            $url = "https://api.jobdataapi.com/v1/jobs";

            $response = Http::timeout(8) // Reduced timeout from 15s to 8s for faster response
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $config['api_key'],
                    'Accept' => 'application/json',
                ])
                ->get($url, [
                    'q' => $query,
                    'location' => 'Philippines',
                    'limit' => min($limit, 100),
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $jobs = [];

                if (isset($data['data']) && is_array($data['data'])) {
                    foreach ($data['data'] as $job) {
                        $jobs[] = [
                            'title' => $job['title'] ?? 'Job Title',
                            'company' => $job['company'] ?? 'Company Not Specified',
                            'location' => $this->extractLocation($job['location'] ?? 'Philippines'),
                            'description' => strip_tags($job['description'] ?? ''),
                            'url' => $job['url'] ?? '#',
                            'source' => 'Jobdata',
                            'published_at' => isset($job['posted_at']) ? date('Y-m-d H:i:s', strtotime($job['posted_at'])) : now(),
                        ];
                    }
                }

                Log::info('Jobdata API fetched jobs', ['count' => count($jobs)]);
                return $jobs;
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Error fetching from Jobdata API', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Fetch jobs from LinkedIn using official API
     */
    protected function fetchFromLinkedInAPI(string $query, int $limit): array
    {
        $config = Config::get('job_apis.linkedin');

        try {
            $url = "https://api.linkedin.com/v2/jobSearch";

            $response = Http::timeout(8) // Reduced timeout from 15s to 8s for faster response
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $config['access_token'],
                    'Accept' => 'application/json',
                ])
                ->get($url, [
                    'keywords' => $query,
                    'location' => 'Philippines',
                    'count' => min($limit, 25),
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $jobs = [];

                if (isset($data['elements']) && is_array($data['elements'])) {
                    foreach ($data['elements'] as $job) {
                        $jobs[] = [
                            'title' => $job['title'] ?? 'Job Title',
                            'company' => $job['companyDetails']['company'] ?? 'Company Not Specified',
                            'location' => $this->extractLocation($job['formattedLocation'] ?? 'Philippines'),
                            'description' => strip_tags($job['description']['text'] ?? ''),
                            'url' => $job['siteJobUrl'] ?? '#',
                            'source' => 'LinkedIn',
                            'published_at' => isset($job['listedAt']) ? date('Y-m-d H:i:s', $job['listedAt'] / 1000) : now(),
                        ];
                    }
                }

                Log::info('LinkedIn API fetched jobs', ['count' => count($jobs)]);
                return $jobs;
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Error fetching from LinkedIn API', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Fetch jobs from LinkedIn using ScraperAPI
     */
    protected function fetchFromLinkedInViaScraperAPI(string $query, int $limit): array
    {
        $config = Config::get('job_apis.scraperapi');

        try {
            $url = "https://api.scraperapi.com";
            $targetUrl = "https://www.linkedin.com/jobs/search?keywords=" . urlencode($query) . "&location=Philippines";

            $response = Http::timeout(30)
                ->get($url, [
                    'api_key' => $config['api_key'],
                    'url' => $targetUrl,
                    'render' => 'true', // JavaScript rendering
                ]);

            if ($response->successful()) {
                $html = $response->body();
                $jobs = [];

                // Parse LinkedIn job listings from HTML
                if (preg_match_all('/<li[^>]*class="[^"]*job-result-card[^"]*"[^>]*>(.*?)<\/li>/is', $html, $matches)) {
                    foreach (array_slice($matches[0], 0, $limit) as $jobHtml) {
                        if (preg_match('/<h3[^>]*>([^<]+)<\/h3>/i', $jobHtml, $titleMatch)) {
                            $title = trim(strip_tags($titleMatch[1]));
                            preg_match('/<h4[^>]*>([^<]+)<\/h4>/i', $jobHtml, $companyMatch);
                            preg_match('/<a[^>]*href="([^"]+)"[^>]*>/i', $jobHtml, $urlMatch);

                            $jobs[] = [
                                'title' => $title,
                                'company' => isset($companyMatch[1]) ? trim(strip_tags($companyMatch[1])) : 'Company Not Specified',
                                'location' => 'Philippines',
                                'description' => substr(strip_tags($jobHtml), 0, 200),
                                'url' => isset($urlMatch[1]) ? (strpos($urlMatch[1], 'http') === 0 ? $urlMatch[1] : 'https://www.linkedin.com' . $urlMatch[1]) : '#',
                                'source' => 'LinkedIn',
                                'published_at' => now(),
                            ];
                        }
                    }
                }

                Log::info('LinkedIn (ScraperAPI) fetched jobs', ['count' => count($jobs)]);
                return $jobs;
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Error fetching from LinkedIn via ScraperAPI', ['error' => $e->getMessage()]);
            return [];
        }
    }
}

