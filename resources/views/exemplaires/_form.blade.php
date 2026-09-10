<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Ouvrage <span class="text-danger">*</span></label>
        <select name="livre_id" class="form-select @error('livre_id') is-invalid @enderror" required>
            <option value="">— Sélectionner un ouvrage —</option>
            @foreach($livres as $livre)
                <option value="{{ $livre->id }}"
                    @selected(old('livre_id', $exemplaire->livre_id ?? ($livrePreSelectionne->id ?? null)) == $livre->id)>
                    {{ $livre->titre }} ({{ $livre->isbn }})
                </option>
            @endforeach
        </select>
        @error('livre_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Code-barres</label>
        <input type="text" name="code_barre" class="form-control @error('code_barre') is-invalid @enderror"
               value="{{ old('code_barre', $exemplaire->code_barre ?? '') }}" placeholder="Généré automatiquement">
        @error('code_barre')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">Laisser vide pour une génération automatique.</div>
    </div>

    <div class="col-md-3">
        <label class="form-label">N° d'inventaire</label>
        <input type="text" name="numero_inventaire" class="form-control @error('numero_inventaire') is-invalid @enderror"
               value="{{ old('numero_inventaire', $exemplaire->numero_inventaire ?? '') }}">
        @error('numero_inventaire')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">État <span class="text-danger">*</span></label>
        <select name="etat" class="form-select" required>
            @foreach(\App\Models\Exemplaire::ETATS as $cle => $libelle)
                <option value="{{ $cle }}" @selected(old('etat', $exemplaire->etat ?? 'bon') === $cle)>{{ $libelle }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">Statut <span class="text-danger">*</span></label>
        <select name="statut" class="form-select" required>
            @foreach(\App\Models\Exemplaire::STATUTS as $cle => $libelle)
                <option value="{{ $cle }}" @selected(old('statut', $exemplaire->statut ?? 'disponible') === $cle)>{{ $libelle }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Emplacement</label>
        <select name="emplacement_id" class="form-select">
            <option value="">— Non rangé —</option>
            @foreach($emplacements as $emplacement)
                <option value="{{ $emplacement->id }}" @selected(old('emplacement_id', $exemplaire->emplacement_id ?? null) == $emplacement->id)>
                    {{ $emplacement->chemin_complet }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">Date d'acquisition</label>
        <input type="date" name="date_acquisition" class="form-control"
               value="{{ old('date_acquisition', optional($exemplaire->date_acquisition ?? null)->format('Y-m-d')) }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Prix d'achat</label>
        <div class="input-group">
            <input type="number" step="0.01" min="0" name="prix_achat" class="form-control"
                   value="{{ old('prix_achat', $exemplaire->prix_achat ?? '') }}">
            <span class="input-group-text">{{ \App\Support\Parametres::devise() }}</span>
        </div>
    </div>

    @if(!isset($exemplaire))
        <div class="col-md-3">
            <label class="form-label">Quantité à créer</label>
            <input type="number" min="1" max="100" name="quantite" class="form-control" value="{{ old('quantite', 1) }}">
            <div class="form-text">Au-delà de 1, les codes-barres sont générés automatiquement.</div>
        </div>
    @endif

    <div class="col-12">
        <label class="form-label">Notes</label>
        <textarea name="notes" rows="2" class="form-control">{{ old('notes', $exemplaire->notes ?? '') }}</textarea>
    </div>
</div>
