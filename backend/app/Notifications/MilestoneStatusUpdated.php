<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class MilestoneStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $milestone;

    public function __construct($milestone)
    {
        $this->milestone = $milestone;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Milestone Status Updated: ' . $this->milestone->template->name)
            ->markdown('emails.milestone-graded', [
                'milestone' => $this->milestone,
                'isCleared' => in_array(strtolower($this->milestone->status), ['approved', 'completed', 'cleared'])
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'milestone_id' => $this->milestone->id,
            'status' => $this->milestone->status,
            'milestone_name' => $this->milestone->template->name,
            'message' => 'Status updated to ' . $this->milestone->status
        ];
    }
}
