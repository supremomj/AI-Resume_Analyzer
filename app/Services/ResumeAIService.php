<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ResumeAIService
{
    protected $apiUrl;

    public function __construct()
    {
        // Get Flask API URL from .env (default: http://localhost:8502)
        $this->apiUrl = env('AI_FLASK_API_URL', 'http://localhost:8502');

        Log::info('ResumeAIService initialized', [
            'api_url' => $this->apiUrl,
        ]);
    }

    /**
     * Get the Flask API URL
     */
    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }

    /**
     * Batch AI match: send resume context + all jobs to Flask for semantic scoring.
     * Returns array of ['job_index' => int, 'match_score' => int] or null on failure.
     */
    public function batchMatchJobs(array $resumeContext, array $jobs): ?array
    {
        try {
            // Prepare minimal job data to send (title + description only)
            $jobPayloads = [];
            foreach ($jobs as $job) {
                $jobPayloads[] = [
                    'title' => $job['title'] ?? '',
                    'description' => substr($job['description'] ?? '', 0, 500),
                ];
            }

            $response = Http::timeout(30) // Allow more time for batch AI processing
                ->post("{$this->apiUrl}/batch_match", [
                    'resume_context' => $resumeContext,
                    'jobs' => $jobPayloads,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Batch AI matching successful', [
                    'jobs_scored' => count($data['results'] ?? []),
                ]);
                return $data['results'] ?? null;
            }

            Log::warning('Batch AI match API returned error', [
                'status' => $response->status(),
                'body' => substr($response->body(), 0, 300),
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Batch AI match failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Test if Flask API is accessible
     */
    public function testConnection(): bool
    {
        try {
            // Try to connect to the Flask API (it might not have a GET endpoint, so we just check if server is reachable)
            $ch = curl_init($this->apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // If we get any response (even 404/405), the server is running
            $isReachable = ($httpCode > 0);

            Log::info('Flask API connection test', [
                'url' => $this->apiUrl,
                'http_code' => $httpCode,
                'reachable' => $isReachable,
            ]);

            return $isReachable;
        } catch (\Exception $e) {
            Log::error('Flask API connection test failed', [
                'url' => $this->apiUrl,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Analyze resume using the Flask AI API
     */
    public function analyzeResume(string $resumePath): ?array
    {
        try {
            // Validate path doesn't contain directory traversal
            if (strpos($resumePath, '..') !== false) {
                Log::error('Invalid resume path detected (directory traversal)', ['path' => $resumePath]);
                return null;
            }

            // Ensure path starts with 'resumes/' directory
            if (strpos($resumePath, 'resumes/') !== 0) {
                Log::error('Invalid resume path detected (not in resumes directory)', ['path' => $resumePath]);
                return null;
            }

            // Ensure path is within resumes directory
            $fullPath = storage_path('app/public/' . $resumePath);
            $realPath = realpath($fullPath);
            $basePath = realpath(storage_path('app/public/resumes'));

            // Verify file is within the resumes directory
            if ($realPath === false || strpos($realPath, $basePath) !== 0) {
                Log::error('Resume file path traversal attempt', ['path' => $resumePath]);
                return null;
            }

            if (!file_exists($realPath)) {
                Log::error('Resume file not found', ['path' => $realPath]);
                return null;
            }

            // Verify it's actually a file
            if (!is_file($realPath)) {
                Log::error('Resume path is not a file', ['path' => $realPath]);
                return null;
            }

            // Use the real path for file operations
            $fullPath = $realPath;

            // Try to analyze - connection test might fail even if API is running, so we'll try anyway
            // The actual API call will fail gracefully if Flask isn't running

            // Call Flask API with timeout and better error handling
            Log::info('Calling Flask API', [
                'url' => "{$this->apiUrl}/predict",
                'file_path' => $fullPath,
                'file_size' => filesize($fullPath),
            ]);

            // Prepare file for upload
            $fileName = basename($fullPath);

            Log::info('Preparing file for Flask API', [
                'file_name' => $fileName,
                'file_path' => $fullPath,
                'file_size_bytes' => filesize($fullPath),
                'file_size_mb' => round(filesize($fullPath) / 1024 / 1024, 2),
            ]);

            // Validate file size before reading (prevent memory exhaustion)
            $fileSize = filesize($fullPath);
            $maxFileSize = 10 * 1024 * 1024; // 10MB max

            if ($fileSize > $maxFileSize) {
                Log::error('Resume file too large', [
                    'file_size' => $fileSize,
                    'max_size' => $maxFileSize,
                ]);
                return null;
            }

            // Read file contents safely
            $fileContents = @file_get_contents($fullPath);
            if ($fileContents === false) {
                Log::error('Failed to read resume file', ['path' => $fullPath]);
                return null;
            }

            // Use file path directly with attach() - Laravel will handle the multipart upload
            $response = Http::timeout(120) // Increased timeout for AI processing
                ->attach(
                    'resume',
                    $fileContents,
                    $fileName
                )->post("{$this->apiUrl}/predict");

            Log::info('Flask API Response', [
                'url' => "{$this->apiUrl}/predict",
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body_preview' => substr($response->body(), 0, 500),
                'headers' => $response->headers(),
                'has_json' => $response->json() !== null,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Check which Flask API version is running
                $apiVersion = $data['api_version'] ?? 'UNKNOWN (old flask_api.py?)';

                Log::info('AI Resume Analysis Success', [
                    'resume_path' => $resumePath,
                    'api_version' => $apiVersion,
                    'has_skills' => isset($data['skills']),
                    'recommended_field' => $data['recommended_field'] ?? null,
                    'data_keys' => array_keys($data),
                    'has_ph_salary_range' => isset($data['ph_salary_range']),
                    'ph_salary_range' => $data['ph_salary_range'] ?? 'NOT IN RESPONSE',
                    'ph_experience_level' => $data['ph_experience_level'] ?? 'NOT IN RESPONSE',
                ]);

                // Warn if using old API
                if (!isset($data['api_version']) || strpos($apiVersion, 'v2.0') === false) {
                    Log::warning('⚠️ WARNING: Flask API appears to be running OLD code!');
                    Log::warning('API Version received: ' . ($apiVersion ?? 'NOT SET'));
                    Log::warning('Expected: Should contain "v2.0"');
                    Log::warning('Please STOP Flask API (Ctrl+C) and RESTART it!');
                    Log::warning('There may be multiple Flask APIs running - check with: netstat -ano ^| findstr :8502');
                }

                return $data;
            } else {
                $errorBody = $response->body();
                $errorPreview = strlen($errorBody) > 500 ? substr($errorBody, 0, 500) . '...' : $errorBody;

                Log::error('AI Resume Analysis Failed', [
                    'status' => $response->status(),
                    'body' => $errorPreview,
                    'url' => "{$this->apiUrl}/predict",
                    'file_path' => $fullPath,
                    'file_exists' => file_exists($fullPath),
                    'file_size' => file_exists($fullPath) ? filesize($fullPath) : 0,
                ]);

                // Return error data so controller can handle it
                return ['error' => 'Flask API returned status ' . $response->status() . ': ' . $errorPreview];
            }
        } catch (\Exception $e) {
            Log::error('AI Resume Analysis Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Get formatted analysis results for display
     */
    public function formatAnalysisResults(array $analysis): array
    {
        // Derive degree/program if not explicitly provided by the AI
        $deriveDegree = function (array $data): ?string {
            $directKeys = ['degree', 'program', 'course', 'education_degree'];
            foreach ($directKeys as $k) {
                if (!empty($data[$k]) && is_string($data[$k])) {
                    return trim($data[$k]);
                }
            }

            $looksLikeDegree = function ($text): bool {
                if (!is_string($text)) {
                    return false;
                }
                $v = strtolower(trim($text));
                if ($v === '' || str_contains($v, '@')) {
                    return false;
                }
                $terms = [
                    'bachelor',
                    'master',
                    'doctor',
                    'degree',
                    'engineering',
                    'science',
                    'technology',
                    'management',
                    'accountancy',
                    'accounting',
                    'finance',
                    'education',
                    'psychology',
                    'nursing',
                    'pharmacy',
                    'communication',
                    'journalism',
                    'mathematics',
                    'statistics',
                    'business',
                    'administration',
                    'economics',
                    'marketing',
                    'civil',
                    'mechanical',
                    'electrical',
                    'computer',
                    'information',
                    'systems',
                    'hospitality',
                    'tourism',
                    'biology',
                    'chemistry',
                    'physics',
                    'public health',
                    'political science',
                    'criminology',
                    'analytics',
                    'data science'
                ];
                foreach ($terms as $term) {
                    if (str_contains($v, $term)) {
                        return true;
                    }
                }
                if (preg_match('/\b(bsit|bscs|bsis|bsa|bsba|bsce|bsie|bsme|bsee|bsn|bsmt|bspsych|mba|jd|md|mscs|msit|ms|ma)\b/i', $text)) {
                    return true;
                }
                return false;
            };

            if (!empty($data['education']) && is_array($data['education'])) {
                foreach ($data['education'] as $edu) {
                    if (is_array($edu)) {
                        foreach ($edu as $val) {
                            if (is_string($val) && strlen($val) < 100 && $looksLikeDegree($val)) {
                                return trim($val);
                            }
                        }
                    } elseif (is_string($edu) && strlen($edu) < 100 && $looksLikeDegree($edu)) {
                        return trim($edu);
                    }
                }
            } elseif (!empty($data['education']) && is_string($data['education']) && strlen($data['education']) < 100 && $looksLikeDegree($data['education'])) {
                return trim($data['education']);
            }

            return null;
        };

        $degreeVal = $deriveDegree($analysis);

        return [
            'skills' => $analysis['skills'] ?? [],
            'experience' => $analysis['experience'] ?? [],
            'education' => $analysis['education'] ?? [],
            'resume_score' => $analysis['resume_score'] ?? 0,
            'recommended_field' => $analysis['recommended_field'] ?? 'Software Engineering',
            'recommended_fields' => $analysis['recommended_fields'] ?? [],
            'recommended_skills' => $analysis['recommended_skills'] ?? [],
            'recommended_courses' => $analysis['recommended_courses'] ?? [],
            'recommended_jobs' => $analysis['recommended_jobs'] ?? [],
            'name' => $analysis['name'] ?? null,
            'email' => $analysis['email'] ?? null,
            'mobile_number' => $analysis['mobile_number'] ?? null,
            // Philippine-specific fields
            'ph_salary_range' => $analysis['ph_salary_range'] ?? null,
            'ph_experience_level' => $analysis['ph_experience_level'] ?? null,
            // Degree/Program (normalize varied keys + derived)
            'degree' => $analysis['degree'] ?? ($analysis['program'] ?? ($analysis['course'] ?? ($analysis['education_degree'] ?? $degreeVal))),
            'program' => $analysis['program'] ?? $degreeVal,
        ];
    }
}




