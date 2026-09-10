@extends('layouts.dashboard')
@section('title', 'Rapports')

@section('content')
<x-entete-page titre="Rapports" icone="fa-file-lines"
    sous-titre="Générez et exportez les rapports de gestion en PDF, Excel ou CSV." />

<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3"><x-carte-stat titre="Notices" :valeur="number_format($stats['total_livres'], 0, ',', ' ')" icone="fa-book" couleur="wood" /></div>
    <div class="col-12 col-sm-6 col-xl-3"><x-carte-stat titre="Emprunts en cours" :valeur="$stats['emprunts_en_cours']" icone="fa-hand-holding" couleur="info" /></div>
    <div class="col-12 col-sm-6 col-xl-3"><x-carte-stat titre="Retards" :valeur="$stats['emprunts_en_retard']" icone="fa-triangle-exclamation" couleur="danger" /></div>
    <div class="col-12 col-sm-6 col-xl-3"><x-carte-stat titre="Pénalités impayées" :valeur="number_format($stats['penalites_impayees'], 0, ',', ' ')" :sous-titre="\App\Support\Parametres::devise()" icone="fa-money-bill-wave" couleur="warning" /></div>
</div>

<div class="row g-3">
    @foreach($rapports as $slug => $libelle)
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="mb-2">
                        <i class="fas {{ match($slug) {
                            'emprunts' => 'fa-hand-holding',
                            'retards' => 'fa-triangle-exclamation',
                            'penalites' => 'fa-money-bill-wave',
                            'livres' => 'fa-book',
                            'usagers' => 'fa-users',
                            'perdus' => 'fa-ban',
                            'endommages' => 'fa-bandage',
                            default => 'fa-file-lines',
                        } }} me-2" style="color: var(--accent-gold);"></i>
                        {{ $libelle }}
                    </h5>
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <a href="{{ route('rapports.afficher', $slug) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-eye me-1"></i> Consulter
                        </a>
                        <a href="{{ route('rapports.afficher', ['rapport' => $slug, 'format' => 'pdf']) }}" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-file-pdf me-1"></i> PDF
                        </a>
                        <a href="{{ route('rapports.afficher', ['rapport' => $slug, 'format' => 'excel']) }}" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-file-excel me-1"></i> Excel
                        </a>
                        <a href="{{ route('rapports.afficher', ['rapport' => $slug, 'format' => 'csv']) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-file-csv me-1"></i> CSV
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card border-0 shadow-sm mt-3">
    <div class="card-body d-flex flex-wrap gap-2 align-items-center">
        <span class="fw-semibold me-2">Exports rapides :</span>
        <a href="{{ route('exports.inventaire') }}" class="btn btn-sm btn-outline-dark"><i class="fas fa-file-pdf me-1"></i> Inventaire complet</a>
        <a href="{{ route('exports.retards') }}" class="btn btn-sm btn-outline-dark"><i class="fas fa-file-pdf me-1"></i> État des retards</a>
    </div>
</div>
@endsection
