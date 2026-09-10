<?php

namespace App\Notifications;

use App\Models\Emprunt;
use Illuminate\Notifications\Messages\MailMessage;

class EcheanceProche extends NotificationBibliotheque
{
    public function __construct(public readonly Emprunt $emprunt) {}

    public function icone(): string
    {
        return 'fa-clock';
    }

    public function couleur(): string
    {
        return 'warning';
    }

    public function toMail(object $notifiable): MailMessage
    {
        $jours = $this->emprunt->joursRestants();

        return $this->mail(
            'Rappel : retour prévu dans '.$jours.' jour(s)',
            "Bonjour {$notifiable->name},",
            [
                "Le retour de « {$this->emprunt->livre?->titre} » est prévu le "
                    .$this->emprunt->date_retour_prevue->translatedFormat('d F Y').'.',
                'Pensez à le rapporter ou à demander un renouvellement pour éviter une pénalité.',
            ],
            route('mes-emprunts'),
            'Voir mes emprunts'
        );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'echeance',
            'titre' => 'Échéance proche',
            'message' => "« {$this->emprunt->livre?->titre} » est à rendre le "
                .$this->emprunt->date_retour_prevue->format('d/m/Y').'.',
            'emprunt_id' => $this->emprunt->id,
            'url' => route('mes-emprunts'),
            'icone' => $this->icone(),
            'couleur' => $this->couleur(),
        ];
    }
}
