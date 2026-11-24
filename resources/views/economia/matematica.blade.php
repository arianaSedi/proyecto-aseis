<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASEIS - Matemática Financiera</title>
</head>
<body>

    <h1>ASEIS</h1>
    <h2>Matemática Financiera</h2>

    <p>Calculadora de intereses, tasas y equivalencias económicas.</p>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('economia.matematica.calcular') }}">

        @csrf

        <label for="operacion">Operación:</label>

        <select name="operacion" id="operacion" required>
            <option value="simple" @selected(old('operacion', $datos['operacion'] ?? '') == 'simple')>
                Interés simple
            </option>

            <option value="compuesto" @selected(old('operacion', $datos['operacion'] ?? '') == 'compuesto')>
                Interés compuesto
            </option>

            <option value="presente" @selected(old('operacion', $datos['operacion'] ?? '') == 'presente')>
                Valor presente
            </option>

            <option value="futuro" @selected(old('operacion', $datos['operacion'] ?? '') == 'futuro')>
                Valor futuro
            </option>

            <option value="efectiva" @selected(old('operacion', $datos['operacion'] ?? '') == 'efectiva')>
                Tasa efectiva anual
            </option>

            <option value="pa" @selected(old('operacion', $datos['operacion'] ?? '') == 'pa')>
                Valor presente de anualidad (P/A)
            </option>

            <option value="fa" @selected(old('operacion', $datos['operacion'] ?? '') == 'fa')>
                Valor futuro de anualidad (F/A)
            </option>

            <option value="ap" @selected(old('operacion', $datos['operacion'] ?? '') == 'ap')>
                Recuperación de capital (A/P)
            </option>

            <option value="af" @selected(old('operacion', $datos['operacion'] ?? '') == 'af')>
                Fondo de amortización (A/F)
            </option>
        </select>

        <br><br>

        <div id="campoCapital">
            <label for="capital" id="labelCapital">
                Capital ($):
            </label>

            <input type="number"
                   name="capital"
                   id="capital"
                   step="0.01"
                   min="0"
                   value="{{ old('capital', $datos['capital'] ?? 450) }}"
                   required>

            <br><br>
        </div>

        <label for="tasa" id="labelTasa">
            Tasa de interés por período (%):
        </label>

        <input type="number"
               name="tasa"
               id="tasa"
               step="0.0001"
               min="0"
               max="1000"
               value="{{ old('tasa', $datos['tasa'] ?? 10) }}"
               required>

        <br><br>

        <label for="periodos" id="labelPeriodos">
            Número de períodos:
        </label>

        <input type="number"
               name="periodos"
               id="periodos"
               min="1"
               max="1000"
               value="{{ old('periodos', $datos['periodos'] ?? 5) }}"
               required>

        <br><br>

        <button type="submit">Calcular</button>

        <a href="{{ route('economia.matematica') }}">
            Limpiar
        </a>

    </form>

    @isset($resultado)

        <hr>

        <h2>Resultado del cálculo</h2>

        <p>
            <strong>Operación:</strong>
            {{ $concepto }}
        </p>

        <p>
            <strong>Fórmula:</strong>
            {{ $formula }}
        </p>

        <h3>
            @if ($unidad == '%')
                {{ number_format($resultado, 2) }} %
            @else
                ${{ number_format($resultado, 2) }}
            @endif
        </h3>

    @endisset

    <script>
        const operacion = document.getElementById('operacion');

        function actualizarCampos() {

            const efectiva = operacion.value === 'efectiva';

            document.getElementById('campoCapital').style.display =
                efectiva ? 'none' : 'block';

            const etiquetas = {
                simple: 'Capital inicial ($):',
                compuesto: 'Capital inicial ($):',
                presente: 'Valor futuro ($):',
                futuro: 'Capital inicial ($):',
                pa: 'Anualidad ($):',
                fa: 'Anualidad ($):',
                ap: 'Valor presente ($):',
                af: 'Valor futuro ($):'
            };

            document.getElementById('labelCapital').textContent =
                etiquetas[operacion.value] || 'Capital ($):';

            document.getElementById('labelTasa').textContent =
                efectiva
                ? 'Tasa nominal anual (%):'
                : 'Tasa de interés por período (%):';

            document.getElementById('labelPeriodos').textContent =
                efectiva
                ? 'Capitalizaciones por año:'
                : 'Número de períodos:';
        }

        operacion.addEventListener('change', actualizarCampos);

        actualizarCampos();
    </script>

</body>
</html>