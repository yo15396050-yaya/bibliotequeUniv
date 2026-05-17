@extends('layouts.dashboard')

@section('title', 'Registre des Étudiants')

@section('breadcrumbs')
    <li class="breadcrumb-item active" style="color: #5D4037;">Étudiants</li>
@endsection

@section('page-title', 'Registre des Étudiants')

@section('content')
<style>
    /* Thème Global */
    .page-wrapper {
        background-color: #FAF3E0;
        min-height: 80vh;
        padding: 1.5rem;
        border-radius: 10px;
    }

    /* Card & Header */
    .custom-card {
        border: none;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        border-radius: 8px;
        overflow: hidden;
    }

    .custom-header {
        background-color: #5D4037 !important; /* Bois Sombre */
        color: #D4AF37 !important; /* Or */
        border-bottom: 3px solid #D4AF37;
        padding: 1rem 1.5rem;
    }

    /* Table Styling */
    .table-container {
        background-color: white;
    }

    .custom-table thead {
        background-color: #f8f1e0;
        color: #5D4037;
    }

    .custom-table thead th {
        border-bottom: 2px solid #D4AF37;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 1px;
        padding: 12px;
    }

    .custom-table tbody tr {
        transition: all 0.2s;
    }

    .custom-table tbody tr:hover {
        background-color: #fffdf5 !important;
    }

    /* Badges & Buttons */
    .badge-filiere {
        background-color: #e0d5ba;
        color: #5D4037;
        font-weight: 600;
    }

    .btn-gold {
        background-color: #D4AF37;
        color: #5D4037;
        border: none;
        font-weight: bold;
        transition: 0.3s;
    }

    .btn-gold:hover {
        background-color: #5D4037;
        color: #FAF3E0;
    }

    .btn-action-view {
        color: #5D4037;
        background: transparent;
        border: 1px solid #5D4037;
    }

    .btn-action-view:hover {
        background: #5D4037;
        color: white;
    }

    /* Empty State */
    .empty-state {
        border: 2px dashed #D4AF37;
        background-color: #fdfbf7;
        color: #5D4037;
    }
</style>

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="card custom-card">
            <div class="card-header custom-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-user-graduate me-2"></i>Registre des Étudiants
                </h5>
                <div class="d-flex gap-2">
                    <input type="text" id="student-search" class="form-control form-control-sm border-gold-soft" placeholder="Rechercher par nom, matricule..." style="width: 250px;">
                    <a href="{{ route('etudiants.create') }}" class="btn btn-gold btn-sm shadow-sm text-white">
                        <i class="fas fa-plus-circle me-1"></i>Inscrire un étudiant
                    </a>
                </div>
            </div>

            <div class="card-body p-0 table-container" id="student-table-container">
                @include('etudiants.partials._table')
            </div>
            
            <div class="card-footer bg-white border-0 py-3">
                <small class="text-muted fst-italic">
                    <i class="fas fa-info-circle me-1"></i> Tapez un nom ou un matricule pour filtrer instantanément la liste.
                </small>
            </div>
        </div>
    </div>
</div>

<style>
    .border-gold-soft { border: 1px solid rgba(212, 175, 55, 0.4); }
    .bg-success-soft { background-color: rgba(46, 204, 113, 0.1); }
    .bg-danger-soft { background-color: rgba(231, 76, 60, 0.1); }
    .btn-white { background-color: #fff; }
    .btn-white:hover { background-color: #f8f9fa; }
    
    .loading-overlay {
        opacity: 0.6;
        pointer-events: none;
        transition: opacity 0.2s ease;
    }
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('student-search');
        const container = document.getElementById('student-table-container');
        let debounceTimer;

        async function fetchStudents(url = null) {
            container.classList.add('loading-overlay');
            const search = searchInput.value;
            const fetchUrl = url || `{{ route('etudiants.index') }}?search=${encodeURIComponent(search)}`;

            try {
                const response = await fetch(fetchUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!response.ok) throw new Error('Erreur réseau');

                const html = await response.text();
                container.innerHTML = html;
                
                if(!url) window.history.pushState({}, '', fetchUrl);
                attachPagination();
            } catch (error) {
                console.error('Erreur:', error);
            } finally {
                container.classList.remove('loading-overlay');
            }
        }

        function attachPagination() {
            container.querySelectorAll('.pagination a').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    fetchStudents(this.href);
                });
            });
        }

        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => fetchStudents(), 350);
        });

        attachPagination();
    });
</script>
@endpush
@endsection