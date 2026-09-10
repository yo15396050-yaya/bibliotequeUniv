@extends('layouts.dashboard')
@section('title', 'Nouvelle pénalité')

@section('content')
<x-entete-page titre="Nouvelle pénalité" icone="fa-money-bill-wave"
    sous-titre="Pénalité forfaitaire : perte, dégradation ou autre motif.">
    <a href="{{ route('penalites.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</x-entete-page>

<x-erreurs />

<div class="card border-0 shadow-sm"><div class="card-body">
    <form action="{{ route('penalites.store') }}" method="POST">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Usager <span class="text-danger">*</span></label>
                <select name="user_id" class="form-select" required>
                    <option value="">— Sélectionner —</option>
                    @foreach($usagers as $usager)
                        <option value="{{ $usager->id }}"
                            @selected(old('user_id', $empruntPreSelectionne->user_id ?? null) == $usager->id)>
                            {{ $usager->name }} ({{ $usager->matricule }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Emprunt concerné</label>
                @if($empruntPreSelectionne)
                    <input type="hidden" name="emprunt_id" value="{{ $empruntPreSelectionne->id }}">
                    <input class="form-control" value="{{ $empruntPreSelectionne->livre?->titre }}" disabled>
                @else
                    <input type="number" name="emprunt_id" class="form-control" value="{{ old('emprunt_id') }}"
                           placeholder="N° d'emprunt (facultatif)">
                @endif
            </div>

            <div class="col-md-4">
                <label class="form-label">Type <span class="text-danger">*</span></label>
                <select name="type" id="type-penalite" class="form-select" required>
                    @foreach(\App\Models\Penalite::TYPES as $cle => $libelle)
                        <option value="{{ $cle }}" @selected(old('type', 'perte') === $cle)
                            data-montant="{{ $montantsParDefaut[$cle] ?? '' }}">{{ $libelle }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Montant</label>
                <div class="input-group">
                    <input type="number" step="1" min="1" name="montant" id="montant-penalite"
                           class="form-control" value="{{ old('montant') }}">
                    <span class="input-group-text">{{ \App\Support\Parametres::devise() }}</span>
                </div>
                <div class="form-text">Vide = montant forfaitaire défini dans les paramètres.</div>
            </div>

            <div class="col-12">
                <label class="form-label">Motif <span class="text-danger">*</span></label>
                <textarea name="motif" rows="3" class="form-control" required>{{ old('motif') }}</textarea>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button class="btn btn-warning"><i class="fas fa-save me-1"></i> Enregistrer</button>
            <a href="{{ route('penalites.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div></div>
@endsection

@push('scripts')
<script>
    // Le montant se pré-remplit avec le forfait configuré pour le type choisi.
    const selectType = document.getElementById('type-penalite');
    const champMontant = document.getElementById('montant-penalite');

    function appliquerForfait() {
        const forfait = selectType.selectedOptions[0]?.dataset.montant;
        if (forfait && !champMontant.value) {
            champMontant.value = Math.round(parseFloat(forfait));
        }
    }

    selectType.addEventListener('change', () => { champMontant.value = ''; appliquerForfait(); });
    appliquerForfait();
</script>
@endpush
