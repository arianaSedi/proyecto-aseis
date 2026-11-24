@props(['color' => 'gris'])

@php
    $colores = [
        'gris' => 'bg-slate-100 text-slate-700',
        'verde' => 'bg-emerald-100 text-emerald-700',
        'ambar' => 'bg-amber-100 text-amber-800',
        'rojo' => 'bg-red-100 text-red-700',
        'azul' => 'bg-blue-100 text-blue-700',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-block rounded px-2 py-0.5 text-xs font-medium ' . ($colores[$color] ?? $colores['gris'])]) }}>
    {{ $slot }}
</span>