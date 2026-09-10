@extends('layouts.dashboard')
@section('title', 'Paramètres')

@section('content')
<x-entete-page titre="Paramètres du système" icone="fa-sliders"
    sous-titre="Toutes les règles métier de la bibliothèque sont configurables ici.">
    <a href="{{ route('settings.roles') }}" class="btn btn-outline-secondary"><i class="fas fa-user-shield me-1"></i> Rôles</a>
    <a href="{{ route('settings.annees') }}" class="btn btn-outline-secondary"><i class="fas fa-calendar me-1"></i> Années académiques</a>
    <form action="{{ route('settings.vider-cache') }}" method="POST">
        @csrf
        <button class="btn btn-outline-dark"><i class="fas fa-broom me-1"></i> Vider le cache</button>
    </form>
</x-entete-page>

<x-erreurs />

<div class="row g-3">
    <div class="col-lg-3">
        <div class="list-group shadow-sm">
            @foreach($groupes as $g)
                <a href="{{ route('settings.index', ['groupe' => $g]) }}"
                   class="list-group-item list-group-item-action {{ $groupe === $g ? 'active' : '' }}">
                    <i class="fas {{ match($g) {
                        'emprunt' => 'fa-hand-holding',
                        'renouvellement' => 'fa-arrows-rotate',
                        'penalite' => 'fa-money-bill-wave',
                        'reservation' => 'fa-bookmark',
                        'notification' => 'fa-bell',
                        'document' => 'fa-file-pdf',
                        default => 'fa-building-columns',
                    } }} me-2"></i>{{ ucfirst($g) }}
                </a>
            @endforeach
        </div>
    </div>

    <div class="col-lg-9">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3">
                <h5 class="mb-0">{{ ucfirst($groupe) }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('settings.update') }}" method="POST">
                    @csrf @method('PUT')
                    <input type="hidden" name="groupe" value="{{ $groupe }}">

                    @foreach($definitions as $cle => $meta)
                        @php
                            $champ = str_replace('.', '__', $cle);
                            $valeur = $valeurs[$cle] ?? $meta['valeur'];
                        @endphp
                        <div class="row align-items-center py-2 border-bottom">
                            <div class="col-md-7">
                                <label class="form-label mb-0" for="p-{{ $champ }}">{{ $meta['libelle'] }}</label>
                                <div class="small" style="opacity:.55;"><code>{{ $cle }}</code></div>
                            </div>
                            <div class="col-md-5">
                                @if($meta['type'] === 'boolean')
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="parametres[{{ $champ }}]" value="0">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                               id="p-{{ $champ }}" name="parametres[{{ $champ }}]" value="1"
                                               @checked(filter_var($valeur, FILTER_VALIDATE_BOOLEAN))>
                                    </div>
                                @elseif(in_array($meta['type'], ['integer', 'decimal']))
                                    <input type="number" step="{{ $meta['type'] === 'decimal' ? '0.01' : '1' }}" min="0"
                                           class="form-control" id="p-{{ $champ }}"
                                           name="parametres[{{ $champ }}]" value="{{ $valeur }}">
                                @else
                                    <input type="text" class="form-control" id="p-{{ $champ }}"
                                           name="parametres[{{ $champ }}]" value="{{ $valeur }}">
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <div class="mt-4">
                        <button class="btn btn-warning"><i class="fas fa-save me-1"></i> Enregistrer les paramètres</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
