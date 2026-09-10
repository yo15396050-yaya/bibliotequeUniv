<?php

namespace App\Http\Requests;

use App\Models\DocumentNumerique;
use App\Services\DocumentService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', DocumentNumerique::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'fichier' => [
                'required', 'file',
                'mimes:'.implode(',', DocumentService::formatsAutorises()),
                'max:'.DocumentService::tailleMaxKo(),
            ],
            'titre' => ['nullable', 'string', 'max:255'],
            'visibilite' => ['required', Rule::in(array_keys(DocumentNumerique::VISIBILITES))],
            'autoriser_telechargement' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'fichier.mimes' => 'Formats acceptés : '.implode(', ', DocumentService::formatsAutorises()).'.',
            'fichier.max' => 'Le fichier dépasse la taille maximale autorisée ('
                .round(DocumentService::tailleMaxKo() / 1024).' Mo).',
        ];
    }
}
