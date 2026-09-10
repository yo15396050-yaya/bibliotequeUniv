<?php

namespace App\Notifications;

use App\Models\Emprunt;
use App\Support\Parametres;
use Illuminate\Notifications\Messages\MailMessage;

class RetardSignale extends NotificationBibliotheque
{
    public function __construct(public readonly Emprunt $emprunt) {}

    public function icone(): string
    {
        return 'fa-triangle-exclamation';
    }

    public function couleur(): string
    {
        return 'danger';
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->mail(
            'Retard de retour : '.$this->emprunt->livre?->titre,
            "Bonjour {$notifiable->name},",
            [
                "L'ouvrage « {$this->emprunt->livre?->titre} » devait être rendu le "
                    .$this->emprunt->date_retour_prevue->translatedFormat('d F Y').'.',
                'Retard actuel : '.$this->emprunt->joursRetard().' jour(s).',
                'Pénalité estimée : '.Parametres::formaterMontant($this->emprunt->calculerPenaliteRetard()).'.',
                'Merci de le rapporter dans les meilleurs délais.',
            ],
            route('mes-emprunts'),
            'Voir mes emprunts'
        );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'retard',
            'titre' => 'Emprunt en retard',
            'message' => "« {$this->emprunt->livre?->titre} » : {$this->emprunt->joursRetard()} jour(s) de retard.",
            'emprunt_id' => $this->emprunt->id,
            'url' => route('mes-emprunts'),
            'icone' => $this->icone(),
            'couleur' => $this->couleur(),
        ];
    }
}
