<?php

namespace App\Services;

use App\Exceptions\RegleMetierException;
use App\Models\Emprunt;
use App\Models\Exemplaire;
use App\Models\Livre;
use App\Models\Penalite;
use App\Models\Renouvellement;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\EmpruntEnregistre;
use App\Notifications\RenouvellementTraite;
use App\Notifications\RetourEnregistre;
use App\Support\Parametres;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Cœur métier de la circulation : prêt, retour, renouvellement.
 * Toutes les opérations sont transactionnelles et verrouillent l'exemplaire
 * concerné pour éviter les doubles emprunts en cas d'accès concurrent.
 */
class EmpruntService
{
    public function __construct(
        private readonly AuditService $audit,
        private readonly PenaliteService $penalites,
        private readonly ReservationService $reservations,
        private readonly NotificationService $notifications,
    ) {}

    /**
     * Vérifie l'éligibilité d'un usager pour un ouvrage donné.
     *
     * @return array<int, string> motifs de refus (vide = éligible)
     */
    public function verifierEligibilite(User $user, Livre $livre, ?Exemplaire $exemplaire = null): array
    {
        $motifs = $user->motifsBlocageEmprunt();

        // Un exemplaire mis de côté au titre d'une réservation reste empruntable
        // par son bénéficiaire, même s'il n'est plus « disponible ».
        $reserveePourLui = $this->reservationEnAttenteDeRetrait($user, $livre);

        if ($exemplaire) {
            if ($exemplaire->livre_id !== $livre->id) {
                $motifs[] = "Cet exemplaire n'appartient pas à cet ouvrage.";
            } elseif (! $exemplaire->estDisponible()
                && ! ($reserveePourLui && $reserveePourLui->exemplaire_id === $exemplaire->id)) {
                $motifs[] = $exemplaire->statut === Exemplaire::STATUT_EMPRUNTE
                    ? 'Cet exemplaire est déjà emprunté.'
                    : "Cet exemplaire n'est pas disponible ({$exemplaire->libelle_statut}).";
            }
        } elseif (! $livre->estDisponible() && ! $reserveePourLui) {
            $motifs[] = "Aucun exemplaire de cet ouvrage n'est disponible.";
        }

        // Un exemplaire mis de côté pour une réservation ne peut pas partir à
        // quelqu'un d'autre.
        $reservationPrioritaire = $livre->reservationsActives()->first();
        if ($reservationPrioritaire
            && $reservationPrioritaire->user_id !== $user->id
            && $reservationPrioritaire->date_notification !== null
            && $livre->exemplaires_disponibles <= $livre->reservationsActives()->whereNotNull('date_notification')->count()) {
            $motifs[] = 'Ce livre est actuellement réservé par un autre usager.';
        }

        if ($user->emprunts()->enCours()->where('livre_id', $livre->id)->exists()) {
            $motifs[] = 'Cet usager a déjà un exemplaire de cet ouvrage en cours.';
        }

        return array_values(array_unique($motifs));
    }

    /**
     * Enregistre un emprunt.
     *
     * @throws RegleMetierException
     */
    public function enregistrerEmprunt(
        User $user,
        Livre $livre,
        ?Exemplaire $exemplaire = null,
        ?Carbon $echeance = null,
        ?string $notes = null
    ): Emprunt {
        return DB::transaction(function () use ($user, $livre, $exemplaire, $echeance, $notes) {
            $livre = Livre::lockForUpdate()->findOrFail($livre->id);

            // Sélection de l'exemplaire : celui demandé, celui mis de côté pour
            // l'usager au titre de sa réservation, ou le premier disponible.
            if ($exemplaire) {
                $exemplaire = Exemplaire::lockForUpdate()->find($exemplaire->id);
            } else {
                $reservation = $this->reservationEnAttenteDeRetrait($user, $livre);

                $exemplaire = ($reservation?->exemplaire_id
                        ? Exemplaire::lockForUpdate()->find($reservation->exemplaire_id)
                        : null)
                    ?? $livre->exemplaires()->disponibles()->lockForUpdate()->first();
            }

            $motifs = $this->verifierEligibilite($user, $livre, $exemplaire);
            if ($motifs !== []) {
                throw RegleMetierException::avec($motifs, 'Emprunt impossible :');
            }

            $dateEmprunt = now();
            $echeance ??= $dateEmprunt->copy()->addDays($user->dureeEmprunt());

            $emprunt = Emprunt::create([
                'user_id' => $user->id,
                'bibliothecaire_id' => Auth::id(),
                'livre_id' => $livre->id,
                'exemplaire_id' => $exemplaire?->id,
                'date_emprunt' => $dateEmprunt->toDateString(),
                'date_retour_prevue' => $echeance->toDateString(),
                'statut' => Emprunt::STATUT_EN_COURS,
                'notes' => $notes,
            ]);

            if ($exemplaire) {
                $exemplaire->update(['statut' => Exemplaire::STATUT_EMPRUNTE]);
            }

            // Si l'usager retire un ouvrage qu'il avait réservé, on honore la réservation.
            $this->reservations->honorerPour($user, $livre, $emprunt);

            $livre->synchroniserCompteurs();
            $user->increment('nombre_emprunts');

            $this->audit->enregistrer(
                'emprunt',
                "Emprunt de « {$livre->titre} » par {$user->name}",
                $emprunt,
                [
                    'livre_id' => $livre->id,
                    'exemplaire' => $exemplaire?->code_barre,
                    'echeance' => $emprunt->date_retour_prevue->toDateString(),
                ],
                'emprunts'
            );

            $this->notifications->envoyer($user, new EmpruntEnregistre($emprunt));

            return $emprunt->load(['user', 'livre', 'exemplaire']);
        });
    }

