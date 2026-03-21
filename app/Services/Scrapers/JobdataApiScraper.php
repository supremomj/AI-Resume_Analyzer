<?php

namespace App\Services\Scrapers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JobdataApiScraper extends BaseScraper
{
    public function fetchJobs(string $query, int $limit): array
    {
        $apiKey = env('JOBDATA_API_KEY', '');

        // If it's still the default placeholder or empty, skip
        if (empty($apiKey) || $apiKey === 'your_api_key_here') {
            Log::info('Jobdata API: No valid API key configured, skipping');
            return [];
        }

        try {
            $url = 'https://jobdataapi.com/api/jobs/';

            Log::info('Fetching jobs from Jobdata API', [
                'query' => $query,
                'limit' => $limit,
            ]);

            $httpOptions = [
                'verify' => config('app.env') === 'production',
            ];

            $response = Http::timeout(10)
                ->withOptions($httpOptions)
                ->withHeaders([
                    'Authorization' => 'Api-Key ' . $apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($url, [
                    'job_title' => $query,
                    'country' => 'PH',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $jobs = [];

                // Jobdata usually returns a paginated response with a 'results' or 'data' array
                $results = $data['results'] ?? $data['data'] ?? ($data ?? []);
                
                if (is_array($results)) {
                    foreach (array_slice($results, 0, $limit) as $job) {
                        // Extract job details robustly to handle slight schema variations
                        $title = trim($job['title'] ?? $job['job_title'] ?? '');
                        if (empty($title) || strlen($title) < 5) {
                            continue;
                        }

                        // Try to get company name from nested object or flat structure
                        $company = $job['company']['name'] ?? $job['company_name'] ?? $job['employer_name'] ?? 'Company Not Specified';
                        
                        $location = $job['location'] ?? $job['city'] ?? $job['job_city'] ?? 'Philippines';
                        
                        $jobs[] = [
                            'title' => $title,
                            'company' => $company,
                            'location' => $this->extractLocation($location),
                            'description' => substr(strip_tags($job['description'] ?? $job['job_description'] ?? ''), 0, 500),
                            'url' => $job['url'] ?? $job['apply_url'] ?? $job['job_apply_link'] ?? '#',
                            'source' => 'Jobdata',
                            'published_at' => isset($job['published_at']) || isset($job['created_at'])
                                ? date('Y-m-d H:i:s', strtotime($job['published_at'] ?? $job['created_at']))
                                : now(),
                        ];
                    }
                }

                Log::info('Jobdata API fetched jobs', [
                    'count' => count($jobs),
                    'query' => $query,
                ]);

                return $jobs;
            } else {
                Log::warning('Jobdata API request failed', [
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 300),
                ]);
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Error fetching from Jobdata API', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
