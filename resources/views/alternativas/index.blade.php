@extends('layouts.sistema')

@section('titulo', 'Alternativas de inversión')

@section('contenido')
    @php
        // Si la ruta todavia no existe (maqueta), el enlace queda en "#"
        $url = fn (string $nombre, $parametros = []) => Route::has($nombre) ? route($nombre, $parametros) : '#';
    @endphp

    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <p class="max-w-2xl text-slate-600">
            Cada alternativa debe tener al menos dos cotizaciones de proveedores distintos. El costo inicial
            de la evaluación sale de la cotización.
        </p>
        <x-boton :href="$url('alternativas.create')">+ Nueva alternativa</x-boton>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        @forelse ($alternativas as $a)
            @php
                $total = $a->cotizaciones->count();
                $minimo = $a->cotizaciones->min('monto');
                $maximo = $a->cotizaciones->max('monto');
            @endphp

            <x-tarjeta class="flex flex-col">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Alternativa {{ $a->codigo }}</p>
                        <h2 class="text-lg font-semibold">{{ $a->nombre }}</h2>
                    </div>
                    <x-insignia :color="$total >= 2 ? 'verde' : 'ambar'">{{ $total }}/2 cotizaciones</x-insignia>
                </div>

                <p class="mt-2 text-sm text-slate-600">{{ $a->descripcion }}</p>

                {{-- Supuestos de la alternativa --}}
                <dl class="mt-4 grid grid-cols-2 gap-3 rounded bg-fondo-100 p-4 text-sm sm:grid-cols-4">
                    <div>
                        <dt class="text-slate-500">Vida útil</dt>
                        <dd class="font-medium">{{ $a->vida_util }} años</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Salvamento</dt>
                        <dd class="font-medium">{{ number_format($a->porcentaje_salvamento, 0) }} %</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Costo anual</dt>
                        <dd class="font-medium">${{ number_format($a->costos_anuales, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Ahorro anual</dt>
                        <dd class="font-medium">${{ number_format($a->ingresos_anuales, 2) }}</dd>
                    </div>
                </dl>

                {{-- Cotizaciones --}}
                <table class="mt-4 w-full text-sm">
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($a->cotizaciones as $c)
                            <tr>
                                <td class="py-2 pr-2 font-semibold">{{ $c->codigo }}</td>
                                <td class="py-2 pr-2">{{ $c->proveedor }}</td>
                                <td class="py-2 text-right">${{ number_format($c->monto, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if ($total >= 2)
                    <p class="mt-2 text-xs text-slate-500">
                        Rango de inversión: ${{ number_format($minimo, 2) }} a ${{ number_format($maximo, 2) }}
                        (diferencia ${{ number_format($maximo - $minimo, 2) }})
                    </p>
                @endif

                {{-- Acciones --}}
                <div class="mt-auto flex flex-wrap items-center gap-3 pt-5">
                    <x-boton :href="$url('alternativas.show', $a->id)">Ver detalle</x-boton>
                    <x-boton :href="$url('alternativas.edit', $a->id)" variante="secundario">Editar</x-boton>
                    @if (Route::has('alternativas.destroy'))
                        <form method="POST" action="{{ route('alternativas.destroy', $a->id) }}"
                              onsubmit="return confirm('¿Eliminar la alternativa {{ $a->codigo }} y sus cotizaciones?')">
                            @csrf
                            @method('DELETE')
                            <button class="text-sm text-red-600 hover:underline">Eliminar</button>
                        </form>
                    @endif
                </div>
            </x-tarjeta>
        @empty
            <x-tarjeta class="lg:col-span-2">
                <p class="text-slate-500">Todavía no hay alternativas registradas.</p>
            </x-tarjeta>
        @endforelse
    </div>
@endsection