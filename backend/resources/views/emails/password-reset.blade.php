<x-mail::message>

# Password Reset Notification

Dear **{{ $user->name }}**,

An administrator has reset your password on the **ACETEL Thesis Monitoring System**. Your temporary credentials are provided below. Please use them to log in immediately and update your password.

<x-mail::panel>
## 🔐 Temporary Credentials

**Portal URL:** [{{ config('app.url') }}]({{ config('app.url') }})
**Email Address:** `{{ $user->email }}`
**New Temporary Password:** `{{ $password }}`
</x-mail::panel>

### 🛡️ Required Action

For the protection of your academic records and data, you are **required to change this password** immediately after logging in. This temporary password will remain active until you update it.

<x-mail::button :url="config('app.url') . '/login'" color="primary">
Log In & Secure Your Account →
</x-mail::button>

### ⚠️ Did Not Request This Reset?

If you did not request or authorise this password reset, your account may have been accessed without your knowledge. Please take the following steps immediately:

1. **Do not use** the temporary password above.
2. **Contact** the ACETEL ICT Helpdesk through the research portal.
3. **Report** the incident to your Program Coordinator.

---

Best regards,

**The ACETEL Directorate**
*Institutional Research & Digital Excellence*

<x-mail::subcopy>
You are receiving this email because an administrator initiated a password reset for your account on the ACETEL Thesis Monitoring System. Please do not reply to this automated email.
</x-mail::subcopy>
</x-mail::message>
