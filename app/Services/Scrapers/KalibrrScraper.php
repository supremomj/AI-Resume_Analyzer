<?php

namespace App\Services\Scrapers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KalibrrScraper extends BaseScraper
{
    public function fetchJobs(string $query, int $limit): array
    {
        try {
            $url = "https://www.kalibrr.com/ph/jobs?q=" . urlencode($query);

            Log::info('Fetching jobs from Kalibrr', ['url' => $url]);

            $httpOptions = [
                'verify' => config('app.env') === 'production',
            ];

            $response = Http::timeout(8)
                ->withOptions($httpOptions)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ])
                ->get($url);

            if ($response->successful()) {
                $html = $response->body();
                $jobs = [];

                if (preg_match('/window\.__INITIAL_STATE__\s*=\s*({.+?});/', $html, $matches)) {
                    $data = json_decode($matches[1], true);
                    if (isset($data['jobs']['list']) && is_array($data['jobs']['list'])) {
                        foreach (array_slice($data['jobs']['list'], 0, $limit) as $job) {
                            $title = $job['name'] ?? $job['title'] ?? 'Job Title';
                            $location = $job['location']['name'] ?? 'Philippines';

                            if (isset($job['location']['city'])) {
                                $location = $job['location']['city'];
                                if (isset($job['location']['province']) && $job['location']['province'] !== $job['location']['city']) {
                                    $location .= ', ' . $job['location']['province'];
                                }
                            }

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
            }
            return [];

        } catch (\Exception $e) {
            Log::error('Error fetching from Kalibrr', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
