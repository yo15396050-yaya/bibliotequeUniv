<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Livre extends Model
{
    use HasFactory;

    protected $fillable = [
        'isbn', 'titre', 'auteur', 'editeur', 'annee_publication',
        'categorie', 'langue', 'nombre_pages', 'resume', 'emplacement_rayon',
        'image_couverture', 'exemplaires_disponibles', 'exemplaires_totaux', 'statut',
        'fichier_numerique', 'disponible_numerique', 'extension_numerique',
        'autoriser_telechargement', 'taille_fichier'
    ];

    // Relations
    public function emprunts()
    {
        return $this->hasMany(Emprunt::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function reservationsActives()
    {
        return $this->reservations()->where('statut', 'active');
    }

    // Méthodes utilitaires
    public function estDisponible()
    {
        return $this->statut === 'disponible' && $this->exemplaires_disponibles > 0;
    }

    public function estDisponibleNumerique()
    {
        return $this->disponible_numerique && !empty($this->fichier_numerique);
    }

    public function nombreEmpruntsEnCours()
    {
        return $this->emprunts()->where('statut', 'en cours')->count();
    }

    public function getPopulariteAttribute()
    {
        $totalEmprunts = $this->emprunts()->count();
        $joursDepuisCreation = max(1, now()->diffInDays($this->created_at));
        
        return round($totalEmprunts / $joursDepuisCreation, 2);
    }

    public function getUrlLectureAttribute()
    {
        if (!$this->estDisponibleNumerique()) {
            return null;
        }
        return route('livres.read', $this->id);
    }

    public function getUrlTelechargementAttribute()
    {
        if (!$this->estDisponibleNumerique() || !$this->autoriser_telechargement) {
            return null;
        }
        return route('livres.download', $this->id);
    }

    // Scopes
    public function scopeDisponibles($query)
    {
        return $query->where('statut', 'disponible')->where('exemplaires_disponibles', '>', 0);
    }

    public function scopeRecherche($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('titre', 'LIKE', "%{$term}%")
              ->orWhere('auteur', 'LIKE', "%{$term}%")
              ->orWhere('isbn', 'LIKE', "%{$term}%")
              ->orWhere('categorie', 'LIKE', "%{$term}%");
        });
    }
}