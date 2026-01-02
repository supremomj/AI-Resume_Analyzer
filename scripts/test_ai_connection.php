<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Services\ResumeAIService;
use App\Services\JobFetchingService;

echo "=== AI Connection Test ===\n\n";

// Test Flask API connection
$aiService = new ResumeAIService();
$apiUrl = $aiService->getApiUrl();

echo "Flask API URL: {$apiUrl}\n";
echo "Testing connection...\n";

$isConnected = $aiService->testConnection();

if ($isConnected) {
    echo "✅ Flask API is reachable!\n\n";
} else {
    echo "❌ Flask API is not reachable\n";
    echo "⚠️  Make sure your Flask API is running on: {$apiUrl}\n\n";
}

// Get user with AI analysis
$user = User::whereNotNull('ai_analysis')->first();

if (!$user) {
    echo "❌ No user found with AI analysis.\n";
    exit(1);
}

echo "=== User AI Analysis ===\n";
echo "User: {$user->email}\n";
echo "Recommended Field: " . ($user->recommended_field ?? 'N/A') . "\n";
echo "Resume Score: " . ($user->resume_score ?? 'N/A') . "\n";

$aiAnalysis = $user->ai_analysis ?? [];
echo "Skills Count: " . count($aiAnalysis['skills'] ?? []) . "\n";
echo "Experience Count: " . count($aiAnalysis['experience'] ?? []) . "\n";
echo "Education Count: " . count($aiAnalysis['education'] ?? []) . "\n\n";

if ($isConnected) {
    echo "=== Testing Job Matching ===\n";
    echo "Fetching 5 jobs to test AI matching...\n\n";
    
    $jobService = new JobFetchingService($user);
    $jobs = $jobService->fetchJobsForUser(5, true);
    
    echo "Jobs fetched: " . count($jobs) . "\n\n";
    
    if (count($jobs) > 0) {
        echo "Sample matched job:\n";
        $job = $jobs[0];
        echo "  Title: " . ($job['title'] ?? 'N/A') . "\n";
        echo "  Company: " . ($job['company'] ?? 'N/A') . "\n";
        echo "  Source: " . ($job['source'] ?? 'N/A') . "\n";
        echo "  AI Match Score: " . ($job['match_score'] ?? $job['match_percentage'] ?? 'N/A') . "%\n";
        echo "\n✅ AI matching is working!\n";
    } else {
        echo "⚠️  No jobs found. This could mean:\n";
        echo "  1. No jobs match the user's profile (match score < 40%)\n";
        echo "  2. Job sources are not returning results\n";
        echo "  3. Check logs for more details\n";
    }
} else {
    echo "⚠️  Cannot test job matching - Flask API is not reachable\n";
    echo "   The system will use fallback keyword matching instead.\n";
}

echo "\n=== Test Complete ===\n";

