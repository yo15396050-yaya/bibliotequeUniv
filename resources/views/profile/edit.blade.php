@extends('layouts.dashboard')
@section('title', 'Mon profil')

@section('content')
<x-entete-page titre="Mon profil" icone="fa-user-shield"
    :sous-titre="$user->libelle_role . ' · ' . ($user->matricule ?? '')" />

<x-erreurs />

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <img src="{{ $user->url_photo }}" class="rounded-circle mb-3"
                     style="width:120px;height:120px;object-fit:cover;" alt="">
                <h5 class="mb-1">{{ $user->name }}</h5>
                <div class="mb-3"><x-badge :statut="$user->statut" :texte="$user->libelle_statut" /></div>

                <dl class="row small text-start mb-0">
                    <dt class="col-5">Matricule</dt><dd class="col-7">{{ $user->matricule ?? '—' }}</dd>
                    <dt class="col-5">Profil</dt><dd class="col-7">{{ $user->libelle_role }}</dd>
                    <dt class="col-5">Faculté</dt><dd class="col-7">{{ $user->faculte ?? '—' }}</dd>
                    <dt class="col-5">Filière</dt><dd class="col-7">{{ $user->filiere ?? '—' }}</dd>
                    <dt class="col-5">Niveau</dt><dd class="col-7">{{ $user->niveau ?? '—' }}</dd>
                    @if($user->estEmprunteur())
                        <dt class="col-5">Quota</dt><dd class="col-7">{{ $user->quotaEmprunts() }} ouvrage(s)</dd>
                        <dt class="col-5">Durée d'emprunt</dt><dd class="col-7">{{ $user->dureeEmprunt() }} jours</dd>
                    @endif
                    <dt class="col-5">Inscrit depuis</dt><dd class="col-7">{{ $user->created_at?->format('d/m/Y') }}</dd>
                </dl>
            </div>
        </div>

        @if($user->estEmprunteur())
            @php $motifs = $user->motifsBlocageEmprunt(); @endphp
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body">
                    @if($motifs === [])
                        <div class="text-success small">
                            <i class="fas fa-circle-check me-1"></i>
                            Votre compte vous permet d'emprunter.
                        </div>
                    @else
                        <div class="small">
                            <strong class="text-danger"><i class="fas fa-ban me-1"></i>Emprunts bloqués :</strong>
                            <ul class="mb-0 ps-3 mt-1">
                                @foreach($motifs as $motif)<li>{{ $motif }}</li>@endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3">
                <h5 class="mb-0"><i class="fas fa-pen me-2" style="color:var(--accent-gold);"></i>Mes informations</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nom complet <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Prénom usuel</label>
                            <input type="text" name="prenom" class="form-control" value="{{ old('prenom', $user->prenom) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Adresse e-mail <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Téléphone</label>
                            <input type="text" name="telephone" class="form-control" value="{{ old('telephone', $user->telephone) }}">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Adresse</label>
                            <input type="text" name="adresse" class="form-control" value="{{ old('adresse', $user->adresse) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Photo de profil</label>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                        </div>
                    </div>

                    <hr class="my-4">

                    <h6 class="mb-3"><i class="fas fa-key me-2" style="color:var(--accent-gold);"></i>Changer de mot de passe</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Mot de passe actuel</label>
                            <input type="password" name="current_password" autocomplete="current-password"
                                   class="form-control @error('current_password') is-invalid @enderror">
                            @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nouveau mot de passe</label>
                            <input type="password" name="new_password" autocomplete="new-password"
                                   class="form-control @error('new_password') is-invalid @enderror">
                            @error('new_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">8 caractères minimum.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Confirmation</label>
                            <input type="password" name="new_password_confirmation" autocomplete="new-password" class="form-control">
                        </div>
                    </div>

                    <div class="mt-4">
                        <button class="btn btn-warning"><i class="fas fa-save me-1"></i> Enregistrer les modifications</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
