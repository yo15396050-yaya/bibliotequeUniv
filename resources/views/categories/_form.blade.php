<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nom <span class="text-danger">*</span></label>
        <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
               value="{{ old('nom', $categorie->nom ?? '') }}" required>
        @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Code <span class="text-danger">*</span></label>
        <input type="text" name="code_categorie" maxlength="10"
               class="form-control @error('code_categorie') is-invalid @enderror"
               value="{{ old('code_categorie', $categorie->code_categorie ?? '') }}" required>
        @error('code_categorie')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Couleur</label>
        <input type="color" name="couleur" class="form-control form-control-color w-100"
               value="{{ old('couleur', $categorie->couleur ?? '#1E3A8A') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Catégorie parente</label>
        <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
            <option value="">— Catégorie principale —</option>
            @foreach($parents as $parent)
                <option value="{{ $parent->id }}" @selected(old('parent_id', $categorie->parent_id ?? null) == $parent->id)>
                    {{ $parent->nom }}
                </option>
            @endforeach
        </select>
        @error('parent_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">Laisser vide pour créer une catégorie de premier niveau.</div>
    </div>
    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" rows="3" class="form-control">{{ old('description', $categorie->description ?? '') }}</textarea>
    </div>
</div>
