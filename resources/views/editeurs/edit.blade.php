@extends('layouts.dashboard')
@section('title', 'Modifier un éditeur')
@section('content')
<x-entete-page :titre="'Modifier : ' . $editeur->nom" icone="fa-building">
    <a href="{{ route('editeurs.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</x-entete-page>
<x-erreurs />
<div class="card border-0 shadow-sm"><div class="card-body">
    <form action="{{ route('editeurs.update', $editeur) }}" method="POST">
        @csrf @method('PUT')
        @include('editeurs._form')
        <div class="mt-4 d-flex gap-2">
            <button class="btn btn-warning"><i class="fas fa-save me-1"></i> Enregistrer</button>
            <a href="{{ route('editeurs.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div></div>
@endsection
