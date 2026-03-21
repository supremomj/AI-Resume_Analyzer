<?php

namespace App\Services\Scrapers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ArbeitnowApiScraper extends BaseScraper
{
    public function fetchJobs(string $query, int $limit): array
    {
        try {
            // Arbeitnow is 100% free and requires NO API KEY
            $url = 'https://www.arbeitnow.com/api/job-board-api';

            Log::info('Fetching jobs from Arbeitnow API (Free, No Key)', [
                'query' => $query,
                'limit' => $limit,
            ]);

            $httpOptions = [
                'verify' => config('app.env') === 'production',
            ];

            $response = Http::timeout(10)
                ->withOptions($httpOptions)
                ->get($url);

            if ($response->successful()) {
                $data = $response->json();
                $jobs = [];

                if (isset($data['data']) && is_array($data['data'])) {
                    // Arbeitnow doesn't have a direct query search in the basic free endpoint,
                    // so we fetch the recent board and filter in memory
                    $queryLower = strtolower($query);
                    $queryWords = explode(' ', $queryLower);

                    foreach ($data['data'] as $job) {
                        $title = trim($job['title'] ?? '');
                        if (empty($title) || strlen($title) < 3) {
                            continue;
                        }

                        // Basic memory filter for query relevance since API doesn't support 'what='
                        $titleLower = strtolower($title);
                        $descriptionLower = strtolower($job['description'] ?? '');
                        
                        $matches = false;
                        foreach ($queryWords as $word) {
                            if (strlen($word) > 2 && (str_contains($titleLower, $word) || str_contains($descriptionLower, $word))) {
                                $matches = true;
                                break;
                            }
                        }

                        if (!$matches && !empty($query)) {
                            continue;
                        }

                        $location = $job['location'] ?? 'Remote';
                        if (isset($job['remote']) && $job['remote']) {
                            $location = 'Remote (' . $location . ')';
                        }

                        $jobs[] = [
                            'title' => $title,
                            'company' => $job['company_name'] ?? 'Company Not Specified',
                            'location' => $this->extractLocation($location),
                            'description' => substr(strip_tags($job['description'] ?? ''), 0, 500),
                            'url' => $job['url'] ?? '#',
                            'source' => 'Arbeitnow',
                            'published_at' => isset($job['created_at']) 
                                ? date('Y-m-d H:i:s', $job['created_at']) 
                                : now(),
                        ];

                        if (count($jobs) >= $limit) {
                            break;
                        }
                    }
                }

                Log::info('Arbeitnow API fetched jobs', [
                    'count' => count($jobs),
                    'query' => $query,
                ]);

                return $jobs;
            } else {
                Log::warning('Arbeitnow API request failed', [
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 300),
                ]);
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Error fetching from Arbeitnow API', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
