@extends('layouts.dashboard')
@section('title', 'Modifier un exemplaire')
@section('content')
<x-entete-page :titre="'Exemplaire ' . $exemplaire->code_barre" icone="fa-barcode"
    :sous-titre="$exemplaire->livre?->titre">
    <a href="{{ route('exemplaires.show', $exemplaire) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</x-entete-page>
<x-erreurs />
<div class="card border-0 shadow-sm"><div class="card-body">
    <form action="{{ route('exemplaires.update', $exemplaire) }}" method="POST">
        @csrf @method('PUT')
        @include('exemplaires._form')
        <div class="mt-4 d-flex gap-2">
            <button class="btn btn-warning"><i class="fas fa-save me-1"></i> Enregistrer</button>
            <a href="{{ route('exemplaires.show', $exemplaire) }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div></div>
@endsection
