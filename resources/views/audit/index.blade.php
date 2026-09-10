@extends('layouts.dashboard')
@section('title', "Journal d'activité")

@section('content')
<x-entete-page titre="Journal d'activité" icone="fa-clipboard-list"
    :sous-titre="$journaux->total() . ' opération(s) tracée(s)'" />

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3">
                <input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Description...">
            </div>
            <div class="col-md-2">
                <select name="module" class="form-select">
                    <option value="">Tous les modules</option>
                    @foreach($modules as $module)
                        <option value="{{ $module }}" @selected(request('module') === $module)>{{ ucfirst($module) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="action" class="form-select">
                    <option value="">Toutes les actions</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" @selected(request('action') === $action)>{{ ucfirst($action) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}"></div>
            <div class="col-md-2"><input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}"></div>
            <div class="col-auto"><button class="btn btn-outline-secondary"><i class="fas fa-filter"></i></button></div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Date</th><th>Utilisateur</th><th>Action</th><th>Module</th><th>Description</th><th>IP</th><th></th></tr></thead>
                <tbody>
                    @forelse($journaux as $journal)
                        <tr>
                            <td class="small text-nowrap">{{ $journal->created_at->format('d/m/Y H:i') }}</td>
                            <td class="small">{{ $journal->user?->name ?? 'Système' }}</td>
                            <td><span class="badge bg-secondary-subtle text-secondary-emphasis">{{ $journal->action }}</span></td>
                            <td class="small">{{ $journal->module }}</td>
                            <td class="small">{{ $journal->description }}</td>
                            <td class="small">{{ $journal->adresse_ip ?? '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('audit.show', $journal) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-vide message="Aucune opération enregistrée." icone="fa-clipboard-list" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $journaux->links() }}
    </div>
</div>
@endsection
