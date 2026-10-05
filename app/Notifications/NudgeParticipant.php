<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class NudgeParticipant extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new notification instance.
     */
    protected $channel;
    protected $sender;

    public function __construct($channel, $sender)
    {
        $this->channel = $channel;
        $this->sender = $sender;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Attention Required: Thesis Project Communication')
                    ->line('Program Coordinator ' . $this->sender->name . ' has sent a nudge regarding your project: ' . $this->channel->thesisProject->title)
                    ->line('Please ensure you are communicating regularly through the official channels.')
                    ->action('Open Communication Channel', url('/theses/' . $this->channel->thesisProject->id))
                    ->line('Timely communication is essential for milestone clearance.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'channel_id' => $this->channel->id,
            'sender_id' => $this->sender->id,
            'message' => 'You have been nudged by the Program Coordinator for your thesis project.'
        ];
    }
}
