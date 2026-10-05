<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewMessage extends Notification implements ShouldQueue
{
    use Queueable;

    public $messageObj;

    public function __construct($message)
    {
        $this->messageObj = $message;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $thesisTitle = $this->messageObj->thesis->title ?? 'a thesis project';

        return (new MailMessage)
            ->subject('New Institutional Message: ' . $thesisTitle)
            ->markdown('emails.new-message', [
                'messageObj' => $this->messageObj,
                'notifiable' => $notifiable
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
