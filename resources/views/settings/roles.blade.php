@extends('layouts.dashboard')
@section('title', 'Rôles et permissions')

@section('content')
<x-entete-page titre="Rôles et permissions" icone="fa-user-shield"
    sous-titre="Matrice RBAC : cochez les permissions accordées à chaque rôle.">
    <a href="{{ route('settings.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Paramètres</a>
</x-entete-page>

<div class="accordion shadow-sm" id="accordeon-roles">
    @foreach($roles as $role)
        <div class="accordion-item border-0">
            <h2 class="accordion-header">
                <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button"
                        data-bs-toggle="collapse" data-bs-target="#role-{{ $role->id }}">
                    <strong>{{ $role->libelle }}</strong>
                    <span class="badge bg-secondary ms-2">{{ $role->users_count }} compte(s)</span>
                    <span class="badge bg-warning text-dark ms-2">{{ $role->permissions->count() }} permission(s)</span>
                </button>
            </h2>
            <div id="role-{{ $role->id }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                 data-bs-parent="#accordeon-roles">
                <div class="accordion-body">
                    <p class="small" style="opacity:.75;">{{ $role->description }}</p>

                    @if($role->nom === 'admin')
                        <div class="alert alert-info border-0 mb-0">
                            <i class="fas fa-circle-info me-2"></i>
                            L'administrateur dispose de toutes les permissions : elles ne peuvent pas être retirées.
                        </div>
                    @else
                        <form action="{{ route('settings.roles.update', $role) }}" method="POST">
                            @csrf @method('PUT')
                            <div class="row g-3">
                                @foreach($catalogue as $module => $permissions)
                                    <div class="col-md-6 col-xl-4">
                                        <div class="border rounded p-2 h-100">
                                            <div class="fw-semibold small text-uppercase mb-2" style="color: var(--accent-gold);">{{ $module }}</div>
                                            @foreach($permissions as $nom => $libelle)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="permissions[]"
                                                           value="{{ $nom }}" id="{{ $role->id }}-{{ $nom }}"
                                                           @checked($role->permissions->contains('nom', $nom))>
                                                    <label class="form-check-label small" for="{{ $role->id }}-{{ $nom }}">{{ $libelle }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button class="btn btn-warning mt-3"><i class="fas fa-save me-1"></i> Enregistrer</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
