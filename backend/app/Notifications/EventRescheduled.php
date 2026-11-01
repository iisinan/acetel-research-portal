<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\DefenceEvent;

class EventRescheduled extends Notification implements ShouldQueue
{
    use Queueable;

    public $event;
    public $previousDate;
    public $previousTime;
    public $milestoneName;

    public function __construct(DefenceEvent $event, ?string $previousDate = null, ?string $previousTime = null, ?string $milestoneName = null)
    {
        $this->event = $event;
        $this->previousDate = $previousDate;
        $this->previousTime = $previousTime;
        $this->milestoneName = $milestoneName;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $eventType = $this->milestoneName ?? ucfirst(str_replace('_', ' ', $this->event->type));
        $newDateStr = $this->event->schedule_start ? $this->event->schedule_start->format('l, F j, Y') : 'TBD';

        return (new MailMessage)
            ->subject('Schedule Movement Notice: ' . $eventType . ' Rescheduled to ' . $newDateStr)
            ->markdown('emails.event-rescheduled', [
                'event' => $this->event,
                'previousDate' => $this->previousDate,
                'previousTime' => $this->previousTime,
                'milestoneName' => $this->milestoneName,
                'notifiable' => $notifiable
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'event_id' => $this->event->id,
            'type' => $this->event->type,
            'milestone_name' => $this->milestoneName,
            'previous_date' => $this->previousDate,
            'previous_time' => $this->previousTime,
            'time' => $this->event->schedule_start,
            'message' => 'Your ' . ($this->milestoneName ?? $this->event->type) . ' presentation schedule has been moved to ' . ($this->event->schedule_start ? $this->event->schedule_start->format('M d, Y g:i A') : 'a new date') . '.',
        ];
    }
}
