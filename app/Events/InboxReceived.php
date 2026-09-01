<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class InboxReceived implements ShouldBroadcastNow
{
    use SerializesModels;

    public function __construct(public Notification $notification) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('users.'.$this->notification->user_id)];
    }

    public function broadcastAs(): string
    {
        return 'inbox.received';
    }

    public function broadcastWith(): array
    {
        $n = $this->notification;

        return [
            'id' => $n->id,
            'type' => $n->type,
            'title' => $n->title,
            'message' => $n->message,
            'link' => $n->link,
            'is_read' => false,
            'created_at' => optional($n->created_at)->toIso8601String(),
        ];
    }
}
