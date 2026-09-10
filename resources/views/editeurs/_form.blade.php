<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nom <span class="text-danger">*</span></label>
        <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
               value="{{ old('nom', $editeur->nom ?? '') }}" required>
        @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Pays</label>
        <input type="text" name="pays" class="form-control" value="{{ old('pays', $editeur->pays ?? '') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Site web</label>
        <input type="url" name="site_web" class="form-control @error('site_web') is-invalid @enderror"
               value="{{ old('site_web', $editeur->site_web ?? '') }}" placeholder="https://...">
        @error('site_web')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $editeur->email ?? '') }}">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
