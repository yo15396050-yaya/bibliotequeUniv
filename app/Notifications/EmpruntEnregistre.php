<?php

namespace App\Notifications;

use App\Models\Emprunt;
use Illuminate\Notifications\Messages\MailMessage;

class EmpruntEnregistre extends NotificationBibliotheque
{
    public function __construct(public readonly Emprunt $emprunt) {}

    public function icone(): string
    {
        return 'fa-hand-holding';
    }

    public function couleur(): string
    {
        return 'success';
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->mail(
            'Emprunt enregistré : '.$this->emprunt->livre?->titre,
            "Bonjour {$notifiable->name},",
            [
                "Votre emprunt de « {$this->emprunt->livre?->titre} » a bien été enregistré.",
                'Date limite de retour : '.$this->emprunt->date_retour_prevue->translatedFormat('d F Y').'.',
                $this->emprunt->exemplaire
                    ? "Exemplaire : {$this->emprunt->exemplaire->code_barre}."
                    : '',
            ],
            route('emprunts.show', $this->emprunt),
            'Voir mon emprunt'
        );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'emprunt',
            'titre' => 'Emprunt enregistré',
            'message' => "« {$this->emprunt->livre?->titre} » — à rendre le "
                .$this->emprunt->date_retour_prevue->format('d/m/Y').'.',
            'emprunt_id' => $this->emprunt->id,
            'url' => route('emprunts.show', $this->emprunt),
            'icone' => $this->icone(),
            'couleur' => $this->couleur(),
        ];
    }
}
