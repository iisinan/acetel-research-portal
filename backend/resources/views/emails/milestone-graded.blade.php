<x-mail::message>

# {{ $isCleared ? 'Institutional Clearance Granted' : 'Milestone Evaluation Returned' }}

Dear **{{ $milestone->thesis->student->user->name ?? 'Scholar' }}**,

{{ $isCleared 
    ? 'Congratulations! You have officially been cleared for the following academic milestone by your supervision panel and the ACETEL Academic Directorate.' 
    : 'Your supervisor or the academic panel has reviewed your recent submission and provided formal evaluation feedback.' }}

<x-mail::panel>
## 📊 Evaluation Summary

| | |
|---|---|
| **Milestone** | {{ $milestone->template->name }} |
| **Current Status** | **{{ strtoupper(str_replace('_', ' ', $milestone->status)) }}** |
| **Evaluated On** | {{ now()->format('F j, Y') }} |
</x-mail::panel>

### 📋 Next Steps

@if($isCleared)
- You may now proceed to the next phase of your academic research.
- Check your portal to view any final remarks or instructions for the upcoming milestone.
@else
- Log in to the portal to read the detailed feedback and examiner remarks.
- Review any generated Action Items assigned to you.
- Make the necessary revisions and prepare for your next submission.
@endif

<x-mail::button :url="config('app.url')" color="{{ $isCleared ? 'success' : 'primary' }}">
View Official Feedback →
</x-mail::button>

Please ensure you adhere strictly to the designated academic protocols moving forward.

Best regards,

**The ACETEL Academic Directorate**
*Thesis Monitoring & Research Excellence*

<x-mail::subcopy>
You are receiving this notification because an evaluation was logged against your academic profile on the ACETEL Thesis Monitoring System.
</x-mail::subcopy>
</x-mail::message>
