<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\JobBookmark;
use App\Models\JobViewHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function dashboard()
    {
        try {
            $totalViews = JobViewHistory::sum('view_count') ?? 0;
        } catch (\Exception $e) {
            $totalViews = 0;
        }

        $stats = [
            'total_users' => User::count(),
            'verified_users' => User::whereNotNull('email_verified_at')->count(),
            'total_bookmarks' => JobBookmark::count(),
            'total_views' => $totalViews,
            'users_with_resumes' => User::whereNotNull('resume_path')->count(),
            'users_with_ai_analysis' => User::whereNotNull('ai_analysis')->count(),
        ];

        // Recent users (last 10)
        $recentUsers = User::latest()->take(10)->get();

        // Users by registration date (last 30 days)
        $usersByDate = User::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top job sources
        $topJobSources = JobBookmark::select('source', DB::raw('COUNT(*) as count'))
            ->whereNotNull('source')
            ->groupBy('source')
            ->orderByDesc('count')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentUsers', 'usersByDate', 'topJobSources'));
    }

    /**
     * Display a listing of users.
     */
    public function users(Request $request)
    {
        $query = User::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role') && $request->role) {
            $query->where('role', $request->role);
        }

        // Filter by verification status
        if ($request->has('verified') && $request->verified !== '') {
            if ($request->verified == '1') {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        $users = $query->latest()->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Display the specified user.
     */
    public function showUser(User $user)
    {
        $bookmarks = $user->bookmarks()->latest()->take(10)->get();
        $viewHistory = $user->jobViewHistory()->latest()->take(10)->get();
        $profileStrength = $user->getProfileStrength();

        return view('admin.users.show', compact('user', 'bookmarks', 'viewHistory', 'profileStrength'));
    }

    /**
     * Update user role.
     */
    public function updateUserRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:user,admin',
        ]);

        $user->update(['role' => $request->role]);

        return back()->with('success', 'User role updated successfully.');
    }

    /**
     * Delete a user.
     */
    public function deleteUser(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User deleted successfully.');
    }
}
