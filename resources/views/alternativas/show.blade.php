@extends('layouts.sistema')

@section('titulo', 'Alternativa ' . $alternativa->codigo)

@section('contenido')
    @php
        $url = fn (string $nombre, $parametros = []) => Route::has($nombre) ? route($nombre, $parametros) : '#';

        $tiposFuente = [
            'cotizacion' => 'Cotización formal',
            'factura' => 'Factura',
            'captura' => 'Captura o foto',
            'catalogo' => 'Catálogo o precio publicado',
            'entrevista' => 'Entrevista',
            'estimacion' => 'Estimación',
        ];

        $cotizaciones = $alternativa->cotizaciones;
        $total = $cotizaciones->count();
    @endphp

    {{-- Encabezado --}}
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <h2 class="text-2xl font-bold">{{ $alternativa->nombre }}</h2>
            <p class="mt-1 max-w-3xl text-slate-600">{{ $alternativa->descripcion }}</p>
        </div>
        <div class="flex gap-2">
            <x-boton :href="$url('alternativas.edit', $alternativa->id)">Editar</x-boton>
            <x-boton :href="$url('alternativas.index')" variante="secundario">Volver</x-boton>
        </div>
    </div>

    {{-- Supuestos --}}
    @php
        $supuestos = [
            ['Vida útil', $alternativa->vida_util . ' años'],
            ['Valor de salvamento', number_format($alternativa->porcentaje_salvamento, 0) . ' % de la inversión'],
            ['Costo fijo anual', '$' . number_format($alternativa->costos_anuales, 2)],
            ['Ahorro o ingreso anual', '$' . number_format($alternativa->ingresos_anuales, 2)],
        ];
    @endphp
    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($supuestos as [$etiqueta, $valor])
            <div class="rounded-2xl bg-white p-5 shadow">
                <p class="text-xs uppercase tracking-wide text-slate-500">{{ $etiqueta }}</p>
                <x-insignia color="ambar" class="mt-2">Supuesto académico · Bloque A</x-insignia>
            </div>
        @endforeach
    </div>

    {{-- Cotizaciones --}}
    <x-tarjeta class="mb-6">
        <div class="mb-4 flex items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold">Cotizaciones y evidencias</h2>
                <p class="text-sm text-slate-500">El enunciado exige al menos dos fuentes por dato y mostrar la fuente junto al resultado.</p>
            </div>
            <x-insignia :color="$total >= 2 ? 'verde' : 'ambar'">{{ $total }}/2 cotizaciones</x-insignia>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-fondo-100 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">Código</th>
                        <th class="px-3 py-2 text-left">Proveedor y equipo</th>
                        <th class="px-3 py-2 text-left">Fuente</th>
                        <th class="px-3 py-2 text-right">Inversión</th>
                        <th class="px-3 py-2 text-right">Salvamento ({{ number_format($alternativa->porcentaje_salvamento, 0) }} %)</th>
                        <th class="px-3 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($cotizaciones as $c)
                        <tr class="align-top">
                            <td class="px-3 py-3 font-semibold">{{ $c->codigo }}</td>
                            <td class="px-3 py-3">
                                <p class="font-medium">{{ $c->proveedor }}</p>
                                <p class="text-slate-600">{{ $c->descripcion }}</p>
                                @if ($c->condicion)
                                    <p class="mt-1 text-xs text-amber-700">{{ $c->condicion }}</p>
                                @endif
                            </td>
                            <td class="px-3 py-3">
                                <x-insignia :color="$c->tipo_fuente === 'estimacion' ? 'ambar' : 'azul'">
                                    {{ $tiposFuente[$c->tipo_fuente] ?? $c->tipo_fuente }}
                                </x-insignia>
                                @if ($c->fecha)
                                    <p class="mt-1 text-xs text-slate-500">{{ \Illuminate\Support\Carbon::parse($c->fecha)->format('d/m/Y') }}</p>
                                @endif
                                @if ($c->archivo_url)
                                    <a href="{{ $c->archivo_url }}" target="_blank" class="mt-1 block text-xs text-blue-600 hover:underline">Ver evidencia</a>
                                @else
                                    <p class="mt-1 text-xs text-slate-400">Sin archivo</p>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-right font-semibold">${{ number_format($c->monto, 2) }}</td>
                            <td class="px-3 py-3 text-right">${{ number_format($c->monto * $alternativa->porcentaje_salvamento / 100, 2) }}</td>
                            <td class="px-3 py-3 text-right">
                                @if (Route::has('cotizaciones.destroy'))
                                    <form method="POST" action="{{ route('cotizaciones.destroy', $c->id) }}"
                                          onsubmit="return confirm('¿Eliminar la cotización {{ $c->codigo }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 hover:underline">Eliminar</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-6 text-center text-slate-500">Todavía no hay cotizaciones.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($total >= 2)
            @php
                $minimo = $cotizaciones->sortBy('monto')->first();
                $maximo = $cotizaciones->sortByDesc('monto')->first();
            @endphp
            <p class="mt-4 rounded bg-fondo-100 p-3 text-sm">
                La cotización <strong>{{ $minimo->codigo }}</strong> cuesta
                <strong>${{ number_format($maximo->monto - $minimo->monto, 2) }}</strong> menos que
                <strong>{{ $maximo->codigo }}</strong>. La diferencia de precio por sí sola no determina cuál conviene:
                revisen garantía, accesorios y vigencia.
            </p>
        @endif
    </x-tarjeta>

    <x-tarjeta titulo="Agregar cotización" subtitulo="Adjunta la captura, foto o PDF como evidencia.">
        <form method="POST" action="{{ $url('alternativas.cotizaciones.store', $alternativa->id) }}" enctype="multipart/form-data"
              class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @csrf
            <x-campo nombre="codigo" etiqueta="Código" ayuda="Ejemplo: {{ $alternativa->codigo }}{{ $total + 1 }}" />
            <x-campo nombre="proveedor" etiqueta="Proveedor" />
            <x-campo nombre="monto" etiqueta="Inversión ($)" tipo="number" paso="0.01" />
            <x-campo nombre="descripcion" etiqueta="Equipo o descripción" class="sm:col-span-2 lg:col-span-3" />

            <label class="block text-sm">
                <span class="mb-1 block text-slate-600">Tipo de fuente</span>
                <select name="tipo_fuente" class="w-full rounded border border-fondo-300 px-3 py-2">
                    @foreach ($tiposFuente as $clave => $nombre)
                        <option value="{{ $clave }}" @selected(old('tipo_fuente') === $clave)>{{ $nombre }}</option>
                    @endforeach
                </select>
            </label>
            <x-campo nombre="fecha" etiqueta="Fecha" tipo="date" />
            <label class="block text-sm">
                <span class="mb-1 block text-slate-600">Evidencia (imagen o PDF)</span>
                <input type="file" name="archivo" accept=".pdf,.jpg,.jpeg,.png,.webp" class="w-full text-sm">
            </label>
            <x-campo nombre="condicion" etiqueta="Condiciones u observaciones" class="sm:col-span-2 lg:col-span-3"
                     ayuda="Ejemplo: confirmar garantía y vigencia del precio" />

            <div class="sm:col-span-2 lg:col-span-3">
                <x-boton>Guardar cotización</x-boton>
            </div>
        </form>
    </x-tarjeta>
@endsection