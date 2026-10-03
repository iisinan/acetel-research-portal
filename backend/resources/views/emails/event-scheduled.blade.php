<x-mail::message>

# Academic Event Scheduled: {{ ucfirst(str_replace('_', ' ', $event->type)) }}

Dear **{{ $notifiable->name }}**,

An official academic presentation has been scheduled involving you on the ACETEL Thesis Monitoring System.

<x-mail::panel>
## 📅 Event Details

| | |
|---|---|
| **Event Type** | {{ ucfirst(str_replace('_', ' ', $event->type)) }} |
| **Candidate** | {{ $event->thesis->student->user->name ?? 'N/A' }} |
| **Date** | {{ $event->schedule_start ? $event->schedule_start->format('l, F j, Y') : 'TBD' }} |
| **Time** | {{ $event->schedule_start ? $event->schedule_start->format('g:i A') : 'TBD' }} |
| **Location/Link** | {{ $event->location ?? 'Online / TBD' }} |
</x-mail::panel>

### ⚠️ Preparation Requirements

- **Students:** Please ensure your presentation materials are uploaded and you join the session at least 10 minutes prior to the scheduled time.
- **Examiners & Supervisors:** Please review the candidate's manuscript and assessment rubrics available in your portal prior to the event.

<x-mail::button :url="config('app.url') . '/dashboard'" color="primary">
View Event Dashboard →
</x-mail::button>

If you have any scheduling conflicts, please inform the Program Coordinator immediately via the messaging system.

Best regards,

**The ACETEL Directorate**
*Institutional Research & Digital Excellence*

<x-mail::subcopy>
You are receiving this notification because an event was scheduled linking to your profile on the ACETEL Thesis Monitoring System.
</x-mail::subcopy>
</x-mail::message>
