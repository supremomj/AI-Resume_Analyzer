<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ResumeUploadService
{
    /**
     * Process a resume upload, store the file, and orchestrate AI analysis.
     * 
     * @param User $user
     * @param UploadedFile $file
     * @return array ['success' => bool, 'message' => string]
     */
    public function processUpload(User $user, UploadedFile $file): array
    {
        // 1. Validate MIME type & Extension
        $allowedMimeTypes = ['application/pdf'];
        $mimeType = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (!in_array($mimeType, $allowedMimeTypes) || $extension !== 'pdf') {
            Log::warning('Invalid file type for resume upload', [
                'mime_type' => $mimeType,
                'extension' => $extension,
                'user_id' => $user->id,
            ]);
            return ['success' => false, 'message' => 'Invalid file type. Only PDF files are allowed.'];
        }

        // 2. Generate secure filename & Store
        $filename = uniqid('resume_', true) . '_' . time() . '.' . $extension;
        
        try {
            $path = $file->storeAs('resumes', $filename, 'public');
            Log::info('Resume file stored', ['path' => $path]);
            
            $user->resume_path = $path;
            $user->save();
        } catch (\Exception $e) {
            Log::error('Error storing resume file', [
                'exception' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
            return ['success' => false, 'message' => 'Resume file storage failure.'];
        }

        // 3. Analyze resume with AI
        try {
            Log::info('Starting AI analysis', [
                'user_id' => $user->id,
                'resume_path' => $path,
            ]);
            
            $aiService = new ResumeAIService();
            $analysis = $aiService->analyzeResume($path);
            
            if ($analysis && !isset($analysis['error'])) {
                $formatted = $aiService->formatAnalysisResults($analysis);

                // Attempt job matching using AI analysis
                $this->attemptJobMatching($aiService, $formatted);
                
                // Save results
                $user->ai_analysis = $formatted;
                $user->resume_score = $formatted['resume_score'] ?? null;
                $user->recommended_field = $formatted['recommended_field'] ?? null;
                $user->save();

                Log::info('AI analysis completed successfully', [
                    'user_id' => $user->id,
                    'recommended_field' => $formatted['recommended_field'] ?? null,
                    'resume_score' => $formatted['resume_score'] ?? null,
                ]);

                return ['success' => true, 'message' => 'Resume uploaded and analyzed successfully!'];
            } else {
                $errorMessage = 'AI analysis is currently unavailable.';
                if ($analysis && isset($analysis['error'])) {
                    $errorMessage = $analysis['error'];
                    Log::error('AI Analysis returned error', ['error' => $errorMessage, 'user_id' => $user->id]);
                } else {
                    Log::error('AI Analysis failed to return valid data', ['user_id' => $user->id]);
                }
                return ['success' => false, 'message' => $errorMessage];
            }
        } catch (\Exception $e) {
            Log::error('Exception during AI analysis', [
                'exception' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
            return ['success' => false, 'message' => 'An error occurred during AI analysis.'];
        }
    }

    /**
     * Hit the Flask API to match jobs.
     * Modifies the $formatted array by reference if successful.
     */
    protected function attemptJobMatching(ResumeAIService $aiService, array &$formatted): void
    {
        try {
            $matchPayload = [
                'skills' => $formatted['skills'] ?? [],
                'recommended_field' => $formatted['recommended_field'] ?? null,
                'experience' => $formatted['experience'] ?? [],
            ];

            $matchResponse = Http::timeout(30)
                ->post($aiService->getApiUrl() . '/match_jobs', $matchPayload);

            if ($matchResponse->successful() && $matchResponse->json()) {
                $matchData = $matchResponse->json();
                if (isset($matchData['results']) && is_array($matchData['results'])) {
                    $formatted['recommended_jobs'] = $matchData['results'];
                }
            } else {
                Log::warning('Job matching failed or returned non-success status', [
                    'status' => $matchResponse->status(),
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Job matching exception', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
