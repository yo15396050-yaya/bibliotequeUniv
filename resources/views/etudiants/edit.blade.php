@extends('layouts.dashboard')

@section('title', 'Modifier l\'Étudiant')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('etudiants.index') }}" style="color: #5D4037;">Étudiants</a></li>
    <li class="breadcrumb-item active">Mise à jour</li>
@endsection

@section('page-title', 'Modifier l\'Étudiant')

@section('content')
<style>
    .edit-wrapper {
        background-color: #FAF3E0;
        padding: 2rem;
        border-radius: 12px;
    }

    .custom-card {
        border: none;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        border-radius: 10px;
    }

    .custom-header {
        background-color: #5D4037 !important;
        color: #D4AF37 !important;
        border-bottom: 3px solid #D4AF37;
    }

    .section-divider {
        border-bottom: 2px solid #f0e6d2;
        padding-bottom: 10px;
        margin-bottom: 20px;
        color: #5D4037;
        font-weight: bold;
    }

    .form-label {
        color: #5D4037;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .input-group-text {
        background-color: #f8f1e0;
        color: #5D4037;
        border-color: #ced4da;
    }

    .btn-update {
        background-color: #5D4037;
        color: #D4AF37;
        font-weight: bold;
        border: none;
        padding: 0.6rem 1.5rem;
    }

    .btn-update:hover {
        background-color: #3e2b25;
        color: #FAF3E0;
    }

    /* Style spécifique pour le switch d'activation */
    .form-check-input:checked {
        background-color: #D4AF37;
        border-color: #D4AF37;
    }
</style>

<div class="edit-wrapper">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card custom-card">
                <div class="card-header custom-header p-3">
                    <h5 class="mb-0">
                        <i class="fas fa-user-edit me-2"></i>Mise à jour du dossier : {{ $etudiant->name }}
                    </h5>
                </div>
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('etudiants.update', $etudiant) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 pe-md-4 border-end">
                                <h6 class="section-divider"><i class="fas fa-id-card-alt me-2"></i>État Civil</h6>
                                
                                <div class="mb-3">
                                    <label for="nom" class="form-label">Nom de famille *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                        <input type="text" class="form-control" id="nom" name="nom" 
                                               value="{{ old('nom', explode(' ', $etudiant->name, 2)[1] ?? '') }}" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="prenom" class="form-label">Prénom *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" id="prenom" name="prenom" 
                                               value="{{ old('prenom', explode(' ', $etudiant->name, 2)[0] ?? '') }}" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Professionnel *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="{{ old('email', $etudiant->email) }}" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="telephone" class="form-label">Téléphone</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                        <input type="text" class="form-control" id="telephone" name="telephone" 
                                               value="{{ old('telephone', $etudiant->telephone) }}">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="adresse" class="form-label">Adresse Résidentielle</label>
                                    <textarea class="form-control" id="adresse" name="adresse" rows="2">{{ old('adresse', $etudiant->adresse) }}</textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-6 ps-md-4">
                                <h6 class="section-divider"><i class="fas fa-graduation-cap me-2"></i>Cursus & Statut</h6>
                                
                                <div class="mb-3">
                                    <label for="matricule" class="form-label">Numéro Matricule *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-fingerprint"></i></span>
                                        <input type="text" class="form-control fw-bold text-primary" id="matricule" name="matricule" 
                                               value="{{ old('matricule', $etudiant->matricule) }}" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="date_naissance" class="form-label">Date de naissance</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar-day"></i></span>
                                        <input type="date" class="form-control" id="date_naissance" name="date_naissance" 
                                               value="{{ old('date_naissance', $etudiant->date_naissance ? $etudiant->date_naissance->format('Y-m-d') : '') }}">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="filiere" class="form-label">Filière</label>
                                    <select class="form-select" id="filiere" name="filiere">
                                        <option value="">-- Sélectionner --</option>
                                        @foreach(['Informatique', 'Gestion', 'Droit', 'Économie', 'Sciences'] as $f)
                                            <option value="{{ $f }}" {{ old('filiere', $etudiant->filiere) == $f ? 'selected' : '' }}>{{ $f }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="niveau" class="form-label">Niveau d'études</label>
                                    <select class="form-select" id="niveau" name="niveau">
                                        <option value="">-- Sélectionner --</option>
                                        @foreach(['Licence 1', 'Licence 2', 'Licence 3', 'Master 1', 'Master 2'] as $n)
                                            <option value="{{ $n }}" {{ old('niveau', $etudiant->niveau) == $n ? 'selected' : '' }}>{{ $n }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="p-3 rounded-3" style="background-color: #f8f1e0; border: 1px solid #eaddca;">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="actif" name="actif" 
                                               {{ $etudiant->actif ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-wood" for="actif">
                                            Autoriser l'accès à la bibliothèque
                                        </label>
                                    </div>
                                    <small class="text-muted d-block mt-1">Si décoché, l'étudiant ne pourra plus effectuer d'emprunts.</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-5 pt-4 border-top">
                            <div>
                                <a href="{{ route('etudiants.index') }}" class="btn btn-outline-secondary me-2">
                                    <i class="fas fa-list me-1"></i>Liste
                                </a>
                                <a href="{{ route('etudiants.show', $etudiant) }}" class="btn btn-outline-dark">
                                    Annuler
                                </a>
                            </div>
                            <button type="submit" class="btn btn-update shadow">
                                <i class="fas fa-save me-2"></i>Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection