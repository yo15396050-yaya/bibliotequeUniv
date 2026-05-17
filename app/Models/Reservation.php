<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'livre_id', 'date_reservation', 
        'date_expiration', 'statut', 'position_file_attente'
    ];

    protected $casts = [
        'date_reservation' => 'date',
        'date_expiration' => 'date'
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

    // Méthodes utilitaires
    public function estActive()
    {
        return $this->statut === 'active' && now()->lessThanOrEqualTo($this->date_expiration);
    }

    public function estExpiree()
    {
        return now()->greaterThan($this->date_expiration);
    }
}