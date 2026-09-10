@extends('layouts.dashboard')

@section('title', 'Nouvelle Réservation')

@push('styles')
<style>
    /* Arrière-plan Papier Crème */
    .reservation-page {
        background-color: #F4F7FC;
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
        background-color: #123A7A !important; /* Bois Sombre */
        color: #F4F7FC !important;
        border-bottom: 3px solid #2563EB !important; /* Bordure Or */
        padding: 1.5rem;
    }

    .icon-box-gold {
        background-color: rgba(37, 99, 235, 0.2);
        padding: 10px;
        border-radius: 8px;
        color: #2563EB;
    }

    /* Labels et Inputs */
    .form-label {
        color: #123A7A;
        font-weight: 600;
    }

    .form-control:focus, .form-select:focus {
        border-color: #2563EB;
        box-shadow: 0 0 0 0.25rem rgba(37, 99, 235, 0.25);
    }

    /* Bouton Or (Gold) */
    .btn-gold {
        background-color: #2563EB !important;
        border-color: #2563EB !important;
        color: #123A7A !important;
        font-weight: bold;
        transition: all 0.3s ease;
    }

    .btn-gold:hover {
        background-color: #123A7A !important;
        border-color: #123A7A !important;
        color: #F4F7FC !important;
        transform: translateY(-2px);
    }

    /* Note d'information personnalisée */
    .info-box-custom {
        background-color: #fcf8e3;
        border-left: 5px solid #2563EB;
        color: #856404;
    }

    /* Fil d'Ariane (Breadcrumb) */
    .breadcrumb-item a {
        color: #123A7A;
        text-decoration: none;
    }
    .breadcrumb-item.active {
        color: #2563EB;
    }
</style>
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
<script>
    // Filtrage au clavier sur les listes déroulantes, sans dépendance externe :
    // on saisit quelques lettres et les options non correspondantes sont masquées.
    document.querySelectorAll('#livre_id, #user_id').forEach(function (select) {
        const champ = document.createElement('input');
        champ.type = 'search';
        champ.className = 'form-control form-control-sm mb-2';
        champ.placeholder = select.id === 'livre_id' ? 'Filtrer les ouvrages…' : 'Filtrer les usagers…';
        select.parentNode.insertBefore(champ, select);

        const options = Array.from(select.options).map(o => ({ element: o, texte: o.text.toLowerCase() }));

        champ.addEventListener('input', function () {
            const terme = this.value.trim().toLowerCase();
            options.forEach(({ element, texte }) => {
                element.hidden = terme !== '' && element.value !== '' && !texte.includes(terme);
            });
        });
    });
</script>
@endpush