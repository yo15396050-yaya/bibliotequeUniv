<?php

namespace App\Policies;

use App\Models\Livre;
use App\Models\User;

class LivrePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->peut('livres.voir');
    }

    public function view(User $user, Livre $livre): bool
    {
        return $user->peut('livres.voir');
    }

    public function create(User $user): bool
    {
        return $user->peut('livres.creer');
    }

    public function update(User $user, Livre $livre): bool
    {
        return $user->peut('livres.modifier');
    }

    public function delete(User $user, Livre $livre): bool
    {
        return $user->peut('livres.supprimer');
    }

    /** Réserver un ouvrage : réservé aux emprunteurs. */
    public function reserver(User $user, Livre $livre): bool
    {
        return $user->estEmprunteur() && $user->estActif();
    }
}
