<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications.
     */
    public function index()
    {
        $notifications = Auth::user()->notifications()->paginate(20);
        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        
        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return back()->with('success', 'All notifications marked as read.');
    }
}
