<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr><th>Étudiant</th><th>Matricule</th><th>Filière / Niveau</th><th>Statut</th><th class="text-center">En cours</th><th class="text-end">Actions</th></tr>
        </thead>
        <tbody>
            @forelse($etudiants as $etudiant)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $etudiant->url_photo }}" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;" alt="">
                            <div class="min-w-0">
                                <a href="{{ route('etudiants.show', $etudiant) }}" class="fw-semibold text-decoration-none d-block text-truncate" style="color:var(--text-main);">
                                    {{ $etudiant->name }}
                                </a>
                                <small style="opacity:.65;">{{ $etudiant->email }}</small>
                            </div>
                        </div>
                    </td>
                    <td>{{ $etudiant->matricule ?? '—' }}</td>
                    <td class="small">{{ $etudiant->filiere ?? '—' }}<div style="opacity:.65;">{{ $etudiant->niveau ?? '' }}</div></td>
                    <td>
                        <x-badge :statut="$etudiant->statut" :texte="$etudiant->libelle_statut" />
                        @unless($etudiant->actif)<span class="badge bg-danger">Désactivé</span>@endunless
                    </td>
                    <td class="text-center">
                        <span class="badge bg-secondary-subtle text-secondary-emphasis">
                            {{ $etudiant->emprunts_en_cours_count ?? 0 }}/{{ $etudiant->quotaEmprunts() }}
                        </span>
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('etudiants.show', $etudiant) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-eye"></i></a>
                        @can('update', $etudiant)
                            <a href="{{ route('etudiants.edit', $etudiant) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-pen"></i></a>
                        @endcan
                        @can('emprunts.enregistrer')
                            <a href="{{ route('emprunts.create', ['user_id' => $etudiant->id]) }}" class="btn btn-sm btn-outline-success" title="Nouvel emprunt">
                                <i class="fas fa-hand-holding"></i>
                            </a>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><x-vide message="Aucun étudiant ne correspond à ces critères." icone="fa-user-graduate" /></td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $etudiants->links() }}
