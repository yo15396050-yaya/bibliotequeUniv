<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Renouvellement extends Model
{
    protected $table = 'renouvellements';

    protected $fillable = [
        'emprunt_id', 'demande_par', 'traite_par', 'ancienne_echeance',
        'nouvelle_echeance', 'statut', 'motif_refus',
    ];

    protected $casts = [
        'ancienne_echeance' => 'date',
        'nouvelle_echeance' => 'date',
    ];

    public function emprunt()
    {
        return $this->belongsTo(Emprunt::class);
    }

    public function demandeur()
    {
        return $this->belongsTo(User::class, 'demande_par');
    }

    public function traitePar()
    {
        return $this->belongsTo(User::class, 'traite_par');
    }
}
