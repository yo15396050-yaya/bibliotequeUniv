<?php

namespace App\Http\Requests;

/**
 * L'écran « étudiant » ne propose pas de choix de profil : le rôle est imposé
 * par le contrôleur, la règle correspondante est donc retirée.
 */
class StoreEtudiantRequest extends StoreUserRequest
{
    public function rules(): array
    {
        $regles = parent::rules();
        unset($regles['role']);

        return $regles;
    }
}
