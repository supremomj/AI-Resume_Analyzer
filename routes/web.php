<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfileImageController;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\JobViewHistoryController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Admin\AdminController;
use Illuminate\Support\Facades\Route;

// Public landing
Route::get('/', function () {
    return view('career');
});

// Dashboard (after login)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Auth-required (user) routes - must be verified to access
Route::middleware(['auth', 'verified'])->group(function () {
    // Profile management
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->middleware('throttle:10,1') // Limit profile updates
        ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Settings management
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::patch('/settings', [SettingsController::class, 'update'])
        ->middleware('throttle:10,1') // Limit settings updates
        ->name('settings.update');
    Route::get('/settings/export', [SettingsController::class, 'export'])->name('settings.export');

    // Home (main user landing)
    Route::get('/home', function () {
        $user = auth()->user()->fresh();
        $profileStrength = $user->getProfileStrength();
        $bookmarkCount = $user->bookmarks()->count();

        return view('home', [
            'profileStrength' => $profileStrength,
            'bookmarkCount' => $bookmarkCount,
        ]);
    })->name('home');

    // Jobs page (user-only) - now with real job fetching
    Route::get('/jobs', [JobController::class, 'index'])->name('jobs');
    Route::get('/api/jobs/home', [JobController::class, 'getJobsForHome'])->name('jobs.home');

    // Job Bookmarks
    Route::get('/bookmarks', [BookmarkController::class, 'show'])->name('bookmarks');

    // Job Bookmarks API (with rate limiting)
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('/api/bookmarks', [BookmarkController::class, 'index'])->name('bookmarks.index');
        Route::post('/api/bookmarks', [BookmarkController::class, 'store'])->name('bookmarks.store');
        Route::delete('/api/bookmarks/{jobUrl}', [BookmarkController::class, 'destroy'])->where('jobUrl', '.*')->name('bookmarks.destroy');
        Route::get('/api/bookmarks/check', [BookmarkController::class, 'check'])->name('bookmarks.check');
    });

    // Job View History API (with rate limiting)
    Route::middleware('throttle:60,1')->group(function () {
        Route::post('/api/job-views/track', [JobViewHistoryController::class, 'track'])->name('job-views.track');
        Route::get('/api/job-views/recently-viewed', [JobViewHistoryController::class, 'getRecentlyViewed'])->name('job-views.recently-viewed');
        Route::delete('/api/job-views/clear', [JobViewHistoryController::class, 'clearHistory'])->name('job-views.clear');
    });

    // Resume upload page & handler
    Route::get('/resume/upload', function () {
        // Refresh user data to get latest AI analysis
        $user = auth()->user()->fresh();

        // Match real courses from database based on AI analysis
        $matchedCourses = collect();
        if ($user->ai_analysis) {
            $courseService = new \App\Services\CourseMatchingService();
            $matchedCourses = $courseService->getMatchedCourses($user->ai_analysis, 6);
        }

        return view('upload', [
            'user' => $user,
            'matchedCourses' => $matchedCourses,
        ]);
    })->name('resume.upload');
    Route::post('/resume/upload', [ResumeController::class, 'upload'])
        ->middleware('throttle:5,1') // Limit to 5 uploads per minute
        ->name('resume.upload.save');
});

// Profile images (public access with security)
Route::get('/storage/profiles/{filename}', [ProfileImageController::class, 'show'])
    ->where('filename', '[A-Za-z0-9._-]+')
    ->middleware('throttle:30,1') // Rate limit image requests
    ->name('profile.image');

// Public/static views
Route::view('/about', 'about');
Route::view('/career', 'career');
Route::view('/login', 'login');
Route::view('/privacy', 'privacy');
Route::view('/register', 'register');
Route::view('/terms', 'terms');
Route::view('/upload', 'upload');
Route::view('/welcome', 'welcome');

// Admin routes - protected by admin middleware
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/{user}', [AdminController::class, 'showUser'])->name('users.show');
    Route::patch('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('users.update-role');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');
});

// Auth scaffolding
require __DIR__ . '/auth.php';
