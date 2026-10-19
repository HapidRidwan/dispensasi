<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Notification $notification) {}

    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel('user.'.$this->notification->user_id)];

        if ($this->notification->user && $this->notification->user->role === 'admin') {
            $channels[] = new PrivateChannel('admin');
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'notification.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->notification->id,
            'title' => $this->notification->title,
            'message' => $this->notification->message,
            'notification_type' => $this->notification->notification_type,
            'dispensation_id' => $this->notification->dispensation_id,
            'is_read' => $this->notification->is_read,
            'created_at' => optional($this->notification->created_at)->toIso8601String(),
        ];
    }
}
