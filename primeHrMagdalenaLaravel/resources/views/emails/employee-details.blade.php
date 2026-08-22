<x-mail::message>
# Your {{ config('app.name') }} account

Hello{{ isset($details['Name']) && $details['Name'] !== '' ? ' ' . $details['Name'] : '' }},

An account has been created for you on the Pagsanjan Human Resources
Information System. Here are the credentials you will use to sign in.

<table style="width: 100%; border-collapse: collapse; margin: 24px 0; font-family: Arial, Helvetica, sans-serif;">
    @foreach ($details as $label => $value)
        <tr>
            <td style="padding: 10px 14px; background-color: #f8fafc; border: 1px solid #e2e8f0; font-weight: bold; color: #334155; width: 40%;">{{ $label }}</td>
            <td style="padding: 10px 14px; background-color: #ffffff; border: 1px solid #e2e8f0; color: #0f172a;">{{ $value }}</td>
        </tr>
    @endforeach
</table>

**Verify your email address first.** We sent you a separate message with a
verification link. Open that link before signing in — your account is not
confirmed until you do.

<x-mail::button :url="route('login')">
Go to sign in
</x-mail::button>

For your own security, change this password once you are signed in, and do not
forward this message to anyone.

Regards,<br>
{{ config('app.name') }}
</x-mail::message>
