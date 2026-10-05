<?php

namespace App\Events;

use App\Models\Evaluation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EvaluationSubmitted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $evaluation;
    public $targetUserId;

    public function __construct(Evaluation $evaluation, $targetUserId)
    {
        $this->evaluation = $evaluation->load(['defenceEvent.thesis.student.user', 'evaluator']);
        $this->targetUserId = $targetUserId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('inbox.' . $this->targetUserId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'EvaluationSubmitted';
    }

    public function broadcastWith(): array
    {
        return [
            'evaluation_id' => $this->evaluation->id,
            'evaluator_name' => $this->evaluation->evaluator->name,
            'student_name' => $this->evaluation->defenceEvent->thesis->student->user->name,
            'recommendation' => str_replace('_', ' ', $this->evaluation->recommendation),
            'timestamp' => now()->toISOString(),
        ];
    }
}
