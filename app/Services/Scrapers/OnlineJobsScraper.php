<?php

namespace App\Services\Scrapers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OnlineJobsScraper extends BaseScraper
{
    public function fetchJobs(string $query, int $limit): array
    {
        try {
            $url = "https://www.onlinejobs.ph/jobseekers/jobsearch?keyword=" . urlencode($query);

            Log::info('Fetching jobs from OnlineJobs.ph', [
                'url' => $url,
                'query' => $query,
                'limit' => $limit
            ]);

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

                $jobContainers = [];
                if (preg_match_all('/<(?:li|div)[^>]*class="[^"]*(?:job|listing|result|item)[^"]*"[^>]*>(.*?)<\/(?:li|div)>/is', $html, $containerMatches)) {
                    $jobContainers = $containerMatches[0];
                } elseif (preg_match_all('/<div[^>]*data-job[^>]*>(.*?)<\/div>/is', $html, $containerMatches)) {
                    $jobContainers = $containerMatches[0];
                }

                foreach (array_slice($jobContainers, 0, $limit * 3) as $container) {
                    if (preg_match('/href="(\/jobseekers\/job\/[^"]+)"/i', $container, $urlMatch)) {
                        $jobUrl = 'https://www.onlinejobs.ph' . $urlMatch[1];
                        $jobId = basename($urlMatch[1]);
                        $jobTitle = null;

                        if (preg_match('/<a[^>]*href="\/jobseekers\/job\/' . preg_quote($jobId, '/') . '"[^>]*>(.*?)<\/a>/is', $container, $linkMatch)) {
                            $linkContent = $linkMatch[1];

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

                            if (preg_match('/^(.*?)\s+(?:we are|looking for|join|hiring|need|fast-growing|skilled|this role|technology|data engineering|based|developer|create|role is)/i', $linkText, $titlePart)) {
                                $linkText = trim($titlePart[1]);
                            }

                            if (
                                stripos($linkText, 'see more') === false &&
                                stripos($linkText, 'view more') === false &&
                                stripos($linkText, '...') === false &&
                                stripos($linkText, 'click here') === false &&
                                stripos($linkText, 'apply now') === false &&
                                !preg_match('/^(we are|looking for|join|hiring|need|fast-growing|skilled|this role|technology|data engineering|based|developer|create|role is)/i', $linkText) &&
                                !preg_match('/\b(br|html|div|span|p)\b/i', $linkText) &&
                                strlen($linkText) > 5 &&
                                strlen($linkText) < 150
                            ) {
                                $jobTitle = $linkText;
                            }
                        }

                        if (!$jobTitle && preg_match('/<(?:h[234]|strong|b)[^>]*>([^<]{10,150})<\/(?:h[234]|strong|b)>/i', $container, $titleMatch)) {
                            $potentialTitle = trim(strip_tags($titleMatch[1]));
                            $potentialTitle = html_entity_decode($potentialTitle, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                            
                            if (
                                !preg_match('/^(we are|looking for|join|hiring|need|fast-growing|skilled|this role)/i', $potentialTitle) &&
                                strlen($potentialTitle) > 5 &&
                                strlen($potentialTitle) < 150
                            ) {
                                $jobTitle = $potentialTitle;
                            }
                        }

                        if (!$jobTitle && preg_match('/data-title="([^"]{10,150})"/i', $container, $dataMatch)) {
                            $jobTitle = trim(html_entity_decode($dataMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                        }

                        if (!$jobTitle) {
                            $contextPattern = '/.{0,300}href="\/jobseekers\/job\/' . preg_quote($jobId, '/') . '".{0,300}/is';
                            if (preg_match($contextPattern, $html, $contextMatch)) {
                                $context = $contextMatch[0];
                                if (preg_match('/<(?:h[234]|strong|b)[^>]*>([^<]{10,200})<\/(?:h[234]|strong|b)>/i', $context, $contextTitle)) {
                                    $jobTitle = trim(strip_tags($contextTitle[1]));
                                }
                            }
                        }

                        $company = 'Company Not Specified';
                        if (preg_match('/<span[^>]*class="[^"]*company[^"]*"[^>]*>([^<]+)<\/span>/i', $container, $companyMatch)) {
                            $company = trim(strip_tags($companyMatch[1]));
                        } elseif (preg_match('/<div[^>]*class="[^"]*employer[^"]*"[^>]*>([^<]+)<\/div>/i', $container, $employerMatch)) {
                            $company = trim(strip_tags($employerMatch[1]));
                        } elseif (preg_match('/\b([A-Z][A-Za-z0-9\s&.,\-()]{3,50})\s+(?:Philippines|PH|Inc|Corp|Corporation|Ltd|Limited|Company|Technologies|Solutions|Services)\b/i', $container, $companyTextMatch)) {
                            $company = trim(strip_tags($companyTextMatch[1]));
                        }

                        $company = html_entity_decode($company, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        if (empty($company) || strlen($company) < 2) {
                            $company = 'Company Not Specified';
                        }

                        $description = '';
                        if (preg_match('/<p[^>]*class="[^"]*description[^"]*"[^>]*>(.*?)<\/p>/is', $container, $descMatch)) {
                            $description = trim(strip_tags($descMatch[1]));
                        } elseif (preg_match('/<div[^>]*class="[^"]*summary[^"]*"[^>]*>(.*?)<\/div>/is', $container, $summaryMatch)) {
                            $description = trim(strip_tags($summaryMatch[1]));
                        } elseif (preg_match('/<(?:h[234]|strong|b)[^>]*>' . preg_quote($jobTitle, '/') . '<\/(?:h[234]|strong|b)>(.*?)(?:<a|<div|<h[234]|$)/is', $container, $afterTitleMatch)) {
                            $potentialDesc = trim(strip_tags($afterTitleMatch[1]));
                            if ($potentialDesc && $potentialDesc !== $jobTitle && strlen($potentialDesc) > 20 && strlen($potentialDesc) < 500) {
                                $description = $potentialDesc;
                            }
                        } else {
                            $containerText = strip_tags($container);
                            $containerText = str_ireplace($jobTitle, '', $containerText);
                            if ($containerText && strlen($containerText) > 30 && strlen($containerText) < 500) {
                                $description = substr($containerText, 0, 200);
                            }
                        }

                        if ($jobTitle) {
                            $jobTitle = html_entity_decode($jobTitle, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                            $jobTitle = preg_replace('/^(Job|Position|Opening|Vacancy|Hiring):\s*/i', '', trim($jobTitle));
                        }

                        if ($description && strtolower(trim($description)) === strtolower(trim($jobTitle))) {
                            $description = '';
                        }

                        if (
                            $jobTitle &&
                            strlen($jobTitle) > 5 &&
                            strlen($jobTitle) < 150 &&
                            stripos($jobTitle, 'see more') === false &&
                            !preg_match('/^(we are|looking for|join|hiring|need|fast-growing|skilled|this role)/i', $jobTitle)
                        ) {
                            $location = 'Philippines';
                            if (preg_match('/<span[^>]*class="[^"]*location[^"]*"[^>]*>([^<]+)<\/span>/i', $container, $locMatch)) {
                                $location = trim(strip_tags($locMatch[1]));
                            } else {
                                $location = $this->extractLocation($container);
                            }

                            $jobs[] = [
                                'title' => $jobTitle,
                                'company' => $company,
                                'location' => $location,
                                'description' => $description ?: '',
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

                if (empty($jobs) && preg_match_all('/href="(\/jobseekers\/job\/(\d+))"/i', $html, $urlMatches, PREG_SET_ORDER)) {
                    foreach (array_slice($urlMatches, 0, $limit) as $urlMatch) {
                        $jobUrl = 'https://www.onlinejobs.ph' . $urlMatch[1];
                        $jobId = $urlMatch[2];

                        $jobs[] = [
                            'title' => "Job Opportunity #{$jobId}",
                            'company' => 'Company Not Specified',
                            'location' => 'Philippines',
                            'description' => "Job posting from OnlineJobs.ph",
                            'url' => $jobUrl,
                            'source' => 'OnlineJobs.ph',
                            'published_at' => now(),
                        ];
                    }
                }

                return $jobs;
            }
            return [];

        } catch (\Exception $e) {
            Log::error('Error fetching from OnlineJobs.ph', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
