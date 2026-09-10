<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Paramètre de configuration métier (durées, quotas, montants).
 * Les lectures passent par le cache pour éviter une requête par appel.
 */
class Parametre extends Model
{
    public const CACHE_KEY = 'parametres.tous';

    protected $table = 'parametres';

    protected $fillable = ['cle', 'valeur', 'type', 'groupe', 'libelle', 'description'];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /** Valeur typée du paramètre. */
    public function getValeurTypeeAttribute(): mixed
    {
        return match ($this->type) {
            'integer' => (int) $this->valeur,
            'decimal' => (float) $this->valeur,
            'boolean' => filter_var($this->valeur, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode((string) $this->valeur, true),
            default => $this->valeur,
        };
    }

    public function scopeGroupe($query, string $groupe)
    {
        return $query->where('groupe', $groupe);
    }
}
