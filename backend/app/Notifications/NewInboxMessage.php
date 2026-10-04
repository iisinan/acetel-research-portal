<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\InboxMessage;

class NewInboxMessage extends Notification implements ShouldQueue
{
    use Queueable;

    public $message;

    public function __construct(InboxMessage $message)
    {
        $this->message = $message;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Message: ' . $this->message->subject)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('You have received a new direct message from ' . $this->message->sender->name . '.')
            ->line('**Subject:** ' . $this->message->subject)
            ->line('**Message:**')
            ->line($this->message->body)
            ->action('View Inbox', url('/inbox'))
            ->line('Thank you for using our application!');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
