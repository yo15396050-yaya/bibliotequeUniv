@extends('layouts.dashboard')
@section('title', 'Nouvel étudiant')

@section('content')
<x-entete-page titre="Nouvel étudiant" icone="fa-user-graduate"
    sous-titre="Le compte est créé avec le matricule comme mot de passe provisoire.">
    <a href="{{ route('etudiants.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</x-entete-page>

<x-erreurs />

<div class="card border-0 shadow-sm"><div class="card-body">
    <form action="{{ route('etudiants.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('etudiants._form')
        <div class="mt-4 d-flex gap-2">
            <button class="btn btn-warning"><i class="fas fa-save me-1"></i> Enregistrer l'étudiant</button>
            <a href="{{ route('etudiants.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div></div>
@endsection
