@props(['message' => 'Aucun résultat.', 'icone' => 'fa-inbox'])

<div class="text-center py-5">
    <i class="fas {{ $icone }} fa-3x mb-3" style="color: var(--accent-leather); opacity:.4;"></i>
    <p class="mb-0" style="opacity:.7;">{{ $message }}</p>
    @if(trim($slot ?? '') !== '')
        <div class="mt-3">{{ $slot }}</div>
    @endif
</div>
