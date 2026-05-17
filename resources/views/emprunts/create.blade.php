@extends('layouts.dashboard')

@section('title', 'Enregistrer un Emprunt')

@section('content')
<style>
    .loan-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    .loan-header {
        background: linear-gradient(45deg, #5D4037, #8d6e63);
        color: #D4AF37;
        padding: 2rem;
    }
    .form-section-title {
        color: #5D4037;
        font-weight: 700;
        border-bottom: 2px solid #D4AF37;
        padding-bottom: 5px;
        margin-bottom: 20px;
        display: inline-block;
    }
    .form-control, .form-select {
        border-radius: 8px;
        padding: 0.75rem;
        border: 1px solid #eaddca;
    }
    .form-control:focus, .form-select:focus {
        border-color: #D4AF37;
        box-shadow: 0 0 0 0.25 margin-bottom rgba(212, 175, 55, 0.25);
    }
    .info-tip {
        background-color: #fff9db;
        border-left: 4px solid #fab005;
        padding: 10px;
        font-size: 0.9rem;
        border-radius: 0 8px 8px 0;
    }
</style>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card loan-card">
                <div class="loan-header text-center">
                    <i class="fas fa-book-reader fa-3x mb-3"></i>
                    <h2 class="mb-0 fw-bold">Nouveau Registre d'Emprunt</h2>
                    <p class="opacity-75">Assigner un ouvrage à un étudiant</p>
                </div>

                <div class="card-body p-4 p-md-5 bg-white">
                    <form action="{{ route('emprunts.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-section-title"><i class="fas fa-user-graduate me-2"></i>Bénéficiaire</label>
                                <div class="mb-3">
                                    <label for="user_id" class="form-label fw-bold">Sélectionner l'Étudiant *</label>
                                    <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror shadow-sm" required>
                                        <option value="">Chercher par nom ou matricule...</option>
                                        @foreach($utilisateurs as $utilisateur)
                                            <option value="{{ $utilisateur->id }}" 
                                                {{ old('user_id') == $utilisateur->id ? 'selected' : '' }}
                                                data-emprunts="{{ $utilisateur->nombre_emprunts }}">
                                                {{ $utilisateur->name }} — [{{ $utilisateur->matricule }}]
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div id="user-warning" class="info-tip d-none">
                                    <i class="fas fa-info-circle me-1"></i> Cet étudiant a déjà plusieurs livres en sa possession.
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="form-section-title"><i class="fas fa-book me-2"></i>Ouvrage</label>
                                <div class="mb-3">
                                    <label for="livre_id" class="form-label fw-bold">Livre à emprunter *</label>
                                    <select name="livre_id" id="livre_id" class="form-select @error('livre_id') is-invalid @enderror shadow-sm" required>
                                        <option value="">Chercher un titre disponible...</option>
                                        @foreach($livres as $livre)
                                            <option value="{{ $livre->id }}" 
                                                {{ old('livre_id') == $livre->id ? 'selected' : '' }}
                                                data-stock="{{ $livre->exemplaires_disponibles }}"
                                                {{ $livre->exemplaires_disponibles <= 0 ? 'disabled' : '' }}>
                                                {{ $livre->titre }} ({{ $livre->exemplaires_disponibles }} dispo.)
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('livre_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="my-4" style="border-top: 2px dashed #eaddca;">

                        <div class="row">
                            <div class="col-md-5">
                                <label class="form-section-title"><i class="fas fa-calendar-alt me-2"></i>Échéance</label>
                                <div class="mb-3">
                                    <label for="date_retour_prevue" class="form-label fw-bold">Retour prévu le *</label>
                                    <input type="date" name="date_retour_prevue" id="date_retour_prevue" 
                                           class="form-control @error('date_retour_prevue') is-invalid @enderror shadow-sm" 
                                           value="{{ old('date_retour_prevue', now()->addDays(15)->format('Y-m-d')) }}" 
                                           required>
                                    <div class="form-text mt-2">
                                        <i class="fas fa-clock me-1"></i> Durée standard : 15 jours.
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-7">
                                <label class="form-section-title"><i class="fas fa-sticky-note me-2"></i>Observations</label>
                                <textarea name="notes" id="notes" rows="3" 
                                          class="form-control shadow-sm" 
                                          placeholder="Ex: État du livre au départ, demande spéciale...">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-5">
                            <a href="{{ route('emprunts.index') }}" class="btn btn-link text-muted text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i> Abandonner
                            </a>
                            <button type="submit" class="btn btn-lg px-5 shadow" style="background-color: #5D4037; color: #D4AF37; font-weight: bold;">
                                <i class="fas fa-check-circle me-2"></i> Valider l'Emprunt
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dateInput = document.getElementById('date_retour_prevue');
        const userSelect = document.getElementById('user_id');
        const userWarning = document.getElementById('user-warning');
        
        // Sécurité date
        const today = new Date().toISOString().split('T')[0];
        dateInput.setAttribute('min', today);

        // Feedback utilisateur dynamique
        userSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const activeLoans = parseInt(selectedOption.getAttribute('data-emprunts') || 0);
            
            if(activeLoans >= 3) { // Seuil d'alerte arbitraire
                userWarning.classList.remove('d-none');
            } else {
                userWarning.classList.add('d-none');
            }
        });
    });
</script>
@endpush