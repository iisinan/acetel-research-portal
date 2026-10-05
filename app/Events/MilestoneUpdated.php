<?php

namespace App\Events;

use App\Models\StudentMilestone;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MilestoneUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $milestone;
    public $message;
    public $recipientId;

    /**
     * Create a new event instance.
     */
    public function __construct(StudentMilestone $milestone, string $message, string $recipientId)
    {
        $this->milestone = $milestone->load('template');
        $this->message = $message;
        $this->recipientId = $recipientId;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('inbox.' . $this->recipientId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'MilestoneUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'milestone_id' => $this->milestone->id,
            'name' => $this->milestone->template->name,
            'status' => $this->milestone->status,
            'message' => $this->message,
        ];
    }
}
