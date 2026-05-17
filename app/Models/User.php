<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'matricule', 'telephone',
        'adresse', 'date_naissance', 'filiere', 'niveau', 'nombre_emprunts', 'actif'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_naissance' => 'date',
        'actif' => 'boolean'
    ];

    // GARANTIR que le mot de passe est toujours correctement haché
    public function setPasswordAttribute($value)
    {
        // Si vide, ne rien faire
        if (empty($value)) {
            return;
        }
        
        // Si ce n'est pas déjà un hash Bcrypt, le hacher
        if (!preg_match('/^\$2y\$/', $value)) {
            $this->attributes['password'] = Hash::make($value);
        } else {
            $this->attributes['password'] = $value;
        }
    }
    
    // VALIDAR le hash lors de la récupération
    public function getAuthPassword()
    {
        $password = $this->attributes['password'];
        
        // Nettoyer le mot de passe des caractères invisibles
        $password = trim($password);
        
        return $password;
    }

    // Relations
    public function emprunts()
    {
        return $this->hasMany(Emprunt::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function empruntsEnCours()
    {
        return $this->emprunts()->where('statut', 'en cours');
    }

    public function empruntsEnRetard()
    {
        return $this->emprunts()->where('statut', 'en retard');
    }

    // Méthodes utilitaires
    public function estAdministrateur()
    {
        return $this->role === 'admin';
    }

    public function estBibliothecaire()
    {
        return $this->role === 'bibliothecaire';
    }

    public function estEtudiant()
    {
        return $this->role === 'etudiant';
    }

    public function peutEmprunter()
    {
        return $this->estEtudiant() && 
               $this->actif && 
               $this->empruntsEnCours()->count() < 5 && 
               $this->empruntsEnRetard()->count() === 0;
    }
}