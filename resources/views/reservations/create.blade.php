@extends('layouts.dashboard')
@section('title', 'Nouvelle réservation')

@php $personnel = Auth::user()->peut('reservations.gerer'); @endphp

@section('content')
<x-entete-page titre="Nouvelle réservation" icone="fa-bookmark"
    sous-titre="Réserver ne retire pas l'ouvrage du stock : un exemplaire est mis de côté lorsqu'il revient.">
    <a href="{{ route('reservations.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Retour
    </a>
</x-entete-page>

<x-erreurs />

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('reservations.store') }}">
                    @csrf

                    <div class="row g-3">
                        @if($personnel)
                            <div class="col-md-6">
                                <label class="form-label">Usager <span class="text-danger">*</span></label>
                                <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                                    <option value="">— Sélectionner un usager —</option>
                                    @foreach($etudiants as $etudiant)
                                        <option value="{{ $etudiant->id }}" @selected(old('user_id') == $etudiant->id)>
                                            {{ $etudiant->name }} — {{ $etudiant->matricule }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        @endif

                        <div class="col-md-6">
                            <label class="form-label">Ouvrage <span class="text-danger">*</span></label>
                            <select name="livre_id" id="livre_id" class="form-select @error('livre_id') is-invalid @enderror" required>
                                <option value="">— Sélectionner un ouvrage —</option>
                                @foreach($livres as $livre)
                                    <option value="{{ $livre->id }}" @selected(old('livre_id') == $livre->id)>
                                        {{ $livre->titre }}
                                        ({{ $livre->exemplaires_disponibles > 0
                                            ? $livre->exemplaires_disponibles.' dispo.'
                                            : 'indisponible' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('livre_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Note</label>
                            <textarea name="notes" rows="2" class="form-control"
                                      placeholder="Ex : ouvrage nécessaire pour un examen.">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button class="btn btn-warning"><i class="fas fa-bookmark me-1"></i> Réserver</button>
                        <a href="{{ route('reservations.index') }}" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3">
                <h6 class="mb-0"><i class="fas fa-circle-info me-2" style="color:var(--accent-gold);"></i>Comment ça marche</h6>
            </div>
            <div class="card-body small" style="opacity:.85;">
                <ol class="ps-3 mb-0">
                    <li class="mb-2">La réservation place l'usager dans une <strong>file d'attente</strong> ordonnée.</li>
                    <li class="mb-2">Au retour d'un exemplaire, le <strong>premier de la file</strong> est notifié et l'exemplaire est mis de côté.</li>
                    <li class="mb-2">Il dispose de
                        <strong>{{ \App\Support\Parametres::entier('reservation.delai_retrait', 2) }} jour(s)</strong>
                        pour le retirer.</li>
                    <li>Passé ce délai, la réservation expire et l'usager suivant est prévenu.</li>
                </ol>
                <hr>
                <p class="mb-0">
                    Maximum
                    <strong>{{ \App\Support\Parametres::entier('reservation.max_par_usager', 3) }}</strong>
                    réservation(s) active(s) par usager.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Filtrage au clavier des listes déroulantes, sans dépendance externe.
    document.querySelectorAll('#livre_id, #user_id').forEach(function (select) {
        const champ = document.createElement('input');
        champ.type = 'search';
        champ.className = 'form-control form-control-sm mb-2';
        champ.placeholder = select.id === 'livre_id' ? 'Filtrer les ouvrages…' : 'Filtrer les usagers…';
        select.parentNode.insertBefore(champ, select);

        const options = Array.from(select.options).map(o => ({ element: o, texte: o.text.toLowerCase() }));

        champ.addEventListener('input', function () {
            const terme = this.value.trim().toLowerCase();
            options.forEach(({ element, texte }) => {
                element.hidden = terme !== '' && element.value !== '' && !texte.includes(terme);
            });
        });
    });
</script>
@endpush
