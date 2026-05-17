@if($livres->count() > 0)
    <div class="row g-4">
        @foreach($livres as $livre)
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card h-100 border-0 shadow-sm book-card transition-hover">
                    <!-- Image Header -->
                    <div class="position-relative">
                        @if($livre->image_couverture)
                            <img src="{{ asset('storage/' . $livre->image_couverture) }}" 
                                 class="card-img-top" alt="{{ $livre->titre }}" 
                                 style="height: 240px; object-fit: cover;">
                        @else
                            <div class="card-img-top d-flex flex-column align-items-center justify-content-center bg-brown-light" 
                                 style="height: 240px; background-color: #ECE5DD;">
                                <i class="fas fa-book fa-3x text-brown opacity-50 mb-2"></i>
                                <span class="text-brown small opacity-75">Pas de couverture</span>
                            </div>
                        @endif
                        
                        <!-- Badge Catégorie Flottant -->
                        <span class="position-absolute top-0 end-0 m-2 badge bg-brown text-gold shadow-sm">
                            {{ $livre->categorie }}
                        </span>
                    </div>
                    
                    <div class="card-body d-flex flex-column p-3">
                        <h6 class="card-title fw-bold text-brown mb-1 text-truncate" title="{{ $livre->titre }}">
                            {{ $livre->titre }}
                        </h6>
                        <p class="card-text text-muted small mb-2 fst-italic">
                            <i class="fas fa-pen-nib me-1 small"></i> {{ Str::limit($livre->auteur, 25) }}
                        </p>
                        
                        <!-- Badges d'info -->
                        <div class="mb-3 d-flex gap-1 flex-wrap">
                            <span class="badge bg-light text-brown border border-light-subtle small-badge">
                                <i class="fas fa-globe me-1"></i>{{ $livre->langue }}
                            </span>
                            <span class="badge bg-light text-brown border border-light-subtle small-badge">
                                <i class="fas fa-barcode me-1"></i>{{ Str::limit($livre->isbn, 13) }}
                            </span>
                        </div>
                        
                        <div class="mt-auto pt-3 border-top border-light">
                            <!-- Disponibilité -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                @if($livre->exemplaires_disponibles > 0)
                                    <span class="text-success small fw-bold">
                                        <i class="fas fa-check-circle me-1"></i>{{ $livre->exemplaires_disponibles }} dispo.
                                    </span>
                                @else
                                    <span class="text-danger small fw-bold">
                                        <i class="fas fa-times-circle me-1"></i>Rupture
                                    </span>
                                @endif
                                <small class="text-muted" style="font-size: 0.7rem;">Stock: {{ $livre->exemplaires_totaux }}</small>
                            </div>
                            
                            <!-- Actions -->
                            <div class="d-grid gap-2">
                                <a href="{{ route('livres.show', $livre) }}" class="btn btn-sm btn-outline-brown fw-bold">
                                    Voir détails
                                </a>
                                @if($livre->exemplaires_disponibles > 0)
                                    <form action="{{ route('reserver', $livre) }}" method="POST" class="d-grid">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-gold text-white fw-bold">
                                            <i class="fas fa-bookmark me-1"></i> Réserver
                                        </button>
                                    </form>
                                    @if($livre->estDisponibleNumerique())
                                        <a href="{{ route('livres.lire', $livre->id) }}" class="btn btn-sm btn-brown fw-bold text-white shadow-sm mt-1">
                                            <i class="fas fa-book-reader me-1"></i> Lire maintenant
                                        </a>
                                    @endif
                                @else
                                    <button class="btn btn-sm btn-light text-muted" disabled>
                                        Indisponible
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    
    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-5">
        {{ $livres->links() }}
    </div>
@else
    <div class="text-center py-5">
        <div class="mb-3">
            <i class="fas fa-search fa-4x text-brown opacity-25"></i>
        </div>
        <h5 class="text-brown fw-bold">Aucun ouvrage trouvé</h5>
        <p class="text-muted">Essayez de modifier vos critères de recherche.</p>
        <a href="{{ route('catalogue') }}" class="btn btn-link text-gold text-decoration-none fw-bold">
            Réinitialiser les filtres
        </a>
    </div>
@endif