    /**
     * Réservation active de cet usager pour cet ouvrage, dont l'exemplaire a
     * été mis de côté (notifiée) et attend d'être retiré.
     */
    private function reservationEnAttenteDeRetrait(User $user, Livre $livre): ?Reservation
    {
        return Reservation::where('user_id', $user->id)
            ->where('livre_id', $livre->id)
            ->where('statut', Reservation::STATUT_ACTIVE)
            ->whereNotNull('date_notification')
            ->orderBy('position_file_attente')
            ->first();
    }

    /**
     * Enregistre le retour d'un emprunt, calcule le retard et la pénalité,
     * remet l'exemplaire en circulation et prévient le premier réservataire.
     *
     * @param  string|null  $etatRetour  neuf|bon|moyen|mauvais|perdu
     */
    public function enregistrerRetour(Emprunt $emprunt, ?string $etatRetour = null, ?string $observation = null): Emprunt
    {
        if (! $emprunt->estEnCours()) {
            throw new RegleMetierException('Cet emprunt a déjà été retourné.');
        }

        return DB::transaction(function () use ($emprunt, $etatRetour, $observation) {
            $emprunt->forceFill([
                'date_retour_effective' => now()->toDateString(),
                'receptionne_par' => Auth::id(),
                'etat_retour' => $etatRetour,
                'statut' => Emprunt::STATUT_RETOURNE,
            ]);

            if ($observation) {
                $emprunt->notes = trim((string) $emprunt->notes."\nRetour : ".$observation);
            }

            $emprunt->save();

            $penalite = $this->penalites->appliquerPenaliteRetard($emprunt);

            $exemplaire = $emprunt->exemplaire;
            if ($exemplaire) {
                $statut = match ($etatRetour) {
                    'perdu' => Exemplaire::STATUT_PERDU,
                    'mauvais' => Exemplaire::STATUT_ENDOMMAGE,
                    default => Exemplaire::STATUT_DISPONIBLE,
                };

                $exemplaire->update(array_filter([
                    'statut' => $statut,
                    'etat' => in_array($etatRetour, array_keys(Exemplaire::ETATS), true) ? $etatRetour : null,
                ]));
            }

            if ($etatRetour === 'perdu') {
                $emprunt->update(['statut' => Emprunt::STATUT_PERDU]);
                $this->penalites->creerPenalite(
                    $emprunt->user,
                    Penalite::TYPE_PERTE,
                    null,
                    "Perte de « {$emprunt->livre?->titre} »",
                    $emprunt
                );
            } elseif ($etatRetour === 'mauvais') {
                $this->penalites->creerPenalite(
                    $emprunt->user,
                    Penalite::TYPE_DEGRADATION,
                    null,
                    "Dégradation de « {$emprunt->livre?->titre} »",
                    $emprunt
                );
            }

            $livre = $emprunt->livre;
            $livre?->synchroniserCompteurs();

            $this->audit->enregistrer(
                'retour',
                "Retour de « {$livre?->titre} » par {$emprunt->user?->name}",
                $emprunt,
                [
                    'jours_retard' => $emprunt->joursRetard(),
                    'penalite' => $penalite?->montant,
                    'etat' => $etatRetour,
                ],
                'emprunts'
            );

            $this->notifications->envoyer($emprunt->user, new RetourEnregistre($emprunt, $penalite));

            // L'exemplaire redevient disponible : on prévient la file d'attente.
            if ($livre && $exemplaire?->estDisponible()) {
                $this->reservations->notifierProchainDeLaFile($livre, $exemplaire);
            }

            return $emprunt->fresh(['user', 'livre', 'exemplaire']);
        });
    }

