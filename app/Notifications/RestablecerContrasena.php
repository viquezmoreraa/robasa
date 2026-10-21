<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RestablecerContrasena extends Notification
{
    public function __construct(private string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
        $minutos = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject('Restablecer contraseña | ROBASA')
            ->greeting('Hola, '.$notifiable->name)
            ->line('Recibimos una solicitud para restablecer la contraseña de su cuenta en ROBASA.')
            ->action('Restablecer contraseña', $url)
            ->line("Este enlace vence en {$minutos} minutos y solo puede usarse una vez.")
            ->line('Si usted no lo solicitó, ignore este correo: su contraseña actual sigue funcionando.')
            ->salutation('Saludos, equipo ROBASA');
    }
}
