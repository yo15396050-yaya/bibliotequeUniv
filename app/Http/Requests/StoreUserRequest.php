<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', User::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'prenom' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'matricule' => ['required', 'string', 'max:50', Rule::unique('users', 'matricule')],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'telephone' => ['nullable', 'string', 'max:30'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'date_naissance' => ['nullable', 'date', 'before:today'],
            'faculte' => ['nullable', 'string', 'max:150'],
            'departement' => ['nullable', 'string', 'max:150'],
            'filiere' => ['nullable', 'string', 'max:150'],
            'niveau' => ['nullable', 'string', 'max:50'],
            'grade' => ['nullable', 'string', 'max:100'],
            'annee_academique_id' => ['nullable', 'integer', 'exists:annees_academiques,id'],
            'statut' => ['required', Rule::in(array_keys(User::STATUTS))],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nom',
            'matricule' => 'matricule',
            'annee_academique_id' => 'année académique',
        ];
    }

    public function messages(): array
    {
        return [
            'matricule.unique' => 'Ce matricule est déjà attribué à un autre usager.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
        ];
    }
}
