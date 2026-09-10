<?php

namespace App\Http\Requests;

use App\Models\PaiementPenalite;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaiementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('encaisser', $this->route('penalite')) ?? false;
    }

    public function rules(): array
    {
        return [
            'montant' => ['required', 'numeric', 'min:1'],
            'mode_paiement' => ['required', Rule::in(array_keys(PaiementPenalite::MODES))],
            'reference' => ['nullable', 'string', 'max:100'],
            'observation' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return ['mode_paiement' => 'mode de paiement'];
    }
}
