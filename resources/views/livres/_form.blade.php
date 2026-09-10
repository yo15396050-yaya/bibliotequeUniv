@php $l = $livre ?? null; @endphp

<div class="row g-4">
    {{-- Identification --}}
    <div class="col-12">
        <h6 class="text-uppercase small fw-bold mb-3" style="color: var(--accent-gold); letter-spacing:1px;">
            <i class="fas fa-fingerprint me-1"></i> Identification
        </h6>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">ISBN <span class="text-danger">*</span></label>
                <input type="text" name="isbn" class="form-control @error('isbn') is-invalid @enderror"
                       value="{{ old('isbn', $l->isbn ?? '') }}" required>
                @error('isbn')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Titre <span class="text-danger">*</span></label>
                <input type="text" name="titre" class="form-control @error('titre') is-invalid @enderror"
                       value="{{ old('titre', $l->titre ?? '') }}" required>
                @error('titre')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Type de document <span class="text-danger">*</span></label>
                <select name="type_document" class="form-select @error('type_document') is-invalid @enderror" required>
                    @foreach($types as $cle => $libelle)
                        <option value="{{ $cle }}" @selected(old('type_document', $l->type_document ?? 'livre') === $cle)>{{ $libelle }}</option>
                    @endforeach
                </select>
                @error('type_document')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Sous-titre</label>
                <input type="text" name="sous_titre" class="form-control"
                       value="{{ old('sous_titre', $l->sous_titre ?? '') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Année de publication <span class="text-danger">*</span></label>
                <input type="number" name="annee_publication" min="1400" max="{{ date('Y') + 1 }}"
                       class="form-control @error('annee_publication') is-invalid @enderror"
                       value="{{ old('annee_publication', $l->annee_publication ?? date('Y')) }}" required>
                @error('annee_publication')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Édition</label>
                <input type="text" name="edition" class="form-control" placeholder="Ex : 3e édition"
                       value="{{ old('edition', $l->edition ?? '') }}">
            </div>
        </div>
    </div>

    {{-- Responsabilités --}}
    <div class="col-12">
        <h6 class="text-uppercase small fw-bold mb-3" style="color: var(--accent-gold); letter-spacing:1px;">
            <i class="fas fa-feather-pointed me-1"></i> Auteurs et éditeur
        </h6>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Auteurs référencés</label>
                <select name="auteurs[]" class="form-select" multiple size="5">
                    @foreach($auteursDisponibles as $auteur)
                        <option value="{{ $auteur->id }}"
                            @selected(collect(old('auteurs', $l?->auteurs->pluck('id')->all() ?? []))->contains($auteur->id))>
                            {{ trim($auteur->prenom.' '.$auteur->nom) }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text">
                    Maintenez Ctrl (ou Cmd) pour en sélectionner plusieurs.
                    <a href="{{ route('auteurs.create') }}" target="_blank">Créer un auteur</a>.
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Auteur(s) — libellé libre <span class="text-danger">*</span></label>
                    <input type="text" name="auteur" class="form-control @error('auteur') is-invalid @enderror"
                           value="{{ old('auteur', $l->auteur ?? '') }}" required>
                    @error('auteur')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Renseigné automatiquement si vous sélectionnez des auteurs référencés.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Éditeur référencé</label>
                    <select name="editeur_id" class="form-select">
                        <option value="">— Aucun —</option>
                        @foreach($editeurs as $editeur)
                            <option value="{{ $editeur->id }}" @selected(old('editeur_id', $l->editeur_id ?? null) == $editeur->id)>
                                {{ $editeur->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Éditeur — libellé libre <span class="text-danger">*</span></label>
                    <input type="text" name="editeur" class="form-control @error('editeur') is-invalid @enderror"
                           value="{{ old('editeur', $l->editeur ?? '') }}" required>
                    @error('editeur')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Classification --}}
    <div class="col-12">
        <h6 class="text-uppercase small fw-bold mb-3" style="color: var(--accent-gold); letter-spacing:1px;">
            <i class="fas fa-tags me-1"></i> Classification
        </h6>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Catégorie référencée</label>
                <select name="categorie_id" class="form-select">
                    <option value="">— Aucune —</option>
                    @foreach($categoriesRef as $categorie)
                        <option value="{{ $categorie->id }}" @selected(old('categorie_id', $l->categorie_id ?? null) == $categorie->id)>
                            {{ $categorie->nom_complet }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Catégorie — libellé <span class="text-danger">*</span></label>
                <input type="text" name="categorie" class="form-control @error('categorie') is-invalid @enderror"
                       value="{{ old('categorie', $l->categorie ?? '') }}" required>
                @error('categorie')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Niveau académique</label>
                <select name="niveau_academique" class="form-select">
                    <option value="">— Non précisé —</option>
                    @foreach($niveaux as $cle => $libelle)
                        <option value="{{ $cle }}" @selected(old('niveau_academique', $l->niveau_academique ?? '') === $cle)>{{ $libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Domaine</label>
                <input type="text" name="domaine" class="form-control" value="{{ old('domaine', $l->domaine ?? '') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Langue <span class="text-danger">*</span></label>
                <input type="text" name="langue" class="form-control @error('langue') is-invalid @enderror"
                       value="{{ old('langue', $l->langue ?? 'Français') }}" list="liste-langues" required>
                <datalist id="liste-langues">
                    @foreach(['Français', 'Anglais', 'Espagnol', 'Arabe', 'Allemand'] as $langue)
                        <option value="{{ $langue }}"></option>
                    @endforeach
                </datalist>
                @error('langue')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Mots-clés</label>
                <input type="text" name="mots_cles" class="form-control" placeholder="séparés par des virgules"
                       value="{{ old('mots_cles', $l->mots_cles ?? '') }}">
            </div>
        </div>
    </div>

    {{-- Contenu --}}
    <div class="col-12">
        <h6 class="text-uppercase small fw-bold mb-3" style="color: var(--accent-gold); letter-spacing:1px;">
            <i class="fas fa-align-left me-1"></i> Contenu
        </h6>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Nombre de pages <span class="text-danger">*</span></label>
                <input type="number" name="nombre_pages" min="1" class="form-control @error('nombre_pages') is-invalid @enderror"
                       value="{{ old('nombre_pages', $l->nombre_pages ?? '') }}" required>
                @error('nombre_pages')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-9">
                <label class="form-label">Résumé</label>
                <textarea name="resume" rows="3" class="form-control">{{ old('resume', $l->resume ?? '') }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Description détaillée</label>
                <textarea name="description" rows="4" class="form-control">{{ old('description', $l->description ?? '') }}</textarea>
            </div>
        </div>
    </div>

    {{-- Localisation et exemplaires --}}
    <div class="col-12">
        <h6 class="text-uppercase small fw-bold mb-3" style="color: var(--accent-gold); letter-spacing:1px;">
            <i class="fas fa-map-location-dot me-1"></i> Localisation et exemplaires
        </h6>
        <div class="row g-3">
            <div class="col-md-5">
                <label class="form-label">Emplacement précis</label>
                <select name="emplacement_id" class="form-select">
                    <option value="">— Non rangé —</option>
                    @foreach($emplacements as $emplacement)
                        <option value="{{ $emplacement->id }}" @selected(old('emplacement_id', $l->emplacement_id ?? null) == $emplacement->id)>
                            {{ $emplacement->chemin_complet }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Cote / rayon <span class="text-danger">*</span></label>
                <input type="text" name="emplacement_rayon" class="form-control @error('emplacement_rayon') is-invalid @enderror"
                       value="{{ old('emplacement_rayon', $l->emplacement_rayon ?? '') }}" required>
                @error('emplacement_rayon')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2">
                <label class="form-label">Exemplaires <span class="text-danger">*</span></label>
                <input type="number" name="exemplaires_totaux" min="1" max="999"
                       class="form-control @error('exemplaires_totaux') is-invalid @enderror"
                       value="{{ old('exemplaires_totaux', $l->exemplaires_totaux ?? 1) }}" required>
                @error('exemplaires_totaux')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            @if(isset($livre))
                <div class="col-md-2">
                    <label class="form-label">Statut <span class="text-danger">*</span></label>
                    <select name="statut" class="form-select" required>
                        @foreach(['disponible', 'emprunté', 'réservé', 'perdu', 'en réparation'] as $statut)
                            <option value="{{ $statut }}" @selected(old('statut', $l->statut ?? 'disponible') === $statut)>
                                {{ ucfirst($statut) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="generer_exemplaires"
                               value="1" id="generer" @checked(old('generer_exemplaires', true))>
                        <label class="form-check-label small" for="generer">Générer les exemplaires</label>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Fichiers --}}
    <div class="col-12">
        <h6 class="text-uppercase small fw-bold mb-3" style="color: var(--accent-gold); letter-spacing:1px;">
            <i class="fas fa-paperclip me-1"></i> Couverture et version numérique
        </h6>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Image de couverture</label>
                <input type="file" name="image_couverture" class="form-control" accept="image/*">
                @if(!empty($l?->image_couverture))
                    <img src="{{ asset('storage/'.$l->image_couverture) }}" class="rounded mt-2" style="height:70px;" alt="">
                @endif
            </div>
            <div class="col-md-4">
                <label class="form-label">Fichier numérique</label>
                <input type="file" name="fichier_numerique" class="form-control"
                       accept=".{{ implode(',.', \App\Services\DocumentService::formatsAutorises()) }}">
                <div class="form-text">
                    Formats : {{ implode(', ', \App\Services\DocumentService::formatsAutorises()) }} —
                    {{ round(\App\Services\DocumentService::tailleMaxKo() / 1024) }} Mo maximum.
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Visibilité du document</label>
                <select name="visibilite_document" class="form-select">
                    @foreach($visibilites as $cle => $libelle)
                        <option value="{{ $cle }}" @selected(old('visibilite_document', 'authentifie') === $cle)>{{ $libelle }}</option>
                    @endforeach
                </select>
                <div class="form-check mt-2">
                    <input type="hidden" name="autoriser_telechargement" value="0">
                    <input class="form-check-input" type="checkbox" name="autoriser_telechargement" value="1"
                           id="telechargeable" @checked(old('autoriser_telechargement', $l->autoriser_telechargement ?? true))>
                    <label class="form-check-label small" for="telechargeable">Téléchargement autorisé</label>
                </div>
            </div>
        </div>
    </div>
</div>
