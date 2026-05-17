@extends('layouts.dashboard')



@section('title', 'Tableau de bord - Gestion Bibliothèque Universitaire')



@section('content')

    <div class="container-fluid px-4" style="background-color: #FAF3E0; min-height: 100vh;">

        <!-- Éléments de données invisibles pour JavaScript -->

        <div id="js-stats-bridge" style="display: none;">
            <span id="stat-total">{{ $stats['total_livres'] ?? 0 }}</span>
            <span id="stat-dispo">{{ $stats['livres_disponibles'] ?? 0 }}</span>
            <span id="chart-labels">{!! json_encode($chart_labels) !!}</span>
            <span id="chart-data">{!! json_encode($chart_data) !!}</span>
            <span id="cat-labels">{!! json_encode($categories_stats->pluck('categorie')) !!}</span>
            <span id="cat-counts">{!! json_encode($categories_stats->pluck('total')) !!}</span>
        </div>



        <!-- En-tête (Version Originale) -->

        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3 pt-3">



            <div>
                <h1 class="h2 mb-1 fw-bold" style="color: #3E2723;">Tableau de bord</h1>
                <p class="mb-0 text-muted"><strong>Bienvenue dans votre espace,</strong> <span class="fw-bold"
                        style="color: #D4AF37;">{{ Auth::user()->name ?? 'Administrateur' }} 👋</span></p>
            </div>

            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('exports.retards') }}" class="btn btn-outline-danger shadow-sm px-3">
                    <i class="fas fa-file-pdf me-2"></i>Rapport Global (PDF)
                </a>
                <div class="d-flex align-items-center">
                    <span class="badge rounded-pill p-2 me-3 shadow-sm" style="background-color: #5D4037;">
                        <i class="fas fa-calendar-alt me-2 text-warning"></i> {{ now()->translatedFormat('l d F Y') }}
                    </span>
                    <span class="badge rounded-pill p-2 shadow-sm text-dark border" style="background-color: #FAF3E0;">
                        <i class="fas fa-clock me-2 text-brown"></i> {{ now()->format('H:i') }}
                    </span>
                </div>
            </div>

        </div>



        <!-- Cartes de Statistiques -->

        <div class="row mb-4">

            <div class="col-xl-3 col-md-6 mb-4">

                <div class="card card-stat border-0 shadow-sm h-100 py-2"
                    style="border-left: 5px solid #D4AF37 !important;">

                    <div class="card-body">

                        <div class="row no-gutters align-items-center">

                            <div class="col mr-2">

                                <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #A1887F;">Livres
                                    Totaux</div>

                                <div class="h3 mb-0 font-weight-bold" style="color: #3E2723;">
                                    {{ $stats['total_livres'] ?? 0 }}</div>

                                <div class="mt-2">

                                    <span class="text-success small font-weight-bold">

                                        <i class="fas fa-check-circle"></i> {{ $stats['livres_disponibles'] ?? 0 }}

                                    </span>

                                    <span class="text-muted small ms-1">en rayon</span>

                                </div>

                            </div>

                            <div class="col-auto">

                                <i class="fas fa-book-reader fa-2x" style="color: #D4AF37;"></i>

                            </div>

                        </div>

                    </div>

                </div>

            </div>



            <div class="col-xl-3 col-md-6 mb-4">

                <div class="card card-stat border-0 shadow-sm h-100 py-2"
                    style="border-left: 5px solid #5D4037 !important;">

                    <div class="card-body">

                        <div class="row no-gutters align-items-center">

                            <div class="col mr-2">

                                <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #A1887F;">Étudiants
                                    Inscrits</div>

                                <div class="h3 mb-0 font-weight-bold" style="color: #3E2723;">
                                    {{ $stats['total_etudiants'] ?? 0 }}</div>

                                <div class="mt-2">

                                    <span class="small font-weight-bold" style="color: #5D4037;">

                                        <i class="fas fa-graduation-cap"></i> Membres actifs

                                    </span>

                                </div>

                            </div>

                            <div class="col-auto">

                                <i class="fas fa-users fa-2x" style="color: #5D4037;"></i>

                            </div>

                        </div>

                    </div>

                </div>

            </div>



            <div class="col-xl-3 col-md-6 mb-4">

                <div class="card card-stat border-0 shadow-sm h-100 py-2"
                    style="border-left: 5px solid #8D6E63 !important;">

                    <div class="card-body">

                        <div class="row no-gutters align-items-center">

                            <div class="col mr-2">

                                <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #A1887F;">Emprunts
                                    Actifs</div>

                                <div class="h3 mb-0 font-weight-bold" style="color: #3E2723;">
                                    {{ $stats['emprunts_en_cours'] ?? 0 }}</div>

                                <div class="mt-2 text-danger small font-weight-bold">

                                    <i class="fas fa-hourglass-half"></i> {{ $stats['emprunts_en_retard'] ?? 0 }} retard(s)

                                </div>

                            </div>

                            <div class="col-auto">

                                <i class="fas fa-exchange-alt fa-2x" style="color: #8D6E63;"></i>

                            </div>

                        </div>

                    </div>

                </div>

            </div>



            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card card-stat border-0 shadow-sm h-100 py-2"
                    style="border-left: 5px solid #e74c3c !important;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #A1887F;">Amendes
                                    Impayées</div>
                                <div class="h3 mb-0 font-weight-bold" style="color: #e74c3c;">
                                    {{ number_format($stats['total_amendes'], 0, ',', ' ') }} FCFA</div>
                                <div class="mt-2 text-danger small font-weight-bold">
                                    <i class="fas fa-coins"></i> Total à recouvrer
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-money-bill-wave fa-2x" style="color: #e74c3c;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>



        <!-- Section Graphiques -->

        <div class="row mb-5">
            <!-- Flux Hebdomadaire -->
            <div class="col-xl-8 col-lg-7">
                <div class="card shadow-sm border-0 mb-4 h-100">
                    <div
                        class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white border-bottom-gold">
                        <h6 class="m-0 font-weight-bold text-brown"><i
                                class="fas fa-chart-line me-2 text-gold"></i>Activités d'Emprunt (7 derniers jours)</h6>
                    </div>
                    <div class="card-body bg-white">
                        <div class="chart-area" style="height: 320px;">
                            <canvas id="empruntsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Répartition par Catégorie -->
            <div class="col-xl-4 col-lg-5">
                <div class="card shadow-sm border-0 mb-4 h-100">
                    <div
                        class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white border-bottom-gold">
                        <h6 class="m-0 font-weight-bold text-brown"><i
                                class="fas fa-chart-pie me-2 text-gold"></i>Répartition par Rayon</h6>
                    </div>
                    <div class="card-body bg-white text-center">
                        <div class="chart-pie pt-2 pb-2" style="height: 250px;">
                            <canvas id="categoryChart"></canvas>
                        </div>
                        <div id="category-legend" class="mt-3 small text-start ps-3"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alertes Retards & Emprunts Récents -->
        <div class="row mb-5">
            <!-- Retards Critiques -->
            <div class="col-xl-5 col-lg-6 mb-4">
                <div class="card shadow-sm border-0 h-100 overflow-hidden">
                    <div class="card-header py-3 bg-danger text-white">
                        <h6 class="m-0 font-weight-bold"><i class="fas fa-exclamation-circle me-2"></i>Retards Critiques
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light small text-uppercase">
                                    <tr>
                                        <th class="ps-3">Livre / Étudiant</th>
                                        <th>Échéance</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($retards_critiques as $retard)
                                        <tr>
                                            <td class="ps-3">
                                                <div class="fw-bold text-truncate" style="max-width: 200px;">
                                                    {{ $retard->livre->titre }}</div>
                                                <small class="text-muted">{{ $retard->user->name }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger-soft text-danger">
                                                    {{ $retard->date_retour_prevue->diffForHumans() }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="text-danger fw-bold small">
                                                    {{ number_format($retard->montant_amende, 0, ',', ' ') }} FCFA</div>
                                                <form action="{{ route('amendes.payer', $retard->id) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="btn btn-sm btn-link text-success p-0 fw-bold border-0 bg-transparent"
                                                        title="Solder l'amende">
                                                        <i class="fas fa-check-double"></i> Payer
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted fst-italic">
                                                <i class="fas fa-check-circle text-success me-2"></i>Aucun retard critique
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Emprunts Récents -->
            <div class="col-xl-7 col-lg-6 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header py-3 bg-white border-bottom">
                        <h6 class="m-0 font-weight-bold text-brown"><i class="fas fa-history me-2 text-gold"></i>Activités
                            Récentes</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light small text-uppercase">
                                    <tr>
                                        <th class="ps-3">Livre</th>
                                        <th>Étudiant</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($emprunts_recents as $recent)
                                        <tr>
                                            <td class="ps-3">
                                                <span
                                                    class="text-brown fw-bold">{{ Str::limit($recent->livre->titre, 30) }}</span>
                                            </td>
                                            <td>{{ $recent->user->name }}</td>
                                            <td>
                                                @if($recent->statut == 'rendu')
                                                    <span class="badge bg-success-soft text-success px-2 py-1">Rendu</span>
                                                @else
                                                    <span class="badge bg-gold-soft text-brown px-2 py-1">En cours</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Services de Gestion Rapide -->

    <div class="row mb-5">

        <div class="col-12 text-center">

            <div class="p-4 rounded shadow-sm bg-white border">

                <h6 class="mb-4 fw-bold text-uppercase letter-spacing-2" style="color: #5D4037;">Services de Gestion Rapide
                </h6>

                <div class="d-flex justify-content-around flex-wrap">

                    <div class="action-item">

                        <a href="{{ route('livres.create') }}" class="btn-action bg-brown shadow">

                            <i class="fas fa-plus text-white"></i>

                        </a>

                        <p class="mt-2 small fw-bold text-brown">Ajouter Livre</p>

                    </div>

                    <div class="action-item">

                        <a href="{{ route('emprunts.create') }}" class="btn-action bg-gold shadow">

                            <i class="fas fa-handshake text-white"></i>

                        </a>

                        <p class="mt-2 small fw-bold text-brown">Nouvel Emprunt</p>

                    </div>

                    <div class="action-item">

                        <a href="{{ route('livres.index') }}" class="btn-action bg-leather shadow">

                            <i class="fas fa-search text-white"></i>

                        </a>

                        <p class="mt-2 small fw-bold text-brown">Catalogue</p>

                    </div>

                    <div class="action-item">

                        <a href="{{ route('etudiants.create') }}" class="btn-action bg-brown shadow">

                            <i class="fas fa-user-plus text-white"></i>

                        </a>

                        <p class="mt-2 small fw-bold text-brown">Nouvel Étudiant</p>

                    </div>

                </div>

            </div>

        </div>

    </div>

    </div>

@endsection



@push('styles')

    <style>
        :root {

            --brown: #5D4037;

            --gold: #D4AF37;

            --leather: #8D6E63;

            --paper: #FAF3E0;

        }

        .text-brown {
            color: var(--brown);
        }

        .text-gold {
            color: var(--gold);
        }

        .bg-brown {
            background-color: var(--brown);
        }

        .bg-gold {
            background-color: var(--gold);
        }

        .bg-leather {
            background-color: var(--leather);
        }

        .border-gold {
            border: 1px solid var(--gold);
        }

        .bg-gold-soft {
            background-color: rgba(212, 175, 55, 0.1);
        }



        .card-stat {
            transition: transform 0.3s ease;
            background-color: #fff;
        }

        .card-stat:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
        }



        .btn-action {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1.5rem;
        }

        .btn-action:hover {
            transform: scale(1.1) rotate(5deg);
            filter: brightness(1.2);
        }



        .letter-spacing-2 {
            letter-spacing: 2px;
        }
    </style>

@endpush



@push('scripts')

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        (function () {
            "use strict";
            document.addEventListener("DOMContentLoaded", function () {
                // Raccourcis pour les couleurs
                const colors = {
                    brown: '#5D4037',
                    gold: '#D4AF37',
                    leather: '#8D6E63',
                    paper: '#FAF3E0',
                    success: '#2ecc71',
                    danger: '#e74c3c'
                };

                // Récupération des données depuis le pont JS
                const labelsFlux = JSON.parse(document.getElementById('chart-labels').innerText);
                const dataFlux = JSON.parse(document.getElementById('chart-data').innerText);
                const catLabels = JSON.parse(document.getElementById('cat-labels').innerText);
                const catCounts = JSON.parse(document.getElementById('cat-counts').innerText);

                // --- GRAPHIQUE LINÉAIRE (FLUX HEBDO) ---
                const ctxEmprunts = document.getElementById('empruntsChart');
                if (ctxEmprunts) {
                    new Chart(ctxEmprunts.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: labelsFlux,
                            datasets: [{
                                label: 'Nouveaux Emprunts',
                                data: dataFlux,
                                borderColor: colors.gold,
                                backgroundColor: 'rgba(212, 175, 55, 0.1)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: colors.brown,
                                pointBorderColor: '#fff',
                                pointRadius: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { stepSize: 1, color: '#888' },
                                    grid: { color: 'rgba(0,0,0,0.05)' }
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: { color: '#888' }
                                }
                            }
                        }
                    });
                }

                // --- GRAPHIQUE CIRCULAIRE (CATÉGORIES) ---
                const ctxCat = document.getElementById('categoryChart');
                if (ctxCat) {
                    const palette = [colors.brown, colors.gold, colors.leather, '#A1887F', '#D7CCC8', '#3E2723'];

                    new Chart(ctxCat.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: catLabels,
                            datasets: [{
                                data: catCounts,
                                backgroundColor: palette,
                                hoverOffset: 10,
                                borderWidth: 2,
                                borderColor: '#fff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            cutout: '65%'
                        }
                    });

                    // Génération de la légende personnalisée
                    const legendContainer = document.getElementById('category-legend');
                    catLabels.slice(0, 5).forEach((label, i) => {
                        const div = document.createElement('div');
                        div.className = 'd-flex align-items-center mb-1';
                        div.innerHTML = `
                            <span style="width:10px;height:10px;background:${palette[i % palette.length]};display:inline-block;margin-right:8px;border-radius:2px;"></span>
                            <span class="text-truncate" style="max-width:150px">${label} (${catCounts[i]})</span>
                        `;
                        legendContainer.appendChild(div);
                    });
                }
            });
        })();
    </script>

@endpush