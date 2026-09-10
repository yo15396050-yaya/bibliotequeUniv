<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateLivreRequest extends StoreLivreRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('livre')) ?? false;
    }

    public function rules(): array
    {
        $regles = parent::rules();

        $regles['isbn'] = [
            'required', 'string', 'max:20',
            Rule::unique('livres', 'isbn')->ignore($this->route('livre')?->id),
        ];

        $regles['statut'] = ['required', Rule::in(['disponible', 'emprunté', 'réservé', 'perdu', 'en réparation'])];

        return $regles;
    }
}
