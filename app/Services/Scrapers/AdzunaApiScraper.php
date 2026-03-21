<?php

namespace App\Services\Scrapers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdzunaApiScraper extends BaseScraper
{
    public function fetchJobs(string $query, int $limit): array
    {
        $appId = env('ADZUNA_APP_ID', '');
        $appKey = env('ADZUNA_APP_KEY', '');

        if (empty($appId) || empty($appKey)) {
            Log::info('Adzuna API: No App ID or Key configured, skipping');
            return [];
        }

        try {
            // Testing 'sg' or 'us' if 'ph' is not supported, but we will try 'ph' first.
            // Some regions are not supported by the adzuna free plan, but it handles errors gracefully.
            $country = 'sg'; // Defaulting to Singapore as a nearby supported English-speaking hub for remote jobs if PH fails in Adzuna
            $url = "https://api.adzuna.com/v1/api/jobs/{$country}/search/1";

            Log::info('Fetching jobs from Adzuna API', [
                'query' => $query,
                'limit' => $limit,
            ]);

            $httpOptions = [
                'verify' => config('app.env') === 'production',
            ];

            $response = Http::timeout(10)
                ->withOptions($httpOptions)
                ->get($url, [
                    'app_id' => $appId,
                    'app_key' => $appKey,
                    'results_per_page' => $limit,
                    'what' => $query,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $jobs = [];

                if (isset($data['results']) && is_array($data['results'])) {
                    foreach ($data['results'] as $job) {
                        $title = trim($job['title'] ?? '');
                        if (empty($title) || strlen($title) < 5) {
                            continue;
                        }

                        $location = $job['location']['display_name'] ?? 'Philippines / Remote';

                        $jobs[] = [
                            'title' => $title,
                            'company' => $job['company']['display_name'] ?? 'Company Not Specified',
                            'location' => $this->extractLocation($location),
                            'description' => substr(strip_tags($job['description'] ?? ''), 0, 500),
                            'url' => $job['redirect_url'] ?? '#',
                            'source' => 'Adzuna',
                            'published_at' => isset($job['created']) 
                                ? date('Y-m-d H:i:s', strtotime($job['created'])) 
                                : now(),
                        ];
                    }
                }

                Log::info('Adzuna API fetched jobs', [
                    'count' => count($jobs),
                    'query' => $query,
                ]);

                return $jobs;
            } else {
                Log::warning('Adzuna API request failed', [
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 300),
                ]);
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Error fetching from Adzuna API', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
