<tr>
<td>
<table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="content-cell" align="center" style="padding: 24px 32px;">
    <!-- Divider -->
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td style="border-top: 1px solid #d1dae8; padding-bottom: 20px;"></td>
        </tr>
    </table>

    <!-- Logo row -->
    <img src="https://aceteltms.nou.edu.ng/images/acetel-logo.jpeg"
         alt="ACETEL"
         width="36"
         height="36"
         style="border-radius: 6px; display: block; margin: 0 auto 10px;">

    <!-- Institution name -->
    <p style="
        font-size: 12px;
        font-weight: 700;
        color: #1a2d5a;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin: 0 0 4px;
    ">ACETEL</p>

    <!-- Tagline -->
    <p style="font-size: 11px; color: #9ca3af; margin: 0 0 12px;">
        Africa Centre for Excellence in Technology Enhanced Learning
    </p>

    <!-- Auto-message note -->
    <p style="font-size: 11px; color: #b0b8c8; margin: 0 0 6px;">
        {{ Illuminate\Mail\Markdown::parse($slot) }}
    </p>

    <!-- Inquiries -->
    <p style="font-size: 12px; color: #6b7280; margin: 0 0 10px;">
        For enquiries, contact us at
        <a href="mailto:isinan@noun.edu.ng" style="color: #2e6fcd; text-decoration: underline; font-weight: 600;">isinan@noun.edu.ng</a>
    </p>

    <!-- Divider -->
    <p style="font-size: 11px; color: #d1dae8; margin: 0 0 8px;">— — —</p>

    <!-- Copyright / Links row -->
    <p style="font-size: 11px; color: #b0b8c8; margin: 0;">
        &copy; {{ date('Y') }} ACETEL Research Directorate &mdash;
        <a href="{{ config('app.url') }}" style="color: #6b7280; text-decoration: underline;">Visit Portal</a>
    </p>
</td>
</tr>
</table>
</td>
</tr>
