<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Auteur extends Model
{
    use HasFactory;

    protected $table = 'auteurs';

    protected $fillable = [
        'nom', 'prenom', 'slug', 'biographie', 'nationalite',
        'date_naissance', 'date_deces', 'photo',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'date_deces' => 'date',
    ];

    protected static function booted(): void
    {
        static::saving(function (Auteur $auteur) {
            if (empty($auteur->slug)) {
                $base = Str::slug(trim($auteur->prenom.' '.$auteur->nom));
                $slug = $base;
                $i = 2;
                while (static::where('slug', $slug)->where('id', '!=', $auteur->id)->exists()) {
                    $slug = $base.'-'.$i++;
                }
                $auteur->slug = $slug;
            }
        });
    }

    public function livres()
    {
        return $this->belongsToMany(Livre::class, 'auteur_livre')->withPivot('role');
    }

    public function getNomCompletAttribute(): string
    {
        return trim($this->prenom.' '.$this->nom);
    }

    public function scopeRecherche($query, ?string $terme)
    {
        if (! $terme) {
            return $query;
        }

        return $query->where(function ($q) use ($terme) {
            $q->where('nom', 'like', "%{$terme}%")
                ->orWhere('prenom', 'like', "%{$terme}%")
                ->orWhere('nationalite', 'like', "%{$terme}%");
        });
    }
}
