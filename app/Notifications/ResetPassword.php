<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class ResetPassword extends ResetPasswordNotification
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->token);
        }

        return $this->buildMailMessage($this->token);
    }

    /**
     * Get the reset password notification mail message for the given token.
     *
     * @param  string  $token
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    protected function buildMailMessage($token)
    {
        return (new MailMessage())
            ->subject(Lang::get('Reset Password Notification'))
            ->line(Lang::get('You are receiving this email because we received a password reset request for your account.'))
            ->line(Lang::get('Your token for password reset: '))
            ->line("**$token**")
            ->line(Lang::get('This password reset token will expire in :count minutes.', [
                'count' => config('auth.passwords.' . config('auth.defaults.passwords') . '.expire')
            ]))
            ->line(Lang::get('If you did not request a password reset, no further action is required.'));
    }
}
