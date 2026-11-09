<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\MilestoneTemplate;

class ExaminerBatchScheduleRescheduled extends Notification implements ShouldQueue
{
    use Queueable;

    public $template;
    public $rescheduledItems;
    public $milestoneName;
    public $disciplineGroup;

    public function __construct(?MilestoneTemplate $template = null, array $rescheduledItems = [], ?string $milestoneName = null, ?string $disciplineGroup = null)
    {
        $this->template = $template;
        $this->rescheduledItems = $rescheduledItems;
        $this->milestoneName = $milestoneName ?? ($template?->name ?? 'Presentation');
        $this->disciplineGroup = $disciplineGroup;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $count = count($this->rescheduledItems);
        $subject = 'Schedule Movement Notice: ' . $count . ' Candidate' . ($count === 1 ? '' : 's') . ' Rescheduled for ' . $this->milestoneName;

        return (new MailMessage)
            ->subject($subject)
            ->markdown('emails.examiner-batch-rescheduled', [
                'template' => $this->template,
                'rescheduledItems' => $this->rescheduledItems,
                'milestoneName' => $this->milestoneName,
                'disciplineGroup' => $this->disciplineGroup,
                'notifiable' => $notifiable,
                'count' => $count,
                'dashboardUrl' => config('app.url') . '/dashboard',
                'presentationsUrl' => $this->template ? config('app.url') . '/presentations/' . $this->template->id : config('app.url') . '/presentations',
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $count = count($this->rescheduledItems);
        return [
            'template_id' => $this->template?->id,
            'milestone_name' => $this->milestoneName,
            'count' => $count,
            'message' => "Schedule update: {$count} candidate(s) for {$this->milestoneName} have been rescheduled to new presentation slots.",
            'action_url' => $this->template ? config('app.url') . '/presentations/' . $this->template->id : config('app.url') . '/presentations',
        ];
    }
}
