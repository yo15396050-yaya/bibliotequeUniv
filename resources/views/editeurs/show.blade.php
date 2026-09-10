@extends('layouts.dashboard')
@section('title', $editeur->nom)
@section('content')
<x-entete-page :titre="$editeur->nom" icone="fa-building" :sous-titre="$editeur->pays">
    @can('editeurs.gerer')
        <a href="{{ route('editeurs.edit', $editeur) }}" class="btn btn-warning"><i class="fas fa-pen me-1"></i> Modifier</a>
    @endcan
    <a href="{{ route('editeurs.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</x-entete-page>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 pt-3"><h5 class="mb-0">Ouvrages publiés ({{ $editeur->livres->count() }})</h5></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Titre</th><th>Auteur(s)</th><th>Année</th><th class="text-center">Exemplaires</th></tr></thead>
            <tbody>
                @forelse($editeur->livres as $livre)
                    <tr>
                        <td><a href="{{ route('livres.show', $livre) }}" class="text-decoration-none" style="color:var(--text-main);">{{ $livre->titre }}</a></td>
                        <td>{{ $livre->auteur }}</td>
                        <td>{{ $livre->annee_publication }}</td>
                        <td class="text-center">{{ $livre->exemplaires_disponibles }}/{{ $livre->exemplaires_totaux }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4"><x-vide message="Aucun ouvrage pour cet éditeur." icone="fa-book" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
