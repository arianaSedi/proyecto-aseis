<?php

namespace App\Http\Controllers\Economia;

use App\Http\Controllers\Controller;
use App\Models\Alternativa;
use Illuminate\Http\Request;

class DepreciacionController extends Controller
{
    public function index(Request $request)
    {
        $alternativas = Alternativa::with('cotizaciones')
            ->orderBy('codigo')
            ->get();

        $cotizaciones = $alternativas->flatMap(function ($alternativa) {

            return $alternativa->cotizaciones->map(function ($c) use ($alternativa) {

                return (object) [
                    'codigo' => $c->codigo,
                    'nombre' => $c->descripcion,
                    'proveedor' => $c->proveedor,
                    'inversion' => (float) $c->monto,
                    'salvamento' => (float) $c->monto
                        * $alternativa->porcentaje_salvamento / 100,
                    'vida_util' => (int) $alternativa->vida_util,
                ];

            });

        })->sortBy('codigo')->values();

        abort_if(
            $cotizaciones->isEmpty(),
            404,
            'No existen cotizaciones para calcular la depreciación.'
        );

        $seleccionada = $cotizaciones->firstWhere(
            'codigo',
            strtoupper($request->query('cotizacion', 'A1'))
        ) ?? $cotizaciones->first();

        $tablas = $this->depreciar(
            $seleccionada->inversion,
            $seleccionada->salvamento,
            $seleccionada->vida_util
        );

        return view('depreciacion.index', [
            'cotizaciones' => $cotizaciones,
            'seleccionada' => $seleccionada,
            'tablas' => $tablas,
        ]);
    }

    private function depreciar(
        float $costo,
        float $salvamento,
        int $n
    ): array {

        // Base depreciable
        $base = $costo - $salvamento;

        // Suma de los dígitos de los años
        $sda = $n * ($n + 1) / 2;

        $tablas = [
            'linea_recta' => [],
            'suma_digitos' => [],
        ];

        foreach (['linea_recta', 'suma_digitos'] as $metodo) {

            $acumulada = 0.0;

            for ($t = 1; $t <= $n; $t++) {

                if ($metodo === 'linea_recta') {

                    $depreciacion = $base / $n;
                    $fraccion = null;

                } else {

                    $aniosRestantes = $n - $t + 1;

                    $depreciacion = ($aniosRestantes / $sda)
                        * $base;

                    $fraccion = $aniosRestantes . '/' . $sda;
                }

                $acumulada += $depreciacion;

                $valorLibros = $costo - $acumulada;

                $tablas[$metodo][] = [
                    'anio' => $t,
                    'fraccion' => $fraccion,
                    'depreciacion' => $depreciacion,
                    'acumulada' => $acumulada,
                    'valor_libros' => $valorLibros,
                ];
            }
        }

        return $tablas;
    }
}