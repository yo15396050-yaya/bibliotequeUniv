<?php

namespace App\Policies;

use App\Models\Exemplaire;
use App\Models\User;

class ExemplairePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->peut('exemplaires.voir');
    }

    public function view(User $user, Exemplaire $exemplaire): bool
    {
        return $user->peut('exemplaires.voir');
    }

    public function create(User $user): bool
    {
        return $user->peut('exemplaires.gerer');
    }

    public function update(User $user, Exemplaire $exemplaire): bool
    {
        return $user->peut('exemplaires.gerer');
    }

    public function delete(User $user, Exemplaire $exemplaire): bool
    {
        return $user->peut('exemplaires.gerer');
    }
}
