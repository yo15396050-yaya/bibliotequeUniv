@extends('layouts.dashboard')
@section('title', 'Emprunt #' . $emprunt->id)

@php
    $retard = $emprunt->joursRetard();
    $devise = \App\Support\Parametres::devise();
    $motifsRenouvellement = $emprunt->motifsBlocageRenouvellement();
@endphp

@section('content')
<x-entete-page :titre="'Emprunt #' . $emprunt->id" icone="fa-hand-holding"
    :sous-titre="$emprunt->livre?->titre . ' — ' . $emprunt->user?->name">
    <form action="{{ route('emprunts.fiche', $emprunt) }}" method="POST">
        @csrf
        <button class="btn btn-outline-dark"><i class="fas fa-file-pdf me-1"></i> Reçu PDF</button>
    </form>
    @can('retour', $emprunt)
        @if($emprunt->estEnCours())
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modale-retour">
                <i class="fas fa-rotate-left me-1"></i> Enregistrer le retour
            </button>
        @endif
    @endcan
    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</x-entete-page>

@if($retard > 0 && ! $emprunt->estRetourne())
    <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center gap-3">
        <i class="fas fa-triangle-exclamation fa-lg"></i>
        <div class="flex-grow-1">
            <strong>{{ $retard }} jour(s) de retard</strong> — pénalité estimée :
            {{ \App\Support\Parametres::formaterMontant($emprunt->calculerPenaliteRetard()) }}
        </div>
        @can('retour', $emprunt)
            <form action="{{ route('emprunts.rappel-mail', $emprunt) }}" method="POST">
                @csrf
                <button class="btn btn-sm btn-outline-dark"><i class="fas fa-paper-plane me-1"></i> Envoyer un rappel</button>
            </form>
        @endcan
    </div>
@endif

