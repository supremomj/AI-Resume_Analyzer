<?php

namespace App\Http\Controllers;

use App\Models\JobViewHistory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class JobViewHistoryController extends Controller
{
    /**
     * Track a job view.
     */
    public function track(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'job_title' => ['required', 'string', 'max:255'],
            'job_url' => ['required', 'url', 'max:500'],
            'company' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:255'],
            'match_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        try {
            $jobUrlHash = hash('sha256', $validated['job_url']);
            
            // Find existing view or create new one
            $viewHistory = JobViewHistory::where('user_id', Auth::id())
                ->where('job_url_hash', $jobUrlHash)
                ->first();

            if ($viewHistory) {
                // Update view count and timestamp
                $viewHistory->increment('view_count');
                $viewHistory->update(['viewed_at' => now()]);
            } else {
                // Create new view history entry
                $viewHistory = JobViewHistory::create([
                    'user_id' => Auth::id(),
                    'job_title' => $validated['job_title'],
                    'job_url' => $validated['job_url'],
                    'job_url_hash' => $jobUrlHash,
                    'company' => $validated['company'] ?? null,
                    'location' => $validated['location'] ?? null,
                    'source' => $validated['source'] ?? null,
                    'match_score' => $validated['match_score'] ?? null,
                    'description' => $validated['description'] ?? null,
                    'viewed_at' => now(),
                ]);
            }

            // Limit to last 50 jobs per user (cleanup old entries)
            $this->cleanupOldViews();

            return response()->json([
                'success' => true,
                'message' => 'Job view tracked',
            ]);
        } catch (\Exception $e) {
            Log::error('Error tracking job view', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to track job view',
            ], 500);
        }
    }

    /**
     * Get recently viewed jobs for the authenticated user.
     */
    public function getRecentlyViewed(): JsonResponse
    {
        $recentlyViewed = JobViewHistory::where('user_id', Auth::id())
            ->orderBy('viewed_at', 'desc')
            ->limit(10)
            ->get()
            ->unique('job_url_hash')
            ->take(10)
            ->values();

        return response()->json([
            'success' => true,
            'jobs' => $recentlyViewed,
        ]);
    }

    /**
     * Clear view history for the authenticated user.
     */
    public function clearHistory(): JsonResponse
    {
        try {
            JobViewHistory::where('user_id', Auth::id())->delete();

            return response()->json([
                'success' => true,
                'message' => 'View history cleared successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error clearing view history', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to clear view history',
            ], 500);
        }
    }

    /**
     * Cleanup old view history entries (keep only last 50 per user).
     */
    private function cleanupOldViews(): void
    {
        $userId = Auth::id();
        $count = JobViewHistory::where('user_id', $userId)->count();

        if ($count > 50) {
            $toDelete = $count - 50;
            JobViewHistory::where('user_id', $userId)
                ->orderBy('viewed_at', 'asc')
                ->limit($toDelete)
                ->delete();
        }
    }
}
