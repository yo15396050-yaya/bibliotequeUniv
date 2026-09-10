<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Notifications\Messages\MailMessage;

class ReservationDisponible extends NotificationBibliotheque
{
    public function __construct(public readonly Reservation $reservation) {}

    public function icone(): string
    {
        return 'fa-bell';
    }

    public function couleur(): string
    {
        return 'success';
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->mail(
            'Votre réservation est disponible : '.$this->reservation->livre?->titre,
            "Bonjour {$notifiable->name},",
            [
                "L'ouvrage « {$this->reservation->livre?->titre} » que vous aviez réservé est disponible.",
                'Il est mis de côté jusqu\'au '
                    .$this->reservation->date_limite_retrait?->translatedFormat('d F Y').'.',
                'Passé ce délai, la réservation sera proposée à l\'usager suivant.',
            ],
            route('reservations.show', $this->reservation),
            'Voir ma réservation'
        );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'reservation_disponible',
            'titre' => 'Réservation disponible',
            'message' => "« {$this->reservation->livre?->titre} » vous attend jusqu'au "
                .$this->reservation->date_limite_retrait?->format('d/m/Y').'.',
            'reservation_id' => $this->reservation->id,
            'url' => route('reservations.show', $this->reservation),
            'icone' => $this->icone(),
            'couleur' => $this->couleur(),
        ];
    }
}
