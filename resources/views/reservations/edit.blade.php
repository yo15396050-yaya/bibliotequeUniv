@extends('layouts.dashboard')
@section('title', 'Modifier la réservation')

@section('content')
<x-entete-page :titre="'Réservation #' . $reservation->id" icone="fa-bookmark"
    :sous-titre="$reservation->livre?->titre . ' — ' . $reservation->user?->name">
    <a href="{{ route('reservations.show', $reservation) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Retour
    </a>
</x-entete-page>

<x-erreurs />

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('reservations.update', $reservation) }}" method="POST">
            @csrf @method('PUT')

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Date d'expiration <span class="text-danger">*</span></label>
                    <input type="date" name="date_expiration"
                           class="form-control @error('date_expiration') is-invalid @enderror"
                           value="{{ old('date_expiration', $reservation->date_expiration?->format('Y-m-d')) }}" required>
                    @error('date_expiration')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Statut <span class="text-danger">*</span></label>
                    <select name="statut" class="form-select @error('statut') is-invalid @enderror" required>
                        @foreach(\App\Models\Reservation::STATUTS as $cle => $libelle)
                            <option value="{{ $cle }}" @selected(old('statut', $reservation->statut) === $cle)>{{ $libelle }}</option>
                        @endforeach
                    </select>
                    @error('statut')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Position dans la file</label>
                    <input type="text" class="form-control" value="{{ $reservation->position_file_attente ?? '—' }}" disabled>
                    <div class="form-text">Recalculée automatiquement à chaque départ de la file.</div>
                </div>

                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" rows="3" class="form-control">{{ old('notes', $reservation->notes) }}</textarea>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-warning"><i class="fas fa-save me-1"></i> Enregistrer</button>
                <a href="{{ route('reservations.show', $reservation) }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
