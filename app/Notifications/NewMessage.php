<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewMessage extends Notification
{
    use Queueable;

    protected $sender;
    protected $message;

    public function __construct(User $sender, Message $message)
    {
        $this->sender = $sender;
        $this->message = $message;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'new_message',
            'sender_id' => $this->sender->id,
            'sender_name' => $this->sender->first_name . ' ' . $this->sender->last_name,
            'message' => 'sent you a message.',
            'content' => $this->message->content,
            'url' => route('messaging.index', $this->sender),
        ];
    }
}
