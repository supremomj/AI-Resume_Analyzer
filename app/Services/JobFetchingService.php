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
            // PRIMARY SOURCE - JSearch API via RapidAPI (free tier, supports PH, aggregates Google Jobs)
            'JSearch' => fn() => $this->fetchFromJSearchAPI($searchQuery, $prioritySourceLimit),

            // SECONDARY SOURCES - Web scrapers as fallback
            'OnlineJobs.ph' => fn() => $this->fetchFromOnlineJobs($searchQuery, $perSourceLimit),
            'Indeed' => fn() => $this->fetchFromIndeed($searchQuery, $perSourceLimit),
            'Kalibrr' => fn() => $this->fetchFromKalibrr($searchQuery, $perSourceLimit),
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
                        'Indeed' => $this->fetchFromIndeed($broaderQuery, 10),
                        'Kalibrr' => $this->fetchFromKalibrr($broaderQuery, 10),
                        'OnlineJobs.ph' => $this->fetchFromOnlineJobs($broaderQuery, 10),
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
     * Fetch jobs from Indeed Philippines
     */
    protected function fetchFromIndeed(string $query, int $limit): array
    {
        try {
            // Indeed has an RSS feed we can use
            $location = 'Philippines';
            $url = "https://ph.indeed.com/rss?q=" . urlencode($query) . "&l=" . urlencode($location);

            Log::info('Fetching jobs from Indeed', ['url' => $url]);

            $httpOptions = [
                'verify' => config('app.env') === 'production', // Only verify SSL in production
            ];

            $response = Http::timeout(8) // Reduced timeout from 15s to 8s for faster response
                ->withOptions($httpOptions)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'application/rss+xml, application/xml, text/xml, */*',
                ])
                ->get($url);

            if ($response->successful()) {
                $body = $response->body();
                $xml = @simplexml_load_string($body);
                $jobs = [];

                if ($xml === false) {
                    Log::warning('Indeed RSS feed parse error', [
                        'url' => $url,
                        'status' => $response->status(),
                        'body_length' => strlen($body),
                        'body_preview' => substr($body, 0, 500),
                        'is_xml' => strpos($body, '<?xml') !== false || strpos($body, '<rss') !== false
                    ]);
                    return [];
                }

                if ($xml && isset($xml->channel->item)) {
                    $itemCount = count($xml->channel->item);
                    Log::info('Indeed RSS parsed successfully', [
                        'items_found' => $itemCount,
                        'limit' => $limit
                    ]);

                    foreach ($xml->channel->item as $item) {
                        $description = strip_tags((string) $item->description);
                        $title = (string) $item->title;

                        // Extract location from description or title
                        $extractedLocation = $this->extractLocation($description . ' ' . $title);

                        // Clean title - remove company name if present
                        $cleanTitle = $title;
                        if (preg_match('/^(.+?)\s*-\s*.+$/', $title, $titleMatch)) {
                            $cleanTitle = trim($titleMatch[1]);
                        }

                        $jobs[] = [
                            'title' => $cleanTitle,
                            'company' => $this->extractCompanyFromIndeed($description),
                            'location' => $extractedLocation,
                            'description' => $description,
                            'url' => (string) $item->link,
                            'source' => 'Indeed',
                            'published_at' => isset($item->pubDate) ? date('Y-m-d H:i:s', strtotime((string) $item->pubDate)) : now(),
                        ];

                        if (count($jobs) >= $limit)
                            break;
                    }
                } else {
                    Log::warning('Indeed RSS has no items', [
                        'url' => $url,
                        'xml_structure' => $xml ? 'valid' : 'invalid',
                        'has_channel' => $xml && isset($xml->channel),
                        'has_items' => $xml && isset($xml->channel->item)
                    ]);
                }

                return $jobs;
            } else {
                Log::warning('Indeed RSS feed request failed', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body_preview' => substr($response->body(), 0, 500),
                    'headers' => $response->headers()
                ]);
            }

            return [];

        } catch (\Exception $e) {
            Log::error('Error fetching from Indeed', [
                'error' => $e->getMessage(),
                'trace' => substr($e->getTraceAsString(), 0, 300)
            ]);
            return [];
        }
    }

    /**
     * Fetch jobs from Kalibrr
     */
    protected function fetchFromKalibrr(string $query, int $limit): array
    {
        try {
            // Kalibrr search URL
            $url = "https://www.kalibrr.com/ph/jobs?q=" . urlencode($query);

            Log::info('Fetching jobs from Kalibrr', ['url' => $url]);

            // Kalibrr doesn't have public RSS, so we'll use their search API if available
            // For now, try to fetch via HTTP and parse HTML
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
                // Parse HTML to extract job listings
                // This is a simplified version - you may need more robust parsing
                $html = $response->body();
                $jobs = [];

                // Try to extract job data from JSON embedded in page
                if (preg_match('/window\.__INITIAL_STATE__\s*=\s*({.+?});/', $html, $matches)) {
                    $data = json_decode($matches[1], true);
                    if (isset($data['jobs']['list']) && is_array($data['jobs']['list'])) {
                        foreach (array_slice($data['jobs']['list'], 0, $limit) as $job) {
                            $title = $job['name'] ?? $job['title'] ?? 'Job Title';
                            $location = $job['location']['name'] ?? 'Philippines';

                            // Extract better location if available
                            if (isset($job['location']['city'])) {
                                $location = $job['location']['city'];
                                if (isset($job['location']['province']) && $job['location']['province'] !== $job['location']['city']) {
                                    $location .= ', ' . $job['location']['province'];
                                }
                            }

                            // Clean title
                            $title = trim(strip_tags($title));
                            $title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');

                            $jobs[] = [
                                'title' => $title,
                                'company' => $job['company']['name'] ?? 'Company Not Specified',
                                'location' => $this->extractLocation($location),
                                'description' => strip_tags($job['description'] ?? ''),
                                'url' => 'https://www.kalibrr.com' . ($job['url'] ?? '/jobs'),
                                'source' => 'Kalibrr',
                                'published_at' => isset($job['created_at']) ? date('Y-m-d H:i:s', strtotime($job['created_at'])) : now(),
                            ];
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
            Log::error('Error fetching from Kalibrr', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Fetch jobs from OnlineJobs.ph
     */
    protected function fetchFromOnlineJobs(string $query, int $limit): array
    {
        try {
            // OnlineJobs.ph search URL - popular Philippine job site
            $url = "https://www.onlinejobs.ph/jobseekers/jobsearch?keyword=" . urlencode($query);

            Log::info('Fetching jobs from OnlineJobs.ph', [
                'url' => $url,
                'query' => $query,
                'limit' => $limit
            ]);

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

                // OnlineJobs.ph structure: Jobs are in list items or divs with job data
                // Try to extract job listings using multiple patterns

                // Pattern 1: Look for job listing containers (most common structure)
                // Jobs are usually in <li> or <div> with class containing "job" or "listing"
                $jobContainers = [];

                // Try to find job containers
                if (preg_match_all('/<(?:li|div)[^>]*class="[^"]*(?:job|listing|result|item)[^"]*"[^>]*>(.*?)<\/(?:li|div)>/is', $html, $containerMatches)) {
                    $jobContainers = $containerMatches[0];
                } elseif (preg_match_all('/<div[^>]*data-job[^>]*>(.*?)<\/div>/is', $html, $containerMatches)) {
                    $jobContainers = $containerMatches[0];
                }

                // Extract jobs from containers
                foreach (array_slice($jobContainers, 0, $limit * 3) as $container) {
                    // Extract job URL - look for /jobseekers/job/ pattern
                    if (preg_match('/href="(\/jobseekers\/job\/[^"]+)"/i', $container, $urlMatch)) {
                        $jobUrl = 'https://www.onlinejobs.ph' . $urlMatch[1];
                        $jobId = basename($urlMatch[1]);

                        // Extract job title - try multiple patterns (prioritize link text as it's usually the actual title)
                        $jobTitle = null;

                        // Pattern 1: Title in link text (PRIORITY - most reliable, usually the actual job title)
                        if (preg_match('/<a[^>]*href="\/jobseekers\/job\/' . preg_quote($jobId, '/') . '"[^>]*>(.*?)<\/a>/is', $container, $linkMatch)) {
                            $linkContent = $linkMatch[1];

                            // If link contains <br /> or similar, split and take only the first part (usually the title)
                            if (preg_match('/<br\s*\/?>/i', $linkContent)) {
                                $parts = preg_split('/<br\s*\/?>/i', $linkContent, 2);
                                $linkText = trim(strip_tags($parts[0]));
                            } else {
                                $linkText = trim(strip_tags($linkContent));
                            }

                            // Clean HTML entities
                            $linkText = html_entity_decode($linkText, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                            // Remove any remaining HTML tags
                            $linkText = preg_replace('/<[^>]+>/', '', $linkText);
                            // Remove common unwanted patterns
                            $linkText = preg_replace('/\s*(br\s*\/?)\s*/i', ' ', $linkText);
                            $linkText = preg_replace('/\s+/', ' ', $linkText);
                            $linkText = trim($linkText);

                            // If title contains description-like text, extract only the title part
                            // Pattern: "Job Title<br />We are looking for..." -> extract "Job Title"
                            if (preg_match('/^(.*?)\s+(?:we are|looking for|join|hiring|need|fast-growing|skilled|this role|technology|data engineering|based|developer|create|role is)/i', $linkText, $titlePart)) {
                                $linkText = trim($titlePart[1]);
                            }

                            // Validate it looks like a job title
                            if (
                                stripos($linkText, 'see more') === false &&
                                stripos($linkText, 'view more') === false &&
                                stripos($linkText, '...') === false &&
                                stripos($linkText, 'click here') === false &&
                                stripos($linkText, 'apply now') === false &&
                                !preg_match('/^(we are|looking for|join|hiring|need|fast-growing|skilled|this role|technology|data engineering|based|developer|create|role is)/i', $linkText) && // Not description-like
                                !preg_match('/\b(br|html|div|span|p)\b/i', $linkText) && // No HTML tag names
                                strlen($linkText) > 5 &&
                                strlen($linkText) < 150
                            ) {
                                $jobTitle = $linkText;
                            }
                        }
                        // Pattern 2: Title in h2, h3, h4, or strong tag within container (but validate it's not description)
                        if (!$jobTitle && preg_match('/<(?:h[234]|strong|b)[^>]*>([^<]{10,150})<\/(?:h[234]|strong|b)>/i', $container, $titleMatch)) {
                            $potentialTitle = trim(strip_tags($titleMatch[1]));
                            $potentialTitle = html_entity_decode($potentialTitle, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                            $potentialTitle = preg_replace('/\s*(br\s*\/?|<\/?[^>]+>)\s*/i', ' ', $potentialTitle);
                            $potentialTitle = preg_replace('/\s+/', ' ', $potentialTitle);
                            $potentialTitle = trim($potentialTitle);

                            // Validate it's not description-like
                            if (
                                !preg_match('/^(we are|looking for|join|hiring|need|fast-growing|skilled|this role)/i', $potentialTitle) &&
                                stripos($potentialTitle, 'see more') === false &&
                                strlen($potentialTitle) > 5 &&
                                strlen($potentialTitle) < 150
                            ) {
                                $jobTitle = $potentialTitle;
                            }
                        }
                        // Pattern 3: Look for title attribute or data attribute
                        if (!$jobTitle && preg_match('/data-title="([^"]{10,150})"/i', $container, $dataMatch)) {
                            $jobTitle = trim(html_entity_decode($dataMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                        }
                        // Pattern 4: Look for title in text node before the job URL (last resort, be very strict)
                        if (!$jobTitle && preg_match('/([A-Z][A-Za-z0-9\s&,\-()]{10,80})\s*<a[^>]*href="\/jobseekers\/job\/' . preg_quote($jobId, '/') . '"/i', $container, $textMatch)) {
                            $potentialTitle = trim(strip_tags($textMatch[1]));
                            $potentialTitle = html_entity_decode($potentialTitle, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                            $potentialTitle = preg_replace('/\s*(br\s*\/?|<\/?[^>]+>)\s*/i', ' ', $potentialTitle);
                            $potentialTitle = preg_replace('/\s+/', ' ', $potentialTitle);
                            $potentialTitle = trim($potentialTitle);

                            // Very strict validation - must look like a job title
                            if (
                                !preg_match('/^(we are|looking for|join|hiring|need|fast-growing|skilled|this role|technology|data engineering)/i', $potentialTitle) &&
                                !preg_match('/\b(br|html|div|span)\b/i', $potentialTitle) && // No HTML tag names
                                strlen($potentialTitle) > 10 &&
                                strlen($potentialTitle) < 100
                            ) {
                                $jobTitle = $potentialTitle;
                            }
                        }

                        // If still no title, try to extract from the HTML context around the URL
                        if (!$jobTitle) {
                            // Get a larger context around the job URL
                            $contextPattern = '/.{0,300}href="\/jobseekers\/job\/' . preg_quote($jobId, '/') . '".{0,300}/is';
                            if (preg_match($contextPattern, $html, $contextMatch)) {
                                $context = $contextMatch[0];
                                // Look for any heading or bold text in context
                                if (preg_match('/<(?:h[234]|strong|b)[^>]*>([^<]{10,200})<\/(?:h[234]|strong|b)>/i', $context, $contextTitle)) {
                                    $jobTitle = trim(strip_tags($contextTitle[1]));
                                }
                            }
                        }

                        // Extract company - improved patterns
                        $company = 'Company Not Specified';
                        // Pattern 1: Company in span with class containing "company"
                        if (preg_match('/<span[^>]*class="[^"]*company[^"]*"[^>]*>([^<]+)<\/span>/i', $container, $companyMatch)) {
                            $company = trim(strip_tags($companyMatch[1]));
                        }
                        // Pattern 2: Employer div
                        elseif (preg_match('/<div[^>]*class="[^"]*employer[^"]*"[^>]*>([^<]+)<\/div>/i', $container, $employerMatch)) {
                            $company = trim(strip_tags($employerMatch[1]));
                        }
                        // Pattern 3: Look for company name patterns in text
                        elseif (preg_match('/\b([A-Z][A-Za-z0-9\s&.,\-()]{3,50})\s+(?:Philippines|PH|Inc|Corp|Corporation|Ltd|Limited|Company|Technologies|Solutions|Services)\b/i', $container, $companyTextMatch)) {
                            $company = trim(strip_tags($companyTextMatch[1]));
                        }

                        // Clean company name
                        $company = html_entity_decode($company, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        $company = preg_replace('/\s+/', ' ', $company);
                        $company = trim($company);

                        if (empty($company) || strlen($company) < 2) {
                            $company = 'Company Not Specified';
                        }

                        // Extract description - try multiple patterns
                        $description = '';

                        // Pattern 1: Description in paragraph with class containing "description"
                        if (preg_match('/<p[^>]*class="[^"]*description[^"]*"[^>]*>(.*?)<\/p>/is', $container, $descMatch)) {
                            $description = trim(strip_tags($descMatch[1]));
                        }
                        // Pattern 2: Summary div
                        elseif (preg_match('/<div[^>]*class="[^"]*summary[^"]*"[^>]*>(.*?)<\/div>/is', $container, $summaryMatch)) {
                            $description = trim(strip_tags($summaryMatch[1]));
                        }
                        // Pattern 3: Any paragraph after the title
                        elseif (preg_match('/<(?:h[234]|strong|b)[^>]*>' . preg_quote($jobTitle, '/') . '<\/(?:h[234]|strong|b)>(.*?)(?:<a|<div|<h[234]|$)/is', $container, $afterTitleMatch)) {
                            $potentialDesc = trim(strip_tags($afterTitleMatch[1]));
                            // Only use if it's different from title and has reasonable length
                            if ($potentialDesc && $potentialDesc !== $jobTitle && strlen($potentialDesc) > 20 && strlen($potentialDesc) < 500) {
                                $description = $potentialDesc;
                            }
                        }
                        // Pattern 4: Look for any text content in the container that's not the title
                        else {
                            $containerText = strip_tags($container);
                            $containerText = preg_replace('/\s+/', ' ', $containerText);
                            // Remove the title from the text
                            $containerText = str_ireplace($jobTitle, '', $containerText);
                            $containerText = trim($containerText);
                            // Use if it's meaningful and different from title
                            if ($containerText && strlen($containerText) > 30 && strlen($containerText) < 500 && $containerText !== $jobTitle) {
                                // Take first 200 characters as description
                                $description = substr($containerText, 0, 200);
                            }
                        }

                        // Clean up title - remove HTML entities and ensure it's clean
                        if ($jobTitle) {
                            $jobTitle = html_entity_decode($jobTitle, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                            $jobTitle = preg_replace('/\s*(br\s*\/?|<\/?[^>]+>)\s*/i', ' ', $jobTitle);
                            $jobTitle = preg_replace('/\s+/', ' ', $jobTitle);
                            $jobTitle = trim($jobTitle);

                            // Remove common prefixes that shouldn't be in title
                            $jobTitle = preg_replace('/^(Job|Position|Opening|Vacancy|Hiring):\s*/i', '', $jobTitle);
                            $jobTitle = trim($jobTitle);
                        }

                        // Clean up description - remove if it's the same as title
                        if ($description && strtolower(trim($description)) === strtolower(trim($jobTitle))) {
                            $description = '';
                        }

                        // Only add if we have a valid title that doesn't look like description
                        if (
                            $jobTitle &&
                            strlen($jobTitle) > 5 &&
                            strlen($jobTitle) < 150 &&
                            stripos($jobTitle, 'see more') === false &&
                            stripos($jobTitle, 'view more') === false &&
                            stripos($jobTitle, '...') === false &&
                            stripos($jobTitle, 'click here') === false &&
                            !preg_match('/^(we are|looking for|join|hiring|need|fast-growing|skilled|this role)/i', $jobTitle) && // Not description-like
                            !preg_match('/\b(br|html|div|span|p)\b/i', $jobTitle)
                        ) { // No HTML tag names
                            // Extract location from container
                            $location = 'Philippines';
                            if (preg_match('/<span[^>]*class="[^"]*location[^"]*"[^>]*>([^<]+)<\/span>/i', $container, $locMatch)) {
                                $location = trim(strip_tags($locMatch[1]));
                            } elseif (preg_match('/\b(Manila|Makati|Quezon City|Pasig|Taguig|Cebu|Davao|BGC|Ortigas|Metro Manila|NCR)\b/i', $container, $locTextMatch)) {
                                $location = $this->extractLocation($locTextMatch[0]);
                            } else {
                                $location = $this->extractLocation($container);
                            }

                            $jobs[] = [
                                'title' => $jobTitle,
                                'company' => $company,
                                'location' => $location,
                                'description' => $description ?: '', // Use empty string instead of title
                                'url' => $jobUrl,
                                'source' => 'OnlineJobs.ph',
                                'published_at' => now(),
                            ];

                            if (count($jobs) >= $limit) {
                                break;
                            }
                        }
                    }
                }

                // Pattern 2: Fallback - extract from job URLs directly and use URL as title source
                if (empty($jobs) && preg_match_all('/href="(\/jobseekers\/job\/(\d+))"/i', $html, $urlMatches, PREG_SET_ORDER)) {
                    Log::info('OnlineJobs.ph: Found job URLs, extracting titles from context', [
                        'url_count' => count($urlMatches)
                    ]);

                    foreach (array_slice($urlMatches, 0, $limit * 2) as $urlMatch) {
                        $jobUrl = 'https://www.onlinejobs.ph' . $urlMatch[1];
                        $jobId = $urlMatch[2];

                        // Try to find title near this URL in the HTML - use larger context
                        $contextPattern = '/.{0,1000}href="\/jobseekers\/job\/' . preg_quote($jobId, '/') . '".{0,1000}/is';
                        if (preg_match($contextPattern, $html, $contextMatch)) {
                            $context = $contextMatch[0];

                            // Look for title in context - try multiple patterns (prioritize link text)
                            $jobTitle = null;

                            // Pattern 1: Title in link text (most reliable)
                            if (preg_match('/<a[^>]*href="\/jobseekers\/job\/' . preg_quote($jobId, '/') . '"[^>]*>(.*?)<\/a>/is', $context, $linkMatch)) {
                                $linkContent = $linkMatch[1];

                                // If link contains <br /> or similar, split and take only the first part
                                if (preg_match('/<br\s*\/?>/i', $linkContent)) {
                                    $parts = preg_split('/<br\s*\/?>/i', $linkContent, 2);
                                    $linkText = trim(strip_tags($parts[0]));
                                } else {
                                    $linkText = trim(strip_tags($linkContent));
                                }

                                $linkText = html_entity_decode($linkText, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                $linkText = preg_replace('/<[^>]+>/', '', $linkText);
                                $linkText = preg_replace('/\s*(br\s*\/?)\s*/i', ' ', $linkText);
                                $linkText = preg_replace('/\s+/', ' ', $linkText);
                                $linkText = trim($linkText);

                                // If title contains description-like text, extract only the title part
                                if (preg_match('/^(.*?)\s+(?:we are|looking for|join|hiring|need|fast-growing|skilled|this role|technology|data engineering|based|developer|create|role is)/i', $linkText, $titlePart)) {
                                    $linkText = trim($titlePart[1]);
                                }

                                if (
                                    stripos($linkText, 'see more') === false &&
                                    stripos($linkText, 'view more') === false &&
                                    !preg_match('/^(we are|looking for|join|hiring|need|fast-growing|skilled|this role|technology|data engineering|based|developer|create|role is)/i', $linkText) &&
                                    !preg_match('/\b(br|html|div|span|p)\b/i', $linkText) &&
                                    strlen($linkText) > 5 &&
                                    strlen($linkText) < 150
                                ) {
                                    $jobTitle = $linkText;
                                }
                            }
                            // Pattern 2: Heading tags (but validate)
                            if (!$jobTitle && preg_match('/<(?:h[123456]|strong|b)[^>]*>([^<]{10,150})<\/(?:h[123456]|strong|b)>/i', $context, $titleMatch)) {
                                $potentialTitle = trim(strip_tags($titleMatch[1]));
                                $potentialTitle = html_entity_decode($potentialTitle, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                $potentialTitle = preg_replace('/\s*(br\s*\/?|<\/?[^>]+>)\s*/i', ' ', $potentialTitle);
                                $potentialTitle = preg_replace('/\s+/', ' ', $potentialTitle);
                                $potentialTitle = trim($potentialTitle);

                                if (
                                    !preg_match('/^(we are|looking for|join|hiring|need|fast-growing|skilled|this role)/i', $potentialTitle) &&
                                    strlen($potentialTitle) > 5 &&
                                    strlen($potentialTitle) < 150
                                ) {
                                    $jobTitle = $potentialTitle;
                                }
                            }
                            // Pattern 3: Look for title in data attributes
                            if (!$jobTitle && preg_match('/data-title="([^"]{10,150})"/i', $context, $dataMatch)) {
                                $jobTitle = trim(html_entity_decode($dataMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                            }

                            // Clean up title
                            if ($jobTitle) {
                                $jobTitle = preg_replace('/\s+/', ' ', $jobTitle);
                                $jobTitle = trim($jobTitle);
                                // Remove common prefixes/suffixes
                                $jobTitle = preg_replace('/^(Job|Position|Opening|Vacancy|Hiring):\s*/i', '', $jobTitle);
                                $jobTitle = trim($jobTitle);
                            }

                            // Extract description from context if possible
                            $description = '';
                            // Try to find description text after the title in the context
                            if (preg_match('/' . preg_quote($jobTitle, '/') . '\s*(.*?)(?:<a|<div|<h[234]|$)/is', $context, $descMatch)) {
                                $potentialDesc = trim(strip_tags($descMatch[1]));
                                // Only use if it's different from title and has reasonable length
                                if ($potentialDesc && $potentialDesc !== $jobTitle && strlen($potentialDesc) > 20 && strlen($potentialDesc) < 500) {
                                    $description = substr($potentialDesc, 0, 200);
                                }
                            }

                            // Only add if we have a meaningful title that doesn't look like description
                            if (
                                $jobTitle &&
                                strlen($jobTitle) > 10 &&
                                strlen($jobTitle) < 150 &&
                                stripos($jobTitle, 'see more') === false &&
                                stripos($jobTitle, 'view more') === false &&
                                stripos($jobTitle, 'click here') === false &&
                                stripos($jobTitle, 'apply now') === false &&
                                !preg_match('/^(we are|looking for|join|hiring|need|fast-growing|skilled|this role|technology|data engineering)/i', $jobTitle) && // Not description-like
                                !preg_match('/\b(br|html|div|span|p)\b/i', $jobTitle)
                            ) { // No HTML tag names
                                $jobs[] = [
                                    'title' => $jobTitle,
                                    'company' => 'Company Not Specified',
                                    'location' => 'Philippines',
                                    'description' => $description, // Use extracted description or empty string
                                    'url' => $jobUrl,
                                    'source' => 'OnlineJobs.ph',
                                    'published_at' => now(),
                                ];

                                if (count($jobs) >= $limit) {
                                    break;
                                }
                            }
                        }
                    }
                }

                // Pattern 3: Last resort - use job ID as part of title if we found URLs but no titles
                if (empty($jobs) && preg_match_all('/href="(\/jobseekers\/job\/(\d+))"/i', $html, $urlMatches, PREG_SET_ORDER)) {
                    Log::warning('OnlineJobs.ph: Found job URLs but could not extract titles, using fallback', [
                        'url_count' => count($urlMatches)
                    ]);

                    foreach (array_slice($urlMatches, 0, $limit) as $urlMatch) {
                        $jobUrl = 'https://www.onlinejobs.ph' . $urlMatch[1];
                        $jobId = $urlMatch[2];

                        // Use a generic title with the job ID
                        $jobs[] = [
                            'title' => "Job Opportunity #{$jobId}",
                            'company' => 'Company Not Specified',
                            'location' => 'Philippines',
                            'description' => "Job posting from OnlineJobs.ph",
                            'url' => $jobUrl,
                            'source' => 'OnlineJobs.ph',
                            'published_at' => now(),
                        ];

                        if (count($jobs) >= $limit) {
                            break;
                        }
                    }
                }

                Log::info('OnlineJobs.ph parsing result', [
                    'jobs_found' => count($jobs),
                    'limit' => $limit,
                    'sample_jobs' => array_slice(array_map(function ($j) {
                        return [
                            'title' => substr($j['title'] ?? '', 0, 50),
                            'url' => substr($j['url'] ?? '', 0, 50),
                            'company' => substr($j['company'] ?? '', 0, 30)
                        ];
                    }, $jobs), 0, 3)
                ]);

                return $jobs;
            } else {
                Log::warning('OnlineJobs.ph request failed', [
                    'url' => $url,
                    'status' => $response->status(),
                    'error' => substr($response->body(), 0, 500)
                ]);
            }

            return [];

        } catch (\Exception $e) {
            Log::error('Error fetching from OnlineJobs.ph', ['error' => $e->getMessage()]);
            return [];
        }
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
     * Extract company name from Indeed job description
     */
    protected function extractCompanyFromIndeed(string $description): string
    {
        // Indeed RSS sometimes includes company in description
        // Pattern 1: "Job Title - Company Name"
        if (preg_match('/-\s*([A-Z][A-Za-z0-9\s&.,\-()]+?)(?:\s*-\s*[A-Z]|$)/', $description, $matches)) {
            $company = trim($matches[1]);
            // Clean up common suffixes
            $company = preg_replace('/\s*-\s*(Philippines|PH|Manila|Makati|BGC)$/i', '', $company);
            if (strlen($company) > 2 && strlen($company) < 100) {
                return trim($company);
            }
        }

        // Pattern 2: Look for company name patterns
        if (preg_match('/\b([A-Z][A-Za-z0-9\s&.,\-()]{3,50})\s+(?:Philippines|PH|Inc|Corp|Corporation|Ltd|Limited|Company)$/i', $description, $matches)) {
            return trim($matches[1]);
        }

        return 'Company Not Specified';
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
     * Fetch jobs from JSearch API (RapidAPI) - aggregates Google Jobs (LinkedIn, Indeed, Glassdoor, etc.)
     * Free tier: 200 requests/month. Supports Philippines via country=ph.
     */
    protected function fetchFromJSearchAPI(string $query, int $limit): array
    {
        $apiKey = env('JSEARCH_API_KEY', '');

        if (empty($apiKey)) {
            Log::info('JSearch API: No API key configured, skipping');
            return [];
        }

        try {
            $url = 'https://jsearch.p.rapidapi.com/search';

            Log::info('Fetching jobs from JSearch API', [
                'query' => $query,
                'limit' => $limit,
            ]);

            $httpOptions = [
                'verify' => config('app.env') === 'production',
            ];

            $response = Http::timeout(15)
                ->withOptions($httpOptions)
                ->withHeaders([
                    'X-RapidAPI-Key' => $apiKey,
                    'X-RapidAPI-Host' => 'jsearch.p.rapidapi.com',
                    'Accept' => 'application/json',
                ])
                ->get($url, [
                    'query' => $query . ' Philippines',
                    'num_pages' => 1,
                    'page' => 1,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $jobs = [];

                if (isset($data['data']) && is_array($data['data'])) {
                    foreach (array_slice($data['data'], 0, $limit) as $job) {
                        $title = trim($job['job_title'] ?? '');
                        if (empty($title) || strlen($title) < 5)
                            continue;

                        $location = $job['job_city'] ?? '';
                        if (!empty($job['job_state'])) {
                            $location .= ($location ? ', ' : '') . $job['job_state'];
                        }
                        if (empty($location)) {
                            $location = $job['job_country'] ?? 'Philippines';
                        }

                        $jobs[] = [
                            'title' => $title,
                            'company' => $job['employer_name'] ?? 'Company Not Specified',
                            'location' => $this->extractLocation($location ?: 'Philippines'),
                            'description' => substr(strip_tags($job['job_description'] ?? ''), 0, 500),
                            'url' => $job['job_apply_link'] ?? $job['job_google_link'] ?? '#',
                            'source' => 'JSearch',
                            'published_at' => isset($job['job_posted_at_datetime_utc'])
                                ? date('Y-m-d H:i:s', strtotime($job['job_posted_at_datetime_utc']))
                                : now(),
                        ];
                    }
                }

                Log::info('JSearch API fetched jobs', [
                    'count' => count($jobs),
                    'total_results' => $data['status'] ?? 'unknown',
                    'query' => $query,
                ]);

                return $jobs;
            } else {
                Log::warning('JSearch API request failed', [
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 300),
                ]);
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Error fetching from JSearch API', ['error' => $e->getMessage()]);
            return [];
        }
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

