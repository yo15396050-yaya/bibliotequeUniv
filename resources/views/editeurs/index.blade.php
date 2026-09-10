@extends('layouts.dashboard')
@section('title', 'Éditeurs')

@section('content')
<x-entete-page titre="Éditeurs" icone="fa-building" :sous-titre="$editeurs->total() . ' éditeur(s)'">
    @can('editeurs.gerer')
        <a href="{{ route('editeurs.create') }}" class="btn btn-warning"><i class="fas fa-plus me-1"></i> Nouvel éditeur</a>
    @endcan
</x-entete-page>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-6">
                <input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Rechercher un éditeur...">
            </div>
            <div class="col-auto"><button class="btn btn-outline-secondary"><i class="fas fa-magnifying-glass"></i></button></div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Éditeur</th><th>Pays</th><th>Contact</th><th class="text-center">Ouvrages</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @forelse($editeurs as $editeur)
                        <tr>
                            <td><a href="{{ route('editeurs.show', $editeur) }}" class="text-decoration-none fw-semibold" style="color:var(--text-main);">{{ $editeur->nom }}</a></td>
                            <td>{{ $editeur->pays ?? '—' }}</td>
                            <td class="small">
                                @if($editeur->email)<div>{{ $editeur->email }}</div>@endif
                                @if($editeur->site_web)<a href="{{ $editeur->site_web }}" target="_blank" rel="noopener">{{ $editeur->site_web }}</a>@endif
                            </td>
                            <td class="text-center"><span class="badge bg-warning text-dark">{{ $editeur->livres_count }}</span></td>
                            <td class="text-end">
                                @can('editeurs.gerer')
                                    <a href="{{ route('editeurs.edit', $editeur) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-pen"></i></a>
                                    <form action="{{ route('editeurs.destroy', $editeur) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Supprimer cet éditeur ?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-vide message="Aucun éditeur enregistré." icone="fa-building" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $editeurs->links() }}
    </div>
</div>
@endsection
