<?php

namespace App\Http\Controllers\Economia;

use App\Http\Controllers\Controller;
use App\Models\Alternativa;
use App\Models\Cotizacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CotizacionController extends Controller
{
    public function store(Request $request, Alternativa $alternativa)
    {
        $request->merge([
            'codigo' => strtoupper(trim((string) $request->codigo)),
        ]);

        $datos = $request->validate([
            'codigo' => 'required|string|max:20|unique:cotizaciones,codigo',
            'proveedor' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'monto' => 'required|numeric|min:0.01',
            'tipo_fuente' => 'required|in:cotizacion,factura,captura,catalogo,entrevista,estimacion',
            'fecha' => 'nullable|date',
            'condicion' => 'nullable|string',
            'archivo' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
        ]);

        $datos['archivo'] = null;

        if ($request->hasFile('archivo')) {
            $datos['archivo'] = $request->file('archivo')
                ->store('evidencias/cotizaciones', 'public');
        }

        try {
            $alternativa->cotizaciones()->create($datos);
        } catch (\Throwable $e) {
            if ($datos['archivo']) {
                Storage::disk('public')->delete($datos['archivo']);
            }

            throw $e;
        }

        return redirect()->route('alternativas.show', $alternativa)
            ->with('ok', 'Cotización registrada correctamente.');
    }

    public function destroy(Cotizacion $cotizacion)
    {
        $alternativaId = $cotizacion->alternativa_id;
        $archivo = $cotizacion->archivo;

        $cotizacion->delete();

        if ($archivo) {
            Storage::disk('public')->delete($archivo);
        }

        return redirect()->route('alternativas.show', $alternativaId)
            ->with('ok', 'Cotización eliminada correctamente.');
    }
}