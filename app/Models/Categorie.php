<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Catégorie du catalogue. Supporte les sous-catégories via `parent_id`.
 */
class Categorie extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = [
        'nom', 'slug', 'code_categorie', 'description', 'parent_id', 'couleur',
    ];

    protected static function booted(): void
    {
        static::saving(function (Categorie $categorie) {
            if (empty($categorie->slug)) {
                $categorie->slug = Str::slug($categorie->nom);
            }
        });
    }

    public function parent()
    {
        return $this->belongsTo(Categorie::class, 'parent_id');
    }

    public function enfants()
    {
        return $this->hasMany(Categorie::class, 'parent_id');
    }

    public function livres()
    {
        return $this->hasMany(Livre::class, 'categorie_id');
    }

    public function scopeRacines($query)
    {
        return $query->whereNull('parent_id');
    }

    public function getNomCompletAttribute(): string
    {
        return $this->parent ? "{$this->parent->nom} › {$this->nom}" : $this->nom;
    }
}
