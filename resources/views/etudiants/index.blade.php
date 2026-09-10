@extends('layouts.dashboard')
@section('title', 'Étudiants')

@section('content')
<x-entete-page titre="Étudiants" icone="fa-user-graduate" :sous-titre="$etudiants->total() . ' étudiant(s) inscrit(s)'">
    @can('usagers.creer')
        <a href="{{ route('etudiants.create') }}" class="btn btn-warning"><i class="fas fa-plus me-1"></i> Nouvel étudiant</a>
    @endcan
</x-entete-page>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="search" name="search" class="form-control" value="{{ request('search') }}"
                       placeholder="Matricule, nom, email, téléphone...">
            </div>
            <div class="col-md-3">
                <select name="filiere" class="form-select">
                    <option value="">Toutes les filières</option>
                    @foreach($filieres as $filiere)
                        <option value="{{ $filiere }}" @selected(request('filiere') === $filiere)>{{ $filiere }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="niveau" class="form-select">
                    <option value="">Tous les niveaux</option>
                    @foreach($niveaux as $niveau)
                        <option value="{{ $niveau }}" @selected(request('niveau') === $niveau)>{{ $niveau }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="statut" class="form-select">
                    <option value="">Tous les statuts</option>
                    @foreach($statuts as $cle => $libelle)
                        <option value="{{ $cle }}" @selected(request('statut') === $cle)>{{ $libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-outline-secondary"><i class="fas fa-filter"></i></button></div>
        </form>

        @include('etudiants.partials._table', ['etudiants' => $etudiants])
    </div>
</div>
@endsection
