<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategorieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->peut('categories.gerer') ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('categorie')?->id;

        return [
            'nom' => ['required', 'string', 'max:255'],
            'code_categorie' => ['required', 'string', 'max:10',
                Rule::unique('categories', 'code_categorie')->ignore($id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id',
                Rule::notIn($id ? [$id] : [])],
            'couleur' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'parent_id.not_in' => 'Une catégorie ne peut pas être sa propre sous-catégorie.',
            'code_categorie.unique' => 'Ce code de catégorie est déjà utilisé.',
        ];
    }
}
