<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation commune aux quatre niveaux de localisation, pilotée par le
 * segment `niveau` de la route.
 */
class StoreLocalisationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->peut('localisations.gerer') ?? false;
    }

    public function rules(): array
    {
        return match ($this->route('niveau')) {
            'bibliotheques' => [
                'nom' => ['required', 'string', 'max:255'],
                'code' => ['required', 'string', 'max:20',
                    Rule::unique('bibliotheques', 'code')->ignore($this->route('id'))],
                'adresse' => ['nullable', 'string', 'max:255'],
                'telephone' => ['nullable', 'string', 'max:30'],
                'active' => ['nullable', 'boolean'],
            ],
            'salles' => [
                'bibliotheque_id' => ['required', 'integer', 'exists:bibliotheques,id'],
                'nom' => ['required', 'string', 'max:255'],
                'code' => ['required', 'string', 'max:20'],
                'capacite' => ['nullable', 'integer', 'min:1', 'max:10000'],
            ],
            'rayons' => [
                'salle_id' => ['required', 'integer', 'exists:salles,id'],
                'nom' => ['required', 'string', 'max:255'],
                'code' => ['required', 'string', 'max:20'],
                'domaine' => ['nullable', 'string', 'max:255'],
            ],
            'emplacements' => [
                'rayon_id' => ['required', 'integer', 'exists:rayons,id'],
                'etagere' => ['required', 'string', 'max:20'],
                'position' => ['nullable', 'string', 'max:20'],
                'cote' => ['nullable', 'string', 'max:255'],
            ],
            default => [],
        };
    }
}
