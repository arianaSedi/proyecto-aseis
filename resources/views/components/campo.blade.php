@props(['nombre', 'etiqueta', 'tipo' => 'text', 'valor' => null, 'paso' => null, 'ayuda' => null])

@php
    $borde = $errors->has($nombre) ? 'border-red-400' : 'border-fondo-300';
    $estilo = "w-full rounded-xl border {$borde} bg-white px-3 py-2 focus:border-petroleo-500 focus:outline-none focus:ring-2 focus:ring-petroleo-100";
@endphp

<label {{ $attributes->only('class')->merge(['class' => 'block text-sm']) }}>
    <span class="mb-1 block text-slate-600">{{ $etiqueta }}</span>

    @if ($tipo === 'textarea')
        <textarea name="{{ $nombre }}" rows="3" class="{{ $estilo }}">{{ old($nombre, $valor) }}</textarea>
    @else
        <input type="{{ $tipo }}" name="{{ $nombre }}" value="{{ old($nombre, $valor) }}"
               @if ($paso) step="{{ $paso }}" min="0" @endif
               {{ $attributes->except('class') }}
               class="{{ $estilo }}">
    @endif

    @if ($ayuda)
        <span class="mt-1 block text-xs text-slate-500">{{ $ayuda }}</span>
    @endif

    @error($nombre)
        <span class="mt-1 block text-xs text-red-600">{{ $message }}</span>
    @enderror
</label>