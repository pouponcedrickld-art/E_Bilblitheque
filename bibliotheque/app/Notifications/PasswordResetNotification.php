<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordResetNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $temporaryPassword) {}

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->subject('Réinitialisation de votre mot de passe')
            ->greeting('Bonjour,')
            ->line('Un administrateur a réinitialisé votre mot de passe sur la plateforme Bibliothèque Numérique.')
            ->line('Voici votre nouveau mot de passe temporaire :')
            ->line("**{$this->temporaryPassword}**")
            ->line('Nous vous recommandons de changer ce mot de passe dès votre prochaine connexion.')
            ->action('Se connecter', url('/login'))
            ->line('Si vous n\'avez pas demandé cette réinitialisation, veuillez contacter un administrateur.');
    }
}
