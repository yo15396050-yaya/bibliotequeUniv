@extends('layouts.dashboard')
@section('title', 'Modifier — ' . $livre->titre)

@section('content')
<x-entete-page :titre="'Modifier : ' . $livre->titre" icone="fa-book"
    :sous-titre="$livre->isbn . ' · ' . $livre->exemplaires()->count() . ' exemplaire(s)'">
    <a href="{{ route('livres.show', $livre) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</x-entete-page>

<x-erreurs />

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('livres.update', $livre) }}" method="POST" enctype="multipart/form-data" id="formulaire-livre">
            @csrf @method('PUT')
            @include('livres._form')

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-warning btn-lg" id="bouton-enregistrer">
                    <i class="fas fa-save me-1"></i> Enregistrer les modifications
                </button>
                <a href="{{ route('livres.show', $livre) }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const selectAuteurs = document.querySelector('select[name="auteurs[]"]');
    const champAuteur = document.querySelector('input[name="auteur"]');

    selectAuteurs?.addEventListener('change', function () {
        const noms = Array.from(this.selectedOptions).map(o => o.text.trim());
        if (noms.length) {
            champAuteur.value = noms.join(', ');
        }
    });

    document.getElementById('formulaire-livre').addEventListener('submit', function () {
        const bouton = document.getElementById('bouton-enregistrer');
        bouton.disabled = true;
        bouton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enregistrement…';
    });
</script>
@endpush
