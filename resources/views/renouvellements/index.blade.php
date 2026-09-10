@extends('layouts.dashboard')
@section('title', 'Renouvellements')

@section('content')
<x-entete-page titre="Demandes de renouvellement" icone="fa-arrows-rotate"
    :sous-titre="$renouvellements->total() . ' demande(s)'" />

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3">
                <select name="statut" class="form-select" onchange="this.form.submit()">
                    <option value="">Tous les statuts</option>
                    <option value="en_attente" @selected(request('statut') === 'en_attente')>En attente</option>
                    <option value="accepte" @selected(request('statut') === 'accepte')>Accepté</option>
                    <option value="refuse" @selected(request('statut') === 'refuse')>Refusé</option>
                </select>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr><th>Usager</th><th>Ouvrage</th><th>Échéance</th><th>Statut</th><th class="text-end">Décision</th></tr>
                </thead>
                <tbody>
                    @forelse($renouvellements as $renouvellement)
                        <tr>
                            <td>
                                {{ $renouvellement->demandeur?->name }}
                                <div class="small" style="opacity:.65;">{{ $renouvellement->demandeur?->matricule }}</div>
                            </td>
                            <td>
                                <a href="{{ route('emprunts.show', $renouvellement->emprunt_id) }}" class="text-decoration-none" style="color:var(--text-main);">
                                    {{ $renouvellement->emprunt?->livre?->titre }}
                                </a>
                            </td>
                            <td class="small">
                                {{ $renouvellement->ancienne_echeance?->format('d/m/Y') }}
                                @if($renouvellement->nouvelle_echeance)
                                    <i class="fas fa-arrow-right mx-1"></i>
                                    <strong>{{ $renouvellement->nouvelle_echeance->format('d/m/Y') }}</strong>
                                @endif
                            </td>
                            <td>
                                <x-badge :statut="$renouvellement->statut" />
                                @if($renouvellement->motif_refus)
                                    <div class="small text-danger">{{ $renouvellement->motif_refus }}</div>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($renouvellement->statut === 'en_attente')
                                    <form action="{{ route('renouvellements.traiter', $renouvellement) }}" method="POST" class="d-inline">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="decision" value="accepter">
                                        <button class="btn btn-sm btn-success"><i class="fas fa-check"></i> Accepter</button>
                                    </form>
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                            data-bs-target="#refus-{{ $renouvellement->id }}">
                                        <i class="fas fa-xmark"></i> Refuser
                                    </button>

                                    <div class="modal fade" id="refus-{{ $renouvellement->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <form action="{{ route('renouvellements.traiter', $renouvellement) }}" method="POST" class="modal-content text-start">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="decision" value="refuser">
                                                <div class="modal-header"><h5 class="modal-title">Refuser la demande</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                                <div class="modal-body">
                                                    <label class="form-label">Motif du refus <span class="text-danger">*</span></label>
                                                    <textarea name="motif_refus" rows="3" class="form-control" required
                                                              placeholder="Ex : ouvrage réservé par un autre usager."></textarea>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                                                    <button class="btn btn-danger">Confirmer le refus</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @else
                                    <span class="small" style="opacity:.6;">
                                        Traité par {{ $renouvellement->traitePar?->name ?? 'le système' }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-vide message="Aucune demande de renouvellement." icone="fa-arrows-rotate" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $renouvellements->links() }}
    </div>
</div>
@endsection
