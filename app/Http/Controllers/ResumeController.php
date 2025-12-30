<?php

namespace App\Http\Controllers;

use App\Services\ResumeAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ResumeController extends Controller
{
    public function upload(Request $request)
    {
        Log::info('Resume upload request received', ['user_id' => Auth::id()]);

        $validated = $request->validate([
            'resume' => 'required|mimes:pdf|max:5120',
        ], [
            'resume.mimes' => 'Only PDF files are allowed. Please upload a PDF file.',
        ]);

        $user = Auth::user();

        if (!$user) {
            Log::error('Authenticated user not found.');
            return back()->withErrors(['User not authenticated.']);
        }

        $file = $request->file('resume');
        Log::info('Resume file received', ['file_present' => $file !== null]);

        if (!$file || !$file->isValid()) {
            Log::error('No resume file detected or invalid file.');
            return back()->withErrors(['No valid file detected.']);
        }

        // Additional security: Validate MIME type - PDF only
        $allowedMimeTypes = [
            'application/pdf',
        ];
        
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, $allowedMimeTypes)) {
            Log::warning('Invalid MIME type for resume upload', [
                'mime_type' => $mimeType,
                'user_id' => $user->id,
            ]);
            return back()->withErrors(['Invalid file type. Only PDF files are allowed.']);
        }

        // Sanitize file extension - PDF only
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = ['pdf'];
        if (!in_array($extension, $allowedExtensions)) {
            Log::warning('Invalid file extension for resume upload', [
                'extension' => $extension,
                'user_id' => $user->id,
            ]);
            return back()->withErrors(['Invalid file extension. Only PDF files are allowed.']);
        }

        // Generate secure filename (no user input in filename)
        $filename = uniqid('resume_', true) . '_' . time() . '.' . $extension;
        Log::info('Generated filename', ['filename' => $filename]);

        try {
            $path = $file->storeAs('resumes', $filename, 'public');
            Log::info('Resume file stored', ['path' => $path]);
        } catch (\Exception $e) {
            Log::error('Error storing resume file', [
                'exception' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
            return back()->withErrors(['Resume file storage failure.']);
        }

        $user->resume_path = $path;
        $user->save();

        Log::info('resume_path saved to user', [
            'user_id' => $user->id,
            'resume_path' => $path,
            // Removed full user array to prevent logging sensitive data
        ]);

        // Analyze resume with AI
        try {
            Log::info('Starting AI analysis', [
                'user_id' => $user->id,
                'resume_path' => $path,
                'api_url' => env('AI_FLASK_API_URL', 'http://localhost:8502'),
            ]);
            
            $aiService = new ResumeAIService();
            $analysis = $aiService->analyzeResume($path);
            
            if ($analysis && !isset($analysis['error'])) {
                $formatted = $aiService->formatAnalysisResults($analysis);

                // Attempt job matching using AI analysis
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
                            'body' => substr($matchResponse->body(), 0, 300),
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Job matching exception', [
                        'error' => $e->getMessage(),
                    ]);
                }

                $user->ai_analysis = $formatted;
                $user->resume_score = $formatted['resume_score'] ?? null;
                $user->recommended_field = $formatted['recommended_field'] ?? null;
                $user->save();
                
                // Refresh user to ensure latest data
                $user = $user->fresh();

                Log::info('AI analysis completed successfully', [
                    'user_id' => $user->id,
                    'recommended_field' => $formatted['recommended_field'],
                    'resume_score' => $formatted['resume_score'],
                    'skills_count' => count($formatted['skills'] ?? []),
                    'has_ai_analysis' => !empty($user->ai_analysis),
                    'has_ph_salary_range' => isset($formatted['ph_salary_range']) && !empty($formatted['ph_salary_range']),
                    'ph_salary_range' => $formatted['ph_salary_range'] ?? 'NOT SET',
                    'ph_experience_level' => $formatted['ph_experience_level'] ?? 'NOT SET',
                    'all_keys' => array_keys($formatted),
                ]);
            } else {
                $errorMessage = 'AI analysis is currently unavailable.';
                if ($analysis && isset($analysis['error'])) {
                    $errorMessage = $analysis['error'];
                }
                
                Log::warning('AI analysis returned null or error', [
                    'user_id' => $user->id,
                    'analysis' => $analysis,
                    'error_message' => $errorMessage,
                ]);
                
                return redirect()->route('resume.upload')->with([
                    'resume' => htmlspecialchars($file->getClientOriginalName(), ENT_QUOTES, 'UTF-8'),
                    'status' => 'Resume uploaded successfully!',
                    'ai_error' => htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') . ' Please make sure your Flask API is running.',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('AI analysis exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id,
            ]);
            
            return redirect()->route('resume.upload')->with([
                'resume' => htmlspecialchars($file->getClientOriginalName(), ENT_QUOTES, 'UTF-8'),
                'status' => 'Resume uploaded successfully!',
                'ai_error' => 'AI analysis failed. Please try again later.',
            ]);
        }

        return redirect()->route('resume.upload')->with([
            'resume' => htmlspecialchars($file->getClientOriginalName(), ENT_QUOTES, 'UTF-8'),
            'status' => 'Resume uploaded and analyzed successfully!',
        ]);
    }
}
