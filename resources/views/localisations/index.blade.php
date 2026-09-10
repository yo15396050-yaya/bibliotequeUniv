@extends('layouts.dashboard')
@section('title', 'Rayons et emplacements')

@section('content')
<x-entete-page titre="Rayons et emplacements" icone="fa-map-location-dot"
    :sous-titre="'Bibliothèque → Salle → Rayon → Étagère · ' . $nombreEmplacements . ' emplacement(s)'" />

@can('localisations.gerer')
<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="mb-3"><i class="fas fa-building-columns me-2" style="color: var(--accent-gold);"></i>Nouvelle bibliothèque</h6>
                <form action="{{ route('localisations.store', 'bibliotheques') }}" method="POST" class="d-grid gap-2">
                    @csrf
                    <input name="nom" class="form-control form-control-sm" placeholder="Nom" required>
                    <input name="code" class="form-control form-control-sm" placeholder="Code (ex : BC)" required>
                    <input name="adresse" class="form-control form-control-sm" placeholder="Adresse">
                    <button class="btn btn-sm btn-warning">Ajouter</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="mb-3"><i class="fas fa-door-open me-2" style="color: var(--accent-gold);"></i>Nouvelle salle</h6>
                <form action="{{ route('localisations.store', 'salles') }}" method="POST" class="d-grid gap-2">
                    @csrf
                    <select name="bibliotheque_id" class="form-select form-select-sm" required>
                        <option value="">Bibliothèque…</option>
                        @foreach($bibliotheques as $bibliotheque)
                            <option value="{{ $bibliotheque->id }}">{{ $bibliotheque->nom }}</option>
                        @endforeach
                    </select>
                    <input name="nom" class="form-control form-control-sm" placeholder="Nom" required>
                    <input name="code" class="form-control form-control-sm" placeholder="Code (ex : A)" required>
                    <button class="btn btn-sm btn-warning">Ajouter</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="mb-3"><i class="fas fa-bookmark me-2" style="color: var(--accent-gold);"></i>Nouveau rayon</h6>
                <form action="{{ route('localisations.store', 'rayons') }}" method="POST" class="d-grid gap-2">
                    @csrf
                    <select name="salle_id" class="form-select form-select-sm" required>
                        <option value="">Salle…</option>
                        @foreach($bibliotheques as $bibliotheque)
                            @foreach($bibliotheque->salles as $salle)
                                <option value="{{ $salle->id }}">{{ $bibliotheque->code }} · {{ $salle->nom }}</option>
                            @endforeach
                        @endforeach
                    </select>
                    <input name="nom" class="form-control form-control-sm" placeholder="Nom" required>
                    <input name="code" class="form-control form-control-sm" placeholder="Code" required>
                    <input name="domaine" class="form-control form-control-sm" placeholder="Domaine">
                    <button class="btn btn-sm btn-warning">Ajouter</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="mb-3"><i class="fas fa-layer-group me-2" style="color: var(--accent-gold);"></i>Nouvel emplacement</h6>
                <form action="{{ route('localisations.store', 'emplacements') }}" method="POST" class="d-grid gap-2">
                    @csrf
                    <select name="rayon_id" class="form-select form-select-sm" required>
                        <option value="">Rayon…</option>
                        @foreach($bibliotheques as $bibliotheque)
                            @foreach($bibliotheque->salles as $salle)
                                @foreach($salle->rayons as $rayon)
                                    <option value="{{ $rayon->id }}">{{ $salle->code }} · {{ $rayon->nom }}</option>
                                @endforeach
                            @endforeach
                        @endforeach
                    </select>
                    <input name="etagere" class="form-control form-control-sm" placeholder="Étagère (ex : 04)" required>
                    <input name="position" class="form-control form-control-sm" placeholder="Position">
                    <input name="cote" class="form-control form-control-sm" placeholder="Cote de rangement">
                    <button class="btn btn-sm btn-warning">Ajouter</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endcan

<x-erreurs />

@forelse($bibliotheques as $bibliotheque)
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-transparent border-0 pt-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-building-columns me-2" style="color: var(--accent-gold);"></i>
                {{ $bibliotheque->nom }} <span class="badge bg-secondary">{{ $bibliotheque->code }}</span>
            </h5>
            @can('localisations.gerer')
                <form action="{{ route('localisations.destroy', ['niveau' => 'bibliotheques', 'id' => $bibliotheque->id]) }}"
                      method="POST" onsubmit="return confirm('Supprimer cette bibliothèque ?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                </form>
            @endcan
        </div>
        <div class="card-body">
            @forelse($bibliotheque->salles as $salle)
                <div class="mb-3 ps-2 border-start border-3" style="border-color: var(--accent-gold) !important;">
                    <div class="fw-semibold d-flex align-items-center gap-2">
                        <i class="fas fa-door-open"></i> {{ $salle->nom }}
                        @can('localisations.gerer')
                            <form action="{{ route('localisations.destroy', ['niveau' => 'salles', 'id' => $salle->id]) }}"
                                  method="POST" onsubmit="return confirm('Supprimer cette salle ?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-link text-danger p-0"><i class="fas fa-trash"></i></button>
                            </form>
                        @endcan
                    </div>

                    <div class="row g-2 mt-1">
                        @forelse($salle->rayons as $rayon)
                            <div class="col-md-6 col-xl-4">
                                <div class="border rounded p-2 h-100">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-semibold small">
                                            <i class="fas fa-bookmark me-1"></i>{{ $rayon->nom }}
                                            <span class="badge bg-light text-dark">{{ $rayon->code }}</span>
                                        </span>
                                        @can('localisations.gerer')
                                            <form action="{{ route('localisations.destroy', ['niveau' => 'rayons', 'id' => $rayon->id]) }}"
                                                  method="POST" onsubmit="return confirm('Supprimer ce rayon ?');">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-link text-danger p-0"><i class="fas fa-trash"></i></button>
                                            </form>
                                        @endcan
                                    </div>
                                    <div class="d-flex flex-wrap gap-1 mt-2">
                                        @forelse($rayon->emplacements as $emplacement)
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis">
                                                Ét. {{ $emplacement->etagere }}{{ $emplacement->position ? ' · '.$emplacement->position : '' }}
                                            </span>
                                        @empty
                                            <span class="small" style="opacity:.6;">Aucun emplacement.</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 small" style="opacity:.6;">Aucun rayon dans cette salle.</div>
                        @endforelse
                    </div>
                </div>
            @empty
                <x-vide message="Aucune salle dans cette bibliothèque." icone="fa-door-open" />
            @endforelse
        </div>
    </div>
@empty
    <div class="card border-0 shadow-sm"><div class="card-body">
        <x-vide message="Aucune bibliothèque enregistrée." icone="fa-building-columns" />
    </div></div>
@endforelse
@endsection
