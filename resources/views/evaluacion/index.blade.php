@extends('layouts.sistema')

@section('titulo', 'Evaluación económica')

@section('contenido')
    @php
        $url = fn (string $nombre, $parametros = []) => Route::has($nombre) ? route($nombre, $parametros) : '#';

        // Criterio principal: mayor VPN (con vidas iguales coincide con el menor CAUE)
        $mejor = $resultados->sortByDesc('vpn')->first();
        $maxVpn = max($resultados->map(fn ($r) => abs($r->vpn))->max(), 0.01);

        $dinero = fn ($v) => $v === null ? '—' : ($v < 0 ? '−$' : '$') . number_format(abs($v), 2);
        $pct = fn ($v) => $v === null ? '—' : number_format($v * 100, 2) . ' %';
    @endphp

    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="max-w-2xl text-slate-600">
                Comparación de las cotizaciones de cada alternativa con VPN, TIR, CAUE y relación beneficio/costo.
            </p>
            <p class="mt-1 text-xs text-slate-500">Fuente de la TMAR: {{ $fuenteTmar ?? 'sin fuente' }}</p>
        </div>
        <form method="GET" action="{{ url()->current() }}" class="flex items-end gap-2">
            <label class="text-sm">
                <span class="mb-1 block text-slate-600">TMAR (%)</span>
                <input type="number" name="tmar" step="0.01" min="0" value="{{ round($tmar * 100, 2) }}"
                       class="w-28 rounded border border-fondo-300 bg-white px-3 py-2">
            </label>
            <x-boton>Recalcular</x-boton>
        </form>
    </div>

    @if ($resultados->isEmpty())
        <x-tarjeta>
            <p class="text-slate-500">Todavía no hay cotizaciones para evaluar.</p>
        </x-tarjeta>
    @else
    
        @if ($mejor->vpn >= 0)
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
                <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">Opción más viable</p>
                <p class="mt-1 text-xl font-bold">{{ $mejor->codigo }} · {{ $mejor->nombre }}</p>
                <p class="mt-1 text-slate-600">
                    {{ $mejor->proveedor }}. Tiene el mayor VPN ({{ $dinero($mejor->vpn) }}) a una TMAR de {{ $pct($tmar) }},
                    con TIR de {{ $pct($mejor->tir) }} y B/C de {{ $mejor->bc === null ? '—' : number_format($mejor->bc, 2) }}.
                </p>
            </div>
        @else
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-5">
                <p class="font-semibold text-red-700">Ninguna opción alcanza la TMAR de {{ $pct($tmar) }}.</p>
                <p class="text-slate-600">La menos desfavorable es {{ $mejor->codigo }} (VPN {{ $dinero($mejor->vpn) }}).</p>
            </div>
        @endif

        <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($resultados as $r)
                <div class="flex flex-col rounded-2xl bg-white p-5 shadow {{ $r->codigo === $mejor->codigo ? 'ring-2 ring-emerald-500' : '' }}">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-lg font-bold">{{ $r->codigo }}</span>
                        <x-insignia :color="$r->vpn >= 0 ? 'verde' : 'rojo'">{{ $r->vpn >= 0 ? 'Aceptable' : 'No aceptable' }}</x-insignia>
                    </div>
                    <p class="text-sm text-slate-600">{{ $r->nombre }}</p>
                    <p class="mb-4 text-xs text-slate-500">{{ $r->proveedor }}</p>

                    <dl class="mt-auto grid grid-cols-2 gap-y-1 text-sm">
                        <dt class="text-slate-500">Inversión</dt>
                        <dd class="text-right">{{ $dinero($r->inversion) }}</dd>
                        <dt class="text-slate-500">VPN</dt>
                        <dd class="text-right font-semibold {{ $r->vpn >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $dinero($r->vpn) }}</dd>
                        <dt class="text-slate-500">TIR</dt>
                        <dd class="text-right">{{ $pct($r->tir) }}</dd>
                        <dt class="text-slate-500">CAUE</dt>
                        <dd class="text-right">{{ $dinero($r->caue) }}</dd>
                        <dt class="text-slate-500">B/C</dt>
                        <dd class="text-right">{{ $r->bc === null ? '—' : number_format($r->bc, 4) }}</dd>
                        <dt class="text-slate-500">Recuperación</dt>
                        <dd class="text-right">{{ $r->recuperacion === null ? '—' : number_format($r->recuperacion, 2) . ' años' }}</dd>
                    </dl>
                </div>
            @endforeach
        </div>

        <x-tarjeta titulo="VPN por cotización" subtitulo="Verde: alcanza la TMAR. Rojo: no la alcanza." class="mb-6">
            <div class="space-y-3">
                @foreach ($resultados as $r)
                    <div class="flex items-center gap-3 text-sm">
                        <span class="w-8 font-semibold">{{ $r->codigo }}</span>
                        <div class="h-6 flex-1 rounded bg-slate-100">
                            <div class="h-6 rounded {{ $r->vpn >= 0 ? 'bg-emerald-500' : 'bg-red-400' }}"
                                 style="width: {{ max(abs($r->vpn) / $maxVpn * 100, 1) }}%"></div>
                        </div>
                        <span class="w-24 text-right font-medium">{{ $dinero($r->vpn) }}</span>
                    </div>
                @endforeach
            </div>
        </x-tarjeta>

        {{-- Tabla comparativa --}}
        <x-tarjeta titulo="Comparación detallada" subtitulo="Vida útil de {{ $resultados->first()->vida_util }} años para todas las opciones." class="mb-6 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-fondo-100 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">Concepto</th>
                        @foreach ($resultados as $r)
                            <th class="px-3 py-2 text-right {{ $r->codigo === $mejor->codigo ? 'text-emerald-700' : '' }}">{{ $r->codigo }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @php
                        $filas = [
                            ['Inversión inicial (P)', fn ($r) => $dinero($r->inversion), false],
                            ['Valor de salvamento (S)', fn ($r) => $dinero($r->salvamento), false],
                            ['Costo fijo anual (C)', fn ($r) => $dinero($r->costos_anuales), false],
                            ['Ahorro anual (I)', fn ($r) => $dinero($r->ingresos_anuales), false],
                            ['Flujo neto anual (I − C)', fn ($r) => $dinero($r->ingresos_anuales - $r->costos_anuales), false],
                            ['VPN', fn ($r) => $dinero($r->vpn), true],
                            ['TIR', fn ($r) => $pct($r->tir), true],
                            ['CAUE (negativo = beneficio anual)', fn ($r) => $dinero($r->caue), true],
                            ['Relación B/C', fn ($r) => $r->bc === null ? '—' : number_format($r->bc, 4), true],
                            ['Recuperación simple', fn ($r) => $r->recuperacion === null ? '—' : number_format($r->recuperacion, 2) . ' años', false],
                        ];
                    @endphp
                    @foreach ($filas as [$etiqueta, $valor, $resaltar])
                        <tr class="{{ $resaltar ? 'font-semibold' : '' }}">
                            <td class="px-3 py-2">{{ $etiqueta }}</td>
                            @foreach ($resultados as $r)
                                <td class="px-3 py-2 text-right">{{ $valor($r) }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                    <tr>
                        <td class="px-3 py-2">Decisión</td>
                        @foreach ($resultados as $r)
                            <td class="px-3 py-2 text-right">
                                <x-insignia :color="$r->vpn >= 0 ? 'verde' : 'rojo'">{{ $r->vpn >= 0 ? 'Aceptable' : 'No aceptable' }}</x-insignia>
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </x-tarjeta>

        {{-- Criterios --}}
        <x-tarjeta titulo="Criterios de decisión">
            <ul class="grid gap-2 text-sm text-slate-600 sm:grid-cols-2">
                <li><strong>VPN ≥ 0:</strong> la opción alcanza la TMAR. Se elige la de mayor VPN.</li>
                <li><strong>TIR ≥ TMAR:</strong> la opción rinde más que la tasa mínima exigida.</li>
                <li><strong>CAUE menor:</strong> con la misma vida útil, elige la misma opción que el VPN.</li>
                <li><strong>B/C ≥ 1:</strong> los beneficios en valor presente superan a los costos.</li>
            </ul>
            <p class="mt-4 text-xs text-slate-500">
                Los supuestos (vida útil, salvamento, costo fijo y ahorro anual) y la fuente de cada precio se consultan en
                <a href="{{ $url('alternativas.index') }}" class="text-blue-600 hover:underline">Alternativas</a>.
            </p>
        </x-tarjeta>
        
        @include('evaluacion._memoria')
    @endif
@endsection