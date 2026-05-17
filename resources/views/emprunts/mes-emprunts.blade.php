@extends('layouts.dashboard')

@section('title', 'Mes Lectures & Emprunts')

@section('content')
<style>
    .book-card {
        border: none;
        border-radius: 12px;
        transition: transform 0.2s;
        background: #fff;
    }
    .book-card:hover {
        transform: translateY(-3px);
    }
    .status-indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
    }
    .progress {
        height: 8px;
        border-radius: 10px;
        background-color: #f0f0f0;
    }
    .fine-alert {
        background-color: #fff1f0;
        border: 1px solid #ffa39e;
        color: #cf1322;
        padding: 10px;
        border-radius: 8px;
        font-weight: bold;
    }
</style>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold" style="color: #5D4037;">
                <i class="fas fa-book-open me-2 text-warning"></i>Ma Bibliothèque Personnelle
            </h2>
            <p class="text-muted">Retrouvez ici l'historique de vos lectures et l'état de vos emprunts actuels.</p>
        </div>
    </div>

    @php $totalAmande = $emprunts->sum('amende'); @endphp
    @if($totalAmande > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="fine-alert shadow-sm d-flex align-items-center justify-content-between">
                <div>
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Attention : Vous avez des frais de retard cumulés de {{ number_format($totalAmande, 0, ',', ' ') }} FCFA.
                </div>
                <button class="btn btn-sm btn-danger">Régler maintenant</button>
            </div>
        </div>
    </div>
    @endif

    <div class="card book-card shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0 fw-bold">Liste de mes mouvements</h5>
                </div>
                <div class="col-auto text-muted small">
                    {{ $emprunts->total() }} ouvrage(s) au total
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            @if($emprunts->isEmpty())
                <div class="text-center py-5">
                    <img src="https://cdn-icons-png.flaticon.com/512/3532/3532353.png" width="80" class="mb-3 opacity-50" alt="">
                    <h5 class="text-muted">Vous n'avez pas encore emprunté de livres.</h5>
                    <a href="{{ route('livres.index') }}" class="btn btn-sm btn-primary mt-2">Parcourir le catalogue</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Ouvrage</th>
                                <th>Dates de possession</th>
                                <th>Progression Temps</th>
                                <th>Statut & Frais</th>
                                <th class="text-end pe-4">Détails</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($emprunts as $emprunt)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light p-2 rounded me-3">
                                            <i class="fas fa-book fa-lg text-secondary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $emprunt->livre->titre }}</div>
                                            <div class="small text-muted">{{ $emprunt->livre->auteur }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="small">
                                        <span class="text-muted">Du</span> {{ $emprunt->date_emprunt->format('d/m/Y') }}<br>
                                        <span class="text-muted">Au</span> <strong>{{ $emprunt->date_retour_prevue->format('d/m/Y') }}</strong>
                                    </div>
                                </td>
                                <td style="width: 200px;">
                                    @if($emprunt->statut != 'retourné')
                                        @php
                                            $totalDays = $emprunt->date_emprunt->diffInDays($emprunt->date_retour_prevue);
                                            $elapsedDays = $emprunt->date_emprunt->diffInDays(now());
                                            $percent = ($totalDays > 0) ? min(100, round(($elapsedDays / $totalDays) * 100)) : 100;
                                            $barColor = $percent > 80 ? 'bg-danger' : ($percent > 50 ? 'bg-warning' : 'bg-success');
                                        @endphp
                                        <div class="d-flex align-items-center">
                                            <div class="progress w-100 me-2">
                                                <div class="progress-bar {{ $barColor }}" style="width: {{ $percent }}%"></div>
                                            </div>
                                            <span class="small fw-bold">{{ $percent }}%</span>
                                        </div>
                                    @else
                                        <span class="badge bg-light text-success border">Restitué le {{ $emprunt->date_retour_effective->format('d/m/Y') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($emprunt->statut == 'en cours')
                                        <span class="status-indicator bg-warning"></span><span class="small fw-bold">À rendre</span>
                                    @elseif($emprunt->statut == 'en retard')
                                        <span class="status-indicator bg-danger"></span><span class="small fw-bold text-danger">En retard</span>
                                    @else
                                        <span class="status-indicator bg-success"></span><span class="small fw-bold text-success">Terminé</span>
                                    @endif

                                    @if($emprunt->amende > 0)
                                        <div class="text-danger small mt-1 fw-bold">
                                            <i class="fas fa-coins me-1"></i>{{ number_format($emprunt->amende, 0, ',', ' ') }} FCFA
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('emprunts.show', $emprunt) }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @if($emprunts->hasPages())
        <div class="card-footer bg-white py-3">
            <div class="d-flex justify-content-center">
                {{ $emprunts->links() }}
            </div>
        </div>
        @endif
    </div>

    <div class="mt-4 p-3 bg-light rounded-3 border-start border-primary border-4">
        <div class="d-flex">
            <i class="fas fa-lightbulb text-warning me-3 fa-2x"></i>
            <div>
                <h6 class="fw-bold mb-1">Le saviez-vous ?</h6>
                <p class="small mb-0 text-muted">Rendre vos livres à temps permet à d'autres étudiants de profiter des ressources de l'université. Si vous avez besoin de plus de temps, passez nous voir à l'accueil pour une prolongation !</p>
            </div>
        </div>
    </div>
</div>
@endsection