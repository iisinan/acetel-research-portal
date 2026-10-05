<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Notifications\Traits\TemplatedNotification;
 
class SupervisorAssigned extends Notification
{
    use Queueable, TemplatedNotification;

    protected $project;

    /**
     * Create a new notification instance.
     */
    public function __construct(\App\Models\ThesisProject $project)
    {
        $this->project = $project;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return $this->getTemplatedMail(
            'supervisor_assigned', 
            [
                'notifiable_name' => $notifiable->name,
                'project_title' => $this->project->title
            ],
            route('milestones.index'),
            'View Research Progress'
        );
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'supervisor_assigned',
            'title' => 'Supervision Panel Authorized',
            'message' => 'Your thesis supervision panel has been finalized.',
            'project_id' => $this->project->id,
            'action_url' => route('milestones.index'),
        ];
    }
}
