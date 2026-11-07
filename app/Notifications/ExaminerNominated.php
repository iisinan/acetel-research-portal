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

    public $template;
    public $customMessage;
    public $studentCount;
    public $disciplineGroup;

    public function __construct(?MilestoneTemplate $template = null, $customMessage = null, $studentCount = 0, $disciplineGroup = null)
    {
        $this->template = $template;
        $this->customMessage = $customMessage;
        $this->studentCount = (int) $studentCount;
        $this->disciplineGroup = $disciplineGroup;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function isSeminarCourse(): bool
    {
        if (!$this->template) {
            return false;
        }

        $slug = strtolower($this->template->slug ?? '');
        $name = strtolower($this->template->name ?? '');

        return $slug === 'seminar_as_a_course'
            || $slug === 'seminar'
            || str_contains($slug, 'seminar_as_a_course')
            || str_contains($name, 'seminar course')
            || str_contains($name, 'seminar as a course');
    }

    public function getDisciplineGroupLabel(): ?string
    {
        if (!$this->disciplineGroup) {
            return null;
        }

        return match ($this->disciplineGroup) {
            'cybersecurity' => 'Cybersecurity Track',
            'artificial_intelligence' => 'Artificial Intelligence Track',
            'management_information_systems' => 'Management Information Systems Track',
            default => ucfirst(str_replace('_', ' ', $this->disciplineGroup)) . ' Track',
        };
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isSeminar = $this->isSeminarCourse();
        $disciplineGroupLabel = $this->getDisciplineGroupLabel();

        $subject = $isSeminar 
            ? 'Official Appointment & Examiner Guide: Seminar as a Course'
            : 'Official Nomination: Examiner for ' . ($this->template->name ?? 'Presentations');

        return (new MailMessage)
            ->subject($subject)
            ->markdown('emails.examiner-nominated', [
                'template' => $this->template,
                'customMessage' => $this->customMessage,
                'studentCount' => $this->studentCount,
                'disciplineGroup' => $this->disciplineGroup,
                'disciplineGroupLabel' => $disciplineGroupLabel,
                'isSeminarCourse' => $isSeminar,
                'notifiable' => $notifiable,
                'presentationUrl' => $this->template ? config('app.url') . '/presentations/' . $this->template->id : config('app.url') . '/presentations',
                'dashboardUrl' => config('app.url') . '/dashboard',
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $isSeminar = $this->isSeminarCourse();
        $title = $isSeminar 
            ? 'Appointed as Examiner for Seminar as a Course' 
            : 'Nominated as Examiner for ' . ($this->template->name ?? 'Presentations');

        $guideSummary = $isSeminar
            ? "You have been assigned to evaluate {$this->studentCount} student(s) for Seminar as a Course. Please check the Presentations tab for your Zoom link and to score candidates after their presentations."
            : "You have been assigned to evaluate {$this->studentCount} student(s) for " . ($this->template->name ?? 'presentations') . '.';

        return [
            'title' => $title,
            'message' => $guideSummary . ($this->customMessage ? ' Message: ' . $this->customMessage : ''),
            'template_id' => $this->template?->id,
            'discipline_group' => $this->disciplineGroup,
            'is_seminar_course' => $isSeminar,
            'action_url' => $this->template ? config('app.url') . '/presentations/' . $this->template->id : config('app.url') . '/presentations',
        ];
    }
}
