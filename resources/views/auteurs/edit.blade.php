@extends('layouts.dashboard')
@section('title', 'Modifier un auteur')

@section('content')
<x-entete-page :titre="'Modifier : ' . $auteur->nom_complet" icone="fa-feather-pointed">
    <a href="{{ route('auteurs.show', $auteur) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</x-entete-page>

<x-erreurs />

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('auteurs.update', $auteur) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            @include('auteurs._form')
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-warning"><i class="fas fa-save me-1"></i> Enregistrer</button>
                <a href="{{ route('auteurs.show', $auteur) }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
