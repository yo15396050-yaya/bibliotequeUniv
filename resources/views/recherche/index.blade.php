@extends('layouts.dashboard')
@section('title', 'Recherche avancée')

@section('content')
<x-entete-page titre="Recherche avancée" icone="fa-magnifying-glass"
    sous-titre="Une seule barre : titre, sous-titre, auteur, ISBN, éditeur, mot-clé, domaine ou code-barres." />

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('recherche') }}">
            <div class="input-group input-group-lg mb-3">
                <span class="input-group-text"><i class="fas fa-magnifying-glass"></i></span>
                <input type="search" name="q" class="form-control" value="{{ $terme }}"
                       placeholder="Rechercher dans toute la bibliothèque..." autofocus>
                <button class="btn btn-warning px-4">Rechercher</button>
            </div>

            <div class="row g-2">
                <div class="col-md-3">
                    <select name="categorie" class="form-select">
                        <option value="">Toutes les catégories</option>
                        @foreach($categories as $categorie)
                            <option value="{{ $categorie }}" @selected(request('categorie') === $categorie)>{{ $categorie }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="langue" class="form-select">
                        <option value="">Toutes les langues</option>
                        @foreach($langues as $langue)
                            <option value="{{ $langue }}" @selected(request('langue') === $langue)>{{ $langue }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="">Tous les types</option>
                        @foreach($types as $cle => $libelle)
                            <option value="{{ $cle }}" @selected(request('type') === $cle)>{{ $libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" name="annee" class="form-control" value="{{ request('annee') }}" placeholder="Année">
                </div>
                <div class="col-md-3">
                    <input type="text" name="emplacement" class="form-control" value="{{ request('emplacement') }}" placeholder="Emplacement / rayon">
                </div>
                <div class="col-12 d-flex gap-3 flex-wrap">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="disponible" value="1" id="f-dispo" @checked(request('disponible') === '1')>
                        <label class="form-check-label" for="f-dispo">Uniquement les ouvrages disponibles</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="numerique" value="1" id="f-num" @checked(request('numerique') === '1')>
                        <label class="form-check-label" for="f-num">Disponibles en version numérique</label>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@if($terme === '')
    <div class="card border-0 shadow-sm"><div class="card-body">
        <x-vide message="Saisissez un terme pour lancer la recherche." icone="fa-magnifying-glass" />
    </div></div>
@else
    @if($resultats['auteurs']->isNotEmpty() || $resultats['exemplaires']->isNotEmpty() || $resultats['usagers']->isNotEmpty())
        <div class="row g-3 mb-4">
            @if($resultats['auteurs']->isNotEmpty())
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent border-0 pt-3"><h6 class="mb-0"><i class="fas fa-feather-pointed me-2"></i>Auteurs</h6></div>
                        <div class="card-body p-0">
                            @foreach($resultats['auteurs'] as $auteur)
                                <a href="{{ route('auteurs.show', $auteur) }}" class="d-block px-3 py-2 border-bottom text-decoration-none" style="color:var(--text-main);">
                                    {{ $auteur->nom_complet }}
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis">{{ $auteur->livres_count }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @if($resultats['exemplaires']->isNotEmpty())
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent border-0 pt-3"><h6 class="mb-0"><i class="fas fa-barcode me-2"></i>Exemplaires</h6></div>
                        <div class="card-body p-0">
                            @foreach($resultats['exemplaires'] as $exemplaire)
                                <a href="{{ route('exemplaires.show', $exemplaire) }}" class="d-block px-3 py-2 border-bottom text-decoration-none" style="color:var(--text-main);">
                                    <code>{{ $exemplaire->code_barre }}</code>
                                    <div class="small text-truncate" style="opacity:.7;">{{ $exemplaire->livre?->titre }}</div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @if($resultats['usagers']->isNotEmpty())
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent border-0 pt-3"><h6 class="mb-0"><i class="fas fa-users me-2"></i>Usagers</h6></div>
                        <div class="card-body p-0">
                            @foreach($resultats['usagers'] as $usager)
                                <a href="{{ route('users.show', $usager) }}" class="d-block px-3 py-2 border-bottom text-decoration-none" style="color:var(--text-main);">
                                    {{ $usager->name }}
                                    <div class="small" style="opacity:.7;">{{ $usager->matricule }} · {{ $usager->libelle_role }}</div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 pt-3">
            <h5 class="mb-0"><i class="fas fa-book me-2" style="color:var(--accent-gold);"></i>
                Ouvrages ({{ $resultats['livres']->total() }})</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Titre</th><th>Auteur(s)</th><th>Catégorie</th><th>Année</th><th class="text-center">Disponibilité</th></tr></thead>
                <tbody>
                    @forelse($resultats['livres'] as $livre)
                        <tr>
                            <td>
                                <a href="{{ route('livres.show', $livre) }}" class="fw-semibold text-decoration-none" style="color:var(--text-main);">
                                    {{ $livre->titre }}
                                </a>
                                @if($livre->disponible_numerique)
                                    <span class="badge bg-info-subtle text-info-emphasis"><i class="fas fa-file-pdf"></i> numérique</span>
                                @endif
                                <div class="small" style="opacity:.65;">{{ $livre->libelle_type }} · {{ $livre->emplacement_rayon }}</div>
                            </td>
                            <td>{{ $livre->auteur }}</td>
                            <td>{{ $livre->categorie }}</td>
                            <td>{{ $livre->annee_publication }}</td>
                            <td class="text-center">
                                <span class="badge {{ $livre->exemplaires_disponibles > 0 ? 'bg-success' : 'bg-danger' }}">
                                    {{ $livre->exemplaires_disponibles }}/{{ $livre->exemplaires_totaux }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-vide :message="'Aucun ouvrage ne correspond à « ' . $terme . ' ».'" icone="fa-book" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $resultats['livres']->links() }}</div>
    </div>
@endif
@endsection
