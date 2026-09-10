@extends('layouts.dashboard')

@section('title', 'Statistiques — Bibliothèque Universitaire')

@section('content')
<x-entete-page titre="Statistiques" icone="fa-chart-line"
    sous-titre="Analyse de l'activité de la bibliothèque sur les 12 derniers mois.">
    @can('rapports.generer')
        <a href="{{ route('rapports.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-file-lines me-1"></i> Rapports
        </a>
    @endcan
</x-entete-page>

<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <x-carte-stat titre="Exemplaires" :valeur="number_format($stats['total_exemplaires'], 0, ',', ' ')" icone="fa-layer-group" couleur="wood" />
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <x-carte-stat titre="Empruntés" :valeur="number_format($stats['exemplaires_empruntes'], 0, ',', ' ')" icone="fa-hand-holding" couleur="info" />
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <x-carte-stat titre="Perdus" :valeur="$stats['exemplaires_perdus']" icone="fa-ban" couleur="danger" />
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <x-carte-stat titre="Endommagés" :valeur="$stats['exemplaires_endommages']" icone="fa-bandage" couleur="warning" />
    </div>
</div>

<div class="row g-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3">
                <h5 class="mb-0"><i class="fas fa-chart-area me-2" style="color: var(--accent-gold);"></i>Évolution mensuelle</h5>
            </div>
            <div class="card-body"><canvas id="graphique-mensuel" height="90"></canvas></div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-3">
                <h5 class="mb-0"><i class="fas fa-trophy me-2" style="color: var(--accent-gold);"></i>Ouvrages les plus empruntés</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>#</th><th>Ouvrage</th><th class="text-end">Emprunts</th></tr></thead>
                    <tbody>
                        @forelse($top_livres as $index => $livre)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <a href="{{ route('livres.show', $livre) }}" class="text-decoration-none" style="color: var(--text-main);">
                                        {{ $livre->titre }}
                                    </a>
                                    <div class="small" style="opacity:.65;">{{ $livre->auteur }}</div>
                                </td>
                                <td class="text-end"><span class="badge bg-warning text-dark">{{ $livre->emprunts_count }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3"><x-vide message="Aucun emprunt enregistré." icone="fa-book" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-3">
                <h5 class="mb-0"><i class="fas fa-users me-2" style="color: var(--accent-gold);"></i>Usagers les plus actifs</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>#</th><th>Usager</th><th class="text-end">Emprunts</th></tr></thead>
                    <tbody>
                        @forelse($top_usagers as $index => $usager)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <a href="{{ route('users.show', $usager) }}" class="text-decoration-none" style="color: var(--text-main);">
                                        {{ $usager->name }}
                                    </a>
                                    <div class="small" style="opacity:.65;">{{ $usager->matricule }} · {{ $usager->libelle_role }}</div>
                                </td>
                                <td class="text-end"><span class="badge bg-warning text-dark">{{ $usager->emprunts_count }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3"><x-vide message="Aucune activité." icone="fa-users" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3">
                <h5 class="mb-0"><i class="fas fa-tags me-2" style="color: var(--accent-gold);"></i>Catégories les plus demandées</h5>
            </div>
            <div class="card-body">
                @if($top_categories->isEmpty())
                    <x-vide message="Aucune donnée." icone="fa-tags" />
                @else
                    <canvas id="graphique-categories-populaires" height="80"></canvas>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
window.addEventListener('load', function () {
    const couleurTexte = getComputedStyle(document.documentElement).getPropertyValue('--text-main') || '#0F172A';
    const optionsCommunes = {
        responsive: true,
        plugins: { legend: { labels: { color: couleurTexte } } },
        scales: {
            y: { beginAtZero: true, ticks: { color: couleurTexte, precision: 0 } },
            x: { ticks: { color: couleurTexte } }
        }
    };

    new Chart(document.getElementById('graphique-mensuel'), {
        type: 'line',
        data: {
            labels: @json($flux_mensuel['labels']),
            datasets: [
                { label: 'Emprunts', data: @json($flux_mensuel['emprunts']), borderColor: '#2563EB', backgroundColor: 'rgba(37,99,235,.18)', tension: .35, fill: true },
                { label: 'Retours', data: @json($flux_mensuel['retours']), borderColor: '#0EA5E9', backgroundColor: 'rgba(14,165,233,.15)', tension: .35, fill: true },
                { label: 'Retards', data: @json($flux_mensuel['retards']), borderColor: '#DC2626', backgroundColor: 'rgba(220,38,38,.12)', tension: .35, fill: true }
            ]
        },
        options: optionsCommunes
    });

    @if($top_categories->isNotEmpty())
    new Chart(document.getElementById('graphique-categories-populaires'), {
        type: 'bar',
        data: {
            labels: @json($top_categories->pluck('categorie')),
            datasets: [{ label: 'Emprunts', data: @json($top_categories->pluck('total')), backgroundColor: '#2563EB', borderRadius: 6 }]
        },
        options: optionsCommunes
    });
    @endif
});
</script>
@endpush
