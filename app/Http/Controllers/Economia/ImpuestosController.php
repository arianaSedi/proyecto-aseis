<?php

namespace App\Http\Controllers\Economia;

use App\Http\Controllers\Controller;
use App\Models\Alternativa;
use Illuminate\Http\Request;

class ImpuestosController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'cotizacion' => 'nullable|string|max:20',
            'metodo' => 'nullable|in:linea_recta,suma_digitos',
            'impuesto' => 'nullable|numeric|min:0|max:100',
            'tmar' => 'nullable|numeric|min:0|max:1000',
        ]);

        // Parámetros de evaluación
        $tasaImpuesto = (float) $request->query('impuesto', 0) / 100;
        $tmar = (float) $request->query('tmar', 10) / 100;
        $metodo = $request->query('metodo', 'linea_recta');

        // Obtener datos de PostgreSQL
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
                    'salvamento' => (float) $c->monto
                        * $a->porcentaje_salvamento / 100,
                    'vida_util' => (int) $a->vida_util,
                    'ingresos_anuales' => (float) $a->ingresos_anuales,
                    'costos_anuales' => (float) $a->costos_anuales,
                ];
            });
        })->sortBy('codigo')->values();

        abort_if(
            $cotizaciones->isEmpty(),
            404,
            'No existen cotizaciones registradas.'
        );

        // Seleccionar cotización
        $seleccionada = $cotizaciones->firstWhere(
            'codigo',
            strtoupper($request->query('cotizacion', 'A1'))
        ) ?? $cotizaciones->first();

        $inversion = $seleccionada->inversion;
        $salvamento = $seleccionada->salvamento;
        $n = $seleccionada->vida_util;
        $ingresos = $seleccionada->ingresos_anuales;
        $egresos = $seleccionada->costos_anuales;

        abort_if($n <= 0, 422, 'La vida útil debe ser positiva.');

        $baseDepreciable = $inversion - $salvamento;
        $sda = $n * ($n + 1) / 2;

        $tabla = [];
        $flujos = [0 => -$inversion];
        $flujosAntes = [0 => -$inversion];

        $depreciacionAcumulada = 0;
        $escudoFiscalTotal = 0;

        for ($anio = 1; $anio <= $n; $anio++) {

            // Calcular depreciación
            if ($metodo === 'linea_recta') {
                $depreciacion = $baseDepreciable / $n;
            } else {
                $depreciacion = (($n - $anio + 1) / $sda)
                    * $baseDepreciable;
            }

            $depreciacionAcumulada += $depreciacion;

            $valorLibros = $inversion
                - $depreciacionAcumulada;

            // Utilidad antes de impuestos
            $uai = $ingresos - $egresos - $depreciacion;

            // Impuesto sobre utilidad positiva
            $impuesto = max(0, $uai) * $tasaImpuesto;

            // Utilidad después de impuestos
            $udi = $uai - $impuesto;

            // Flujo neto de efectivo
            $fne = $udi + $depreciacion;

            // Escudo fiscal efectivo
            $impuestoSinDepreciacion =
                max(0, $ingresos - $egresos) * $tasaImpuesto;

            $escudoFiscal = $impuestoSinDepreciacion - $impuesto;

            $escudoFiscalTotal += $escudoFiscal;

            // Valor de salvamento en el último año
            $vs = 0;
            $impuestoSalvamento = 0;

            if ($anio === $n) {
                $vs = $salvamento;

                $gananciaVenta = $vs - $valorLibros;

                $impuestoSalvamento =
                    max(0, $gananciaVenta) * $tasaImpuesto;

                $fne += $vs - $impuestoSalvamento;
            }

            $flujos[$anio] = $fne;

            $flujosAntes[$anio] =
                $ingresos - $egresos + $vs;

            // Guardar fila de resultados
            $tabla[] = [
                'anio' => $anio,
                'ingresos' => $ingresos,
                'egresos' => $egresos,
                'depreciacion' => $depreciacion,
                'uai' => $uai,
                'impuesto' => $impuesto,
                'udi' => $udi,
                'salvamento' => $vs,
                'impuesto_salvamento' => $impuestoSalvamento,
                'escudo_fiscal' => $escudoFiscal,
                'valor_libros' => $valorLibros,
                'fne' => $fne,
                'valor_presente' => $fne / pow(1 + $tmar, $anio),
            ];
        }

        // Indicadores antes y después de impuestos
        $indicadoresAntes = $this->evaluar(
            $flujosAntes,
            $tmar,
            $n
        );

        $indicadores = $this->evaluar(
            $flujos,
            $tmar,
            $n
        );

        $datos = [
            'cotizaciones' => $cotizaciones,
            'seleccionada' => $seleccionada,
            'metodo' => $metodo,
            'tasaImpuesto' => $tasaImpuesto,
            'tmar' => $tmar,
            'tabla' => $tabla,
            'flujos' => $flujos,
            'flujosAntes' => $flujosAntes,
            'indicadores' => $indicadores,
            'indicadoresAntes' => $indicadoresAntes,
            'escudoFiscalTotal' => $escudoFiscalTotal,
            'fuenteImpuesto' => 'Tasa configurable; pendiente de confirmar tratamiento tributario de ASEIS.',
        ];

        // Mostrar JSON mientras se desarrolla la vista
        if ($request->boolean('json') || !view()->exists('impuestos.index')) {
            return response()->json(
                $datos,
                200,
                [],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            );
        }

        return view('impuestos.index', $datos);
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

    private function evaluar(
        array $flujos,
        float $tmar,
        int $n
    ): array {

        $vpn = $this->calcularVPN($flujos, $tmar);

        $tir = $this->calcularTIR($flujos);

        $factorAP = $tmar == 0
            ? 1 / $n
            : $tmar / (1 - pow(1 + $tmar, -$n));

        $caue = -$vpn * $factorAP;

        return [
            'vpn' => $vpn,
            'tir' => $tir,
            'caue' => $caue,
            'aceptable' => $vpn >= 0,
        ];
    }
}