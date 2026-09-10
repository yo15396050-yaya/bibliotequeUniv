@extends('layouts.dashboard')
@section('title', 'Années académiques')

@section('content')
<x-entete-page titre="Années académiques" icone="fa-calendar">
    <a href="{{ route('settings.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Paramètres</a>
</x-entete-page>

<x-erreurs />

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3"><h6 class="mb-0">Nouvelle année</h6></div>
            <div class="card-body">
                <form action="{{ route('settings.annees.store') }}" method="POST" class="d-grid gap-2">
                    @csrf
                    <input name="libelle" class="form-control" placeholder="Ex : 2025-2026" required>
                    <label class="form-label small mb-0">Début</label>
                    <input type="date" name="date_debut" class="form-control" required>
                    <label class="form-label small mb-0">Fin</label>
                    <input type="date" name="date_fin" class="form-control" required>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="active" value="1" id="active">
                        <label class="form-check-label" for="active">Définir comme année courante</label>
                    </div>
                    <button class="btn btn-warning">Ajouter</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Année</th><th>Période</th><th class="text-center">Usagers</th><th class="text-end">Statut</th></tr></thead>
                    <tbody>
                        @forelse($annees as $annee)
                            <tr>
                                <td class="fw-semibold">{{ $annee->libelle }}</td>
                                <td class="small">{{ $annee->date_debut->format('d/m/Y') }} → {{ $annee->date_fin->format('d/m/Y') }}</td>
                                <td class="text-center">{{ $annee->users_count }}</td>
                                <td class="text-end">
                                    @if($annee->active)
                                        <span class="badge bg-success">Année courante</span>
                                    @else
                                        <form action="{{ route('settings.annees.activer', $annee) }}" method="POST" class="d-inline">
                                            @csrf @method('PUT')
                                            <button class="btn btn-sm btn-outline-primary">Activer</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><x-vide message="Aucune année académique enregistrée." icone="fa-calendar" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
