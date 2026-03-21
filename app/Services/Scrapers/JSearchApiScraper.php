<?php

namespace App\Services\Scrapers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JSearchApiScraper extends BaseScraper
{
    public function fetchJobs(string $query, int $limit): array
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
}
