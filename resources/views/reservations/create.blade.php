@extends('layouts.dashboard')

@section('title', 'Nouvelle Réservation')

@push('styles')
<style>
    /* Arrière-plan Papier Crème */
    .reservation-page {
        background-color: #FAF3E0;
        min-height: 100vh;
        padding-top: 2rem;
    }

    /* Style de la Carte */
    .card-custom {
        border: none;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    /* En-tête Bois Sombre et Or */
    .card-custom .card-header {
        background-color: #5D4037 !important; /* Bois Sombre */
        color: #FAF3E0 !important;
        border-bottom: 3px solid #D4AF37 !important; /* Bordure Or */
        padding: 1.5rem;
    }

    .icon-box-gold {
        background-color: rgba(212, 175, 55, 0.2);
        padding: 10px;
        border-radius: 8px;
        color: #D4AF37;
    }

    /* Labels et Inputs */
    .form-label {
        color: #5D4037;
        font-weight: 600;
    }

    .form-control:focus, .form-select:focus {
        border-color: #D4AF37;
        box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.25);
    }

    /* Bouton Or (Gold) */
    .btn-gold {
        background-color: #D4AF37 !important;
        border-color: #D4AF37 !important;
        color: #5D4037 !important;
        font-weight: bold;
        transition: all 0.3s ease;
    }

    .btn-gold:hover {
        background-color: #5D4037 !important;
        border-color: #5D4037 !important;
        color: #FAF3E0 !important;
        transform: translateY(-2px);
    }

    /* Note d'information personnalisée */
    .info-box-custom {
        background-color: #fcf8e3;
        border-left: 5px solid #D4AF37;
        color: #856404;
    }

    /* Fil d'Ariane (Breadcrumb) */
    .breadcrumb-item a {
        color: #5D4037;
        text-decoration: none;
    }
    .breadcrumb-item.active {
        color: #D4AF37;
    }
</style>
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.css" rel="stylesheet">
@endpush

@section('content')
<div class="reservation-page">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('reservations.index') }}">Réservations</a></li>
                        <li class="breadcrumb-item active">Nouveau formulaire</li>
                    </ol>
                </nav>

                <div class="card card-custom">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <div class="icon-box-gold me-3">
                                <i class="fas fa-calendar-plus fa-lg"></i>
                            </div>
                            <h5 class="mb-0 fw-bold">Créer une réservation d'ouvrage</h5>
                        </div>
                    </div>
                    
                    <div class="card-body p-4 bg-white">
                        <form method="POST" action="{{ route('reservations.store') }}">
                            @csrf
                            
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label for="livre_id" class="form-label">Ouvrage à réserver <span class="text-danger">*</span></label>
                                    <select name="livre_id" id="livre_id" class="form-select @error('livre_id') is-invalid @enderror" required>
                                        <option value=""></option>
                                        @foreach($livres as $livre)
                                            <option value="{{ $livre->id }}" {{ old('livre_id') == $livre->id ? 'selected' : '' }}>
                                                {{ $livre->titre }} — {{ $livre->auteur }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('livre_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Seuls les livres disponibles ou déjà empruntés apparaissent.</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="user_id" class="form-label">Bénéficiaire (Étudiant) <span class="text-danger">*</span></label>
                                    <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                                        <option value=""></option>
                                        @foreach($etudiants as $etudiant)
                                            <option value="{{ $etudiant->id }}" {{ old('user_id') == $etudiant->id ? 'selected' : '' }}>
                                                {{ $etudiant->name }} ({{ $etudiant->matricule }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label for="date_reservation" class="form-label">Début de validité</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="far fa-clock text-brown"></i></span>
                                        <input type="datetime-local" name="date_reservation" id="date_reservation" 
                                               class="form-control @error('date_reservation') is-invalid @enderror" 
                                               value="{{ old('date_reservation', now()->format('Y-m-d\TH:i')) }}" required>
                                    </div>
                                    @error('date_reservation')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="date_fin_reservation" class="form-label">Date d'expiration <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-hourglass-end text-gold"></i></span>
                                        <input type="datetime-local" name="date_fin_reservation" id="date_fin_reservation" 
                                               class="form-control @error('date_fin_reservation') is-invalid @enderror" 
                                               value="{{ old('date_fin_reservation', now()->addDays(7)->format('Y-m-d\TH:i')) }}" required>
                                    </div>
                                    <div class="form-text text-danger italic small">Par défaut : 7 jours de réservation.</div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="notes" class="form-label">Notes & Observations</label>
                                <textarea name="notes" id="notes" rows="3" class="form-control" placeholder="Ex: Réservation prioritaire pour examen...">{{ old('notes') }}</textarea>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                <a href="{{ route('reservations.index') }}" class="btn btn-outline-secondary px-4">
                                    Annuler
                                </a>
                                <button type="submit" class="btn btn-gold px-5 shadow-sm">
                                    <i class="fas fa-check-circle me-2"></i>Confirmer la réservation
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="mt-4 info-box-custom p-3 rounded shadow-sm">
                    <p class="small mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Règle académique :</strong> Une réservation empêche l'emprunt de l'ouvrage par un autre étudiant jusqu'à sa date d'expiration.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        new TomSelect('#livre_id', { placeholder: "Chercher un livre...", allowEmptyOption: true });
        new TomSelect('#user_id', { placeholder: "Chercher un étudiant...", allowEmptyOption: true });
    });
</script>
@endpush