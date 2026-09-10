<?php

namespace App\Http\Requests;

use App\Models\Exemplaire;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExemplaireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Exemplaire::class) ?? false;
    }

    public function rules(): array
    {
        $exemplaire = $this->route('exemplaire');

        return [
            'livre_id' => ['required', 'integer', 'exists:livres,id'],
            'code_barre' => [
                'nullable', 'string', 'max:50',
                Rule::unique('exemplaires', 'code_barre')->ignore($exemplaire?->id),
            ],
            'numero_inventaire' => [
                'nullable', 'string', 'max:50',
                Rule::unique('exemplaires', 'numero_inventaire')->ignore($exemplaire?->id),
            ],
            'etat' => ['required', Rule::in(array_keys(Exemplaire::ETATS))],
            'statut' => ['required', Rule::in(array_keys(Exemplaire::STATUTS))],
            'emplacement_id' => ['nullable', 'integer', 'exists:emplacements,id'],
            'date_acquisition' => ['nullable', 'date', 'before_or_equal:today'],
            'prix_achat' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'quantite' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'code_barre.unique' => 'Ce code-barres est déjà attribué à un autre exemplaire.',
        ];
    }
}
