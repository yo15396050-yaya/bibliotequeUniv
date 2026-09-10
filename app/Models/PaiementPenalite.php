<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaiementPenalite extends Model
{
    public const MODES = [
        'especes' => 'Espèces',
        'mobile_money' => 'Mobile Money',
        'virement' => 'Virement',
        'cheque' => 'Chèque',
    ];

    protected $table = 'paiements_penalites';

    protected $fillable = [
        'penalite_id', 'montant', 'mode_paiement', 'reference',
        'encaisse_par', 'date_paiement', 'observation',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_paiement' => 'datetime',
    ];

    public function penalite()
    {
        return $this->belongsTo(Penalite::class);
    }

    public function caissier()
    {
        return $this->belongsTo(User::class, 'encaisse_par');
    }
}
