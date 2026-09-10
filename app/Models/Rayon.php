<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rayon extends Model
{
    protected $fillable = ['salle_id', 'nom', 'code', 'domaine'];

    public function salle()
    {
        return $this->belongsTo(Salle::class);
    }

    public function emplacements()
    {
        return $this->hasMany(Emplacement::class);
    }
}
