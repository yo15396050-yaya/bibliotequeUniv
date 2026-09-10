<?php

namespace App\Notifications;

use App\Models\Renouvellement;
use Illuminate\Notifications\Messages\MailMessage;

class RenouvellementTraite extends NotificationBibliotheque
{
    public function __construct(public readonly Renouvellement $renouvellement) {}

    public function icone(): string
    {
        return 'fa-arrows-rotate';
    }

    public function couleur(): string
    {
        return $this->renouvellement->statut === 'accepte' ? 'success' : 'danger';
    }

    public function toMail(object $notifiable): MailMessage
    {
        $accepte = $this->renouvellement->statut === 'accepte';

        return $this->mail(
            $accepte ? 'Renouvellement accepté' : 'Renouvellement refusé',
            "Bonjour {$notifiable->name},",
            array_filter([
                $accepte
                    ? "Votre demande de renouvellement pour « {$this->renouvellement->emprunt?->livre?->titre} » a été acceptée."
                    : "Votre demande de renouvellement pour « {$this->renouvellement->emprunt?->livre?->titre} » a été refusée.",
                $accepte && $this->renouvellement->nouvelle_echeance
                    ? 'Nouvelle date limite : '.$this->renouvellement->nouvelle_echeance->translatedFormat('d F Y').'.'
                    : null,
                ! $accepte ? ($this->renouvellement->motif_refus ?: null) : null,
            ]),
            route('mes-emprunts'),
            'Voir mes emprunts'
        );
    }

    public function toArray(object $notifiable): array
    {
        $accepte = $this->renouvellement->statut === 'accepte';

        return [
            'type' => 'renouvellement',
            'titre' => $accepte ? 'Renouvellement accepté' : 'Renouvellement refusé',
            'message' => $accepte
                ? 'Nouvelle échéance : '.$this->renouvellement->nouvelle_echeance?->format('d/m/Y').'.'
                : ($this->renouvellement->motif_refus ?: 'Demande refusée.'),
            'renouvellement_id' => $this->renouvellement->id,
            'url' => route('mes-emprunts'),
            'icone' => $this->icone(),
            'couleur' => $this->couleur(),
        ];
    }
}
