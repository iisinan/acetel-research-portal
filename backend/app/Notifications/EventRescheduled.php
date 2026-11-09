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

        $candidateName = $this->event->thesis?->student?->user?->name;
        $isStudent = $notifiable->id === ($this->event->thesis?->student?->user_id ?? null);

        $subject = $isStudent
            ? 'Schedule Movement Notice: ' . $eventType . ' Rescheduled to ' . $newDateStr
            : 'Schedule Movement Notice: Candidate ' . ($candidateName ?? 'Presentation') . ' - ' . $eventType . ' Rescheduled to ' . $newDateStr;

        return (new MailMessage)
            ->subject($subject)
            ->markdown('emails.event-rescheduled', [
                'event' => $this->event,
                'previousDate' => $this->previousDate,
                'previousTime' => $this->previousTime,
                'milestoneName' => $this->milestoneName,
                'notifiable' => $notifiable,
                'isStudent' => $isStudent,
                'candidateName' => $candidateName,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $candidateName = $this->event->thesis?->student?->user?->name;
        $isStudent = $notifiable->id === ($this->event->thesis?->student?->user_id ?? null);
        $subject = $isStudent ? 'Your' : ($candidateName ? "Candidate {$candidateName}'s" : 'The');

        return [
            'event_id' => $this->event->id,
            'type' => $this->event->type,
            'milestone_name' => $this->milestoneName,
            'previous_date' => $this->previousDate,
            'previous_time' => $this->previousTime,
            'time' => $this->event->schedule_start,
            'candidate_name' => $candidateName,
            'is_examiner' => !$isStudent,
            'message' => "{$subject} " . ($this->milestoneName ?? $this->event->type) . ' presentation schedule has been moved to ' . ($this->event->schedule_start ? $this->event->schedule_start->format('M d, Y g:i A') : 'a new date') . '.',
        ];
    }
}
