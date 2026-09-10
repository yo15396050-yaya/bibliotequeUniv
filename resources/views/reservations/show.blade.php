@extends('layouts.dashboard')
@section('title', 'Réservation #' . $reservation->id)

@section('content')
<x-entete-page :titre="'Réservation #' . $reservation->id" icone="fa-bookmark"
    :sous-titre="$reservation->livre?->titre">
    @can('update', $reservation)
        <a href="{{ route('reservations.edit', $reservation) }}" class="btn btn-outline-primary">
            <i class="fas fa-pen me-1"></i> Modifier
        </a>
    @endcan
    @if($reservation->statut === \App\Models\Reservation::STATUT_ACTIVE)
        @can('annuler', $reservation)
            <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#annulation">
                <i class="fas fa-xmark me-1"></i> Annuler
            </button>
        @endcan
    @endif
    <a href="{{ route('reservations.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Retour
    </a>
</x-entete-page>

@if($reservation->estPrete())
    <div class="alert alert-success border-0 shadow-sm d-flex align-items-center gap-3">
        <i class="fas fa-circle-check fa-lg"></i>
        <div>
            <strong>L'ouvrage est disponible.</strong>
            Il est mis de côté jusqu'au {{ $reservation->date_limite_retrait?->format('d/m/Y') }}.
            Passé ce délai, il sera proposé à l'usager suivant.
        </div>
    </div>
@endif

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Détail</h5>
                <x-badge :statut="$reservation->statut" :texte="$reservation->libelle_statut" />
            </div>
            <div class="card-body">
                <dl class="row small mb-0">
                    <dt class="col-5">Ouvrage</dt>
                    <dd class="col-7">
                        <a href="{{ route('livres.show', $reservation->livre_id) }}" class="text-decoration-none">
                            {{ $reservation->livre?->titre }}
                        </a>
                        <div style="opacity:.65;">{{ $reservation->livre?->auteur }}</div>
                    </dd>
                    <dt class="col-5">Usager</dt>
                    <dd class="col-7">
                        {{ $reservation->user?->name }}
                        <div style="opacity:.65;">{{ $reservation->user?->matricule }}</div>
                    </dd>
                    <dt class="col-5">Position dans la file</dt>
                    <dd class="col-7">{{ $reservation->position_file_attente ?? '—' }}</dd>
                    <dt class="col-5">Réservée le</dt>
                    <dd class="col-7">{{ $reservation->date_reservation?->format('d/m/Y') }}</dd>
                    <dt class="col-5">Expire le</dt>
                    <dd class="col-7">{{ $reservation->date_expiration?->format('d/m/Y') }}</dd>
                    @if($reservation->date_notification)
                        <dt class="col-5">Notifiée le</dt>
                        <dd class="col-7">{{ $reservation->date_notification->format('d/m/Y à H:i') }}</dd>
                        <dt class="col-5">À retirer avant</dt>
                        <dd class="col-7 fw-semibold">{{ $reservation->date_limite_retrait?->format('d/m/Y') }}</dd>
                    @endif
                    @if($reservation->exemplaire)
                        <dt class="col-5">Exemplaire réservé</dt>
                        <dd class="col-7">
                            <code>{{ $reservation->exemplaire->code_barre }}</code>
                            <div style="opacity:.65;">{{ $reservation->exemplaire->emplacement?->chemin_complet }}</div>
                        </dd>
                    @endif
                </dl>

                @if($reservation->notes)
                    <hr><p class="small mb-0" style="white-space:pre-line;">{{ $reservation->notes }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3">
                <h5 class="mb-0"><i class="fas fa-circle-info me-2" style="color:var(--accent-gold);"></i>Disponibilité de l'ouvrage</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Exemplaires disponibles</span>
                    <strong>{{ $reservation->livre?->exemplaires_disponibles }} / {{ $reservation->livre?->exemplaires_totaux }}</strong>
                </div>
                <div class="progress mb-3" style="height:8px;">
                    @php
                        $total = max(1, (int) $reservation->livre?->exemplaires_totaux);
                        $part = round(100 * (int) $reservation->livre?->exemplaires_disponibles / $total);
                    @endphp
                    <div class="progress-bar bg-success" style="width: {{ $part }}%"></div>
                </div>

                <p class="small mb-0" style="opacity:.75;">
                    Dès qu'un exemplaire est restitué, le premier usager de la file est prévenu
                    et dispose de {{ \App\Support\Parametres::entier('reservation.delai_retrait', 2) }} jour(s)
                    pour venir le retirer.
                </p>
            </div>

            @can('update', $reservation)
                @if($reservation->statut === \App\Models\Reservation::STATUT_ACTIVE && ! $reservation->date_notification)
                    <div class="card-footer bg-transparent">
                        <form action="{{ route('reservations.notifier', $reservation) }}" method="POST">
                            @csrf
                            <button class="btn btn-outline-primary w-100">
                                <i class="fas fa-bell me-1"></i> Mettre un exemplaire de côté et notifier
                            </button>
                        </form>
                    </div>
                @endif
            @endcan
        </div>
    </div>
</div>

@can('annuler', $reservation)
<div class="modal fade" id="annulation" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('reservations.annuler', $reservation) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Annuler la réservation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Motif (facultatif)</label>
                <textarea name="motif" rows="3" class="form-control"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
                <button class="btn btn-danger">Confirmer l'annulation</button>
            </div>
        </form>
    </div>
</div>
@endcan
@endsection
