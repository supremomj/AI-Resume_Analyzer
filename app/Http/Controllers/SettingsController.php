<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Show the settings page
     */
    public function index(Request $request): View
    {
        $user = $request->user()->fresh();
        return view('settings', [
            'user' => $user,
        ]);
    }

    /**
     * Update user settings
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user()->fresh();
        
        $validated = $request->validate([
            'language' => ['nullable', 'in:en,tl'],
        ]);

        // Update settings in database
        $settingsData = [
            'language' => $validated['language'] ?? $user->language ?? 'en',
        ];

        // Update user settings
        $user->update($settingsData);

        // Set app locale based on user's language preference
        if (isset($validated['language'])) {
            app()->setLocale($validated['language']);
            session(['locale' => $validated['language']]);
        }

        return redirect()->route('settings.index')->with('success', __('Settings saved successfully!'));
    }

    /**
     * Export user data
     */
    public function export(Request $request)
    {
        $user = $request->user()->fresh();
        
        $data = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'contact_number' => $user->contact_number,
                'address' => $user->address,
                'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                'created_at' => $user->created_at->toIso8601String(),
                'updated_at' => $user->updated_at->toIso8601String(),
            ],
            'ai_analysis' => $user->ai_analysis,
            'resume_score' => $user->resume_score,
            'recommended_field' => $user->recommended_field,
            'bookmarks' => $user->bookmarks()->get()->map(function ($bookmark) {
                return [
                    'job_title' => $bookmark->job_title,
                    'job_url' => $bookmark->job_url,
                    'company' => $bookmark->company,
                    'location' => $bookmark->location,
                    'created_at' => $bookmark->created_at->toIso8601String(),
                ];
            }),
            'job_view_history' => $user->jobViewHistory()->get()->map(function ($history) {
                return [
                    'job_title' => $history->job_title,
                    'job_url' => $history->job_url,
                    'company' => $history->company,
                    'location' => $history->location,
                    'viewed_at' => $history->created_at->toIso8601String(),
                ];
            }),
            'exported_at' => now()->toIso8601String(),
        ];

        $filename = 'user_data_' . $user->id . '_' . now()->format('Y-m-d_His') . '.json';
        
        return response()->json($data, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}

