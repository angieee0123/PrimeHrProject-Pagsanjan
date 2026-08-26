<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * The six-digit code behind Forgot Password.
 *
 * Sent on the `mail` channel only. A database copy would be a plaintext reset
 * credential sitting in a table any admin can read, which defeats the point of
 * hashing it on `password_reset_codes`.
 */
class PasswordResetCodeNotification extends Notification
{
    public function __construct(
        private string $code,
        private int $ttlMinutes,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your ' . config('app.name') . ' password reset code')
            ->markdown('emails.password-reset-code', [
                'name'       => $notifiable->name ?? '',
                'code'       => $this->code,
                'ttlMinutes' => $this->ttlMinutes,
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}
