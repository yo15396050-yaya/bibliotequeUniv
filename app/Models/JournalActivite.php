<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Entrée du journal d'audit (lecture seule côté application).
 */
class JournalActivite extends Model
{
    protected $table = 'journaux_activite';

    protected $fillable = [
        'user_id', 'action', 'module', 'sujet_type', 'sujet_id',
        'description', 'donnees', 'adresse_ip', 'user_agent',
    ];

    protected $casts = ['donnees' => 'array'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sujet()
    {
        return $this->morphTo(__FUNCTION__, 'sujet_type', 'sujet_id');
    }

    public function scopeModule($query, ?string $module)
    {
        return $module ? $query->where('module', $module) : $query;
    }

    public function scopeAction($query, ?string $action)
    {
        return $action ? $query->where('action', $action) : $query;
    }
}