    /**
     * Renouvelle un emprunt. Si la validation par un bibliothécaire est requise
     * et que la demande vient de l'usager, la demande reste « en attente ».
     */
    public function renouveler(Emprunt $emprunt, User $demandeur, bool $forcerValidation = false): Renouvellement
    {
        $motifs = $emprunt->motifsBlocageRenouvellement();
        if ($motifs !== []) {
            throw RegleMetierException::avec($motifs, 'Renouvellement impossible :');
        }

        $validationRequise = Parametres::booleen('renouvellement.validation_requise', false)
            && ! $demandeur->estPersonnel()
            && ! $forcerValidation;

        return DB::transaction(function () use ($emprunt, $demandeur, $validationRequise) {
            $ancienne = $emprunt->date_retour_prevue->copy();
            $nouvelle = $ancienne->copy()->addDays($emprunt->user->dureeEmprunt());

            $renouvellement = Renouvellement::create([
                'emprunt_id' => $emprunt->id,
                'demande_par' => $demandeur->id,
                'traite_par' => $validationRequise ? null : ($demandeur->estPersonnel() ? $demandeur->id : null),
                'ancienne_echeance' => $ancienne->toDateString(),
                'nouvelle_echeance' => $validationRequise ? null : $nouvelle->toDateString(),
                'statut' => $validationRequise ? 'en_attente' : 'accepte',
            ]);

            if (! $validationRequise) {
                $emprunt->update([
                    'date_retour_prevue' => $nouvelle->toDateString(),
                    'nombre_renouvellements' => $emprunt->nombre_renouvellements + 1,
                ]);

                $this->notifications->envoyer($emprunt->user, new RenouvellementTraite($renouvellement));
            }

            $this->audit->enregistrer(
                'renouvellement',
                "Renouvellement de l'emprunt #{$emprunt->id} ({$renouvellement->statut})",
                $renouvellement,
                ['ancienne' => $ancienne->toDateString(), 'nouvelle' => $nouvelle->toDateString()],
                'emprunts'
            );

            return $renouvellement;
        });
    }

    /** Validation (ou refus) d'une demande de renouvellement en attente. */
    public function traiterRenouvellement(Renouvellement $renouvellement, bool $accepte, ?string $motifRefus = null): Renouvellement
    {
        if ($renouvellement->statut !== 'en_attente') {
            throw new RegleMetierException('Cette demande a déjà été traitée.');
        }

        return DB::transaction(function () use ($renouvellement, $accepte, $motifRefus) {
            $emprunt = $renouvellement->emprunt;

            if ($accepte) {
                $motifs = $emprunt->motifsBlocageRenouvellement();
                if ($motifs !== []) {
                    throw RegleMetierException::avec($motifs, 'Renouvellement impossible :');
                }

                $nouvelle = $emprunt->date_retour_prevue->copy()->addDays($emprunt->user->dureeEmprunt());

                $emprunt->update([
                    'date_retour_prevue' => $nouvelle->toDateString(),
                    'nombre_renouvellements' => $emprunt->nombre_renouvellements + 1,
                ]);

                $renouvellement->update([
                    'statut' => 'accepte',
                    'nouvelle_echeance' => $nouvelle->toDateString(),
                    'traite_par' => Auth::id(),
                ]);
            } else {
                $renouvellement->update([
                    'statut' => 'refuse',
                    'motif_refus' => $motifRefus,
                    'traite_par' => Auth::id(),
                ]);
            }

            $this->notifications->envoyer($emprunt->user, new RenouvellementTraite($renouvellement->fresh()));

            $this->audit->enregistrer(
                'renouvellement',
                "Demande de renouvellement #{$renouvellement->id} ".($accepte ? 'acceptée' : 'refusée'),
                $renouvellement,
                ['motif_refus' => $motifRefus],
                'emprunts'
            );

            return $renouvellement;
        });
    }

    /**
     * Passe en « en retard » les emprunts dont l'échéance est dépassée et
     * génère les pénalités correspondantes.
     *
     * @return array{marques: int, penalites: int}
     */
    public function traiterRetards(): array
    {
        $marques = 0;
        $penalites = 0;

        Emprunt::with(['user', 'livre'])
            ->where('statut', Emprunt::STATUT_EN_COURS)
            ->whereDate('date_retour_prevue', '<', now()->toDateString())
            ->chunkById(200, function ($emprunts) use (&$marques, &$penalites) {
                foreach ($emprunts as $emprunt) {
                    $emprunt->update(['statut' => Emprunt::STATUT_EN_RETARD]);
                    $marques++;

                    if ($this->penalites->appliquerPenaliteRetard($emprunt)) {
                        $penalites++;
                    }
                }
            });

        // Réévaluation des pénalités des retards déjà signalés.
        Emprunt::with(['user', 'livre'])
            ->where('statut', Emprunt::STATUT_EN_RETARD)
            ->whereNull('date_retour_effective')
            ->chunkById(200, function ($emprunts) {
                foreach ($emprunts as $emprunt) {
                    $this->penalites->appliquerPenaliteRetard($emprunt);
                }
            });

        return ['marques' => $marques, 'penalites' => $penalites];
    }
}
