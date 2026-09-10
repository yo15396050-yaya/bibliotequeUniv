<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nom <span class="text-danger">*</span></label>
        <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
               value="{{ old('nom', $auteur->nom ?? '') }}" required>
        @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Prénom</label>
        <input type="text" name="prenom" class="form-control @error('prenom') is-invalid @enderror"
               value="{{ old('prenom', $auteur->prenom ?? '') }}">
        @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Nationalité</label>
        <input type="text" name="nationalite" class="form-control"
               value="{{ old('nationalite', $auteur->nationalite ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Date de naissance</label>
        <input type="date" name="date_naissance" class="form-control"
               value="{{ old('date_naissance', optional($auteur->date_naissance ?? null)->format('Y-m-d')) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Date de décès</label>
        <input type="date" name="date_deces" class="form-control"
               value="{{ old('date_deces', optional($auteur->date_deces ?? null)->format('Y-m-d')) }}">
    </div>
    <div class="col-12">
        <label class="form-label">Biographie</label>
        <textarea name="biographie" rows="4" class="form-control">{{ old('biographie', $auteur->biographie ?? '') }}</textarea>
    </div>
    <div class="col-md-6">
        <label class="form-label">Photo</label>
        <input type="file" name="photo" class="form-control" accept="image/*">
        @if(!empty($auteur?->photo))
            <img src="{{ asset('storage/' . $auteur->photo) }}" alt="" class="rounded mt-2" style="height:70px;">
        @endif
    </div>
</div>
