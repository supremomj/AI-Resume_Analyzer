<?php

namespace App\Services\Scrapers;

interface JobScraperInterface
{
    /**
     * Fetch jobs from the specific source based on query and limit.
     *
     * @param string $query The search query (e.g., job title or skills)
     * @param int $limit The maximum number of jobs to return
     * @return array Array of job listings
     */
    public function fetchJobs(string $query, int $limit): array;
}
