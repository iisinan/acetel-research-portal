<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\DefenceEvent;

class EventScheduled extends Notification implements ShouldQueue
{
    use Queueable;

    protected $event;

    public function __construct(DefenceEvent $event)
    {
        $this->event = $event;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Presentation Scheduled: ' . ucfirst(str_replace('_', ' ', $this->event->type)))
            ->markdown('emails.event-scheduled', [
                'event' => $this->event,
                'notifiable' => $notifiable
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'event_id' => $this->event->id,
            'type' => $this->event->type,
            'time' => $this->event->schedule_start,
            'message' => 'Event scheduled: ' . $this->event->type,
        ];
    }
}
