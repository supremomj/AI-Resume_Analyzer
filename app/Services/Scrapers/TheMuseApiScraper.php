<?php

namespace App\Services\Scrapers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TheMuseApiScraper extends BaseScraper
{
    public function fetchJobs(string $query, int $limit): array
    {
        try {
            // The Muse API is free, no key required for basic access
            $url = 'https://www.themuse.com/api/public/jobs';

            Log::info('Fetching jobs from The Muse API', [
                'query' => $query,
                'limit' => $limit,
            ]);

            $httpOptions = [
                'verify' => config('app.env') === 'production',
            ];

            // The Muse API filters strictly by their predetermined categories if we use the category param,
            // but we can fetch recent and filter in memory if the query isn't a strict match.
            // For better results, we try mapping common queries to categories or fetch latest.
            
            $response = Http::timeout(10)
                ->withOptions($httpOptions)
                ->get($url, [
                    'page' => 1,
                    // 'category' => $query // Often fails if not an exact preset category
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $jobs = [];

                if (isset($data['results']) && is_array($data['results'])) {
                    $queryLower = strtolower($query);
                    $queryWords = explode(' ', $queryLower);

                    foreach ($data['results'] as $job) {
                        $title = trim($job['name'] ?? '');
                        if (empty($title) || strlen($title) < 3) {
                            continue;
                        }

                        // Basic memory filter for query relevance
                        $titleLower = strtolower($title);
                        $descriptionLower = strtolower($job['contents'] ?? '');
                        
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

                        $locationStr = 'Remote';
                        if (isset($job['locations']) && is_array($job['locations']) && count($job['locations']) > 0) {
                            $locationStr = $job['locations'][0]['name'] ?? 'Remote';
                        }

                        $jobs[] = [
                            'title' => $title,
                            'company' => $job['company']['name'] ?? 'Company Not Specified',
                            'location' => $this->extractLocation($locationStr),
                            'description' => substr(strip_tags($job['contents'] ?? ''), 0, 500),
                            'url' => $job['refs']['landing_page'] ?? '#',
                            'source' => 'The Muse',
                            'published_at' => isset($job['publication_date']) 
                                ? date('Y-m-d H:i:s', strtotime($job['publication_date'])) 
                                : now(),
                        ];

                        if (count($jobs) >= $limit) {
                            break;
                        }
                    }
                }

                Log::info('The Muse API fetched jobs', [
                    'count' => count($jobs),
                    'query' => $query,
                ]);

                return $jobs;
            } else {
                Log::warning('The Muse API request failed', [
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 300),
                ]);
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Error fetching from The Muse API', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
