<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Emplacement extends Model
{
    protected $fillable = ['rayon_id', 'etagere', 'position', 'cote'];

    public function rayon()
    {
        return $this->belongsTo(Rayon::class);
    }

    public function exemplaires()
    {
        return $this->hasMany(Exemplaire::class);
    }

    /** Libellé complet : Bibliothèque → Salle → Rayon → Étagère. */
    public function getCheminCompletAttribute(): string
    {
        $rayon = $this->rayon;
        $salle = $rayon?->salle;
        $bibliotheque = $salle?->bibliotheque;

        return collect([
            $bibliotheque?->nom,
            $salle?->nom,
            $rayon?->nom,
            'Étagère '.$this->etagere,
            $this->position,
        ])->filter()->implode(' → ');
    }
}
