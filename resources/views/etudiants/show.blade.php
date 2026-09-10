@extends('layouts.dashboard')
@section('title', $etudiant->name)

@php $devise = \App\Support\Parametres::devise(); @endphp

@section('content')
<x-entete-page :titre="$etudiant->name" icone="fa-user-graduate"
    :sous-titre="$etudiant->matricule . ' · ' . ($etudiant->filiere ?? '') . ' ' . ($etudiant->niveau ?? '')">
    @can('emprunts.enregistrer')
        <a href="{{ route('emprunts.create', ['user_id' => $etudiant->id]) }}" class="btn btn-warning">
            <i class="fas fa-hand-holding me-1"></i> Nouvel emprunt
        </a>
    @endcan
    <a href="{{ route('exports.etudiant', $etudiant) }}" class="btn btn-outline-dark"><i class="fas fa-file-pdf me-1"></i> Fiche PDF</a>
    @can('update', $etudiant)
        <a href="{{ route('etudiants.edit', $etudiant) }}" class="btn btn-outline-primary"><i class="fas fa-pen me-1"></i> Modifier</a>
    @endcan
    <a href="{{ route('etudiants.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</x-entete-page>

@if($motifsBlocage)
    <div class="alert alert-warning border-0 shadow-sm">
        <h6 class="alert-heading"><i class="fas fa-ban me-2"></i>Cet étudiant ne peut pas emprunter</h6>
        <ul class="mb-0 ps-3">@foreach($motifsBlocage as $motif)<li>{{ $motif }}</li>@endforeach</ul>
    </div>
@endif

<div class="row g-3 mb-3">
    <div class="col-12 col-sm-6 col-xl-3"><x-carte-stat titre="Emprunts" :valeur="$statistiques['emprunts_total']" icone="fa-book" couleur="wood" /></div>
    <div class="col-12 col-sm-6 col-xl-3"><x-carte-stat titre="En cours" :valeur="$statistiques['emprunts_en_cours']" icone="fa-hand-holding" couleur="info" /></div>
    <div class="col-12 col-sm-6 col-xl-3"><x-carte-stat titre="En retard" :valeur="$statistiques['emprunts_en_retard']" icone="fa-triangle-exclamation" couleur="danger" /></div>
    <div class="col-12 col-sm-6 col-xl-3"><x-carte-stat titre="Dette" :valeur="number_format($statistiques['dette'], 0, ',', ' ')" :sous-titre="$devise" icone="fa-money-bill-wave" couleur="warning" /></div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <img src="{{ $etudiant->url_photo }}" class="rounded-circle mb-3" style="width:110px;height:110px;object-fit:cover;" alt="">
                <h5 class="mb-1">{{ $etudiant->name }}</h5>
                <div class="mb-3"><x-badge :statut="$etudiant->statut" :texte="$etudiant->libelle_statut" /></div>
                <dl class="row small text-start mb-0">
                    <dt class="col-5">Matricule</dt><dd class="col-7">{{ $etudiant->matricule }}</dd>
                    <dt class="col-5">Email</dt><dd class="col-7 text-break">{{ $etudiant->email }}</dd>
                    <dt class="col-5">Téléphone</dt><dd class="col-7">{{ $etudiant->telephone ?? '—' }}</dd>
                    <dt class="col-5">Faculté</dt><dd class="col-7">{{ $etudiant->faculte ?? '—' }}</dd>
                    <dt class="col-5">Département</dt><dd class="col-7">{{ $etudiant->departement ?? '—' }}</dd>
                    <dt class="col-5">Filière</dt><dd class="col-7">{{ $etudiant->filiere ?? '—' }}</dd>
                    <dt class="col-5">Niveau</dt><dd class="col-7">{{ $etudiant->niveau ?? '—' }}</dd>
                    <dt class="col-5">Année</dt><dd class="col-7">{{ $etudiant->anneeAcademique?->libelle ?? '—' }}</dd>
                    <dt class="col-5">Quota</dt><dd class="col-7">{{ $etudiant->quotaEmprunts() }} ouvrage(s)</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent border-0 pt-3"><h5 class="mb-0">Historique des emprunts</h5></div>
            <div class="table-responsive" style="max-height: 420px; overflow-y:auto;">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Ouvrage</th><th>Emprunt</th><th>Échéance</th><th>Retour</th><th>Statut</th></tr></thead>
                    <tbody>
                        @forelse($etudiant->emprunts as $emprunt)
                            <tr>
                                <td class="small">
                                    <a href="{{ route('emprunts.show', $emprunt) }}" class="text-decoration-none" style="color:var(--text-main);">
                                        {{ $emprunt->livre?->titre }}
                                    </a>
                                </td>
                                <td class="small">{{ $emprunt->date_emprunt?->format('d/m/Y') }}</td>
                                <td class="small">{{ $emprunt->date_retour_prevue?->format('d/m/Y') }}</td>
                                <td class="small">{{ $emprunt->date_retour_effective?->format('d/m/Y') ?? '—' }}</td>
                                <td><x-badge :statut="$emprunt->statut" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><x-vide message="Aucun emprunt." icone="fa-book" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 pt-3"><h6 class="mb-0">Réservations actives</h6></div>
                    <div class="card-body p-0">
                        @forelse($etudiant->reservations as $reservation)
                            <div class="px-3 py-2 border-bottom small d-flex justify-content-between">
                                <span class="text-truncate">{{ $reservation->livre?->titre }}</span>
                                <span class="badge bg-secondary">n° {{ $reservation->position_file_attente }}</span>
                            </div>
                        @empty
                            <x-vide message="Aucune réservation." icone="fa-bookmark" />
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 pt-3"><h6 class="mb-0">Pénalités</h6></div>
                    <div class="card-body p-0">
                        @forelse($etudiant->penalites as $penalite)
                            <div class="d-flex justify-content-between px-3 py-2 border-bottom small">
                                <a href="{{ route('penalites.show', $penalite) }}" class="text-decoration-none text-truncate" style="color:var(--text-main);">
                                    {{ $penalite->libelle_type }}
                                </a>
                                <span class="text-nowrap">
                                    {{ number_format((float) $penalite->montant, 0, ',', ' ') }} {{ $devise }}
                                </span>
                            </div>
                        @empty
                            <x-vide message="Aucune pénalité." icone="fa-circle-check" />
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
