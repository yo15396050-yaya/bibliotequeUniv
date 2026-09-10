@extends('layouts.dashboard')

@section('title', 'Tableau de bord — Bibliothèque Universitaire')

@section('content')
<x-entete-page titre="Tableau de bord" icone="fa-gauge-high"
    :sous-titre="'Vue d\'ensemble de la bibliothèque au ' . now()->translatedFormat('d F Y')">
    @can('emprunts.enregistrer')
        <a href="{{ route('emprunts.create') }}" class="btn btn-warning">
            <i class="fas fa-plus me-1"></i> Nouvel emprunt
        </a>
        <a href="{{ route('emprunts.guichet') }}" class="btn btn-outline-secondary">
            <i class="fas fa-barcode me-1"></i> Guichet de retour
        </a>
    @endcan
</x-entete-page>

{{-- Indicateurs principaux --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <x-carte-stat titre="Ouvrages" :valeur="number_format($stats['total_livres'], 0, ',', ' ')"
            icone="fa-book" couleur="wood"
            :sous-titre="number_format($stats['total_exemplaires'], 0, ',', ' ') . ' exemplaires'"
            :lien="route('livres.index')" />
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <x-carte-stat titre="Disponibles" :valeur="number_format($stats['exemplaires_disponibles'], 0, ',', ' ')"
            icone="fa-circle-check" couleur="success"
            :sous-titre="$stats['livres_disponibles'] . ' titres empruntables'"
            :lien="route('catalogue')" />
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <x-carte-stat titre="Emprunts en cours" :valeur="number_format($stats['emprunts_en_cours'], 0, ',', ' ')"
            icone="fa-hand-holding" couleur="info"
            :sous-titre="$stats['exemplaires_reserves'] . ' exemplaire(s) réservé(s)'"
            :lien="route('emprunts.index')" />
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <x-carte-stat titre="En retard" :valeur="number_format($stats['emprunts_en_retard'], 0, ',', ' ')"
            icone="fa-triangle-exclamation" couleur="danger"
            :sous-titre="\App\Support\Parametres::formaterMontant($stats['penalites_impayees']) . ' impayés'"
            :lien="route('emprunts.index', ['statut' => 'en retard'])" />
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <x-carte-stat titre="Étudiants actifs" :valeur="number_format($stats['etudiants_actifs'], 0, ',', ' ')"
            icone="fa-user-graduate" couleur="gold"
            :sous-titre="$stats['total_etudiants'] . ' inscrits au total'"
            :lien="route('etudiants.index')" />
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <x-carte-stat titre="Enseignants" :valeur="number_format($stats['total_enseignants'], 0, ',', ' ')"
            icone="fa-chalkboard-user" couleur="wood" :lien="route('users.index', ['role' => 'enseignant'])" />
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <x-carte-stat titre="Réservations actives" :valeur="number_format($stats['reservations_actives'], 0, ',', ' ')"
            icone="fa-bookmark" couleur="warning" :lien="route('reservations.index')" />
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <x-carte-stat titre="Pénalités impayées" :valeur="$stats['nombre_penalites_impayees']"
            icone="fa-money-bill-wave" couleur="danger"
            :sous-titre="\App\Support\Parametres::formaterMontant($stats['penalites_impayees'])"
            :lien="route('penalites.index', ['statut' => 'impayee'])" />
    </div>
</div>

<div class="row g-3">
    {{-- Flux de circulation --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-3">
                <h5 class="mb-0"><i class="fas fa-chart-column me-2" style="color: var(--accent-gold);"></i>Circulation des 7 derniers jours</h5>
            </div>
            <div class="card-body">
                <canvas id="graphique-flux" height="110"></canvas>
            </div>
        </div>
    </div>

    {{-- Répartition du catalogue --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-3">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2" style="color: var(--accent-gold);"></i>Catalogue par catégorie</h5>
            </div>
            <div class="card-body">
                @if($categories_stats->isEmpty())
                    <x-vide message="Aucune donnée de catégorie." icone="fa-tags" />
                @else
                    <canvas id="graphique-categories" height="220"></canvas>
                @endif
            </div>
        </div>
    </div>

    {{-- Retards prioritaires --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-triangle-exclamation me-2 text-danger"></i>Retards prioritaires</h5>
                <a href="{{ route('emprunts.index', ['statut' => 'en retard']) }}" class="small text-decoration-none">Tout voir</a>
            </div>
            <div class="card-body p-0">
                @forelse($retards_critiques as $emprunt)
                    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                        <div class="min-w-0 me-2">
                            <a href="{{ route('emprunts.show', $emprunt) }}" class="fw-semibold text-decoration-none d-block text-truncate"
                               style="color: var(--text-main);">{{ $emprunt->livre?->titre }}</a>
                            <small style="opacity:.7;">{{ $emprunt->user?->name }} · {{ $emprunt->user?->matricule }}</small>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <span class="badge bg-danger">{{ $emprunt->joursRetard() }} j</span>
                            <div class="small" style="opacity:.7;">{{ \App\Support\Parametres::formaterMontant($emprunt->calculerPenaliteRetard()) }}</div>
                        </div>
                    </div>
                @empty
                    <x-vide message="Aucun retard : tout est à jour." icone="fa-circle-check" />
                @endforelse
            </div>
        </div>
    </div>

    {{-- Derniers emprunts --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-clock-rotate-left me-2" style="color: var(--accent-gold);"></i>Derniers mouvements</h5>
                <a href="{{ route('emprunts.index') }}" class="small text-decoration-none">Tout voir</a>
            </div>
            <div class="card-body p-0">
                @forelse($emprunts_recents as $emprunt)
                    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                        <div class="min-w-0 me-2">
                            <a href="{{ route('emprunts.show', $emprunt) }}" class="fw-semibold text-decoration-none d-block text-truncate"
                               style="color: var(--text-main);">{{ $emprunt->livre?->titre }}</a>
                            <small style="opacity:.7;">{{ $emprunt->user?->name }} · {{ $emprunt->date_emprunt?->format('d/m/Y') }}</small>
                        </div>
                        <x-badge :statut="$emprunt->statut" />
                    </div>
                @empty
                    <x-vide message="Aucun emprunt enregistré." icone="fa-hand-holding" />
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
window.addEventListener('load', function () {
    const couleurTexte = getComputedStyle(document.documentElement).getPropertyValue('--text-main') || '#0F172A';

    new Chart(document.getElementById('graphique-flux'), {
        type: 'bar',
        data: {
            labels: @json($chart_labels),
            datasets: [
                {
                    label: 'Emprunts',
                    data: @json($chart_data),
                    backgroundColor: '#2563EB',
                    borderRadius: 6,
                },
                {
                    label: 'Retours',
                    data: @json($chart_retours),
                    backgroundColor: '#0EA5E9',
                    borderRadius: 6,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { labels: { color: couleurTexte } } },
            scales: {
                y: { beginAtZero: true, ticks: { color: couleurTexte, precision: 0 } },
                x: { ticks: { color: couleurTexte } }
            }
        }
    });

    @if($categories_stats->isNotEmpty())
    new Chart(document.getElementById('graphique-categories'), {
        type: 'doughnut',
        data: {
            labels: @json($categories_stats->pluck('categorie')),
            datasets: [{
                data: @json($categories_stats->pluck('total')),
                backgroundColor: ['#2563EB', '#0F2557', '#0EA5E9', '#60A5FA',
                                  '#123A7A', '#38BDF8', '#93C5FD', '#1D4ED8'],
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom', labels: { color: couleurTexte, boxWidth: 12 } } }
        }
    });
    @endif
});
</script>
@endpush
