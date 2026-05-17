@extends('layouts.dashboard')

@section('title', 'Gestion des Livres')

@section('content')
<div class="container-fluid py-4">
    {{-- Header de Page --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-brown">
                <i class="fas fa-layer-group me-2 text-gold"></i>Gestion des Livres
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Inventaire</li>
                </ol>
            </nav>
        </div>
            <div class="d-flex gap-2">
                <a href="{{ route('exports.inventaire') }}" class="btn btn-outline-danger btn-sm shadow-sm">
                    <i class="fas fa-file-pdf me-1"></i>Inventaire PDF
                </a>
                <a href="{{ route('livres.create') }}" class="btn btn-gold btn-sm shadow-sm">
                    <i class="fas fa-plus-circle me-1"></i>Inscrire un livre
                </a>
            </div>
    </div>

    {{-- Filtres et recherche --}}
    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="card-header bg-brown-light border-0 py-3">
            <h6 class="mb-0 text-brown small fw-bold text-uppercase tracking-wider">
                <i class="fas fa-filter me-2"></i>Critères de recherche
            </h6>
        </div>
        <div class="card-body bg-paper">
            <form action="{{ route('livres.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                               placeholder="Titre, auteur ou ISBN..." 
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="categorie" class="form-select custom-select">
                        <option value="">Toutes les catégories</option>
                        @foreach($categories as $categorie)
                            <option value="{{ $categorie }}" {{ request('categorie') == $categorie ? 'selected' : '' }}>
                                {{ $categorie }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="statut" class="form-select custom-select">
                        <option value="">Tous les statuts</option>
                        <option value="disponible" {{ request('statut') == 'disponible' ? 'selected' : '' }}>Disponible</option>
                        <option value="emprunté" {{ request('statut') == 'emprunté' ? 'selected' : '' }}>Emprunté</option>
                        <option value="réservé" {{ request('statut') == 'réservé' ? 'selected' : '' }}>Réservé</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-gold text-white w-100 fw-bold">
                        Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Liste des livres --}}
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-body p-0" id="livres-table-container">
            @include('livres.partials._table')
        </div>
    </div>
</div>

@push('styles')
<style>
    :root {
        --brown: #5D4037;
        --brown-light: #f4efed;
        --gold: #D4AF37;
        --gold-dark: #B8860B;
        --paper: #FAF3E0;
    }

    .text-brown { color: var(--brown); }
    .text-gold { color: var(--gold); }
    .text-gold-dark { color: var(--gold-dark); }
    .bg-brown { background-color: var(--brown); }
    .bg-brown-light { background-color: var(--brown-light); }
    .bg-paper { background-color: var(--paper); }

    .btn-brown {
        background-color: var(--brown);
        color: white;
        border: none;
        font-weight: 600;
        transition: all 0.3s;
    }
    .btn-brown:hover {
        background-color: #3E2723;
        color: var(--gold);
        transform: translateY(-2px);
    }

    .btn-gold {
        background-color: var(--gold);
        border: none;
        transition: all 0.3s;
    }
    .btn-gold:hover { background-color: var(--gold-dark); transform: translateY(-1px); }

    .book-icon-sm {
        width: 40px;
        height: 40px;
        background-color: var(--paper);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(212, 175, 55, 0.2);
    }

    .custom-table thead th {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
        border: none;
        padding: 1.2rem 1rem;
    }

    .custom-table tbody tr {
        transition: background 0.2s;
        border-bottom: 1px solid rgba(0,0,0,0.03);
    }

    .custom-table tbody tr:hover {
        background-color: rgba(212, 175, 55, 0.05) !important;
    }

    .badge-paper {
        background-color: #fff;
        color: var(--brown);
        font-weight: 500;
    }

    .badge-status {
        padding: 0.4rem 0.8rem;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
    }

    .bg-success-light { background-color: #e8f5e9; }
    .bg-warning-light { background-color: #fff8e1; }
    .bg-info-light { background-color: #e3f2fd; }

    .tracking-wider { letter-spacing: 0.05em; }
    
    .btn-white {
        background: white;
        border: 1px solid #eee;
    }
    .btn-white:hover {
        background: #f8f9fa;
        border-color: var(--gold);
    }

    .pagination .page-link {
        color: var(--brown);
        border: none;
        margin: 0 3px;
        border-radius: 5px;
    }
    .pagination .page-item.active .page-link {
        background-color: var(--gold);
        color: white;
    }

    .loading-opacity {
        opacity: 0.5;
        transition: opacity 0.3s ease;
        pointer-events: none;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tableContainer = document.getElementById('livres-table-container');
        const searchInput = document.querySelector('input[name="search"]');
        const categorieSelect = document.querySelector('select[name="categorie"]');
        const statutSelect = document.querySelector('select[name="statut"]');
        const filterForm = searchInput.closest('form');

        let debounceTimer;

        async function fetchLivres(url = null) {
            tableContainer.classList.add('loading-opacity');
            
            const formData = new FormData(filterForm);
            const params = new URLSearchParams(formData);
            
            const fetchUrl = url || `${window.location.pathname}?${params.toString()}`;

            try {
                const response = await fetch(fetchUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) throw new Error('Erreur réseau');

                const html = await response.text();
                tableContainer.innerHTML = html;
                
                // Mettre à jour l'URL sans recharger la page
                window.history.pushState({}, '', fetchUrl);

                // Ré-attacher les écouteurs pour la pagination AJAX
                attachPaginationLinks();
            } catch (error) {
                console.error('Erreur lors de la recherche:', error);
            } finally {
                tableContainer.classList.remove('loading-opacity');
            }
        }

        function attachPaginationLinks() {
            const paginationLinks = tableContainer.querySelectorAll('.pagination a');
            paginationLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    fetchLivres(this.href);
                });
            });
        }

        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => fetchLivres(), 300);
        });

        [categorieSelect, statutSelect].forEach(select => {
            select.addEventListener('change', () => fetchLivres());
        });

        // Initialisation de la pagination AJAX
        attachPaginationLinks();
    });
</script>
@endpush
@endsection