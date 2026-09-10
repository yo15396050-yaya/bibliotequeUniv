@php $e = $etudiant ?? null; @endphp
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Nom complet <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $e->name ?? '') }}" placeholder="Ex : Kouassi Awa" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Prénom usuel</label>
        <input type="text" name="prenom" class="form-control" value="{{ old('prenom', $e->prenom ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Matricule <span class="text-danger">*</span></label>
        <input type="text" name="matricule" class="form-control @error('matricule') is-invalid @enderror"
               value="{{ old('matricule', $e->matricule ?? '') }}" placeholder="Ex : ETU-2025-001" required>
        @error('matricule')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Email <span class="text-danger">*</span></label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $e->email ?? '') }}" required>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Téléphone</label>
        <input type="text" name="telephone" class="form-control" value="{{ old('telephone', $e->telephone ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Date de naissance</label>
        <input type="date" name="date_naissance" class="form-control"
               value="{{ old('date_naissance', optional($e->date_naissance ?? null)->format('Y-m-d')) }}">
    </div>

    <div class="col-md-4">
        <label class="form-label">Faculté</label>
        <input type="text" name="faculte" class="form-control" value="{{ old('faculte', $e->faculte ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Département</label>
        <input type="text" name="departement" class="form-control" value="{{ old('departement', $e->departement ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Filière</label>
        <input type="text" name="filiere" class="form-control" value="{{ old('filiere', $e->filiere ?? '') }}"
               list="liste-filieres">
        <datalist id="liste-filieres">
            @foreach(['Informatique','Mathématiques','Droit','Économie','Gestion','Médecine','Sciences','Lettres','Philosophie','Ingénierie'] as $f)
                <option value="{{ $f }}"></option>
            @endforeach
        </datalist>
    </div>

    <div class="col-md-4">
        <label class="form-label">Niveau</label>
        <select name="niveau" class="form-select">
            <option value="">— Non renseigné —</option>
            @foreach(['Licence 1','Licence 2','Licence 3','Master 1','Master 2','Doctorat'] as $n)
                <option value="{{ $n }}" @selected(old('niveau', $e->niveau ?? '') === $n)>{{ $n }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Année académique</label>
        <select name="annee_academique_id" class="form-select">
            <option value="">— Non renseignée —</option>
            @foreach($anneesAcademiques as $annee)
                <option value="{{ $annee->id }}" @selected(old('annee_academique_id', $e->annee_academique_id ?? null) == $annee->id)>
                    {{ $annee->libelle }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Statut <span class="text-danger">*</span></label>
        <select name="statut" class="form-select" required>
            @foreach($statuts as $cle => $libelle)
                <option value="{{ $cle }}" @selected(old('statut', $e->statut ?? 'actif') === $cle)>{{ $libelle }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-8">
        <label class="form-label">Adresse</label>
        <input type="text" name="adresse" class="form-control" value="{{ old('adresse', $e->adresse ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Photo</label>
        <input type="file" name="photo" class="form-control" accept="image/*">
        @if(!empty($e?->photo))
            <img src="{{ asset('storage/' . $e->photo) }}" class="rounded mt-2" style="height:60px;" alt="">
        @endif
    </div>

    <div class="col-md-6">
        <label class="form-label">Mot de passe</label>
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">Vide : {{ isset($e) ? 'inchangé' : 'le matricule sert de mot de passe provisoire' }}.</div>
    </div>
    <div class="col-md-6">
        <label class="form-label">Confirmation</label>
        <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
    </div>
</div>
