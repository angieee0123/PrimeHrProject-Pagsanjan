<x-mail::message>
# Password reset code

Hello{{ $name !== '' ? ' ' . $name : '' }},

Someone asked to reset the password for this address on the Pagsanjan Human
Resources Information System. Enter this code on the reset page to continue.

<table style="width: 100%; border-collapse: collapse; margin: 24px 0; font-family: Arial, Helvetica, sans-serif;">
    <tr>
        <td style="padding: 18px; background-color: #f8fafc; border: 1px solid #e2e8f0; text-align: center; font-size: 30px; font-weight: bold; letter-spacing: 8px; color: #0f172a;">{{ $code }}</td>
    </tr>
</table>

The code expires in {{ $ttlMinutes }} minutes and can only be used once.

**If you did not ask for this, no action is needed** — your password has not
changed, and nobody can change it without this code. Do not forward this
message: anyone holding these six digits can take over your account.

<x-mail::button :url="route('password.forgot')">
Open the reset page
</x-mail::button>

Regards,<br>
{{ config('app.name') }}
</x-mail::message>
