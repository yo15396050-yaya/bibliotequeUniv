<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Notifications\Messages\MailMessage;

class ReservationExpiree extends NotificationBibliotheque
{
    public function __construct(public readonly Reservation $reservation) {}

    public function icone(): string
    {
        return 'fa-hourglass-end';
    }

    public function couleur(): string
    {
        return 'secondary';
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->mail(
            'Réservation expirée : '.$this->reservation->livre?->titre,
            "Bonjour {$notifiable->name},",
            [
                "Votre réservation pour « {$this->reservation->livre?->titre} » a expiré faute de retrait dans le délai imparti.",
                'Vous pouvez réserver à nouveau depuis le catalogue.',
            ],
            route('catalogue'),
            'Retourner au catalogue'
        );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'reservation_expiree',
            'titre' => 'Réservation expirée',
            'message' => "Votre réservation pour « {$this->reservation->livre?->titre} » a expiré.",
            'reservation_id' => $this->reservation->id,
            'url' => route('reservations.index'),
            'icone' => $this->icone(),
            'couleur' => $this->couleur(),
        ];
    }
}
