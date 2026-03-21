<?php

namespace App\Services\Scrapers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JoobleApiScraper extends BaseScraper
{
    public function fetchJobs(string $query, int $limit): array
    {
        $apiKey = env('JOOBLE_API_KEY', '');

        if (empty($apiKey)) {
            Log::info('Jooble API: No API key configured, skipping');
            return [];
        }

        try {
            $url = 'https://jooble.org/api/' . $apiKey;

            Log::info('Fetching jobs from Jooble API', [
                'query' => $query,
                'limit' => $limit,
            ]);

            $httpOptions = [
                'verify' => config('app.env') === 'production',
            ];

            // Jooble expects a JSON POST payload
            $payload = [
                'keywords' => $query,
                'location' => 'Philippines',
                'ResultOnPage' => $limit,
                'page' => 1
            ];

            $response = Http::timeout(10)
                ->withOptions($httpOptions)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $jobs = [];

                if (isset($data['jobs']) && is_array($data['jobs'])) {
                    foreach (array_slice($data['jobs'], 0, $limit) as $job) {
                        $title = trim($job['title'] ?? '');
                        if (empty($title) || strlen($title) < 5) {
                            continue;
                        }

                        $location = $job['location'] ?? 'Philippines';

                        $jobs[] = [
                            'title' => $title,
                            'company' => $job['company'] ?? 'Company Not Specified',
                            'location' => $this->extractLocation($location),
                            'description' => substr(strip_tags($job['snippet'] ?? ''), 0, 500),
                            'url' => $job['link'] ?? '#',
                            'source' => 'Jooble',
                            'published_at' => isset($job['updated']) 
                                ? date('Y-m-d H:i:s', strtotime($job['updated'])) 
                                : now(),
                        ];
                    }
                }

                Log::info('Jooble API fetched jobs', [
                    'count' => count($jobs),
                    'total_results' => $data['totalCount'] ?? 'unknown',
                    'query' => $query,
                ]);

                return $jobs;
            } else {
                Log::warning('Jooble API request failed', [
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 300),
                ]);
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Error fetching from Jooble API', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
