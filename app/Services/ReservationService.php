<?php

namespace App\Services;

use App\Exceptions\RegleMetierException;
use App\Models\Emprunt;
use App\Models\Exemplaire;
use App\Models\Livre;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\ReservationDisponible;
use App\Notifications\ReservationExpiree;
use App\Support\Parametres;
use Illuminate\Support\Facades\DB;

/**
 * File d'attente des réservations.
 *
 * Règle : réserver ne consomme PAS de stock. Un exemplaire n'est mis de côté
 * qu'au moment où il redevient disponible, au profit du premier de la file.
 */
class ReservationService
{
    public function __construct(
        private readonly AuditService $audit,
        private readonly NotificationService $notifications,
    ) {}

    /**
     * @return array<int, string> motifs de refus (vide = réservation possible)
     */
    public function verifierEligibilite(User $user, Livre $livre): array
    {
        $motifs = [];

        if (! $user->estEmprunteur()) {
            $motifs[] = 'Seuls les étudiants et les enseignants peuvent réserver.';
        }

        if (! $user->estActif()) {
            $motifs[] = 'Ce compte est inactif ou suspendu.';
        }

        if ($user->reservations()->actives()->where('livre_id', $livre->id)->exists()) {
            $motifs[] = 'Vous avez déjà une réservation active sur cet ouvrage.';
        }

        if ($user->emprunts()->enCours()->where('livre_id', $livre->id)->exists()) {
            $motifs[] = 'Vous avez déjà cet ouvrage en cours d\'emprunt.';
        }

        $max = Parametres::entier('reservation.max_par_usager', 3);
        if ($user->reservations()->actives()->count() >= $max) {
            $motifs[] = "Vous avez atteint le nombre maximal de réservations actives ({$max}).";
        }

        return $motifs;
    }

    /**
     * Place l'usager dans la file d'attente de l'ouvrage.
     *
     * @throws RegleMetierException
     */
    public function reserver(User $user, Livre $livre, ?string $notes = null): Reservation
    {
        $motifs = $this->verifierEligibilite($user, $livre);
        if ($motifs !== []) {
            throw RegleMetierException::avec($motifs, 'Réservation impossible :');
        }

        return DB::transaction(function () use ($user, $livre, $notes) {
            $position = Reservation::fileDAttente($livre->id)->lockForUpdate()->count() + 1;
            $duree = Parametres::entier('reservation.duree_validite', 7);

            $reservation = Reservation::create([
                'user_id' => $user->id,
                'livre_id' => $livre->id,
                'date_reservation' => now()->toDateString(),
                'date_expiration' => now()->addDays($duree)->toDateString(),
                'statut' => Reservation::STATUT_ACTIVE,
                'position_file_attente' => $position,
                'notes' => $notes,
            ]);

            $this->audit->enregistrer(
                'reservation',
                "Réservation de « {$livre->titre} » par {$user->name} (position {$position})",
                $reservation,
                ['position' => $position],
                'reservations'
            );

            // Un exemplaire est déjà libre et personne devant : on notifie tout de suite.
            if ($position === 1 && $livre->exemplaires_disponibles > 0) {
                $this->notifierProchainDeLaFile($livre, $livre->exemplairesDisponibles()->first());
            }

            return $reservation;
        });
    }

    /**
     * Notifie le premier réservataire de la file qu'un exemplaire est prêt,
     * et fixe son délai de retrait.
     */
    public function notifierProchainDeLaFile(Livre $livre, ?Exemplaire $exemplaire = null): ?Reservation
    {
        $reservation = Reservation::with('user')
            ->fileDAttente($livre->id)
            ->whereNull('date_notification')
            ->first();

        if (! $reservation) {
            return null;
        }

        $delai = Parametres::entier('reservation.delai_retrait', 2);

        $reservation->update([
            'exemplaire_id' => $exemplaire?->id,
            'date_notification' => now(),
            'date_limite_retrait' => now()->addDays($delai)->toDateString(),
        ]);

        if ($exemplaire) {
            $exemplaire->update(['statut' => Exemplaire::STATUT_RESERVE]);
            $livre->synchroniserCompteurs();
        }

        $this->notifications->envoyer($reservation->user, new ReservationDisponible($reservation));

        $this->audit->enregistrer(
            'reservation',
            "Exemplaire mis à disposition pour la réservation #{$reservation->id}",
            $reservation,
            ['exemplaire' => $exemplaire?->code_barre, 'limite' => $reservation->date_limite_retrait?->toDateString()],
            'reservations'
        );

        return $reservation;
    }

