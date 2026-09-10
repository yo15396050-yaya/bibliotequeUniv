@extends('layouts.dashboard')

@section('title', 'Réservation #' . ($reservation->id ?? ''))

@section('content')
<div class="container-fluid py-4" style="background-color: #F4F7FC; min-height: 100vh;">
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px; overflow: hidden;">
                <!-- En-tête : Bois Sombre (#123A7A) avec bordure Or (#2563EB) -->
                <div class="card-header py-3 border-0 d-flex justify-content-between align-items-center" style="background-color: #123A7A; border-bottom: 3px solid #2563EB !important;">
                    <h5 class="mb-0 fw-bold text-white">
                        <i class="fas fa-receipt me-2" style="color: #2563EB;"></i>Dossier Réservation #{{ $reservation->id }}
                    </h5>
                    @if($reservation->estActive())
                        <span class="badge rounded-pill px-3 shadow-sm" style="background-color: #2563EB; color: #123A7A;">Active</span>
                    @elseif($reservation->estExpiree())
                        <span class="badge bg-danger rounded-pill px-3 shadow-sm text-white">Expirée</span>
                    @else
                        <span class="badge bg-success rounded-pill px-3 shadow-sm text-white">Terminée</span>
                    @endif
                </div>

                <div class="card-body p-4 bg-white">
                    <!-- Section Temporelle avec vérification de nullité pour éviter l'erreur format() -->
                    <div class="row g-4 mb-4 text-center bg-light p-3 rounded" style="border: 1px inset #f0e6d2;">
                        <div class="col-6 col-md-3">
                            <div class="text-muted small text-uppercase fw-bold">Créée le</div>
                            <div class="fw-bold" style="color: #123A7A;">
                                {{ $reservation->date_reservation ? $reservation->date_reservation->format('d/m/Y') : 'N/A' }}
                            </div>
                            <div class="text-muted small">
                                {{ $reservation->date_reservation ? $reservation->date_reservation->format('H:i') : '--:--' }}
                            </div>
                        </div>
                        <div class="col-6 col-md-3 border-start">
                            <div class="text-muted small text-uppercase fw-bold text-danger">Expire le</div>
                            <div class="fw-bold text-danger">
                                {{ $reservation->date_fin_reservation ? $reservation->date_fin_reservation->format('d/m/Y') : 'N/A' }}
                            </div>
                            <div class="text-muted small">
                                {{ $reservation->date_fin_reservation ? $reservation->date_fin_reservation->format('H:i') : '--:--' }}
                            </div>
                        </div>
                        <div class="col-md-6 border-start d-none d-md-block text-start ps-4">
                            <div class="text-muted small text-uppercase fw-bold" style="color: #123A7A;">Temps restant</div>
                            <h4 class="fw-bold mb-0" style="color: #2563EB;">
                                @if($reservation->estActive() && $reservation->date_fin_reservation)
                                    {{ now()->diffInDays($reservation->date_fin_reservation) }} jours
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </h4>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <!-- Titre avec bordure Dorée -->
                            <h6 class="fw-bold mb-3 pb-2" style="color: #123A7A; border-bottom: 2px solid #2563EB;">
                                <i class="fas fa-book me-2" style="color: #2563EB;"></i>L'Ouvrage
                            </h6>
                            <div class="mb-3 p-2 rounded" style="background-color: #fcfcfc;">
                                <label class="text-muted small d-block">Titre complet</label>
                                <span class="fw-bold text-dark">{{ $reservation->livre->titre ?? 'Inconnu' }}</span>
                            </div>
                            <div class="mb-3 p-2">
                                <label class="text-muted small d-block">Auteur</label>
                                <span class="text-dark">{{ $reservation->livre->auteur ?? 'Inconnu' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 border-start">
                            <h6 class="fw-bold mb-3 pb-2" style="color: #123A7A; border-bottom: 2px solid #2563EB;">
                                <i class="fas fa-user-graduate me-2" style="color: #2563EB;"></i>L'Étudiant
                            </h6>
                            <div class="mb-3 p-2 rounded" style="background-color: #fcfcfc;">
                                <label class="text-muted small d-block">Nom & Prénoms</label>
                                <span class="fw-bold text-dark">{{ $reservation->user->name ?? 'Inconnu' }}</span>
                            </div>
                            <div class="mb-3 p-2">
                                <label class="text-muted small d-block">Identifiant / Matricule</label>
                                <code class="px-2 py-1 rounded" style="background-color: #F4F7FC; color: #123A7A; border: 1px solid #2563EB;">
                                    {{ $reservation->user->matricule ?? '-' }}
                                </code>
                            </div>
                        </div>
                    </div>

                    @if($reservation->notes)
                    <div class="mt-4 p-3 rounded italic shadow-sm" style="background-color: #fff9eb; border-left: 4px solid #2563EB;">
                        <strong style="color: #123A7A;">Note interne :</strong><br>
                        <span class="text-muted small italic">"{{ $reservation->notes }}"</span>
                    </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center mt-5 pt-3 border-top">
                        <a href="{{ route('reservations.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                            <i class="fas fa-arrow-left me-2"></i>Retour
                        </a>
                        
                        <div class="btn-group gap-2">
                            @if($reservation->estActive())
                                <a href="{{ route('reservations.edit', $reservation) }}" class="btn btn-light border rounded-pill px-4 shadow-sm">
                                    <i class="fas fa-edit me-2" style="color: #123A7A;"></i>Modifier
                                </a>
                                
                                <form method="POST" action="{{ route('reservations.annuler', $reservation) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-custom-gold rounded-pill px-4 shadow-sm" onclick="return confirm('Confirmer l\'annulation ?')">
                                        <i class="fas fa-times me-2"></i>Annuler la demande
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar contextuelle -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header text-white py-3 border-0" style="background-color: #123A7A; border-bottom: 2px solid #2563EB !important;">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2" style="color: #2563EB;"></i>Statut de l'Ouvrage</h6>
                </div>
                <div class="card-body" style="background-color: #fdfbf7;">
                    <ul class="list-group list-group-flush bg-transparent">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                            <span class="text-muted small font-weight-bold">ISBN</span>
                            <span class="fw-bold" style="color: #123A7A;">{{ $reservation->livre->isbn ?? 'N/A' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                            <span class="text-muted small font-weight-bold">Rayon</span>
                            <span class="badge border text-dark bg-white" style="border-color: #2563EB !important;">{{ $reservation->livre->emplacement_rayon ?? '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                            <span class="text-muted small font-weight-bold">Disponibilité</span>
                            <span class="fw-bold {{ ($reservation->livre->exemplaires_disponibles ?? 0) > 0 ? 'text-success' : 'text-danger' }}">
                                {{ $reservation->livre->exemplaires_disponibles ?? 0 }} / {{ $reservation->livre->exemplaires_totaux ?? 0 }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header text-white py-3 border-0" style="background-color: #123A7A;">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-user-graduate me-2" style="color: #2563EB;"></i>Contact Étudiant</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3 d-flex align-items-center">
                        <i class="fas fa-phone-alt me-3" style="color: #2563EB;"></i>
                        <span class="text-dark">{{ $reservation->user->telephone ?? 'Non renseigné' }}</span>
                    </div>
                    <div class="mb-3 d-flex align-items-center">
                        <i class="fas fa-envelope me-3" style="color: #2563EB;"></i>
                        <a href="mailto:{{ $reservation->user->email ?? '' }}" class="text-decoration-none" style="color: #123A7A; font-weight: 500;">
                            {{ $reservation->user->email ?? 'N/A' }}
                        </a>
                    </div>
                    <div class="mb-0 d-flex align-items-center">
                        <i class="fas fa-university me-3" style="color: #2563EB;"></i>
                        <span class="small text-muted">{{ $reservation->user->filiere ?? '-' }} ({{ $reservation->user->niveau ?? '-' }})</span>
                    </div>
                    
                    <hr class="my-3 shadow-sm">
                    <div class="d-grid">
                        <a href="{{ route('etudiants.show', $reservation->user->id ?? 0) }}" class="btn btn-sm btn-outline-dark rounded-pill">
                            Consulter le profil complet
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Bouton Or (#2563EB) -> Bois Sombre (#123A7A) */
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
    }

    .badge { font-weight: 600; letter-spacing: 0.5px; }
    .italic { font-style: italic; }
</style>
@endsection