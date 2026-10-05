<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class SupervisorRoleAssigned extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $assignment;

    public function __construct($assignment)
    {
        $this->assignment = $assignment;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $role = ucfirst($this->assignment->role);

        return (new MailMessage)
            ->subject('New Supervision Assignment: ' . $role)
            ->markdown('emails.supervisor-assigned', [
                'assignment' => $this->assignment,
                'notifiable' => $notifiable
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'assignment_id' => $this->assignment->id,
            'thesis_id' => $this->assignment->thesis_project_id,
            'role' => $this->assignment->role,
            'message' => 'New supervisor assignment: ' . $this->assignment->role
        ];
    }
}
