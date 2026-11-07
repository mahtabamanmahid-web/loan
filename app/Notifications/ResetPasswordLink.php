<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Email containing the password-reset link (used when the user resets by email). */
class ResetPasswordLink extends Notification
{
    public function __construct(public string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Reset your password').' – '.site_name())
            ->line(__('You requested a password reset. This link is valid for 60 minutes.'))
            ->action(__('Reset Password'), route('password.reset', ['token' => $this->token, 'email' => $notifiable->email]))
            ->line(__('If you did not request this, you can ignore this email.'));
    }
}
