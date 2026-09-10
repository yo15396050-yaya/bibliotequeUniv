<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Editeur extends Model
{
    use HasFactory;

    protected $table = 'editeurs';

    protected $fillable = ['nom', 'slug', 'pays', 'site_web', 'email'];

    protected static function booted(): void
    {
        static::saving(function (Editeur $editeur) {
            if (empty($editeur->slug)) {
                $editeur->slug = Str::slug($editeur->nom);
            }
        });
    }

    public function livres()
    {
        return $this->hasMany(Livre::class);
    }
}
