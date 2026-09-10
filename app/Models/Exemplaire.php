<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Exemplaire physique d'un livre. À ne pas confondre avec la notice
 * bibliographique (App\Models\Livre) : « Algorithmique avancée » est un livre,
 * ALG-0001 / ALG-0002 sont ses exemplaires.
 */
class Exemplaire extends Model
{
    use HasFactory;

    public const STATUT_DISPONIBLE = 'disponible';

    public const STATUT_EMPRUNTE = 'emprunte';

    public const STATUT_RESERVE = 'reserve';

    public const STATUT_PERDU = 'perdu';

    public const STATUT_ENDOMMAGE = 'endommage';

    public const STATUT_EN_REPARATION = 'en_reparation';

    public const STATUT_RETIRE = 'retire';

    public const STATUTS = [
        self::STATUT_DISPONIBLE => 'Disponible',
        self::STATUT_EMPRUNTE => 'Emprunté',
        self::STATUT_RESERVE => 'Réservé',
        self::STATUT_PERDU => 'Perdu',
        self::STATUT_ENDOMMAGE => 'Endommagé',
        self::STATUT_EN_REPARATION => 'En réparation',
        self::STATUT_RETIRE => 'Retiré',
    ];

    public const ETATS = [
        'neuf' => 'Neuf',
        'bon' => 'Bon',
        'moyen' => 'Moyen',
        'mauvais' => 'Mauvais',
    ];

    protected $table = 'exemplaires';

    protected $fillable = [
        'livre_id', 'code_barre', 'numero_inventaire', 'etat', 'statut',
        'emplacement_id', 'date_acquisition', 'prix_achat', 'notes',
    ];

    protected $casts = [
        'date_acquisition' => 'date',
        'prix_achat' => 'decimal:2',
    ];

    public function livre()
    {
        return $this->belongsTo(Livre::class);
    }

    public function emplacement()
    {
        return $this->belongsTo(Emplacement::class);
    }

    public function emprunts()
    {
        return $this->hasMany(Emprunt::class);
    }

    public function empruntEnCours()
    {
        return $this->hasOne(Emprunt::class)->whereIn('statut', ['en cours', 'en retard']);
    }

    public function estDisponible(): bool
    {
        return $this->statut === self::STATUT_DISPONIBLE;
    }

    public function getLibelleStatutAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    public function scopeDisponibles($query)
    {
        return $query->where('statut', self::STATUT_DISPONIBLE);
    }

    /**
     * Génère le prochain code-barres pour un livre : 3 lettres du titre + n° séquentiel.
     */
    public static function genererCodeBarre(Livre $livre): string
    {
        $prefixe = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $livre->titre ?: 'BIB').'XXX', 0, 3));
        $sequence = static::where('livre_id', $livre->id)->count() + 1;

        do {
            $code = sprintf('%s-%04d', $prefixe, $sequence);
            $sequence++;
        } while (static::where('code_barre', $code)->exists());

        return $code;
    }
}
