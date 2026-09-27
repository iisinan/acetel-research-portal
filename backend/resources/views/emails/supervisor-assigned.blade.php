<x-mail::message>

# Formal Supervision Assignment

Dear **{{ $notifiable->name }}**,

You have been formally appointed as a **{{ ucfirst($assignment->role) }} Supervisor** for a new thesis project by the ACETEL Academic Directorate. 

<x-mail::panel>
## 📋 Project & Student Details

| | |
|---|---|
| **Student** | {{ $assignment->thesisProject->student->user->name ?? 'N/A' }} |
| **Programme** | {{ $assignment->thesisProject->student->program->name ?? 'N/A' }} |
| **Thesis Title** | {{ $assignment->thesisProject->title }} |
| **Your Role** | {{ ucfirst($assignment->role) }} Supervisor |
| **Assigned On** | {{ $assignment->assigned_at ? $assignment->assigned_at->format('F j, Y') : now()->format('F j, Y') }} |
</x-mail::panel>

### 📌 Your Next Steps

As the assigned supervisor, you are expected to:
- **Review** the student's submitted research proposal and timeline.
- **Communicate** with the student via the portal to establish meeting schedules.
- **Guide** the student through their upcoming academic milestones.

<x-mail::button :url="config('app.url') . '/dashboard'" color="primary">
View Project & Student Dashboard →
</x-mail::button>

We trust you will provide the guidance necessary for the student's successful academic progress.

Best regards,

**The ACETEL Directorate**
*Institutional Research & Digital Excellence*

<x-mail::subcopy>
You are receiving this notification because an administrative allocation was recorded on your faculty profile in the ACETEL Thesis Monitoring System.
</x-mail::subcopy>
</x-mail::message>
