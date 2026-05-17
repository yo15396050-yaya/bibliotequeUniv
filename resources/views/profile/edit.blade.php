@extends('layouts.app')

@section('title', 'Mon Profil')

@push('styles')
<style>
    /* Arrière-plan principal */
    .profile-container {
        background-color: #FAF3E0;
        padding: 2rem 0;
        min-height: 100vh;
    }

    /* Carte et En-tête */
    .card-custom {
        border: none;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .card-custom .card-header {
        background-color: #5D4037 !important; /* Bois Sombre */
        color: #FAF3E0 !important;
        border-bottom: 3px solid #D4AF37 !important; /* Bordure Or */
    }

    /* Titres de sections */
    .section-title {
        color: #5D4037;
        border-bottom: 2px solid #D4AF37 !important;
        font-weight: bold;
        text-transform: uppercase;
        font-size: 0.9rem;
        letter-spacing: 1px;
    }

    /* Champs de formulaire */
    .form-control:focus {
        border-color: #D4AF37 !important; /* Focus Or */
        box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.25);
    }

    /* Bouton d'enregistrement */
    .btn-gold {
        background-color: #D4AF37 !important;
        border-color: #D4AF37 !important;
        color: #5D4037 !important;
        font-weight: bold;
        transition: all 0.3s ease;
    }

    .btn-gold:hover {
        background-color: #5D4037 !important; /* Bois Sombre au survol */
        border-color: #5D4037 !important;
        color: #FAF3E0 !important;
    }

    /* Alertes et badges */
    .alert-info-custom {
        background-color: #fcf8e3;
        border-left: 4px solid #D4AF37;
        color: #856404;
    }
</style>
@endpush

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
    <li class="breadcrumb-item active">Mon Profil</li>
@endsection

@section('content')
<div class="profile-container">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card card-custom">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user-circle me-2"></i>Mon Profil
                        </h5>
                    </div>
                    <div class="card-body bg-white">
                        <form action="{{ route('profile.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-5">
                                <h6 class="section-title pb-2 mb-4">
                                    <i class="fas fa-info-circle me-2"></i>Informations personnelles
                                </h6>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label fw-bold">Nom complet *</label>
                                        <input type="text" 
                                               class="form-control @error('name') is-invalid @enderror" 
                                               id="name" name="name" 
                                               value="{{ old('name', $user->name) }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label fw-bold">Email *</label>
                                        <input type="email" 
                                               class="form-control @error('email') is-invalid @enderror" 
                                               id="email" name="email" 
                                               value="{{ old('email', $user->email) }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="telephone" class="form-label fw-bold">Téléphone</label>
                                        <input type="text" 
                                               class="form-control @error('telephone') is-invalid @enderror" 
                                               id="telephone" name="telephone" 
                                               value="{{ old('telephone', $user->telephone) }}">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="adresse" class="form-label fw-bold">Adresse</label>
                                        <input type="text" 
                                               class="form-control @error('adresse') is-invalid @enderror" 
                                               id="adresse" name="adresse" 
                                               value="{{ old('adresse', $user->adresse) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-5">
                                <h6 class="section-title pb-2 mb-4">
                                    <i class="fas fa-key me-2"></i>Sécurité
                                </h6>
                                
                                <div class="alert alert-info-custom mb-4 shadow-sm">
                                    <i class="fas fa-lightbulb me-2"></i>
                                    Laissez ces champs vides si vous ne souhaitez pas changer votre mot de passe.
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="current_password" class="form-label fw-bold">Mot de passe actuel</label>
                                        <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                               id="current_password" name="current_password">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="new_password" class="form-label fw-bold">Nouveau mot de passe</label>
                                        <input type="password" class="form-control @error('new_password') is-invalid @enderror" 
                                               id="new_password" name="new_password">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="new_password_confirmation" class="form-label fw-bold">Confirmation</label>
                                        <input type="password" class="form-control" 
                                               id="new_password_confirmation" name="new_password_confirmation">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-5">
                                <h6 class="section-title pb-2 mb-4">
                                    <i class="fas fa-database me-2"></i>Informations système
                                </h6>
                                
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <div class="p-3 border rounded bg-light">
                                            <small class="text-muted d-block">Rôle</small>
                                            <span class="fw-bold">{{ ucfirst($user->role ?? 'Étudiant') }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 border rounded bg-light">
                                            <small class="text-muted d-block">Membre depuis</small>
                                            <span class="fw-bold">{{ $user->created_at?->format('d/m/Y') ?? 'Non définie' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 border rounded bg-light">
                                            <small class="text-muted d-block">Dernière connexion</small>
                                            <span class="fw-bold">{{ $user->last_login_at ? $user->last_login_at->format('d/m/Y') : 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary px-4">
                                    <i class="fas fa-arrow-left me-2"></i>Retour
                                </a>
                                <div>
                                    <button type="reset" class="btn btn-link text-danger text-decoration-none me-3">
                                        Réinitialiser
                                    </button>
                                    <button type="submit" class="btn btn-gold px-4 py-2 shadow-sm">
                                        <i class="fas fa-save me-2"></i>Mettre à jour le profil
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const currentPassword = document.getElementById('current_password');
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('new_password_confirmation');
        
        function validatePasswords() {
            if (newPassword.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Les mots de passe ne correspondent pas');
            } else {
                confirmPassword.setCustomValidity('');
            }
            
            if (newPassword.value && !currentPassword.value) {
                currentPassword.setCustomValidity('Veuillez saisir votre mot de passe actuel');
            } else {
                currentPassword.setCustomValidity('');
            }
        }
        
        newPassword.addEventListener('input', validatePasswords);
        confirmPassword.addEventListener('input', validatePasswords);
        currentPassword.addEventListener('input', validatePasswords);
    });
</script>
@endpush