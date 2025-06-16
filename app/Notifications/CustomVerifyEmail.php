<?php


namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class CustomVerifyEmail extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verificar dirección de correo electrónico - ConectaPro')
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line('Gracias por registrarte en ConectaPro.')
            ->line('Para completar tu registro, por favor verifica tu dirección de correo electrónico haciendo clic en el botón de abajo.')
            ->action('Verificar Email', $verificationUrl)
            ->line('Si no creaste una cuenta, no se requiere ninguna acción adicional.')
            ->line('Este enlace expirará en 60 minutos.')
            ->salutation('¡Bienvenido al equipo de ConectaPro!');
    }

    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}