<div class="row g-3">
    {{-- Détail de l'emprunt --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Détail</h5>
                <x-badge :statut="$emprunt->statut" />
            </div>
            <div class="card-body">
                <dl class="row small mb-0">
                    <dt class="col-5">Ouvrage</dt>
                    <dd class="col-7">
                        <a href="{{ route('livres.show', $emprunt->livre_id) }}" class="text-decoration-none">
                            {{ $emprunt->livre?->titre }}
                        </a>
                        <div style="opacity:.65;">{{ $emprunt->livre?->auteur }}</div>
                    </dd>

                    <dt class="col-5">Exemplaire</dt>
                    <dd class="col-7">
                        @if($emprunt->exemplaire)
                            <a href="{{ route('exemplaires.show', $emprunt->exemplaire) }}" class="text-decoration-none">
                                <code>{{ $emprunt->exemplaire->code_barre }}</code>
                            </a>
                            <div style="opacity:.65;">{{ $emprunt->exemplaire->emplacement?->chemin_complet }}</div>
                        @else
                            <span style="opacity:.5;">Non rattaché à un exemplaire</span>
                        @endif
                    </dd>

                    <dt class="col-5">Date d'emprunt</dt>
                    <dd class="col-7">{{ $emprunt->date_emprunt?->format('d/m/Y') }}</dd>

                    <dt class="col-5">Date limite</dt>
                    <dd class="col-7">
                        {{ $emprunt->date_retour_prevue?->format('d/m/Y') }}
                        @if(! $emprunt->estRetourne())
                            <span class="badge {{ $retard > 0 ? 'bg-danger' : 'bg-success' }}">
                                {{ $retard > 0 ? $retard.' j de retard' : 'J-'.$emprunt->joursRestants() }}
                            </span>
                        @endif
                    </dd>

                    <dt class="col-5">Retour effectif</dt>
                    <dd class="col-7">{{ $emprunt->date_retour_effective?->format('d/m/Y') ?? '—' }}</dd>

                    <dt class="col-5">Renouvellements</dt>
                    <dd class="col-7">
                        {{ $emprunt->nombre_renouvellements }} / {{ $emprunt->user?->maxRenouvellements() }}
                    </dd>

                    <dt class="col-5">Enregistré par</dt>
                    <dd class="col-7">{{ $emprunt->bibliothecaire?->name ?? '—' }}</dd>

                    @if($emprunt->receptionniste)
                        <dt class="col-5">Réceptionné par</dt>
                        <dd class="col-7">{{ $emprunt->receptionniste->name }}</dd>
                    @endif

                    @if($emprunt->etat_retour)
                        <dt class="col-5">État au retour</dt>
                        <dd class="col-7">{{ \App\Models\Exemplaire::ETATS[$emprunt->etat_retour] ?? ucfirst($emprunt->etat_retour) }}</dd>
                    @endif
                </dl>

                @if($emprunt->notes)
                    <hr>
                    <p class="small mb-0" style="white-space:pre-line;">{{ $emprunt->notes }}</p>
                @endif
            </div>

            @can('renouveler', $emprunt)
                @if($emprunt->estEnCours())
                    <div class="card-footer bg-transparent">
                        @if($motifsRenouvellement === [])
                            <form action="{{ route('emprunts.renouveler', $emprunt) }}" method="POST">
                                @csrf
                                <button class="btn btn-outline-primary w-100">
                                    <i class="fas fa-arrows-rotate me-1"></i> Renouveler cet emprunt
                                </button>
                            </form>
                        @else
                            <div class="small" style="opacity:.75;">
                                <strong><i class="fas fa-circle-info me-1"></i>Renouvellement impossible :</strong>
                                <ul class="mb-0 ps-3">
                                    @foreach($motifsRenouvellement as $motif)<li>{{ $motif }}</li>@endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endif
            @endcan
        </div>
    </div>

    <div class="col-lg-7">
        {{-- Usager --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body d-flex align-items-center gap-3">
                <img src="{{ $emprunt->user?->url_photo }}" class="rounded-circle"
                     style="width:56px;height:56px;object-fit:cover;" alt="">
                <div class="flex-grow-1 min-w-0">
                    <a href="{{ route('users.show', $emprunt->user_id) }}"
                       class="fw-semibold text-decoration-none d-block" style="color:var(--text-main);">
                        {{ $emprunt->user?->name }}
                    </a>
                    <div class="small" style="opacity:.7;">
                        {{ $emprunt->user?->matricule }} · {{ $emprunt->user?->libelle_role }}
                        @if($emprunt->user?->filiere) · {{ $emprunt->user->filiere }} @endif
                    </div>
                </div>
                <div class="text-end small">
                    <div>{{ $emprunt->user?->emprunts()->enCours()->count() }} / {{ $emprunt->user?->quotaEmprunts() }} emprunt(s)</div>
                    <div style="opacity:.65;">{{ $emprunt->user?->email }}</div>
                </div>
            </div>
        </div>

        {{-- Pénalités liées --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent border-0 pt-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-money-bill-wave me-2" style="color:var(--accent-gold);"></i>Pénalités liées</h6>
                @can('penalites.gerer')
                    <a href="{{ route('penalites.create', ['emprunt_id' => $emprunt->id]) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-plus me-1"></i> Ajouter
                    </a>
                @endcan
            </div>
            <div class="card-body p-0">
                @forelse($emprunt->penalites as $penalite)
                    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                        <div class="min-w-0">
                            <a href="{{ route('penalites.show', $penalite) }}" class="fw-semibold small text-decoration-none"
                               style="color:var(--text-main);">{{ $penalite->libelle_type }}</a>
                            <div class="small text-truncate" style="opacity:.65;">{{ $penalite->motif }}</div>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <div class="fw-bold">{{ number_format((float) $penalite->montant, 0, ',', ' ') }} {{ $devise }}</div>
                            <x-badge :statut="$penalite->statut" :texte="$penalite->libelle_statut" />
                        </div>
                    </div>
                @empty
                    <x-vide message="Aucune pénalité sur cet emprunt." icone="fa-circle-check" />
                @endforelse
            </div>
        </div>

        {{-- Renouvellements --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3">
                <h6 class="mb-0"><i class="fas fa-arrows-rotate me-2" style="color:var(--accent-gold);"></i>Historique des renouvellements</h6>
            </div>
            <div class="card-body p-0">
                @forelse($emprunt->renouvellements as $renouvellement)
                    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom small">
                        <div>
                            {{ $renouvellement->ancienne_echeance?->format('d/m/Y') }}
                            <i class="fas fa-arrow-right mx-1"></i>
                            {{ $renouvellement->nouvelle_echeance?->format('d/m/Y') ?? '—' }}
                            <div style="opacity:.65;">
                                Demandé par {{ $renouvellement->demandeur?->name }}
                                le {{ $renouvellement->created_at->format('d/m/Y') }}
                            </div>
                        </div>
                        <x-badge :statut="$renouvellement->statut" />
                    </div>
                @empty
                    <x-vide message="Aucun renouvellement." icone="fa-arrows-rotate" />
                @endforelse
            </div>
        </div>
    </div>
</div>

@can('retour', $emprunt)
    @if($emprunt->estEnCours())
        <div class="modal fade" id="modale-retour" tabindex="-1">
            <div class="modal-dialog">
                <form action="{{ route('emprunts.retour', $emprunt) }}" method="POST" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Enregistrer le retour</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small">
                            <strong>{{ $emprunt->livre?->titre }}</strong><br>
                            {{ $emprunt->user?->name }} — échéance du {{ $emprunt->date_retour_prevue?->format('d/m/Y') }}
                        </p>
                        @if($retard > 0)
                            <div class="alert alert-warning border-0 small">
                                {{ $retard }} jour(s) de retard — pénalité de
                                {{ \App\Support\Parametres::formaterMontant($emprunt->calculerPenaliteRetard()) }}
                                appliquée automatiquement.
                            </div>
                        @endif
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
                        <button class="btn btn-success"><i class="fas fa-rotate-left me-1"></i> Confirmer le retour</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endcan
@endsection
