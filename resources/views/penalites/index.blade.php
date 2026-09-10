@extends('layouts.dashboard')
@section('title', 'Pénalités')

@php $devise = \App\Support\Parametres::devise(); @endphp

@section('content')
<x-entete-page :titre="Auth::user()->estPersonnel() ? 'Pénalités' : 'Mes pénalités'" icone="fa-money-bill-wave"
    :sous-titre="$penalites->total() . ' pénalité(s)'">
    @can('penalites.gerer')
        <a href="{{ route('penalites.create') }}" class="btn btn-warning"><i class="fas fa-plus me-1"></i> Nouvelle pénalité</a>
    @endcan
</x-entete-page>

<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <x-carte-stat titre="Total facturé" :valeur="number_format($statistiques['total'], 0, ',', ' ')" :sous-titre="$devise" icone="fa-file-invoice" couleur="wood" />
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <x-carte-stat titre="Encaissé" :valeur="number_format($statistiques['encaisse'], 0, ',', ' ')" :sous-titre="$devise" icone="fa-hand-holding-dollar" couleur="success" />
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <x-carte-stat titre="Reste dû" :valeur="number_format($statistiques['impaye'], 0, ',', ' ')" :sous-titre="$devise" icone="fa-triangle-exclamation" couleur="danger" />
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <x-carte-stat titre="Pénalités impayées" :valeur="$statistiques['nombre_impayees']" icone="fa-receipt" couleur="warning" />
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            @can('penalites.voir')
                <div class="col-md-4">
                    <input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Nom, matricule, email...">
                </div>
            @endcan
            <div class="col-md-3">
                <select name="statut" class="form-select">
                    <option value="">Tous les statuts</option>
                    @foreach(\App\Models\Penalite::STATUTS as $cle => $libelle)
                        <option value="{{ $cle }}" @selected(request('statut') === $cle)>{{ $libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="type" class="form-select">
                    <option value="">Tous les types</option>
                    @foreach(\App\Models\Penalite::TYPES as $cle => $libelle)
                        <option value="{{ $cle }}" @selected(request('type') === $cle)>{{ $libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-outline-secondary"><i class="fas fa-filter"></i></button></div>
            <div class="col-auto"><a href="{{ route('penalites.index') }}" class="btn btn-link">Réinitialiser</a></div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Date</th>
                        @can('penalites.voir')<th>Usager</th>@endcan
                        <th>Type / Motif</th><th class="text-end">Montant</th><th class="text-end">Reste</th>
                        <th>Statut</th><th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($penalites as $penalite)
                        <tr>
                            <td class="small">{{ $penalite->created_at->format('d/m/Y') }}</td>
                            @can('penalites.voir')
                                <td>
                                    <a href="{{ route('users.show', $penalite->user_id) }}" class="text-decoration-none" style="color:var(--text-main);">
                                        {{ $penalite->user?->name }}
                                    </a>
                                    <div class="small" style="opacity:.65;">{{ $penalite->user?->matricule }}</div>
                                </td>
                            @endcan
                            <td>
                                <strong class="small">{{ $penalite->libelle_type }}</strong>
                                <div class="small text-truncate" style="opacity:.7; max-width:280px;">
                                    {{ $penalite->emprunt?->livre?->titre ?? $penalite->motif }}
                                </div>
                            </td>
                            <td class="text-end">{{ number_format((float) $penalite->montant, 0, ',', ' ') }}</td>
                            <td class="text-end fw-bold {{ $penalite->reste_a_payer > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($penalite->estSoldee() ? 0 : $penalite->reste_a_payer, 0, ',', ' ') }}
                            </td>
                            <td><x-badge :statut="$penalite->statut" :texte="$penalite->libelle_statut" /></td>
                            <td class="text-end">
                                <a href="{{ route('penalites.show', $penalite) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-vide message="Aucune pénalité." icone="fa-circle-check" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $penalites->links() }}
    </div>
</div>
@endsection
