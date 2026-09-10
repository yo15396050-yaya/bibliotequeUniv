@props([
    'titre',
    'valeur',
    'icone' => 'fa-chart-simple',
    'couleur' => 'gold',
    'sousTitre' => null,
    'lien' => null,
])

@php
    $fonds = [
        'gold' => 'var(--accent-gold)',
        'wood' => 'var(--wood-primary)',
        'success' => '#2e7d32',
        'warning' => '#ed6c02',
        'danger' => '#c62828',
        'info' => '#0277bd',
    ];
    $fond = $fonds[$couleur] ?? $fonds['gold'];
@endphp

<div class="card border-0 shadow-sm h-100 carte-stat">
    <div class="card-body d-flex align-items-center gap-3">
        <div class="carte-stat-icone flex-shrink-0" style="background: {{ $fond }}1a; color: {{ $fond }};">
            <i class="fas {{ $icone }}"></i>
        </div>
        <div class="min-w-0">
            <div class="text-uppercase small fw-semibold" style="letter-spacing:.5px; opacity:.7;">{{ $titre }}</div>
            <div class="fs-3 fw-bold lh-1" >{{ $valeur }}</div>
            @if($sousTitre)
                <div class="small" style="opacity:.65;">{{ $sousTitre }}</div>
            @endif
        </div>
    </div>
    @if($lien)
        <a href="{{ $lien }}" class="stretched-link" aria-label="{{ $titre }}"></a>
    @endif
</div>
