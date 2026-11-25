<?php

namespace App\Http\Controllers\Economia;

use App\Http\Controllers\Controller;
use App\Models\Alternativa;
use Illuminate\Http\Request;

class ReemplazoController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'tmar' => 'nullable|numeric|min:0|max:100',
            'cotizacion' => 'nullable|string|max:20',
            'costo_mantenimiento' => 'nullable|numeric|min:0',
            'incremento_mantenimiento' => 'nullable|numeric|min:0|max:100',
            'perdida_valor' => 'nullable|numeric|min:0|max:100',
        ]);

        $tmar = (float) $request->query('tmar', 10) / 100;

        $alternativas = Alternativa::with('cotizaciones')
            ->orderBy('codigo')
            ->get();

        $cotizaciones = $alternativas->flatMap(function ($a) {
            return $a->cotizaciones->map(function ($c) use ($a) {
                return (object) [
                    'codigo' => $c->codigo,
                    'nombre' => $c->descripcion,
                    'proveedor' => $c->proveedor,
                    'inversion' => (float) $c->monto,
                    'vida_util' => (int) $a->vida_util,
                    'costos_anuales' => (float) $a->costos_anuales,
                    'salvamento' => (float) $c->monto
                        * $a->porcentaje_salvamento / 100,
                ];
            });
        })->sortBy('codigo')->values();

        abort_if(
            $cotizaciones->isEmpty(),
            404,
            'No existen cotizaciones registradas.'
        );

        $seleccionada = $cotizaciones->firstWhere(
            'codigo',
            strtoupper($request->query('cotizacion', 'A1'))
        ) ?? $cotizaciones->first();

        $inversion = $seleccionada->inversion;
        $n = $seleccionada->vida_util;

        abort_if($n <= 0, 422, 'La vida útil debe ser positiva.');

        $mantenimiento = (float) $request->query(
            'costo_mantenimiento',
            $seleccionada->costos_anuales
        );

        $incremento = (float) $request->query(
            'incremento_mantenimiento',
            20
        ) / 100;

        $perdidaValor = (float) $request->query(
            'perdida_valor',
            30
        ) / 100;

        $tabla = [];
        $vpMantenimiento = 0;

        for ($anio = 1; $anio <= $n; $anio++) {

            // Costo de mantenimiento de cada año
            $costoAnual = $mantenimiento
                * pow(1 + $incremento, $anio - 1);

            // Valor de mercado estimado
            $valorMercado = $inversion
                * pow(1 - $perdidaValor, $anio);

            // Valor presente del mantenimiento
            $vpAnual = $costoAnual
                / pow(1 + $tmar, $anio);

            $vpMantenimiento += $vpAnual;

            // Valor presente del valor de mercado
            $vpMercado = $valorMercado
                / pow(1 + $tmar, $anio);

            // Valor presente de los costos
            $vpCostos = $inversion
                + $vpMantenimiento
                - $vpMercado;

            // Factor de recuperación de capital
            $factorAP = $tmar == 0
                ? 1 / $anio
                : $tmar / (1 - pow(1 + $tmar, -$anio));

            // Costo anual uniforme equivalente
            $caue = $vpCostos * $factorAP;

            $tabla[] = [
                'anio' => $anio,
                'mantenimiento' => $costoAnual,
                'valor_mercado' => $valorMercado,
                'vp_mantenimiento' => $vpAnual,
                'vp_mantenimiento_acumulado' => $vpMantenimiento,
                'vp_mercado' => $vpMercado,
                'vp_costos' => $vpCostos,
                'caue' => $caue,
            ];
        }

        // Encontrar el año con menor CAUE
        $optimo = collect($tabla)
            ->sortBy('caue')
            ->first();

        $datos = [
            'cotizaciones' => $cotizaciones,
            'seleccionada' => $seleccionada,
            'tmar' => $tmar,
            'mantenimiento' => $mantenimiento,
            'incremento' => $incremento,
            'perdidaValor' => $perdidaValor,
            'tabla' => $tabla,
            'vidaEconomica' => $optimo['anio'],
            'caueMinimo' => $optimo['caue'],
            'optimo' => $optimo,
            'fuenteDatos' => 'Valores estimados para simulación académica. Pendientes de validación con ASEIS.',
        ];

        if ($request->boolean('json') || !view()->exists('reemplazo.index')) {
            return response()->json(
                $datos,
                200,
                [],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            );
        }

        return view('reemplazo.index', $datos);
    }
}