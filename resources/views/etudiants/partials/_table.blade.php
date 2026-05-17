<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="bg-light">
            <tr>
                <th class="ps-4">Étudiant</th>
                <th>Matricule</th>
                <th>Filière & Niveau</th>
                <th>Statut</th>
                <th class="text-end pe-4">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($etudiants as $etudiant)
                <tr>
                    <td class="ps-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm me-3 bg-paper d-flex align-items-center justify-content-center rounded-circle border" style="width: 40px; height: 40px;">
                                <i class="fas fa-user-graduate text-brown"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-brown">{{ $etudiant->name }}</h6>
                                <small class="text-muted">{{ $etudiant->email }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-light text-brown border">{{ $etudiant->matricule }}</span>
                    </td>
                    <td>
                        <div class="small fw-bold text-brown">{{ $etudiant->filiere ?? 'N/A' }}</div>
                        <div class="small text-muted">{{ $etudiant->niveau ?? 'N/A' }}</div>
                    </td>
                    <td>
                        @if($etudiant->actif ?? true)
                            <span class="badge bg-success-soft text-success px-2 py-1">Actif</span>
                        @else
                            <span class="badge bg-danger-soft text-danger px-2 py-1">Inactif</span>
                        @endif
                    </td>
                    <td class="text-end pe-4">
                        <div class="btn-group shadow-sm rounded">
                            <a href="{{ route('etudiants.show', $etudiant) }}" class="btn btn-sm btn-white text-brown border" title="Voir le profil">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('etudiants.edit', $etudiant) }}" class="btn btn-sm btn-white text-brown border" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-white text-danger border" 
                                    onclick="if(confirm('Confirmer la suppression ?')) document.getElementById('delete-form-{{ $etudiant->id }}').submit();" title="Supprimer">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                        <form id="delete-form-{{ $etudiant->id }}" action="{{ route('etudiants.destroy', $etudiant->id) }}" method="POST" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <div class="text-muted mb-2">
                            <i class="fas fa-users-slash fa-3x opacity-25"></i>
                        </div>
                        <p class="mb-0">Aucun étudiant trouvé.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-between align-items-center mt-4 px-4">
    <div class="small text-muted">
        Affichage de {{ $etudiants->firstItem() ?? 0 }} à {{ $etudiants->lastItem() ?? 0 }} sur {{ $etudiants->total() }} étudiants
    </div>
    <div class="pagination-container">
        {{ $etudiants->links() }}
    </div>
</div>
