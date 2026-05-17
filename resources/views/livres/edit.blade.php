@extends('layouts.dashboard')

@section('title', 'Modifier le Livre : ' . $livre->titre)

@section('content')
    <!-- Conteneur principal avec fond Papier/Crème -->
    <div class="container-fluid py-4" style="background-color: #FAF3E0; min-height: 100vh;">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-lg border-0 rounded-3">

                    <!-- En-tête : Bois Sombre avec bordure inférieure Or -->
                    <div class="card-header text-white py-3"
                        style="background-color: #5D4037; border-bottom: 4px solid #D4AF37;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold">
                                <i class="fas fa-edit me-2" style="color: #D4AF37;"></i>Modifier la fiche :
                                {{ $livre->titre }}
                            </h5>
                            <span class="badge bg-gold text-brown px-3 py-2">ID: #{{ $livre->id }}</span>
                        </div>
                    </div>

                    <div class="card-body p-4 bg-white">
                        <form action="{{ route('livres.update', $livre->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <!-- Colonne de gauche -->
                                <div class="col-md-6">
                                    <!-- Informations de base -->
                                    <div class="mb-4">
                                        <h6 class="section-title pb-2 mb-3">
                                            <i class="fas fa-info-circle me-2"></i>Informations de base
                                        </h6>

                                        <div class="mb-3">
                                            <label for="isbn" class="form-label fw-bold text-brown">ISBN *</label>
                                            <input type="text"
                                                class="form-control custom-focus @error('isbn') is-invalid @enderror"
                                                id="isbn" name="isbn" value="{{ old('isbn', $livre->isbn) }}" required>
                                            @error('isbn')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="titre" class="form-label fw-bold text-brown">Titre *</label>
                                            <input type="text"
                                                class="form-control custom-focus @error('titre') is-invalid @enderror"
                                                id="titre" name="titre" value="{{ old('titre', $livre->titre) }}" required>
                                            @error('titre')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="auteur" class="form-label fw-bold text-brown">Auteur(s) *</label>
                                            <input type="text"
                                                class="form-control custom-focus @error('auteur') is-invalid @enderror"
                                                id="auteur" name="auteur" value="{{ old('auteur', $livre->auteur) }}"
                                                required>
                                            @error('auteur')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="editeur" class="form-label fw-bold text-brown">Éditeur *</label>
                                            <input type="text"
                                                class="form-control custom-focus @error('editeur') is-invalid @enderror"
                                                id="editeur" name="editeur" value="{{ old('editeur', $livre->editeur) }}"
                                                required>
                                            @error('editeur')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Informations de publication -->
                                    <div class="mb-4">
                                        <h6 class="section-title pb-2 mb-3">
                                            <i class="fas fa-calendar-alt me-2"></i>Informations de publication
                                        </h6>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="annee_publication" class="form-label fw-bold text-brown">Année
                                                    *</label>
                                                <input type="number"
                                                    class="form-control custom-focus @error('annee_publication') is-invalid @enderror"
                                                    id="annee_publication" name="annee_publication"
                                                    value="{{ old('annee_publication', $livre->annee_publication) }}"
                                                    min="1900" max="{{ date('Y') }}" required>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="nombre_pages" class="form-label fw-bold text-brown">Nombre de
                                                    pages *</label>
                                                <input type="number"
                                                    class="form-control custom-focus @error('nombre_pages') is-invalid @enderror"
                                                    id="nombre_pages" name="nombre_pages"
                                                    value="{{ old('nombre_pages', $livre->nombre_pages) }}" min="1"
                                                    required>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="langue" class="form-label fw-bold text-brown">Langue *</label>
                                            <select class="form-select custom-focus @error('langue') is-invalid @enderror"
                                                name="langue" required>
                                                @foreach(['Français', 'Anglais', 'Espagnol', 'Allemand', 'Autre'] as $lang)
                                                    <option value="{{ $lang }}" {{ old('langue', $livre->langue) == $lang ? 'selected' : '' }}>{{ $lang }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Colonne de droite -->
                                <div class="col-md-6">
                                    <!-- Catégorie et classification -->
                                    <div class="mb-4">
                                        <h6 class="section-title pb-2 mb-3">
                                            <i class="fas fa-tags me-2"></i>Classification & Statut
                                        </h6>

                                        <div class="mb-3">
                                            <label for="categorie" class="form-label fw-bold text-brown">Catégorie *</label>
                                            <select class="form-select custom-focus" name="categorie" required>
                                                @foreach(['Informatique', 'Littérature', 'Science', 'Histoire', 'Philosophie', 'Économie', 'Droit', 'Médecine', 'Art', 'Langues', 'Autre'] as $cat)
                                                    <option value="{{ $cat }}" {{ old('categorie', $livre->categorie) == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="statut" class="form-label fw-bold text-brown">Statut de l'ouvrage
                                                *</label>
                                            <select class="form-select custom-focus" name="statut" required>
                                                <option value="disponible" {{ old('statut', $livre->statut) == 'disponible' ? 'selected' : '' }}>Disponible</option>
                                                <option value="emprunté" {{ old('statut', $livre->statut) == 'emprunté' ? 'selected' : '' }}>Emprunté (Interne seulement)</option>
                                                <option value="réservé" {{ old('statut', $livre->statut) == 'réservé' ? 'selected' : '' }}>Réservé</option>
                                                <option value="perdu" {{ old('statut', $livre->statut) == 'perdu' ? 'selected' : '' }}>Perdu</option>
                                                <option value="en réparation" {{ old('statut', $livre->statut) == 'en réparation' ? 'selected' : '' }}>En réparation</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="emplacement_rayon" class="form-label fw-bold text-brown">Emplacement
                                                *</label>
                                            <input type="text" class="form-control custom-focus" name="emplacement_rayon"
                                                value="{{ old('emplacement_rayon', $livre->emplacement_rayon) }}" required>
                                        </div>
                                    </div>

                                    <!-- Stock -->
                                    <div class="mb-4">
                                        <h6 class="section-title pb-2 mb-3">
                                            <i class="fas fa-boxes me-2"></i>Gestion du stock
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label for="exemplaires_totaux"
                                                    class="form-label fw-bold text-brown">Exemplaires totaux *</label>
                                                <input type="number" class="form-control custom-focus"
                                                    name="exemplaires_totaux"
                                                    value="{{ old('exemplaires_totaux', $livre->exemplaires_totaux) }}"
                                                    min="1" required>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Image de couverture -->
                                    <div class="mb-4">
                                        <h6 class="section-title pb-2 mb-3">
                                            <i class="fas fa-image me-2"></i>Image de couverture
                                        </h6>
                                        <div class="mb-3">
                                            <label for="image_couverture" class="form-label fw-bold text-brown">Nouvelle
                                                image (Optionnel)</label>
                                            <input type="file" class="form-control custom-focus" id="image_couverture"
                                                name="image_couverture" accept="image/*">
                                        </div>

                                        <div class="text-center p-3 border rounded bg-light">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <small class="d-block mb-2 text-muted fst-italic">Actuelle :</small>
                                                    @if($livre->image_couverture)
                                                        <img src="{{ asset('storage/' . $livre->image_couverture) }}"
                                                            class="img-thumbnail" style="max-height: 100px;">
                                                    @else
                                                        <span class="text-muted small">Aucune</span>
                                                    @endif
                                                </div>
                                                <div class="col border-start">
                                                    <small class="d-block mb-2 text-muted fst-italic">Aperçu :</small>
                                                    <img id="image_preview" src="#" class="img-thumbnail d-none"
                                                        style="max-height: 100px;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Résumé -->
                            <div class="mb-4">
                                <h6 class="section-title pb-2 mb-3">
                                    <i class="fas fa-align-left me-2"></i>Résumé
                                </h6>
                                <textarea class="form-control custom-focus" name="resume"
                                    rows="4">{{ old('resume', $livre->resume) }}</textarea>
                            </div>

                            <!-- Boutons -->
                            <div class="d-flex justify-content-between align-items-center mt-5 pt-3 border-top">
                                <a href="{{ route('livres.show', $livre->id) }}" class="btn btn-outline-secondary px-4">
                                    <i class="fas fa-times me-2"></i>Annuler
                                </a>
                                <button type="submit" class="btn btn-gold btn-lg shadow-sm" id="submitBtn">
                                    <i class="fas fa-save me-2"></i>Mettre à jour la fiche
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        :root {
            --bois-sombre: #5D4037;
            --or: #D4AF37;
            --papier: #FAF3E0;
        }

        .text-brown {
            color: var(--bois-sombre) !important;
        }

        .bg-gold {
            background-color: var(--or) !important;
        }

        .section-title {
            color: var(--bois-sombre);
            border-bottom: 2px solid var(--or) !important;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }

        .custom-focus:focus {
            border-color: var(--or);
            box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.25);
        }

        .btn-gold {
            background-color: var(--or);
            color: #fff;
            border: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-gold:hover {
            background-color: var(--bois-sombre);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(93, 64, 55, 0.3);
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const imageInput = document.getElementById('image_couverture');
            const imagePreview = document.getElementById('image_preview');

            if (imageInput && imagePreview) {
                imageInput.addEventListener('change', function () {
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = e => {
                            imagePreview.src = e.target.result;
                            imagePreview.classList.remove('d-none');
                        }
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            }
        });
    </script>
@endpush