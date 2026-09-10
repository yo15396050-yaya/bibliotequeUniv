@extends('layouts.dashboard')
@section('title', $auteur->nom_complet)

@section('content')
<x-entete-page :titre="$auteur->nom_complet" icone="fa-feather-pointed"
    :sous-titre="$auteur->nationalite">
    @can('auteurs.gerer')
        <a href="{{ route('auteurs.edit', $auteur) }}" class="btn btn-warning"><i class="fas fa-pen me-1"></i> Modifier</a>
    @endcan
    <a href="{{ route('auteurs.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</x-entete-page>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                @if($auteur->photo)
                    <img src="{{ asset('storage/' . $auteur->photo) }}" class="rounded-circle mb-3"
                         style="width:120px;height:120px;object-fit:cover;" alt="">
                @else
                    <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center"
                         style="width:120px;height:120px;background:var(--wood-primary);color:var(--accent-gold);">
                        <i class="fas fa-user fa-3x"></i>
                    </div>
                @endif
                <h5>{{ $auteur->nom_complet }}</h5>
                <ul class="list-unstyled small mb-0" style="opacity:.8;">
                    @if($auteur->date_naissance)<li>Né(e) le {{ $auteur->date_naissance->format('d/m/Y') }}</li>@endif
                    @if($auteur->date_deces)<li>Décédé(e) le {{ $auteur->date_deces->format('d/m/Y') }}</li>@endif
                    <li>{{ $auteur->livres->count() }} ouvrage(s) au catalogue</li>
                </ul>
            </div>
        </div>
        @if($auteur->biographie)
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-transparent border-0 pt-3"><h6 class="mb-0">Biographie</h6></div>
                <div class="card-body pt-0"><p class="mb-0" style="white-space:pre-line;">{{ $auteur->biographie }}</p></div>
            </div>
        @endif
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3"><h5 class="mb-0">Ouvrages de cet auteur</h5></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Titre</th><th>Catégorie</th><th>Année</th><th class="text-center">Disponibles</th></tr></thead>
                    <tbody>
                        @forelse($auteur->livres as $livre)
                            <tr>
                                <td><a href="{{ route('livres.show', $livre) }}" class="text-decoration-none" style="color:var(--text-main);">{{ $livre->titre }}</a></td>
                                <td>{{ $livre->categorie }}</td>
                                <td>{{ $livre->annee_publication }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $livre->exemplaires_disponibles > 0 ? 'bg-success' : 'bg-danger' }}">
                                        {{ $livre->exemplaires_disponibles }}/{{ $livre->exemplaires_totaux }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><x-vide message="Aucun ouvrage rattaché à cet auteur." icone="fa-book" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
