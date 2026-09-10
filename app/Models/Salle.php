<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salle extends Model
{
    protected $fillable = ['bibliotheque_id', 'nom', 'code', 'capacite'];

    public function bibliotheque()
    {
        return $this->belongsTo(Bibliotheque::class);
    }

    public function rayons()
    {
        return $this->hasMany(Rayon::class);
    }
}
