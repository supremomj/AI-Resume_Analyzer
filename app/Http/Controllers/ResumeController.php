<?php

namespace App\Http\Controllers;

use App\Services\ResumeUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ResumeController extends Controller
{
    public function upload(Request $request)
    {
        Log::info('Resume upload request received', ['user_id' => Auth::id()]);

        $request->validate([
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
        
        if (!$file || !$file->isValid()) {
            return back()->withErrors(['No valid file detected.']);
        }

        $uploadService = new ResumeUploadService();
        $result = $uploadService->processUpload($user, $file);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        } else {
            return back()->withErrors([$result['message']]);
        }
    }
}
