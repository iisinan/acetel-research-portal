<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\MilestoneTemplate;

class ExaminerNominated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $template;
    protected $customMessage;
    protected $studentCount;

    public function __construct(MilestoneTemplate $template, $customMessage, $studentCount)
    {
        $this->template = $template;
        $this->customMessage = $customMessage;
        $this->studentCount = $studentCount;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Official Nomination: Examiner for ' . $this->template->name)
            ->greeting('Dear ' . $notifiable->name . ',')
            ->line('You have been officially nominated as an Examiner for the upcoming "' . $this->template->name . '" presentations.')
            ->line('You have been assigned to evaluate **' . $this->studentCount . '** student(s).');

        if ($this->customMessage) {
            $mail->line('**Message from Coordinator/Admin:**')
                 ->line($this->customMessage);
        }

        $mail->line('**Your Responsibilities:**')
            ->line('- Please be present at the scheduled presentation sessions.')
            ->line('- Review the candidates\' manuscripts and presentation slides.')
            ->line('- Score the students using the rubrics provided in your dashboard after their presentations.')
            ->action('View Your Schedule', config('app.url') . '/dashboard')
            ->line('Thank you for your service and dedication to academic excellence.');

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Nominated as Examiner for ' . $this->template->name,
            'message' => 'You have been assigned to evaluate ' . $this->studentCount . ' students. ' . ($this->customMessage ? 'Message: ' . $this->customMessage : ''),
        ];
    }
}
