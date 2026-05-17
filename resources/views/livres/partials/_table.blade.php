@if($livres->isEmpty())
    <div class="text-center py-5 bg-paper">
        <div class="mb-3">
            <i class="fas fa-book-open fa-4x text-gold opacity-25"></i>
        </div>
        <h5 class="text-brown opacity-75">Aucun ouvrage ne correspond à votre recherche</h5>
        <p class="text-muted small">Essayez de modifier vos filtres ou d'ajouter un nouveau livre.</p>
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 custom-table">
            <thead class="bg-brown text-white">
                <tr>
                    <th class="ps-4">Ouvrage</th>
                    <th>Auteur</th>
                    <th>Catégorie</th>
                    <th class="text-center">Stock</th>
                    <th>Statut</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white">
                @foreach($livres as $livre)
                <tr>
                    <td class="ps-4">
                        <div class="d-flex align-items-center">
                            <div class="book-icon-sm me-3">
                                <i class="fas fa-book text-brown"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-brown">{{ Str::limit($livre->titre, 45) }}</div>
                                <code class="small text-gold-dark">{{ $livre->isbn }}</code>
                            </div>
                        </div>
                    </td>
                    <td><span class="text-muted">{{ $livre->auteur }}</span></td>
                    <td>
                        <span class="badge badge-paper text-brown border">{{ $livre->categorie }}</span>
                    </td>
                    <td class="text-center">
                        <div class="small fw-bold">{{ $livre->exemplaires_disponibles }} <span class="text-muted">/ {{ $livre->exemplaires_totaux }}</span></div>
                        <div class="progress mt-1" style="height: 4px; width: 60px; margin: 0 auto;">
                            @php $perc = ($livre->exemplaires_disponibles / max(1, $livre->exemplaires_totaux)) * 100; @endphp
                            <div class="progress-bar bg-gold" role="progressbar" style="width: {{ $perc }}%"></div>
                        </div>
                    </td>
                    <td>
                        @if($livre->statut == 'disponible')
                            <span class="badge-status bg-success-light text-success">
                                <i class="fas fa-check-circle me-1"></i> Disponible
                            </span>
                        @elseif($livre->statut == 'emprunté')
                            <span class="badge-status bg-warning-light text-warning">
                                <i class="fas fa-clock me-1"></i> Emprunté
                            </span>
                        @else
                            <span class="badge-status bg-info-light text-info">
                                <i class="fas fa-bookmark me-1"></i> Réservé
                            </span>
                        @endif
                    </td>
                    <td class="text-end pe-4">
                        <div class="btn-group shadow-sm rounded">
                            <a href="{{ route('livres.show', $livre) }}" class="btn btn-white btn-sm px-3" title="Détails">
                                <i class="fas fa-eye text-brown"></i>
                            </a>
                            <a href="{{ route('livres.edit', $livre) }}" class="btn btn-white btn-sm px-3" title="Modifier">
                                <i class="fas fa-edit text-gold"></i>
                            </a>
                            <button type="button" class="btn btn-white btn-sm px-3" 
                                    data-bs-toggle="modal" data-bs-target="#deleteModal{{ $livre->id }}">
                                <i class="fas fa-trash text-danger"></i>
                            </button>
                        </div>

                        {{-- Modal de suppression stylisé --}}
                        <div class="modal fade" id="deleteModal{{ $livre->id }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow-lg">
                                    <div class="modal-body text-center p-5">
                                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-4"></i>
                                        <h4 class="text-brown mb-3">Supprimer cet ouvrage ?</h4>
                                        <p class="text-muted mb-4">Vous êtes sur le point de retirer <strong>"{{ $livre->titre }}"</strong> de l'inventaire. Cette action est irréversible.</p>
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Annuler</button>
                                            <form action="{{ route('livres.destroy', $livre) }}" method="POST">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger px-4">Confirmer la suppression</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-center py-4 bg-paper border-top">
        {{ $livres->links() }};
    </div>
@endif
