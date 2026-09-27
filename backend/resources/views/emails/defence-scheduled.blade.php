<x-mail::message>

# {{ $defenceTypeLabel }} — Official Schedule Confirmation

Dear **{{ $user->name }}**,

Congratulations on reaching this important milestone in your academic journey. The ACETEL Academic Directorate is pleased to confirm that your **{{ $defenceTypeLabel }}** has been officially scheduled.

Please review the details carefully and prepare accordingly.

<x-mail::panel>

## 📅 Defence Schedule Details

| | |
|---|---|
| **Event** | {{ $defenceTypeLabel }} |
| **Candidate** | {{ $user->name }} |
| **Scheduled Date** | {{ \Carbon\Carbon::parse($defenceDate)->format('l, F j, Y') }} |
| **Status** | Confirmed ✓ |

</x-mail::panel>

### 📋 Pre-Defence Checklist

To ensure a successful defence, please complete the following before your scheduled date:

- ✅ **Review** all submitted chapters and milestone documents in the portal.
- ✅ **Prepare** a clear, structured presentation (typically 15–20 minutes).
- ✅ **Confirm** your attendance with your supervisor and program coordinator.
- ✅ **Test** any presentation equipment or materials beforehand.
- ✅ **Arrive** at the designated venue **at least 15 minutes early**.

> **Important Notice:** Failure to appear on the scheduled date without prior written notification to the Academic Directorate may result in rescheduling penalties or administrative action as per ACETEL institutional regulations.

<x-mail::button :url="config('app.url')" color="primary">
View Your Research Portal →
</x-mail::button>

### 📞 Need Assistance?

If you have any questions about your defence date, venue, or preparation requirements, please reach out to:

- **Your Supervisor** — via the portal messaging system
- **Your Program Coordinator** — through the research portal
- **ACETEL Administration** — through the Help & Support section

We wish you all the best in your upcoming defence. Your hard work and dedication to academic excellence are commendable.

Best regards,

**The ACETEL Academic Directorate**
*Thesis Monitoring & Research Excellence*

<x-mail::subcopy>
You are receiving this notification because a formal defence date has been assigned to your research profile on the ACETEL Thesis Monitoring System. If you believe this was sent in error, please contact the administration immediately through the portal. Please do not reply to this automated email.
</x-mail::subcopy>
</x-mail::message>
