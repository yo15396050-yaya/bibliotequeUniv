@extends('layouts.dashboard')
@section('title', 'Exemplaire ' . $exemplaire->code_barre)

@section('content')
<x-entete-page :titre="'Exemplaire ' . $exemplaire->code_barre" icone="fa-barcode"
    :sous-titre="$exemplaire->livre?->titre">
    <a href="{{ route('exemplaires.etiquette', $exemplaire) }}" target="_blank" class="btn btn-outline-dark">
        <i class="fas fa-tag me-1"></i> Étiquette
    </a>
    @can('update', $exemplaire)
        <a href="{{ route('exemplaires.edit', $exemplaire) }}" class="btn btn-warning"><i class="fas fa-pen me-1"></i> Modifier</a>
    @endcan
    @can('delete', $exemplaire)
        <form action="{{ route('exemplaires.destroy', $exemplaire) }}" method="POST"
              onsubmit="return confirm('Supprimer définitivement cet exemplaire ?');">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger"><i class="fas fa-trash me-1"></i> Supprimer</button>
        </form>
    @endcan
</x-entete-page>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">Fiche de l'exemplaire</h5>
                <dl class="row mb-0 small">
                    <dt class="col-5">Code-barres</dt><dd class="col-7"><code>{{ $exemplaire->code_barre }}</code></dd>
                    <dt class="col-5">N° d'inventaire</dt><dd class="col-7">{{ $exemplaire->numero_inventaire ?? '—' }}</dd>
                    <dt class="col-5">Statut</dt><dd class="col-7"><x-badge :statut="$exemplaire->statut" :texte="$exemplaire->libelle_statut" /></dd>
                    <dt class="col-5">État</dt><dd class="col-7">{{ \App\Models\Exemplaire::ETATS[$exemplaire->etat] ?? $exemplaire->etat }}</dd>
                    <dt class="col-5">Emplacement</dt><dd class="col-7">{{ $exemplaire->emplacement?->chemin_complet ?? '—' }}</dd>
                    <dt class="col-5">Acquisition</dt><dd class="col-7">{{ $exemplaire->date_acquisition?->format('d/m/Y') ?? '—' }}</dd>
                    <dt class="col-5">Prix d'achat</dt>
                    <dd class="col-7">{{ $exemplaire->prix_achat ? \App\Support\Parametres::formaterMontant($exemplaire->prix_achat) : '—' }}</dd>
                </dl>
                @if($exemplaire->notes)
                    <hr><p class="small mb-0" style="white-space:pre-line;">{{ $exemplaire->notes }}</p>
                @endif
            </div>
        </div>

        @if($exemplaire->estDisponible())
            @can('emprunts.enregistrer')
                <a href="{{ route('emprunts.create', ['livre_id' => $exemplaire->livre_id, 'code_barre' => $exemplaire->code_barre]) }}"
                   class="btn btn-warning w-100 mt-3">
                    <i class="fas fa-hand-holding me-1"></i> Enregistrer un emprunt avec cet exemplaire
                </a>
            @endcan
        @endif
    </div>

    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3"><h5 class="mb-0">Historique des emprunts</h5></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Usager</th><th>Emprunt</th><th>Retour</th><th>Statut</th></tr></thead>
                    <tbody>
                        @forelse($exemplaire->emprunts as $emprunt)
                            <tr>
                                <td>
                                    <a href="{{ route('emprunts.show', $emprunt) }}" class="text-decoration-none" style="color:var(--text-main);">
                                        {{ $emprunt->user?->name }}
                                    </a>
                                    <div class="small" style="opacity:.65;">{{ $emprunt->user?->matricule }}</div>
                                </td>
                                <td>{{ $emprunt->date_emprunt?->format('d/m/Y') }}</td>
                                <td>{{ $emprunt->date_retour_effective?->format('d/m/Y') ?? '—' }}</td>
                                <td><x-badge :statut="$emprunt->statut" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><x-vide message="Cet exemplaire n'a jamais été emprunté." icone="fa-hand-holding" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
