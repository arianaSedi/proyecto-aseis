<?php

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

/*
| MAQUETAS TEMPORALES
| Sirven para ver el diseño con los datos del Bloque A mientras Miriam
| programa los controladores. Cuando sus rutas reales existan, se borra
| este archivo y su línea en routes/web.php.
*/

$bloqueA = function (): Collection {
    $alternativa = fn (array $datos, array $cotizaciones) => (object) ($datos + [
        'cotizaciones' => collect($cotizaciones)->map(fn ($c) => (object) ($c + ['archivo_url' => null])),
    ]);

    return collect([
        $alternativa([
            'id' => 1,
            'codigo' => 'A',
            'nombre' => 'Prensa térmica para camisas con DTF',
            'descripcion' => 'Prensa plana para aplicar transferencias DTF compradas a terceros a las 110 camisas de cada jornada.',
            'vida_util' => 5,
            'porcentaje_salvamento' => 10,
            'costos_anuales' => 30,
            'ingresos_anuales' => 330,
        ], [
            ['id' => 1, 'codigo' => 'A1', 'proveedor' => 'Soluciones Centroamérica, San Miguel', 'descripcion' => 'Prensa térmica plana Colormaker de 32 × 45 cm', 'monto' => 450.00, 'tipo_fuente' => 'captura', 'fecha' => '2026-11-23', 'condicion' => 'Precio en conversación de WhatsApp facilitada por la ASEIS; confirmar garantía y vigencia'],
            ['id' => 2, 'codigo' => 'A2', 'proveedor' => 'ACOSA, San Miguel', 'descripcion' => 'Prensa plana SB05 de 38 × 38 cm', 'monto' => 395.00, 'tipo_fuente' => 'catalogo', 'fecha' => null, 'condicion' => 'Precio de catálogo anterior; verificar existencia y precio actual'],
        ]),
        $alternativa([
            'id' => 2,
            'codigo' => 'B',
            'nombre' => 'Equipo para sublimación multiproducto',
            'descripcion' => 'Prensa térmica y equipo de sublimación para tazas, termos, lapiceros, gorras y camisas.',
            'vida_util' => 5,
            'porcentaje_salvamento' => 10,
            'costos_anuales' => 90,
            'ingresos_anuales' => 330,
        ], [
            ['id' => 3, 'codigo' => 'B1', 'proveedor' => 'Soluciones Centroamérica, San Miguel', 'descripcion' => 'Combo sublimación 10 en 1 con Epson F170, papel A4 y cinta térmica', 'monto' => 1119.00, 'tipo_fuente' => 'captura', 'fecha' => null, 'condicion' => 'Promoción anunciada para octubre de 2026; requiere reconfirmación'],
            ['id' => 4, 'codigo' => 'B2', 'proveedor' => 'Varios proveedores', 'descripcion' => 'Prensa 8 en 1 (ACOSA $454.99), Epson F170 (Valdés $367.25), prensa de lapiceros (TodoTransfer $299.99) y horno (Sublimaster $349.99)', 'monto' => 1472.22, 'tipo_fuente' => 'catalogo', 'fecha' => null, 'condicion' => 'Precios publicados; no incluyen fletes ni insumos'],
        ]),
    ]);
};

Route::prefix('maqueta')->name('maqueta.')->group(function () use ($bloqueA) {
    Route::get('/alternativas', fn () => view('alternativas.index', ['alternativas' => $bloqueA()]))
        ->name('alternativas');

    Route::get('/alternativas/nueva', fn () => view('alternativas.form', ['alternativa' => null]))
        ->name('alternativas.nueva');

    Route::get('/alternativas/{codigo}', function (string $codigo) use ($bloqueA) {
        $alternativa = $bloqueA()->firstWhere('codigo', strtoupper($codigo));
        abort_unless($alternativa, 404);

        return view('alternativas.show', ['alternativa' => $alternativa]);
    })->name('alternativas.ver');

    Route::get('/alternativas/{codigo}/editar', function (string $codigo) use ($bloqueA) {
        $alternativa = $bloqueA()->firstWhere('codigo', strtoupper($codigo));
        abort_unless($alternativa, 404);

        return view('alternativas.form', ['alternativa' => $alternativa]);
    })->name('alternativas.editar');
});


