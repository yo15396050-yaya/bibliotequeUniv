@extends('layouts.dashboard')

@section('title', 'Catalogue des Livres')
@section('page-title', 'Catalogue des Livres')

@section('content')
<!-- Conteneur principal avec fond Papier/Crème -->
<div class="container-fluid py-4" style="background-color: #FAF3E0; min-height: 100vh;">
    
    <!-- En-tête de page -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 fw-bold text-brown">
            <i class="fas fa-swatchbook me-2 text-gold"></i>Catalogue
        </h2>
        <a href="{{ route('livres.index') }}" class="btn btn-outline-brown shadow-sm">
            <i class="fas fa-cog me-1"></i> Gestion de l'inventaire
        </a>
    </div>

    <!-- Filtres de recherche -->
    <div class="card shadow-sm border-0 mb-5 rounded-3 overflow-hidden">
        <div class="card-header bg-white border-bottom-gold py-3">
            <h6 class="mb-0 fw-bold text-brown">
                <i class="fas fa-search me-2 text-gold"></i>Recherche avancée
            </h6>
        </div>
        <div class="card-body bg-white p-4">
            <form method="GET" action="{{ route('catalogue') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label fw-bold text-brown small text-uppercase">Mots-clés</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-gold"><i class="fas fa-heading"></i></span>
                            <input type="text" class="form-control custom-focus border-start-0" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Titre, auteur, ISBN...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="categorie" class="form-label fw-bold text-brown small text-uppercase">Rayon / Catégorie</label>
                        <select class="form-select custom-focus" id="categorie" name="categorie">
                            <option value="">Toutes les catégories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ request('categorie') == $category ? 'selected' : '' }}>
                                    {{ $category }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="disponible" class="form-label fw-bold text-brown small text-uppercase">Statut</label>
                        <select class="form-select custom-focus" id="disponible" name="disponible">
                            <option value="">Tous les livres</option>
                            <option value="1" {{ request('disponible') == '1' ? 'selected' : '' }}>
                                Disponibles uniquement
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-gold w-100 fw-bold shadow-sm text-white">
                            Filtrer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Résultats -->
    <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
        <div class="card-header text-white py-3 d-flex justify-content-between align-items-center" style="background-color: #5D4037;">
            <h5 class="mb-0">
                @if(request('search') || request('categorie') || request('disponible'))
                    <i class="fas fa-filter me-2 text-gold"></i>Résultats ({{ $livres->total() }})
                @else
                    <i class="fas fa-list me-2 text-gold"></i>Collection complète ({{ $livres->total() }})
                @endif
            </h5>
            <span class="badge bg-gold text-white shadow-sm">{{ $livres->currentPage() }} / {{ $livres->lastPage() }}</span>
        </div>
        
        <div class="card-body bg-light-texture p-4" id="catalogue-results">
            @include('livres.partials._catalogue_grid')
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    :root {
        --bois-sombre: #5D4037;
        --or: #D4AF37;
        --or-dark: #B8860B;
        --papier: #FAF3E0;
    }

    .text-brown { color: var(--bois-sombre) !important; }
    .text-gold { color: var(--or) !important; }
    
    .bg-brown { background-color: var(--bois-sombre) !important; }
    .bg-gold { background-color: var(--or) !important; }
    
    /* Bordure Or sous les headers */
    .border-bottom-gold {
        border-bottom: 2px solid var(--or) !important;
    }

    /* Bouton Or */
    .btn-gold {
        background-color: var(--or);
        border: none;
        transition: all 0.3s ease;
    }
    .btn-gold:hover {
        background-color: var(--or-dark);
        color: white;
        transform: translateY(-1px);
    }

    /* Bouton Outline Brown */
    .btn-outline-brown {
        color: var(--bois-sombre);
        border-color: var(--bois-sombre);
        transition: all 0.3s ease;
    }
    .btn-outline-brown:hover {
        background-color: var(--bois-sombre);
        color: white;
    }

    /* Champs de formulaire */
    .custom-focus:focus {
        border-color: var(--or);
        box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.25);
    }

    /* Carte Livre */
    .book-card {
        border-top: 4px solid var(--or) !important;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .book-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(93, 64, 55, 0.15) !important;
    }

    /* Pagination (Personnalisation Bootstrap) */
    .pagination .page-link {
        color: var(--bois-sombre);
        border: none;
        margin: 0 2px;
        border-radius: 4px;
    }
    .pagination .page-item.active .page-link {
        background-color: var(--bois-sombre);
        border-color: var(--bois-sombre);
        color: var(--or);
    }
    
    /* Texture légère pour le fond des résultats */
    .bg-light-texture {
        background-image: radial-gradient(#5D4037 0.5px, transparent 0.5px);
        background-size: 20px 20px;
        background-color: #fff;
        opacity: 0.95; 
        /* Note: Si l'opacité gêne, on peut l'enlever, c'est pour un effet papier subtil */
    }
    
    .small-badge {
        font-size: 0.75rem;
        background-color: #f8f9fa; 
    }

    .loading-overlay {
        opacity: 0.6;
        pointer-events: none;
        transition: opacity 0.2s ease;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const resultsContainer = document.getElementById('catalogue-results');
        const searchInput = document.getElementById('search');
        const categorieSelect = document.getElementById('categorie');
        const disponibleSelect = document.getElementById('disponible');
        const filterForm = searchInput.closest('form');

        let debounceTimer;

        async function fetchCatalogue(url = null) {
            resultsContainer.classList.add('loading-overlay');
            
            const formData = new FormData(filterForm);
            const params = new URLSearchParams(formData);
            const fetchUrl = url || `${window.location.pathname}?${params.toString()}`;

            try {
                const response = await fetch(fetchUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!response.ok) throw new Error('Erreur de chargement');

                const html = await response.text();
                resultsContainer.innerHTML = html;
                
                window.history.pushState({}, '', fetchUrl);
                attachPagination();
            } catch (error) {
                console.error('Erreur:', error);
            } finally {
                resultsContainer.classList.remove('loading-overlay');
            }
        }

        function attachPagination() {
            resultsContainer.querySelectorAll('.pagination a').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    fetchCatalogue(this.href);
                });
            });
        }

        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => fetchCatalogue(), 350);
        });

        [categorieSelect, disponibleSelect].forEach(element => {
            element.addEventListener('change', () => fetchCatalogue());
        });

        attachPagination();
    });
</script>
@endpush