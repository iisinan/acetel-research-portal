<x-mail::message>

# New Institutional Message

Dear **{{ $notifiable->name }}**,

You have received a new communication regarding the thesis project: **{{ $messageObj->thesis->title ?? 'Untitled Project' }}**.

<x-mail::panel>
**From:** {{ $messageObj->sender->name ?? 'System User' }}  
**Sent On:** {{ $messageObj->created_at->format('l, F j, Y g:i A') }}

---

*"{!! nl2br(e($messageObj->content)) !!}"*
</x-mail::panel>

### ↩️ How to Reply

Please log in to the ACETEL Research Portal to view the full conversation history and respond securely. Do not reply directly to this email.

<x-mail::button :url="config('app.url')" color="primary">
Open Secure Messaging →
</x-mail::button>

Best regards,

**The ACETEL Directorate**
*Institutional Research & Digital Excellence*

<x-mail::subcopy>
You are receiving this notification because an institutional message was sent to your account on the ACETEL Thesis Monitoring System.
</x-mail::subcopy>
</x-mail::message>