/*
| Evaluación económica (maqueta): calcula VPN, TIR, CAUE, B/C y recuperación
| de cada cotización con los supuestos de su alternativa.
| Cuando Miriam tenga su controlador de evaluación, esto se borra.
*/
$evaluar = function (float $inversion, int $n, float $salvamento, float $ingresos, float $costos, float $tmar): array {
    $fne = $ingresos - $costos;
    $flujos = [0 => -$inversion];
    for ($t = 1; $t <= $n; $t++) {
        $flujos[$t] = $fne + ($t === $n ? $salvamento : 0);
    }

    $van = function (float $tasa) use ($flujos): float {
        $total = 0.0;
        foreach ($flujos as $t => $f) {
            $total += $f / (1 + $tasa) ** $t;
        }
        return $total;
    };

    $pa = $tmar == 0.0 ? $n : ((1 + $tmar) ** $n - 1) / ($tmar * (1 + $tmar) ** $n);
    $pf = 1 / (1 + $tmar) ** $n;
    $vpn = $van($tmar);

    // TIR por bisección (null si el flujo no cambia de signo)
    $tir = null;
    $a = -0.99;
    $b = 10.0;
    if ($van($a) * $van($b) < 0) {
        for ($k = 0; $k < 200; $k++) {
            $m = ($a + $b) / 2;
            if ($van($a) * $van($m) <= 0) {
                $b = $m;
            } else {
                $a = $m;
            }
        }
        $tir = ($a + $b) / 2;
    }

    $vpCostos = $inversion - $salvamento * $pf + $costos * $pa;

    return [
        'flujos' => $flujos,
        'vpn' => $vpn,
        'caue' => -$vpn / $pa,
        'tir' => $tir,
        'bc' => $vpCostos > 0 ? ($ingresos * $pa) / $vpCostos : null,
        'recuperacion' => $fne > 0 ? $inversion / $fne : null,
    ];
};

Route::get('/maqueta/evaluacion', function (Request $request) use ($bloqueA, $evaluar) {
    $tmar = max((float) $request->query('tmar', 10), 0) / 100;

    $resultados = $bloqueA()->flatMap(function ($alternativa) use ($tmar, $evaluar) {
        return $alternativa->cotizaciones->map(function ($c) use ($alternativa, $tmar, $evaluar) {
            $salvamento = $c->monto * $alternativa->porcentaje_salvamento / 100;

            return (object) ([
                'codigo' => $c->codigo,
                'alternativa' => $alternativa->codigo,
                'nombre' => $c->descripcion,
                'proveedor' => $c->proveedor,
                'inversion' => $c->monto,
                'salvamento' => $salvamento,
                'vida_util' => $alternativa->vida_util,
                'costos_anuales' => $alternativa->costos_anuales,
                'ingresos_anuales' => $alternativa->ingresos_anuales,
            ] + $evaluar($c->monto, $alternativa->vida_util, $salvamento, $alternativa->ingresos_anuales, $alternativa->costos_anuales, $tmar));
        });
    })->values();

    return view('evaluacion.index', [
        'tmar' => $tmar,
        'fuenteTmar' => 'Valor de prueba; pendiente de confirmar con la ASEIS o el docente',
        'resultados' => $resultados,
    ]);
})->name('maqueta.evaluacion');


$depreciar = function (float $costo, float $salvamento, int $n): array {
    $base = $costo - $salvamento;
    $sda = $n * ($n + 1) / 2;
    $tablas = ['linea_recta' => [], 'suma_digitos' => []];

    foreach (['linea_recta', 'suma_digitos'] as $metodo) {
        $acumulada = 0.0;
        for ($t = 1; $t <= $n; $t++) {
            $d = $metodo === 'linea_recta' ? $base / $n : ($n - $t + 1) / $sda * $base;
            $acumulada += $d;
            $tablas[$metodo][] = [
                'anio' => $t,
                'fraccion' => $metodo === 'suma_digitos' ? ($n - $t + 1) . '/' . $sda : null,
                'depreciacion' => $d,
                'acumulada' => $acumulada,
                'valor_libros' => $costo - $acumulada,
            ];
        }
    }

    return $tablas;
};

Route::get('/maqueta/depreciacion', function (Request $request) use ($bloqueA, $depreciar) {
    $cotizaciones = $bloqueA()->flatMap(fn ($a) => $a->cotizaciones->map(fn ($c) => (object) [
        'codigo' => $c->codigo,
        'nombre' => $c->descripcion,
        'proveedor' => $c->proveedor,
        'inversion' => $c->monto,
        'salvamento' => $c->monto * $a->porcentaje_salvamento / 100,
        'vida_util' => $a->vida_util,
    ]))->values();

    $seleccionada = $cotizaciones->firstWhere('codigo', strtoupper($request->query('cotizacion', 'A1')))
        ?? $cotizaciones->first();

    return view('depreciacion.index', [
        'cotizaciones' => $cotizaciones,
        'seleccionada' => $seleccionada,
        'tablas' => $depreciar($seleccionada->inversion, $seleccionada->salvamento, $seleccionada->vida_util),
    ]);
})->name('maqueta.depreciacion');