<?php

namespace App\Http\Controllers;

use App\Services\JobFetchingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class JobController extends Controller
{
    protected $jobService;

    public function __construct(JobFetchingService $jobService)
    {
        $this->jobService = $jobService;
    }

    /**
     * Display jobs page with real job listings
     */
    public function index(Request $request): View
    {
        $filters = [
            'location' => $request->get('location'),
            'min_score' => $request->get('min_score'),
            'search' => $request->get('search'),
            'source' => $request->get('source'),
        ];
        
        $sort = $request->get('sort', 'match_score'); // Default: sort by match score
        $sortOrder = $request->get('order', 'desc'); // Default: descending
        
        // Check if force refresh is requested - always force refresh for real-time jobs
        $forceRefresh = $request->boolean('refresh', true); // Default to true for real-time
        
        // Initialize JobFetchingService with the authenticated user for real-time fetching
        $user = auth()->user();
        $jobFetchingService = new JobFetchingService($user);
        
        // Fetch more jobs to ensure variety (100 jobs, then filter and sort)
        $jobs = $jobFetchingService->getJobsWithFilters($filters, 100, $forceRefresh);
        
        // Apply sorting
        $jobs = $this->sortJobs($jobs, $sort, $sortOrder);
        
        // Filter out any sample jobs (shouldn't exist anymore, but just in case)
        $jobs = collect($jobs)->filter(fn($job) => !($job['is_sample'] ?? false) && ($job['source'] ?? '') !== 'Sample Jobs')->values()->all();
        
        Log::info('Jobs fetched for jobs page', [
            'user_id' => $user->id,
            'jobs_count' => count($jobs),
            'force_refresh' => $forceRefresh,
        ]);
        
        return view('jobs', [
            'jobs' => $jobs,
            'filters' => $filters,
            'sort' => $sort,
            'sortOrder' => $sortOrder,
        ]);
    }
    
    /**
     * Sort jobs based on criteria
     */
    protected function sortJobs(array $jobs, string $sort, string $order): array
    {
        usort($jobs, function ($a, $b) use ($sort, $order) {
            $valueA = $this->getSortValue($a, $sort);
            $valueB = $this->getSortValue($b, $sort);
            
            if ($order === 'asc') {
                return $valueA <=> $valueB;
            } else {
                return $valueB <=> $valueA;
            }
        });
        
        return $jobs;
    }
    
    /**
     * Get sort value for a job
     */
    protected function getSortValue(array $job, string $sort): mixed
    {
        return match($sort) {
            'match_score' => $job['match_score'] ?? $job['match_percentage'] ?? 0,
            'date' => isset($job['published_at']) ? strtotime($job['published_at']) : 0,
            'title' => strtolower($job['title'] ?? ''),
            'company' => strtolower($job['company'] ?? ''),
            default => $job['match_score'] ?? 0,
        };
    }

    /**
     * Get jobs for home page (AJAX endpoint)
     */
    public function getJobsForHome(Request $request)
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated',
                ], 401);
            }
            
            if (!$user->ai_analysis) {
                return response()->json([
                    'success' => true,
                    'jobs' => [],
                    'message' => 'Please upload your resume to get job recommendations',
                ]);
            }
            
            // Validate and sanitize limit parameter
            $limit = (int) $request->get('limit', 6);
            $limit = max(1, min(50, $limit)); // Clamp between 1 and 50
            
            // Check if force refresh is requested (bypass cache)
            $forceRefresh = $request->boolean('refresh', false);
            
            // Initialize JobFetchingService with the authenticated user
            $jobFetchingService = new JobFetchingService($user);
            $jobs = $jobFetchingService->fetchJobsForUser($limit, $forceRefresh);
            
            Log::info('Jobs fetched for home page', [
                'user_id' => $user->id,
                'jobs_count' => count($jobs),
                'force_refresh' => $forceRefresh,
                'limit' => $limit,
                'recommended_field' => $user->recommended_field ?? 'N/A',
            ]);
            
            return response()->json([
                'success' => true,
                'jobs' => $jobs,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching jobs for home', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch jobs. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}

