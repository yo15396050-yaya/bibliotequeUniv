@extends('layouts.dashboard')
@section('title', 'Nouvel emprunt')

@section('content')
<x-entete-page titre="Enregistrer un emprunt" icone="fa-hand-holding"
    sous-titre="Sélection de l'usager → ouvrage ou scan → exemplaire → vérification automatique → validation.">
    <a href="{{ route('emprunts.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</x-entete-page>

<x-erreurs />

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="{{ route('emprunts.store') }}" method="POST" id="formulaire-emprunt">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">1. Usager <span class="text-danger">*</span></label>
                            <select name="user_id" id="champ-usager" class="form-select @error('user_id') is-invalid @enderror" required>
                                <option value="">— Sélectionner un usager —</option>
                                @foreach($utilisateurs as $utilisateur)
                                    <option value="{{ $utilisateur->id }}"
                                        @selected(old('user_id', request('user_id')) == $utilisateur->id)>
                                        {{ $utilisateur->name }} — {{ $utilisateur->matricule }}
                                        ({{ \App\Models\User::ROLES[$utilisateur->role] ?? $utilisateur->role }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">2. Ouvrage <span class="text-danger">*</span></label>
                            <select name="livre_id" id="champ-livre" class="form-select @error('livre_id') is-invalid @enderror" required>
                                <option value="">— Sélectionner un ouvrage —</option>
                                @foreach($livres as $livre)
                                    <option value="{{ $livre->id }}" data-disponibles="{{ $livre->exemplaires_disponibles }}"
                                        @selected(old('livre_id', $livrePreSelectionne->id ?? ($exemplairePreSelectionne->livre_id ?? null)) == $livre->id)>
                                        {{ $livre->titre }} — {{ $livre->exemplaires_disponibles }} dispo.
                                    </option>
                                @endforeach
                            </select>
                            @error('livre_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">3. Exemplaire</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                <input type="text" id="champ-code-barre" class="form-control"
                                       value="{{ $exemplairePreSelectionne->code_barre ?? '' }}"
                                       placeholder="Scanner un code-barres…" autocomplete="off">
                            </div>
                            <input type="hidden" name="exemplaire_id" id="champ-exemplaire"
                                   value="{{ old('exemplaire_id', $exemplairePreSelectionne->id ?? '') }}">
                            <div class="form-text" id="retour-scan">
                                Facultatif : sans code-barres, le premier exemplaire disponible est attribué.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">4. Date limite de retour</label>
                            <input type="date" name="date_retour_prevue" class="form-control @error('date_retour_prevue') is-invalid @enderror"
                                   value="{{ old('date_retour_prevue') }}" min="{{ now()->addDay()->toDateString() }}">
                            @error('date_retour_prevue')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Vide : calculée automatiquement selon le profil de l'usager.</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" rows="2" class="form-control" placeholder="Observation éventuelle…">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button class="btn btn-warning btn-lg" id="bouton-valider">
                            <i class="fas fa-check me-1"></i> Valider l'emprunt
                        </button>
                        <a href="{{ route('emprunts.index') }}" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3">
                <h6 class="mb-0"><i class="fas fa-circle-info me-2" style="color: var(--accent-gold);"></i>Contrôles automatiques</h6>
            </div>
            <div class="card-body small">
                <p style="opacity:.8;">Avant validation, le système vérifie&nbsp;:</p>
                <ul class="ps-3 mb-0" style="opacity:.85;">
                    <li>que le compte de l'usager est actif ;</li>
                    <li>qu'il n'a pas atteint son quota d'emprunts ;</li>
                    <li>qu'il n'a aucun emprunt en retard ;</li>
                    <li>qu'aucune pénalité bloquante n'est due ;</li>
                    <li>que l'exemplaire est bien disponible ;</li>
                    <li>qu'aucune réservation prioritaire ne porte sur l'ouvrage.</li>
                </ul>
                <hr>
                <p class="mb-0" style="opacity:.7;">
                    La date limite est calculée à partir de la durée d'emprunt configurée pour le profil de l'usager.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Scan d'un code-barres : recherche instantanée de l'exemplaire.
    const champCode = document.getElementById('champ-code-barre');
    const champExemplaire = document.getElementById('champ-exemplaire');
    const champLivre = document.getElementById('champ-livre');
    const retour = document.getElementById('retour-scan');

    function rechercherExemplaire() {
        const code = champCode.value.trim();
        if (code === '') {
            champExemplaire.value = '';
            retour.className = 'form-text';
            retour.textContent = "Facultatif : sans code-barres, le premier exemplaire disponible est attribué.";
            return;
        }

        retour.className = 'form-text';
        retour.textContent = 'Recherche…';

        fetch("{{ route('exemplaires.recherche') }}?code_barre=" + encodeURIComponent(code), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(async reponse => ({ ok: reponse.ok, donnees: await reponse.json() }))
            .then(({ ok, donnees }) => {
                if (!ok || !donnees.trouve) {
                    champExemplaire.value = '';
                    retour.className = 'form-text text-danger';
                    retour.textContent = donnees.message ?? 'Exemplaire introuvable.';
                    return;
                }

                const ex = donnees.exemplaire;
                champExemplaire.value = ex.id;
                champLivre.value = ex.livre_id;

                if (ex.disponible) {
                    retour.className = 'form-text text-success';
                    retour.textContent = `« ${ex.titre} » — exemplaire ${ex.code_barre} disponible.`;
                } else {
                    retour.className = 'form-text text-danger';
                    retour.textContent = `« ${ex.titre} » — exemplaire ${ex.libelle_statut.toLowerCase()}.`;
                }
            })
            .catch(() => {
                retour.className = 'form-text text-danger';
                retour.textContent = 'La recherche a échoué. Réessayez.';
            });
    }

    let minuteur = null;
    champCode.addEventListener('input', () => {
        clearTimeout(minuteur);
        minuteur = setTimeout(rechercherExemplaire, 300);
    });
    champCode.addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); rechercherExemplaire(); }
    });

    // Le choix manuel d'un autre ouvrage annule l'exemplaire scanné.
    champLivre.addEventListener('change', () => {
        champExemplaire.value = '';
        champCode.value = '';
    });

    // Feedback immédiat, protection contre le double envoi.
    document.getElementById('formulaire-emprunt').addEventListener('submit', function () {
        const bouton = document.getElementById('bouton-valider');
        bouton.disabled = true;
        bouton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enregistrement…';
    });

    @if($exemplairePreSelectionne)
        rechercherExemplaire();
    @endif
</script>
@endpush
