<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateUserRequest extends StoreUserRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('user') ?? $this->route('etudiant')) ?? false;
    }

    public function rules(): array
    {
        $cible = $this->route('user') ?? $this->route('etudiant');
        $id = is_object($cible) ? $cible->id : $cible;

        $regles = parent::rules();
        $regles['email'] = ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)];
        $regles['matricule'] = ['required', 'string', 'max:50', Rule::unique('users', 'matricule')->ignore($id)];

        // Le rôle n'est modifiable que par qui détient la permission dédiée.
        if (! $this->user()?->peut('usagers.roles')) {
            unset($regles['role']);
        }

        return $regles;
    }
}
