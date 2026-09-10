<?php

namespace App\Http\Requests;

use App\Models\Emprunt;
use Illuminate\Foundation\Http\FormRequest;

class StoreEmpruntRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Emprunt::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'livre_id' => ['required', 'integer', 'exists:livres,id'],
            'exemplaire_id' => ['nullable', 'integer', 'exists:exemplaires,id'],
            'date_retour_prevue' => ['nullable', 'date', 'after:today'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'user_id' => 'usager',
            'livre_id' => 'ouvrage',
            'exemplaire_id' => 'exemplaire',
            'date_retour_prevue' => 'date limite de retour',
        ];
    }
}
