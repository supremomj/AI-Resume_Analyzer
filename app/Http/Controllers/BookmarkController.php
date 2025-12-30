<?php

namespace App\Http\Controllers;

use App\Models\JobBookmark;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BookmarkController extends Controller
{
    /**
     * Display the bookmarks page.
     */
    public function show(): View
    {
        $bookmarks = JobBookmark::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('bookmarks', [
            'bookmarks' => $bookmarks,
        ]);
    }

    /**
     * Get all bookmarked jobs for the authenticated user (API endpoint).
     */
    public function index(): JsonResponse
    {
        $bookmarks = JobBookmark::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'bookmarks' => $bookmarks,
        ]);
    }

    /**
     * Bookmark a job.
     */
    public function store(Request $request): JsonResponse
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
            // Generate hash for unique constraint
            $jobUrlHash = hash('sha256', $validated['job_url']);
            
            $bookmark = JobBookmark::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'job_url_hash' => $jobUrlHash,
                ],
                array_merge($validated, ['job_url_hash' => $jobUrlHash])
            );

            return response()->json([
                'success' => true,
                'message' => 'Job bookmarked successfully',
                'bookmark' => $bookmark,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bookmark job',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove a bookmark.
     */
    public function destroy(string $jobUrl): JsonResponse
    {
        // Decode the URL parameter (Laravel automatically decodes route parameters)
        $decodedUrl = urldecode($jobUrl);
        $jobUrlHash = hash('sha256', $decodedUrl);
        
        $bookmark = JobBookmark::where('user_id', Auth::id())
            ->where('job_url_hash', $jobUrlHash)
            ->first();

        if (!$bookmark) {
            return response()->json([
                'success' => false,
                'message' => 'Bookmark not found',
            ], 404);
        }

        $bookmark->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bookmark removed successfully',
        ]);
    }

    /**
     * Check if a job is bookmarked.
     */
    public function check(Request $request): JsonResponse
    {
        $request->validate([
            'job_url' => ['required', 'string'],
        ]);
        
        $jobUrl = $request->get('job_url');
        $jobUrlHash = hash('sha256', $jobUrl);
        
        $isBookmarked = JobBookmark::where('user_id', Auth::id())
            ->where('job_url_hash', $jobUrlHash)
            ->exists();

        return response()->json([
            'success' => true,
            'is_bookmarked' => $isBookmarked,
        ]);
    }
}

