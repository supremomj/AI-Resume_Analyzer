<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Like;
use App\Models\Comment;
use App\Notifications\SocialEngagement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FeedController extends Controller
{
    /**
     * Display the community feed.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get IDs of people the user is connected to
        $connectedUserIds = $user->sentRequests()->where('status', 'accepted')->pluck('connected_user_id')
            ->merge($user->receivedRequests()->where('status', 'accepted')->pluck('user_id'))
            ->push($user->id); // Include user's own posts

        $posts = Post::whereIn('user_id', $connectedUserIds)
            ->with(['user', 'comments.user'])
            ->withCount(['likes', 'comments'])
            ->withExists(['likes as user_liked' => function($query) {
                $query->where('user_id', Auth::id());
            }])
            ->latest()
            ->paginate(10);

        return view('feed', compact('posts'));
    }

    /**
     * Toggle like on a post.
     */
    public function toggleLike(Post $post)
    {
        $like = $post->likes()->where('user_id', Auth::id())->first();

        if ($like) {
            $like->delete();
            return back()->with('status', 'Post unliked.');
        }

        $post->likes()->create(['user_id' => Auth::id()]);

        // Notify the post owner (if it's not the current user)
        if ($post->user_id !== Auth::id()) {
            $post->user->notify(new SocialEngagement(Auth::user(), $post, 'like'));
        }

        return back()->with('status', 'Post liked!');
    }

    /**
     * Store a comment on a post.
     */
    public function storeComment(Request $request, Post $post)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $post->comments()->create([
            'user_id' => Auth::id(),
            'content' => $validated['content'],
        ]);

        // Notify the post owner (if it's not the current user)
        if ($post->user_id !== Auth::id()) {
            $post->user->notify(new SocialEngagement(Auth::user(), $post, 'comment'));
        }

        return back()->with('success', 'Comment posted.');
    }

    /**
     * Store a new post.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:2000',
            'image' => 'nullable|image|max:5120', // 5MB limit
        ]);

        $post = new Post();
        $post->user_id = Auth::id();
        $post->content = $validated['content'];

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('posts', 'public');
            $post->image_path = $path;
        }

        $post->save();

        return back()->with('success', 'Post shared to your network!');
    }

    /**
     * Delete a post.
     */
    public function destroy(Post $post)
    {
        if ($post->user_id !== Auth::id()) {
            abort(403);
        }

        if ($post->image_path) {
            Storage::disk('public')->delete($post->image_path);
        }

        $post->delete();

        return back()->with('success', 'Post deleted.');
    }
}
