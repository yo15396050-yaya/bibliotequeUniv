<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penalite extends Model
{
    public const TYPE_RETARD = 'retard';

    public const TYPE_PERTE = 'perte';

    public const TYPE_DEGRADATION = 'degradation';

    public const TYPE_AUTRE = 'autre';

    public const TYPES = [
        self::TYPE_RETARD => 'Retard',
        self::TYPE_PERTE => 'Livre perdu',
        self::TYPE_DEGRADATION => 'Livre endommagé',
        self::TYPE_AUTRE => 'Autre',
    ];

    public const STATUT_IMPAYEE = 'impayee';

    public const STATUT_PARTIELLE = 'partiellement_payee';

    public const STATUT_PAYEE = 'payee';

    public const STATUT_ANNULEE = 'annulee';

    public const STATUTS = [
        self::STATUT_IMPAYEE => 'Impayée',
        self::STATUT_PARTIELLE => 'Partiellement payée',
        self::STATUT_PAYEE => 'Payée',
        self::STATUT_ANNULEE => 'Annulée',
    ];

    protected $table = 'penalites';

    protected $fillable = [
        'user_id', 'emprunt_id', 'type', 'montant', 'montant_paye',
        'statut', 'jours_retard', 'motif', 'cree_par', 'date_annulation',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'montant_paye' => 'decimal:2',
        'date_annulation' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function emprunt()
    {
        return $this->belongsTo(Emprunt::class);
    }

    public function paiements()
    {
        return $this->hasMany(PaiementPenalite::class);
    }

    public function createur()
    {
        return $this->belongsTo(User::class, 'cree_par');
    }

    public function getResteAPayerAttribute(): float
    {
        return max(0, round((float) $this->montant - (float) $this->montant_paye, 2));
    }

    public function estSoldee(): bool
    {
        return in_array($this->statut, [self::STATUT_PAYEE, self::STATUT_ANNULEE], true);
    }

    /** Une pénalité impayée ou partiellement payée bloque les nouveaux emprunts. */
    public function scopeBloquantes($query)
    {
        return $query->whereIn('statut', [self::STATUT_IMPAYEE, self::STATUT_PARTIELLE]);
    }

    public function getLibelleTypeAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getLibelleStatutAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }
}
