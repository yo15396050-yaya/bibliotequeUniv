@extends('layouts.dashboard')
@section('title', $user->name)

@php $devise = \App\Support\Parametres::devise(); @endphp

@section('content')
<x-entete-page :titre="$user->name" icone="fa-user" :sous-titre="$user->libelle_role . ' · ' . ($user->matricule ?? '')">
    @can('update', $user)
        <a href="{{ route('users.edit', $user) }}" class="btn btn-warning"><i class="fas fa-pen me-1"></i> Modifier</a>
    @endcan
    <a href="{{ route('exports.etudiant', $user) }}" class="btn btn-outline-dark"><i class="fas fa-file-pdf me-1"></i> Fiche PDF</a>
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</x-entete-page>

@if($motifsBlocage)
    <div class="alert alert-warning border-0 shadow-sm">
        <h6 class="alert-heading"><i class="fas fa-ban me-2"></i>Cet usager ne peut pas emprunter</h6>
        <ul class="mb-0 ps-3">@foreach($motifsBlocage as $motif)<li>{{ $motif }}</li>@endforeach</ul>
    </div>
@endif

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <img src="{{ $user->url_photo }}" class="rounded-circle mb-3" style="width:110px;height:110px;object-fit:cover;" alt="">
                <h5 class="mb-1">{{ $user->name }}</h5>
                <div class="mb-2"><x-badge :statut="$user->statut" :texte="$user->libelle_statut" /></div>
                <dl class="row small text-start mb-0">
                    <dt class="col-5">Matricule</dt><dd class="col-7">{{ $user->matricule ?? '—' }}</dd>
                    <dt class="col-5">Email</dt><dd class="col-7 text-break">{{ $user->email }}</dd>
                    <dt class="col-5">Téléphone</dt><dd class="col-7">{{ $user->telephone ?? '—' }}</dd>
                    <dt class="col-5">Faculté</dt><dd class="col-7">{{ $user->faculte ?? '—' }}</dd>
                    <dt class="col-5">Filière</dt><dd class="col-7">{{ $user->filiere ?? '—' }}</dd>
                    <dt class="col-5">Niveau</dt><dd class="col-7">{{ $user->niveau ?? '—' }}</dd>
                    <dt class="col-5">Année</dt><dd class="col-7">{{ $user->anneeAcademique?->libelle ?? '—' }}</dd>
                    @if($user->estEmprunteur())
                        <dt class="col-5">Quota</dt><dd class="col-7">{{ $user->quotaEmprunts() }} ouvrage(s)</dd>
                        <dt class="col-5">Durée d'emprunt</dt><dd class="col-7">{{ $user->dureeEmprunt() }} jours</dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="row g-3 mb-3">
            <div class="col-12 col-sm-6 col-xl-3"><x-carte-stat titre="Emprunts" :valeur="$statistiques['emprunts_total']" icone="fa-book" couleur="wood" /></div>
            <div class="col-12 col-sm-6 col-xl-3"><x-carte-stat titre="En cours" :valeur="$statistiques['emprunts_en_cours']" icone="fa-hand-holding" couleur="info" /></div>
            <div class="col-12 col-sm-6 col-xl-3"><x-carte-stat titre="En retard" :valeur="$statistiques['emprunts_en_retard']" icone="fa-triangle-exclamation" couleur="danger" /></div>
            <div class="col-12 col-sm-6 col-xl-3"><x-carte-stat titre="Dette" :valeur="number_format($statistiques['dette'], 0, ',', ' ')" :sous-titre="$devise" icone="fa-money-bill-wave" couleur="warning" /></div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent border-0 pt-3"><h5 class="mb-0">Derniers emprunts</h5></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Ouvrage</th><th>Emprunt</th><th>Échéance</th><th>Statut</th></tr></thead>
                    <tbody>
                        @forelse($user->emprunts as $emprunt)
                            <tr>
                                <td><a href="{{ route('emprunts.show', $emprunt) }}" class="text-decoration-none" style="color:var(--text-main);">{{ $emprunt->livre?->titre }}</a></td>
                                <td class="small">{{ $emprunt->date_emprunt?->format('d/m/Y') }}</td>
                                <td class="small">{{ $emprunt->date_retour_prevue?->format('d/m/Y') }}</td>
                                <td><x-badge :statut="$emprunt->statut" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><x-vide message="Aucun emprunt." icone="fa-book" /></td></tr>
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
                        @forelse($user->reservations as $reservation)
                            <div class="px-3 py-2 border-bottom small">
                                {{ $reservation->livre?->titre }}
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
                        @forelse($user->penalites as $penalite)
                            <div class="d-flex justify-content-between px-3 py-2 border-bottom small">
                                <a href="{{ route('penalites.show', $penalite) }}" class="text-decoration-none" style="color:var(--text-main);">
                                    {{ $penalite->libelle_type }}
                                </a>
                                <span>{{ number_format((float) $penalite->montant, 0, ',', ' ') }} {{ $devise }}</span>
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
