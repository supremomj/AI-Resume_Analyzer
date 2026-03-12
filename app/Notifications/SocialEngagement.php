<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SocialEngagement extends Notification
{
    use Queueable;

    protected $user;
    protected $post;
    protected $engagementType; // 'like' or 'comment'

    public function __construct(User $user, Post $post, string $type)
    {
        $this->user = $user;
        $this->post = $post;
        $this->engagementType = $type;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $action = $this->engagementType === 'like' ? 'liked your post.' : 'commented on your post.';
        
        return [
            'type' => $this->engagementType,
            'user_id' => $this->user->id,
            'user_name' => $this->user->first_name . ' ' . $this->user->last_name,
            'post_id' => $this->post->id,
            'message' => $action,
            'url' => route('feed'), // Ideally scroll to post but feed is fine
        ];
    }
}
