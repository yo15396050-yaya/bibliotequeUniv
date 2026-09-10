@props(['titre', 'sousTitre' => null, 'icone' => null])

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1 d-flex align-items-center gap-2">
            @if($icone)<i class="fas {{ $icone }}" style="color: var(--accent-gold);"></i>@endif
            {{ $titre }}
        </h1>
        @if($sousTitre)
            <p class="mb-0" style="opacity:.7;">{{ $sousTitre }}</p>
        @endif
    </div>
    @if(trim($slot ?? '') !== '')
        <div class="d-flex flex-wrap gap-2">{{ $slot }}</div>
    @endif
</div>
