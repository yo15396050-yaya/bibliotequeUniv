@extends('layouts.dashboard')
@section('title', 'Pénalité #' . $penalite->id)

@php $devise = \App\Support\Parametres::devise(); @endphp

@section('content')
<x-entete-page :titre="'Pénalité #' . $penalite->id" icone="fa-money-bill-wave"
    :sous-titre="$penalite->libelle_type . ' — ' . $penalite->user?->name">
    <a href="{{ route('penalites.recu', $penalite) }}" class="btn btn-outline-dark"><i class="fas fa-file-pdf me-1"></i> Reçu PDF</a>
    <a href="{{ route('penalites.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</x-entete-page>

<x-erreurs />

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Détail</h5>
                    <x-badge :statut="$penalite->statut" :texte="$penalite->libelle_statut" />
                </div>
                <dl class="row small mb-0">
                    <dt class="col-5">Usager</dt>
                    <dd class="col-7">
                        <a href="{{ route('users.show', $penalite->user_id) }}" class="text-decoration-none">{{ $penalite->user?->name }}</a>
                        <div style="opacity:.65;">{{ $penalite->user?->matricule }}</div>
                    </dd>
                    <dt class="col-5">Type</dt><dd class="col-7">{{ $penalite->libelle_type }}</dd>
                    @if($penalite->jours_retard)
                        <dt class="col-5">Jours de retard</dt><dd class="col-7">{{ $penalite->jours_retard }}</dd>
                    @endif
                    @if($penalite->emprunt)
                        <dt class="col-5">Emprunt</dt>
                        <dd class="col-7">
                            <a href="{{ route('emprunts.show', $penalite->emprunt) }}" class="text-decoration-none">
                                {{ $penalite->emprunt->livre?->titre }}
                            </a>
                        </dd>
                    @endif
                    <dt class="col-5">Montant</dt><dd class="col-7 fw-bold">{{ number_format((float) $penalite->montant, 0, ',', ' ') }} {{ $devise }}</dd>
                    <dt class="col-5">Déjà payé</dt><dd class="col-7 text-success">{{ number_format((float) $penalite->montant_paye, 0, ',', ' ') }} {{ $devise }}</dd>
                    <dt class="col-5">Reste à payer</dt>
                    <dd class="col-7 fw-bold {{ $penalite->reste_a_payer > 0 && !$penalite->estSoldee() ? 'text-danger' : 'text-success' }}">
                        {{ number_format($penalite->estSoldee() ? 0 : $penalite->reste_a_payer, 0, ',', ' ') }} {{ $devise }}
                    </dd>
                    <dt class="col-5">Créée le</dt><dd class="col-7">{{ $penalite->created_at->format('d/m/Y à H:i') }}</dd>
                </dl>
                @if($penalite->motif)
                    <hr><p class="small mb-0" style="white-space:pre-line;">{{ $penalite->motif }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        @can('encaisser', $penalite)
            @if(! $penalite->estSoldee())
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-transparent border-0 pt-3"><h5 class="mb-0">Encaisser un paiement</h5></div>
                    <div class="card-body">
                        <form action="{{ route('penalites.payer', $penalite) }}" method="POST" class="row g-3">
                            @csrf
                            <div class="col-md-4">
                                <label class="form-label">Montant <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="1" min="1" max="{{ $penalite->reste_a_payer }}"
                                           name="montant" class="form-control" value="{{ (int) $penalite->reste_a_payer }}" required>
                                    <span class="input-group-text">{{ $devise }}</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Mode <span class="text-danger">*</span></label>
                                <select name="mode_paiement" class="form-select" required>
                                    @foreach(\App\Models\PaiementPenalite::MODES as $cle => $libelle)
                                        <option value="{{ $cle }}">{{ $libelle }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Référence</label>
                                <input type="text" name="reference" class="form-control" placeholder="N° de transaction">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Observation</label>
                                <input type="text" name="observation" class="form-control">
                            </div>
                            <div class="col-12 d-flex gap-2">
                                <button class="btn btn-success"><i class="fas fa-hand-holding-dollar me-1"></i> Encaisser</button>
                                @can('annuler', $penalite)
                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#annulation">
                                        <i class="fas fa-xmark me-1"></i> Annuler la pénalité
                                    </button>
                                @endcan
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        @endcan

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3"><h5 class="mb-0">Historique des paiements</h5></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Date</th><th>Montant</th><th>Mode</th><th>Référence</th><th>Encaissé par</th></tr></thead>
                    <tbody>
                        @forelse($penalite->paiements as $paiement)
                            <tr>
                                <td class="small">{{ $paiement->date_paiement->format('d/m/Y H:i') }}</td>
                                <td class="fw-semibold">{{ number_format((float) $paiement->montant, 0, ',', ' ') }} {{ $devise }}</td>
                                <td>{{ \App\Models\PaiementPenalite::MODES[$paiement->mode_paiement] ?? $paiement->mode_paiement }}</td>
                                <td class="small">{{ $paiement->reference ?? '—' }}</td>
                                <td class="small">{{ $paiement->caissier?->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><x-vide message="Aucun paiement enregistré." icone="fa-receipt" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@can('annuler', $penalite)
<div class="modal fade" id="annulation" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('penalites.annuler', $penalite) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Annuler la pénalité</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="form-label">Motif de l'annulation <span class="text-danger">*</span></label>
                <textarea name="motif" rows="3" class="form-control" required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
                <button class="btn btn-danger">Confirmer l'annulation</button>
            </div>
        </form>
    </div>
</div>
@endcan
@endsection
