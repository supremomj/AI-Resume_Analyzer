<?php

namespace App\Services\Scrapers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RemotiveApiScraper extends BaseScraper
{
    public function fetchJobs(string $query, int $limit): array
    {
        try {
            // Remotive is 100% free and requires NO API KEY
            $url = 'https://remotive.com/api/remote-jobs';

            Log::info('Fetching jobs from Remotive API (Free, No Key)', [
                'query' => $query,
                'limit' => $limit,
            ]);

            $httpOptions = [
                'verify' => config('app.env') === 'production',
            ];

            $response = Http::timeout(10)
                ->withOptions($httpOptions)
                ->get($url, [
                    'search' => $query,
                    'limit' => $limit * 2 // Fetch extra to filter down for PH later
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $jobs = [];

                if (isset($data['jobs']) && is_array($data['jobs'])) {
                    foreach (array_slice($data['jobs'], 0, $limit) as $job) {
                        $title = trim($job['title'] ?? '');
                        if (empty($title) || strlen($title) < 5) {
                            continue;
                        }

                        // Remotive jobs are remote, but often have a candidate_required_location
                        $location = $job['candidate_required_location'] ?? 'Remote';
                        
                        $jobs[] = [
                            'title' => $title,
                            'company' => $job['company_name'] ?? 'Company Not Specified',
                            'location' => 'Remote (' . $location . ')',
                            'description' => substr(strip_tags($job['description'] ?? ''), 0, 500),
                            'url' => $job['url'] ?? '#',
                            'source' => 'Remotive',
                            'published_at' => isset($job['publication_date']) 
                                ? date('Y-m-d H:i:s', strtotime($job['publication_date'])) 
                                : now(),
                        ];
                    }
                }

                Log::info('Remotive API fetched jobs', [
                    'count' => count($jobs),
                    'query' => $query,
                ]);

                return $jobs;
            } else {
                Log::warning('Remotive API request failed', [
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 300),
                ]);
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Error fetching from Remotive API', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
