@extends('layouts.dashboard')
@section('title', $categorie->nom)
@section('content')
<x-entete-page :titre="$categorie->nom_complet" icone="fa-tags"
    :sous-titre="$categorie->description">
    @can('categories.gerer')
        <a href="{{ route('categories.edit', $categorie) }}" class="btn btn-warning"><i class="fas fa-pen me-1"></i> Modifier</a>
    @endcan
    <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</x-entete-page>

@if($categorie->enfants->isNotEmpty())
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap gap-2 align-items-center">
            <span class="fw-semibold me-2">Sous-catégories :</span>
            @foreach($categorie->enfants as $enfant)
                <a href="{{ route('categories.show', $enfant) }}" class="badge rounded-pill bg-warning text-dark text-decoration-none px-3 py-2">
                    {{ $enfant->nom }}
                </a>
            @endforeach
        </div>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 pt-3"><h5 class="mb-0">Ouvrages ({{ $livres->total() }})</h5></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Titre</th><th>Auteur(s)</th><th>Type</th><th class="text-center">Disponibles</th></tr></thead>
            <tbody>
                @forelse($livres as $livre)
                    <tr>
                        <td><a href="{{ route('livres.show', $livre) }}" class="text-decoration-none" style="color:var(--text-main);">{{ $livre->titre }}</a></td>
                        <td>{{ $livre->auteur }}</td>
                        <td>{{ $livre->libelle_type }}</td>
                        <td class="text-center">
                            <span class="badge {{ $livre->exemplaires_disponibles > 0 ? 'bg-success' : 'bg-danger' }}">
                                {{ $livre->exemplaires_disponibles }}/{{ $livre->exemplaires_totaux }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4"><x-vide message="Aucun ouvrage dans cette catégorie." icone="fa-book" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-body">{{ $livres->links() }}</div>
</div>
@endsection