    /**
     * Honore la réservation de l'usager lorsqu'il retire effectivement l'ouvrage.
     */
    public function honorerPour(User $user, Livre $livre, ?Emprunt $emprunt = null): ?Reservation
    {
        $reservation = Reservation::actives()
            ->where('user_id', $user->id)
            ->where('livre_id', $livre->id)
            ->orderBy('position_file_attente')
            ->first();

        if (! $reservation) {
            return null;
        }

        $reservation->update(['statut' => Reservation::STATUT_HONOREE]);
        $this->reordonnerFile($livre);

        $this->audit->enregistrer(
            'reservation',
            "Réservation #{$reservation->id} honorée",
            $reservation,
            ['emprunt_id' => $emprunt?->id],
            'reservations'
        );

        return $reservation;
    }

    public function annuler(Reservation $reservation, ?string $motif = null): Reservation
    {
        if ($reservation->statut !== Reservation::STATUT_ACTIVE) {
            throw new RegleMetierException('Cette réservation n\'est plus active.');
        }

        return DB::transaction(function () use ($reservation, $motif) {
            $reservation->update([
                'statut' => Reservation::STATUT_ANNULEE,
                'notes' => trim((string) $reservation->notes."\nAnnulation : ".(string) $motif),
            ]);

            $this->libererExemplaire($reservation);
            $this->reordonnerFile($reservation->livre);

            $this->audit->enregistrer(
                'annulation',
                "Réservation #{$reservation->id} annulée",
                $reservation,
                ['motif' => $motif],
                'reservations'
            );

            return $reservation;
        });
    }

    /**
     * Expire les réservations non retirées dans le délai et passe la main
     * au suivant dans la file.
     *
     * @return int nombre de réservations expirées
     */
    public function expirerReservationsDepassees(): int
    {
        $expirees = 0;

        Reservation::with(['user', 'livre', 'exemplaire'])
            ->actives()
            ->where(function ($q) {
                $q->whereNotNull('date_limite_retrait')
                    ->whereDate('date_limite_retrait', '<', now()->toDateString());
            })
            ->orWhere(function ($q) {
                $q->where('statut', Reservation::STATUT_ACTIVE)
                    ->whereNull('date_limite_retrait')
                    ->whereDate('date_expiration', '<', now()->toDateString());
            })
            ->chunkById(200, function ($reservations) use (&$expirees) {
                foreach ($reservations as $reservation) {
                    DB::transaction(function () use ($reservation, &$expirees) {
                        $reservation->update(['statut' => Reservation::STATUT_EXPIREE]);
                        $exemplaire = $this->libererExemplaire($reservation);

                        $this->notifications->envoyer($reservation->user, new ReservationExpiree($reservation));

                        if ($reservation->livre) {
                            $this->reordonnerFile($reservation->livre);
                            // Au suivant.
                            $this->notifierProchainDeLaFile($reservation->livre, $exemplaire);
                        }

                        $expirees++;
                    });
                }
            });

        return $expirees;
    }

    /** Remet l'exemplaire mis de côté en circulation. */
    private function libererExemplaire(Reservation $reservation): ?Exemplaire
    {
        $exemplaire = $reservation->exemplaire;

        if ($exemplaire && $exemplaire->statut === Exemplaire::STATUT_RESERVE) {
            $exemplaire->update(['statut' => Exemplaire::STATUT_DISPONIBLE]);
            $reservation->livre?->synchroniserCompteurs();
        }

        return $exemplaire;
    }

    /** Renumérote la file d'attente après un départ. */
    public function reordonnerFile(?Livre $livre): void
    {
        if (! $livre) {
            return;
        }

        Reservation::fileDAttente($livre->id)->get()
            ->each(fn (Reservation $reservation, int $index) => $reservation->forceFill([
                'position_file_attente' => $index + 1,
            ])->save());
    }
}
