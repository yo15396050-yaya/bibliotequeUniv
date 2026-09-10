<?php

namespace App\Http\Requests;

use App\Models\Reservation;
use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Reservation::class) ?? false;
    }

    public function rules(): array
    {
        return [
            // Un usager ne peut réserver que pour lui-même : le champ n'est
            // accepté que pour le personnel (voir le contrôleur).
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'livre_id' => ['required', 'integer', 'exists:livres,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
