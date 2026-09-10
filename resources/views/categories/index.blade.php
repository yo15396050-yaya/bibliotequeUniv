@extends('layouts.dashboard')
@section('title', 'Catégories')

@section('content')
<x-entete-page titre="Catégories" icone="fa-tags" sous-titre="Classification du catalogue et sous-catégories.">
    @can('categories.gerer')
        <a href="{{ route('categories.create') }}" class="btn btn-warning"><i class="fas fa-plus me-1"></i> Nouvelle catégorie</a>
    @endcan
</x-entete-page>

<div class="row g-3">
    @forelse($categories as $categorie)
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h5 class="mb-1">
                                <span class="badge me-1" style="background: {{ $categorie->couleur ?: 'var(--wood-primary)' }};">
                                    {{ $categorie->code_categorie }}
                                </span>
                                <a href="{{ route('categories.show', $categorie) }}" class="text-decoration-none" style="color:var(--text-main);">
                                    {{ $categorie->nom }}
                                </a>
                            </h5>
                            <small style="opacity:.7;">{{ $categorie->livres_count }} ouvrage(s)</small>
                        </div>
                        @can('categories.gerer')
                            <div class="dropdown">
                                <button class="btn btn-sm btn-link" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-vertical"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ route('categories.edit', $categorie) }}"><i class="fas fa-pen me-2"></i>Modifier</a></li>
                                    <li>
                                        <form action="{{ route('categories.destroy', $categorie) }}" method="POST"
                                              onsubmit="return confirm('Supprimer cette catégorie ?');">
                                            @csrf @method('DELETE')
                                            <button class="dropdown-item text-danger"><i class="fas fa-trash me-2"></i>Supprimer</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        @endcan
                    </div>

                    @if($categorie->description)
                        <p class="small mb-2" style="opacity:.75;">{{ $categorie->description }}</p>
                    @endif

                    @if($categorie->enfants->isNotEmpty())
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($categorie->enfants as $enfant)
                                <a href="{{ route('categories.show', $enfant) }}"
                                   class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis text-decoration-none">
                                    {{ $enfant->nom }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12"><div class="card border-0 shadow-sm"><div class="card-body">
            <x-vide message="Aucune catégorie enregistrée." icone="fa-tags" />
        </div></div></div>
    @endforelse
</div>

<div class="mt-3">{{ $categories->links() }}</div>
@endsection
