@extends('layouts.dashboard')

@section('title', 'Ajouter un Nouveau Livre')

@section('content')
    <!-- Conteneur principal avec fond Papier/Crème -->
    <div class="container-fluid py-4" style="background-color: #F4F7FC; min-height: 100vh;">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-lg border-0 rounded-3">

                    <!-- En-tête : Bois Sombre avec bordure inférieure Or -->
                    <div class="card-header text-white py-3"
                        style="background-color: #123A7A; border-bottom: 4px solid #2563EB;">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-book-medical me-2" style="color: #2563EB;"></i>Ajouter un Nouveau Livre
                        </h5>
                    </div>

                    <div class="card-body p-4 bg-white">
                        <form action="{{ route('livres.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

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
                                                id="isbn" name="isbn" value="{{ old('isbn') }}"
                                                placeholder="Ex: 978-2-1234-5678-9" required>
                                            @error('isbn')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted fst-italic">Code unique d'identification du
                                                livre</small>
                                        </div>

                                        <div class="mb-3">
                                            <label for="titre" class="form-label fw-bold text-brown">Titre *</label>
                                            <input type="text"
                                                class="form-control custom-focus @error('titre') is-invalid @enderror"
                                                id="titre" name="titre" value="{{ old('titre') }}"
                                                placeholder="Titre complet du livre" required>
                                            @error('titre')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="auteur" class="form-label fw-bold text-brown">Auteur(s) *</label>
                                            <input type="text"
                                                class="form-control custom-focus @error('auteur') is-invalid @enderror"
                                                id="auteur" name="auteur" value="{{ old('auteur') }}"
                                                placeholder="Nom complet de l'auteur" required>
                                            @error('auteur')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="editeur" class="form-label fw-bold text-brown">Éditeur *</label>
                                            <input type="text"
                                                class="form-control custom-focus @error('editeur') is-invalid @enderror"
                                                id="editeur" name="editeur" value="{{ old('editeur') }}"
                                                placeholder="Maison d'édition" required>
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
                                                    value="{{ old('annee_publication') }}" min="1900" max="{{ date('Y') }}"
                                                    required>
                                                @error('annee_publication')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="nombre_pages" class="form-label fw-bold text-brown">Nombre de
                                                    pages *</label>
                                                <input type="number"
                                                    class="form-control custom-focus @error('nombre_pages') is-invalid @enderror"
                                                    id="nombre_pages" name="nombre_pages" value="{{ old('nombre_pages') }}"
                                                    min="1" required>
                                                @error('nombre_pages')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="langue" class="form-label fw-bold text-brown">Langue *</label>
                                            <select class="form-select custom-focus @error('langue') is-invalid @enderror"
                                                id="langue" name="langue" required>
                                                <option value="">Sélectionner la langue</option>
                                                <option value="Français" {{ old('langue') == 'Français' ? 'selected' : '' }}>
                                                    Français</option>
                                                <option value="Anglais" {{ old('langue') == 'Anglais' ? 'selected' : '' }}>
                                                    Anglais</option>
                                                <option value="Espagnol" {{ old('langue') == 'Espagnol' ? 'selected' : '' }}>
                                                    Espagnol</option>
                                                <option value="Allemand" {{ old('langue') == 'Allemand' ? 'selected' : '' }}>
                                                    Allemand</option>
                                                <option value="Autre" {{ old('langue') == 'Autre' ? 'selected' : '' }}>Autre
                                                </option>
                                            </select>
                                            @error('langue')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Colonne de droite -->
                                <div class="col-md-6">
                                    <!-- Catégorie et classification -->
                                    <div class="mb-4">
                                        <h6 class="section-title pb-2 mb-3">
                                            <i class="fas fa-tags me-2"></i>Classification
                                        </h6>

                                        <div class="mb-3">
                                            <label for="categorie" class="form-label fw-bold text-brown">Catégorie *</label>
                                            <select
                                                class="form-select custom-focus @error('categorie') is-invalid @enderror"
                                                id="categorie" name="categorie" required>
                                                <option value="">Sélectionner une catégorie</option>
                                                <option value="Informatique" {{ old('categorie') == 'Informatique' ? 'selected' : '' }}>Informatique</option>
                                                <option value="Littérature" {{ old('categorie') == 'Littérature' ? 'selected' : '' }}>Littérature</option>
                                                <option value="Science" {{ old('categorie') == 'Science' ? 'selected' : '' }}>
                                                    Science</option>
                                                <option value="Histoire" {{ old('categorie') == 'Histoire' ? 'selected' : '' }}>Histoire</option>
                                                <option value="Philosophie" {{ old('categorie') == 'Philosophie' ? 'selected' : '' }}>Philosophie</option>
                                                <option value="Économie" {{ old('categorie') == 'Économie' ? 'selected' : '' }}>Économie</option>
                                                <option value="Droit" {{ old('categorie') == 'Droit' ? 'selected' : '' }}>
                                                    Droit</option>
                                                <option value="Médecine" {{ old('categorie') == 'Médecine' ? 'selected' : '' }}>Médecine</option>
                                                <option value="Art" {{ old('categorie') == 'Art' ? 'selected' : '' }}>Art
                                                </option>
                                                <option value="Langues" {{ old('categorie') == 'Langues' ? 'selected' : '' }}>
                                                    Langues</option>
                                                <option value="Autre" {{ old('categorie') == 'Autre' ? 'selected' : '' }}>
                                                    Autre</option>
                                            </select>
                                            @error('categorie')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="emplacement_rayon" class="form-label fw-bold text-brown">Emplacement
                                                *</label>
                                            <input type="text"
                                                class="form-control custom-focus @error('emplacement_rayon') is-invalid @enderror"
                                                id="emplacement_rayon" name="emplacement_rayon"
                                                value="{{ old('emplacement_rayon') }}" placeholder="Ex: A1-23, B5-12"
                                                required>
                                            @error('emplacement_rayon')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted fst-italic">Rayon et position dans la
                                                bibliothèque</small>
                                        </div>
                                    </div>

                                    <!-- Stock et exemplaires -->
                                    <div class="mb-4">
                                        <h6 class="section-title pb-2 mb-3">
                                            <i class="fas fa-boxes me-2"></i>Gestion du stock
                                        </h6>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="exemplaires_totaux"
                                                    class="form-label fw-bold text-brown">Exemplaires totaux *</label>
                                                <input type="number"
                                                    class="form-control custom-focus @error('exemplaires_totaux') is-invalid @enderror"
                                                    id="exemplaires_totaux" name="exemplaires_totaux"
                                                    value="{{ old('exemplaires_totaux', 1) }}" min="1" required>
                                                @error('exemplaires_totaux')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="text-muted fst-italic">Nombre total d'exemplaires</small>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="exemplaires_disponibles"
                                                    class="form-label fw-bold text-brown">Exemplaires disponibles</label>
                                                <input type="number" class="form-control bg-light text-muted"
                                                    id="exemplaires_disponibles" name="exemplaires_disponibles"
                                                    value="{{ old('exemplaires_disponibles') }}" min="0" readonly>
                                                @error('exemplaires_disponibles')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="text-muted fst-italic">Calculé automatiquement</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Image de couverture -->
                                    <div class="mb-4">
                                        <h6 class="section-title pb-2 mb-3">
                                            <i class="fas fa-image me-2"></i>Image de couverture
                                        </h6>

                                        <div class="mb-3">
                                            <label for="image_couverture"
                                                class="form-label fw-bold text-brown">Image</label>
                                            <input type="file"
                                                class="form-control custom-focus @error('image_couverture') is-invalid @enderror"
                                                id="image_couverture" name="image_couverture"
                                                accept="image/jpeg,image/png,image/jpg,image/gif">
                                            @error('image_couverture')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted fst-italic">Formats acceptés: JPEG, PNG, JPG, GIF (max:
                                                2MB)</small>
                                        </div>

                                        <div class="text-center p-3 border rounded bg-light mt-2">
                                            <img id="image_preview"
                                                src="https://via.placeholder.com/200x300/e0e0e0/5D4037?text=Aucun+Visuel"
                                                alt="Aperçu de la couverture" class="img-thumbnail d-none shadow-sm"
                                                style="max-width: 150px; max-height: 220px; border-color: #2563EB;">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Résumé -->
                            <div class="mb-4">
                                <h6 class="section-title pb-2 mb-3">
                                    <i class="fas fa-align-left me-2"></i>Résumé
                                </h6>

                                <div class="mb-3">
                                    <label for="resume" class="form-label fw-bold text-brown">Résumé du livre</label>
                                    <textarea class="form-control custom-focus @error('resume') is-invalid @enderror"
                                        id="resume" name="resume" rows="4"
                                        placeholder="Brève description du contenu du livre...">{{ old('resume') }}</textarea>
                                    @error('resume')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted fst-italic float-end" id="charCount">Maximum 2000
                                        caractères</small>
                                </div>
                            </div>

                            <!-- Boutons d'action -->
                            <div class="d-flex justify-content-between align-items-center mt-5 pt-3 border-top">
                                <div>
                                    <a href="{{ route('livres.index') }}" class="btn btn-outline-secondary px-4">
                                        <i class="fas fa-arrow-left me-2"></i>Retour à la liste
                                    </a>
                                </div>
                                <div>
                                    <button type="reset" class="btn btn-link text-danger text-decoration-none me-3 fw-bold">
                                        <i class="fas fa-redo me-1"></i>Réinitialiser
                                    </button>
                                    <button type="submit" class="btn btn-gold btn-lg shadow-sm" id="submitBtn">
                                        <i class="fas fa-save me-2"></i>
                                        <span id="btnText">Enregistrer le livre</span>
                                        <span id="btnLoading" class="d-none">
                                            <span class="spinner-border spinner-border-sm me-2"></span>
                                            Enregistrement...
                                        </span>
                                    </button>
                                </div>
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
        /* VARIABLES DE COULEUR */
        :root {
            --bois-sombre: #123A7A;
            --or: #2563EB;
            --papier: #F4F7FC;
        }

        /* TEXTES */
        .text-brown {
            color: var(--bois-sombre) !important;
        }

        /* TITRES DE SECTIONS */
        .section-title {
            color: var(--bois-sombre);
            border-bottom: 2px solid var(--or) !important;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }

        /* CHAMPS DE FORMULAIRE - FOCUS DORÉ */
        .custom-focus:focus {
            border-color: var(--or);
            box-shadow: 0 0 0 0.25rem rgba(37, 99, 235, 0.25);
        }

        /* BOUTON PRINCIPAL (OR -> BOIS SOMBRE) */
        .btn-gold {
            background-color: var(--or);
            color: #fff;
            border: none;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .btn-gold:hover {
            background-color: var(--bois-sombre);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(18, 58, 122, 0.3);
        }

        /* INDICATEURS REQUIS */
        .form-label.fw-bold::after {
            content: " *";
            color: #dc3545;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Mettre à jour les exemplaires disponibles
            const exemplairesTotaux = document.getElementById('exemplaires_totaux');
            const exemplairesDisponibles = document.getElementById('exemplaires_disponibles');

            if (exemplairesTotaux && exemplairesDisponibles) {
                exemplairesTotaux.addEventListener('input', function () {
                    exemplairesDisponibles.value = this.value;
                });
                exemplairesDisponibles.value = exemplairesTotaux.value;
            }

            // Prévisualisation de l'image
            const imageInput = document.getElementById('image_couverture');
            const imagePreview = document.getElementById('image_preview');

            if (imageInput && imagePreview) {
                imageInput.addEventListener('change', function (e) {
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            imagePreview.src = e.target.result;
                            imagePreview.classList.remove('d-none');
                        }
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            }

            // Gestion du bouton submit (Loading)
            const form = document.querySelector('form');
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const btnLoading = document.getElementById('btnLoading');

            if (form) {
                form.addEventListener('submit', function () {
                    submitBtn.disabled = true;
                    btnText.classList.add('d-none');
                    btnLoading.classList.remove('d-none');
                });
            }

            // Auto-focus sur le champ ISBN
            const isbnField = document.getElementById('isbn');
            if (isbnField) isbnField.focus();

            // Validation Année
            const anneeField = document.getElementById('annee_publication');
            if (anneeField) {
                anneeField.addEventListener('blur', function () {
                    const currentYear = new Date().getFullYear();
                    const inputYear = parseInt(this.value);
                    if (inputYear < 1900) {
                        this.setCustomValidity('L\'année doit être supérieure à 1900');
                    } else if (inputYear > currentYear) {
                        this.setCustomValidity('L\'année ne peut pas être dans le futur');
                    } else {
                        this.setCustomValidity('');
                    }
                });
            }

            // Compteur caractères
            const resumeField = document.getElementById('resume');
            const charCount = document.getElementById('charCount');
            if (resumeField && charCount) {
                resumeField.addEventListener('input', function () {
                    const len = this.value.length;
                    charCount.textContent = len + '/2000 caractères';
                    if (len > 2000) charCount.classList.add('text-danger');
                    else charCount.classList.remove('text-danger');
                });
            }
        });
    </script>
@endpush