<?php

namespace App\Http\Controllers\Economia;

use App\Http\Controllers\Controller;
use App\Models\Alternativa;
use Illuminate\Http\Request;

class EvaluacionEconomicaController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'tmar' => 'nullable|numeric|min:0|max:1000',
        ]);

        $tmar = (float) $request->query('tmar', 10) / 100;

        $alternativas = Alternativa::with('cotizaciones')
            ->orderBy('codigo')
            ->get();

        $resultados = $alternativas->flatMap(function ($alternativa) use ($tmar) {

            return $alternativa->cotizaciones->map(function ($cotizacion) use ($alternativa, $tmar) {

                $inversion = (float) $cotizacion->monto;
                $vidaUtil = (int) $alternativa->vida_util;
                $ingresos = (float) $alternativa->ingresos_anuales;
                $costos = (float) $alternativa->costos_anuales;

                $salvamento = $inversion *
                    $alternativa->porcentaje_salvamento / 100;

                $indicadores = $this->evaluar(
                    $inversion,
                    $vidaUtil,
                    $salvamento,
                    $ingresos,
                    $costos,
                    $tmar
                );

                return (object) ([
                    'codigo' => $cotizacion->codigo,
                    'alternativa' => $alternativa->codigo,
                    'nombre' => $cotizacion->descripcion,
                    'proveedor' => $cotizacion->proveedor,
                    'inversion' => $inversion,
                    'salvamento' => $salvamento,
                    'vida_util' => $vidaUtil,
                    'costos_anuales' => $costos,
                    'ingresos_anuales' => $ingresos,
                ] + $indicadores);
            });

        })->values();

        return view('evaluacion.index', [
            'tmar' => $tmar,
            'fuenteTmar' => 'Valor de prueba; pendiente de confirmar con la ASEIS o el docente',
            'resultados' => $resultados,
        ]);
    }

    private function evaluar(
        float $inversion,
        int $n,
        float $salvamento,
        float $ingresos,
        float $costos,
        float $tmar
    ): array {

        // Flujo neto de efectivo anual
        $fne = $ingresos - $costos;

        // Construcción del flujo de efectivo
        $flujos = [
            0 => -$inversion
        ];

        for ($t = 1; $t <= $n; $t++) {
            $flujos[$t] = $fne;

            if ($t === $n) {
                $flujos[$t] += $salvamento;
            }
        }

        // Factores de equivalencia
        $pa = $tmar == 0
            ? $n
            : (1 - pow(1 + $tmar, -$n)) / $tmar;

        $pf = 1 / pow(1 + $tmar, $n);

        // Valor presente neto
        $vpn = $this->calcularVPN($flujos, $tmar);

        // Tasa interna de retorno
        $tir = $this->calcularTIR($flujos);

        // Costo anual uniforme equivalente neto
        $caue = -$vpn / $pa;

        // Relación beneficio/costo
        $vpBeneficios = $ingresos * $pa;

        $vpCostos = $inversion
            + ($costos * $pa)
            - ($salvamento * $pf);

        $bc = $vpCostos > 0
            ? $vpBeneficios / $vpCostos
            : null;

        // Recuperación simple de la inversión
        $recuperacion = $fne > 0
            ? $inversion / $fne
            : null;

        return [
            'flujos' => $flujos,
            'vpn' => $vpn,
            'tir' => $tir,
            'caue' => $caue,
            'bc' => $bc,
            'recuperacion' => $recuperacion,
        ];
    }

    private function calcularVPN(array $flujos, float $tasa): float
    {
        $vpn = 0;

        foreach ($flujos as $anio => $flujo) {
            $vpn += $flujo / pow(1 + $tasa, $anio);
        }

        return $vpn;
    }

    private function calcularTIR(array $flujos): ?float
    {
        // Método numérico de bisección
        $inferior = -0.99;
        $superior = 10.0;

        $vpnInferior = $this->calcularVPN($flujos, $inferior);
        $vpnSuperior = $this->calcularVPN($flujos, $superior);

        if (abs($vpnInferior) < 0.0000001) {
            return $inferior;
        }

        if (abs($vpnSuperior) < 0.0000001) {
            return $superior;
        }

        if ($vpnInferior * $vpnSuperior > 0) {
            return null;
        }

        for ($i = 0; $i < 200; $i++) {

            $medio = ($inferior + $superior) / 2;

            $vpnMedio = $this->calcularVPN($flujos, $medio);

            if (abs($vpnMedio) < 0.0000001) {
                return $medio;
            }

            if ($vpnInferior * $vpnMedio <= 0) {
                $superior = $medio;
            } else {
                $inferior = $medio;
                $vpnInferior = $vpnMedio;
            }
        }

        return ($inferior + $superior) / 2;
    }
}