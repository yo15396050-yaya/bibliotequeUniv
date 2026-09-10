@php $u = $user ?? null; @endphp
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Nom complet <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $u->name ?? '') }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Prénom</label>
        <input type="text" name="prenom" class="form-control" value="{{ old('prenom', $u->prenom ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Matricule <span class="text-danger">*</span></label>
        <input type="text" name="matricule" class="form-control @error('matricule') is-invalid @enderror"
               value="{{ old('matricule', $u->matricule ?? '') }}" required>
        @error('matricule')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Email <span class="text-danger">*</span></label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $u->email ?? '') }}" required>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Téléphone</label>
        <input type="text" name="telephone" class="form-control" value="{{ old('telephone', $u->telephone ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Date de naissance</label>
        <input type="date" name="date_naissance" class="form-control"
               value="{{ old('date_naissance', optional($u->date_naissance ?? null)->format('Y-m-d')) }}">
    </div>

    @if(!isset($sansRole))
        <div class="col-md-4">
            <label class="form-label">Profil <span class="text-danger">*</span></label>
            <select name="role" class="form-select @error('role') is-invalid @enderror"
                    @disabled(!Auth::user()->peut('usagers.roles')) required>
                @foreach($roles as $cle => $libelle)
                    <option value="{{ $cle }}" @selected(old('role', $u->role ?? 'etudiant') === $cle)>{{ $libelle }}</option>
                @endforeach
            </select>
            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    @endif

    <div class="col-md-4">
        <label class="form-label">Statut <span class="text-danger">*</span></label>
        <select name="statut" class="form-select" required>
            @foreach($statuts as $cle => $libelle)
                <option value="{{ $cle }}" @selected(old('statut', $u->statut ?? 'actif') === $cle)>{{ $libelle }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Année académique</label>
        <select name="annee_academique_id" class="form-select">
            <option value="">— Non renseignée —</option>
            @foreach($anneesAcademiques as $annee)
                <option value="{{ $annee->id }}" @selected(old('annee_academique_id', $u->annee_academique_id ?? null) == $annee->id)>
                    {{ $annee->libelle }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Faculté</label>
        <input type="text" name="faculte" class="form-control" value="{{ old('faculte', $u->faculte ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Département</label>
        <input type="text" name="departement" class="form-control" value="{{ old('departement', $u->departement ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Filière</label>
        <input type="text" name="filiere" class="form-control" value="{{ old('filiere', $u->filiere ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Niveau</label>
        <input type="text" name="niveau" class="form-control" value="{{ old('niveau', $u->niveau ?? '') }}"
               placeholder="Ex : Licence 3">
    </div>
    <div class="col-md-4">
        <label class="form-label">Grade (enseignant)</label>
        <input type="text" name="grade" class="form-control" value="{{ old('grade', $u->grade ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Adresse</label>
        <input type="text" name="adresse" class="form-control" value="{{ old('adresse', $u->adresse ?? '') }}">
    </div>

    <div class="col-md-4">
        <label class="form-label">Photo</label>
        <input type="file" name="photo" class="form-control" accept="image/*">
        @if(!empty($u?->photo))
            <img src="{{ asset('storage/' . $u->photo) }}" class="rounded mt-2" style="height:60px;" alt="">
        @endif
    </div>
    <div class="col-md-4">
        <label class="form-label">Mot de passe</label>
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">Vide : {{ isset($u) ? 'mot de passe inchangé' : 'le matricule sert de mot de passe provisoire' }}.</div>
    </div>
    <div class="col-md-4">
        <label class="form-label">Confirmation</label>
        <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
    </div>
</div>
