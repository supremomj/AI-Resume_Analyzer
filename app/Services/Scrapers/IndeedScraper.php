<?php

namespace App\Services\Scrapers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IndeedScraper extends BaseScraper
{
    public function fetchJobs(string $query, int $limit): array
    {
        try {
            // Indeed has an RSS feed we can use
            $location = 'Philippines';
            $url = "https://ph.indeed.com/rss?q=" . urlencode($query) . "&l=" . urlencode($location);

            Log::info('Fetching jobs from Indeed', ['url' => $url]);

            $httpOptions = [
                'verify' => config('app.env') === 'production',
            ];

            $response = Http::timeout(8)
                ->withOptions($httpOptions)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                    'Accept' => 'application/rss+xml, application/xml, text/xml, */*',
                ])
                ->get($url);

            if ($response->successful()) {
                $body = $response->body();
                $xml = @simplexml_load_string($body);
                $jobs = [];

                if ($xml === false) {
                    return [];
                }

                if ($xml && isset($xml->channel->item)) {
                    foreach ($xml->channel->item as $item) {
                        $description = strip_tags((string) $item->description);
                        $title = (string) $item->title;

                        $extractedLocation = $this->extractLocation($description . ' ' . $title);

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
                }

                return $jobs;
            }
            return [];

        } catch (\Exception $e) {
            Log::error('Error fetching from Indeed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function extractCompanyFromIndeed(string $description): string
    {
        if (preg_match('/-\s*([A-Z][A-Za-z0-9\s&.,\-()]+?)(?:\s*-\s*[A-Z]|$)/', $description, $matches)) {
            $company = trim($matches[1]);
            $company = preg_replace('/\s*-\s*(Philippines|PH|Manila|Makati|BGC)$/i', '', $company);
            if (strlen($company) > 2 && strlen($company) < 100) {
                return trim($company);
            }
        }

        if (preg_match('/\b([A-Z][A-Za-z0-9\s&.,\-()]{3,50})\s+(?:Philippines|PH|Inc|Corp|Corporation|Ltd|Limited|Company)$/i', $description, $matches)) {
            return trim($matches[1]);
        }

        return 'Company Not Specified';
    }
}
