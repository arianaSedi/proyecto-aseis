<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Inicio') · ASEIS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-fondo-200 text-noche-900">
    @php
        $iconos = [
            'inicio' => 'M3 11l9-8 9 8M5 10v10h14V10',
            'matematica' => 'M6 3h12v18H6zM9 7h6M9 12h.01M12 12h.01M15 12h.01M9 16h.01M12 16h.01M15 16h.01',
            'alternativas' => 'M9 6h11M9 12h11M9 18h11M4 6h.01M4 12h.01M4 18h.01',
            'evaluacion' => 'M4 20V11M10 20V5M16 20v-8M3 20h18',
            'depreciacion' => 'M3 7l6 6 4-4 8 8M21 11v6h-6',
            'impuestos' => 'M19 5L5 19M7 7h.01M17 17h.01',
            'reemplazo' => 'M4 4v6h6M20 20v-6h-6M5.5 15a7 7 0 0 0 12 2.5M18.5 9a7 7 0 0 0-12-2.5',
            'contable' => 'M5 4h14v16H5zM9 8h6M9 12h6M9 16h3',
            'costos' => 'M3 12l9-9h9v9l-9 9zM16 8h.01',
        ];

        $secciones = [
            'General' => [
                ['Inicio', 'inicio', 'inicio', null, 'inicio'],
            ],
            'Ingeniería económica' => [
                ['Matemática financiera', 'economia.matematica', 'economia.matematica*', null, 'matematica'],
                ['Alternativas', 'alternativas.index', 'alternativas.*', 'maqueta.alternativas', 'alternativas'],
                ['Evaluación económica', 'economia.evaluacion', 'economia.evaluacion*', 'maqueta.evaluacion', 'evaluacion'],
                ['Depreciación', 'economia.depreciacion', 'economia.depreciacion*', 'maqueta.depreciacion', 'depreciacion'],
                ['Después de impuestos', 'economia.impuestos', 'economia.impuestos*', null, 'impuestos'],
                ['Reemplazo', 'economia.reemplazo', 'economia.reemplazo*', null, 'reemplazo'],
            ],
            'Sistemas contables' => [
                ['Registro contable', 'contabilidad.index', 'contabilidad.*', null, 'contable'],
                ['Costos por evento', 'costos.index', 'costos.*', null, 'costos'],
            ],
        ];
    @endphp

    <div class="flex min-h-screen">
        <div id="fondoMenu" class="fixed inset-0 z-30 hidden bg-noche-900/50 lg:hidden"></div>

        <aside id="menuLateral"
               class="sin-scroll fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col overflow-y-auto bg-noche-900 text-slate-300 transition-transform
                      lg:sticky lg:top-0 lg:h-screen lg:translate-x-0">
            <div class="flex items-center gap-3 rounded-b-3xl bg-petroleo-700 px-6 py-5">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border-4 border-white/80 bg-petroleo-500 font-bold text-white">
                    AS
                </div>
                <div>
                    <p class="font-semibold text-white">ASEIS</p>
                    <p class="text-xs text-petroleo-100">Decisión de inversión</p>
                </div>
            </div>

            <nav class="flex-1 space-y-4 py-5 pr-0 pl-4 text-sm">
                @foreach ($secciones as $titulo => $items)
                    <div>
                        <p class="mb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ $titulo }}</p>
                        @foreach ($items as [$nombre, $ruta, $patron, $maqueta, $icono])
                            @php
                                $destino = Route::has($ruta) ? $ruta : ($maqueta && Route::has($maqueta) ? $maqueta : null);
                                $activo = $destino && (request()->routeIs($patron) || ($destino === $maqueta && request()->routeIs($maqueta . '*')));
                            @endphp

                            @if ($destino)
                                <a href="{{ route($destino) }}"
                                   class="flex items-center gap-3 rounded-l-full py-2 pr-4 pl-3 transition
                                          {{ $activo ? 'bg-fondo-200 font-semibold text-petroleo-700' : 'hover:bg-white/10 hover:text-white' }}">
                                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                        <path d="{{ $iconos[$icono] }}" />
                                    </svg>
                                    <span class="flex-1 truncate">{{ $nombre }}</span>
                                    @if ($destino === $maqueta)
                                        <span class="rounded-full bg-mostaza-400/20 px-2 text-[10px] font-semibold uppercase text-mostaza-400">maqueta</span>
                                    @endif
                                </a>
                            @else
                                <span class="flex items-center gap-3 py-2 pr-4 pl-3 text-slate-600">
                                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                        <path d="{{ $iconos[$icono] }}" />
                                    </svg>
                                    <span class="flex-1 truncate">{{ $nombre }}</span>
                                    <span class="text-[10px] uppercase">pronto</span>
                                </span>
                            @endif
                        @endforeach
                    </div>
                @endforeach
            </nav>
        </aside>

        {{-- Contenido --}}
       <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex items-center gap-3 px-4 py-4 sm:px-6 lg:px-8 lg:py-6">
                <button id="botonMenu" type="button" class="rounded-xl bg-white p-2 shadow-sm lg:hidden" aria-label="Abrir menú">
                    <svg class="h-6 w-6 text-petroleo-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <h1 class="text-xl font-semibold text-noche-900 sm:text-2xl">@yield('titulo', 'Inicio')</h1>
            </header>

            <main class="flex-1 px-4 pb-8 sm:px-6 lg:px-8">
                @if (session('ok'))
                    <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">{{ session('ok') }}</div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                        <p class="font-semibold">Revisa los datos:</p>
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('contenido')
            </main>
        </div>
    </div>

    <script>
        // Abrir y cerrar el menu lateral en pantallas pequeñas
        (function () {
            const menu = document.getElementById('menuLateral');
            const fondo = document.getElementById('fondoMenu');
            const boton = document.getElementById('botonMenu');

            function alternar(abrir) {
                menu.classList.toggle('-translate-x-full', !abrir);
                fondo.classList.toggle('hidden', !abrir);
            }

            boton.addEventListener('click', () => alternar(true));
            fondo.addEventListener('click', () => alternar(false));
        })();
    </script>
    @stack('scripts')
</body>
</html>