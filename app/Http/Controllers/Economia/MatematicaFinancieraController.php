<?php

namespace App\Http\Controllers\Economia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MatematicaFinancieraController extends Controller
{
    public function index()
    {
        return view('economia.matematica');
    }

    public function calcular(Request $request)
    {
        $esConversion = in_array($request->operacion, [
            'efectiva', 'nominal', 'equivalente'
        ]);

        $request->validate([
            'operacion' => 'required|in:simple,compuesto,presente,futuro,efectiva,pa,fa,ap,af,nominal,equivalente',
            'capital' => $esConversion
                ? 'nullable|numeric|min:0'
                : 'required|numeric|min:0',
            'tasa' => 'required|numeric|min:0|max:1000',
            'periodos' => $request->operacion === 'equivalente'
                ? 'nullable|integer|min:1|max:1000'
                : 'required|integer|min:1|max:1000',
            'frecuencia_origen' => 'required_if:operacion,equivalente|nullable|in:1,2,3,4,6,12,24',
            'frecuencia_destino' => 'required_if:operacion,equivalente|nullable|in:1,2,3,4,6,12,24',
        ]);

        $capital = (float) $request->capital;
        $tasa = $request->tasa / 100;
        $n = (int) $request->periodos;

        $resultado = 0;
        $formula = '';
        $concepto = '';
        $unidad = '$';

        switch ($request->operacion) {

            case 'simple':
                $resultado = $capital * $tasa * $n;
                $formula = 'I = P × i × n';
                $concepto = 'Interés simple';
                break;

            case 'compuesto':
                $resultado = $capital * (pow(1 + $tasa, $n) - 1);
                $formula = 'I = P × [(1 + i)^n - 1]';
                $concepto = 'Interés compuesto';
                break;

            case 'presente':
                $resultado = $capital / pow(1 + $tasa, $n);
                $formula = 'P = F / (1 + i)^n';
                $concepto = 'Valor presente';
                break;

            case 'futuro':
                $resultado = $capital * pow(1 + $tasa, $n);
                $formula = 'F = P × (1 + i)^n';
                $concepto = 'Valor futuro';
                break;

            case 'efectiva':
                $resultado = (pow(1 + $tasa / $n, $n) - 1) * 100;
                $formula = 'TEA = (1 + j/m)^m - 1';
                $concepto = 'Tasa efectiva anual';
                $unidad = '%';
                break;

            case 'nominal':
                $resultado = $n * (pow(1 + $tasa, 1 / $n) - 1) * 100;
                $formula = 'j = m × [(1 + TEA)^(1/m) - 1]';
                $concepto = 'Tasa nominal anual';
                $unidad = '%';
                break;

            case 'equivalente':
                $origen = (int) $request->frecuencia_origen;
                $destino = (int) $request->frecuencia_destino;

                $resultado = (pow(1 + $tasa, $origen / $destino) - 1) * 100;

                $formula = 'i2 = (1 + i1)^(m1/m2) - 1';
                $concepto = 'Tasa efectiva equivalente';
                $unidad = '%';
                break;

            case 'pa':
                $resultado = $tasa == 0
                    ? $capital * $n
                    : $capital * ((1 - pow(1 + $tasa, -$n)) / $tasa);

                $formula = 'P = A × [(1 - (1+i)^(-n)) / i]';
                $concepto = 'Valor presente de una anualidad (P/A)';
                break;

            case 'fa':
                $resultado = $tasa == 0
                    ? $capital * $n
                    : $capital * ((pow(1 + $tasa, $n) - 1) / $tasa);

                $formula = 'F = A × [((1+i)^n - 1) / i]';
                $concepto = 'Valor futuro de una anualidad (F/A)';
                break;

            case 'ap':
                $resultado = $tasa == 0
                    ? $capital / $n
                    : $capital * ($tasa / (1 - pow(1 + $tasa, -$n)));

                $formula = 'A = P × [i / (1 - (1+i)^(-n))]';
                $concepto = 'Recuperación de capital (A/P)';
                break;

            case 'af':
                $resultado = $tasa == 0
                    ? $capital / $n
                    : $capital * ($tasa / (pow(1 + $tasa, $n) - 1));

                $formula = 'A = F × [i / ((1+i)^n - 1)]';
                $concepto = 'Fondo de amortización (A/F)';
                break;
        }

        return view('economia.matematica', [
            'resultado' => $resultado,
            'formula' => $formula,
            'concepto' => $concepto,
            'unidad' => $unidad,
            'datos' => $request->all(),
        ]);
    }
}