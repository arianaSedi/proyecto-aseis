@extends('layouts.sistema')

@section('titulo', 'Evaluación después de impuestos')

@section('contenido')
    @php
        $dinero = fn ($v) => $v === null ? '—' : ($v < 0 ? '−$' : '$') . number_format(abs($v), 2);
        $pct = fn ($v) => $v === null ? '—' : number_format($v * 100, 2) . ' %';
        $metodos = ['linea_recta' => 'Línea recta', 'suma_digitos' => 'Suma de dígitos (SDA)'];
    @endphp

    <p class="mb-6 max-w-3xl text-slate-600">
        UAI = I − C − D · Impuesto = UAI × T · UDI = UAI − Impuesto · FNE = UDI + D (+ salvamento en el último año).
    </p>

    <div class="mb-4 flex flex-wrap gap-2">
        @foreach ($cotizaciones as $c)
            <a href="{{ request()->fullUrlWithQuery(['cotizacion' => $c->codigo]) }}"
               class="rounded-xl px-4 py-2 text-sm font-medium {{ $c->codigo === $seleccionada->codigo ? 'bg-petroleo-700 text-white' : 'bg-white text-noche-900 shadow-sm hover:bg-fondo-100' }}">
                {{ $c->codigo }}
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ url()->current() }}" class="mb-6 grid gap-4 rounded-2xl bg-white p-5 shadow-sm sm:grid-cols-2 lg:grid-cols-4">
        <input type="hidden" name="cotizacion" value="{{ $seleccionada->codigo }}">
        <label class="block text-sm">
            <span class="mb-1 block text-slate-600">Tasa de impuesto (%)</span>
            <input type="number" name="impuesto" step="0.01" min="0" max="100" value="{{ round($tasaImpuesto * 100, 2) }}"
                   class="w-full rounded-xl border border-fondo-300 px-3 py-2 focus:border-petroleo-500 focus:outline-none focus:ring-2 focus:ring-petroleo-100">
        </label>
        <label class="block text-sm">
            <span class="mb-1 block text-slate-600">TMAR (%)</span>
            <input type="number" name="tmar" step="0.01" min="0" value="{{ round($tmar * 100, 2) }}"
                   class="w-full rounded-xl border border-fondo-300 px-3 py-2 focus:border-petroleo-500 focus:outline-none focus:ring-2 focus:ring-petroleo-100">
        </label>
        <label class="block text-sm">
            <span class="mb-1 block text-slate-600">Método de depreciación</span>
            <select name="metodo" class="w-full rounded-xl border border-fondo-300 px-3 py-2 focus:border-petroleo-500 focus:outline-none focus:ring-2 focus:ring-petroleo-100">
                @foreach ($metodos as $clave => $nombre)
                    <option value="{{ $clave }}" @selected($metodo === $clave)>{{ $nombre }}</option>
                @endforeach
            </select>
        </label>
        <div class="flex items-end">
            <x-boton class="w-full">Recalcular</x-boton>
        </div>
    </form>

    @if ($tasaImpuesto == 0)
        <div class="mb-6 rounded-2xl border border-mostaza-300 bg-mostaza-100 px-4 py-3 text-sm text-noche-900">
            La tasa de impuesto es 0 %: el resultado es igual al de antes de impuestos.
            {{ $fuenteImpuesto }}
        </div>
    @endif

    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-indicador titulo="VPN antes de impuestos" :valor="$dinero($indicadoresAntes['vpn'])"
                     :nota="'TIR ' . $pct($indicadoresAntes['tir'])" color="noche" />
        <x-indicador titulo="VPN después de impuestos" :valor="$dinero($indicadores['vpn'])"
                     :nota="'TIR ' . $pct($indicadores['tir'])" />
        <x-indicador titulo="CAUE después de impuestos" :valor="$dinero($indicadores['caue'])"
                     nota="Negativo = beneficio anual" />
        <x-indicador titulo="Escudo fiscal total" :valor="$dinero($escudoFiscalTotal)"
                     nota="Impuesto que se ahorra por depreciar" color="mostaza" />
    </div>

    <div class="mb-6 rounded-2xl p-5 {{ $indicadores['aceptable'] ? 'border border-emerald-200 bg-emerald-50' : 'border border-red-200 bg-red-50' }}">
        <p class="font-semibold {{ $indicadores['aceptable'] ? 'text-emerald-700' : 'text-red-700' }}">
            {{ $seleccionada->codigo }}:
            {{ $indicadores['aceptable'] ? 'sigue siendo aceptable después de impuestos' : 'no es aceptable después de impuestos' }}
            (VPN {{ $dinero($indicadores['vpn']) }} a TMAR {{ $pct($tmar) }}).
        </p>
        <p class="text-sm text-slate-600">{{ $seleccionada->nombre }} · {{ $seleccionada->proveedor }}</p>
    </div>

    @php
        $comparacion = [
            ['VPN', $dinero($indicadoresAntes['vpn']), $dinero($indicadores['vpn']), $dinero($indicadores['vpn'] - $indicadoresAntes['vpn'])],
            ['TIR', $pct($indicadoresAntes['tir']), $pct($indicadores['tir']),
                $indicadores['tir'] === null || $indicadoresAntes['tir'] === null ? '—' : number_format(($indicadores['tir'] - $indicadoresAntes['tir']) * 100, 2) . ' pts'],
            ['CAUE', $dinero($indicadoresAntes['caue']), $dinero($indicadores['caue']), $dinero($indicadores['caue'] - $indicadoresAntes['caue'])],
            ['Decisión', $indicadoresAntes['aceptable'] ? 'Aceptable' : 'No aceptable', $indicadores['aceptable'] ? 'Aceptable' : 'No aceptable', ''],
        ];
    @endphp
    <x-tarjeta titulo="Antes y después de impuestos" subtitulo="CAUE negativo = beneficio neto anual." class="mb-6">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[32rem] text-sm">
                <thead class="bg-fondo-100 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">Indicador</th>
                        <th class="px-3 py-2 text-right">Antes de impuestos</th>
                        <th class="px-3 py-2 text-right">Después de impuestos</th>
                        <th class="px-3 py-2 text-right">Diferencia</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-fondo-100">
                    @foreach ($comparacion as [$indicador, $antes, $despues, $diferencia])
                        <tr>
                            <td class="px-3 py-2 font-medium">{{ $indicador }}</td>
                            <td class="px-3 py-2 text-right">{{ $antes }}</td>
                            <td class="px-3 py-2 text-right font-semibold">{{ $despues }}</td>
                            <td class="px-3 py-2 text-right text-slate-500">{{ $diferencia }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-tarjeta>

    {{-- Tabla --}}
    <x-tarjeta titulo="Flujo de efectivo después de impuestos"
               :subtitulo="'Depreciación por ' . ($metodos[$metodo] ?? $metodo) . ' · T = ' . $pct($tasaImpuesto)">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[64rem] text-sm">
                <thead class="bg-fondo-100 text-slate-600">
                    <tr>
                        <th class="px-2 py-2 text-left">Año</th>
                        <th class="px-2 py-2 text-right">Ingresos</th>
                        <th class="px-2 py-2 text-right">Egresos</th>
                        <th class="px-2 py-2 text-right">Depreciación</th>
                        <th class="px-2 py-2 text-right">UAI</th>
                        <th class="px-2 py-2 text-right">Impuesto</th>
                        <th class="px-2 py-2 text-right">UDI</th>
                        <th class="px-2 py-2 text-right">Salvamento</th>
                        <th class="px-2 py-2 text-right">Escudo fiscal</th>
                        <th class="px-2 py-2 text-right">FNE</th>
                        <th class="px-2 py-2 text-right">Valor presente</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-fondo-100">
                    <tr>
                        <td class="px-2 py-2">0</td>
                        <td colspan="8"></td>
                        <td class="px-2 py-2 text-right font-semibold">{{ $dinero($flujos[0]) }}</td>
                        <td class="px-2 py-2 text-right">{{ $dinero($flujos[0]) }}</td>
                    </tr>
                    @foreach ($tabla as $fila)
                        <tr>
                            <td class="px-2 py-2">{{ $fila['anio'] }}</td>
                            <td class="px-2 py-2 text-right">{{ $dinero($fila['ingresos']) }}</td>
                            <td class="px-2 py-2 text-right">{{ $dinero($fila['egresos']) }}</td>
                            <td class="px-2 py-2 text-right">{{ $dinero($fila['depreciacion']) }}</td>
                            <td class="px-2 py-2 text-right">{{ $dinero($fila['uai']) }}</td>
                            <td class="px-2 py-2 text-right">{{ $dinero($fila['impuesto']) }}</td>
                            <td class="px-2 py-2 text-right">{{ $dinero($fila['udi']) }}</td>
                            <td class="px-2 py-2 text-right">
                                {{ $dinero($fila['salvamento']) }}
                                @if ($fila['impuesto_salvamento'] > 0)
                                    <span class="block text-xs text-red-600">− {{ $dinero($fila['impuesto_salvamento']) }} impuesto</span>
                                @endif
                            </td>
                            <td class="px-2 py-2 text-right text-petroleo-700">{{ $dinero($fila['escudo_fiscal']) }}</td>
                            <td class="px-2 py-2 text-right font-semibold">{{ $dinero($fila['fne']) }}</td>
                            <td class="px-2 py-2 text-right">{{ $dinero($fila['valor_presente']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-fondo-100 font-semibold">
                    <tr>
                        <td class="px-2 py-2" colspan="10">VPN después de impuestos (suma de valores presentes)</td>
                        <td class="px-2 py-2 text-right">{{ $dinero($indicadores['vpn']) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <p class="mt-3 text-xs text-slate-500">Fuente de la tasa de impuesto: {{ $fuenteImpuesto }}</p>
    </x-tarjeta>
@endsection