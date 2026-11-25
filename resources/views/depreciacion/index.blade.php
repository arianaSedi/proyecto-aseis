@extends('layouts.sistema')

@section('titulo', 'Depreciación')

@section('contenido')
    @php
        $metodos = [
            'linea_recta' => 'Línea recta',
            'suma_digitos' => 'Suma de dígitos de los años (SDA)',
        ];

        $P = $seleccionada->inversion;
        $S = $seleccionada->salvamento;
        $n = $seleccionada->vida_util;
        $base = $P - $S;
        $sda = $n * ($n + 1) / 2;

        $dinero = fn ($v) => '$' . number_format($v, 2);
    @endphp

    <p class="mb-6 max-w-3xl text-slate-600">
        Cuadro de depreciación por cotización con dos métodos, como pide la guía del proyecto.
        La depreciación no es salida de efectivo, pero reduce la utilidad gravable en la evaluación después de impuestos.
    </p>

    <div class="mb-6 flex flex-wrap gap-2">
        @foreach ($cotizaciones as $c)
            <a href="{{ url()->current() }}?cotizacion={{ $c->codigo }}"
               class="rounded px-4 py-2 text-sm font-medium {{ $c->codigo === $seleccionada->codigo ? 'bg-petroleo-700 text-white' : 'bg-white text-slate-700 shadow hover:bg-fondo-100' }}">
                {{ $c->codigo }}
            </a>
        @endforeach
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl bg-white p-5 shadow sm:col-span-2 lg:col-span-1">
            <p class="text-xs uppercase tracking-wide text-slate-500">Activo</p>
            <p class="mt-1 font-semibold">{{ $seleccionada->codigo }} · {{ $seleccionada->nombre }}</p>
            <p class="text-xs text-slate-500">{{ $seleccionada->proveedor }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow">
            <p class="text-xs uppercase tracking-wide text-slate-500">Costo (P)</p>
            <p class="mt-1 text-xl font-semibold">{{ $dinero($P) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow">
            <p class="text-xs uppercase tracking-wide text-slate-500">Salvamento (S)</p>
            <p class="mt-1 text-xl font-semibold">{{ $dinero($S) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow">
            <p class="text-xs uppercase tracking-wide text-slate-500">Vida útil (n)</p>
            <p class="mt-1 text-xl font-semibold">{{ $n }} años</p>
        </div>
    </div>

    <p class="mb-6 rounded bg-white p-4 text-sm shadow">
        Base depreciable = P − S = {{ number_format($P, 2) }} − {{ number_format($S, 2) }} = <strong>{{ $dinero($base) }}</strong>
    </p>

    <div class="mb-6 grid gap-6 lg:grid-cols-2">
        @foreach ($metodos as $clave => $nombre)
            @php
                $tabla = $tablas[$clave];
                $total = array_sum(array_column($tabla, 'depreciacion'));
                $cuadra = abs($total - $base) < 0.01;
            @endphp
            <x-tarjeta :titulo="$nombre">
                <p class="-mt-2 mb-4 text-sm text-slate-600">
                    @if ($clave === 'linea_recta')
                        D = (P − S) / n = {{ number_format($base, 2) }} / {{ $n }} = <strong>{{ $dinero($base / $n) }}</strong> por año
                    @else
                        SDA = n(n + 1) / 2 = {{ $sda }} · D<sub>t</sub> = (años restantes / {{ $sda }}) × {{ number_format($base, 2) }}
                    @endif
                </p>

                <table class="w-full text-sm">
                    <thead class="bg-fondo-100 text-slate-600">
                        <tr>
                            <th class="px-2 py-1 text-left">Año</th>
                            @if ($clave === 'suma_digitos')
                                <th class="px-2 py-1 text-right">Fracción</th>
                            @endif
                            <th class="px-2 py-1 text-right">Depreciación</th>
                            <th class="px-2 py-1 text-right">Acumulada</th>
                            <th class="px-2 py-1 text-right">Valor en libros</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="text-slate-500">
                            <td class="px-2 py-1">0</td>
                            @if ($clave === 'suma_digitos')
                                <td></td>
                            @endif
                            <td></td>
                            <td></td>
                            <td class="px-2 py-1 text-right">{{ $dinero($P) }}</td>
                        </tr>
                        @foreach ($tabla as $fila)
                            <tr>
                                <td class="px-2 py-1">{{ $fila['anio'] }}</td>
                                @if ($clave === 'suma_digitos')
                                    <td class="px-2 py-1 text-right">{{ $fila['fraccion'] }}</td>
                                @endif
                                <td class="px-2 py-1 text-right">{{ $dinero($fila['depreciacion']) }}</td>
                                <td class="px-2 py-1 text-right">{{ $dinero($fila['acumulada']) }}</td>
                                <td class="px-2 py-1 text-right">{{ $dinero($fila['valor_libros']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <p class="mt-3 text-sm {{ $cuadra ? 'text-emerald-600' : 'text-amber-700' }}">
                    Verificación: suma de depreciaciones = {{ $dinero($total) }} {{ $cuadra ? '= P − S ✓' : '≠ P − S' }}
                </p>
            </x-tarjeta>
        @endforeach
    </div>

    <x-tarjeta titulo="Comparación de métodos" subtitulo="La SDA deprecia más en los primeros años; la línea recta reparte igual cada año. Al final ambos llegan al salvamento.">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-fondo-100 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">Año</th>
                        <th class="px-3 py-2 text-right">Línea recta</th>
                        <th class="px-3 py-2 text-right">SDA</th>
                        <th class="px-3 py-2 text-right">Diferencia</th>
                        <th class="px-3 py-2 text-left">Depreciación del año</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($tablas['linea_recta'] as $i => $lr)
                        @php
                            $sd = $tablas['suma_digitos'][$i];
                            $maximo = max($tablas['suma_digitos'][0]['depreciacion'], 0.01);
                        @endphp
                        <tr>
                            <td class="px-3 py-2">{{ $lr['anio'] }}</td>
                            <td class="px-3 py-2 text-right">{{ $dinero($lr['depreciacion']) }}</td>
                            <td class="px-3 py-2 text-right">{{ $dinero($sd['depreciacion']) }}</td>
                            <td class="px-3 py-2 text-right">{{ $dinero($sd['depreciacion'] - $lr['depreciacion']) }}</td>
                            <td class="px-3 py-2">
                                <div class="space-y-1">
                                    <div class="h-2 rounded bg-petroleo-600" style="width: {{ $lr['depreciacion'] / $maximo * 100 }}%"></div>
                                    <div class="h-2 rounded bg-mostaza-400" style="width: {{ $sd['depreciacion'] / $maximo * 100 }}%"></div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="mt-3 flex gap-4 text-xs text-slate-500">
            <span class="flex items-center gap-1"><span class="inline-block h-2 w-4 rounded bg-petroleo-600"></span> Línea recta</span>
            <span class="flex items-center gap-1"><span class="inline-block h-2 w-4 rounded bg-mostaza-400"></span> Suma de dígitos</span>
        </p>
    </x-tarjeta>
@endsection