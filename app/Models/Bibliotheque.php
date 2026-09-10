<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bibliotheque extends Model
{
    protected $table = 'bibliotheques';

    protected $fillable = ['nom', 'code', 'adresse', 'telephone', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function salles()
    {
        return $this->hasMany(Salle::class);
    }

    public function rayons()
    {
        return $this->hasManyThrough(Rayon::class, Salle::class);
    }
}
