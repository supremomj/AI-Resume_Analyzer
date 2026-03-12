<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ConnectionRequest extends Notification
{
    use Queueable;

    protected $sender;

    public function __construct(User $sender)
    {
        $this->sender = $sender;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'connection_request',
            'sender_id' => $this->sender->id,
            'sender_name' => $this->sender->first_name . ' ' . $this->sender->last_name,
            'message' => 'sent you a connection request.',
            'url' => route('connections.index'),
        ];
    }
}
