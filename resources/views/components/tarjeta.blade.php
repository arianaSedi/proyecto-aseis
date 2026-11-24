@props(['titulo' => null, 'subtitulo' => null])

<div {{ $attributes->merge(['class' => 'rounded-2xl bg-white p-5 shadow-sm sm:p-6']) }}>
    @if ($titulo)
        <h2 class="text-lg font-semibold text-noche-900">{{ $titulo }}</h2>
    @endif
    @if ($subtitulo)
        <p class="mb-4 text-sm text-slate-500">{{ $subtitulo }}</p>
    @elseif ($titulo)
        <div class="mb-4"></div>
    @endif

    {{ $slot }}
</div>