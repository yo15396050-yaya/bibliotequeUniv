<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRetourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('retour', $this->route('emprunt')) ?? false;
    }

    public function rules(): array
    {
        return [
            'etat_retour' => ['nullable', Rule::in(['neuf', 'bon', 'moyen', 'mauvais', 'perdu'])],
            'observation' => ['nullable', 'string', 'max:500'],
        ];
    }
}
