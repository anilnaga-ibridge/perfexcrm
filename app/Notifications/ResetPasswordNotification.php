<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $resetUrl = url('/admin/reset-password/' . $this->token . '?email=' . urlencode($notifiable->getEmailForPasswordReset()));
        $appName = config('app.name', 'iBRIDGE DIGITAL CRM');

        return (new MailMessage)
            ->subject('Password Reset Request - iBRIDGE DIGITAL CRM')
            ->view('emails.reset_password', [
                'resetUrl' => $resetUrl,
                'email'    => $notifiable->getEmailForPasswordReset(),
                'user'     => $notifiable,
                'appName'  => $appName,
            ]);
    }
}
