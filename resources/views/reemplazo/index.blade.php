@extends('layouts.sistema')

@section('titulo', 'Vida económica y reemplazo')

@section('contenido')
    @php
        $dinero = fn ($v) => ($v < 0 ? '−$' : '$') . number_format(abs($v), 2);
        $pct = fn ($v) => number_format($v * 100, 2) . ' %';
        $maxCaue = max(collect($tabla)->max('caue'), 0.01);
    @endphp

    <p class="mb-6 max-w-3xl text-slate-600">
        Para cada año posible de reemplazo se calcula el CAUE del equipo: inversión más mantenimiento creciente,
        menos el valor de mercado al venderlo. El año con el <strong>menor CAUE</strong> es la vida económica:
        conviene reemplazar el equipo al final de ese año.
    </p>

    {{-- Selector de cotización --}}
    <div class="mb-4 flex flex-wrap gap-2">
        @foreach ($cotizaciones as $c)
            <a href="{{ request()->fullUrlWithQuery(['cotizacion' => $c->codigo, 'costo_mantenimiento' => null]) }}"
               class="rounded-xl px-4 py-2 text-sm font-medium {{ $c->codigo === $seleccionada->codigo ? 'bg-petroleo-700 text-white' : 'bg-white text-noche-900 shadow-sm hover:bg-fondo-100' }}">
                {{ $c->codigo }}
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ url()->current() }}" class="mb-6 grid gap-4 rounded-2xl bg-white p-5 shadow-sm sm:grid-cols-2 lg:grid-cols-5">
        <input type="hidden" name="cotizacion" value="{{ $seleccionada->codigo }}">
        @php
            $campos = [
                ['tmar', 'TMAR (%)', round($tmar * 100, 2)],
                ['costo_mantenimiento', 'Mantenimiento año 1 ($)', round($mantenimiento, 2)],
                ['incremento_mantenimiento', 'Aumento anual del mantenimiento (%)', round($incremento * 100, 2)],
                ['perdida_valor', 'Pérdida de valor por año (%)', round($perdidaValor * 100, 2)],
            ];
        @endphp
        @foreach ($campos as [$nombre, $etiqueta, $valor])
            <label class="block text-sm">
                <span class="mb-1 block text-slate-600">{{ $etiqueta }}</span>
                <input type="number" name="{{ $nombre }}" step="0.01" min="0" value="{{ $valor }}"
                       class="w-full rounded-xl border border-fondo-300 px-3 py-2 focus:border-petroleo-500 focus:outline-none focus:ring-2 focus:ring-petroleo-100">
            </label>
        @endforeach
        <div class="flex items-end">
            <x-boton class="w-full">Recalcular</x-boton>
        </div>
    </form>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-indicador titulo="Vida económica" :valor="$vidaEconomica . ($vidaEconomica == 1 ? ' año' : ' años')"
                     nota="Año con el menor CAUE" />
        <x-indicador titulo="CAUE mínimo" :valor="$dinero($caueMinimo)" nota="Costo anual en ese año" color="mostaza" />
        <x-indicador titulo="Vida útil supuesta" :valor="$seleccionada->vida_util . ' años'" nota="Supuesto del Bloque A" color="noche" />
        <x-indicador titulo="Inversión" :valor="$dinero($seleccionada->inversion)" :nota="$seleccionada->codigo . ' · ' . $seleccionada->proveedor" />
    </div>

    <div class="mb-6 rounded-2xl border border-petroleo-100 bg-petroleo-50 p-5">
        <p class="font-semibold text-petroleo-700">
            Conviene reemplazar {{ $seleccionada->codigo }} al final del año {{ $vidaEconomica }},
            con un CAUE de {{ $dinero($caueMinimo) }}.
        </p>
        @if ($vidaEconomica == $seleccionada->vida_util)
            <p class="text-sm text-slate-600">
                El CAUE baja hasta el último año, así que con estos supuestos conviene usar el equipo toda su vida útil.
            </p>
        @endif
    </div>

    <x-tarjeta titulo="CAUE según el año de reemplazo" subtitulo="La barra mostaza es la vida económica." class="mb-6">
        <div class="space-y-3">
            @foreach ($tabla as $fila)
                <div class="flex items-center gap-3 text-sm">
                    <span class="w-14 shrink-0">Año {{ $fila['anio'] }}</span>
                    <div class="h-6 flex-1 rounded-lg bg-fondo-100">
                        <div class="h-6 rounded-lg {{ $fila['anio'] == $vidaEconomica ? 'bg-mostaza-400' : 'bg-petroleo-500' }}"
                             style="width: {{ max($fila['caue'] / $maxCaue * 100, 1) }}%"></div>
                    </div>
                    <span class="w-24 shrink-0 text-right font-medium">{{ $dinero($fila['caue']) }}</span>
                </div>
            @endforeach
        </div>
    </x-tarjeta>

    <x-tarjeta titulo="Cálculo por año" :subtitulo="'TMAR ' . $pct($tmar) . ' · mantenimiento crece ' . $pct($incremento) . ' por año · el equipo pierde ' . $pct($perdidaValor) . ' de su valor por año'">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[48rem] text-sm">
                <thead class="bg-fondo-100 text-slate-600">
                    <tr>
                        <th class="px-2 py-2 text-left">Año</th>
                        <th class="px-2 py-2 text-right">Mantenimiento</th>
                        <th class="px-2 py-2 text-right">Valor de mercado</th>
                        <th class="px-2 py-2 text-right">VP mantenimiento acumulado</th>
                        <th class="px-2 py-2 text-right">VP valor de mercado</th>
                        <th class="px-2 py-2 text-right">VP costos</th>
                        <th class="px-2 py-2 text-right">CAUE</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-fondo-100">
                    @foreach ($tabla as $fila)
                        <tr class="{{ $fila['anio'] == $vidaEconomica ? 'bg-mostaza-100 font-semibold' : '' }}">
                            <td class="px-2 py-2">{{ $fila['anio'] }}</td>
                            <td class="px-2 py-2 text-right">{{ $dinero($fila['mantenimiento']) }}</td>
                            <td class="px-2 py-2 text-right">{{ $dinero($fila['valor_mercado']) }}</td>
                            <td class="px-2 py-2 text-right">{{ $dinero($fila['vp_mantenimiento_acumulado']) }}</td>
                            <td class="px-2 py-2 text-right">{{ $dinero($fila['vp_mercado']) }}</td>
                            <td class="px-2 py-2 text-right">{{ $dinero($fila['vp_costos']) }}</td>
                            <td class="px-2 py-2 text-right">{{ $dinero($fila['caue']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="mt-3 text-xs text-slate-500">
            CAUE del año k = [P + VP(mantenimiento hasta k) − VP(valor de mercado en k)] × (A/P, i, k). {{ $fuenteDatos }}
        </p>
    </x-tarjeta>
@endsection