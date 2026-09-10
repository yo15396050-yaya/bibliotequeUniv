<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEditeurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->peut('editeurs.gerer') ?? false;
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255',
                Rule::unique('editeurs', 'nom')->ignore($this->route('editeur')?->id)],
            'pays' => ['nullable', 'string', 'max:100'],
            'site_web' => ['nullable', 'url', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
        ];
    }
}
