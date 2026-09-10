@extends('layouts.dashboard')
@section('title', 'Mes emprunts')

@section('content')
<x-entete-page titre="Mes emprunts" icone="fa-hand-holding"
    :sous-titre="$emprunts->total() . ' emprunt(s) dans votre historique'">
    <a href="{{ route('catalogue') }}" class="btn btn-warning">
        <i class="fas fa-book-open-reader me-1"></i> Parcourir le catalogue
    </a>
</x-entete-page>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <ul class="nav nav-pills flex-wrap gap-1">
            <li class="nav-item">
                <a class="nav-link {{ request('statut') ? '' : 'active' }}" href="{{ route('mes-emprunts') }}">Tous</a>
            </li>
            @foreach(\App\Models\Emprunt::STATUTS as $cle => $libelle)
                <li class="nav-item">
                    <a class="nav-link {{ request('statut') === $cle ? 'active' : '' }}"
                       href="{{ route('mes-emprunts', ['statut' => $cle]) }}">{{ $libelle }}</a>
                </li>
            @endforeach
        </ul>
    </div>
</div>

<div class="row g-3">
    @forelse($emprunts as $emprunt)
        @php
            $retard = $emprunt->joursRetard();
            $jours = $emprunt->joursRestants();
            $motifs = $emprunt->motifsBlocageRenouvellement();
        @endphp
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex gap-3">
                    @if($emprunt->livre?->image_couverture)
                        <img src="{{ asset('storage/'.$emprunt->livre->image_couverture) }}"
                             class="rounded flex-shrink-0" style="width:64px;height:88px;object-fit:cover;" alt="">
                    @else
                        <div class="rounded d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:64px;height:88px;background:var(--wood-primary);color:#fff;">
                            <i class="fas fa-book"></i>
                        </div>
                    @endif

                    <div class="min-w-0 flex-grow-1">
                        <a href="{{ route('livres.show', $emprunt->livre_id) }}"
                           class="fw-semibold text-decoration-none d-block" style="color:var(--text-main);">
                            {{ $emprunt->livre?->titre }}
                        </a>
                        <div class="small mb-2" style="opacity:.65;">{{ $emprunt->livre?->auteur }}</div>

                        <div class="small mb-1">
                            <i class="fas fa-calendar-day me-1" style="opacity:.6;"></i>
                            Emprunté le {{ $emprunt->date_emprunt?->format('d/m/Y') }}
                        </div>
                        <div class="small mb-2">
                            <i class="fas fa-calendar-check me-1" style="opacity:.6;"></i>
                            À rendre le {{ $emprunt->date_retour_prevue?->format('d/m/Y') }}
                        </div>

                        <div class="d-flex flex-wrap gap-1">
                            <x-badge :statut="$emprunt->statut" />
                            @if(! $emprunt->estRetourne())
                                @if($retard > 0)
                                    <span class="badge bg-danger">{{ $retard }} j de retard</span>
                                @elseif($jours <= 3)
                                    <span class="badge bg-warning text-dark">J-{{ $jours }}</span>
                                @endif
                            @endif
                            @if($emprunt->nombre_renouvellements > 0)
                                <span class="badge bg-secondary-subtle text-secondary-emphasis">
                                    {{ $emprunt->nombre_renouvellements }} renouvellement(s)
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-transparent d-flex gap-2">
                    <a href="{{ route('emprunts.show', $emprunt) }}" class="btn btn-sm btn-outline-secondary flex-grow-1">
                        <i class="fas fa-eye me-1"></i> Détail
                    </a>
                    @if($emprunt->estEnCours())
                        @if($motifs === [])
                            <form action="{{ route('emprunts.renouveler', $emprunt) }}" method="POST" class="flex-grow-1">
                                @csrf
                                <button class="btn btn-sm btn-warning w-100">
                                    <i class="fas fa-arrows-rotate me-1"></i> Renouveler
                                </button>
                            </form>
                        @else
                            <button class="btn btn-sm btn-outline-secondary flex-grow-1" disabled
                                    title="{{ implode(' ', $motifs) }}">
                                <i class="fas fa-ban me-1"></i> Non renouvelable
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <x-vide message="Vous n'avez aucun emprunt correspondant." icone="fa-book">
                    <a href="{{ route('catalogue') }}" class="btn btn-sm btn-warning">Découvrir le catalogue</a>
                </x-vide>
            </div></div>
        </div>
    @endforelse
</div>

<div class="mt-3">{{ $emprunts->links() }}</div>
@endsection
