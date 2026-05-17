<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Etudiant extends Model
{
    protected $fillable = [
        'matricule', 'nom', 'prenom', 'email', 'telephone', 
        'adresse', 'date_naissance', 'filiere', 'niveau', 
        'nombre_emprunts', 'actif'
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'actif' => 'boolean',
        'nombre_emprunts' => 'integer'
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class, 'matricule', 'matricule');
    }

    public function emprunts()
    {
        return $this->hasMany(Emprunt::class, 'user_id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'user_id');
    }

    // Méthodes utilitaires
    public function peutEmprunter()
    {
        return $this->actif && 
               $this->emprunts()->where('statut', 'en cours')->count() < 5 && 
               $this->emprunts()->where('statut', 'en retard')->count() === 0;
    }

    public function empruntsEnCours()
    {
        return $this->emprunts()->where('statut', 'en cours')->get();
    }

    public function empruntsEnRetard()
    {
        return $this->emprunts()->where('statut', 'en retard')->get();
    }

    // Scopes
    public function scopeActifs($query)
    {
        return $query->where('actif', true);
    }

    public function scopeParFiliere($query, $filiere)
    {
        return $query->where('filiere', $filiere);
    }

    public function scopeParNiveau($query, $niveau)
    {
        return $query->where('niveau', $niveau);
    }
}
