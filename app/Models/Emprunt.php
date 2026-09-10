<?php

namespace App\Models;

use App\Support\Parametres;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emprunt extends Model
{
    use HasFactory;

    public const STATUT_EN_COURS = 'en cours';

    public const STATUT_RETOURNE = 'retourné';

    public const STATUT_EN_RETARD = 'en retard';

    public const STATUT_PERDU = 'perdu';

    public const STATUTS = [
        self::STATUT_EN_COURS => 'En cours',
        self::STATUT_RETOURNE => 'Retourné',
        self::STATUT_EN_RETARD => 'En retard',
        self::STATUT_PERDU => 'Perdu',
    ];

    protected $fillable = [
        'user_id', 'bibliothecaire_id', 'livre_id', 'exemplaire_id',
        'date_emprunt', 'date_retour_prevue', 'date_retour_effective', 'receptionne_par',
        'statut', 'nombre_renouvellements', 'etat_retour', 'amende', 'notes',
    ];

    protected $casts = [
        'date_emprunt' => 'date',
        'date_retour_prevue' => 'date',
        'date_retour_effective' => 'date',
        'amende' => 'decimal:2',
        'nombre_renouvellements' => 'integer',
    ];

    /* ---------------------------------------------------------------------
     | Relations
     |--------------------------------------------------------------------*/

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bibliothecaire()
    {
        return $this->belongsTo(User::class, 'bibliothecaire_id');
    }

    public function receptionniste()
    {
        return $this->belongsTo(User::class, 'receptionne_par');
    }

    public function livre()
    {
        return $this->belongsTo(Livre::class);
    }

    public function exemplaire()
    {
        return $this->belongsTo(Exemplaire::class);
    }

    public function renouvellements()
    {
        return $this->hasMany(Renouvellement::class);
    }

    public function penalites()
    {
        return $this->hasMany(Penalite::class);
    }

    /* ---------------------------------------------------------------------
     | Règles de retard / pénalité
     |--------------------------------------------------------------------*/

    public function estEnCours(): bool
    {
        return in_array($this->statut, [self::STATUT_EN_COURS, self::STATUT_EN_RETARD], true);
    }

    public function estRetourne(): bool
    {
        return $this->date_retour_effective !== null || $this->statut === self::STATUT_RETOURNE;
    }

    /**
     * Nombre de jours de retard (0 si pas de retard).
     * Référence : date de retour effective si le livre est rendu, sinon aujourd'hui.
     */
    public function joursRetard(): int
    {
        if (! $this->date_retour_prevue) {
            return 0;
        }

        $reference = $this->date_retour_effective ?: now();

        return max(0, (int) $this->date_retour_prevue->startOfDay()
            ->diffInDays($reference->copy()->startOfDay(), false));
    }

    public function estEnRetard(): bool
    {
        return ! $this->estRetourne() && $this->joursRetard() > 0;
    }

    /** Jours restants avant l'échéance (0 si dépassée). */
    public function joursRestants(): int
    {
        if ($this->estRetourne() || ! $this->date_retour_prevue) {
            return 0;
        }

        return max(0, (int) now()->startOfDay()
            ->diffInDays($this->date_retour_prevue->copy()->startOfDay(), false));
    }

    /**
     * Montant de la pénalité de retard calculée selon les paramètres en vigueur
     * (jours de grâce, montant/jour, plafond).
     */
    public function calculerPenaliteRetard(): float
    {
        $jours = $this->joursRetard() - Parametres::entier('penalite.jours_grace', 0);

        if ($jours <= 0) {
            return 0.0;
        }

        $montant = $jours * Parametres::decimal('penalite.montant_par_jour', 100);
        $plafond = Parametres::decimal('penalite.plafond_retard', 0);

        return $plafond > 0 ? min($montant, $plafond) : $montant;
    }

    /** Montant affiché : pénalités enregistrées, ou estimation si non encore générées. */
    public function getMontantAmendeAttribute(): float
    {
        $enregistre = (float) $this->penalites()
            ->where('statut', '!=', Penalite::STATUT_ANNULEE)
            ->sum('montant');

        if ($enregistre > 0) {
            return $enregistre;
        }

        if ((float) $this->amende > 0) {
            return (float) $this->amende;
        }

        return $this->calculerPenaliteRetard();
    }

    public function getLibelleStatutAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? (string) $this->statut;
    }

    public function peutEtreRenouvele(): bool
    {
        return empty($this->motifsBlocageRenouvellement());
    }

    /**
     * @return array<int, string>
     */
    public function motifsBlocageRenouvellement(): array
    {
        $motifs = [];

        if (! $this->estEnCours()) {
            $motifs[] = "Cet emprunt n'est plus en cours.";
        }

        if ($this->estEnRetard()) {
            $motifs[] = 'Un emprunt en retard ne peut pas être renouvelé.';
        }

        $max = $this->user?->maxRenouvellements() ?? 1;
        if ($this->nombre_renouvellements >= $max) {
            $motifs[] = "Le nombre maximal de renouvellements est atteint ({$max}).";
        }

        if ($this->livre && $this->livre->reservationsActives()->exists()) {
            $motifs[] = 'Ce livre est actuellement réservé par un autre usager.';
        }

        if ($this->user && $this->user->penalitesBloquantes()->exists()) {
            $dette = (float) $this->user->penalitesBloquantes()->sum('montant')
                - (float) $this->user->penalitesBloquantes()->sum('montant_paye');
            if ($dette >= Parametres::decimal('penalite.seuil_blocage', 1000)) {
                $motifs[] = 'Cet usager possède une pénalité bloquante.';
            }
        }

        return $motifs;
    }

    /* ---------------------------------------------------------------------
     | Scopes
     |--------------------------------------------------------------------*/

    public function scopeEnCours(Builder $query): Builder
    {
        return $query->whereIn('statut', [self::STATUT_EN_COURS, self::STATUT_EN_RETARD]);
    }

    public function scopeEnRetard(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where('statut', self::STATUT_EN_RETARD)
                ->orWhere(fn (Builder $sq) => $sq->where('statut', self::STATUT_EN_COURS)
                    ->whereDate('date_retour_prevue', '<', now()->toDateString()));
        });
    }

    /** Emprunts dont l'échéance tombe dans les X prochains jours. */
    public function scopeEcheanceProche(Builder $query, ?int $jours = null): Builder
    {
        $jours ??= Parametres::entier('emprunt.jours_alerte_echeance', 3);

        return $query->where('statut', self::STATUT_EN_COURS)
            ->whereDate('date_retour_prevue', '>=', now()->toDateString())
            ->whereDate('date_retour_prevue', '<=', now()->addDays($jours)->toDateString());
    }
}
