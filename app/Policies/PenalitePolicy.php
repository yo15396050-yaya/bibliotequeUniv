<?php

namespace App\Policies;

use App\Models\Penalite;
use App\Models\User;

class PenalitePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->peut('penalites.voir') || $user->estEmprunteur();
    }

    public function view(User $user, Penalite $penalite): bool
    {
        return $user->peut('penalites.voir') || $penalite->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->peut('penalites.gerer');
    }

    public function encaisser(User $user, Penalite $penalite): bool
    {
        return $user->peut('penalites.encaisser');
    }

    public function annuler(User $user, Penalite $penalite): bool
    {
        return $user->peut('penalites.gerer');
    }
}
