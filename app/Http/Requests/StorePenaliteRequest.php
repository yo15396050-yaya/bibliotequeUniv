<?php

namespace App\Http\Requests;

use App\Models\Penalite;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePenaliteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Penalite::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'emprunt_id' => ['nullable', 'integer', 'exists:emprunts,id'],
            'type' => ['required', Rule::in(array_keys(Penalite::TYPES))],
            'montant' => ['nullable', 'numeric', 'min:1', 'max:99999999'],
            'motif' => ['required', 'string', 'max:1000'],
        ];
    }
}
