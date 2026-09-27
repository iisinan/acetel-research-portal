<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\StudentMilestone;

class MilestoneGraded extends Notification implements ShouldQueue
{
    use Queueable;

    protected $milestone;

    public function __construct(StudentMilestone $milestone)
    {
        $this->milestone = $milestone;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isCleared = in_array(strtolower($this->milestone->status), ['approved', 'completed', 'cleared']);
        
        return (new MailMessage)
            ->subject(($isCleared ? 'Institutional Clearance: ' : 'Milestone Evaluation: ') . $this->milestone->template->name)
            ->markdown('emails.milestone-graded', [
                'milestone' => $this->milestone,
                'isCleared' => $isCleared
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'milestone_id' => $this->milestone->id,
            'status' => $this->milestone->status,
            'message' => 'Milestone ' . $this->milestone->template->name . ' has been reviewed.',
        ];
    }
}
