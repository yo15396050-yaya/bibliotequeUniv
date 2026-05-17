@extends('layouts.dashboard')

@section('title', 'Suivi des Emprunts')

@section('content')
<style>
    :root {
        --primary-wood: #5D4037;
        --gold-accent: #D4AF37;
        --soft-beige: #FAF3E0;
    }

    .stat-container {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        border-bottom: 4px solid #ced4da;
        transition: all 0.3s ease;
        height: 100%;
    }

    .stat-container.active-emprunts { border-color: var(--gold-accent); }
    .stat-container.late-emprunts { border-color: #dc3545; }
    .stat-container.total-emprunts { border-color: var(--primary-wood); }

    .stat-icon {
        width: 45px;
        height: 45px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
        font-size: 1.2rem;
    }

    .filter-card {
        background-color: var(--soft-beige);
        border: none;
        border-radius: 12px;
    }

    .table-emprunt thead {
        background-color: var(--primary-wood);
        color: white;
    }

    .badge-retard {
        background-color: #fff1f0;
        color: #cf1322;
        border: 1px solid #ffa39e;
        padding: 5px 10px;
    }

    .btn-action {
        width: 35px;
        height: 35px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        margin: 0 2px;
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0" style="color: var(--primary-wood);">
                <i class="fas fa-exchange-alt me-2"></i>Circulation des Ouvrages
            </h2>
            <p class="text-muted">Suivi des prêts et retours en temps réel</p>
        </div>
        <div>
            <a href="{{ route('emprunts.index', ['statut' => 'en retard']) }}" class="btn btn-danger shadow-sm me-2">
                <i class="fas fa-clock me-1"></i> Alertes Retards
            </a>
            <a href="{{ route('emprunts.create') }}" class="btn shadow-sm" style="background-color: var(--gold-accent); color: var(--primary-wood); font-weight: bold;">
                <i class="fas fa-plus-circle me-1"></i> Nouvel Emprunt
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-container total-emprunts shadow-sm">
                <div class="stat-icon bg-light text-dark"><i class="fas fa-layer-group"></i></div>
                <h6 class="text-muted small text-uppercase fw-bold">Cumul Historique</h6>
                <h3 class="fw-bold mb-0">{{ $statistiques['total'] ?? 0 }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-container active-emprunts shadow-sm">
                <div class="stat-icon" style="background-color: #fffbe6; color: #d4b106;"><i class="fas fa-book-reader"></i></div>
                <h6 class="text-muted small text-uppercase fw-bold">Prêts en cours</h6>
                <h3 class="fw-bold mb-0 text-warning">{{ $statistiques['en_cours'] ?? 0 }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-container late-emprunts shadow-sm">
                <div class="stat-icon" style="background-color: #fff1f0; color: #cf1322;"><i class="fas fa-exclamation-circle"></i></div>
                <h6 class="text-muted small text-uppercase fw-bold">Hors délais</h6>
                <h3 class="fw-bold mb-0 text-danger">{{ $statistiques['en_retard'] ?? 0 }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-container shadow-sm border-success">
                <div class="stat-icon" style="background-color: #f6ffed; color: #389e0d;"><i class="fas fa-check-double"></i></div>
                <h6 class="text-muted small text-uppercase fw-bold">Retours validés</h6>
                <h3 class="fw-bold mb-0 text-success">{{ $statistiques['retournes'] ?? 0 }}</h3>
            </div>
        </div>
    </div>

    <div class="card filter-card mb-4 shadow-sm">
        <div class="card-body">
            <form action="{{ route('emprunts.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted">Statut du prêt</label>
                    <select name="statut" class="form-select border-0 shadow-sm">
                        <option value="">Tous les états</option>
                        <option value="en cours" {{ request('statut') == 'en cours' ? 'selected' : '' }}>📖 En cours</option>
                        <option value="retourné" {{ request('statut') == 'retourné' ? 'selected' : '' }}>✅ Retourné</option>
                        <option value="en retard" {{ request('statut') == 'en retard' ? 'selected' : '' }}>⚠️ En retard</option>
                        <option value="perdu" {{ request('statut') == 'perdu' ? 'selected' : '' }}>❌ Perdu</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">Période du</label>
                    <input type="date" name="date_debut" class="form-control border-0 shadow-sm" value="{{ request('date_debut') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">Au</label>
                    <input type="date" name="date_fin" class="form-control border-0 shadow-sm" value="{{ request('date_fin') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-dark w-100 shadow-sm">
                        <i class="fas fa-filter me-2"></i>Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            @if($emprunts->isEmpty())
                <div class="text-center py-5">
                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="100" class="opacity-25 mb-3" alt="">
                    <h5 class="text-muted">Aucun mouvement enregistré pour cette période</h5>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-emprunt mb-0 align-middle">
                        <thead>
                            <tr>
                                <th class="ps-4">Référence</th>
                                <th>Étudiant</th>
                                <th>Livre emprunté</th>
                                <th>Dates (Emprunt / Retour)</th>
                                <th>État actuel</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($emprunts as $emprunt)
                            <tr>
                                <td class="ps-4"><span class="fw-bold text-muted">#{{ $emprunt->id }}</span></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                            <i class="fas fa-user-circle text-secondary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $emprunt->user->name ?? 'Anonyme' }}</div>
                                            <div class="small text-muted">{{ $emprunt->user->matricule ?? '' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;">
                                        <i class="fas fa-book me-1 text-muted"></i> {{ $emprunt->livre->titre ?? 'Livre inconnu' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="small">
                                        <div><span class="text-muted">Début:</span> {{ $emprunt->date_emprunt->format('d/m/y') }}</div>
                                        <div class="fw-bold"><span class="text-muted">Prévu:</span> {{ $emprunt->date_retour_prevue->format('d/m/y') }}</div>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $joursRestants = now()->diffInDays($emprunt->date_retour_prevue, false);
                                    @endphp

                                    @if($emprunt->statut == 'en retard' || ($emprunt->statut == 'en cours' && $joursRestants < 0))
                                        <span class="badge badge-retard rounded-pill">
                                            <i class="fas fa-exclamation-triangle me-1"></i> Retard {{ abs($joursRestants) }}j
                                        </span>
                                    @elseif($emprunt->statut == 'en cours')
                                        <span class="badge bg-light text-dark border rounded-pill">
                                            <i class="fas fa-hourglass-half me-1 text-warning"></i> En cours ({{ $joursRestants }}j)
                                        </span>
                                    @elseif($emprunt->statut == 'retourné')
                                        <span class="badge bg-success-subtle text-success rounded-pill px-3">
                                            <i class="fas fa-check me-1"></i> Rendu
                                        </span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill">{{ $emprunt->statut }}</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <a href="{{ route('emprunts.show', $emprunt) }}" class="btn btn-outline-dark btn-action" title="Détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if($emprunt->statut == 'en cours' || $emprunt->statut == 'en retard')
                                            <form action="{{ route('emprunts.retour', $emprunt) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-action" title="Confirmer le retour" onclick="return confirm('Confirmer la remise de l\'ouvrage ?')">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            </form>
                                        @endif

                                        <a href="{{ route('emprunts.fiche', $emprunt) }}" class="btn btn-info btn-action text-white" title="Imprimer fiche">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center p-4">
                    {{ $emprunts->appends(request()->input())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection