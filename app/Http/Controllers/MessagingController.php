<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessagingController extends Controller
{
    /**
     * Display the messaging interface.
     */
    public function index(User $user = null)
    {
        $currentUser = Auth::user();
        
        // Get all users who the current user has exchanged messages with
        $conversations = User::whereIn('id', function($query) use ($currentUser) {
            $query->select('receiver_id')
                ->from('messages')
                ->where('sender_id', $currentUser->id)
                ->union(
                    DB::table('messages')
                        ->select('sender_id')
                        ->from('messages')
                        ->where('receiver_id', $currentUser->id)
                );
        })->get();

        // If a specific user is selected, get the chat history
        $messages = collect();
        if ($user) {
            $messages = Message::where(function($q) use ($currentUser, $user) {
                    $q->where('sender_id', $currentUser->id)
                      ->where('receiver_id', $user->id);
                })->orWhere(function($q) use ($currentUser, $user) {
                    $q->where('sender_id', $user->id)
                      ->where('receiver_id', $currentUser->id);
                })
                ->orderBy('created_at', 'asc')
                ->get();

            // Mark messages as read
            Message::where('sender_id', $user->id)
                ->where('receiver_id', $currentUser->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        return view('messaging.index', compact('conversations', 'user', 'messages'));
    }

    /**
     * Store a new message.
     */
    public function store(Request $request, User $user)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        // Check if users are connected
        $isConnected = Auth::user()->connections()->contains($user->id);
        
        if (!$isConnected) {
            return back()->with('error', 'You can only message your connections.');
        }

        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $user->id,
            'content' => $validated['content'],
        ]);

        // Notify the receiver
        $user->notify(new NewMessage(Auth::user(), $message));

        return back()->with('success', 'Message sent.');
    }
}
