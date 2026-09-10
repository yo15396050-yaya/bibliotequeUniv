<?php

namespace App\Http\Requests;

use App\Models\Livre;
use App\Support\Parametres;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLivreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Livre::class) ?? false;
    }

    public function rules(): array
    {
        $formats = implode(',', \App\Services\DocumentService::formatsAutorises());

        return [
            'isbn' => ['required', 'string', 'max:20', Rule::unique('livres', 'isbn')],
            'titre' => ['required', 'string', 'max:255'],
            'sous_titre' => ['nullable', 'string', 'max:255'],
            'auteur' => ['required', 'string', 'max:255'],
            'auteurs' => ['nullable', 'array'],
            'auteurs.*' => ['integer', 'exists:auteurs,id'],
            'editeur' => ['required', 'string', 'max:255'],
            'editeur_id' => ['nullable', 'integer', 'exists:editeurs,id'],
            'annee_publication' => ['required', 'integer', 'min:1400', 'max:'.(date('Y') + 1)],
            'edition' => ['nullable', 'string', 'max:50'],
            'categorie' => ['required', 'string', 'max:100'],
            'categorie_id' => ['nullable', 'integer', 'exists:categories,id'],
            'type_document' => ['required', Rule::in(array_keys(Livre::TYPES_DOCUMENT))],
            'niveau_academique' => ['nullable', Rule::in(array_keys(Livre::NIVEAUX_ACADEMIQUES))],
            'domaine' => ['nullable', 'string', 'max:255'],
            'langue' => ['required', 'string', 'max:50'],
            'nombre_pages' => ['required', 'integer', 'min:1', 'max:100000'],
            'resume' => ['nullable', 'string', 'max:5000'],
            'description' => ['nullable', 'string', 'max:10000'],
            'mots_cles' => ['nullable', 'string', 'max:255'],
            'emplacement_rayon' => ['required', 'string', 'max:50'],
            'emplacement_id' => ['nullable', 'integer', 'exists:emplacements,id'],
            'image_couverture' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'fichier_numerique' => ['nullable', 'file', 'mimes:'.$formats, 'max:'.\App\Services\DocumentService::tailleMaxKo()],
            'visibilite_document' => ['nullable', Rule::in(array_keys(\App\Models\DocumentNumerique::VISIBILITES))],
            'autoriser_telechargement' => ['nullable', 'boolean'],
            'exemplaires_totaux' => ['required', 'integer', 'min:1', 'max:999'],
            'generer_exemplaires' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'isbn' => 'ISBN',
            'annee_publication' => 'année de publication',
            'emplacement_rayon' => 'rayon',
            'exemplaires_totaux' => "nombre d'exemplaires",
            'type_document' => 'type de document',
        ];
    }

    public function messages(): array
    {
        return [
            'isbn.unique' => 'Un ouvrage possède déjà cet ISBN.',
            'fichier_numerique.mimes' => 'Formats acceptés : '.Parametres::chaine('document.formats_autorises', 'pdf,epub,docx').'.',
        ];
    }
}
