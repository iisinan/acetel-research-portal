<?php

namespace App\Notifications\Traits;

use App\Models\EmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;

trait TemplatedNotification
{
    /**
     * Get the templated mail message.
     */
    protected function getTemplatedMail($slug, $data, $actionUrl = null, $actionText = 'View Details')
    {
        $template = EmailTemplate::where('slug', $slug)->first();

        if (!$template) {
            // Fallback if template missing
            return (new MailMessage)
                ->subject('Notification from Trajectory Hub')
                ->line('You have a new institutional notification.');
        }

        $parsed = $template->parse($data);

        $mail = (new MailMessage)
            ->subject($parsed['subject'])
            ->markdown('mail.institutional', [
                'content' => $parsed['content'],
                'actionUrl' => $actionUrl,
                'actionText' => $actionText
            ]);

        return $mail;
    }
}
