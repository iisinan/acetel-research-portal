<?php

namespace App\Events;

use App\Models\StudentMilestone;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MilestoneSubmitted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $milestone;
    public $targetUserId;
    public $studentName;

    public function __construct(StudentMilestone $milestone, $targetUserId)
    {
        $this->milestone = $milestone->load(['template', 'thesis.student.user']);
        $this->targetUserId = $targetUserId;
        $this->studentName = $this->milestone->thesis->student->user->name;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('inbox.' . $this->targetUserId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'MilestoneSubmitted';
    }

    public function broadcastWith(): array
    {
        return [
            'milestone_id' => $this->milestone->id,
            'milestone_name' => $this->milestone->template->name,
            'student_name' => $this->studentName,
            'thesis_id' => $this->milestone->thesis_project_id,
            'timestamp' => now()->toISOString(),
        ];
    }
}
