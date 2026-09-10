@props(['statut' => '', 'texte' => null])

@php
    $palette = [
        // Emprunts
        'en cours' => ['bg-primary-subtle text-primary-emphasis', 'fa-hand-holding'],
        'retourné' => ['bg-success-subtle text-success-emphasis', 'fa-circle-check'],
        'en retard' => ['bg-danger-subtle text-danger-emphasis', 'fa-triangle-exclamation'],
        'perdu' => ['bg-dark-subtle text-dark-emphasis', 'fa-ban'],
        // Exemplaires
        'disponible' => ['bg-success-subtle text-success-emphasis', 'fa-circle-check'],
        'emprunte' => ['bg-primary-subtle text-primary-emphasis', 'fa-hand-holding'],
        'emprunté' => ['bg-primary-subtle text-primary-emphasis', 'fa-hand-holding'],
        'reserve' => ['bg-warning-subtle text-warning-emphasis', 'fa-bookmark'],
        'réservé' => ['bg-warning-subtle text-warning-emphasis', 'fa-bookmark'],
        'endommage' => ['bg-warning-subtle text-warning-emphasis', 'fa-bandage'],
        'en_reparation' => ['bg-info-subtle text-info-emphasis', 'fa-screwdriver-wrench'],
        'en réparation' => ['bg-info-subtle text-info-emphasis', 'fa-screwdriver-wrench'],
        'retire' => ['bg-secondary-subtle text-secondary-emphasis', 'fa-box-archive'],
        // Réservations
        'active' => ['bg-success-subtle text-success-emphasis', 'fa-bookmark'],
        'expirée' => ['bg-secondary-subtle text-secondary-emphasis', 'fa-hourglass-end'],
        'annulée' => ['bg-secondary-subtle text-secondary-emphasis', 'fa-xmark'],
        'honorée' => ['bg-success-subtle text-success-emphasis', 'fa-circle-check'],
        // Pénalités
        'impayee' => ['bg-danger-subtle text-danger-emphasis', 'fa-money-bill-wave'],
        'partiellement_payee' => ['bg-warning-subtle text-warning-emphasis', 'fa-coins'],
        'payee' => ['bg-success-subtle text-success-emphasis', 'fa-circle-check'],
        'annulee' => ['bg-secondary-subtle text-secondary-emphasis', 'fa-xmark'],
        // Comptes
        'actif' => ['bg-success-subtle text-success-emphasis', 'fa-circle-check'],
        'suspendu' => ['bg-danger-subtle text-danger-emphasis', 'fa-user-lock'],
        'diplome' => ['bg-info-subtle text-info-emphasis', 'fa-graduation-cap'],
        'radie' => ['bg-dark-subtle text-dark-emphasis', 'fa-user-slash'],
        // Renouvellements
        'en_attente' => ['bg-warning-subtle text-warning-emphasis', 'fa-clock'],
        'accepte' => ['bg-success-subtle text-success-emphasis', 'fa-circle-check'],
        'refuse' => ['bg-danger-subtle text-danger-emphasis', 'fa-circle-xmark'],
    ];

    [$classes, $icone] = $palette[$statut] ?? ['bg-secondary-subtle text-secondary-emphasis', 'fa-circle-info'];
@endphp

<span {{ $attributes->merge(['class' => "badge rounded-pill {$classes} px-3 py-2"]) }}>
    <i class="fas {{ $icone }} me-1"></i>{{ $texte ?? ucfirst(str_replace('_', ' ', $statut)) }}
</span>
