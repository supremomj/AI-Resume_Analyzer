<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Services\JobFetchingService;
use Illuminate\Support\Facades\Config;

echo "=== Testing API Job Fetching (Philippines) ===\n\n";

// Check API configurations
echo "=== API Configuration ===\n";
$adzuna = Config::get('job_apis.adzuna');
$jooble = Config::get('job_apis.jooble');
$jobdata = Config::get('job_apis.jobdata');
$scraper = Config::get('job_apis.scraperapi');

echo "Adzuna: " . ($adzuna['enabled'] && !empty($adzuna['app_id']) ? '✅ Configured' : '❌ Not Configured') . "\n";
echo "Jooble: " . ($jooble['enabled'] && !empty($jooble['api_key']) && $jooble['api_key'] !== 'your_api_key_here' ? '✅ Configured' : '❌ Not Configured') . "\n";
echo "Jobdata: " . ($jobdata['enabled'] && !empty($jobdata['api_key']) && $jobdata['api_key'] !== 'your_api_key_here' ? '✅ Configured' : '❌ Not Configured') . "\n";
echo "ScraperAPI: " . ($scraper['enabled'] && !empty($scraper['api_key']) ? '✅ Configured' : '❌ Not Configured') . "\n\n";

// Get user with AI analysis
$user = User::whereNotNull('ai_analysis')->first();

if (!$user) {
    echo "❌ No user found with AI analysis.\n";
    exit(1);
}

echo "=== User Info ===\n";
echo "User: {$user->email}\n";
echo "Recommended Field: " . ($user->recommended_field ?? 'N/A') . "\n";
$skills = $user->ai_analysis['skills'] ?? [];
echo "Skills: " . count($skills) . "\n\n";

echo "=== Testing Job Fetching ===\n";
echo "Fetching jobs using configured APIs...\n\n";

$service = new JobFetchingService($user);
$jobs = $service->fetchJobsForUser(20, true); // Force refresh

echo "Total jobs fetched: " . count($jobs) . "\n\n";

if (count($jobs) > 0) {
    // Group by source
    $sources = [];
    foreach ($jobs as $job) {
        $source = $job['source'] ?? 'Unknown';
        $sources[$source] = ($sources[$source] ?? 0) + 1;
    }
    
    echo "=== Jobs by Source ===\n";
    foreach ($sources as $source => $count) {
        echo "  {$source}: {$count} jobs\n";
    }
    
    // Show API-sourced jobs
    $apiJobs = array_filter($jobs, function($job) {
        $apiSources = ['Adzuna', 'Jooble', 'Jobdata', 'LinkedIn'];
        return in_array($job['source'] ?? '', $apiSources);
    });
    
    echo "\n=== API-Sourced Jobs ===\n";
    echo "Total from APIs: " . count($apiJobs) . "\n";
    
    if (count($apiJobs) > 0) {
        echo "\nSample API jobs:\n";
        foreach (array_slice($apiJobs, 0, 5) as $job) {
            echo "  - " . ($job['title'] ?? 'N/A') . " @ " . ($job['company'] ?? 'N/A') . " (via " . ($job['source'] ?? 'N/A') . ")\n";
        }
    }
    
    // Show all jobs with match scores
    echo "\n=== Top 10 Jobs (by Match Score) ===\n";
    usort($jobs, function($a, $b) {
        $scoreA = $a['match_score'] ?? $a['match_percentage'] ?? 0;
        $scoreB = $b['match_score'] ?? $b['match_percentage'] ?? 0;
        return $scoreB <=> $scoreA;
    });
    
    foreach (array_slice($jobs, 0, 10) as $job) {
        $score = $job['match_score'] ?? $job['match_percentage'] ?? 0;
        echo sprintf("  [%d%%] %s @ %s (via %s)\n", 
            $score,
            substr($job['title'] ?? 'N/A', 0, 40),
            substr($job['company'] ?? 'N/A', 0, 20),
            $job['source'] ?? 'N/A'
        );
    }
} else {
    echo "⚠️  No jobs found.\n";
    echo "\nPossible reasons:\n";
    echo "  1. APIs are not configured or keys are invalid\n";
    echo "  2. No jobs match the user's profile (match score < 40%)\n";
    echo "  3. Job sources are not returning results\n";
    echo "  4. Check logs: storage/logs/laravel.log\n";
}

echo "\n=== Test Complete ===\n";

