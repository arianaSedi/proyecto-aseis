@props(['href' => null, 'variante' => 'primario'])

@php
    $estilos = [
        'primario' => 'bg-petroleo-700 text-white hover:bg-petroleo-800',
        'acento' => 'bg-mostaza-400 text-noche-900 hover:bg-mostaza-500',
        'secundario' => 'bg-fondo-100 text-noche-900 hover:bg-fondo-200',
        'peligro' => 'bg-red-600 text-white hover:bg-red-700',
    ];
    $clases = 'inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-medium transition ' . ($estilos[$variante] ?? $estilos['primario']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $clases]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'submit', 'class' => $clases]) }}>{{ $slot }}</button>
@endif