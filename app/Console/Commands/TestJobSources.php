<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestJobSources extends Command
{
    protected $signature = 'jobs:test-sources';
    protected $description = 'Test job fetching from all sources to diagnose issues';

    public function handle()
    {
        $this->info('Testing job sources...');
        $this->newLine();
        
        $query = 'Software Engineer';
        $testSources = [
            'Indeed' => 'https://ph.indeed.com/rss?q=' . urlencode($query) . '&l=Philippines',
            'Kalibrr' => 'https://www.kalibrr.com/ph/jobs?q=' . urlencode($query),
            'OnlineJobs.ph' => 'https://www.onlinejobs.ph/jobseekers/jobsearch?keyword=' . urlencode($query),
        ];
        
        foreach ($testSources as $name => $url) {
            $this->info("Testing {$name}...");
            $this->line("URL: {$url}");
            
            try {
                $startTime = microtime(true);
                $response = Http::timeout(10)
                    ->withOptions(['verify' => config('app.env') === 'production'])
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                        'Accept' => '*/*',
                    ])
                    ->get($url);
                
                $duration = round((microtime(true) - $startTime) * 1000, 2);
                
                $this->line("Status: {$response->status()}");
                $this->line("Duration: {$duration}ms");
                $this->line("Body Length: " . strlen($response->body()) . " bytes");
                
                if ($response->successful()) {
                    $body = $response->body();
                    $preview = substr($body, 0, 200);
                    $this->line("Body Preview: {$preview}...");
                    
                    // Check for common blocking indicators
                    if (stripos($body, 'captcha') !== false || 
                        stripos($body, 'cloudflare') !== false ||
                        stripos($body, 'access denied') !== false ||
                        stripos($body, 'blocked') !== false) {
                        $this->warn("⚠️  Possible bot detection/blocking detected!");
                    }
                    
                    if ($name === 'Indeed') {
                        if (strpos($body, '<?xml') !== false || strpos($body, '<rss') !== false) {
                            $this->info("✅ Valid XML/RSS feed");
                        } else {
                            $this->warn("⚠️  Not a valid XML/RSS feed");
                        }
                    }
                } else {
                    $this->error("❌ Request failed");
                    $this->line("Response: " . substr($response->body(), 0, 200));
                }
            } catch (\Exception $e) {
                $this->error("❌ Exception: " . $e->getMessage());
            }
            
            $this->newLine();
        }
        
        $this->info('Test complete!');
    }
}

