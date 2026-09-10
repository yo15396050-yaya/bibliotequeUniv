@extends('layouts.dashboard')
@section('title', 'Guichet de retour')

@section('content')
<x-entete-page titre="Guichet de retour" icone="fa-barcode"
    sous-titre="Scannez le code-barres de l'exemplaire : recherche → validation → résultat.">
    <a href="{{ route('emprunts.create') }}" class="btn btn-outline-secondary"><i class="fas fa-hand-holding me-1"></i> Enregistrer un emprunt</a>
</x-entete-page>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form method="GET" action="{{ route('emprunts.guichet') }}" id="formulaire-scan">
                    <label class="form-label fw-semibold">Code-barres de l'exemplaire</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                        <input type="text" name="code_barre" id="champ-code-barre" class="form-control"
                               value="{{ request('code_barre') }}" placeholder="Scanner ou saisir…" autofocus autocomplete="off">
                        <button class="btn btn-warning">Rechercher</button>
                    </div>
                    <div class="form-text">
                        Le lecteur de code-barres se comporte comme un clavier : la recherche se lance automatiquement.
                    </div>
                </form>

                @if($erreur)
                    <div class="alert alert-danger border-0 mt-3 mb-0">
                        <i class="fas fa-circle-exclamation me-2"></i>{{ $erreur }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        @if($emprunt)
            @php $retard = $emprunt->joursRetard(); @endphp
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Emprunt trouvé</h5>
                    <x-badge :statut="$emprunt->statut" />
                </div>
                <div class="card-body">
                    <dl class="row small">
                        <dt class="col-4">Ouvrage</dt><dd class="col-8 fw-semibold">{{ $emprunt->livre?->titre }}</dd>
                        <dt class="col-4">Exemplaire</dt><dd class="col-8"><code>{{ $emprunt->exemplaire?->code_barre }}</code></dd>
                        <dt class="col-4">Usager</dt>
                        <dd class="col-8">{{ $emprunt->user?->name }} <span style="opacity:.65;">({{ $emprunt->user?->matricule }})</span></dd>
                        <dt class="col-4">Emprunté le</dt><dd class="col-8">{{ $emprunt->date_emprunt?->format('d/m/Y') }}</dd>
                        <dt class="col-4">Échéance</dt><dd class="col-8">{{ $emprunt->date_retour_prevue?->format('d/m/Y') }}</dd>
                    </dl>

                    @if($retard > 0)
                        <div class="alert alert-danger border-0">
                            <i class="fas fa-triangle-exclamation me-2"></i>
                            <strong>{{ $retard }} jour(s) de retard</strong> — pénalité calculée :
                            {{ \App\Support\Parametres::formaterMontant($emprunt->calculerPenaliteRetard()) }}
                        </div>
                    @else
                        <div class="alert alert-success border-0"><i class="fas fa-circle-check me-2"></i>Retour dans les délais.</div>
                    @endif

                    <form action="{{ route('emprunts.retour', $emprunt) }}" method="POST" class="row g-3">
                        @csrf
                        <div class="col-md-5">
                            <label class="form-label">État de l'exemplaire</label>
                            <select name="etat_retour" class="form-select">
                                <option value="bon">Bon état</option>
                                <option value="neuf">Neuf</option>
                                <option value="moyen">État moyen</option>
                                <option value="mauvais">Endommagé (pénalité)</option>
                                <option value="perdu">Perdu (pénalité)</option>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label">Observation</label>
                            <input type="text" name="observation" class="form-control" placeholder="Facultatif">
                        </div>
                        <div class="col-12">
                            <button class="btn btn-success btn-lg w-100" id="bouton-retour">
                                <i class="fas fa-rotate-left me-1"></i> Enregistrer le retour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <div class="card border-0 shadow-sm h-100"><div class="card-body d-flex align-items-center justify-content-center">
                <x-vide message="Scannez un code-barres pour afficher l'emprunt correspondant." icone="fa-barcode" />
            </div></div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Le lecteur de code-barres termine sa saisie par « Entrée » : soumission
    // immédiate, sans clic supplémentaire.
    const champ = document.getElementById('champ-code-barre');
    const formulaire = document.getElementById('formulaire-scan');

    champ?.addEventListener('keydown', e => {
        if (e.key === 'Enter' && champ.value.trim() !== '') {
            formulaire.submit();
        }
    });

    // Feedback immédiat : le bouton se désactive pour éviter un double retour.
    document.getElementById('bouton-retour')?.closest('form')?.addEventListener('submit', function () {
        const bouton = document.getElementById('bouton-retour');
        bouton.disabled = true;
        bouton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enregistrement…';
    });
</script>
@endpush
