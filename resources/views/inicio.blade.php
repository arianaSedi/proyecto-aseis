@extends('layouts.sistema')

@section('titulo', 'Inicio')

@section('contenido')
    <div class="mb-8 rounded-2xl bg-white p-6 shadow">
        <h2 class="text-2xl font-bold">Aplicación de apoyo a la decisión de inversión</h2>
        <p class="mt-2 max-w-3xl text-slate-600">
            Asociación de Estudiantes de Ingeniería de Sistemas (ASEIS). La aplicación integra el registro contable y
            el sistema de costos con las técnicas de evaluación de Ingeniería Económica para recomendar la alternativa
            de inversión más viable.
        </p>
    </div>

    @php
        $enlace = fn (string $real, string $maqueta) => Route::has($real) ? route($real) : (Route::has($maqueta) ? route($maqueta) : null);
    @endphp
    <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-indicador titulo="Alternativas" valor="2" nota="A: prensa DTF · B: sublimación"
                     :href="$enlace('alternativas.index', 'maqueta.alternativas')" />
        <x-indicador titulo="Cotizaciones" valor="4" nota="Dos por alternativa" color="mostaza"
                     :href="$enlace('alternativas.index', 'maqueta.alternativas')" />
        <x-indicador titulo="Menor inversión" valor="$395.00" nota="A2 · ACOSA, San Miguel"
                     :href="$enlace('economia.evaluacion', 'maqueta.evaluacion')" />
        <x-indicador titulo="Mayor inversión" valor="$1,472.22" nota="B2 · Varios proveedores" color="noche"
                     :href="$enlace('economia.evaluacion', 'maqueta.evaluacion')" />
    </div>

    <div class="mb-8 grid gap-4 md:grid-cols-3">
        <div class="rounded-2xl bg-white p-5 shadow">
            <p class="text-xs uppercase tracking-wide text-slate-500">Organización</p>
            <p class="mt-1 font-semibold">ASEIS</p>
            <p class="text-sm text-slate-600">Asociación de estudiantes, sin fines de lucro</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow">
            <p class="text-xs uppercase tracking-wide text-slate-500">Decisión de inversión</p>
            <p class="mt-1 font-semibold">Compra de maquinaria de personalización</p>
            <p class="text-sm text-slate-600">Hoy encargan 330 camisas al año a $6.00 cada una</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow">
            <p class="text-xs uppercase tracking-wide text-slate-500">Alternativas</p>
            <p class="mt-1 font-semibold">2 alternativas · 4 cotizaciones</p>
            <p class="text-sm text-slate-600">Dos cotizaciones de proveedores distintos por alternativa</p>
        </div>
    </div>

    {{-- Alternativas y cotizaciones (Bloque A) --}}
    @php
        $alternativasBloqueA = [
            [
                'codigo' => 'A',
                'nombre' => 'Prensa térmica para camisas con DTF',
                'cotizaciones' => [
                    ['A1', 'Prensa térmica Colormaker 32 × 45 cm', 'Soluciones Centroamérica', 450.00],
                    ['A2', 'Prensa plana SB05 38 × 38 cm', 'ACOSA, San Miguel', 395.00],
                ],
            ],
            [
                'codigo' => 'B',
                'nombre' => 'Equipo para sublimación multiproducto',
                'cotizaciones' => [
                    ['B1', 'Combo sublimación 10 en 1 con Epson F170', 'Soluciones Centroamérica', 1119.00],
                    ['B2', 'Prensa 8 en 1 + Epson F170 + prensa de lapiceros + horno', 'Varios proveedores', 1472.22],
                ],
            ],
        ];
    @endphp

    <div class="mb-8 grid gap-6 lg:grid-cols-2">
        @foreach ($alternativasBloqueA as $alt)
            <div class="rounded-2xl bg-white p-6 shadow">
                <p class="text-sm font-semibold text-slate-500">Alternativa {{ $alt['codigo'] }}</p>
                <h2 class="mb-3 text-lg font-semibold">{{ $alt['nombre'] }}</h2>
                <table class="w-full text-sm">
                    <thead class="bg-fondo-100 text-slate-600">
                        <tr>
                            <th class="px-3 py-2 text-left">Cotización</th>
                            <th class="px-3 py-2 text-left">Equipo y proveedor</th>
                            <th class="px-3 py-2 text-right">Inversión</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($alt['cotizaciones'] as [$codigo, $equipo, $proveedor, $monto])
                            <tr class="align-top">
                                <td class="px-3 py-2 font-semibold">{{ $codigo }}</td>
                                <td class="px-3 py-2">
                                    {{ $equipo }}
                                    <span class="block text-xs text-slate-500">{{ $proveedor }}</span>
                                </td>
                                <td class="px-3 py-2 text-right">${{ number_format($monto, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>
    <p class="-mt-5 mb-8 text-xs text-slate-500">
        Fuente: Bloque A, información recopilada de la ASEIS (23/11/2026). Precios por reconfirmar con cotización escrita.
    </p>

    @php
        $modulos = [
            ['Matemática financiera', 'economia.matematica', 'Interés simple y compuesto, valor presente y futuro, anualidades y tasa efectiva.'],
            ['Alternativas', 'alternativas.index', 'Registro de las alternativas de inversión y sus cotizaciones.'],
            ['Evaluación económica', 'economia.evaluacion', 'VPN, TIR, CAUE, B/C y comparación de alternativas.'],
            ['Depreciación', 'economia.depreciacion', 'Línea recta y suma de dígitos, con tablas por año.'],
            ['Después de impuestos', 'economia.impuestos', 'Flujo UAI → impuesto → UDI → FNE de cada alternativa.'],
            ['Reemplazo', 'economia.reemplazo', 'Comparación del equipo actual contra el equipo nuevo.'],
        ];
    @endphp

    <h2 class="mb-3 text-lg font-semibold">Módulos</h2>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($modulos as [$nombre, $ruta, $descripcion])
            @if (Route::has($ruta))
                <a href="{{ route($ruta) }}" class="rounded-2xl bg-white p-5 shadow transition hover:shadow-md hover:ring-2 hover:ring-slate-300">
                    <p class="font-semibold">{{ $nombre }}</p>
                    <p class="mt-1 text-sm text-slate-600">{{ $descripcion }}</p>
                </a>
            @else
                <div class="rounded-2xl bg-white/60 p-5 shadow-sm">
                    <p class="font-semibold text-slate-400">{{ $nombre }} <span class="ml-1 text-xs uppercase">pronto</span></p>
                    <p class="mt-1 text-sm text-slate-400">{{ $descripcion }}</p>
                </div>
            @endif
        @endforeach
    </div>
@endsection