<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProfileImageController extends Controller
{
    /**
     * Serve profile images with security checks
     */
    public function show(string $filename)
    {
        // Sanitize filename to prevent path traversal attacks
        $filename = basename($filename);
        
        // Validate filename format (alphanumeric, dots, underscores, hyphens only)
        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $filename)) {
            abort(404);
        }
        
        // Prevent directory traversal
        if (strpos($filename, '..') !== false || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
            abort(404);
        }
        
        $path = storage_path('app/public/profiles/' . $filename);
        
        // Normalize path to prevent directory traversal
        $realPath = realpath($path);
        $basePath = realpath(storage_path('app/public/profiles'));
        
        // Ensure file is within the profiles directory
        if ($realPath === false || strpos($realPath, $basePath) !== 0) {
            abort(404);
        }
        
        // Check if file exists
        if (!file_exists($realPath)) {
            abort(404);
        }
        
        // Check if it's actually a file (not a directory)
        if (!is_file($realPath)) {
            abort(404);
        }
        
        // Validate file extension
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions)) {
            abort(404);
        }
        
        // Get MIME type and validate it's an image
        $mimeType = mime_content_type($realPath);
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($mimeType, $allowedMimeTypes)) {
            abort(404);
        }
        
        // Return file with proper security headers
        return response()->file($realPath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
        ]);
    }
}

