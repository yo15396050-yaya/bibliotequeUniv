@extends('layouts.dashboard')
@section('title', 'Exemplaires')

@section('content')
<x-entete-page titre="Exemplaires" icone="fa-barcode"
    :sous-titre="$exemplaires->total() . ' exemplaire(s) — un ouvrage peut en compter plusieurs'">
    @can('exemplaires.gerer')
        <a href="{{ route('exemplaires.create') }}" class="btn btn-warning"><i class="fas fa-plus me-1"></i> Nouvel exemplaire</a>
    @endcan
</x-entete-page>

<div class="row g-3 mb-4">
    @foreach(\App\Models\Exemplaire::STATUTS as $cle => $libelle)
        @if(($statistiques[$cle] ?? 0) > 0 || in_array($cle, ['disponible', 'emprunte']))
            <div class="col-12 col-sm-6 col-xl-3">
                <x-carte-stat :titre="$libelle" :valeur="$statistiques[$cle] ?? 0"
                    :icone="match($cle) {
                        'disponible' => 'fa-circle-check',
                        'emprunte' => 'fa-hand-holding',
                        'reserve' => 'fa-bookmark',
                        'perdu' => 'fa-ban',
                        'endommage' => 'fa-bandage',
                        'en_reparation' => 'fa-screwdriver-wrench',
                        default => 'fa-box-archive',
                    }"
                    :couleur="match($cle) {
                        'disponible' => 'success', 'emprunte' => 'info', 'reserve' => 'warning',
                        'perdu' => 'danger', 'endommage' => 'warning', default => 'wood',
                    }"
                    :lien="route('exemplaires.index', ['statut' => $cle])" />
            </div>
        @endif
    @endforeach
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-5">
                <input type="search" name="search" class="form-control" value="{{ request('search') }}"
                       placeholder="Code-barres, n° d'inventaire, titre, ISBN...">
            </div>
            <div class="col-md-3">
                <select name="statut" class="form-select">
                    <option value="">Tous les statuts</option>
                    @foreach(\App\Models\Exemplaire::STATUTS as $cle => $libelle)
                        <option value="{{ $cle }}" @selected(request('statut') === $cle)>{{ $libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="etat" class="form-select">
                    <option value="">Tous les états</option>
                    @foreach(\App\Models\Exemplaire::ETATS as $cle => $libelle)
                        <option value="{{ $cle }}" @selected(request('etat') === $cle)>{{ $libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-outline-secondary"><i class="fas fa-filter"></i></button></div>
            <div class="col-auto"><a href="{{ route('exemplaires.index') }}" class="btn btn-link">Réinitialiser</a></div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr><th>Code-barres</th><th>Ouvrage</th><th>Emplacement</th><th>État</th><th>Statut</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($exemplaires as $exemplaire)
                        <tr>
                            <td><code>{{ $exemplaire->code_barre }}</code></td>
                            <td>
                                <a href="{{ route('livres.show', $exemplaire->livre_id) }}" class="text-decoration-none" style="color:var(--text-main);">
                                    {{ $exemplaire->livre?->titre }}
                                </a>
                                <div class="small" style="opacity:.65;">{{ $exemplaire->livre?->auteur }}</div>
                            </td>
                            <td class="small">{{ $exemplaire->emplacement?->chemin_complet ?? '—' }}</td>
                            <td>{{ \App\Models\Exemplaire::ETATS[$exemplaire->etat] ?? $exemplaire->etat }}</td>
                            <td><x-badge :statut="$exemplaire->statut" :texte="$exemplaire->libelle_statut" /></td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('exemplaires.show', $exemplaire) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('exemplaires.etiquette', $exemplaire) }}" target="_blank" class="btn btn-sm btn-outline-dark" title="Étiquette"><i class="fas fa-tag"></i></a>
                                @can('exemplaires.gerer')
                                    <a href="{{ route('exemplaires.edit', $exemplaire) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-pen"></i></a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-vide message="Aucun exemplaire ne correspond à ces critères." icone="fa-barcode" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $exemplaires->links() }}
    </div>
</div>
@endsection
