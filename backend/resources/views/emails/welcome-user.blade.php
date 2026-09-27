<x-mail::message>

# Welcome to the ACETEL Research Portal

Dear **{{ $user->name }}**,

We are delighted to inform you that your institutional research account has been successfully created on the **ACETEL Thesis Monitoring System**. You now have full access to our research management infrastructure.

Please find your secure login credentials below.

<x-mail::panel>
## 🔐 Your Access Credentials

**Portal URL:** [{{ config('app.url') }}]({{ config('app.url') }})
**Email Address:** `{{ $user->email }}`
**Temporary Password:** `{{ $password }}`
</x-mail::panel>

### 🛡️ Mandatory First-Login Action

For the security of your account and compliance with ACETEL's institutional data policy, you **must change this temporary password** immediately after your first successful login. Failure to do so may result in restricted access to certain features.

<x-mail::button :url="config('app.url') . '/login'" color="primary">
Access the Research Portal →
</x-mail::button>

### 💡 Getting Started

Once you are logged in, here are your recommended first steps:

- **Complete Your Profile** — Add your department, contact details, and profile photo.
- **Review Your Thesis Dashboard** — Explore your assigned milestones and submission schedule.
- **Connect with Your Supervisor** — Use the integrated messaging system to introduce yourself.
- **Set Notification Preferences** — Stay informed on upcoming deadlines and feedback.

---

If you did not expect to receive this email, or if you believe this account was created in error, please contact the ACETEL ICT Helpdesk immediately at **{{ config('app.url') }}**.

Best regards,

**The ACETEL Directorate**
*Institutional Research & Digital Excellence*

<x-mail::subcopy>
You are receiving this email because an administrator provisioned a research account for you on the ACETEL Thesis Monitoring System. Please do not reply to this automated email.
</x-mail::subcopy>
</x-mail::message>
