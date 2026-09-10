<?php

namespace App\Policies;

use App\Models\Emprunt;
use App\Models\User;

class EmpruntPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->peut('emprunts.voir') || $user->estEmprunteur();
    }

    /** Un usager ne voit que ses propres emprunts. */
    public function view(User $user, Emprunt $emprunt): bool
    {
        return $user->peut('emprunts.voir') || $emprunt->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->peut('emprunts.enregistrer');
    }

    public function retour(User $user, Emprunt $emprunt): bool
    {
        return $user->peut('emprunts.retour');
    }

    /** L'usager peut demander le renouvellement de son propre emprunt. */
    public function renouveler(User $user, Emprunt $emprunt): bool
    {
        return $user->peut('emprunts.renouveler') || $emprunt->user_id === $user->id;
    }

    public function delete(User $user, Emprunt $emprunt): bool
    {
        return $user->estAdministrateur();
    }
}
