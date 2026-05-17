<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Livre;

class LivrePolicy
{
    public function viewAny(User $user)
    {
        return true; // Tous les utilisateurs connectés peuvent voir les livres
    }

    public function view(User $user, Livre $livre)
    {
        return true;
    }

    public function create(User $user)
    {
        return $user->estAdministrateur() || $user->estBibliothecaire();
    }

    public function update(User $user, Livre $livre)
    {
        return $user->estAdministrateur() || $user->estBibliothecaire();
    }

    public function delete(User $user, Livre $livre)
    {
        return $user->estAdministrateur();
    }
}