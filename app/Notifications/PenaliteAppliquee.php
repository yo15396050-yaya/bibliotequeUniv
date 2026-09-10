<?php

namespace App\Notifications;

use App\Models\Penalite;
use App\Support\Parametres;
use Illuminate\Notifications\Messages\MailMessage;

class PenaliteAppliquee extends NotificationBibliotheque
{
    public function __construct(public readonly Penalite $penalite) {}

    public function icone(): string
    {
        return 'fa-money-bill-wave';
    }

    public function couleur(): string
    {
        return 'danger';
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->mail(
            'Nouvelle pénalité : '.Parametres::formaterMontant($this->penalite->montant),
            "Bonjour {$notifiable->name},",
            [
                "Une pénalité de type « {$this->penalite->libelle_type} » vous a été appliquée.",
                'Montant : '.Parametres::formaterMontant($this->penalite->montant).'.',
                $this->penalite->motif ?: '',
                'Vous pouvez la régler auprès du service de la bibliothèque.',
            ],
            route('penalites.index'),
            'Voir mes pénalités'
        );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'penalite',
            'titre' => 'Pénalité appliquée',
            'message' => $this->penalite->libelle_type.' — '
                .Parametres::formaterMontant($this->penalite->montant).'.',
            'penalite_id' => $this->penalite->id,
            'url' => route('penalites.index'),
            'icone' => $this->icone(),
            'couleur' => $this->couleur(),
        ];
    }
}
