<x-mail::message>

# Milestone Submission Received

Dear Supervisor,

A student has submitted documentation for an academic milestone that requires your review and evaluation.

<x-mail::panel>
## 📝 Submission Details

| | |
|---|---|
| **Student** | {{ $submission->milestone->thesis->student->user->name ?? 'Student' }} |
| **Milestone** | {{ $submission->milestone->template->name }} |
| **Version** | v{{ $submission->version ?? '1.0' }} |
| **Submitted On** | {{ $submission->created_at->format('l, F j, Y \a\t g:i A') }} |
</x-mail::panel>

### 🔍 Required Actions

As the assigned supervisor, please log in to the research portal to:
- Review the uploaded documents and manuscript.
- Provide structured feedback and actionable recommendations.
- Select an appropriate evaluation decision (e.g., Approve, Revise).

<x-mail::button :url="config('app.url')" color="primary">
Access Portal & Review Submission →
</x-mail::button>

Timely feedback is essential to the academic progress of your supervisee. We appreciate your continued dedication to academic excellence.

Best regards,

**The ACETEL Academic Directorate**
*Thesis Monitoring & Research Excellence*

<x-mail::subcopy>
You are receiving this notification because you are assigned as a supervisor to this thesis project on the ACETEL Thesis Monitoring System.
</x-mail::subcopy>
</x-mail::message>
