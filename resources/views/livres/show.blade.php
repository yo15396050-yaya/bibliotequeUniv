@extends('layouts.dashboard')

@section('title', 'Détails du Livre : ' . $livre->titre)

@section('content')
    <style>
        /* Thème Global */
        .detail-wrapper {
            background-color: #FAF3E0;
            min-height: 100vh;
            padding: 2rem 0;
        }

        /* Card Styling */
        .card {
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .main-header {
            background-color: #5D4037 !important;
            color: #D4AF37 !important;
            border-bottom: 3px solid #D4AF37;
        }

        .section-header {
            background-color: #f8f1e0 !important;
            color: #5D4037;
            font-weight: bold;
            border-bottom: 1px solid #e0d5ba;
        }

        /* Typographie */
        .text-wood {
            color: #5D4037;
        }

        .text-gold {
            color: #D4AF37;
        }

        /* Badges & Progress */
        .bg-gold {
            background-color: #D4AF37;
            color: #5D4037;
        }

        .progress {
            background-color: #e0d5ba;
            height: 12px !important;
            border-radius: 10px;
        }

        .progress-bar {
            background-color: #5D4037;
            border-radius: 10px;
        }

        /* Image */
        .book-cover-detail {
            border: 4px solid #fff;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease;
        }

        .book-cover-detail:hover {
            transform: scale(1.02);
        }

        /* Buttons */
        .btn-gold {
            background-color: #D4AF37;
            color: #5D4037;
            border: none;
            font-weight: 600;
        }

        .btn-gold:hover {
            background-color: #5D4037;
            color: #FAF3E0;
        }

        /* Suggestions */
        .suggestion-card {
            border: 1px solid #e0d5ba;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s;
            background-color: #fdfbf7;
        }

        .suggestion-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(93, 64, 55, 0.1);
        }

        .suggestion-img {
            height: 150px;
            object-fit: cover;
        }
    </style>

    <div class="detail-wrapper">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-11 col-lg-10">
                    <div class="card overflow-hidden">
                        <div class="card-header main-header p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-book-open me-2"></i>Archive de la Bibliothèque
                                </h5>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('livres.index') }}" class="btn btn-sm btn-outline-light">
                                        <i class="fas fa-list me-1"></i>Liste
                                    </a>
                                    <a href="{{ route('livres.edit', $livre->id) }}" class="btn btn-sm btn-gold">
                                        <i class="fas fa-edit me-1"></i>Modifier
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                        data-bs-target="#deleteModal">
                                        <i class="fas fa-trash me-1"></i>Supprimer
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card-body bg-white p-4">
                            <div class="row">
                                <div class="col-md-4 border-end">
                                    <div class="text-center mb-4">
                                        @if($livre->image_couverture)
                                            <img src="{{ asset('storage/' . $livre->image_couverture) }}"
                                                alt="{{ $livre->titre }}" class="img-fluid rounded book-cover-detail">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center rounded"
                                                style="height: 350px; border: 2px dashed #D4AF37;">
                                                <div class="text-muted"><i class="fas fa-image fa-3x mb-2"></i><br>Aucune
                                                    couverture</div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="card mb-3 border">
                                        <div class="card-header section-header small">DISPONIBILITÉ</div>
                                        <div class="card-body py-2">
                                            <div class="d-flex justify-content-between mb-1">
                                                <small class="text-muted">Total:</small>
                                                <span class="badge bg-dark">{{ $livre->exemplaires_totaux }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <small class="text-muted">En rayon:</small>
                                                <span class="badge bg-gold">{{ $livre->exemplaires_disponibles }}</span>
                                            </div>

                                            @php
                                                $percentage = $livre->exemplaires_totaux > 0
                                                    ? ($livre->exemplaires_disponibles / $livre->exemplaires_totaux) * 100
                                                    : 0;
                                            @endphp
                                            <div class="progress mb-1">
                                                <div class="progress-bar" role="progressbar"
                                                    style="width: {{ $percentage }}%"></div>
                                            </div>
                                            <div class="text-center">
                                                <small class="text-wood fw-bold">{{ number_format($percentage, 0) }}%
                                                    disponible</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-8 ps-md-4">
                                    <h2 class="text-wood fw-bold mb-1">{{ $livre->titre }}</h2>
                                    <h5 class="text-muted mb-4">par <span
                                            class="text-gold fw-bold">{{ $livre->auteur }}</span></h5>

                                    <div class="mb-4">
                                        <h6 class="text-wood border-bottom pb-2"><i
                                                class="fas fa-quote-left me-2 text-gold"></i>Synopsis</h6>
                                        <p class="text-secondary lh-lg">
                                            {{ $livre->resume ?? 'Aucun résumé disponible pour cet ouvrage.' }}
                                        </p>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-sm-6">
                                            <div class="card bg-light-subtle h-100 border-0 shadow-none"
                                                style="background-color: #fdfbf7;">
                                                <div class="card-body">
                                                    <h6 class="fw-bold text-wood mb-3"><i
                                                            class="fas fa-print me-2 text-gold"></i>Édition</h6>
                                                    <ul class="list-unstyled small">
                                                        <li class="mb-2"><strong>ISBN :</strong> {{ $livre->isbn }}</li>
                                                        <li class="mb-2"><strong>Éditeur :</strong> {{ $livre->editeur }}
                                                        </li>
                                                        <li class="mb-2"><strong>Année :</strong>
                                                            {{ $livre->annee_publication }}</li>
                                                        <li><strong>Langue :</strong> <span
                                                                class="badge border text-dark">{{ $livre->langue }}</span>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="card bg-light-subtle h-100 border-0 shadow-none"
                                                style="background-color: #fdfbf7;">
                                                <div class="card-body">
                                                    <h6 class="fw-bold text-wood mb-3"><i
                                                            class="fas fa-archive me-2 text-gold"></i>Classement</h6>
                                                    <ul class="list-unstyled small">
                                                        <li class="mb-2"><strong>Catégorie :</strong> <span
                                                                class="text-gold fw-bold">{{ $livre->categorie }}</span>
                                                        </li>
                                                        <li class="mb-2"><strong>Rayon :</strong> <span
                                                                class="badge bg-dark"><i
                                                                    class="fas fa-map-marker-alt me-1 text-gold"></i>{{ $livre->emplacement_rayon }}</span>
                                                        </li>
                                                        <li class="mb-2"><strong>Pages :</strong> {{ $livre->nombre_pages }}
                                                        </li>
                                                        <li><strong>Enregistré le :</strong>
                                                            {{ $livre->created_at->format('d/m/Y') }}</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Section Historique --}}
                                    <div class="mt-5">
                                        <h6 class="text-wood border-bottom pb-2 mb-3">
                                            <i class="fas fa-history me-2 text-gold"></i>Historique des Emprunts
                                        </h6>
                                        <div class="table-responsive rounded border shadow-sm">
                                            <table class="table table-hover align-middle mb-0 small">
                                                <thead class="bg-light text-wood">
                                                    <tr>
                                                        <th class="ps-3 text-uppercase" style="font-size: 0.7rem;">Étudiant
                                                        </th>
                                                        <th class="text-uppercase" style="font-size: 0.7rem;">Sortie</th>
                                                        <th class="text-uppercase" style="font-size: 0.7rem;">Retour prévu
                                                        </th>
                                                        <th class="text-uppercase" style="font-size: 0.7rem;">Statut</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($livre->emprunts as $emprunt)
                                                        <tr>
                                                            <td class="ps-3">
                                                                <div class="fw-bold">{{ $emprunt->user->name }}</div>
                                                                <div class="text-muted" style="font-size: 0.65rem;">
                                                                    {{ $emprunt->user->email }}</div>
                                                            </td>
                                                            <td>{{ $emprunt->date_emprunt ? $emprunt->date_emprunt->format('d/m/Y') : '-' }}
                                                            </td>
                                                            <td>{{ $emprunt->date_retour_prevue ? $emprunt->date_retour_prevue->format('d/m/Y') : '-' }}
                                                            </td>
                                                            <td>
                                                                @if($emprunt->statut == 'rendu')
                                                                    <span
                                                                        class="badge bg-success-soft text-success border border-success-subtle px-2 py-1">Rendu</span>
                                                                @elseif($emprunt->statut == 'en retard')
                                                                    <span
                                                                        class="badge bg-danger-soft text-danger border border-danger-subtle px-2 py-1">En
                                                                        retard</span>
                                                                @else
                                                                    <span
                                                                        class="badge bg-gold-soft text-brown border border-gold-subtle px-2 py-1">En
                                                                        cours</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="4" class="text-center py-4 text-muted fst-italic">
                                                                <i class="fas fa-info-circle me-1"></i>Aucun historique
                                                                d'emprunt disponible.
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="mt-5 pt-3 border-top d-flex justify-content-between align-items-center">
                                        <a href="{{ route('livres.index') }}"
                                            class="btn btn-link text-wood p-0 text-decoration-none">
                                            <i class="fas fa-long-arrow-alt-left me-2"></i>Retour à la collection
                                        </a>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('livres.create') }}" class="btn btn-outline-dark">
                                                <i class="fas fa-plus me-1"></i>Nouvel ajout
                                            </a>
                                            <a href="{{ route('livres.edit', $livre->id) }}" class="btn btn-gold">
                                                <i class="fas fa-pencil-alt me-1"></i>Éditer la fiche
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section Suggestions --}}
        @if($similaires->count() > 0)
            <div class="container-fluid mt-5 mb-5 pb-5">
                <div class="row justify-content-center">
                    <div class="col-md-11 col-lg-10">
                        <h5 class="text-wood fw-bold mb-4">
                            <i class="fas fa-magic me-2 text-gold"></i>Ouvrages similaires dans le rayon <span
                                class="text-gold">{{ $livre->categorie }}</span>
                        </h5>
                        <div class="row g-4">
                            @foreach($similaires as $item)
                                <div class="col-sm-6 col-md-3">
                                    <a href="{{ route('livres.show', $item->id) }}" class="text-decoration-none">
                                        <div class="card h-100 suggestion-card border-0 shadow-sm">
                                            <div class="position-relative">
                                                @if($item->image_couverture)
                                                    <img src="{{ asset('storage/' . $item->image_couverture) }}"
                                                        class="card-img-top suggestion-img" alt="{{ $item->titre }}">
                                                @else
                                                    <div
                                                        class="suggestion-img bg-light d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-book fa-2x text-muted"></i>
                                                    </div>
                                                @endif
                                                <span class="position-absolute top-0 end-0 m-2 badge bg-gold text-brown small"
                                                    style="font-size: 0.6rem;">{{ $item->categorie }}</span>
                                            </div>
                                            <div class="card-body p-2">
                                                <h6 class="card-title text-wood small fw-bold mb-1 text-truncate">{{ $item->titre }}
                                                </h6>
                                                <p class="card-text text-muted fst-italic" style="font-size: 0.7rem;">
                                                    {{ $item->auteur }}</p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle text-gold me-2"></i>Confirmation</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <p class="mb-1 text-muted small">Vous êtes sur le point de retirer de la collection :</p>
                    <h5 class="text-wood fw-bold mb-3">{{ $livre->titre }}</h5>
                    <div class="alert alert-warning border-0 small">
                        <i class="fas fa-info-circle me-1"></i> Cette action supprimera également les données de suivi
                        associées.
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form action="{{ route('livres.destroy', $livre->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger px-4 text-white">Confirmer la suppression</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Animation subtile de la barre de progression
            const progressBar = document.querySelector('.progress-bar');
            if (progressBar) {
                const finalWidth = progressBar.style.width;
                progressBar.style.width = '0%';
                setTimeout(() => {
                    progressBar.style.transition = 'width 1.2s cubic-bezier(0.4, 0, 0.2, 1)';
                    progressBar.style.width = finalWidth;
                }, 200);
            }
        });
    </script>
@endsection