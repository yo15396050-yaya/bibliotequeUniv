@extends('layouts.dashboard')
@section('title', 'Modifier ' . $user->name)

@section('content')
<x-entete-page :titre="'Modifier : ' . $user->name" icone="fa-user-pen" :sous-titre="$user->libelle_role">
    <a href="{{ route('users.show', $user) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</x-entete-page>

<x-erreurs />

<div class="card border-0 shadow-sm mb-3"><div class="card-body">
    <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('users._form')
        <div class="mt-4 d-flex gap-2">
            <button class="btn btn-warning"><i class="fas fa-save me-1"></i> Enregistrer</button>
            <a href="{{ route('users.show', $user) }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div></div>

@can('gererRoles', $user)
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-transparent border-0 pt-3">
        <h5 class="mb-0"><i class="fas fa-user-shield me-2" style="color:var(--accent-gold);"></i>Rôles et permissions</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('users.roles', $user) }}" method="POST" class="row g-3">
            @csrf @method('PUT')
            <div class="col-md-4">
                <label class="form-label">Profil principal</label>
                <select name="role" class="form-select">
                    @foreach($roles as $cle => $libelle)
                        <option value="{{ $cle }}" @selected($user->role === $cle)>{{ $libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-8">
                <label class="form-label">Rôles additionnels</label>
                <div class="d-flex flex-wrap gap-3">
                    @foreach($rolesAdditionnels as $roleAdditionnel)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="roles[]"
                                   value="{{ $roleAdditionnel->id }}" id="role-{{ $roleAdditionnel->id }}"
                                   @checked($user->roles->contains($roleAdditionnel->id))>
                            <label class="form-check-label" for="role-{{ $roleAdditionnel->id }}">{{ $roleAdditionnel->libelle }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-12"><button class="btn btn-outline-primary"><i class="fas fa-shield-halved me-1"></i> Mettre à jour les rôles</button></div>
        </form>
    </div>
</div>
@endcan

@can('update', $user)
<div class="card border-0 shadow-sm">
    <div class="card-body d-flex flex-wrap gap-2 align-items-center">
        <form action="{{ route('users.mot-de-passe', $user) }}" method="POST"
              onsubmit="return confirm('Réinitialiser le mot de passe de cet utilisateur ?');">
            @csrf @method('PUT')
            <button class="btn btn-outline-dark"><i class="fas fa-key me-1"></i> Réinitialiser le mot de passe</button>
        </form>
        <form action="{{ route('users.toggle-status', $user) }}" method="POST">
            @csrf @method('PUT')
            <button class="btn btn-outline-{{ $user->actif ? 'danger' : 'success' }}">
                <i class="fas fa-{{ $user->actif ? 'user-lock' : 'user-check' }} me-1"></i>
                {{ $user->actif ? 'Désactiver le compte' : 'Activer le compte' }}
            </button>
        </form>
        @can('delete', $user)
            <form action="{{ route('users.destroy', $user) }}" method="POST"
                  onsubmit="return confirm('Supprimer définitivement ce compte ?');">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger"><i class="fas fa-trash me-1"></i> Supprimer</button>
            </form>
        @endcan
    </div>
</div>
@endcan
@endsection
