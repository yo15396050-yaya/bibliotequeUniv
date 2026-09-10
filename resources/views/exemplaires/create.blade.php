@extends('layouts.dashboard')
@section('title', 'Nouvel exemplaire')
@section('content')
<x-entete-page titre="Nouvel exemplaire" icone="fa-barcode"
    sous-titre="Un exemplaire est une copie physique d'un ouvrage du catalogue.">
    <a href="{{ route('exemplaires.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</x-entete-page>
<x-erreurs />
<div class="card border-0 shadow-sm"><div class="card-body">
    <form action="{{ route('exemplaires.store') }}" method="POST">
        @csrf
        @include('exemplaires._form')
        <div class="mt-4 d-flex gap-2">
            <button class="btn btn-warning"><i class="fas fa-save me-1"></i> Enregistrer</button>
            <a href="{{ route('exemplaires.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div></div>
@endsection
