@extends('layouts.dashboard')

@section('title', 'Suivi de l\'Emprunt #' . $emprunt->id)

@section('content')
<style>
    /* Fond de page conforme à la charte */
    body { background-color: #FAF3E0; }

    .detail-card { 
        border-radius: 15px; 
        border: none; 
        box-shadow: 0 5px 15px rgba(93, 64, 55, 0.1); 
    }

    .status-banner {
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        /* Bordure légère couleur Or pour le rappel */
        border: 1px solid rgba(212, 175, 55, 0.3) !important;
    }

    .info-label { 
        color: #8d6e63; /* Brun clair */
        font-weight: 600; 
        font-size: 0.85rem; 
        text-transform: uppercase; 
    }

    .info-value { 
        color: #5D4037; /* Ton bois sombre */
        font-weight: 700; 
        font-size: 1.05rem; 
    }

    .section-divider { 
        border-left: 4px solid #D4AF37; /* Ligne Or */
        padding-left: 15px; 
        margin-bottom: 20px; 
        color: #5D4037;
    }

    .text-gold { color: #D4AF37 !important; }
    .bg-wood { background-color: #5D4037 !important; color: #FAF3E0; }
</style>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-xl-10">
            
            @php
                $statusColor = $emprunt->statut == 'retourné' ? 'success' : ($emprunt->statut == 'en retard' ? 'danger' : 'warning');
                $statusIcon = $emprunt->statut == 'retourné' ? 'fa-check-double' : ($emprunt->statut == 'en retard' ? 'fa-clock' : 'fa-hourglass-half');
            @endphp
            
            {{-- Bannière de statut --}}
            <div class="status-banner bg-{{ $statusColor }} bg-opacity-10 text-{{ $statusColor }}">
                <div>
                    <i class="fas {{ $statusIcon }} fa-lg me-2"></i>
                    <span class="fw-bold">Statut actuel : {{ strtoupper($emprunt->statut) }}</span>
                </div>
                <div class="btn-group">
                    <a href="{{ route('emprunts.fiche', $emprunt) }}" class="btn btn-sm btn-outline-{{ $statusColor }} shadow-sm">
                        <i class="fas fa-file-pdf me-1"></i> Générer le reçu
                    </a>
                    <a href="{{ route('emprunts.index') }}" class="btn btn-sm bg-wood shadow-sm ms-2">
                        <i class="fas fa-list me-1"></i> Voir la liste
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-7">
                    <div class="card detail-card mb-4">
                        <div class="card-body">
                            <div class="section-divider">
                                <h5 class="mb-0 fw-bold">Détails de l'Étudiant & de l'Ouvrage</h5>
                            </div>
                            
                            <div class="row g-4">
                                <div class="col-sm-6">
                                    <div class="info-label">Étudiant</div>
                                    {{-- Changé text-primary en text-gold --}}
                                    <div class="info-value text-gold">{{ $emprunt->user->name }}</div>
                                    <small class="text-muted"><i class="fas fa-id-card me-1"></i> {{ $emprunt->user->matricule }}</small><br>
                                    <small class="text-muted"><i class="fas fa-envelope me-1"></i> {{ $emprunt->user->email }}</small>
                                </div>
                                <div class="col-sm-6">
                                    <div class="info-label">Livre emprunté</div>
                                    <div class="info-value">{{ $emprunt->livre->titre }}</div>
                                    <small class="text-muted">Auteur : {{ $emprunt->livre->auteur }}</small><br>
                                    {{-- Badge personnalisé en bois --}}
                                    <small class="badge bg-wood mt-1">ISBN: {{ $emprunt->livre->isbn }}</small>
                                </div>
                            </div>

                            @if($emprunt->notes)
                            <div class="mt-4 p-3 rounded bg-light border-start border-3 border-warning">
                                <div class="info-label mb-1">Notes de l'agent</div>
                                <p class="mb-0 small fst-italic">"{{ $emprunt->notes }}"</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="card detail-card border-top border-4 border-{{ $statusColor }}">
                        <div class="card-body">
                            <div class="section-divider">
                                <h5 class="mb-0 fw-bold">Chronologie</h5>
                            </div>

                            <div class="d-flex justify-content-between mb-3 pb-2 border-bottom">
                                <span class="text-muted small">Date de sortie :</span>
                                <span class="fw-bold" style="color: #5D4037;">{{ $emprunt->date_emprunt?->format('d M Y') }}</span>
                            </div>

                            <div class="d-flex justify-content-between mb-3 pb-2 border-bottom">
                                <span class="text-muted small text-danger">Retour attendu :</span>
                                <span class="fw-bold text-danger">{{ $emprunt->date_retour_prevue?->format('d M Y') }}</span>
                            </div>

                            <div class="d-flex justify-content-between mb-3 pb-2 border-bottom">
                                <span class="text-muted small">Retour effectif :</span>
                                <span class="fw-bold {{ $emprunt->date_retour_effective ? 'text-success' : 'text-muted' }}">
                                    {{ $emprunt->date_retour_effective ? $emprunt->date_retour_effective->format('d M Y') : 'En attente...' }}
                                </span>
                            </div>

                            @if($emprunt->amende > 0)
                            <div class="alert alert-danger d-flex align-items-center mb-0 mt-3 shadow-sm border-0">
                                <i class="fas fa-coins fa-2x me-3"></i>
                                <div>
                                    <div class="small">Amende calculée</div>
                                    <div class="h5 mb-0 fw-bold">{{ number_format($emprunt->amende, 0, ',', ' ') }} FCFA</div>
                                </div>
                            </div>
                            @endif
                        </div>
                        
                        @if($emprunt->statut != 'retourné')
                        <div class="card-footer bg-white p-4">
                            <form action="{{ route('emprunts.retour', $emprunt) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100 py-2 fw-bold shadow">
                                    <i class="fas fa-undo-alt me-2"></i> Confirmer la Réception
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection