@extends('layouts.dashboard')
@section('title', 'Nouvel ouvrage')

@section('content')
<x-entete-page titre="Nouvel ouvrage" icone="fa-book"
    sous-titre="La notice décrit l'œuvre ; ses exemplaires physiques sont générés automatiquement.">
    <a href="{{ route('livres.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</x-entete-page>

<x-erreurs />

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('livres.store') }}" method="POST" enctype="multipart/form-data" id="formulaire-livre">
            @csrf
            @include('livres._form')

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-warning btn-lg" id="bouton-enregistrer">
                    <i class="fas fa-save me-1"></i> Enregistrer l'ouvrage
                </button>
                <a href="{{ route('livres.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Le libellé libre des auteurs suit la sélection des auteurs référencés.
    const selectAuteurs = document.querySelector('select[name="auteurs[]"]');
    const champAuteur = document.querySelector('input[name="auteur"]');

    selectAuteurs?.addEventListener('change', function () {
        const noms = Array.from(this.selectedOptions).map(o => o.text.trim());
        if (noms.length) {
            champAuteur.value = noms.join(', ');
        }
    });

    // Protection contre le double envoi.
    document.getElementById('formulaire-livre').addEventListener('submit', function () {
        const bouton = document.getElementById('bouton-enregistrer');
        bouton.disabled = true;
        bouton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enregistrement…';
    });
</script>
@endpush
