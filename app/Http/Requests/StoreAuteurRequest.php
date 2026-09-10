<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAuteurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->peut('auteurs.gerer') ?? false;
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['nullable', 'string', 'max:255'],
            'biographie' => ['nullable', 'string', 'max:5000'],
            'nationalite' => ['nullable', 'string', 'max:100'],
            'date_naissance' => ['nullable', 'date', 'before:today'],
            'date_deces' => ['nullable', 'date', 'after:date_naissance'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ];
    }
}
