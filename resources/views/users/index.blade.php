@extends('layouts.dashboard')
@section('title', 'Comptes utilisateurs')

@section('content')
<x-entete-page titre="Comptes utilisateurs" icone="fa-users-gear" :sous-titre="$users->total() . ' compte(s)'">
    @can('usagers.creer')
        <a href="{{ route('users.create') }}" class="btn btn-warning"><i class="fas fa-plus me-1"></i> Nouveau compte</a>
    @endcan
</x-entete-page>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-5">
                <input type="search" name="search" class="form-control" value="{{ request('search') }}"
                       placeholder="Nom, matricule, email, téléphone...">
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select">
                    <option value="">Tous les profils</option>
                    @foreach($roles as $cle => $libelle)
                        <option value="{{ $cle }}" @selected(request('role') === $cle)>{{ $libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="statut" class="form-select">
                    <option value="">Tous les statuts</option>
                    @foreach($statuts as $cle => $libelle)
                        <option value="{{ $cle }}" @selected(request('statut') === $cle)>{{ $libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-outline-secondary"><i class="fas fa-filter"></i></button></div>
            <div class="col-auto"><a href="{{ route('users.index') }}" class="btn btn-link">Réinitialiser</a></div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Utilisateur</th><th>Matricule</th><th>Profil</th><th>Statut</th><th class="text-center">Emprunts</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @forelse($users as $utilisateur)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $utilisateur->url_photo }}" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;" alt="">
                                    <div class="min-w-0">
                                        <a href="{{ route('users.show', $utilisateur) }}" class="fw-semibold text-decoration-none d-block text-truncate" style="color:var(--text-main);">
                                            {{ $utilisateur->name }}
                                        </a>
                                        <small style="opacity:.65;">{{ $utilisateur->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $utilisateur->matricule ?? '—' }}</td>
                            <td><span class="badge bg-secondary-subtle text-secondary-emphasis">{{ $utilisateur->libelle_role }}</span></td>
                            <td>
                                <x-badge :statut="$utilisateur->statut" :texte="$utilisateur->libelle_statut" />
                                @unless($utilisateur->actif)<span class="badge bg-danger">Désactivé</span>@endunless
                            </td>
                            <td class="text-center">{{ $utilisateur->emprunts_count }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('users.show', $utilisateur) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-eye"></i></a>
                                @can('update', $utilisateur)
                                    <a href="{{ route('users.edit', $utilisateur) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-pen"></i></a>
                                    <form action="{{ route('users.toggle-status', $utilisateur) }}" method="POST" class="d-inline">
                                        @csrf @method('PUT')
                                        <button class="btn btn-sm btn-outline-{{ $utilisateur->actif ? 'danger' : 'success' }}"
                                                title="{{ $utilisateur->actif ? 'Désactiver' : 'Activer' }}">
                                            <i class="fas fa-{{ $utilisateur->actif ? 'user-lock' : 'user-check' }}"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-vide message="Aucun compte ne correspond à ces critères." icone="fa-users" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $users->links() }}
    </div>
</div>
@endsection
