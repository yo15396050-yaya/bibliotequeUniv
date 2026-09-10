@extends('layouts.dashboard')
@section('title', 'Réservations')

@php $personnel = Auth::user()->peut('reservations.voir'); @endphp

@section('content')
<x-entete-page :titre="$personnel ? 'Réservations' : 'Mes réservations'" icone="fa-bookmark"
    :sous-titre="$reservations->total() . ' réservation(s)'">
    @can('create', \App\Models\Reservation::class)
        <a href="{{ route('reservations.create') }}" class="btn btn-warning">
            <i class="fas fa-plus me-1"></i> Nouvelle réservation
        </a>
    @endcan
</x-entete-page>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="search" name="search" class="form-control" value="{{ request('search') }}"
                       placeholder="Rechercher un ouvrage...">
            </div>
            <div class="col-md-3">
                <select name="statut" class="form-select" onchange="this.form.submit()">
                    <option value="">Tous les statuts</option>
                    @foreach(\App\Models\Reservation::STATUTS as $cle => $libelle)
                        <option value="{{ $cle }}" @selected(request('statut') === $cle)>{{ $libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-outline-secondary"><i class="fas fa-filter"></i></button></div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        @if($personnel)<th>Usager</th>@endif
                        <th>Ouvrage</th><th class="text-center">File</th>
                        <th>Réservée le</th><th>Disponibilité</th><th>Statut</th><th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reservations as $reservation)
                        <tr>
                            @if($personnel)
                                <td>
                                    <a href="{{ route('users.show', $reservation->user_id) }}"
                                       class="fw-semibold text-decoration-none" style="color:var(--text-main);">
                                        {{ $reservation->user?->name }}
                                    </a>
                                    <div class="small" style="opacity:.65;">{{ $reservation->user?->matricule }}</div>
                                </td>
                            @endif
                            <td class="text-truncate" style="max-width:260px;">
                                <a href="{{ route('livres.show', $reservation->livre_id) }}"
                                   class="text-decoration-none" style="color:var(--text-main);">
                                    {{ $reservation->livre?->titre }}
                                </a>
                            </td>
                            <td class="text-center">
                                @if($reservation->statut === \App\Models\Reservation::STATUT_ACTIVE)
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis">
                                        n° {{ $reservation->position_file_attente }}
                                    </span>
                                @else
                                    <span class="small" style="opacity:.5;">—</span>
                                @endif
                            </td>
                            <td class="small">{{ $reservation->date_reservation?->format('d/m/Y') }}</td>
                            <td class="small">
                                @if($reservation->estPrete())
                                    <span class="text-success fw-semibold">
                                        <i class="fas fa-circle-check me-1"></i>Disponible
                                    </span>
                                    <div style="opacity:.7;">
                                        à retirer avant le {{ $reservation->date_limite_retrait?->format('d/m/Y') }}
                                    </div>
                                @else
                                    <span style="opacity:.6;">En attente</span>
                                @endif
                            </td>
                            <td><x-badge :statut="$reservation->statut" :texte="$reservation->libelle_statut" /></td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('reservations.show', $reservation) }}"
                                   class="btn btn-sm btn-outline-secondary"><i class="fas fa-eye"></i></a>

                                @if($reservation->statut === \App\Models\Reservation::STATUT_ACTIVE)
                                    @can('annuler', $reservation)
                                        <form action="{{ route('reservations.annuler', $reservation) }}" method="POST"
                                              class="d-inline" onsubmit="return confirm('Annuler cette réservation ?');">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-danger" title="Annuler">
                                                <i class="fas fa-xmark"></i>
                                            </button>
                                        </form>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $personnel ? 7 : 6 }}">
                                <x-vide message="Aucune réservation." icone="fa-bookmark">
                                    <a href="{{ route('catalogue') }}" class="btn btn-sm btn-warning">Parcourir le catalogue</a>
                                </x-vide>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $reservations->links() }}
    </div>
</div>
@endsection
