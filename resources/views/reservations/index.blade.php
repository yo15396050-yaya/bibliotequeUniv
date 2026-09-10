@extends('layouts.dashboard')

@section('title', 'Gestion des Réservations')

@section('content')
<div class="container-fluid py-4" style="background-color: #F4F7FC; min-height: 100vh;">
    <div class="row align-items-center mb-4">
        <div class="col">
            <!-- Titre de section avec style Marron Sombre et Bordure Dorée -->
            <h2 class="fw-bold mb-1" style="color: #123A7A; border-left: 5px solid #2563EB; padding-left: 15px;">
                Réservations en attente
            </h2>
            <p class="text-muted small ps-4">Gérez les demandes de mise de côté des ouvrages.</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('reservations.create') }}" class="btn btn-custom-gold rounded-pill px-4 shadow-sm">
                <i class="fas fa-plus-circle me-2"></i>Nouvelle Réservation
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
        <!-- En-tête : Bois Sombre (#123A7A) avec bordure Or (#2563EB) -->
        <div class="card-header border-0 py-3" style="background-color: #123A7A; border-bottom: 3px solid #2563EB !important;">
            <h5 class="mb-0 text-white font-weight-bold">
                <i class="fas fa-list-ul me-2" style="color: #2563EB;"></i> Liste des demandes
            </h5>
        </div>

        <div class="card-body p-0 bg-white">
            @if($reservations->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background-color: #fcf8f3;">
                            <tr>
                                <th class="ps-4 py-3 border-0 text-uppercase small fw-bold" style="color: #123A7A;">Ouvrage</th>
                                <th class="py-3 border-0 text-uppercase small fw-bold" style="color: #123A7A;">Étudiant</th>
                                <th class="py-3 border-0 text-uppercase small fw-bold" style="color: #123A7A;">Période</th>
                                <th class="py-3 border-0 text-uppercase small fw-bold text-center" style="color: #123A7A;">Statut</th>
                                <th class="pe-4 py-3 border-0 text-uppercase small fw-bold text-end" style="color: #123A7A;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reservations as $reservation)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm rounded p-2 me-3 text-center shadow-sm" style="background-color: #F4F7FC; color: #2563EB; border: 1px solid #2563EB;">
                                            <i class="fas fa-book"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold" style="color: #123A7A;">{{ $reservation->livre->titre ?? 'Livre inconnu' }}</div>
                                            <div class="text-muted small">ISBN: {{ $reservation->livre->isbn ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $reservation->user->name ?? 'Utilisateur inconnu' }}</div>
                                    <div class="badge bg-light text-dark border">{{ $reservation->user->matricule ?? '-' }}</div>
                                </td>
                                <td>
                                    <div class="small">
                                        <span style="color: #2563EB;"><i class="far fa-calendar-alt me-1"></i></span> 
                                        {{ $reservation->date_reservation ? $reservation->date_reservation->format('d/m/Y') : 'N/A' }}
                                        <br>
                                        <span class="text-danger"><i class="far fa-clock me-1"></i></span> 
                                        <span class="fw-bold" style="color: #123A7A;">{{ $reservation->date_fin_reservation ? $reservation->date_fin_reservation->format('d/m/Y') : 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($reservation->estActive())
                                        <span class="badge rounded-pill px-3 shadow-sm" style="background-color: #2563EB; color: #123A7A;">
                                            ACTIVE
                                        </span>
                                    @elseif($reservation->estExpiree())
                                        <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger px-3 border border-danger">
                                            EXPIRÉE
                                        </span>
                                    @else
                                        <span class="badge rounded-pill bg-secondary px-3 text-white">
                                            TERMINÉE
                                        </span>
                                    @endif
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="btn-group shadow-sm rounded border overflow-hidden">
                                        <a href="{{ route('reservations.show', $reservation) }}" class="btn btn-white btn-sm" title="Détails">
                                            <i class="fas fa-eye" style="color: #123A7A;"></i>
                                        </a>
                                        
                                        @if($reservation->estActive())
                                            <a href="{{ route('emprunts.create', ['res_id' => $reservation->id]) }}" class="btn btn-white btn-sm border-start border-end" title="Convertir en Emprunt">
                                                <i class="fas fa-exchange-alt" style="color: #2563EB;"></i>
                                            </a>
                                            
                                            <form action="{{ route('reservations.annuler', $reservation) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-white btn-sm" title="Annuler" onclick="return confirm('Annuler cette réservation ?')">
                                                    <i class="fas fa-trash-alt text-danger"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="card-footer bg-white border-0 py-4">
                    <div class="d-flex justify-content-center">
                        {{ $reservations->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-4x mb-3" style="color: #2563EB; opacity: 0.3;"></i>
                    <h4 class="fw-light" style="color: #123A7A;">Aucune réservation trouvée</h4>
                    <p class="text-muted mb-4 small">Le registre des réservations est actuellement vide.</p>
                    <a href="{{ route('reservations.create') }}" class="btn btn-custom-gold px-4 rounded-pill">
                        Faire une réservation
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    /* Bouton Or (#2563EB) qui passe en Bois Sombre (#123A7A) au survol */
    .btn-custom-gold {
        background-color: #2563EB;
        color: #F4F7FC;
        border: none;
        font-weight: bold;
        transition: all 0.3s ease;
    }

    .btn-custom-gold:hover {
        background-color: #123A7A;
        color: #2563EB;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    /* Boutons de la table */
    .btn-white {
        background-color: #ffffff;
        border: none;
    }

    .btn-white:hover {
        background-color: #fcf8f3 !important;
    }

    /* Focus Doré pour les champs (si présents) */
    .form-control:focus {
        border-color: #2563EB;
        box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.25);
    }

    .avatar-sm { width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; }
    .badge { font-weight: 600; }
</style>
@endsection