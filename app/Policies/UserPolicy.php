<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->peut('usagers.voir');
    }

    /** Chacun peut consulter sa propre fiche. */
    public function view(User $user, User $cible): bool
    {
        return $user->peut('usagers.voir') || $user->id === $cible->id;
    }

    public function create(User $user): bool
    {
        return $user->peut('usagers.creer');
    }

    public function update(User $user, User $cible): bool
    {
        if ($user->id === $cible->id) {
            return true;
        }

        // Un bibliothécaire ne modifie pas un administrateur.
        if ($cible->estAdministrateur() && ! $user->estAdministrateur()) {
            return false;
        }

        return $user->peut('usagers.modifier');
    }

    /** L'état métier (emprunts en cours) est vérifié par le contrôleur. */
    public function delete(User $user, User $cible): bool
    {
        return $user->peut('usagers.supprimer') && $user->id !== $cible->id;
    }

    public function gererRoles(User $user, User $cible): bool
    {
        return $user->peut('usagers.roles') && $user->id !== $cible->id;
    }
}
