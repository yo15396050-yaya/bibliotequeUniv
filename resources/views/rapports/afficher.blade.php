@extends('layouts.dashboard')
@section('title', $titre)

@section('content')
<x-entete-page :titre="$titre" icone="fa-file-lines" :sous-titre="$periode">
    <a href="{{ route('rapports.afficher', array_merge(['rapport' => $rapport], request()->query(), ['format' => 'pdf'])) }}"
       class="btn btn-outline-danger"><i class="fas fa-file-pdf me-1"></i> PDF</a>
    <a href="{{ route('rapports.afficher', array_merge(['rapport' => $rapport], request()->query(), ['format' => 'excel'])) }}"
       class="btn btn-outline-success"><i class="fas fa-file-excel me-1"></i> Excel</a>
    <a href="{{ route('rapports.afficher', array_merge(['rapport' => $rapport], request()->query(), ['format' => 'csv'])) }}"
       class="btn btn-outline-secondary"><i class="fas fa-file-csv me-1"></i> CSV</a>
    <a href="{{ route('rapports.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</x-entete-page>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Du</label>
                <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Au</label>
                <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Rapport</label>
                <select name="rapport" class="form-select" onchange="window.location='{{ url('rapports') }}/' + this.value">
                    @foreach($rapports as $slug => $libelle)
                        <option value="{{ $slug }}" @selected($slug === $rapport)>{{ $libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-warning"><i class="fas fa-filter me-1"></i> Filtrer</button></div>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    @foreach($statistiques as $libelle => $valeur)
        <div class="col-12 col-sm-6 col-xl-3"><x-carte-stat :titre="$libelle" :valeur="$valeur" icone="fa-chart-simple" couleur="wood" /></div>
    @endforeach
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle">
                <thead><tr>@foreach($colonnes as $entete)<th>{{ $entete }}</th>@endforeach</tr></thead>
                <tbody>
                    @forelse($lignes as $ligne)
                        <tr>@foreach(array_keys($colonnes) as $cle)<td class="small">{{ $ligne[$cle] ?? '—' }}</td>@endforeach</tr>
                    @empty
                        <tr><td colspan="{{ count($colonnes) }}"><x-vide message="Aucune donnée pour ces critères." icone="fa-file-lines" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="small" style="opacity:.65;">{{ $lignes->count() }} ligne(s).</div>
    </div>
</div>
@endsection
