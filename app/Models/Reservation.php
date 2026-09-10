<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    public const STATUT_ACTIVE = 'active';

    public const STATUT_EXPIREE = 'expirée';

    public const STATUT_ANNULEE = 'annulée';

    public const STATUT_HONOREE = 'honorée';

    public const STATUTS = [
        self::STATUT_ACTIVE => 'Active',
        self::STATUT_EXPIREE => 'Expirée',
        self::STATUT_ANNULEE => 'Annulée',
        self::STATUT_HONOREE => 'Honorée',
    ];

    protected $fillable = [
        'user_id', 'livre_id', 'exemplaire_id', 'date_reservation', 'date_expiration',
        'date_notification', 'date_limite_retrait', 'statut', 'position_file_attente', 'notes',
    ];

    protected $casts = [
        'date_reservation' => 'date',
        'date_expiration' => 'date',
        'date_limite_retrait' => 'date',
        'date_notification' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function livre()
    {
        return $this->belongsTo(Livre::class);
    }

    public function exemplaire()
    {
        return $this->belongsTo(Exemplaire::class);
    }

    public function estActive(): bool
    {
        return $this->statut === self::STATUT_ACTIVE && ! $this->estExpiree();
    }

    public function estExpiree(): bool
    {
        $limite = $this->date_limite_retrait ?: $this->date_expiration;

        return $limite !== null && now()->startOfDay()->greaterThan($limite->copy()->startOfDay());
    }

    /** La réservation a été notifiée : l'exemplaire attend d'être retiré. */
    public function estPrete(): bool
    {
        return $this->statut === self::STATUT_ACTIVE && $this->date_notification !== null;
    }

    public function getLibelleStatutAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? (string) $this->statut;
    }

    public function scopeActives(Builder $query): Builder
    {
        return $query->where('statut', self::STATUT_ACTIVE);
    }

    public function scopeFileDAttente(Builder $query, int $livreId): Builder
    {
        return $query->where('livre_id', $livreId)
            ->where('statut', self::STATUT_ACTIVE)
            ->orderBy('position_file_attente')
            ->orderBy('created_at');
    }
}
