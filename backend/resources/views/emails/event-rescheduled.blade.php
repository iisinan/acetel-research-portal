<x-mail::message>

# Presentation Schedule Moved: {{ $milestoneName ?? ucfirst(str_replace('_', ' ', $event->type)) }}

Dear **{{ $notifiable->name }}**,

This is an official notice to inform you that your presentation schedule for **{{ $milestoneName ?? ucfirst(str_replace('_', ' ', $event->type)) }}** on the ACETEL Thesis Monitoring System has been **rescheduled / moved**.

<x-mail::panel>
## 📅 Updated Presentation Schedule

| | |
|---|---|
| **Candidate** | {{ $event->thesis->student->user->name ?? $notifiable->name }} |
| **Milestone / Event** | {{ $milestoneName ?? ucfirst(str_replace('_', ' ', $event->type)) }} |
@if(!empty($previousDate))
| **Previous Date** | {{ \Carbon\Carbon::parse($previousDate)->format('l, F j, Y') }} {{ !empty($previousTime) ? '(' . \Carbon\Carbon::parse($previousTime)->format('g:i A') . ')' : '' }} |
@endif
| **New Date** | **{{ $event->schedule_start ? $event->schedule_start->format('l, F j, Y') : 'TBD' }}** |
| **New Time** | **{{ $event->schedule_start ? $event->schedule_start->format('g:i A') : 'TBD' }}** |
| **Meeting / Zoom Link** | {{ $event->location ?? 'Online / Provided in Portal' }} |
</x-mail::panel>

### ⚠️ Important Action Required

- **Presentation Materials:** Please ensure your slides and manuscript are up to date and uploaded to the portal ahead of your new slot.
- **Attendance:** Please join the presentation link at least 10 minutes prior to your new scheduled time.
- **Support:** If you have an unavoidable conflict with this new date, contact your Program Coordinator immediately through the system messaging.

<x-mail::button :url="config('app.url') . '/dashboard'" color="primary">
View Updated Schedule on Dashboard →
</x-mail::button>

Best regards,

**The ACETEL Directorate**  
*Institutional Research & Digital Excellence*

<x-mail::subcopy>
You are receiving this email because your presentation slot was moved on the ACETEL Thesis Monitoring System.
</x-mail::subcopy>
</x-mail::message>
