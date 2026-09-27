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

    /**
     * Create a new notification instance.
     */
    public function __construct(DefenceEvent $event)
    {
        $this->event = $event;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
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
        $studentName = $this->event->thesis && $this->event->thesis->student && $this->event->thesis->student->user 
            ? $this->event->thesis->student->user->name 
            : 'A student';
            
        return (new MailMessage)
                    ->subject('New Presentation Scheduled: ' . ucfirst($this->event->type))
                    ->line('A presentation has been scheduled.')
                    ->line('Student: ' . $studentName)
                    ->line('Type: ' . ucfirst($this->event->type))
                    ->line('Date: ' . ($this->event->schedule_start ? $this->event->schedule_start->format('M d, Y') : 'TBD'))
                    ->line('Time: ' . ($this->event->schedule_start ? $this->event->schedule_start->format('H:i') : 'TBD'))
                    ->line('Location: ' . ($this->event->location ?? 'Online / TBD'))
                    ->action('View Details', url('/dashboard'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
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
