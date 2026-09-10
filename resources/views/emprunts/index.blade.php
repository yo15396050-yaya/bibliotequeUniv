@extends('layouts.dashboard')
@section('title', 'Emprunts')

@section('content')
<x-entete-page titre="Emprunts" icone="fa-hand-holding"
    :sous-titre="$emprunts->total() . ' emprunt(s) enregistré(s)'">
    @can('emprunts.enregistrer')
        <a href="{{ route('emprunts.create') }}" class="btn btn-warning">
            <i class="fas fa-plus me-1"></i> Nouvel emprunt
        </a>
        <a href="{{ route('emprunts.guichet') }}" class="btn btn-outline-secondary">
            <i class="fas fa-barcode me-1"></i> Guichet de retour
        </a>
        <form action="{{ route('emprunts.rappel-retard') }}" method="POST"
              onsubmit="return confirm('Marquer en retard tous les emprunts échus et générer les pénalités ?');">
            @csrf
            <button class="btn btn-outline-danger">
                <i class="fas fa-triangle-exclamation me-1"></i> Traiter les retards
            </button>
        </form>
    @endcan
</x-entete-page>

<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <x-carte-stat titre="Total" :valeur="number_format($statistiques['total'], 0, ',', ' ')"
            icone="fa-list" couleur="wood" :lien="route('emprunts.index')" />
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <x-carte-stat titre="En cours" :valeur="$statistiques['en_cours']" icone="fa-hand-holding"
            couleur="info" :lien="route('emprunts.index', ['statut' => 'en cours'])" />
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <x-carte-stat titre="En retard" :valeur="$statistiques['en_retard']" icone="fa-triangle-exclamation"
            couleur="danger" :lien="route('emprunts.index', ['statut' => 'en retard'])" />
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <x-carte-stat titre="Retournés" :valeur="number_format($statistiques['retournes'], 0, ',', ' ')"
            icone="fa-circle-check" couleur="success" :lien="route('emprunts.index', ['statut' => 'retourné'])" />
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="search" name="search" class="form-control" value="{{ request('search') }}"
                       placeholder="Usager, matricule, ouvrage, code-barres...">
            </div>
            <div class="col-md-2">
                <select name="statut" class="form-select">
                    <option value="">Tous les statuts</option>
                    @foreach(\App\Models\Emprunt::STATUTS as $cle => $libelle)
                        <option value="{{ $cle }}" @selected(request('statut') === $cle)>{{ $libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}"
                       aria-label="Emprunté à partir du">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}"
                       aria-label="Emprunté jusqu'au">
            </div>
            <div class="col-auto"><button class="btn btn-outline-secondary"><i class="fas fa-filter"></i></button></div>
            <div class="col-auto"><a href="{{ route('emprunts.index') }}" class="btn btn-link">Réinitialiser</a></div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Usager</th><th>Ouvrage</th><th>Exemplaire</th>
                        <th>Emprunt</th><th>Échéance</th><th>Statut</th><th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($emprunts as $emprunt)
                        @php $retard = $emprunt->joursRetard(); @endphp
                        <tr>
                            <td>
                                <a href="{{ route('users.show', $emprunt->user_id) }}"
                                   class="fw-semibold text-decoration-none" style="color:var(--text-main);">
                                    {{ $emprunt->user?->name }}
                                </a>
                                <div class="small" style="opacity:.65;">{{ $emprunt->user?->matricule }}</div>
                            </td>
                            <td class="text-truncate" style="max-width:240px;">
                                <a href="{{ route('livres.show', $emprunt->livre_id) }}"
                                   class="text-decoration-none" style="color:var(--text-main);">
                                    {{ $emprunt->livre?->titre }}
                                </a>
                                <div class="small" style="opacity:.65;">{{ $emprunt->livre?->auteur }}</div>
                            </td>
                            <td>
                                @if($emprunt->exemplaire)
                                    <code class="small">{{ $emprunt->exemplaire->code_barre }}</code>
                                @else
                                    <span class="small" style="opacity:.5;">—</span>
                                @endif
                            </td>
                            <td class="small">{{ $emprunt->date_emprunt?->format('d/m/Y') }}</td>
                            <td class="small">
                                {{ $emprunt->date_retour_prevue?->format('d/m/Y') }}
                                @if($retard > 0 && ! $emprunt->estRetourne())
                                    <div class="text-danger fw-semibold">{{ $retard }} j de retard</div>
                                @endif
                            </td>
                            <td><x-badge :statut="$emprunt->statut" /></td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('emprunts.show', $emprunt) }}"
                                   class="btn btn-sm btn-outline-secondary" title="Détail"><i class="fas fa-eye"></i></a>

                                @if($emprunt->estEnCours())
                                    @can('retour', $emprunt)
                                        <button class="btn btn-sm btn-success" title="Enregistrer le retour"
                                                data-bs-toggle="modal" data-bs-target="#retour-{{ $emprunt->id }}">
                                            <i class="fas fa-rotate-left"></i>
                                        </button>
                                    @endcan
                                    @if($retard > 0)
                                        @can('retour', $emprunt)
                                            <form action="{{ route('emprunts.rappel-mail', $emprunt) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-danger" title="Envoyer un rappel">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    @endif
                                @endif
                            </td>
                        </tr>

                        @if($emprunt->estEnCours())
                            @can('retour', $emprunt)
                                <div class="modal fade" id="retour-{{ $emprunt->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <form action="{{ route('emprunts.retour', $emprunt) }}" method="POST" class="modal-content">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Retour — {{ $emprunt->livre?->titre }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="small mb-3">
                                                    Usager : <strong>{{ $emprunt->user?->name }}</strong><br>
                                                    Échéance : {{ $emprunt->date_retour_prevue?->format('d/m/Y') }}
                                                    @if($retard > 0)
                                                        <span class="text-danger fw-semibold">
                                                            — {{ $retard }} jour(s) de retard,
                                                            pénalité estimée {{ \App\Support\Parametres::formaterMontant($emprunt->calculerPenaliteRetard()) }}
                                                        </span>
                                                    @endif
                                                </p>
                                                <label class="form-label">État de l'exemplaire</label>
                                                <select name="etat_retour" class="form-select mb-3">
                                                    <option value="bon">Bon état</option>
                                                    <option value="neuf">Neuf</option>
                                                    <option value="moyen">État moyen</option>
                                                    <option value="mauvais">Endommagé (pénalité)</option>
                                                    <option value="perdu">Perdu (pénalité)</option>
                                                </select>
                                                <label class="form-label">Observation</label>
                                                <input type="text" name="observation" class="form-control" placeholder="Facultatif">
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <button class="btn btn-success"><i class="fas fa-rotate-left me-1"></i> Enregistrer le retour</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endcan
                        @endif
                    @empty
                        <tr><td colspan="7"><x-vide message="Aucun emprunt ne correspond à ces critères." icone="fa-hand-holding" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $emprunts->links() }}
    </div>
</div>
@endsection
