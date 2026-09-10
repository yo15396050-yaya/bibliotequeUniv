<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateEtudiantRequest extends StoreEtudiantRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('etudiant')) ?? false;
    }

    public function rules(): array
    {
        $etudiant = $this->route('etudiant');
        $id = is_object($etudiant) ? $etudiant->id : $etudiant;

        $regles = parent::rules();
        $regles['email'] = ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)];
        $regles['matricule'] = ['required', 'string', 'max:50', Rule::unique('users', 'matricule')->ignore($id)];

        return $regles;
    }
}
