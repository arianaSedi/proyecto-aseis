@props(['titulo', 'valor', 'nota' => null, 'color' => 'petroleo', 'href' => null])

@php
    $colores = [
        'petroleo' => 'bg-petroleo-700 text-white',
        'mostaza' => 'bg-mostaza-400 text-noche-900',
        'noche' => 'bg-noche-800 text-white',
    ];
    $clases = 'flex flex-col justify-between rounded-2xl p-5 shadow-sm min-h-32 ' . ($colores[$color] ?? $colores['petroleo']);
@endphp

<div {{ $attributes->merge(['class' => $clases]) }}>
    <div>
        <p class="text-xs opacity-80">{{ $titulo }}</p>
        <p class="mt-1 text-2xl font-semibold">{{ $valor }}</p>
    </div>
    <div class="mt-4 flex items-center justify-between gap-2 text-xs">
        <span class="opacity-80">{{ $nota }}</span>
        @if ($href)
            <a href="{{ $href }}" class="rounded-full p-1 hover:bg-white/15" aria-label="Ver más">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M5 12h14M13 6l6 6-6 6" />
                </svg>
            </a>
        @endif
    </div>
</div>