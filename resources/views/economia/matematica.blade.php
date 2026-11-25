@extends('layouts.sistema')

@section('titulo', 'Matemática financiera')

@section('contenido')
    @php
        // Operación elegida (después de calcular o al volver con errores)
        $op = old('operacion', $datos['operacion'] ?? 'simple');

        $grupos = [
            'Interés' => [
                'simple' => 'Interés simple',
                'compuesto' => 'Interés compuesto',
            ],
            'Cantidad única' => [
                'presente' => 'Valor presente (P/F)',
                'futuro' => 'Valor futuro (F/P)',
            ],
            'Anualidades' => [
                'pa' => 'Valor presente de anualidad (P/A)',
                'fa' => 'Valor futuro de anualidad (F/A)',
                'ap' => 'Recuperación de capital (A/P)',
                'af' => 'Fondo de amortización (A/F)',
            ],
            'Tasas' => [
                'efectiva' => 'Tasa nominal a efectiva anual',
                'nominal' => 'Tasa efectiva anual a nominal',
                'equivalente' => 'Tasas efectivas equivalentes',
            ],
        ];
    @endphp

    <p class="mb-6 text-slate-600">Calculadora de intereses, tasas y equivalencias económicas.</p>

    <div class="grid gap-6 lg:grid-cols-5">
        {{-- Formulario --}}
        <form method="POST" action="{{ route('economia.matematica.calcular') }}"
              class="space-y-4 rounded-2xl bg-white p-6 shadow lg:col-span-2">
            @csrf

            <label class="block text-sm">
                <span class="mb-1 block text-slate-600">Operación</span>
                <select name="operacion" id="operacion" required class="w-full rounded border border-fondo-300 px-3 py-2">
                    @foreach ($grupos as $grupo => $opciones)
                        <optgroup label="{{ $grupo }}">
                            @foreach ($opciones as $valor => $texto)
                                <option value="{{ $valor }}" @selected($op === $valor)>{{ $texto }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </label>

            <label id="campoCapital" class="block text-sm">
                <span id="labelCapital" class="mb-1 block text-slate-600">Capital ($)</span>
                <input type="number" name="capital" id="capital" step="0.01" min="0"
                       value="{{ old('capital', $datos['capital'] ?? 450) }}"
                       class="w-full rounded border border-fondo-300 px-3 py-2">
            </label>

            <label class="block text-sm">
                <span id="labelTasa" class="mb-1 block text-slate-600">Tasa de interés por período (%)</span>
                <input type="number" name="tasa" id="tasa" step="0.0001" min="0" max="1000" required
                       value="{{ old('tasa', $datos['tasa'] ?? 10) }}"
                       class="w-full rounded border border-fondo-300 px-3 py-2">
            </label>

            <label class="block text-sm">
                <span id="labelPeriodos" class="mb-1 block text-slate-600">Número de períodos</span>
                <input type="number" name="periodos" id="periodos" min="1" max="1000" required
                       value="{{ old('periodos', $datos['periodos'] ?? 5) }}"
                       class="w-full rounded border border-fondo-300 px-3 py-2">
            </label>

            <div id="camposConversion" class="hidden space-y-4">
                @php
                    $frecuencias = [
                        1 => 'Anual',
                        2 => 'Semestral',
                        3 => 'Cuatrimestral',
                        4 => 'Trimestral',
                        6 => 'Bimestral',
                        12 => 'Mensual',
                        24 => 'Quincenal',
                    ];
                @endphp

                <label class="block text-sm">
                    <span class="mb-1 block text-slate-600">
                        Período de origen
                    </span>

                    <select name="frecuencia_origen"
                            class="w-full rounded border border-fondo-300 px-3 py-2">
                        @foreach ($frecuencias as $valor => $nombre)
                            <option value="{{ $valor }}"
                                @selected(old('frecuencia_origen', $datos['frecuencia_origen'] ?? 1) == $valor)>
                                {{ $nombre }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label class="block text-sm">
                    <span class="mb-1 block text-slate-600">
                        Período de destino
                    </span>

                    <select name="frecuencia_destino"
                            class="w-full rounded border border-fondo-300 px-3 py-2">
                        @foreach ($frecuencias as $valor => $nombre)
                            <option value="{{ $valor }}"
                                @selected(old('frecuencia_destino', $datos['frecuencia_destino'] ?? 12) == $valor)>
                                {{ $nombre }}
                            </option>
                        @endforeach
                    </select>
                </label>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="rounded-xl bg-petroleo-700 px-4 py-2 text-white hover:bg-petroleo-800">Calcular</button>
                <a href="{{ route('economia.matematica') }}" class="rounded-xl bg-fondo-100 px-4 py-2 hover:bg-fondo-200">Limpiar</a>
            </div>
        </form>

        {{-- Resultado --}}
        <div class="lg:col-span-3">
            @isset($resultado)
                <div class="rounded-2xl bg-white p-6 shadow">
                    <p class="text-sm uppercase tracking-wide text-slate-500">{{ $concepto }}</p>
                    <p class="mt-2 text-4xl font-bold text-slate-900">
                        @if ($unidad == '%')
                            {{ number_format($resultado, 4) }} %
                        @else
                            ${{ number_format($resultado, 2) }}
                        @endif
                    </p>

                    <div class="mt-6 rounded bg-fondo-100 p-4">
                        <p class="text-sm text-slate-500">Fórmula</p>
                        <p class="font-mono text-lg">{{ $formula }}</p>
                    </div>

                                        
                    <div class="mt-4">
                        <p class="mb-2 text-sm text-slate-500">Datos usados</p>

                        <dl class="grid grid-cols-2 gap-y-1 text-sm sm:grid-cols-3">

                            @if (!in_array($datos['operacion'], ['efectiva', 'nominal', 'equivalente']))
                                <div>
                                    <dt class="text-slate-500">Monto</dt>
                                    <dd class="font-medium">
                                        ${{ number_format((float) ($datos['capital'] ?? 0), 2) }}
                                    </dd>
                                </div>
                            @endif

                            <div>
                                <dt class="text-slate-500">
                                    @switch($datos['operacion'])
                                        @case('efectiva')
                                            Tasa nominal anual
                                            @break
                                        @case('nominal')
                                            Tasa efectiva anual
                                            @break
                                        @case('equivalente')
                                            Tasa efectiva de origen
                                            @break
                                        @default
                                            Tasa (i)
                                    @endswitch
                                </dt>

                                <dd class="font-medium">
                                    {{ $datos['tasa'] }} %
                                </dd>
                            </div>

                            @if ($datos['operacion'] === 'equivalente')
                                <div>
                                    <dt class="text-slate-500">Período de origen</dt>
                                    <dd class="font-medium">
                                        {{ $frecuencias[$datos['frecuencia_origen']] }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-slate-500">Período de destino</dt>
                                    <dd class="font-medium">
                                        {{ $frecuencias[$datos['frecuencia_destino']] }}
                                    </dd>
                                </div>
                            @else
                                <div>
                                    <dt class="text-slate-500">
                                        {{ in_array($datos['operacion'], ['efectiva', 'nominal'])
                                            ? 'Capitalizaciones por año'
                                            : 'Períodos (n)' }}
                                    </dt>

                                    <dd class="font-medium">
                                        {{ $datos['periodos'] }}
                                    </dd>
                                </div>
                            @endif

                        </dl>
                    </div>
                </div>
            @else
                <div class="flex h-full min-h-48 items-center justify-center rounded-2xl border-2 border-dashed border-fondo-300 p-6 text-center text-slate-500">
                    Elige una operación, escribe los datos y presiona <strong class="mx-1">Calcular</strong> para ver el resultado.
                </div>
            @endisset
        </div>
    </div>
@endsection


@push('scripts')
    <script>
        (function () {
            const operacion = document.getElementById('operacion');

            const etiquetas = {
                simple: 'Capital inicial ($)',
                compuesto: 'Capital inicial ($)',
                presente: 'Valor futuro ($)',
                futuro: 'Capital inicial ($)',
                pa: 'Anualidad ($)',
                fa: 'Anualidad ($)',
                ap: 'Valor presente ($)',
                af: 'Valor futuro ($)'
            };

            function actualizarCampos() {
                const efectiva = operacion.value === 'efectiva';
                const nominal = operacion.value === 'nominal';
                const equivalente = operacion.value === 'equivalente';

                const conversion = efectiva || nominal || equivalente;

                document.getElementById('periodos').required = !equivalente;
                document.getElementById('periodos').disabled = equivalente;

                document.getElementById('capital').disabled = conversion;
                document.getElementById('capital').required = !conversion;

                document.getElementById('campoCapital').style.display =
                    conversion ? 'none' : 'block';

                document.getElementById('camposConversion').style.display =
                    equivalente ? 'block' : 'none';

                document.getElementById('periodos').closest('label').style.display =
                    equivalente ? 'none' : 'block';

                document.getElementById('labelCapital').textContent =
                    etiquetas[operacion.value] || 'Capital ($)';

                document.getElementById('labelTasa').textContent =
                    efectiva ? 'Tasa nominal anual (%)' :
                    nominal ? 'Tasa efectiva anual (%)' :
                    equivalente ? 'Tasa efectiva de origen (%)' :
                    'Tasa de interés por período (%)';

                document.getElementById('labelPeriodos').textContent =
                    conversion ? 'Capitalizaciones por año' :
                    'Número de períodos';

                document.querySelectorAll('#camposConversion select').forEach(campo => {
                    campo.disabled = !equivalente;
                    campo.required = equivalente;
                });
            }

            operacion.addEventListener('change', actualizarCampos);
            actualizarCampos();
        })();
    </script>
@endpush