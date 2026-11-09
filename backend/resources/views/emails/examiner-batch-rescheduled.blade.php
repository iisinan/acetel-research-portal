<x-mail::message>

# Presentation Schedule Moved: {{ $milestoneName }}

Dear **{{ $notifiable->name }}**,

This is an official notice to inform you that presentation schedules for **{{ $count }} candidate(s)** under **{{ $milestoneName }}** have been **rescheduled / moved** on the ACETEL Thesis Monitoring System.

As an assigned examiner, please review the updated presentation timetable below:

<x-mail::panel>
## 📅 Updated Presentation Timetable

| Candidate | Matric No. | Previous Slot | New Date & Time | Meeting Link |
|:---|:---|:---|:---|:---|
@foreach($rescheduledItems as $item)
| **{{ $item['candidate_name'] }}** | {{ $item['student_id'] ?? '—' }} | {{ $item['previous_slot'] ?? '—' }} | **{{ $item['new_slot'] }}** | @if(!empty($item['meeting_link']) && filter_var($item['meeting_link'], FILTER_VALIDATE_URL))[Join Meeting]({{ $item['meeting_link'] }})@else{{ $item['meeting_link'] ?? 'Online / Portal' }}@endif |
@endforeach
</x-mail::panel>

### ⚠️ Important Action for Examiners:
- **Assessment & Grading:** Please access candidate manuscripts, presentations, and scoring rubrics directly via your Presentations dashboard.
- **Punctuality:** Please join the designated track meeting link ahead of each candidate's scheduled presentation slot.

<x-mail::button :url="$presentationsUrl" color="primary">
Open Examiner Presentations Dashboard →
</x-mail::button>

Best regards,

**The ACETEL Directorate**  
*Institutional Research & Digital Excellence*

<x-mail::subcopy>
You are receiving this notification because candidates you evaluate were rescheduled on the ACETEL Thesis Monitoring System.
</x-mail::subcopy>
</x-mail::message>
