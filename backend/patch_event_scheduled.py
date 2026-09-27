import re

path = '/Users/sinan/Herd/Thesis Monotoring system/backend/app/Notifications/EventScheduled.php'
with open(path, 'r') as f:
    content = f.read()

old_mail = """    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('A defence event has been scheduled.')
                    ->line('Type: ' . ucfirst($this->event->type))
                    ->line('Date: ' . $this->event->schedule_start->format('M d, Y H:i'))
                    ->line('Location: ' . $this->event->location)
                    ->action('View Details', url('/dashboard'));
    }"""

new_mail = """    public function toMail(object $notifiable): MailMessage
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
    }"""

content = content.replace(old_mail, new_mail)

with open(path, 'w') as f:
    f.write(content)
