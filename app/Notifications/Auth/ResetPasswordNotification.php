<?php

namespace App\Notifications\Auth;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    public function __construct(
        private readonly string $token
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url =  config('app.url') . '/reset-password?' . http_build_query([
                'token' => $this->token,
                'email' => $notifiable->email
            ]);

        return (new MailMessage)
            ->subject('Reset hasła - Wartki Nurt')
            ->greeting('Cześć!')
            ->line('Otrzymujesz tego emaila, ponieważ otrzymaliśmy prośbę o reset hasła dla Twojego konta.')
            ->action('Resetuj hasło', $url)
            ->line('Link do resetowania hasła wygaśnie za 24 godziny.')
            ->line('Jeśli nie prosiłeś o reset hasła, zignoruj tę wiadomość.');
    }
}
