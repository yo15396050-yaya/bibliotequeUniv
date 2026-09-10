<?php

namespace App\Services;

use App\Exceptions\RegleMetierException;
use App\Models\Emprunt;
use App\Models\PaiementPenalite;
use App\Models\Penalite;
use App\Models\User;
use App\Notifications\PenaliteAppliquee;
use App\Support\Parametres;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PenaliteService
{
    public function __construct(
        private readonly AuditService $audit,
        private readonly NotificationService $notifications,
    ) {}

    /**
     * Crée (ou met à jour) la pénalité de retard liée à un emprunt.
     * Idempotent : un seul enregistrement de type « retard » par emprunt.
     */
    public function appliquerPenaliteRetard(Emprunt $emprunt): ?Penalite
    {
        $montant = $emprunt->calculerPenaliteRetard();

        if ($montant <= 0) {
            return null;
        }

        $penalite = Penalite::firstOrNew([
            'emprunt_id' => $emprunt->id,
            'type' => Penalite::TYPE_RETARD,
        ]);

        // Une pénalité déjà soldée n'est jamais réévaluée.
        if ($penalite->exists && $penalite->estSoldee()) {
            return $penalite;
        }

        $nouvelle = ! $penalite->exists;

        $penalite->fill([
            'user_id' => $emprunt->user_id,
            'montant' => $montant,
            'jours_retard' => $emprunt->joursRetard(),
            'motif' => sprintf(
                'Retard de %d jour(s) sur « %s ».',
                $emprunt->joursRetard(),
                $emprunt->livre?->titre ?? 'ouvrage'
            ),
            'cree_par' => Auth::id(),
        ]);

        $penalite->statut = $penalite->montant_paye > 0
            ? Penalite::STATUT_PARTIELLE
            : Penalite::STATUT_IMPAYEE;

        $penalite->save();

        $emprunt->forceFill(['amende' => $montant])->save();

        if ($nouvelle) {
            $this->audit->enregistrer(
                'penalite',
                "Pénalité de retard de {$montant} appliquée à l'emprunt #{$emprunt->id}",
                $penalite,
                ['montant' => $montant, 'jours_retard' => $emprunt->joursRetard()],
                'penalites'
            );

            $this->notifications->envoyer($emprunt->user, new PenaliteAppliquee($penalite));
        }

        return $penalite;
    }

    /** Pénalité forfaitaire (perte, dégradation, autre). */
    public function creerPenalite(
        User $user,
        string $type,
        ?float $montant = null,
        ?string $motif = null,
        ?Emprunt $emprunt = null
    ): Penalite {
        $montant ??= match ($type) {
            Penalite::TYPE_PERTE => Parametres::decimal('penalite.montant_perte', 25000),
            Penalite::TYPE_DEGRADATION => Parametres::decimal('penalite.montant_degradation', 10000),
            default => 0,
        };

        if ($montant <= 0) {
            throw new RegleMetierException('Le montant de la pénalité doit être supérieur à zéro.');
        }

        $penalite = Penalite::create([
            'user_id' => $user->id,
            'emprunt_id' => $emprunt?->id,
            'type' => $type,
            'montant' => $montant,
            'statut' => Penalite::STATUT_IMPAYEE,
            'motif' => $motif ?: (Penalite::TYPES[$type] ?? 'Pénalité'),
            'cree_par' => Auth::id(),
        ]);

        $this->audit->enregistrer(
            'penalite',
            "Pénalité « {$penalite->libelle_type} » de {$montant} créée pour {$user->name}",
            $penalite,
            ['montant' => $montant, 'type' => $type],
            'penalites'
        );

        $this->notifications->envoyer($user, new PenaliteAppliquee($penalite));

        return $penalite;
    }

    /**
     * Encaisse un paiement (total ou partiel) et met à jour le statut.
     */
    public function enregistrerPaiement(
        Penalite $penalite,
        float $montant,
        string $mode = 'especes',
        ?string $reference = null,
        ?string $observation = null
    ): PaiementPenalite {
        if ($penalite->statut === Penalite::STATUT_ANNULEE) {
            throw new RegleMetierException('Cette pénalité a été annulée : aucun paiement possible.');
        }

        if ($montant <= 0) {
            throw new RegleMetierException('Le montant du paiement doit être supérieur à zéro.');
        }

        if (round($montant, 2) > $penalite->reste_a_payer) {
            throw new RegleMetierException(sprintf(
                'Le montant saisi dépasse le reste à payer (%s).',
                Parametres::formaterMontant($penalite->reste_a_payer)
            ));
        }

        return DB::transaction(function () use ($penalite, $montant, $mode, $reference, $observation) {
            $paiement = PaiementPenalite::create([
                'penalite_id' => $penalite->id,
                'montant' => $montant,
                'mode_paiement' => $mode,
                'reference' => $reference,
                'encaisse_par' => Auth::id(),
                'date_paiement' => now(),
                'observation' => $observation,
            ]);

            $penalite->montant_paye = round((float) $penalite->montant_paye + $montant, 2);
            $penalite->statut = $penalite->montant_paye >= (float) $penalite->montant
                ? Penalite::STATUT_PAYEE
                : Penalite::STATUT_PARTIELLE;
            $penalite->save();

            if ($penalite->statut === Penalite::STATUT_PAYEE && $penalite->emprunt) {
                $penalite->emprunt->forceFill(['amende' => 0])->save();
            }

            $this->audit->enregistrer(
                'paiement',
                "Paiement de {$montant} sur la pénalité #{$penalite->id}",
                $paiement,
                ['montant' => $montant, 'mode' => $mode, 'statut' => $penalite->statut],
                'penalites'
            );

            return $paiement;
        });
    }

    public function annuler(Penalite $penalite, string $motif): Penalite
    {
        if ($penalite->statut === Penalite::STATUT_PAYEE) {
            throw new RegleMetierException('Une pénalité déjà payée ne peut pas être annulée.');
        }

        $penalite->update([
            'statut' => Penalite::STATUT_ANNULEE,
            'motif' => trim($penalite->motif."\nAnnulation : ".$motif),
            'date_annulation' => now(),
        ]);

        if ($penalite->emprunt) {
            $penalite->emprunt->forceFill(['amende' => 0])->save();
        }

        $this->audit->enregistrer(
            'annulation',
            "Pénalité #{$penalite->id} annulée",
            $penalite,
            ['motif' => $motif],
            'penalites'
        );

        return $penalite;
    }

    /** Dette non soldée d'un usager. */
    public function detteDe(User $user): float
    {
        return (float) $user->penalitesBloquantes()->sum('montant')
            - (float) $user->penalitesBloquantes()->sum('montant_paye');
    }
}
