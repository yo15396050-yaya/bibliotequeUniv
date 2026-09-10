<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Role extends Model
{
    protected $fillable = ['nom', 'libelle', 'description', 'systeme'];

    protected $casts = ['systeme' => 'boolean'];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('rbac.permissions_par_role'));
        static::deleted(fn () => Cache::forget('rbac.permissions_par_role'));
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function aLaPermission(string $permission): bool
    {
        return $this->permissions->contains('nom', $permission);
    }
}
