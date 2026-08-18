<x-mail::message>
# Hello, newly registered employee!

You have been successfully registered to the Pagsanjan-HRIS Website!

Here are your login credentials in order to access the system.

<table style="width: 100%; border-collapse: collapse; margin: 24px 0; font-family: Arial, Helvetica, sans-serif;">
    @foreach ($details as $key => $value)
        <tr>
            <td style="padding: 10px 14px; background-color: #f8fafc; border: 1px solid #e2e8f0; font-weight: bold; color: #334155; width: 40%;">{{ ucfirst($key) }}</td>
            <td style="padding: 10px 14px; background-color: #ffffff; border: 1px solid #e2e8f0; color: #0f172a;">{{ $value }}</td>
        </tr>
    @endforeach
</table>

Regards,<br>
{{ config('app.name') }}
</x-mail::message>