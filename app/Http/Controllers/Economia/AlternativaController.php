<?php

namespace App\Http\Controllers\Economia;

use App\Http\Controllers\Controller;
use App\Models\Alternativa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class AlternativaController extends Controller
{
    public function index()
    {
        $alternativas = Alternativa::with('cotizaciones')
            ->orderBy('codigo')
            ->get();

        return view('alternativas.index', compact('alternativas'));
    }

    public function create()
    {
        return view('alternativas.form', [
            'alternativa' => null
        ]);
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);

        Alternativa::create($datos);

        return redirect()->route('alternativas.index')
            ->with('ok', 'Alternativa registrada correctamente.');
    }

    public function show(Alternativa $alternativa)
    {
        $alternativa->load('cotizaciones');

        return view('alternativas.show', compact('alternativa'));
    }

    public function edit(Alternativa $alternativa)
    {
        return view('alternativas.form', compact('alternativa'));
    }

    public function update(Request $request, Alternativa $alternativa)
    {
        $datos = $this->validar($request, $alternativa);

        $alternativa->update($datos);

        return redirect()->route('alternativas.show', $alternativa)
            ->with('ok', 'Alternativa actualizada correctamente.');
    }

    public function destroy(Alternativa $alternativa)
    {
        $archivos = $alternativa->cotizaciones()
            ->pluck('archivo')
            ->filter();

        $alternativa->delete();

        foreach ($archivos as $archivo) {
            Storage::disk('public')->delete($archivo);
        }

        return redirect()->route('alternativas.index')
            ->with('ok', 'Alternativa eliminada correctamente.');
    }

    private function validar(Request $request, ?Alternativa $alternativa = null): array
    {
        $request->merge([
            'codigo' => strtoupper(trim((string) $request->codigo)),
        ]);

        $codigoUnico = Rule::unique('alternativas', 'codigo');

        if ($alternativa) {
            $codigoUnico->ignore($alternativa->id);
        }

        return $request->validate([
            'codigo' => ['required', 'string', 'max:20', $codigoUnico],
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'vida_util' => 'required|integer|min:1|max:100',
            'porcentaje_salvamento' => 'required|numeric|min:0|max:100',
            'costos_anuales' => 'required|numeric|min:0',
            'ingresos_anuales' => 'required|numeric|min:0',
        ]);
    }
}