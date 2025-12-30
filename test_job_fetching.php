<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Services\JobFetchingService;

echo "=== Job Fetching Service Test ===\n\n";

// Get first user with AI analysis
$user = User::whereNotNull('ai_analysis')->first();

if (!$user) {
    echo "❌ No user found with AI analysis. Please upload a resume first.\n";
    exit(1);
}

echo "✅ Found user: {$user->email}\n";
echo "✅ User has AI analysis: " . (!empty($user->ai_analysis) ? 'Yes' : 'No') . "\n\n";

// Create service instance
$service = new JobFetchingService($user);

echo "Fetching jobs...\n";
echo "---\n\n";

// Fetch jobs
$jobs = $service->fetchJobsForUser(10, true);

echo "Total jobs fetched: " . count($jobs) . "\n\n";

if (count($jobs) > 0) {
    // Group by source
    $sources = [];
    foreach ($jobs as $job) {
        $source = $job['source'] ?? 'Unknown';
        $sources[$source] = ($sources[$source] ?? 0) + 1;
    }
    
    echo "Jobs by source:\n";
    foreach ($sources as $source => $count) {
        echo "  - {$source}: {$count}\n";
    }
    
    echo "\n---\n";
    echo "Sample job:\n";
    echo "  Title: " . ($jobs[0]['title'] ?? 'N/A') . "\n";
    echo "  Company: " . ($jobs[0]['company'] ?? 'N/A') . "\n";
    echo "  Location: " . ($jobs[0]['location'] ?? 'N/A') . "\n";
    echo "  Source: " . ($jobs[0]['source'] ?? 'N/A') . "\n";
    echo "  Match Score: " . ($jobs[0]['match_percentage'] ?? $jobs[0]['match_score'] ?? 'N/A') . "%\n";
} else {
    echo "⚠️  No jobs found. This could mean:\n";
    echo "  1. No jobs match the user's profile\n";
    echo "  2. API keys are not configured (check .env file)\n";
    echo "  3. Web scraping sources are not returning results\n";
    echo "  4. Check logs: storage/logs/laravel.log\n";
}

echo "\n=== Test Complete ===\n";

