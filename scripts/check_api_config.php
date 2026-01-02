<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== API Configuration Check ===\n\n";

// Check Adzuna
$adzuna = config('job_apis.adzuna');
echo "Adzuna API:\n";
echo "  Enabled: " . ($adzuna['enabled'] ? '✅ Yes' : '❌ No') . "\n";
echo "  APP_ID: " . (!empty($adzuna['app_id']) ? '✅ Set (' . substr($adzuna['app_id'], 0, 8) . '...)' : '❌ Not Set') . "\n";
echo "  APP_KEY: " . (!empty($adzuna['app_key']) ? '✅ Set (' . substr($adzuna['app_key'], 0, 8) . '...)' : '❌ Not Set') . "\n";
echo "  Country: {$adzuna['country']}\n\n";

// Check Jooble
$jooble = config('job_apis.jooble');
echo "Jooble API:\n";
echo "  Enabled: " . ($jooble['enabled'] ? '✅ Yes' : '❌ No') . "\n";
echo "  API_KEY: " . (!empty($jooble['api_key']) && $jooble['api_key'] !== 'your_api_key_here' ? '✅ Set (' . substr($jooble['api_key'], 0, 8) . '...)' : '⚠️  Not Set (placeholder)') . "\n";
echo "  Country: {$jooble['country']}\n\n";

// Check Jobdata
$jobdata = config('job_apis.jobdata');
echo "Jobdata API:\n";
echo "  Enabled: " . ($jobdata['enabled'] ? '✅ Yes' : '❌ No') . "\n";
echo "  API_KEY: " . (!empty($jobdata['api_key']) && $jobdata['api_key'] !== 'your_api_key_here' ? '✅ Set (' . substr($jobdata['api_key'], 0, 8) . '...)' : '⚠️  Not Set (placeholder)') . "\n\n";

// Check ScraperAPI
$scraper = config('job_apis.scraperapi');
echo "ScraperAPI:\n";
echo "  Enabled: " . ($scraper['enabled'] ? '✅ Yes' : '❌ No') . "\n";
echo "  API_KEY: " . (!empty($scraper['api_key']) ? '✅ Set (' . substr($scraper['api_key'], 0, 8) . '...)' : '❌ Not Set') . "\n";
echo "  Use For: " . implode(', ', $scraper['use_for']) . "\n\n";

// Summary
echo "=== Summary ===\n";
$configured = [];
if ($adzuna['enabled'] && !empty($adzuna['app_id']) && !empty($adzuna['app_key'])) {
    $configured[] = 'Adzuna';
}
if ($jooble['enabled'] && !empty($jooble['api_key']) && $jooble['api_key'] !== 'your_api_key_here') {
    $configured[] = 'Jooble';
}
if ($jobdata['enabled'] && !empty($jobdata['api_key']) && $jobdata['api_key'] !== 'your_api_key_here') {
    $configured[] = 'Jobdata';
}
if ($scraper['enabled'] && !empty($scraper['api_key'])) {
    $configured[] = 'ScraperAPI';
}

if (count($configured) > 0) {
    echo "✅ Configured APIs: " . implode(', ', $configured) . "\n";
} else {
    echo "⚠️  No APIs fully configured yet\n";
}

echo "\n=== Status ===\n";
echo "Your system will use:\n";
echo "  - API-based fetching: " . (count($configured) > 0 ? '✅ Yes (' . count($configured) . ' APIs)' : '❌ No (using web scraping only)') . "\n";
echo "  - Web scraping fallback: ✅ Always available\n";

