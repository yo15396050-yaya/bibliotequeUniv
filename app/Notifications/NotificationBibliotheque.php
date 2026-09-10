<?php

namespace App\Notifications;

use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Base commune : choisit les canaux actifs et fabrique un mail cohérent.
 * Les notifications sont mises en file d'attente pour ne pas ralentir
 * les opérations de guichet.
 */
abstract class NotificationBibliotheque extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return NotificationService::canaux();
    }

    /** Icône Font Awesome affichée dans le centre de notifications. */
    abstract public function icone(): string;

    /** Couleur Bootstrap du badge. */
    public function couleur(): string
    {
        return 'primary';
    }

    protected function mail(string $sujet, string $salutation, array $lignes, ?string $url = null, ?string $libelleAction = null): MailMessage
    {
        $message = (new MailMessage)
            ->subject($sujet)
            ->greeting($salutation);

        foreach ($lignes as $ligne) {
            $message->line($ligne);
        }

        if ($url) {
            $message->action($libelleAction ?? 'Consulter', $url);
        }

        return $message->salutation('L\'équipe de la bibliothèque universitaire');
    }
}
