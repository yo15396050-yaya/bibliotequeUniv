@extends('layouts.dashboard')
@section('title', 'Auteurs')

@section('content')
<x-entete-page titre="Auteurs" icone="fa-feather-pointed"
    :sous-titre="$auteurs->total() . ' auteur(s) référencé(s)'">
    @can('auteurs.gerer')
        <a href="{{ route('auteurs.create') }}" class="btn btn-warning"><i class="fas fa-plus me-1"></i> Nouvel auteur</a>
    @endcan
</x-entete-page>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-6">
                <input type="search" name="search" class="form-control" value="{{ request('search') }}"
                       placeholder="Rechercher par nom, prénom ou nationalité...">
            </div>
            <div class="col-auto"><button class="btn btn-outline-secondary"><i class="fas fa-magnifying-glass"></i></button></div>
            @if(request('search'))
                <div class="col-auto"><a href="{{ route('auteurs.index') }}" class="btn btn-link">Réinitialiser</a></div>
            @endif
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Auteur</th><th>Nationalité</th><th class="text-center">Ouvrages</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @forelse($auteurs as $auteur)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($auteur->photo)
                                        <img src="{{ asset('storage/' . $auteur->photo) }}" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;" alt="">
                                    @else
                                        <span class="rounded-circle d-inline-flex align-items-center justify-content-center"
                                              style="width:36px;height:36px;background:var(--wood-primary);color:var(--accent-gold);">
                                            <i class="fas fa-user"></i>
                                        </span>
                                    @endif
                                    <a href="{{ route('auteurs.show', $auteur) }}" class="text-decoration-none fw-semibold" style="color:var(--text-main);">
                                        {{ $auteur->nom_complet }}
                                    </a>
                                </div>
                            </td>
                            <td>{{ $auteur->nationalite ?? '—' }}</td>
                            <td class="text-center"><span class="badge bg-warning text-dark">{{ $auteur->livres_count }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('auteurs.show', $auteur) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-eye"></i></a>
                                @can('auteurs.gerer')
                                    <a href="{{ route('auteurs.edit', $auteur) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-pen"></i></a>
                                    <form action="{{ route('auteurs.destroy', $auteur) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Supprimer cet auteur ?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><x-vide message="Aucun auteur enregistré." icone="fa-feather-pointed" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $auteurs->links() }}
    </div>
</div>
@endsection
