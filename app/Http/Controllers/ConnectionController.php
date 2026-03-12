<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\User;
use App\Notifications\ConnectionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConnectionController extends Controller
{
    /**
     * Display a listing of connections and pending requests.
     */
    public function index()
    {
        $user = Auth::user();
        
        $connections = $user->connections();
        $pendingReceived = $user->receivedRequests()->where('status', 'pending')->with('requester')->get();
        $pendingSent = $user->sentRequests()->where('status', 'pending')->with('recipient')->get();

        // Already connected or pending IDs
        $excludedUserIds = Connection::where('user_id', $user->id)->pluck('connected_user_id')
            ->merge(Connection::where('connected_user_id', $user->id)->pluck('user_id'))
            ->push($user->id);

        $discoverUsers = User::whereNotIn('id', $excludedUserIds)
            ->where('role', '!=', 'admin')
            ->inRandomOrder()
            ->limit(6)
            ->get();

        return view('connections.index', compact('connections', 'pendingReceived', 'pendingSent', 'discoverUsers'));
    }

    /**
     * Send a connection request.
     */
    public function sendRequest(User $user)
    {
        $requester = Auth::user();

        // Check if a connection already exists
        $exists = Connection::where(function($query) use ($requester, $user) {
            $query->where('user_id', $requester->id)->where('connected_user_id', $user->id);
        })->orWhere(function($query) use ($requester, $user) {
            $query->where('user_id', $user->id)->where('connected_user_id', $requester->id);
        })->exists();

        if ($exists) {
            return back()->with('error', 'A connection or request already exists.');
        }

        $connection = Connection::create([
            'user_id' => $requester->id,
            'connected_user_id' => $user->id,
            'status' => 'pending',
        ]);

        // Notify the recipient
        $user->notify(new ConnectionRequest($requester));

        return back()->with('success', 'Connection request sent!');
    }

    /**
     * Accept a connection request.
     */
    public function acceptRequest(Connection $connection)
    {
        if ($connection->connected_user_id !== Auth::id()) {
            abort(403);
        }

        $connection->update(['status' => 'accepted']);

        return back()->with('success', 'Connection request accepted!');
    }

    /**
     * Reject/Ignore a connection request.
     */
    public function rejectRequest(Connection $connection)
    {
        if ($connection->connected_user_id !== Auth::id()) {
            abort(403);
        }

        $connection->delete(); // Or update to 'rejected'

        return back()->with('success', 'Connection request rejected.');
    }

    /**
     * Remove an existing connection.
     */
    public function removeConnection(User $user)
    {
        $currentUserId = Auth::id();
        
        Connection::where(function($query) use ($currentUserId, $user) {
            $query->where('user_id', $currentUserId)->where('connected_user_id', $user->id);
        })->orWhere(function($query) use ($currentUserId, $user) {
            $query->where('user_id', $user->id)->where('connected_user_id', $currentUserId);
        })->delete();

        return back()->with('success', 'Connection removed.');
    }
}
