@extends('layouts.dashboard')

@section('title', 'Mon Espace Personnel')

@section('content')
<div class="container-fluid px-4" style="background-color: #FAF3E0; min-height: 100vh;">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3 pt-3">
        <div>
            <h1 class="h2 mb-1 fw-bold" style="color: #3E2723;">Mon Espace Personnel</h1>
            <p class="mb-0 text-muted"><strong>Heureux de vous revoir,</strong> <span class="fw-bold" style="color: #D4AF37;">{{ Auth::user()->name }} 👋</span></p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="badge rounded-pill p-2 shadow-sm" style="background-color: #5D4037; color: #D4AF37;">
                Matricule: {{ Auth::user()->matricule ?? 'N/A' }}
            </span>
        </div>
    </div>

    <!-- Cartes de Statistiques Personnelles -->
    <div class="row mb-4">
        <!-- Emprunts Totaux -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 py-2" style="border-left: 5px solid #D4AF37 !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #A1887F;">Total Emprunts</div>
                            <div class="h3 mb-0 font-weight-bold" style="color: #3E2723;">{{ $stats['total_emprunts'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-book fa-2x" style="color: #D4AF37;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Emprunts en cours -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 py-2" style="border-left: 5px solid #5D4037 !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #A1887F;">Emprunts en cours</div>
                            <div class="h3 mb-0 font-weight-bold" style="color: #3E2723;">{{ $stats['emprunts_en_cours'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hourglass-half fa-2x" style="color: #5D4037;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Réservations Actives -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 py-2" style="border-left: 5px solid #8D6E63 !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #A1887F;">Réservations Actives</div>
                            <div class="h3 mb-0 font-weight-bold" style="color: #3E2723;">{{ $stats['reservations_actives'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bookmark fa-2x" style="color: #8D6E63;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mes Amendes -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 py-2" style="border-left: 5px solid #e74c3c !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #A1887F;">Mes Amendes</div>
                            <div class="h3 mb-0 font-weight-bold" style="color: #e74c3c;">{{ number_format($stats['total_amendes'], 0, ',', ' ') }} FCFA</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-coins fa-2x" style="color: #e74c3c;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Principale -->
    <div class="row mb-5">
        <!-- Historique Récent -->
        <div class="col-xl-8 col-lg-7 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header py-3 bg-white border-bottom-gold d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-brown"><i class="fas fa-history me-2 text-gold"></i>Mes Dernières Activités</h6>
                    <a href="{{ route('mes-emprunts') }}" class="btn btn-sm btn-outline-brown rounded-pill">Voir tout</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light small text-uppercase">
                                <tr>
                                    <th class="ps-3">Livre</th>
                                    <th>Date d'emprunt</th>
                                    <th>Date de retour prévue</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($emprunts_recents as $emprunt)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold text-brown">{{ $emprunt->livre->titre }}</div>
                                            <small class="text-muted">{{ $emprunt->livre->auteur }}</small>
                                        </td>
                                        <td>{{ $emprunt->date_emprunt->format('d/m/Y') }}</td>
                                        <td>
                                            <span class="{{ $emprunt->date_retour_prevue->isPast() && $emprunt->statut !== 'retourné' ? 'text-danger fw-bold' : '' }}">
                                                {{ $emprunt->date_retour_prevue->format('d/m/Y') }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($emprunt->statut == 'retourné')
                                                <span class="badge bg-success-soft text-success px-2 py-1">Retourné</span>
                                            @elseif($emprunt->statut == 'en retard')
                                                <span class="badge bg-danger-soft text-danger px-2 py-1">En retard</span>
                                            @else
                                                <span class="badge bg-gold-soft text-brown px-2 py-1">En cours</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted fst-italic">
                                            Aucune activité récente trouvée.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mes Réservations & Raccourcis -->
        <div class="col-xl-4 col-lg-5">
            <!-- Réservations -->
            <div class="card shadow-sm border-0 mb-4 h-100">
                <div class="card-header py-3 bg-white border-bottom-gold">
                    <h6 class="m-0 font-weight-bold text-brown"><i class="fas fa-bookmark me-2 text-gold"></i>Mes Réservations</h6>
                </div>
                <div class="card-body">
                    @forelse($reservations_recentes as $res)
                        <div class="d-flex align-items-center mb-3 p-2 rounded border-start border-4 border-gold bg-light">
                            <div class="ms-2">
                                <div class="fw-bold text-truncate" style="max-width: 200px;">{{ $res->livre->titre }}</div>
                                <small class="text-muted">Le {{ $res->created_at->format('d/m/Y') }}</small>
                            </div>
                            <div class="ms-auto">
                                <span class="badge bg-gold text-white">Active</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-bookmark fa-2x mb-2 opacity-25"></i>
                            <p class="small mb-0">Aucune réservation active.</p>
                        </div>
                    @endforelse
                    
                    <div class="d-grid mt-3">
                        <a href="{{ route('catalogue') }}" class="btn btn-gold text-white fw-bold shadow-sm">
                            <i class="fas fa-search me-2"></i>Parcourir le catalogue
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Services Rapides -->
    <div class="row mb-5">
        <div class="col-12 text-center">
            <div class="p-4 rounded shadow-sm bg-white border">
                <h6 class="mb-4 fw-bold text-uppercase letter-spacing-2" style="color: #5D4037;">Mes Services Rapides</h6>
                <div class="d-flex justify-content-around flex-wrap">
                    <div class="action-item">
                        <a href="{{ route('catalogue') }}" class="btn-action bg-gold shadow">
                            <i class="fas fa-book-open text-white"></i>
                        </a>
                        <p class="mt-2 small fw-bold text-brown">Catalogue</p>
                    </div>
                    <div class="action-item">
                        <a href="{{ route('mes-emprunts') }}" class="btn-action bg-brown shadow">
                            <i class="fas fa-history text-white"></i>
                        </a>
                        <p class="mt-2 small fw-bold text-brown">Mes Emprunts</p>
                    </div>
                    <div class="action-item">
                        <a href="{{ route('profile') }}" class="btn-action bg-leather shadow">
                            <i class="fas fa-user-cog text-white"></i>
                        </a>
                        <p class="mt-2 small fw-bold text-brown">Mon Profil</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --brown: #5D4037;
        --gold: #D4AF37;
        --leather: #8D6E63;
        --paper: #FAF3E0;
    }
    .text-brown { color: var(--brown); }
    .text-gold { color: var(--gold); }
    .bg-brown { background-color: var(--brown); }
    .bg-gold { background-color: var(--gold); }
    .bg-leather { background-color: var(--leather); }
    .border-bottom-gold { border-bottom: 2px solid var(--gold) !important; }
    
    .bg-success-soft { background-color: rgba(46, 204, 113, 0.1); }
    .bg-danger-soft { background-color: rgba(231, 76, 60, 0.1); }
    .bg-gold-soft { background-color: rgba(212, 175, 55, 0.1); }

    .btn-outline-brown {
        color: var(--brown);
        border-color: var(--brown);
    }
    .btn-outline-brown:hover {
        background-color: var(--brown);
        color: white;
    }
    .btn-gold {
        background-color: var(--gold);
        border: none;
    }
    .btn-gold:hover {
        background-color: #B8860B;
        color: white;
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
    .letter-spacing-2 { letter-spacing: 2px; }
</style>
@endsection
