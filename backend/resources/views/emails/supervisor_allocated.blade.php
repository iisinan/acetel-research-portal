<x-mail::message>

# {{ $details['greeting'] }}

{{ $details['body'] }}

@if(isset($details['user_details']) && !empty($details['user_details']))
<x-mail::panel>

## 📋 {{ $details['user_role'] }} Information

| Field | Details |
|-------|---------|
| **Name** | {{ $details['user_details']['name'] }} |
| **Email** | {{ $details['user_details']['email'] }} |
@if(isset($details['user_details']['department']))
| **Department** | {{ $details['user_details']['department'] }} |
@endif
@if(isset($details['user_details']['program']))
| **Programme** | {{ $details['user_details']['program'] }} |
@endif
@if(isset($details['user_details']['thesis_title']))
| **Thesis Title** | {{ $details['user_details']['thesis_title'] }} |
@endif

</x-mail::panel>
@endif

### 📌 What This Means For You

@if(isset($details['user_role']) && $details['user_role'] === 'Supervisor')
As a **supervisor**, you are now formally responsible for guiding this student through their thesis journey. Please:

- **Review** the student's submitted thesis proposal and documentation in the portal.
- **Schedule** an introductory meeting to align on expectations and milestones.
- **Monitor** progress regularly and provide timely feedback on submissions.
@else
A **supervisor** has been assigned to your thesis project. You can now:

- **View** your supervisor's profile and contact details in the portal.
- **Connect** via the integrated messaging system to introduce yourself.
- **Review** upcoming milestone deadlines with your supervisor's guidance.
@endif

@if(isset($details['action_text']) && isset($details['action_url']))
<x-mail::button :url="$details['action_url']" color="success">
{{ $details['action_text'] }} →
</x-mail::button>
@endif

---

If you have any questions or concerns about this assignment, please contact your **Program Coordinator** through the research portal.

Best regards,

**The ACETEL Directorate**
*Institutional Research & Digital Excellence*

<x-mail::subcopy>
You are receiving this notification because a supervisor allocation was recorded on your academic profile in the ACETEL Thesis Monitoring System. Please do not reply to this automated email.
</x-mail::subcopy>
</x-mail::message>
