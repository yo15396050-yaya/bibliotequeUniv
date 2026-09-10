@extends('layouts.dashboard')

@section('title', 'QR Code - ' . $livre->titre)

@section('content')
<div class="container-fluid py-4" style="background-color: #F4F7FC; min-height: 100vh;">
    <div class="row justify-content-center">
        <div class="col-md-6">
            {{-- Carte du QR Code --}}
            <div class="card shadow-lg border-0 rounded-3 overflow-hidden animate__animated animate__fadeInUp">
                <div class="card-header text-white text-center py-3" style="background-color: #123A7A; border-bottom: 4px solid #2563EB;">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-qrcode me-2" style="color: #2563EB;"></i>Génération d'Étiquette
                    </h5>
                </div>
                
                <div class="card-body bg-white p-5 text-center" id="printable-area">
                    {{-- Branding discret sur l'étiquette --}}
                    <div class="mb-4 text-brown opacity-75">
                        <small class="text-uppercase tracking-wider fw-bold">Bibliothèque Universitaire</small>
                    </div>

                    {{-- Le QR Code --}}
                    <div class="qr-container p-4 d-inline-block bg-white border border-2 rounded mb-4" style="border-color: #2563EB !important;">
                        <img src="{{ $qrCode }}" alt="QR Code du livre" style="width: 250px; height: 250px;">
                    </div>

                    {{-- Infos du livre --}}
                    <div class="book-info-qr">
                        <h4 class="text-brown fw-bold mb-1">{{ $livre->titre }}</h4>
                        <p class="text-muted mb-3">par <span class="text-gold fw-bold">{{ $livre->auteur }}</span></p>
                        
                        <div class="d-flex justify-content-center gap-3 mb-4">
                            <span class="badge bg-paper border text-brown px-3 py-2">
                                <i class="fas fa-barcode me-1"></i> {{ $livre->isbn }}
                            </span>
                            <span class="badge bg-dark text-white px-3 py-2">
                                <i class="fas fa-map-marker-alt me-1 text-gold"></i> {{ $livre->emplacement_rayon }}
                            </span>
                        </div>
                    </div>

                    <div class="border-top pt-4 no-print">
                        <p class="text-muted small mb-3">
                            Scannez ce code pour accéder instantanément à la fiche numérique, gérer les emprunts ou lire l'ouvrage en ligne.
                        </p>
                        
                        <div class="d-flex justify-content-center gap-2">
                            <button onclick="window.print()" class="btn btn-gold px-4 shadow-sm">
                                <i class="fas fa-print me-2"></i> Imprimer l'étiquette
                            </button>
                            <a href="{{ route('livres.show', $livre->id) }}" class="btn btn-outline-brown px-4">
                                <i class="fas fa-arrow-left me-2"></i> Retour
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer bg-light text-center py-3 no-print">
                    <small class="text-muted">Généré le {{ date('d/m/Y à H:i') }}</small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --brown: #123A7A;
        --gold: #2563EB;
        --paper: #F4F7FC;
    }

    .text-brown { color: var(--brown); }
    .text-gold { color: var(--gold); }
    .bg-brown { background-color: var(--brown); }
    .bg-paper { background-color: var(--paper); }

    .btn-gold {
        background-color: var(--gold);
        color: white;
        border: none;
        font-weight: 600;
        transition: all 0.3s;
    }
    .btn-gold:hover {
        background-color: var(--brown);
        color: var(--gold);
        transform: translateY(-2px);
    }

    .btn-outline-brown {
        color: var(--brown);
        border: 2px solid var(--brown);
        font-weight: 600;
        transition: all 0.3s;
    }
    .btn-outline-brown:hover {
        background-color: var(--brown);
        color: white;
    }

    .qr-container {
        box-shadow: 0 10px 30px rgba(37, 99, 235, 0.1);
        transition: transform 0.3s ease;
    }
    .qr-container:hover {
        transform: scale(1.05);
    }

    @media print {
        .no-print { display: none !important; }
        body { background: white !important; }
        .container-fluid { padding: 0 !important; background: white !important; }
        .card { box-shadow: none !important; border: none !important; }
        .card-header { background-color: white !important; color: black !important; border-bottom: 2px solid black !important; }
        .qr-container { border: 1px solid #ccc !important; box-shadow: none !important; }
        .detail-wrapper { padding: 0 !important; }
    }

    .tracking-wider { letter-spacing: 2px; }
</style>
@endsection
