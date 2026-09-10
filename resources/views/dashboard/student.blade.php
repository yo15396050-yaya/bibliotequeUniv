@extends('layouts.dashboard')

@section('title', 'Mon espace — Bibliothèque Universitaire')

@section('content')
<x-entete-page :titre="'Bonjour ' . (Auth::user()->prenom ?: Auth::user()->name)" icone="fa-user"
    sous-titre="Voici l'état de vos emprunts, réservations et pénalités.">
    <a href="{{ route('catalogue') }}" class="btn btn-warning">
        <i class="fas fa-book-open-reader me-1"></i> Parcourir le catalogue
    </a>
</x-entete-page>

@php $devise = \App\Support\Parametres::devise(); @endphp

{{-- Indicateurs personnels --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <x-carte-stat titre="Emprunts actifs" :valeur="$stats['emprunts_en_cours']"
            icone="fa-hand-holding" couleur="info"
            :sous-titre="'Quota : ' . $stats['quota'] . ' ouvrage(s)'"
            :lien="route('mes-emprunts')" />
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <x-carte-stat titre="À retourner bientôt" :valeur="$stats['a_retourner_bientot']"
            icone="fa-clock" couleur="warning" :lien="route('mes-emprunts')" />
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <x-carte-stat titre="En retard" :valeur="$stats['emprunts_en_retard']"
            icone="fa-triangle-exclamation" couleur="danger"
            :lien="route('mes-emprunts', ['statut' => 'en retard'])" />
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <x-carte-stat titre="Réservations" :valeur="$stats['reservations_actives']"
            icone="fa-bookmark" couleur="gold" :lien="route('reservations.index')" />
    </div>
</div>

@if($stats['penalites_impayees'] > 0)
    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center gap-3">
        <i class="fas fa-money-bill-wave fa-lg"></i>
        <div class="flex-grow-1">
            <strong>Pénalités impayées : {{ \App\Support\Parametres::formaterMontant($stats['penalites_impayees']) }}</strong>
            <div class="small">Régularisez votre situation auprès de la bibliothèque pour continuer à emprunter.</div>
        </div>
        <a href="{{ route('penalites.index') }}" class="btn btn-sm btn-outline-dark">Voir le détail</a>
    </div>
@endif

<div class="row g-3">
    {{-- Emprunts en cours --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-hand-holding me-2" style="color: var(--accent-gold);"></i>Mes emprunts en cours</h5>
                <a href="{{ route('mes-emprunts') }}" class="small text-decoration-none">Tout voir</a>
            </div>
            <div class="card-body p-0">
                @forelse($emprunts_en_cours as $emprunt)
                    @php $jours = $emprunt->joursRestants(); $retard = $emprunt->estEnRetard(); @endphp
                    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                        <div class="min-w-0 me-2">
                            <a href="{{ route('livres.show', $emprunt->livre_id) }}"
                               class="fw-semibold text-decoration-none d-block text-truncate" style="color: var(--text-main);">
                                {{ $emprunt->livre?->titre }}
                            </a>
                            <small style="opacity:.7;">
                                À rendre le {{ $emprunt->date_retour_prevue->format('d/m/Y') }}
                            </small>
                        </div>
                        <div class="text-end flex-shrink-0 d-flex align-items-center gap-2">
                            @if($retard)
                                <span class="badge bg-danger">{{ $emprunt->joursRetard() }} j de retard</span>
                            @elseif($jours <= 3)
                                <span class="badge bg-warning text-dark">J-{{ $jours }}</span>
                            @else
                                <span class="badge bg-success-subtle text-success-emphasis">J-{{ $jours }}</span>
                            @endif

                            @if($emprunt->peutEtreRenouvele())
                                <form action="{{ route('emprunts.renouveler', $emprunt) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-secondary" title="Demander un renouvellement">
                                        <i class="fas fa-arrows-rotate"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <x-vide message="Vous n'avez aucun emprunt en cours." icone="fa-book">
                        <a href="{{ route('catalogue') }}" class="btn btn-sm btn-warning">Découvrir le catalogue</a>
                    </x-vide>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Réservations --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-bookmark me-2" style="color: var(--accent-gold);"></i>Mes réservations</h5>
                <a href="{{ route('reservations.index') }}" class="small text-decoration-none">Tout voir</a>
            </div>
            <div class="card-body p-0">
                @forelse($reservations_recentes as $reservation)
                    <div class="px-3 py-2 border-bottom">
                        <a href="{{ route('reservations.show', $reservation) }}"
                           class="fw-semibold text-decoration-none d-block text-truncate" style="color: var(--text-main);">
                            {{ $reservation->livre?->titre }}
                        </a>
                        <small style="opacity:.75;">
                            @if($reservation->estPrete())
                                <span class="text-success fw-semibold">Disponible</span> — à retirer avant le
                                {{ $reservation->date_limite_retrait?->format('d/m/Y') }}
                            @else
                                Position {{ $reservation->position_file_attente }} dans la file d'attente
                            @endif
                        </small>
                    </div>
                @empty
                    <x-vide message="Aucune réservation en cours." icone="fa-bookmark" />
                @endforelse
            </div>
        </div>
    </div>

    {{-- Historique récent --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-3">
                <h5 class="mb-0"><i class="fas fa-clock-rotate-left me-2" style="color: var(--accent-gold);"></i>Historique récent</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Ouvrage</th>
                            <th class="d-none d-md-table-cell">Emprunté le</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($emprunts_recents as $emprunt)
                            <tr>
                                <td class="text-truncate" style="max-width: 260px;">{{ $emprunt->livre?->titre }}</td>
                                <td class="d-none d-md-table-cell">{{ $emprunt->date_emprunt?->format('d/m/Y') }}</td>
                                <td><x-badge :statut="$emprunt->statut" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="3"><x-vide message="Aucun emprunt à ce jour." icone="fa-book" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pénalités --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-3">
                <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2" style="color: var(--accent-gold);"></i>Mes pénalités</h5>
            </div>
            <div class="card-body p-0">
                @forelse($penalites as $penalite)
                    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                        <div class="min-w-0 me-2">
                            <div class="fw-semibold small">{{ $penalite->libelle_type }}</div>
                            <small style="opacity:.7;">{{ $penalite->emprunt?->livre?->titre ?? $penalite->motif }}</small>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <div class="fw-bold">{{ number_format($penalite->reste_a_payer, 0, ',', ' ') }} {{ $devise }}</div>
                            <x-badge :statut="$penalite->statut" :texte="$penalite->libelle_statut" />
                        </div>
                    </div>
                @empty
                    <x-vide message="Aucune pénalité : bravo !" icone="fa-circle-check" />
                @endforelse
            </div>
        </div>
    </div>

    {{-- Recommandations --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3">
                <h5 class="mb-0"><i class="fas fa-wand-magic-sparkles me-2" style="color: var(--accent-gold);"></i>Suggestions pour vous</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @forelse($recommandations as $livre)
                        <div class="col-6 col-md-4 col-lg-2">
                            <a href="{{ route('livres.show', $livre) }}" class="text-decoration-none" style="color: var(--text-main);">
                                <div class="card h-100 border-0 shadow-sm">
                                    @if($livre->image_couverture)
                                        <img src="{{ asset('storage/' . $livre->image_couverture) }}" class="card-img-top"
                                             alt="{{ $livre->titre }}" style="height:150px; object-fit:cover;">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center"
                                             style="height:150px; background: var(--wood-primary);">
                                            <i class="fas fa-book fa-2x" style="color: var(--accent-gold);"></i>
                                        </div>
                                    @endif
                                    <div class="card-body p-2">
                                        <div class="small fw-semibold text-truncate">{{ $livre->titre }}</div>
                                        <div class="small text-truncate" style="opacity:.65;">{{ $livre->auteur }}</div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @empty
                        <div class="col-12"><x-vide message="Aucune suggestion pour le moment." icone="fa-wand-magic-sparkles" /></div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
