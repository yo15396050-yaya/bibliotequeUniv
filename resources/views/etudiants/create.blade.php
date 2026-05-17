@extends('layouts.dashboard')

@section('title', 'Inscription Étudiant')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('etudiants.index') }}" style="color: #5D4037;">Étudiants</a></li>
    <li class="breadcrumb-item active">Nouvelle Inscription</li>
@endsection

@section('page-title', 'Ajouter un Nouvel Étudiant')

@section('content')
<style>
    .form-wrapper {
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
        padding: 1.2rem;
    }

    .form-label {
        color: #5D4037;
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }

    .input-group-text {
        background-color: #f8f1e0;
        border-color: #ced4da;
        color: #5D4037;
    }

    .form-control:focus, .form-select:focus {
        border-color: #D4AF37;
        box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.25);
    }

    .info-box {
        background-color: #fff9e6;
        border-left: 5px solid #D4AF37;
        color: #5D4037;
        font-size: 0.85rem;
    }

    .btn-save {
        background-color: #5D4037;
        color: #D4AF37;
        border: none;
        padding: 0.6rem 1.5rem;
        font-weight: bold;
        transition: 0.3s;
    }

    .btn-save:hover {
        background-color: #3e2b25;
        color: #FAF3E0;
        transform: translateY(-2px);
    }

    .btn-back {
        background-color: transparent;
        color: #5D4037;
        border: 1px solid #5D4037;
    }

    .btn-back:hover {
        background-color: #5D4037;
        color: white;
    }
</style>

<div class="form-wrapper">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card custom-card">
                <div class="card-header custom-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user-plus me-2"></i>Formulaire d'Inscription
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('etudiants.store') }}" method="POST">
                        @csrf

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control @error('nom') is-invalid @enderror" 
                                           id="nom" name="nom" value="{{ old('nom') }}" placeholder="Ex: DUPONT" required>
                                </div>
                                @error('nom') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control @error('prenom') is-invalid @enderror" 
                                           id="prenom" name="prenom" value="{{ old('prenom') }}" placeholder="Ex: Jean" required>
                                </div>
                                @error('prenom') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="email" class="form-label">Adresse Email <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}" placeholder="nom@etudiant.univ.fr" required>
                                </div>
                                @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="matricule" class="form-label">N° Matricule <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                    <input type="text" class="form-control @error('matricule') is-invalid @enderror" 
                                           id="matricule" name="matricule" value="{{ old('matricule') }}" placeholder="Ex: ETU-2024-001" required>
                                </div>
                                @error('matricule') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="telephone" class="form-label">Téléphone</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="text" class="form-control @error('telephone') is-invalid @enderror" 
                                           id="telephone" name="telephone" value="{{ old('telephone') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="date_naissance" class="form-label">Date de Naissance</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="date" class="form-control @error('date_naissance') is-invalid @enderror" 
                                           id="date_naissance" name="date_naissance" value="{{ old('date_naissance') }}">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="filiere" class="form-label">Filière Académique</label>
                                <select class="form-select @error('filiere') is-invalid @enderror" id="filiere" name="filiere">
                                    <option value="">-- Choisir --</option>
                                    @foreach(['Informatique', 'Mathématiques', 'Physique', 'Chimie', 'Droit', 'Économie'] as $f)
                                        <option value="{{ $f }}" {{ old('filiere') == $f ? 'selected' : '' }}>{{ $f }}</option>
                                    @endforeach
                                    <option value="Autre">Autre</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="niveau" class="form-label">Niveau d'Études</label>
                                <select class="form-select @error('niveau') is-invalid @enderror" id="niveau" name="niveau">
                                    <option value="">-- Choisir --</option>
                                    @foreach(['Licence 1', 'Licence 2', 'Licence 3', 'Master 1', 'Master 2', 'Doctorat'] as $n)
                                        <option value="{{ $n }}" {{ old('niveau') == $n ? 'selected' : '' }}>{{ $n }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="adresse" class="form-label">Adresse Domicile</label>
                            <textarea class="form-control" id="adresse" name="adresse" rows="2" placeholder="Rue, Ville, Code Postal...">{{ old('adresse') }}</textarea>
                        </div>

                        <div class="alert info-box d-flex align-items-center mb-4 shadow-sm">
                            <i class="fas fa-shield-alt fa-2x me-3 text-gold" style="color: #D4AF37;"></i>
                            <div>
                                <strong>Sécurité :</strong> Un compte sera créé automatiquement. Le mot de passe provisoire est <code class="bg-white px-1">password</code>.
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center border-top pt-4">
                            <a href="{{ route('etudiants.index') }}" class="btn btn-back">
                                <i class="fas fa-times me-2"></i>Annuler
                            </a>
                            <button type="submit" class="btn btn-save shadow">
                                <i class="fas fa-check-circle me-2"></i>Finaliser l'Inscription
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection