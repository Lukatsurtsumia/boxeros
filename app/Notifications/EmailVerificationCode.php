<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Emails a 6-digit code the fighter types in to verify their email — nicer and less
 * error-prone than a magic link (no opening a link in the wrong browser and being asked
 * to log in again).
 */
class EmailVerificationCode extends Notification
{
    public function __construct(public string $code) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Your BoxerOS verification code'))
            ->greeting(__('Welcome to BoxerOS! 🥊'))
            ->line(__('Enter this code to verify your email and step into your corner:'))
            ->line('**'.$this->code.'**')
            ->line(__('This code expires in 15 minutes.'))
            ->line(__("If you didn't sign up for BoxerOS, you can safely ignore this email."));
    }
}
