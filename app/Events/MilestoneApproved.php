<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MilestoneApproved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $milestone;
    public $recipientId;

    /**
     * Create a new event instance.
     */
    public function __construct(\App\Models\StudentMilestone $milestone, $recipientId)
    {
        $this->milestone = $milestone->load('template');
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

    public function broadcastAs()
    {
        return 'MilestoneApproved';
    }

    public function broadcastWith()
    {
        return [
            'milestone_id' => $this->milestone->id,
            'name' => $this->milestone->template->name,
            'status' => $this->milestone->status,
            'message' => 'Institutional clearance has been granted for: ' . $this->milestone->template->name,
        ];
    }
}
