<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentNumerique extends Model
{
    public const VISIBILITES = [
        'public' => 'Public (tout visiteur connecté)',
        'authentifie' => 'Tout utilisateur authentifié',
        'etudiant' => 'Étudiants et personnel',
        'enseignant' => 'Enseignants et personnel',
        'personnel' => 'Personnel de la bibliothèque uniquement',
    ];

    protected $table = 'documents_numeriques';

    protected $fillable = [
        'livre_id', 'titre', 'chemin', 'nom_original', 'format', 'mime_type',
        'taille', 'visibilite', 'autoriser_telechargement', 'nombre_telechargements', 'ajoute_par',
    ];

    protected $casts = [
        'autoriser_telechargement' => 'boolean',
        'taille' => 'integer',
        'nombre_telechargements' => 'integer',
    ];

    public function livre()
    {
        return $this->belongsTo(Livre::class);
    }

    public function auteurAjout()
    {
        return $this->belongsTo(User::class, 'ajoute_par');
    }

    public function getTailleLisibleAttribute(): string
    {
        $octets = (int) $this->taille;
        foreach (['o', 'Ko', 'Mo', 'Go'] as $unite) {
            if ($octets < 1024) {
                return round($octets, 1).' '.$unite;
            }
            $octets /= 1024;
        }

        return round($octets, 1).' To';
    }
}
