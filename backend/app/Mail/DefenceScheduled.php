<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DefenceScheduled extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $defenceType;
    public $defenceDate;
    public $defenceTypeLabel;

    /**
     * Create a new message instance.
     */
    public function __construct($user, string $defenceType, string $defenceDate)
    {
        $this->user = $user;
        $this->defenceType = $defenceType;
        $this->defenceDate = $defenceDate;
        $this->defenceTypeLabel = match($defenceType) {
            'seminar'   => 'Seminar Presentation',
            'proposal'  => 'Proposal Defence',
            'progress_report_1' => 'Progress Report 1 Presentation',
            'progress_report_2' => 'Progress Report 2 Presentation',
            'internal'  => 'Internal Defence',
            'external'  => 'External Defence',
            default     => ucfirst(str_replace('_', ' ', $defenceType)) . ' Defence',
        };
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "📅 {$this->defenceTypeLabel} Scheduled – " . \Carbon\Carbon::parse($this->defenceDate)->format('D, M j, Y'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.defence-scheduled',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
