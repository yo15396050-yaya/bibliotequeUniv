<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emprunt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'livre_id', 'date_emprunt', 'date_retour_prevue',
        'date_retour_effective', 'statut', 'amende', 'notes'
    ];

    protected $casts = [
        'date_emprunt' => 'date',
        'date_retour_prevue' => 'date',
        'date_retour_effective' => 'date',
        'amende' => 'decimal:2'
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function livre()
    {
        return $this->belongsTo(Livre::class);
    }

    // Accesseurs
    public function getMontantAmendeAttribute()
    {
        // Si l'emprunt est déjà rendu et que l'amende a été fixée
        if ($this->date_retour_effective && $this->amende > 0) {
            return $this->amende;
        }

        // Si l'emprunt est en retard, calculer l'amende théorique
        if ($this->estEnRetard()) {
            $joursRetard = now()->startOfDay()->diffInDays($this->date_retour_prevue->startOfDay(), false);
            if ($joursRetard < 0) {
                return abs($joursRetard) * 100; // 100 FCFA par jour de retard
            }
        }

        return 0;
    }

    // Méthodes utilitaires
    public function estEnRetard()
    {
        return $this->statut === 'en retard' || 
               ($this->statut === 'en cours' && now()->startOfDay()->greaterThan($this->date_retour_prevue->startOfDay()));
    }

    public function joursRestants()
    {
        if ($this->statut !== 'en cours') {
            return 0;
        }

        $jours = now()->startOfDay()->diffInDays($this->date_retour_prevue->startOfDay(), false);
        return max(0, $jours);
    }

    public function solderAmende()
    {
        $this->update(['amende' => 0]);
        return true;
    }
}