<?php

namespace App\Notifications;

use App\Models\Emprunt;
use App\Models\Penalite;
use App\Support\Parametres;
use Illuminate\Notifications\Messages\MailMessage;

class RetourEnregistre extends NotificationBibliotheque
{
    public function __construct(
        public readonly Emprunt $emprunt,
        public readonly ?Penalite $penalite = null,
    ) {}

    public function icone(): string
    {
        return 'fa-rotate-left';
    }

    public function couleur(): string
    {
        return $this->penalite ? 'warning' : 'success';
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->mail(
            'Retour enregistré : '.$this->emprunt->livre?->titre,
            "Bonjour {$notifiable->name},",
            array_filter([
                "Le retour de « {$this->emprunt->livre?->titre} » a bien été enregistré le "
                    .$this->emprunt->date_retour_effective?->translatedFormat('d F Y').'.',
                $this->penalite
                    ? 'Une pénalité de '.Parametres::formaterMontant($this->penalite->montant)
                        ." a été appliquée ({$this->penalite->jours_retard} jour(s) de retard)."
                    : 'Merci d\'avoir respecté les délais.',
            ]),
            route('mes-emprunts'),
            'Voir mes emprunts'
        );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'retour',
            'titre' => 'Retour enregistré',
            'message' => "« {$this->emprunt->livre?->titre} » a été rendu."
                .($this->penalite ? ' Pénalité : '.Parametres::formaterMontant($this->penalite->montant).'.' : ''),
            'emprunt_id' => $this->emprunt->id,
            'url' => route('mes-emprunts'),
            'icone' => $this->icone(),
            'couleur' => $this->couleur(),
        ];
    }
}
